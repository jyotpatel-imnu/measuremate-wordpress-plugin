<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measgaau_remove_from_cart_event($cart_item_key, $instance)
{
    // Check if instance is valid and has get_cart_item method
    if (!is_object($instance) || !method_exists($instance, 'get_cart_item')) {
        return;
    }

    $cart_item = $instance->get_cart_item($cart_item_key);
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (!$cart_item || !isset($cart_item['product_id'])) {
        return;
    }

    $product = wc_get_product($cart_item['product_id']);

    if (!$product) {
        return;
    }

    $item = measgaau_format_item($product->get_id(), $cart_item['quantity']);

    $measgaau_event_data = array(
        'event'     => 'remove_from_cart',
        'ecommerce' => array(
            'currency' => get_woocommerce_currency(),
            'value' => floatval($product->get_price()),
            'items'    => array($item),
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );

    // Fixed: Register and enqueue the correct script handle
    wp_register_script('ga4-remove-from-cart', false, array(), '1.0.0', true);
    wp_enqueue_script('ga4-remove-from-cart');
    wp_add_inline_script('ga4-remove-from-cart', 'window.ga4RemoveFromCartData = ' . wp_json_encode($measgaau_event_data) . ';', 'before');
}
add_action('woocommerce_cart_item_removed', 'measgaau_remove_from_cart_event', 10, 2);

function measgaau_print_remove_from_cart_script()
{
    if (!wp_script_is('ga4-remove-from-cart', 'enqueued')) {
        return;
    }

    $options = get_option('measgaau_options');
    if (isset($options['remove_from_cart']) && $options['remove_from_cart']) {
        $script = '
            if (window.ga4RemoveFromCartData) {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push(window.ga4RemoveFromCartData);
                console.log("GA4 remove_from_cart event pushed:", window.ga4RemoveFromCartData);
            }
        ';
        wp_add_inline_script('ga4-remove-from-cart', $script);
    }
}
add_action('wp_footer', 'measgaau_print_remove_from_cart_script');

function measgaau_enqueue_ajax_remove_from_cart_script()
{
    // Only enqueue on cart page or where cart fragments are used
    if (!is_cart() && !is_shop() && !is_product_category() && !is_product()) {
        return;
    }

    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }
    
    $options = get_option('measgaau_options');
    if (isset($options['remove_from_cart']) && $options['remove_from_cart']) {
        // Register and enqueue a script handle
        wp_register_script('measgaau-remove-from-cart-ajax', '', array('jquery'), '1.0', true);
        wp_enqueue_script('measgaau-remove-from-cart-ajax');
        
        // Localize script to pass Ajax URL and nonce
        wp_localize_script('measgaau-remove-from-cart-ajax', 'measgaau_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('measgaau_product_details_nonce'),
            'email' => esc_js($email),
            'hashed_email' => esc_js($hashed_email)
        ));
        
        // Improved inline script with better event handling
        $inline_script = "
            jQuery(document).ready(function($) {
                // Handle both direct clicks and dynamically loaded content
                $(document).on('click', 'a.remove, .woocommerce-cart-form .remove', function(e) {
                    var \$this = $(this);
                    var product_id = \$this.data('product_id');
                    
                    // Try different ways to get product ID
                    if (!product_id) {
                        // Try getting from href
                        var href = \$this.attr('href');
                        if (href) {
                            var matches = href.match(/remove_item=([^&]+)/);
                            if (matches) {
                                // This is the cart item key, we need to get product_id differently
                                var cart_item_key = matches[1];
                                // Look for product_id in the row
                                var \$row = \$this.closest('tr');
                                if (\$row.length) {
                                    product_id = \$row.find('[data-product_id]').data('product_id');
                                }
                            }
                        }
                    }
                    
                    if (!product_id) {
                        console.log('Product ID not found for remove from cart event');
                        return;
                    }

                    $.ajax({
                        url: measgaau_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'measgaau_get_product_details',
                            product_id: product_id,
                            nonce: measgaau_ajax.nonce
                        },
                        success: function(response) {
                            if (response && response.success) {
                                window.dataLayer = window.dataLayer || [];
                                window.dataLayer.push({
                                    'event': 'remove_from_cart',
                                    'ecommerce': response.data,
                                    'user_data': {
                                        'email': measgaau_ajax.email,
                                        'hashed_email': measgaau_ajax.hashed_email,
                                    }
                                });
                                console.log('GA4 remove_from_cart event pushed via AJAX:', response.data);
                            } else {
                                console.error('Failed to get product details:', response.data);
                            }
                        },
                        error: function() {
                            console.error('AJAX request failed.');
                        }
                    });
                });
                
                // Also handle cart updates via AJAX
                $(document.body).on('updated_wc_div', function() {
                    console.log('Cart updated - remove from cart events may have been processed');
                });
            });
        ";
        
        wp_add_inline_script('measgaau-remove-from-cart-ajax', $inline_script);
    }
}
add_action('wp_enqueue_scripts', 'measgaau_enqueue_ajax_remove_from_cart_script');

function measgaau_get_product_details_callback()
{
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'measgaau_product_details_nonce')) {
        wp_send_json_error('Security check failed');
    }

    // Validate and sanitize the product ID
    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        wp_send_json_error('Invalid product ID');
    }

    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error('Product not found');
    }

    $categories = wp_get_post_terms($product_id, 'product_cat');
    $category_names = array();
    if (is_array($categories) && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }
    }
    $category_list = implode(', ', $category_names);

    // Construct and return the product details
    wp_send_json_success(array(
        'currency' => get_woocommerce_currency(),
        'value' => floatval($product->get_price()),
        'items' => array(array(
            'item_id' => $product->get_id(),
            'item_name' => $product->get_name(),
            'item_category' => $category_list,
            'quantity' => 1,
            'price' => floatval($product->get_price()),
        ))
    ));
}
add_action('wp_ajax_measgaau_get_product_details', 'measgaau_get_product_details_callback');
add_action('wp_ajax_nopriv_measgaau_get_product_details', 'measgaau_get_product_details_callback');

// Alternative approach: Hook into cart fragments update
function measgaau_handle_cart_fragments_remove($fragments) {
    if (isset($_POST['remove_from_cart'])) {
        $cart_item_key = sanitize_text_field($_POST['remove_from_cart']);
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        
        if ($cart_item && isset($cart_item['product_id'])) {
            $product = wc_get_product($cart_item['product_id']);
            if ($product) {
                $current_user = wp_get_current_user();
                $hashed_email = '';
                $email = '';
                if ($current_user->exists()) {
                    $hashed_email = hash('sha256', $current_user->user_email);
                    $email = $current_user->user_email;
                }
                
                $categories = wp_get_post_terms($product->get_id(), 'product_cat');
                $category_names = array();
                if (is_array($categories) && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $category_names[] = $category->name;
                    }
                }
                $category_list = implode(', ', $category_names);
                
                $event_data = array(
                    'event' => 'remove_from_cart',
                    'ecommerce' => array(
                        'currency' => get_woocommerce_currency(),
                        'value' => floatval($product->get_price()) * $cart_item['quantity'],
                        'items' => array(array(
                            'item_id' => $product->get_id(),
                            'item_name' => $product->get_name(),
                            'item_category' => $category_list,
                            'quantity' => $cart_item['quantity'],
                            'price' => floatval($product->get_price()),
                        ))
                    ),
                    'user_data' => array(
                        'email_hashed' => $hashed_email,
                        'email' => $email
                    )
                );
                
                // Add script to fragments
                $fragments['ga4_remove_from_cart'] = '<script>
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push(' . wp_json_encode($event_data) . ');
                    console.log("GA4 remove_from_cart event pushed via fragments:", ' . wp_json_encode($event_data) . ');
                </script>';
            }
        }
    }
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'measgaau_handle_cart_fragments_remove');

?>