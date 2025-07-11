<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_add_to_wishlist()
{
    $options = get_option('measgaau_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    
    if (isset($options['add_to_wishlist']) && $options['add_to_wishlist']) {
        $wishlist_items = get_user_wishlist_items(); // Replace with actual wishlist logic

        $cart = WC()->cart;
        $items = measgaau_format_cart_items($cart);

        $total_value = 0;
        foreach ($wishlist_items as $item) {
            $product = wc_get_product($item->product_id);
            $item_price = $product->get_price();
            $total_value += $item_price * $item->quantity;
        }

        // Register and enqueue script
        wp_register_script('measgaau-add-to-wishlist', '', array(), '1.0', true);
        wp_enqueue_script('measgaau-add-to-wishlist');
        
        // Prepare data
        $wishlist_data = array(
            'event' => 'add_to_wishlist',
            'ecommerce' => array(
                'currency' => get_woocommerce_currency(),
                'value' => $total_value,
                'items' => $items
            ),
            'user_data' => array(
                'email_hashed' => $hashed_email,
                'email' => $current_user->user_email
            )
        );
        
        // Add inline script
        $inline_script = '
            window.dataLayer = window.dataLayer || [];
            dataLayer.push(' . wp_json_encode($wishlist_data) . ');
        ';
        
        wp_add_inline_script('measgaau-add-to-wishlist', $inline_script);
    }
}

// Adjust the hook based on your wishlist functionality
add_action('your_wishlist_add_action', 'measgaau_add_to_wishlist');
?>