<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_gtm_purchase($order_id)
{
    $options = get_option('measgaau_options');

    if (isset($options['purchase']) && $options['purchase']) {
        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $products = [];

        foreach ($items as $item) {
            $product = $item->get_product();
            $products[] = measgaau_format_item($product->get_id(), $item->get_quantity());
        }

        $hashed_email = measgaau_hash_email($order->get_billing_email());
        $hashed_phone = measgaau_hash_email($order->get_billing_phone());
        
        // Register and enqueue script
        wp_register_script('measgaau-purchase-tracking', '', array(), '1.0', true);
        wp_enqueue_script('measgaau-purchase-tracking');
        
        // Prepare data for JavaScript
        $purchase_data = array(
            'event' => 'purchase',
            'ecommerce' => array(
                'currency' => $order->get_currency(),
                'transaction_id' => $order->get_order_number(),
                'value' => $order->get_total(),
                'tax' => $order->get_total_tax(),
                'shipping' => $order->get_shipping_total(),
                'coupon' => implode(', ', $order->get_coupon_codes()),
                'items' => $products,
                'user_data' => array(
                    'email' => $order->get_billing_email(),
                    'email_hashed' => $hashed_email,
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'address_1' => $order->get_billing_address_1(),
                    'address_2' => $order->get_billing_address_2(),
                    'city' => $order->get_billing_city(),
                    'postcode' => $order->get_billing_postcode(),
                    'country' => $order->get_billing_country(),
                    'state' => $order->get_billing_state(),
                    'phone' => $order->get_billing_phone(),
                    'phone_hashed' => $hashed_phone
                ),
            )
        );
        
        // Add inline script
        $inline_script = '
            window.dataLayer = window.dataLayer || [];
            dataLayer.push(' . wp_json_encode($purchase_data) . ');
        ';
        
        wp_add_inline_script('measgaau-purchase-tracking', $inline_script);
    }
}
add_action('woocommerce_thankyou', 'measgaau_gtm_purchase');
?>