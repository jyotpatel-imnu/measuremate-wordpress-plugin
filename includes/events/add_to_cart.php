<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_add_to_cart_event($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    $options = get_option('measgaau_options');
    if (!isset($options['add_to_cart']) || !$options['add_to_cart']) {
        return;
    }

    $product = wc_get_product($product_id);
    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measgaau_hash_email($email) : '';

    $item = measgaau_format_item($product_id, $quantity);
    
    $event_data = array(
        'event' => 'add_to_cart',
        'ecommerce' => array(
            'currency' => get_woocommerce_currency(),
            'value' => floatval($product->get_price()) * $quantity,
            'items' => array($item),
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );

    // Store data in cookie instead of inline script
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('measgaau_add_to_cart_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_add_to_cart', 'measgaau_add_to_cart_event', 10, 6);

function measgaau_enqueue_add_to_cart_script()
{
    $options = get_option('measgaau_options');
    if (!isset($options['add_to_cart']) || !$options['add_to_cart']) {
        return;
    }
    
    // Register and enqueue script
    wp_register_script('measgaau-add-to-cart', '', array('jquery'), '1.0', true);
    wp_enqueue_script('measgaau-add-to-cart');
    
    // Localize script to pass cookie paths
    wp_localize_script('measgaau-add-to-cart', 'measgaau_add_to_cart', array(
        'cookie_path' => COOKIEPATH,
        'cookie_domain' => COOKIE_DOMAIN
    ));
    
    // Add inline script
    $inline_script = '
        jQuery(document).ready(function($) {
            function pushAddToCartData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("measgaau_add_to_cart_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        console.log("Add to Cart:", data);
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "measgaau_add_to_cart_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + measgaau_add_to_cart.cookie_path + "; domain=" + measgaau_add_to_cart.cookie_domain;
                    } catch(e) {
                        console.error("Add to cart data error:", e);
                    }
                }
            }
            
            // Check on load (for non-AJAX calls)
            pushAddToCartData();
            
            // Check after AJAX add to cart events
            $(document.body).on("added_to_cart updated_wc_div wc_fragments_refreshed", function() {
                setTimeout(pushAddToCartData, 500);
            });
        });
    ';
    
    wp_add_inline_script('measgaau-add-to-cart', $inline_script);
}
add_action('wp_footer', 'measgaau_enqueue_add_to_cart_script');

?>