<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_form_submitted_ajax_handler()
{
    $options = get_option('measgaau_options');
    if (!isset($options['form_submitted']) || !$options['form_submitted']) {
        wp_die();
    }
    
    // Verify nonce for security - unslash and sanitize first
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'measgaau_form_submitted_nonce')) {
        wp_die('Security check failed');
    }
    
    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measgaau_hash_email($email) : '';
    
    // Unslash and sanitize all POST data
    $form_id = isset($_POST['form_id']) ? sanitize_text_field(wp_unslash($_POST['form_id'])) : '';
    $form_name = isset($_POST['form_name']) ? sanitize_text_field(wp_unslash($_POST['form_name'])) : '';
    $form_classes = isset($_POST['form_classes']) ? sanitize_text_field(wp_unslash($_POST['form_classes'])) : '';
    $form_action = isset($_POST['form_action']) ? esc_url_raw(wp_unslash($_POST['form_action'])) : '';
    $form_method = isset($_POST['form_method']) ? sanitize_text_field(wp_unslash($_POST['form_method'])) : 'post';
    $page_title = isset($_POST['page_title']) ? sanitize_text_field(wp_unslash($_POST['page_title'])) : '';
    $page_location = isset($_POST['page_location']) ? esc_url_raw(wp_unslash($_POST['page_location'])) : '';
    $gtm_unique_event_id = isset($_POST['gtm_unique_event_id']) ? absint(wp_unslash($_POST['gtm_unique_event_id'])) : time();
    
    $event_data = array(
        'event' => 'form_submitted',
        'form_data' => array(
            'form_id' => $form_id,
            'form_name' => $form_name,
            'form_classes' => $form_classes,
            'form_action' => $form_action,
            'form_method' => $form_method,
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
    setcookie('measgaau_form_submitted_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
    
    wp_send_json_success();
}
add_action('wp_ajax_measgaau_form_submitted', 'measgaau_form_submitted_ajax_handler');
add_action('wp_ajax_nopriv_measgaau_form_submitted', 'measgaau_form_submitted_ajax_handler');

function measgaau_enqueue_form_submitted_script()
{
    $options = get_option('measgaau_options');
    if (!isset($options['form_submitted']) || !$options['form_submitted']) {
        return;
    }
    
    // Register and enqueue script
    wp_register_script('measgaau-form-submitted', '', array('jquery'), '1.0', true);
    wp_enqueue_script('measgaau-form-submitted');
    
    // Localize script
    wp_localize_script('measgaau-form-submitted', 'measgaau_form_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('measgaau_form_submitted_nonce'),
        'cookie_path' => COOKIEPATH,
        'cookie_domain' => COOKIE_DOMAIN
    ));
    
    // Add inline script
    $inline_script = '
jQuery(document).ready(function($) {
    var ajaxurl = measgaau_form_ajax.ajax_url;
    var nonce = measgaau_form_ajax.nonce;
    
    var lastClickedButton = null;

    // Track the clicked submit button
    $(document).on("click", "form button[type=\"submit\"], form input[type=\"submit\"]", function(e) {
        lastClickedButton = $(this);
    });

    function pushFormSubmittedData() {
        var cookieValue = document.cookie.split("; ").find(row => row.startsWith("measgaau_form_submitted_data="));
        if (cookieValue) {
            try {
                var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                console.log("Form Submitted:", data);
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push(data);
                
                document.cookie = "measgaau_form_submitted_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + measgaau_form_ajax.cookie_path + "; domain=" + measgaau_form_ajax.cookie_domain;
            } catch(e) {
                console.error("Form submitted data error:", e);
            }
        }
    }

    $(document).on("submit", "form", function(e) {
        var $form = $(this);

        if ($form.hasClass("woocommerce-cart-form") || $form.hasClass("checkout")) {
            return;
        }

        var formName = $form.attr("name") || "";
        if (!formName && lastClickedButton) {
            formName = lastClickedButton.attr("name") || lastClickedButton.text() || "";
        }

        var formClass = $form.attr("class") || "";
        if (!formClass && lastClickedButton) {
            formClass = lastClickedButton.attr("class") || lastClickedButton.text() || "";
        }

        var formData = {
            action: "measgaau_form_submitted",
            nonce: nonce,
            form_id: $form.attr("id") || "",
            form_name: formName,
            form_classes: $form.attr("class") || formClass,
            form_action: $form.attr("action") || "",
            form_method: $form.attr("method") || "post",
            page_title: document.title || "",
            page_location: window.location.href,
            gtm_unique_event_id: Date.now()
        };

        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                setTimeout(pushFormSubmittedData, 100);
            }
        });
    });

    pushFormSubmittedData();
});
    ';
    
    wp_add_inline_script('measgaau-form-submitted', $inline_script);
}
add_action('wp_footer', 'measgaau_enqueue_form_submitted_script');
?>