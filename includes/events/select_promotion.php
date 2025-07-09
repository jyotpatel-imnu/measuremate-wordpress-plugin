<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measuremate_select_promotion_event()
{
    $options = get_option('measuremate_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measuremate_hash_email($email) : '';
   
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
    setcookie('measuremate_promotion_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_applied_coupon', 'measuremate_select_promotion_event');


function measuremate_print_promotion_script()
{
    $options = get_option('measuremate_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            function pushPromotionData() {
                var cookieValue = document.cookie.split('; ').find(row => row.startsWith('measuremate_promotion_data='));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split('=')[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "measuremate_promotion_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=<?php echo esc_js(COOKIEPATH); ?>; domain=<?php echo esc_js(COOKIE_DOMAIN); ?>";
                    } catch(e) {
                        console.error('Promotion data error:', e);
                    }
                }
            }
            
            // Check on load (for non AJAX calls)
            pushPromotionData();
            
            // Check after updates (for AJAX calls)
            $(document.body).on('updated_wc_div applied_coupon fkcart_fragments_refreshed', function() {
                setTimeout(pushPromotionData, 500);
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'measuremate_print_promotion_script');
?>