<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measuremate_input_focused_ajax_handler()
{
    $options = get_option('measuremate_options');
    if (!isset($options['input_focused']) || !$options['input_focused']) {
        wp_die();
    }
    
    // Verify nonce for security - unslash and sanitize first
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'measuremate_input_focused_nonce')) {
        wp_die('Security check failed');
    }
    
    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measuremate_hash_email($email) : '';
    
    // Unslash and sanitize all POST data
    $input_type = isset($_POST['input_type']) ? sanitize_text_field(wp_unslash($_POST['input_type'])) : '';
    $input_id = isset($_POST['input_id']) ? sanitize_text_field(wp_unslash($_POST['input_id'])) : '';
    $input_label = isset($_POST['input_label']) ? sanitize_text_field(wp_unslash($_POST['input_label'])) : '';
    $page_title = isset($_POST['page_title']) ? sanitize_text_field(wp_unslash($_POST['page_title'])) : '';
    $page_location = isset($_POST['page_location']) ? esc_url_raw(wp_unslash($_POST['page_location'])) : '';
    $gtm_unique_event_id = isset($_POST['gtm_unique_event_id']) ? absint(wp_unslash($_POST['gtm_unique_event_id'])) : time();
    
    $event_data = array(
        'event' => 'input_focused',
        'input_data' => array(
            'input_type' => $input_type,
            'input_id' => $input_id,
            'input_label' => $input_label,
            'page_title' => $page_title,
            'page_location' => $page_location
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        ),
        'gtm.uniqueEventId' => $gtm_unique_event_id
    );
    
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('measuremate_input_focused_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
    
    wp_send_json_success();
}
add_action('wp_ajax_measuremate_input_focused', 'measuremate_input_focused_ajax_handler');
add_action('wp_ajax_nopriv_measuremate_input_focused', 'measuremate_input_focused_ajax_handler');

function measuremate_print_input_focused_script()
{
    $options = get_option('measuremate_options');
    if (!isset($options['input_focused']) || !$options['input_focused']) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            var ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
            var nonce = '<?php echo esc_js(wp_create_nonce('measuremate_input_focused_nonce')); ?>';
            
            function pushInputFocusedData() {
                var cookieValue = document.cookie.split('; ').find(row => row.startsWith('measuremate_input_focused_data='));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split('=')[1])));
                        console.log('Input Focused:', data);
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        document.cookie = "measuremate_input_focused_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=<?php echo esc_js(COOKIEPATH); ?>; domain=<?php echo esc_js(COOKIE_DOMAIN); ?>";
                    } catch(e) {
                        console.error('Input focused data error:', e);
                    }
                }
            }
            
            function getInputLabel($input) {
                var label = '';
                
                if ($input.attr('id')) {
                    label = $('label[for="' + $input.attr('id') + '"]').text().trim();
                }
                
                if (!label) {
                    label = $input.closest('label').text().trim();
                }
                
                if (!label) {
                    label = $input.prev('label').text().trim();
                }
                
                if (!label) {
                    label = $input.attr('placeholder') || '';
                }
                
                return label.substring(0, 100);
            }
            
            $(document).on('focus', 'input:not([type="submit"]):not([type="button"]), textarea, select', function(e) {
                var $input = $(this);
                
                var inputData = {
                    action: 'measuremate_input_focused',
                    nonce: nonce,
                    input_type: $input.prop('tagName').toLowerCase() === 'select' ? 'select' : ($input.attr('type') || 'text'),
                    input_id: $input.attr('id') || '',                
                    input_label: getInputLabel($input),
                    page_title: document.title || '',
                    page_location: window.location.href,
                    gtm_unique_event_id: Date.now()
                };
                
                $.post(ajaxurl, inputData, function(response) {
                    if (response.success) {
                        setTimeout(pushInputFocusedData, 100);
                    }
                });
            });
            
            pushInputFocusedData();
        });
    </script>
    <?php
}
add_action('wp_footer', 'measuremate_print_input_focused_script');
?>