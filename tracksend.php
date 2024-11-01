<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              tracksend.co
 * @since             1.0.0
 * @package           Tracksend
 *
 * @wordpress-plugin
 * Plugin Name:       Tracksend for WooCommerce
 * Plugin URI:        http://www.tracksend.co
 * Description:       A WordPress plugin to connect to Tracksend's WooCommerce integration.
 * Version:           1.0.4
 * Author:            Tracksend
 * Author URI:        http://www.tracksend.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tracksend
 * Domain Path:       /languages
 * WC requires at least: 3.5.0
 * WC tested up to: 6.2.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TRACKSEND_VERSION', '1.0.0' );

/**
 * Check State of the plugin
 */

if(!function_exists('is_plugin_active')) {
	include_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

/**
 * Check for the existence of woocommerce and only other requiremnts 
 */

 function tracksend_check_requirements(){
	 if( is_plugin_active('woocommerce/woocommerce.php') ){
		 return true;
	 }
	 else {
		 add_action('admin_notices', 'tracksend_missing_wc_notice');
		 return false;
	 }
 }

 /**
  * Display a Message advicing that woocommerce is required 
  */

  function tracksend_missing_wc_notice(){
	  $class = 'notice notice-error';
	  $message = __('Tracksend for WooCommerce requires WooCommerce to be installed and active', 'tracksend');

	  printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
  }

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tracksend-activator.php
 */
function activate_tracksend() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tracksend-activator.php';
	Tracksend_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tracksend-deactivator.php
 */
function deactivate_tracksend() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tracksend-deactivator.php';
	Tracksend_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tracksend' );
register_deactivation_hook( __FILE__, 'deactivate_tracksend' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tracksend.php';

/**Setup Logging  */
function tracksend_log($action, $message, $data = array()) {
    if (function_exists('wc_get_logger')) {
        if (is_array($data) && !empty($data)) $message .= " :: ".wc_print_r($data, true);
        wc_get_logger()->notice("{$action} :: {$message}", array('source' => 'tracksend'));
    }
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */



function run_tracksend() {
	if(tracksend_check_requirements()){

		
		$plugin = new Tracksend();
		$plugin->run();
	}
}
run_tracksend();
