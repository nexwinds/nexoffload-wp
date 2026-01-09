<?php
/**
 * Plugin Name: Nexoffload for Bunny
 * Plugin Full Name: Nexoffload Media for Bunny – Optimize & Deliver
 * Plugin Slug: nexoffload-for-bunny
 * Plugin URI: https://nexoffload.nexwinds.com
 * Description: Seamlessly optimize and offload WordPress media to Bunny.net Edge Storage for blazing-fast delivery and lighter server load.
 * Version: 1.0.0
 * Author: diogoc,Nexwinds
 * Author URI: https://nexwinds.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: nexoffload-for-bunny
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NOFB_VERSION', '1.0.0');
define('NOFB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NOFB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NOFB_PLUGIN_FILE', __FILE__);

// For backward compatibility with older naming
define('nofb_VERSION', NOFB_VERSION);
define('nofb_PLUGIN_DIR', NOFB_PLUGIN_DIR);
define('nofb_PLUGIN_URL', NOFB_PLUGIN_URL);
define('nofb_PLUGIN_FILE', NOFB_PLUGIN_FILE);

// Define default settings
define('NOFB_DEFAULT_MAX_FILE_SIZE', 150); // KB
define('NOFB_MAX_INTERNAL_QUEUE', 100);

// Internal batch size globals (not user-configurable)
global $nofb_optimization_batch_size;
$nofb_optimization_batch_size = 5;

// Load configuration from wp-config.php
if (!defined('NOFB_API_KEY')) {
    define('NOFB_API_KEY', '');
}
if (!defined('NOFB_API_REGION')) {
    define('NOFB_API_REGION', 'us'); // Default to US region
}

// Activation hook
register_activation_hook(__FILE__, 'nofb_activate');
function nofb_activate() {
    // Set default options
    $defaults = array(
        'nofb_auto_optimize' => false,
        'nofb_file_versioning' => false,
        'nofb_max_file_size' => NOFB_DEFAULT_MAX_FILE_SIZE,
        'nofb_optimization_queue' => array()
    );
    
    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }
    
    // Schedule cron jobs
    if (!wp_next_scheduled('nofb_process_optimization_queue')) {
        wp_schedule_event(time(), 'every_minute', 'nofb_process_optimization_queue');
    }
}

/**
 * Synchronize metadata between old and new keys to ensure consistency
 * This helps fix issues after plugin renaming
 */
function nofb_synchronize_metadata() {
    global $wpdb;
    
    // Get all attachment IDs
    $cache_key = 'nofb_sync_attachments';
    $attachments = wp_cache_get($cache_key, 'bunny_media_offload');
    
    if (false === $attachments) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $attachments = $wpdb->get_results(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit'"
        );
        
        wp_cache_set($cache_key, $attachments, 'bunny_media_offload', HOUR_IN_SECONDS);
    }
    
    $updated_count = 0;
    
    // Key pairs to check for synchronization (old => new)
    $meta_key_pairs = array(
        '_bmfo_optimized' => '_nofb_optimized',
        '_bmfo_original_size' => '_nofb_original_size',
        '_bmfo_optimized_size' => '_nofb_optimized_size'
    );
    
    foreach ($attachments as $attachment) {
        $attachment_id = $attachment->ID;
        $updated = false;
        
        foreach ($meta_key_pairs as $old_key => $new_key) {
            $meta_value = get_post_meta($attachment_id, $old_key, true);
            if ($meta_value) {
                update_post_meta($attachment_id, $new_key, $meta_value);
                $updated = true;
            }
        }
        
        if ($updated) {
            $updated_count++;
        }
    }
    
    return $updated_count;
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'nofb_deactivate');
function nofb_deactivate() {
    // Clear scheduled hooks
    wp_clear_scheduled_hook('nofb_process_optimization_queue');
}

// Add custom cron interval
add_filter('cron_schedules', 'nofb_add_cron_interval');
function nofb_add_cron_interval($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => esc_html__('Every Minute', 'nexoffload-for-bunny')
    );
    return $schedules;
}

// Load class files
require_once NOFB_PLUGIN_DIR . 'includes/class-nofb-api.php';
require_once NOFB_PLUGIN_DIR . 'includes/class-nofb-queue.php';
require_once NOFB_PLUGIN_DIR . 'includes/class-nofb-optimizer.php';
require_once NOFB_PLUGIN_DIR . 'includes/class-nofb-media-library.php';
require_once NOFB_PLUGIN_DIR . 'includes/class-nofb-eligibility.php';
require_once NOFB_PLUGIN_DIR . 'includes/class-nofb-ajax.php';
require_once NOFB_PLUGIN_DIR . 'admin/class-nofb-admin.php';

// Optimized helper functions

/**
 * Get cached plugin options to avoid repeated database calls
 */
function nofb_get_cached_options() {
    static $options_cache = null;
    
    if ($options_cache === null) {
        $options_cache = array(
            'file_versioning' => get_option('nofb_file_versioning', false),
            'auto_optimize' => get_option('nofb_auto_optimize', false)
        );
    }
    
    return $options_cache;
}

/**
 * Get attachment ID from URL with caching
 */
function nofb_get_attachment_id_from_url($url) {
    static $id_cache = array();
    
    $cache_key = md5($url);
    if (isset($id_cache[$cache_key])) {
        return $id_cache[$cache_key];
    }
    
    // Try direct lookup
    $attachment_id = attachment_url_to_postid($url);
    
    if (!$attachment_id) {
        // Try without size suffix
        $clean_url = preg_replace('/-\d+x\d+(\.[a-zA-Z]+)$/', '$1', $url);
        $attachment_id = attachment_url_to_postid($clean_url);
    }
    
    if (!$attachment_id) {
        // Database lookup as last resort
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['baseurl'] . '/', '', $url);
        $relative_path_clean = preg_replace('/-\d+x\d+(\.[a-zA-Z]+)$/', '$1', $relative_path);
        
        global $wpdb;
        $cache_key_db = 'nofb_attachment_by_file_' . md5($relative_path . $relative_path_clean);
        $attachment_id = wp_cache_get($cache_key_db, 'nexoffload_for_bunny');
        
        if (false === $attachment_id) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $attachment_id = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta 
                 WHERE meta_key = '_wp_attached_file' 
                 AND (meta_value = %s OR meta_value = %s)",
                $relative_path,
                $relative_path_clean
            ));
            
            wp_cache_set($cache_key_db, $attachment_id, 'nexoffload_for_bunny', HOUR_IN_SECONDS);
        }
    }
    
    $id_cache[$cache_key] = $attachment_id ? intval($attachment_id) : 0;
    return $id_cache[$cache_key];
}

/**
 * Batch get attachment meta to reduce database queries
 */
function nofb_get_attachments_meta($attachment_ids) {
    global $wpdb;
    
    if (empty($attachment_ids)) return array();
    
    $cache_key = 'nofb_batch_meta_' . md5(implode(',', $attachment_ids));
    $cached = wp_cache_get($cache_key, 'nofb_attachments');
    
    if ($cached !== false) {
        return $cached;
    }
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct DB query required for bulk metadata retrieval with custom caching (see wp_cache_set below)
    $query = $wpdb->prepare(
        sprintf(
            "SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta 
             WHERE post_id IN (%s) 
             AND meta_key IN ('_nofb_migrated', '_nofb_bunny_url', '_nofb_version')",
            implode(',', array_fill(0, count($attachment_ids), '%d'))
        ),
        $attachment_ids
    );
    /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- $query is properly prepared above; Required for bulk metadata retrieval with custom caching */
    $results = $wpdb->get_results($query);
    
    $organized = array();
    foreach ($results as $row) {
        $organized[$row->post_id][$row->meta_key] = $row->meta_value;
    }
    
    wp_cache_set($cache_key, $organized, 'nofb_attachments', HOUR_IN_SECONDS);
    return $organized;
}

// Initialize the plugin
add_action('init', 'nofb_init');
function nofb_init() {
    global $nofb_media_library, $nofb_admin, $nofb_ajax;
    
    // Load text domain for translations
    load_plugin_textdomain('nexoffload-for-bunny', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    
    // Initialize classes
    $nofb_media_library = new NOFB_Media_Library();
    
    // Admin functionality
    if (is_admin()) {
        $nofb_admin = new NOFB_Admin();
        $nofb_ajax = new NOFB_AJAX();
    }
    
    // Hook into media upload if auto-optimize is enabled
    $options = nofb_get_cached_options();
    if ($options['auto_optimize']) {
        add_filter('wp_handle_upload', 'nofb_handle_upload', 10, 2);
    }
}

// Handle file upload
function nofb_handle_upload($upload, $context) {
    if (!is_array($upload) || isset($upload['error'])) {
        return $upload;
    }
    
    // Skip the optimization if user disables it explicitly or site is not HTTPS
    if (get_option('nofb_disable_auto_optimize', false) || !is_ssl()) {
        return $upload;
    }
    
    // Add to optimization queue
    $queue = new NOFB_Queue('optimization');
    $queue->add($upload['file']);
    
    return $upload;
}

// Process optimization queue cron job
add_action('nofb_process_optimization_queue', 'nofb_process_optimization_queue_callback');
function nofb_process_optimization_queue_callback() {
    try {
        nofb_log('Starting optimization process...');
        
        $queue = new NOFB_Queue('optimization');
        $optimizer = new NOFB_Optimizer();
        
        global $nofb_optimization_batch_size;
        
        // Allow filtering the batch size
        $batch_size = apply_filters('nofb_optimization_batch_size', $nofb_optimization_batch_size);
        
        // Ensure batch size is within limits (1-5)
        $batch_size = min(max(1, $batch_size), 5);
        
        $batch = $queue->get_batch($batch_size);
        if (!empty($batch)) {
            nofb_log('Processing batch of ' . count($batch) . ' files...');
            
            // Verify files exist and are readable before proceeding
            $valid_batch = array();
            foreach ($batch as $file_path) {
                if (!file_exists($file_path)) {
                    nofb_log('File does not exist: ' . basename($file_path), 'warning');
                    // Remove from queue to prevent processing attempts
                    $queue->remove($file_path);
                    continue;
                }
                
                if (!is_readable($file_path)) {
                    nofb_log('File is not readable: ' . basename($file_path), 'warning');
                    // Remove from queue to prevent processing attempts
                    $queue->remove($file_path);
                    continue;
                }
                
                $valid_batch[] = $file_path;
            }
            
            // Log files being optimized
            if (defined('WP_DEBUG') && WP_DEBUG) {
                nofb_log('Files to optimize: ' . implode(', ', array_map('basename', $valid_batch)));
            }
            
            // Skip processing if no valid files remain
            if (empty($valid_batch)) {
                nofb_log('No valid files to process in batch.', 'warning');
                return;
            }
            
            // Process valid batch
            $result = $optimizer->optimize_batch($valid_batch);
            
            if ($result === false) {
                nofb_log('Error: Optimization failed.', 'error');
                // Add more detailed error context
                if (function_exists('error_get_last') && $error = error_get_last()) {
                    nofb_log('Last PHP error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'], 'error');
                }
                
                // Check server resource limits
                $memory_usage = function_exists('memory_get_usage') ? round(memory_get_usage() / 1024 / 1024, 2) . 'MB' : 'N/A';
                $memory_limit = ini_get('memory_limit');
                nofb_log("System info - Memory usage: {$memory_usage}, Memory limit: {$memory_limit}", 'info');
                
                // Keep failed files in queue for retry unless tried too many times
                foreach ($valid_batch as $file_path) {
                    $retry_count = $queue->get_retry_count($file_path);
                    if ($retry_count >= 3) {
                        nofb_log('Removing file after 3 failed attempts: ' . basename($file_path), 'warning');
                        $queue->remove($file_path);
                    }
                }
            } else if ($result === 0) {
                nofb_log('Optimization completed but no files were optimized. Check API response for details.', 'warning');
                // Remove processed files from queue
                $queue->remove_batch($valid_batch);
            } else {
                nofb_log('Processed ' . count($valid_batch) . ' files with ' . $result . ' successes.');
                // Remove successfully processed files from queue
                $queue->remove_batch($valid_batch);
            }
        } else {
            nofb_log('No files to process in queue.');
        }
    } catch (Exception $e) {
        nofb_log('Exception in optimization process: ' . $e->getMessage(), 'error');
        nofb_log('Exception trace: ' . $e->getTraceAsString(), 'error');
    } catch (Error $e) {
        nofb_log('Fatal error in optimization process: ' . $e->getMessage(), 'error');
        nofb_log('Error trace: ' . $e->getTraceAsString(), 'error');
    }
}

/**
 * Filter: nofb_api_timeout
 * 
 * Allows customizing the API timeout value for image optimization requests.
 * If you experience timeout issues, you can increase this value.
 * 
 * @param int $timeout The timeout value in seconds. Default: 120
 * @return int Modified timeout value
 * 
 * Example usage:
 * add_filter('nofb_api_timeout', function($timeout) { return 180; }); // Set to 3 minutes
 */

/**
 * Filter: nofb_optimization_batch_size
 * 
 * Allows dynamically adjusting the optimization batch size.
 * If you experience timeout issues, you can reduce this value.
 * 
 * @param int $batch_size The batch size (1-5). Default: 5
 * @return int Modified batch size
 * 
 * Example usage:
 * add_filter('nofb_optimization_batch_size', function($size) { return 2; }); // Process 2 images at a time
 */

/**
 * Internal logging function with proper debug checks
 * 
 * @param string $message Message to log
 * @param string $level Log level (info, warning, error)
 * @return void
 */
function nofb_log($message, $level = 'info') {
    // Only log if debugging is enabled
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $timestamp = '[' . gmdate('H:i:s') . '.' . sprintf('%03d', round(microtime(true) % 1 * 1000)) . ']';
        $formatted_message = $timestamp . ' nofb [' . strtoupper($level) . ']: ' . esc_html($message);
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only used in development environments
        error_log($formatted_message);
    }
    
    // Always log critical errors regardless of debug setting
    if ($level === 'error' && defined('nofb_CRITICAL_LOGGING') && nofb_CRITICAL_LOGGING) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only used for critical errors in production
        error_log('nofb Critical Error: ' . esc_html($message));
    }
}
