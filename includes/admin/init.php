<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function measuremate_options_page() {
    add_menu_page(
        'Measuremate - Your Personal GA4 Expert',    // Page title
        'Measuremate',             // Menu title
        'manage_options',            // Capability
        'wc-gtm-settings',           // Menu slug
        'measuremate_options_page_html',  // Callback function
        plugins_url('/images/measuremate-3d-logo.png', __FILE__),       // Icon URL (using a WordPress dashicon)
        25                           // Position
    );
}
add_action('admin_menu', 'measuremate_options_page');

function measuremate_admin_styles() {
    ?>
    <style>
        /* Target the menu icon specifically using the menu class */
        #adminmenu .toplevel_page_wc-gtm-settings .wp-menu-image img {
            width: 20px !important;
            height: 20px !important;
            padding: 6px 0 !important;
            box-sizing: content-box !important;
        }
        
        /* When menu is active/current */
        #adminmenu .toplevel_page_wc-gtm-settings.current .wp-menu-image img,
    </style>
    <?php
}
add_action('admin_head', 'measuremate_admin_styles');

function measuremate_enqueue_admin_styles() {
    // Ensure it's only loaded in the WordPress dashboard.
    if ( is_admin() ) {
        wp_enqueue_style( 'wc-gtm-admin-styles', plugins_url('/css/style.css', __FILE__), array(), '1.0.0' );
        wp_enqueue_style(
            'measuremate-outfit-font',
            'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&display=swap',
            array(),
            '1.0.0'
        );
    }

}
add_action( 'admin_enqueue_scripts', 'measuremate_enqueue_admin_styles' );


function measuremate_options_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    settings_errors('measuremate_messages');

    ?>
    <style>
        body, .wrap, input, select, textarea, h1, h2, h3, h4, h5, h6, p {
            font-family: 'Outfit', Arial, sans-serif !important;
        }

        #adminmenu .toplevel_page_wc-gtm-settings img {
            width: 20px !important;
            height: auto !important;
        }   


        .custom-container {
            margin: 0;
            text-align: center;
        }

        .custom-heading {
            font-weight: 600;
            color: #000;
            line-height: 1.5;
            font-size: 18px;
            margin-bottom: 36px; /* 9 * 4px */
        }

        .custom-heading span {
            display: block;
        }

        .custom-heading .bolder {
            font-weight: 700;
        }

        .custom-heading .smaller {
            font-size: 15px;
        }

        .custom-paragraph {
            text-align: center;
        }

        .custom-button {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
            text-decoration: none;
            margin: 4px;
        }

        .btn-primary {
            color: #fff;
            background-color: #000;
            border-color: #000;
        }

        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
            color: #fff;
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Events table styles */
        .events-table {
            width: 100%;
            border-collapse: collapse;
            background: #f8f8f8;
            margin-top: 20px;
        }

        .events-table td {
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            background: #f8f8f8;
        }

        .events-table label {
            display: flex;
            align-items: center;
            margin: 0;
            cursor: pointer;
        }

        .events-table input[type="checkbox"] {
            margin-right: 8px;
            margin-left: 0;
            accent-color: #000;
        }

        /* Override WordPress default checkbox styles */
        .events-table input[type="checkbox"]:checked::before {
            content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="black" d="M14.83 4.89l1.34.94-5.81 8.38H9.02L5.78 9.67l1.34-1.25 2.57 2.4z"/></svg>');
        }

        /* Main content container width adjustment */
        .main-content-wrapper {
            max-width: 75%;
        }

        /* Logo padding adjustment */
        .measuremate-logo {
            margin-top: 10px !important;
            margin-bottom: 10px !important;
        }

        /* Submit button */
        .wp-core-ui .button-primary {
            background: #000;
            border-color: #000;
            color: #fff;
        }

        .wp-core-ui .button-primary:hover {
            background: #333;
            border-color: #333;
        }
    </style>
<div class="wrap">
    <?php 
    // Fix for line 179: Use wp_get_attachment_image_url if possible, or ensure the path is correct
    $image_url = plugins_url('/images/measuremate-logo.png', __FILE__); 
    ?>
    <img src="<?php echo esc_url($image_url); ?>" alt="Measuremate Logo" class="measuremate-logo" style="width: 250px; height: auto;">


        <div style="display: flex; justify-content: space-between; align-items: stretch;">
    <!-- Main Settings/Events Section -->
    <div class="main-content-wrapper" style="flex: 75%; max-width: 75%; padding-right: 2%;">
        <div class="postbox" style="height: 100%; box-sizing: border-box;">
            <div class="inside">
                <form action="options.php" method="post" id="measuremate-options-form">
                    <?php
                    settings_fields('measuremate'); // This registers nonces etc. for the page
                    
                    // Display GTM settings
                    do_settings_sections('wc-gtm-settings');
                    
                    // Display Events settings
                    do_settings_sections('wc-gtm-settings-events');
                    
                    submit_button('Save Changes');
                    ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Right Sidebar with iframe -->
    <div style="flex: 23%; max-width: 23%;">
        <div class="postbox" style="height: 100%; box-sizing: border-box; margin: 0;">
            <div class="custom-container" style="padding: 0; height: 100%; overflow: hidden;">
                <iframe 
                    src="https://app.themeasuremate.com" 
                    style="width: 100%; height: 100%; border: none; display: block;"
                    title="Measuremate App"
                    frameborder="0"
                    allowfullscreen>
                </iframe>
            </div>
        </div>
    </div>
    </div>
    </div>
    <?php
}


function measuremate_get_defaults() {
    return [
        'view_item' => 1,
        'add_to_cart' => 1,
        'purchase' => 1,
        'view_item_list' => 1,
        'begin_checkout' => 1,
        'view_cart' => 1,
        'refund' => 1,
        'add_to_wishlist' => 1,
        'add_payment_info' => 1,
        'add_shipping_info' => 1,
        'remove_from_cart' => 1,
        'select_item' => 1,
        'view_promotion' => 1,
        'select_promotion' => 1,
        'page_view'=> 1,
        'clicked'=> 1,
        'form_submitted'=> 1,
        'input_blurred'=> 1,
        'input_changed'=> 1,
        'input_focused'=> 1,
        'measuremate_url' => 'https://googletagmanager.com/'
    ];
}

add_filter('default_option_measuremate_options', 'measuremate_get_defaults');

function measuremate_admin_scripts($hook) {
    if ('toplevel_page_wc-gtm-settings' != $hook) {
        return;
    }
    wp_enqueue_script('wc-gtm-admin', plugins_url('/js/admin.js', __FILE__), array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'measuremate_admin_scripts');

function measuremate_admin_notices() {
    if ($error = get_transient('measuremate_settings_error')) {
        echo '<div class="error"><p>' . esc_html($error) . '</p></div>';
        delete_transient('measuremate_settings_error');  // Remove the error now that we've displayed it.
    }
}

add_action('admin_notices', 'measuremate_admin_notices');

function measuremate_code_sanitize($input) {
    if (!empty($input) && strpos($input, 'GTM-') !== 0) { // If the input doesn't start with 'GTM-'
        set_transient('measuremate_settings_error', 'The GTM Code must start with "GTM-".', 45);
        return get_option('measuremate_code'); // Return the old value
    }
    return sanitize_text_field($input);
}

function measuremate_events_sanitize($input) {
    return $input;
}

function measuremate_options_sanitize($input) {
    $sanitized = array();
    
    // Define all possible checkbox fields
    $checkbox_fields = array(
        'view_item', 'add_to_cart', 'purchase', 'view_item_list',
        'begin_checkout', 'view_cart', 'refund', 'add_to_wishlist',
        'add_payment_info', 'add_shipping_info', 'remove_from_cart',
        'select_item', 'view_promotion', 'select_promotion',
        'page_view', 'clicked', 'form_submitted', 'input_blurred',
        'input_changed', 'input_focused'
    );
    
    // Sanitize checkboxes
    foreach ($checkbox_fields as $field) {
        $sanitized[$field] = isset($input[$field]) ? 1 : 0;
    }
    
    // Sanitize other fields
    $sanitized['measuremate_url'] = 'https://googletagmanager.com/';
    $sanitized['measuremate_url_toggle'] = isset($input['measuremate_url_toggle']) ? sanitize_text_field($input['measuremate_url_toggle']) : '';
    $sanitized['enhanced_tracking_v2'] = isset($input['enhanced_tracking_v2']) ? 1 : 0;
    $sanitized['enhanced_tracking_v2_container_id'] = isset($input['enhanced_tracking_v2_container_id']) ? sanitize_text_field($input['enhanced_tracking_v2_container_id']) : '';
    
    // Reset enhanced tracking if no container ID
    if (!empty($sanitized['enhanced_tracking_v2']) && empty($sanitized['enhanced_tracking_v2_container_id'])) {
        $sanitized['enhanced_tracking_v2'] = 0;
    }
    
    return $sanitized;
}


function measuremate_success_message($old_value, $value, $option) {
    if ($old_value !== $value) { // Only show if the value has changed.
        add_settings_error('measuremate_messages', 'measuremate_message', 'Settings saved', 'updated');
    }
}
add_action('update_option_measuremate_code', 'measuremate_success_message', 10, 3);
add_action('update_option_measuremate_url', 'measuremate_success_message', 10, 3);
add_action('update_option_measuremate_options', 'measuremate_success_message', 10, 3);

function measuremate_section_gtm_cb($args) {
    echo esc_html('Enter your Google Tag Manager settings below:');
}

function measuremate_code_cb($args) {
    $gtm_code = get_option('measuremate_code');
    echo '<input name="measuremate_code" id="measuremate_code" type="text" value="' . esc_attr($gtm_code) . '" class="regular-text">';
    // echo '<p class="description">You can fill in your Google Tag Manager web container ID</p>';
}

function measuremate_url_cb($args) {
    $gtm_url = get_option('measuremate_url');
    echo ( '<input name="measuremate_url" id="measuremate_url" type="text" value="' . esc_attr($gtm_url) . '" class="regular-text">' );
}

function measuremate_section_callback($args) {
    echo ( '<p class="description">✅ Please Enable/Disable the required DataLayer Events from the list below. 👉 Click SAVE when finished.</p>' );
    
    // Start the events table
    $options = get_option('measuremate_options');
    $events = [
        'view_item' => 'View Item',
        'add_to_cart' => 'Add to Cart',
        'purchase' => 'Purchase',
        'view_cart' => 'View Cart',
        'view_item_list' => 'View Item List',
        'begin_checkout' => 'Begin Checkout',
        'refund' => 'Refund',
        'add_to_wishlist' => 'Add to Wishlist',
        'add_payment_info' => 'Add Payment Info',
        'add_shipping_info' => 'Add Shipping Info',
        'remove_from_cart' => 'Remove from Cart',
        'select_item' => 'Select Item',
        'view_promotion' => 'View Promotion',
        'select_promotion' => 'Select Promotion',
        'page_view' => 'Page View',
        'clicked' => 'Clicked',
        'form_submitted' => 'Form Submitted',
        'input_blurred' => 'Input Blurred',
        'input_changed' => 'Input Changed',
        'input_focused' => 'Input Focused',
    ];
    
    echo '<table class="events-table">';
    
    $count = 0;
    foreach ($events as $event_key => $event_label) {
        if ($count % 4 == 0) {
            echo '<tr>';
        }
        
        $checked = isset($options[$event_key]) ? checked($options[$event_key], 1, false) : '';
        
        echo '<td>';
        echo '<label for="measuremate_field_' . esc_attr($event_key) . '">';
        echo '<input name="measuremate_options[' . esc_attr($event_key) . ']" type="checkbox" id="measuremate_field_' . esc_attr($event_key) . '" value="1" ' . esc_attr($checked) . '>';
        echo esc_html($event_label);
        echo '</label>';
        echo '</td>';
        
        $count++;
        if ($count % 4 == 0) {
            echo '</tr>';
        }
    }
    
    // Close the last row if needed
    if ($count % 4 != 0) {
        while ($count % 4 != 0) {
            echo '<td></td>';
            $count++;
        }
        echo '</tr>';
    }
    
    echo '</table>';
}

function measuremate_field_callback($args) {
    // This function is no longer needed since we're handling everything in measuremate_section_callback
    return;
}

function measuremate_url_toggle_cb() {
    $options = get_option('measuremate_options', array());
    $value = isset($options['measuremate_url_toggle']) ? $options['measuremate_url_toggle'] : '';
    echo ( '<input type="text" id="measuremate_url_toggle" name="measuremate_options[measuremate_url_toggle]" style="width:350px; " value="' . esc_attr($value) . '" />' );
    echo ( '<p class="description">Read <a href="https://app.themeasuremate.com">this article</a> to find out how to use the Enhanced Tracking Script</p>' );
    echo ( '<p class="description"><i>If you do not want to use the Enhanced Tracking Script, leave this field empty</i></p>' );
}

function measuremate_enhanced_tracking_v2_cb($args) {
    $options = get_option('measuremate_options');
    $disabled = !isset($options['measuremate_url_toggle']) || $options['measuremate_url_toggle'] == '';
    $v2_active = isset($options['enhanced_tracking_v2']) ? checked($options['enhanced_tracking_v2'], 1, false) : '';
    $container_id = isset($options['enhanced_tracking_v2_container_id']) ? $options['enhanced_tracking_v2_container_id'] : '';

    echo '<div id="enhanced_tracking_v2_section" style="' . ($disabled ? 'opacity: 0.7;' : '') . '">';
    
    // Toggle
    echo "<div style='display:flex; gap: 6px;'>";
    echo ("<input style='margin-top: 7px;' name='measuremate_options[enhanced_tracking_v2]' " . ($disabled ? 'disabled' : '') . " type='checkbox' id='measuremate_enhanced_tracking_v2' value='1' " . esc_attr($v2_active) . ">");
    echo '<p class="description"><b>Enable</b></p>';
    echo "</div>";

    echo '<p class="description" style="margin-top: 10px;"><b>Measuremate Container Identifier</b></p>';
    echo '<input type="text" id="enhanced_tracking_v2_container_id" name="measuremate_options[enhanced_tracking_v2_container_id]" ' . ($disabled ? 'disabled' : "") . ' style="width:350px;" value="' . esc_attr($container_id) . '" />';
    
    echo '</div>';
    // echo '<p class="description"><i>The Enhanced Tracking Script v2 can only be used when you have entered a subdomain for the Enhanced Tracking Script.</i></p>';
}


function measuremate_settings_init() {
    // Register the GTM code setting.
    register_setting('measuremate', 'measuremate_code', array('sanitize_callback' => 'measuremate_code_sanitize'));

    // Add section for GTM
    add_settings_section(
        'measuremate_section_gtm',
        'Google Tag Manager Settings',
        'measuremate_section_gtm_cb',
        'wc-gtm-settings'
    );

    // Add field to input GTM code
    add_settings_field(
        'measuremate_code',
        'GTM Container Id',
        'measuremate_code_cb',
        'wc-gtm-settings',
        'measuremate_section_gtm'
    );

    register_setting('measuremate', 'measuremate_url', array('sanitize_callback' => 'measuremate_options_sanitize'));

    // Register a new setting for our options page for the events.
    register_setting('measuremate', 'measuremate_options', array('sanitize_callback' => 'measuremate_options_sanitize'));

    // Add a new section to our options page for the events.
    add_settings_section(
        'measuremate_section_events',       
        'Select DataLayer Events to Track',
        'measuremate_section_callback',
        'wc-gtm-settings-events'      
    );

    // We don't need to add individual fields anymore since we're handling everything in measuremate_section_callback
    // But WordPress still expects them for the settings API to work properly, so we add dummy fields
    $events = [
        'view_item',
        'add_to_cart',
        'purchase',
        'view_cart',
        'view_item_list',
        'begin_checkout',
        'refund',
        'add_to_wishlist',
        'add_payment_info',
        'add_shipping_info',
        'remove_from_cart',
        'select_item',
        'view_promotion',
        'select_promotion',
        'page_view',
        'clicked',
        'form_submitted',
        'input_blurred',
        'input_changed',
        'input_focused',
    ];

    // foreach ($events as $event) {
    //     add_settings_field(
    //         'measuremate_field_' . $event,
    //         ucfirst(str_replace('_', ' ', $event)),
    //         'measuremate_field_callback',
    //         'wc-gtm-settings-events',   
    //         'measuremate_section_events',    
    //         [
    //             'label_for' => 'measuremate_field_' . $event,
    //             'event_name' => $event
    //         ]
    //     );
    // }
}


add_action('admin_init', 'measuremate_settings_init');


?>