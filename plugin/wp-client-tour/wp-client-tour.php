<?php
/**
 * Plugin Name: WP Client Tour
 * Plugin URI:  https://github.com/kingsbury-labs/wp-client-tour
 * Description: AI-authored guided help tours for your clients inside wp-admin. Zero dependencies, role-aware, set-and-forget.
 * Version:     1.1.0
 * Author:      Rob Kingsbury
 * Author URI:  https://kingsburycreative.com
 * License:     MIT
 * Text Domain: wp-client-tour
 */

defined( 'ABSPATH' ) || exit;

define( 'WCT_VERSION', '1.1.0' );
define( 'WCT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCT_TOURS_DIR', apply_filters( 'wct_tours_dir', WCT_PLUGIN_DIR . 'tours/' ) );
define( 'WCT_META_KEY', 'wct_completed_tours' );
define( 'WCT_OPTION_TEST_MODE', 'wct_test_mode' );

require_once WCT_PLUGIN_DIR . 'includes/class-tour-loader.php';
require_once WCT_PLUGIN_DIR . 'includes/class-tour-renderer.php';
require_once WCT_PLUGIN_DIR . 'includes/class-admin-page.php';
require_once WCT_PLUGIN_DIR . 'includes/class-update-checker.php';
require_once WCT_PLUGIN_DIR . 'includes/class-dashboard-widget.php';

add_action( 'admin_init', array( 'WCT_Tour_Renderer', 'init' ) );
add_action( 'admin_menu', array( 'WCT_Admin_Page', 'register' ) );
add_action( 'rest_api_init', array( 'WCT_Tour_Renderer', 'register_rest_routes' ) );
add_action( 'plugins_loaded', array( 'WCT_Update_Checker', 'init' ) );

if ( get_option( WCT_Dashboard_Widget::OPTION_KEY ) ) {
	add_action( 'plugins_loaded', array( 'WCT_Dashboard_Widget', 'init' ) );
}

register_activation_hook(
	__FILE__,
	static function () {
		if ( ! file_exists( WCT_TOURS_DIR ) ) {
			wp_mkdir_p( WCT_TOURS_DIR );
		}
	}
);
