<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_gtm_view_cart()
{
    $options = get_option('measgaau_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['view_cart']) && $options['view_cart']) {
        if (is_cart() && WC()->cart) {
            $cart = WC()->cart->get_cart();
            $products = [];
            $total_value = 0;
            foreach ($cart as $cart_item) {
                $product = $cart_item['data'];
                $price = $product->get_price();
                $qty = $cart_item['quantity'];
                if (!is_numeric($price)) $price = 0;
                if (!is_numeric($qty)) $qty = 0;
                $item_total = (float) $price * (float) $qty;
                $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
                $category_list = implode(', ', $categories);

                $products[] = measgaau_format_item($product->get_id(), $cart_item['quantity']);
                $total_value += $item_total;
            }
            
            // Register and enqueue script
            wp_register_script('measgaau-view-cart-tracking', '', array(), '1.0', true);
            wp_enqueue_script('measgaau-view-cart-tracking');
            
            // Prepare data for JavaScript
            $view_cart_data = array(
                'event' => 'view_cart',
                'ecommerce' => array(
                    'currency' => get_woocommerce_currency(),
                    'value' => $total_value,
                    'items' => $products
                ),
                'user_data' => array(
                    'email_hashed' => $hashed_email,
                    'email' => $email
                )
            );
            
            // Add inline script
            $inline_script = '
                window.dataLayer = window.dataLayer || [];
                dataLayer.push(' . wp_json_encode($view_cart_data) . ');
            ';
            
            wp_add_inline_script('measgaau-view-cart-tracking', $inline_script);
        }
    }
}
add_action('wp_footer', 'measgaau_gtm_view_cart');
?>