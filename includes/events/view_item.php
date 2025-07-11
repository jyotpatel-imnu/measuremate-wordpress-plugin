<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_gtm_view_item()
{
    $options = get_option('measgaau_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = measgaau_hash_email($current_user->user_email);
    }
    if (isset($options['view_item']) && $options['view_item']) {
        if (is_product()) {
            global $product;
            // If the global product isn't set, get it based on the current ID.
            if (!$product) {
                $product = wc_get_product(get_the_ID());
            }

            $item = measgaau_format_item($product->get_id());

            if ($product) {
                // Register and enqueue script
                wp_register_script('measgaau-view-item-tracking', '', array(), '1.0', true);
                wp_enqueue_script('measgaau-view-item-tracking');
                
                // Prepare data for JavaScript
                $view_item_data = array(
                    'event' => 'view_item',
                    'ecommerce' => array(
                        'currency' => get_woocommerce_currency(),
                        'value' => $product->get_price(),
                        'items' => array($item),
                        'user_data' => array(
                            'email' => $current_user->user_email,
                            'email_hashed' => $hashed_email
                        )
                    )
                );
                
                // Add inline script
                $inline_script = '
                    window.dataLayer = window.dataLayer || [];
                    dataLayer.push(' . wp_json_encode($view_item_data) . ');
                ';
                
                wp_add_inline_script('measgaau-view-item-tracking', $inline_script);
            }
        }
    }
}
add_action('wp_footer', 'measgaau_gtm_view_item');
?>