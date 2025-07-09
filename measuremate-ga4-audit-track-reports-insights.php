<?php
/*
Plugin Name: Measuremate – GA4 Audit, Track, Reports & Insights
Plugin URI: https://github.com/jyotpatel-imnu/measuremate-wordpress-plugin
Description: Measuremate is your all-in-one Google Analytics™ 4 (GA4) expert. Audit, track tags, auto-push GTM/GA4, validate events, export data, get insights instantly.
Version: 1.1.6
Author: JubatusAI Labs Pvt Ltd.
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.5
Tested up to: 6.8
Stable tag: 1.1.6
Requires PHP: 7.2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function measuremate_enqueue_jquery() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'measuremate_enqueue_jquery');

// Constant plugin path
define('MEASUREMATE_PLUGIN_PATH', plugin_dir_url( __FILE__ ));

// admin Page
require_once plugin_dir_path(__FILE__) . 'includes/admin/init.php';

// inject gtm codes
require_once plugin_dir_path(__FILE__) . 'includes/inject_gtm.php';

// load functions
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// load events
require_once plugin_dir_path(__FILE__) . 'includes/events/view_item.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/add_to_cart.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/view_cart.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/begin_checkout.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/purchase.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/view_item_list.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/refund.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/add_to_wishlist.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/add_shipping_info.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/remove_from_cart.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/select_item.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/view_promotion.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/select_promotion.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/page_view.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/clicked.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/form_submitted.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/input_blurred.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/input_changed.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/input_focused.php';