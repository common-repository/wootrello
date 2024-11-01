<?php
/**
 * The core plugin class.
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current version of the plugin.
 * @since      1.0.0
 * @package    Wootrello
 * @subpackage Wootrello/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Wootrello_Helpers {
	/**
	 * Admin notice function;
	 * @since    1.0.0
	*/
	public function test(){
		// 
		return "INFO : hello from testing.";
	}

	public function log_status(){
		// 
		if (function_exists('get_option')){
			return"get_option function exist";
		} else {
			return"get_option function is not exist";
		}
	}

	/**
	 * LOG ! For Good, This the log Method 
	 * @since      1.0.0
	 * @param      string    $function_name     Function name.	 [  __METHOD__  ]
	 * @param      string    $status_code       The name of this plugin.
	 * @param      string    $status_message    The version of this plugin.
	*/
	public function wootrello_log($function_name = '', $status_code = '', $status_message = ''){
		if (function_exists('get_option')){
			# WooTrello Log Status 
			$wootrelloLogStatus  = get_option( "wootrelloLogStatus" );
			# check and balances
			if( $wootrelloLogStatus  == 'Disable'){
				return  array( FALSE, "WooTrello log is Disable! enable it to keep the Log.");
			}
			# Check and Balance Bro 
			if(empty($status_code) OR empty($status_message)){
				return  array(FALSE, "status_code or status_message is Empty");
			}
			# inserting custom log by using custom post type 
			$r = wp_insert_post( 
				array(	
					'post_content'  => $status_message,
					'post_title'  	=> $status_code,
					'post_status'  	=> "publish",
					'post_excerpt'  => $function_name ,
					'post_type'  	=> "wootrello_log",
				)
			);
			# if Successfully inserted its Okay
			if($r){
				return  array(TRUE, "Successfully inserted to the Log"); 
			}
		} else {
			return  array(FALSE, "get_option function is not exist"); 
		}
	}

	/**
	 * wootrello_wooCommerce_order_metaKeys
	*/
	public function wootrello_wooCommerce_order_metaKeys(){
		# Global Db object 
		global $wpdb;
		#
		if (!isset($wpdb)) {
			return array(FALSE, 'ERROR: $wpdb not defined.');
		}
		# New Code Starts 
		$meta_keys = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->prefix}wc_orders_meta");
		# return Depend on the Query result
		if(empty($meta_keys)){
			return array(FALSE, 'EMPTY ! No Meta key exist of the Post type X');
		} else {
			return array(TRUE, $meta_keys);
		}
	}

	/**
	 * wootrello_wooCommerce_order_post_metaKeys
	*/
	public function wootrello_wooCommerce_order_post_metaKeys(){
		# Global Db object 
		global $wpdb;
		#
		if (!isset($wpdb)) {
			return array(FALSE, 'ERROR: $wpdb not defined.');
		}
		# Query 
		$query = "
			SELECT DISTINCT($wpdb->postmeta.meta_key) 
			FROM $wpdb->posts 
			LEFT JOIN $wpdb->postmeta 
			ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
			WHERE $wpdb->posts.post_type = 'shop_order' 
			AND $wpdb->postmeta.meta_key != '' 
		";
		# execute Query
		$meta_keys = $wpdb->get_col( $query );
		# return Depend on the Query result
		if(empty($meta_keys)){
			return array(FALSE, 'EMPTY ! No Meta key exist of the Post type X');
		} else {
			return array(TRUE, $meta_keys);
		}
	}

	/**
	* wootrello_user_previous_orders, Current order user Previous order history ;
	* @param    string     $billing_email     User email Address 
	* @since    2.0.0
	*/
	public function  wootrello_user_previous_orders( $billing_email = '' ){
		# Check and balance Bro 
		if( empty($billing_email) OR !filter_var( $billing_email, FILTER_VALIDATE_EMAIL) ){
			# inserting log
			$this->wootrello_log('wootrello_user_previous_orders', 717, 'ERROR: EMPTY Billing address or INVALID EMAIL address ');
			#
			return array( FALSE, 'ERROR: EMPTY Billing address or INVALID EMAIL address ');
		}
		# Getting all orders
		$orders = wc_get_orders( array(
			'limit'       => -1,
			'return'      => 'objects',
			'orderby'     => 'date',
			'customer'    => $billing_email,
		));

		# Counting the Number of Orders of that user OR Check and Balance 
		if(! count( $orders )){
			$this->wootrello_log('wootrello_user_previous_orders ', 101, 'ERROR: Order id is Empty.');
			return array( FALSE, "There is no orders of this user");
		}
		# Orders Status Holder; aka empty array() 
		$order_statuses = array();
		foreach ( $orders as $key => $value ) {
			$order_statuses[$value->get_status()][] = $value->get_id();
		}
		# Holder Empty;
		$status_numbers = array();
		$txt = "";
		foreach ( $order_statuses as $key => $order_ids ) {
			$status_numbers[  $key ] = count(  $order_ids );
			$txt .= $key ." - ". count(  $order_ids ) .", ";
		}
		# return array with two parameters one for bool and other is data;
		return array( TRUE, $status_numbers, $txt );
	}

	/**
	 *  wootrello_write_card_status_on_order_meta
	 *  @since  3.2.0
	*/
	public function wootrello_write_status_on_order_meta($order_id, $order_status) {
		# Empty check 
		if (empty($order_id)) {
			$this->wootrello_log('wootrello_write_status_on_order_meta', 101, 'ERROR: Order id is Empty in wootrello_write_status_on_order_meta().');
			error_log('ERROR: Order id is Empty in wootrello_write_status_on_order_meta().');
			return array(FALSE, "Order id is Empty!");
		}
		
		# Empty check 
		if (empty($order_status)) {
			$this->wootrello_log('wootrello_write_status_on_order_meta', 102, 'ERROR: Order status is Empty.');
			error_log('ERROR: Order status is Empty in wootrello_write_status_on_order_meta().');
			return array(FALSE, "Order status is Empty!");
		}
		
		# getting current timeStamp 
		$timestamp = current_time('timestamp');
		
		# if WP timeStamp is Empty
		if (empty($timestamp) OR !is_numeric($timestamp)) {
			$timestamp = time();
		}
		
		# getting meta value and updating the value 
		if (function_exists('wc_get_order') && method_exists('WC_Order', 'get_meta')) {
			$order = wc_get_order($order_id);
			if ($order) {
				$meta_value = $order->get_meta("wootrello_status");
				
				# Ensure meta_value is an array
				if (!is_array($meta_value)) {
					$meta_value = array();
				}
				
				# Update the meta value
				if (empty($meta_value)) {
					$meta_value = array();
					$meta_value[$order_status][] = $timestamp;
				} else {
					if (!isset($meta_value[$order_status]) || !is_array($meta_value[$order_status])) {
						$meta_value[$order_status] = array();
					}
					$meta_value[$order_status][] = $timestamp;
				}
				#
				$order->update_meta_data("wootrello_status", $meta_value);
				$order->save();
			} else {
				$this->wootrello_log('wootrello_write_status_on_order_meta', 102, 'ERROR: Order does not exist with this ID in wootrello_write_status_on_order_meta().');
				error_log('ERROR: Order does not exist with this ID in wootrello_write_status_on_order_meta().');
			}
		} else {
			$this->wootrello_log('wootrello_write_status_on_order_meta', 102, 'ERROR: wc_get_order or get_meta does not exist.');
			error_log('ERROR: wc_get_order or get_meta does not exist.');
		}
		
		return array(TRUE, "Status updated!");
	}
	
	/**
	 * date initials to Due date conversion.
	 * @since    2.0.0
	 * @param    string    $date initials.
	*/
	public function DueDateCalc($selected =''){
		// 
		if( $selected == "1d" ) {
			$date = date("Y-m-d", time() + 86400 );
		} elseif ( $selected == "2d" ) {
			$date = date("Y-m-d", time() + 86400 * 2 );
		} elseif ( $selected == "3d" ) {
			$date = date("Y-m-d", time() + 86400 * 3 );
		} elseif ( $selected == "5d" ) {
			$date = date("Y-m-d", time() + 86400 * 5 );
		} elseif ( $selected == "1w" ) {
			$date = date("Y-m-d", time() + 86400 * 7 );
		} elseif ( $selected == "2w" ) {
			$date = date("Y-m-d", time() + 86400 * 14 );
		} elseif ( $selected == "1m" ) {
			$date = date("Y-m-d", time() + 86400 * 30 );
		} elseif ( $selected == "3m" ) {
			$date = date("Y-m-d", time() + 86400 * 90 );
		} elseif ( $selected == "6m" ) {
			$date = date("Y-m-d", time() + 86400 * 180 );
		}  else {
			$date =  date("Y-m-d", time());
		}
		// 
		return  $date;
	}

	/**
	* Third party plugin :
	* Checkout Field Editor ( Checkout Manager ) for WooCommerce.
	* BETA testing;
	* Important Pro Version of this Plugin has Changed; So Mark it for Update ***
	* @since    2.0.0
    */
    public function wootrello_woo_checkout_field_editor_pro_fields(){
        # Getting Active Plugins;
        $active_plugins 				= get_option( 'active_plugins' );
        $woo_checkout_field_editor_pro 	= array();
        # Checking Plugin installed or Not.
        if ( in_array( 'woo-checkout-field-editor-pro/checkout-form-designer.php', $active_plugins ) ) {
            $a = get_option( "wc_fields_billing" );
            $b = get_option( "wc_fields_shipping" );
            $c = get_option( "wc_fields_additional" );
            
            if ( $a ) {
                foreach ( $a as $key => $field ) {
                    if ( isset( $field['custom'] ) AND $field['custom'] == 1 ) {
                        $woo_checkout_field_editor_pro[$key]['type']  = isset($field['type'])  ? $field['type']  : "";
                        $woo_checkout_field_editor_pro[$key]['name']  = isset($field['name'])  ? $field['name']  : "";
                        $woo_checkout_field_editor_pro[$key]['label'] = isset($field['label']) ? $field['label'] : ""; 
                    }
                }
            }

            if ( $b ) {
                foreach ( $b as $key => $field ) {
                    if ( isset( $field['custom'] ) AND $field['custom'] == 1 ) {
                        $woo_checkout_field_editor_pro[$key]['type']  = isset($field['type'])  ? $field['type']  : "";
                        $woo_checkout_field_editor_pro[$key]['name']  = isset($field['name'])  ? $field['name']  : "";
                        $woo_checkout_field_editor_pro[$key]['label'] = isset($field['label']) ? $field['label'] : ""; 
                    }
                }
            }

            if ( $c ) {
                foreach ( $c as $key => $field ) {
                    if ( isset( $field['custom'] ) AND $field['custom'] == 1 ) {
                        $woo_checkout_field_editor_pro[$key]['type']  = isset($field['type'])  ? $field['type']  : "";
                        $woo_checkout_field_editor_pro[$key]['name']  = isset($field['name'])  ? $field['name']  : "";
                        $woo_checkout_field_editor_pro[$key]['label'] = isset($field['label']) ? $field['label'] : ""; 
                    }
                }
            }

        } else {
            return array( FALSE, "Checkout Field Editor aka Checkout Manager for WooCommerce is not INSTALLED." );
        }
        # Do or not
        if ( empty($woo_checkout_field_editor_pro) ) {
            # Insert Log
			$this->wootrello_log( 'wootrello_woo_checkout_field_editor_pro_fields', 720, 'ERROR: Checkout Field Editor aka Checkout Manager for WooCommerce is EMPTY no Custom Field !' );
            # return
            return array( FALSE, "ERROR: Checkout Field Editor aka Checkout Manager for WooCommerce is EMPTY no Custom Field. " );
        } else {
            return array( TRUE, $woo_checkout_field_editor_pro );
        }
    }
}
