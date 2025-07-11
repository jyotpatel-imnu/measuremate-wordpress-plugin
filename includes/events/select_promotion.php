<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_select_promotion_event()
{
    $options = get_option('measgaau_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measgaau_hash_email($email) : '';
   
    $event_data = array(
        'event' => 'select_promotion',
        'ecommerce' => array(
            'item_list_id' => 'cart',
            'item_list_name' => 'Shopping Cart',
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );
   
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('measgaau_promotion_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_applied_coupon', 'measgaau_select_promotion_event');

function measgaau_enqueue_promotion_script()
{
    $options = get_option('measgaau_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }
    
    // Register and enqueue script
    wp_register_script('measgaau-promotion-tracking', '', array('jquery'), '1.0', true);
    wp_enqueue_script('measgaau-promotion-tracking');
    
    // Localize script
    wp_localize_script('measgaau-promotion-tracking', 'measgaau_promotion', array(
        'cookie_path' => COOKIEPATH,
        'cookie_domain' => COOKIE_DOMAIN
    ));
    
    // Add inline script
    $inline_script = '
        jQuery(document).ready(function($) {
            function pushPromotionData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("measgaau_promotion_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "measgaau_promotion_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + measgaau_promotion.cookie_path + "; domain=" + measgaau_promotion.cookie_domain;
                    } catch(e) {
                        console.error("Promotion data error:", e);
                    }
                }
            }
            
            // Check on load (for non AJAX calls)
            pushPromotionData();
            
            // Check after updates (for AJAX calls)
            $(document.body).on("updated_wc_div applied_coupon fkcart_fragments_refreshed", function() {
                setTimeout(pushPromotionData, 500);
            });
        });
    ';
    
    wp_add_inline_script('measgaau-promotion-tracking', $inline_script);
}
add_action('wp_footer', 'measgaau_enqueue_promotion_script');
?>