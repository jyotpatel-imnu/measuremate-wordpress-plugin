<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_refund($order_id)
{
    $options = get_option('measgaau_options');
    $order = wc_get_order($order_id);

    $cart = WC()->cart;
    $items = array();

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if(empty($product) === True)
        {
            continue;
        }
        
        $categories = get_the_terms($product->get_id(), 'product_cat');
        $item_categories = array();
        foreach ($categories as $category) {
            $item_categories[] = $category->name;
        }

        $items[] = measgaau_format_item($product->get_id(), $item->get_quantity());
    }
    
    // Register and enqueue script
    wp_register_script('measgaau-refund-tracking', '', array(), '1.0', true);
    wp_enqueue_script('measgaau-refund-tracking');
    
    // Prepare data for JavaScript
    $refund_data = array(
        'event' => 'refund',
        'ecommerce' => array(
            'currency' => $order->get_currency(),
            'transaction_id' => $order_id,
            'value' => $order->get_total(),
            'shipping' => $order->get_shipping_total(),
            'tax' => $order->get_total_tax(),
            'items' => $items
        )
    );
    
    // Add inline script
    $inline_script = '
        window.dataLayer = window.dataLayer || [];
        dataLayer.push(' . wp_json_encode($refund_data) . ');
    ';
    
    wp_add_inline_script('measgaau-refund-tracking', $inline_script);
}

add_action('woocommerce_order_status_refunded', 'measgaau_refund');
?>