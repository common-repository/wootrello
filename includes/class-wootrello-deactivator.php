<?php
/**
 * Fired during plugin deactivation.This class defines all code necessary to run during the plugin's deactivation.
 * @since      1.0.0
 * @package    Wootrello
 * @subpackage Wootrello/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Wootrello_Deactivator {

	/**
	 * Short Description. (use period)
	 * Long Description.
	 * @since    1.0.0
	 */
	public static function deactivate() {
		# Delete log status;
		delete_option( "wootrelloLogStatus");
	}
}