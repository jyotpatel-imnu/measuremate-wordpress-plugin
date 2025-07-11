<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_add_payment_info()
{
    $options = get_option('measgaau_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    // Check if we're in checkout
    if (is_checkout()) {
        $cart = WC()->cart;
        if ($cart) {
            $total_value = 0;
            $items = measgaau_format_cart_items($cart);
    
            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                $item_total = $cart_item['line_total'];
                $total_value += $item_total;
            }
        }
    }

    if (isset($options['add_payment_info']) && $options['add_payment_info']) {
        // Register and enqueue script
        wp_register_script('measgaau-add-payment-info', '', array(), '1.0', true);
        wp_enqueue_script('measgaau-add-payment-info');
        
        // Prepare data
        $payment_data = array(
            'event' => 'add_payment_info',
            'ecommerce' => array(
                'currency' => get_woocommerce_currency(),
                'value' => $total_value,
                'items' => $items
            ),
            'user_data' => array(
                'email' => $current_user->user_email,
                'email_hashed' => $hashed_email
            )
        );
        
        // Add inline script
        $inline_script = '
            window.dataLayer = window.dataLayer || [];
            dataLayer.push(' . wp_json_encode($payment_data) . ');
        ';
        
        wp_add_inline_script('measgaau-add-payment-info', $inline_script);
    }
}

add_action('woocommerce_after_checkout_billing_form', 'measgaau_add_payment_info');
?>