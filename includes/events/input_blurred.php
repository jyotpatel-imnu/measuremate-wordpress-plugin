<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_input_blurred_ajax_handler()
{
    $options = get_option('measgaau_options');
    if (!isset($options['input_blurred']) || !$options['input_blurred']) {
        wp_die();
    }
    
    // Verify nonce for security - unslash and sanitize first
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'measgaau_input_blurred_nonce')) {
        wp_die('Security check failed');
    }
    
    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measgaau_hash_email($email) : '';
    
    // Unslash and sanitize all POST data
    $input_type = isset($_POST['input_type']) ? sanitize_text_field(wp_unslash($_POST['input_type'])) : '';
    $input_id = isset($_POST['input_id']) ? sanitize_text_field(wp_unslash($_POST['input_id'])) : '';
    $input_label = isset($_POST['input_label']) ? sanitize_text_field(wp_unslash($_POST['input_label'])) : '';
    $had_value = isset($_POST['had_value']) ? sanitize_text_field(wp_unslash($_POST['had_value'])) : 'false';
    $page_title = isset($_POST['page_title']) ? sanitize_text_field(wp_unslash($_POST['page_title'])) : '';
    $page_location = isset($_POST['page_location']) ? esc_url_raw(wp_unslash($_POST['page_location'])) : '';
    $gtm_unique_event_id = isset($_POST['gtm_unique_event_id']) ? absint(wp_unslash($_POST['gtm_unique_event_id'])) : time();
    
    $event_data = array(
        'event' => 'input_blurred',
        'input_data' => array(
            'input_type' => $input_type,
            'input_id' => $input_id,
            'input_label' => $input_label,
            'had_value' => $had_value,
            'page_title' => $page_title,
            'page_location' => $page_location
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        ),
        'gtm.uniqueEventId' => $gtm_unique_event_id
    );
    
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('measgaau_input_blurred_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
    
    wp_send_json_success();
}
add_action('wp_ajax_measgaau_input_blurred', 'measgaau_input_blurred_ajax_handler');
add_action('wp_ajax_nopriv_measgaau_input_blurred', 'measgaau_input_blurred_ajax_handler');

function measgaau_enqueue_input_blurred_script()
{
    $options = get_option('measgaau_options');
    if (!isset($options['input_blurred']) || !$options['input_blurred']) {
        return;
    }
    
    // Register and enqueue script
    wp_register_script('measgaau-input-blurred-tracking', '', array('jquery'), '1.0', true);
    wp_enqueue_script('measgaau-input-blurred-tracking');
    
    // Localize script to pass Ajax URL and nonce
    wp_localize_script('measgaau-input-blurred-tracking', 'measgaau_input_blurred_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('measgaau_input_blurred_nonce'),
        'cookie_path' => COOKIEPATH,
        'cookie_domain' => COOKIE_DOMAIN
    ));
    
    // Add inline script
    $inline_script = '
        jQuery(document).ready(function($) {
            var ajaxurl = measgaau_input_blurred_ajax.ajax_url;
            var nonce = measgaau_input_blurred_ajax.nonce;
            
            function pushInputBlurredData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("measgaau_input_blurred_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        console.log("Input Blurred:", data);
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        document.cookie = "measgaau_input_blurred_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + measgaau_input_blurred_ajax.cookie_path + "; domain=" + measgaau_input_blurred_ajax.cookie_domain;
                    } catch(e) {
                        console.error("Input blurred data error:", e);
                    }
                }
            }
            
            function getInputLabel($input) {
                var label = "";
                
                if ($input.attr("id")) {
                    label = $(\'label[for="\' + $input.attr("id") + \'"]\').text().trim();
                }
                
                if (!label) {
                    label = $input.closest("label").text().trim();
                }
                
                if (!label) {
                    label = $input.prev("label").text().trim();
                }
                
                if (!label) {
                    label = $input.attr("placeholder") || "";
                }
                
                return label.substring(0, 100);
            }
            
            $(document).on("blur", "input:not([type=\'submit\']):not([type=\'button\']), textarea, select", function(e) {
                var $input = $(this);
                
                var inputData = {
                    action: "measgaau_input_blurred",
                    nonce: nonce,
                    input_type: $input.prop("tagName").toLowerCase() === "select" ? "select" : ($input.attr("type") || "text"),
                    input_id: $input.attr("id") || "",
                    input_label: getInputLabel($input),
                    had_value: $input.val() ? "true" : "false",
                    page_title: document.title || "",
                    page_location: window.location.href,
                    gtm_unique_event_id: Date.now()
                };
                
                $.post(ajaxurl, inputData, function(response) {
                    if (response.success) {
                        setTimeout(pushInputBlurredData, 100);
                    }
                });
            });
            
            pushInputBlurredData();
        });
    ';
    
    wp_add_inline_script('measgaau-input-blurred-tracking', $inline_script);
}
add_action('wp_footer', 'measgaau_enqueue_input_blurred_script');
?>