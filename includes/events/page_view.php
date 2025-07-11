<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_page_view_event()
{
    $options = get_option('measgaau_options');
    if (!isset($options['page_view']) || !$options['page_view']) {
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measgaau_hash_email($email) : '';

    $event_data = array(
        'event' => 'page_view',
        'page_title' => get_the_title(),
        'page_location' => get_permalink(),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );

    // Store data in cookie instead of inline script
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('measgaau_page_view_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('wp', 'measgaau_page_view_event');

function measgaau_enqueue_page_view_script()
{
    $options = get_option('measgaau_options');
    if (!isset($options['page_view']) || !$options['page_view']) {
        return;
    }
    
    // Register and enqueue script
    wp_register_script('measgaau-page-view-tracking', '', array('jquery'), '1.0', true);
    wp_enqueue_script('measgaau-page-view-tracking');
    
    // Localize script
    wp_localize_script('measgaau-page-view-tracking', 'measgaau_page_view', array(
        'cookie_path' => COOKIEPATH,
        'cookie_domain' => COOKIE_DOMAIN
    ));
    
    // Add inline script
    $inline_script = '
        jQuery(document).ready(function($) {
            function pushPageViewData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("measgaau_page_view_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        console.log("Page View:", data);
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "measgaau_page_view_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + measgaau_page_view.cookie_path + "; domain=" + measgaau_page_view.cookie_domain;
                    } catch(e) {
                        console.error("Page view data error:", e);
                    }
                }
            }
            
            // Check on load
            pushPageViewData();
        });
    ';
    
    wp_add_inline_script('measgaau-page-view-tracking', $inline_script);
}
add_action('wp_footer', 'measgaau_enqueue_page_view_script');
?>