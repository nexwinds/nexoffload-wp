<?php
/**
 * NOFB API Class
 * Handles API connections and requests
 */

if (!defined('ABSPATH')) {
    exit;
}

class NOFB_API {
    
    private $api_key;
    private $api_region;
    private $api_base_url;
    
    public function __construct() {
        $this->api_key = NOFB_API_KEY;
        $this->api_region = NOFB_API_REGION;
        $this->api_base_url = $this->get_api_base_url();
    }
    
    /**
     * Get API base URL based on region
     */
    private function get_api_base_url() {
        if ($this->api_region === 'eu' || $this->api_region === 'me') {
            return 'https://api-eu.nexoffload.nexwinds.com';
        }
        return 'https://api-us.nexoffload.nexwinds.com';
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        // Check if API key is set
        if (empty($this->api_key)) {
            return array(
                'status' => 'error',
                'message' => __('API key not configured.', 'nexoffload-for-bunny')
            );
        }
        
        // Send test request
        $response = wp_remote_get(
            $this->api_base_url . '/v1/account/status',
            array(
                'headers' => array(
                    'x-api-key' => $this->api_key
                ),
                'timeout' => 15,
                'sslverify' => true
            )
        );
        
        if (is_wp_error($response)) {
            return array(
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code === 200 && isset($response_body['status']) && $response_body['status'] === 'active') {
            return array(
                'status' => 'success',
                /* translators: %s: Number of API credits available or N/A if unavailable */
                'message' => sprintf(
                    // translators: %s: Number of API credits available or N/A if unavailable
                    __('API connection successful. Credits: %s', 'nexoffload-for-bunny'),
                    isset($response_body['credits']) ? $response_body['credits'] : 'N/A'
                ),
                'credits' => isset($response_body['credits']) ? $response_body['credits'] : 0
            );
        } elseif ($response_code === 401) {
            return array(
                'status' => 'error',
                'message' => __('Invalid API key.', 'nexoffload-for-bunny')
            );
        } else {
            return array(
                'status' => 'error',
                /* translators: %s: Error message from API response or "Unknown error" if unavailable */
                'message' => sprintf(
                    // translators: %s: Error message from API response or "Unknown error" if unavailable
                    __('API error: %s', 'nexoffload-for-bunny'),
                    isset($response_body['message']) ? $response_body['message'] : 'Unknown error'
                )
            );
        }
    }
    
    /**
     * Get account status and credits
     */
    public function get_account_status() {
        // Send request
        $response = wp_remote_get(
            $this->api_base_url . '/v1/account/status',
            array(
                'headers' => array(
                    'x-api-key' => $this->api_key
                ),
                'timeout' => 15,
                'sslverify' => true
            )
        );
        
        if (is_wp_error($response)) {
            return array(
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code === 200) {
            return $response_body;
        } else {
            return array(
                'status' => 'error',
                /* translators: %s: Error message from API or "Unknown error" if unavailable */
                'message' => sprintf(
                    // translators: %s: Error message from API or "Unknown error" if unavailable
                    __('API error: %s', 'nexoffload-for-bunny'),
                    isset($response_body['message']) ? $response_body['message'] : 'Unknown error'
                )
            );
        }
    }
    
} 