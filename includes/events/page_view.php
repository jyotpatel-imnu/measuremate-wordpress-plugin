<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measuremate_page_view_event()
{
    $options = get_option('measuremate_options');
    if (!isset($options['page_view']) || !$options['page_view']) {
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measuremate_hash_email($email) : '';

    $event_data = array(
        'event' => 'page_view',
        'page_title' => get_the_title(),
        'page_location' => get_permalink(),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );

    // Store data in cookie instead of inline script
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('measuremate_page_view_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('wp', 'measuremate_page_view_event');

function measuremate_print_page_view_script()
{
    $options = get_option('measuremate_options');
    if (!isset($options['page_view']) || !$options['page_view']) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            function pushPageViewData() {
                var cookieValue = document.cookie.split('; ').find(row => row.startsWith('measuremate_page_view_data='));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split('=')[1])));
                        console.log('Page View:', data);
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "measuremate_page_view_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=<?php echo esc_js(COOKIEPATH); ?>; domain=<?php echo esc_js(COOKIE_DOMAIN); ?>";
                    } catch(e) {
                        console.error('Page view data error:', e);
                    }
                }
            }
            
            // Check on load
            pushPageViewData();
        });
    </script>
    <?php
}
add_action('wp_footer', 'measuremate_print_page_view_script');

?>