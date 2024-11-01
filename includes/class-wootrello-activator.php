<?php
/**
 * Fired during plugin activation. This class defines all code necessary to run during the plugin's activation.
 * @link       http://javmah.tk
 * @since      1.0.0
 * @package    Wootrello
 * @subpackage Wootrello/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Wootrello_Activator {

	/**
	 * Short Description. (use period)
	 * Long Description.
	 * @since    2.0.0
	 */
	public static function activate() {
		# setting installed date to option table;
		$installed = get_option("wootrello_installed");
		
		if(! $installed){
			update_option("wootrello_installed", time());		# first time installed date;
		}else{
			update_option("wootrello_re_installed", time());	# last time installed date;
		}
		#--------------------------------------------------------------------------------------------
		# Transferring Trello API Key to Version 3.0.0  
		# Old Api key 
		$old_wootrello_access_code 	= get_option( 'wootrello_access_code' );
		# New Trello API Holder 
		$wootrello_trello_API   	= get_option( "wootrello_trello_API" );  // NEW
		# New Wootrello Settings 
		$wootrello_settings 		= get_option( 'wootrello_trello_settings' );
		# version 1.0.0 to 3.0.0
		if(isset($old_wootrello_access_code) AND !empty($old_wootrello_access_code ) AND empty($wootrello_trello_API)){
			# Updating the Trello API key 
			update_option( "wootrello_trello_API", $old_wootrello_access_code );	
			# Delete old access code ;
			delete_option( "wootrello_access_code");
		} 
		
		# Version 2.0.0 to 3.0.0
		if(isset($wootrello_settings['trello_access_code']) AND !empty($wootrello_settings['trello_access_code']) AND empty($wootrello_trello_API)){
			# updating the Trello API key 
			update_option( "wootrello_trello_API", $wootrello_settings['trello_access_code'] );	
			# Delete Previous Settings 
			delete_option( "wootrello_trello_settings");
		} 
	}

}

