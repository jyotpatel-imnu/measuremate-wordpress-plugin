<?php
if (!defined('ABSPATH')) exit;

function measgaau_view_promotion()
{
    $options = get_option('measgaau_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['view_promotion']) && $options['view_promotion']) {
        // Veronderstelt dat er een manier is om de getoonde promoties op te halen
        $promotions = []; // Hier moet een logica komen om promoties op te halen

        if (!empty($promotions)) {
            $promotion_items = [];
            
            foreach ($promotions as $promotion) {
                $coupon = new WC_Coupon($promotion['coupon_code']);

                $promotion_id = '';
                $promotion_code = '';
                $promotion_amount = '';

                if ($coupon !== null && method_exists($coupon, 'get_id')) {
                    $promotion_id = $coupon->get_id();
                }
                if ($coupon !== null && method_exists($coupon, 'get_code')) {
                    $promotion_code = $coupon->get_code();
                }
                if ($coupon !== null && method_exists($coupon, 'get_amount')) {
                    $promotion_amount = $coupon->get_amount();
                }
                
                $promotion_data = [
                    'item_id' => $promotion_id,
                    'item_name' => $promotion_code,
                    'coupon' => $promotion_code,
                    'discount' => $promotion_amount,
                ];

                $promotion_items[] = $promotion_data;
            }
            
            // Register and enqueue script
            wp_register_script('measgaau-view-promotion-tracking', '', array(), '1.0', true);
            wp_enqueue_script('measgaau-view-promotion-tracking');
            
            // Prepare data for JavaScript
            $view_promotion_data = array(
                'event' => 'view_promotion',
                'ecommerce' => array(
                    'promotion_id' => isset($promotion_id) ? $promotion_id : '',
                    'promotion_name' => isset($promotion_code) ? $promotion_code : '',
                    'items' => $promotion_items
                ),
                'user_data' => array(
                    'email_hashed' => $hashed_email,
                    'email' => $email
                )
            );
            
            // Add inline script
            $inline_script = '
                window.dataLayer = window.dataLayer || [];
                dataLayer.push(' . wp_json_encode($view_promotion_data) . ');
            ';
            
            wp_add_inline_script('measgaau-view-promotion-tracking', $inline_script);
        }
    }
}

add_action('wp_footer', 'measgaau_view_promotion');
?>