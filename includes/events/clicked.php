<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_clicked_event_ajax_handler()
{
    $options = get_option('measgaau_options');
    if (!isset($options['clicked']) || !$options['clicked']) {
        wp_die();
    }
    
    // Verify nonce for security - unslash nonce first
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'measgaau_clicked_nonce')) {
        wp_die('Security check failed');
    }
    
    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? measgaau_hash_email($email) : '';
    
    // Unslash POST data before sanitization
    $element_type = isset($_POST['element_type']) ? sanitize_text_field(wp_unslash($_POST['element_type'])) : '';
    $element_text = isset($_POST['element_text']) ? substr(sanitize_text_field(wp_unslash($_POST['element_text'])), 0, 100) : '';
    $element_href = isset($_POST['element_href']) ? esc_url_raw(wp_unslash($_POST['element_href'])) : '';
    $page_title = isset($_POST['page_title']) ? sanitize_text_field(wp_unslash($_POST['page_title'])) : '';
    $page_location = isset($_POST['page_location']) ? esc_url_raw(wp_unslash($_POST['page_location'])) : '';
    
    $event_data = array(
        'event' => 'clicked',
        'click_data' => array(
            'element_type' => $element_type,
            'element_text' => $element_text,
            'element_href' => $element_href,
            'page_title' => $page_title,
            'page_location' => $page_location
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );
    
    // Add GTM event ID
    $gtm_event_id = isset($_POST['gtm_unique_event_id']) ? absint(wp_unslash($_POST['gtm_unique_event_id'])) : time();
    $event_data['gtm.uniqueEventId'] = $gtm_event_id;
    
    // Add product data if available
    $product_id_raw = isset($_POST['product_id']) ? sanitize_text_field(wp_unslash($_POST['product_id'])) : '';
    if (!empty($product_id_raw)) {
        $product_id = absint($product_id_raw);
        $product = wc_get_product($product_id);
        if ($product) {
            $event_data['ecommerce'] = array(
                'items' => array(measgaau_format_item($product_id, 1))
            );
        }
    }
    
    // Store data in cookie
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('measgaau_clicked_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
    
    wp_send_json_success();
}
add_action('wp_ajax_measgaau_clicked_event', 'measgaau_clicked_event_ajax_handler');
add_action('wp_ajax_nopriv_measgaau_clicked_event', 'measgaau_clicked_event_ajax_handler');

function measgaau_enqueue_clicked_script()
{
    $options = get_option('measgaau_options');
    if (!isset($options['clicked']) || !$options['clicked']) {
        return;
    }
    
    // Register and enqueue script
    wp_register_script('measgaau-clicked-tracking', '', array('jquery'), '1.0', true);
    wp_enqueue_script('measgaau-clicked-tracking');
    
    // Localize script to pass Ajax URL and nonce
    wp_localize_script('measgaau-clicked-tracking', 'measgaau_clicked_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('measgaau_clicked_nonce'),
        'cookie_path' => COOKIEPATH,
        'cookie_domain' => COOKIE_DOMAIN
    ));
    
    // Add inline script
    $inline_script = '
        jQuery(document).ready(function($) {
            var ajaxurl = measgaau_clicked_ajax.ajax_url;
            var nonce = measgaau_clicked_ajax.nonce;
            
            function pushClickedData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("measgaau_clicked_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        console.log("Clicked:", data);
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "measgaau_clicked_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + measgaau_clicked_ajax.cookie_path + "; domain=" + measgaau_clicked_ajax.cookie_domain;
                    } catch(e) {
                        console.error("Clicked data error:", e);
                    }
                }
            }
            
            // Track clicks
            $(document).on("click", "a, button, input[type=\"submit\"], input[type=\"button\"], [role=\"button\"], .clickable", function(e) {
                var $element = $(this);
                
                // Get element text - try multiple methods
                var elementText = "";
                if ($element.attr("title")) {
                    elementText = $element.attr("title");
                } else if ($element.attr("aria-label")) {
                    elementText = $element.attr("aria-label");
                } else if ($element.val()) {
                    elementText = $element.val();
                } else if ($element.text().trim()) {
                    elementText = $element.text().trim();
                } else if ($element.find("img").attr("alt")) {
                    elementText = $element.find("img").attr("alt");
                }
                
                // Get all classes as string
                var elementClasses = $element.attr("class") || "";
                
                var clickData = {
                    action: "measgaau_clicked_event",
                    nonce: nonce,
                    element_type: this.tagName ? this.tagName.toLowerCase() : "unknown",
                    element_text: elementText.substring(0, 100),
                    element_href: $element.attr("href") || $element.closest("a").attr("href") || "",
                    page_title: document.title || "",
                    page_location: window.location.href,
                    gtm_unique_event_id: Date.now()
                };
                
                // Check for WooCommerce product - multiple methods
                var productId = 0;
                
                // Method 1: Direct data attribute
                if ($element.data("product_id")) {
                    productId = $element.data("product_id");
                }
                // Method 2: Parent element data attribute
                else if ($element.closest("[data-product_id]").length) {
                    productId = $element.closest("[data-product_id]").data("product_id");
                }
                // Method 3: Form input
                else if ($element.closest("form").find("[name=\"add-to-cart\"]").length) {
                    productId = $element.closest("form").find("[name=\"add-to-cart\"]").val();
                }
                // Method 4: Product wrapper
                else if ($element.closest(".product").length) {
                    var $product = $element.closest(".product");
                    productId = $product.find(".add_to_cart_button").data("product_id") || 
                               $product.find("[data-product_id]").data("product_id") ||
                               $product.attr("data-product_id");
                }
                
                if (productId) {
                    clickData.product_id = parseInt(productId);
                }
                
                // Send AJAX request
                $.post(ajaxurl, clickData, function(response) {
                    if (response.success) {
                        setTimeout(pushClickedData, 100);
                    }
                });
            });
            
            // Check for existing clicked data on page load
            pushClickedData();
        });
    ';
    
    wp_add_inline_script('measgaau-clicked-tracking', $inline_script);
}
add_action('wp_footer', 'measgaau_enqueue_clicked_script');
?>