<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_gtm_view_item_list()
{
    $options = get_option('measgaau_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['view_item_list']) && $options['view_item_list']) {
        if (is_shop() || is_product_category() || is_product_tag()) {
            global $wp_query;
            $products = [];
            foreach ($wp_query->posts as $post) {
                $product = wc_get_product($post->ID);
                if ($product) {
                    $products[] = measgaau_format_item($product->get_id());
                }
            }
            $item_list_id = 'default_list_id';
            $item_list_name = 'Default List';

            if (is_product_category()) {
                $queried_object = get_queried_object();
                $item_list_id = $queried_object->term_id;
                $item_list_name = $queried_object->name;
            } elseif (is_search()) {
                $item_list_id = 'search_results';
                $item_list_name = 'Search Results for "' . get_search_query() . '"';
            } elseif (is_shop()) {
                $item_list_id = 'shop_page';
                $item_list_name = 'Shop Page';
            }
            
            // Register and enqueue script
            wp_register_script('measgaau-view-item-list-tracking', '', array(), '1.0', true);
            wp_enqueue_script('measgaau-view-item-list-tracking');
            
            // Prepare data for JavaScript
            $view_item_list_data = array(
                'event' => 'view_item_list',
                'ecommerce' => array(
                    'item_list_id' => $item_list_id,
                    'item_list_name' => $item_list_name,
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
                dataLayer.push(' . wp_json_encode($view_item_list_data) . ');
            ';
            
            wp_add_inline_script('measgaau-view-item-list-tracking', $inline_script);
        }
    }
}
add_action('wp_footer', 'measgaau_gtm_view_item_list');
?>