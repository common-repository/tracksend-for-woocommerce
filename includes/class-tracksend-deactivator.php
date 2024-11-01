<?php

/**
 * Fired during plugin deactivation
 *
 * @link       tracksend.co
 * @since      1.0.0
 *
 * @package    Tracksend
 * @subpackage Tracksend/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Tracksend
 * @subpackage Tracksend/includes
 * @author     Tracksend <admin@tracksend.co>
 */
class Tracksend_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$option_name = 'tracksend_api_key';
 
        delete_option($option_name);

	}

}
