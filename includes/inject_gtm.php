<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// All datalayer injections
function measuremate_inject_gtm_script()
{
    $gtm_code = get_option('measuremate_code', '');
    
    if (empty($gtm_code)) {
        return;
    }

    $gtm_options = get_option('measuremate_options', array());

    // Check if the option isn't an array or if it doesn't contain the expected key.
    if (!is_array($gtm_options) || !isset($gtm_options['measuremate_url_toggle']) || $gtm_options['measuremate_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com'; // Default value
    } else {
        $gtm_url = $gtm_options['measuremate_url_toggle'];
    }

    if (!preg_match('/^https?:\/\//', $gtm_url)) {
        $gtm_url = 'https://' . $gtm_url;
    }
    $gtm_url = rtrim($gtm_url, '/');

    $parameter = "id";
    if (isset($gtm_options['enhanced_tracking_v2']) && $gtm_options['enhanced_tracking_v2']) {
        $container_id = $gtm_options['enhanced_tracking_v2_container_id'];
        $gtm_url = $gtm_url . "/$container_id.js";
        $gtm_code = str_replace('GTM-', '', $gtm_code);
        $parameter = "tg";
    } else {
        $gtm_url = $gtm_url . '/gtm.js';
    }

    ?>
    <script>
    (function(w,d,s,l,i){
        w[l]=w[l]||[];
        w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),
            dl=l!='dataLayer'?'&l='+l:'',
            parameter = '<?php echo esc_js($parameter); ?>',
            gtm_url = '<?php echo esc_url($gtm_url); ?>',
            gtm_code = '<?php echo esc_js($gtm_code); ?>';
        j.async=true;
        j.src = gtm_url + '?' + parameter + '=' + gtm_code + dl;
        f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js($gtm_code); ?>');
    </script>
    <?php
}
add_action('wp_head', 'measuremate_inject_gtm_script');


function measuremate_inject_gtm_noscript()
{
    $gtm_code = get_option('measuremate_code', '');

    if (empty($gtm_code)) {
        return;
    }

    $gtm_options = get_option('measuremate_options', array());
    if (!is_array($gtm_options) || !isset($gtm_options['measuremate_url_toggle']) || $gtm_options['measuremate_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com'; // Default value
    } else {
        $gtm_url = $gtm_options['measuremate_url_toggle'];
    }

    if (!preg_match('/^https?:\/\//', $gtm_url)) {
        $gtm_url = 'https://' . $gtm_url;
    }
    $gtm_url = rtrim($gtm_url, '/');

    $parameter = "id";
    if (isset($gtm_options['enhanced_tracking_v2']) && $gtm_options['enhanced_tracking_v2']) {
        $container_id = $gtm_options['enhanced_tracking_v2_container_id'];
        $gtm_url = $gtm_url . "/$container_id.html";
        $gtm_code = str_replace('GTM-', '', $gtm_code);
        $parameter = "tg";
    } else {
        $gtm_url = $gtm_url . '/ns.html';
    }

    ?>
    <noscript>
        <iframe src="<?php echo esc_url($gtm_url) . '?' . esc_attr($parameter) . '=' . esc_attr($gtm_code); ?>"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <?php
}
// If your theme supports the 'wp_body_open' action (introduced in WP 5.2), you can use that.
add_action('wp_body_open', 'measuremate_inject_gtm_noscript');
