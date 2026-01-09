<?php
/**
 * Documentation template for Nexoffload
 */
if (!defined('ABSPATH')) {
    exit;
}

// Verify request when needed
$nonce_is_valid = true;
if (isset($_REQUEST['_wpnonce'])) {
    $nonce_is_valid = wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'nofb_documentation');
}

// Get active tab
$active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'getting-started';
?>

<div class="wrap nofb-documentation">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="nofb-documentation-container">
        <div class="nofb-sidebar">
            <ul>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=nexoffload-for-bunny-documentation&tab=getting-started&_wpnonce=' . wp_create_nonce('nofb_documentation'))); ?>" class="<?php echo esc_attr($active_tab == 'getting-started' ? 'active' : ''); ?>">
                        <?php esc_html_e('Getting Started', 'nexoffload-for-bunny'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=nexoffload-for-bunny-documentation&tab=configuration&_wpnonce=' . wp_create_nonce('nofb_documentation'))); ?>" class="<?php echo esc_attr($active_tab == 'configuration' ? 'active' : ''); ?>">
                        <?php esc_html_e('Configuration', 'nexoffload-for-bunny'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=nexoffload-for-bunny-documentation&tab=optimization&_wpnonce=' . wp_create_nonce('nofb_documentation'))); ?>" class="<?php echo esc_attr($active_tab == 'optimization' ? 'active' : ''); ?>">
                        <?php esc_html_e('Optimization', 'nexoffload-for-bunny'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=nexoffload-for-bunny-documentation&tab=faq&_wpnonce=' . wp_create_nonce('nofb_documentation'))); ?>" class="<?php echo esc_attr($active_tab == 'faq' ? 'active' : ''); ?>">
                        <?php esc_html_e('FAQ', 'nexoffload-for-bunny'); ?>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nofb-content">
            <?php if ($active_tab === 'getting-started'): ?>
            
            <h2><?php esc_html_e('Getting Started with Nexoffload', 'nexoffload-for-bunny'); ?></h2>
            
            <p><?php esc_html_e('Nexoffload helps you optimize your media files for faster delivery.', 'nexoffload-for-bunny'); ?></p>
            
            <h3><?php esc_html_e('Quick Start Guide', 'nexoffload-for-bunny'); ?></h3>
            
            <ol>
                <li>
                    <strong><?php esc_html_e('Set up API credentials', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('Go to the Settings page and enter your NOFB API key.', 'nexoffload-for-bunny'); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('Optimize your media files', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('Use the Media Manager to optimize your existing media files or enable automatic optimization for new uploads.', 'nexoffload-for-bunny'); ?></p>
                </li>
            </ol>
            
            <div class="nofb-cta">
                <a href="<?php echo esc_url(admin_url('admin.php?page=nexoffload-for-bunny-settings')); ?>" class="button button-primary">
                    <?php esc_html_e('Configure Settings', 'nexoffload-for-bunny'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=nexoffload-for-bunny-manager')); ?>" class="button">
                    <?php esc_html_e('Manage Media', 'nexoffload-for-bunny'); ?>
                </a>
            </div>
            
            <?php elseif ($active_tab === 'configuration'): ?>
            
            <h2><?php esc_html_e('Configuration', 'nexoffload-for-bunny'); ?></h2>
            
            <h3><?php esc_html_e('API Settings', 'nexoffload-for-bunny'); ?></h3>
            
            <p><?php esc_html_e('To use Bunny Media Offload, you need to configure your Bunny.net API credentials:', 'nexoffload-for-bunny'); ?></p>
            
            <ul>
                <li>
                    <strong><?php esc_html_e('Bunny API Key', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('You can find your API key in your Bunny.net account settings.', 'nexoffload-for-bunny'); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('Storage Zone', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('The name of your Storage Zone on Bunny.net.', 'nexoffload-for-bunny'); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('Custom Hostname', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('If you have a custom domain for your CDN, enter it here.', 'nexoffload-for-bunny'); ?></p>
                </li>
            </ul>
            
            <h3><?php esc_html_e('General Settings', 'nexoffload-for-bunny'); ?></h3>
            
            <ul>
                <li>
                    <strong><?php esc_html_e('Auto Optimization', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('Automatically optimize new media uploads.', 'nexoffload-for-bunny'); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('File Versioning', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('Add version parameters to file URLs for better cache control.', 'nexoffload-for-bunny'); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e('Max File Size', 'nexoffload-for-bunny'); ?></strong>
                    <p><?php esc_html_e('Maximum file size for optimization in KB.', 'nexoffload-for-bunny'); ?></p>
                </li>
            </ul>
            
            <h3><?php esc_html_e('Advanced Configuration', 'nexoffload-for-bunny'); ?></h3>
            
            <p><?php esc_html_e('For added security, you can define your API credentials in your wp-config.php file:', 'nexoffload-for-bunny'); ?></p>
            
            <h4><?php esc_html_e('Bunny.net Storage API', 'nexoffload-for-bunny'); ?></h4>
            <p><?php esc_html_e('These settings are required for CDN migration and file storage:', 'nexoffload-for-bunny'); ?></p>
            
            <pre><code>define('BUNNY_API_KEY', 'your_bunny_api_key_here');
define('BUNNY_STORAGE_ZONE', 'your_storage_zone_here');
define('BUNNY_CUSTOM_HOSTNAME', 'cdn.yourdomain.com');</code></pre>
            
            <h4><?php esc_html_e('nofb Optimization API', 'nexoffload-for-bunny'); ?></h4>
            <p><?php esc_html_e('These settings are required for image optimization services:', 'nexoffload-for-bunny'); ?></p>
            
            <pre><code>define('NOFB_API_KEY', 'your_nofb_api_key_here');
define('NOFB_API_REGION', 'us'); // 'us' or 'eu'</code></pre>
            
            <?php elseif ($active_tab === 'optimization'): ?>
            
            <h2><?php esc_html_e('Media Optimization', 'nexoffload-for-bunny'); ?></h2>
            
            <p><?php esc_html_e('Nexoffload can optimize your media files to reduce file size without sacrificing quality.', 'nexoffload-for-bunny'); ?></p>
            
            <h3><?php esc_html_e('How Optimization Works', 'nexoffload-for-bunny'); ?></h3>
            
            <p><?php esc_html_e('The optimization process uses industry-standard image compression algorithms to reduce file sizes:', 'nexoffload-for-bunny'); ?></p>
            
            <h4><?php esc_html_e('Supported File Types for Optimization', 'nexoffload-for-bunny'); ?></h4>
            <p><?php esc_html_e('Optimization support: JPEG, JPG, PNG, HEIC, TIFF, AVIF and WEBP.', 'nexoffload-for-bunny'); ?></p>
            
            <ul>
                <li><?php esc_html_e('JPEG/JPG files: Optimized with lossy compression while maintaining visual quality', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('PNG files: Optimized with lossless compression to maintain exact pixel-perfect quality', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('HEIC files: Apple\'s high-efficiency image format with advanced compression', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('TIFF files: Lossless compression while maintaining full quality', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('AVIF/WebP files: Modern formats with superior compression efficiency', 'nexoffload-for-bunny'); ?></li>
            </ul>
            
            <h4><?php esc_html_e('Optimization Eligibility', 'nexoffload-for-bunny'); ?></h4>
            <p><strong><?php esc_html_e('Note:', 'nexoffload-for-bunny'); ?></strong> <?php esc_html_e('Files of type JPEG, JPG, PNG, HEIC, TIFF are eligible for optimization regardless of size.', 'nexoffload-for-bunny'); ?></p>
            
            <h3><?php esc_html_e('Batch Optimization', 'nexoffload-for-bunny'); ?></h3>
            
            <p><?php esc_html_e('To optimize all your existing media files:', 'nexoffload-for-bunny'); ?></p>
            
            <ol>
                <li><?php esc_html_e('Go to the Media Manager > Optimization tab', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('Click the "Optimize All Media Files" button', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('Monitor the progress in the optimization log', 'nexoffload-for-bunny'); ?></li>
            </ol>
            
            <h3><?php esc_html_e('Automatic Optimization', 'nexoffload-for-bunny'); ?></h3>
            
            <p><?php esc_html_e('To enable automatic optimization for new uploads:', 'nexoffload-for-bunny'); ?></p>
            
            <ol>
                <li><?php esc_html_e('Go to Settings > General Settings', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('Enable the "Auto Optimization" option', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('Set your preferred max file size', 'nexoffload-for-bunny'); ?></li>
                <li><?php esc_html_e('Save changes', 'nexoffload-for-bunny'); ?></li>
            </ol>
            
            <?php elseif ($active_tab === 'faq'): ?>
            
            <h2><?php esc_html_e('Frequently Asked Questions', 'nexoffload-for-bunny'); ?></h2>
            
            <div class="nofb-faq">
                <div class="nofb-faq-item">
                    <h3><?php esc_html_e('How does the optimization process affect image quality?', 'nexoffload-for-bunny'); ?></h3>
                    <div class="nofb-faq-content">
                        <p><?php esc_html_e('The plugin uses high-quality compression algorithms that reduce file size while maintaining visual quality. In most cases, the difference is imperceptible to the human eye.', 'nexoffload-for-bunny'); ?></p>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div> 