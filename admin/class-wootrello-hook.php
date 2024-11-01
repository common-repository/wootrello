<?php
/**
 * The admin-specific functionality of the plugin.
 * @link       http://javmah.tk
 * @since      1.0.0
 * @package    Wootrello
 * @subpackage Wootrello/admin
 * @author     javmah <jaedmah@gmail.com>
*/
# Include the helper class
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wootrello-helpers.php';
// 
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wootrello-trello-api.php';


class Wootrello_Hooks {
	
	// Property to hold the instance of Wootrello_Helpers
    protected $helpers;

	// Property to hold the instance of Wootrello_Helpers
    protected $trelloApi;

	/**
	 * The ID of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	*/
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
	private $version;

	/**
	 * The version of this plugin.  
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
	private $active_plugins = array();

	/**
	 * WooCommerce Order statuses .
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $key    trello Application key of this plugin.
	*/
	private $order_statuses = array('new_order' => 'New checkout page order');

	/**
	 * Initialize the class and set its properties.
	 * @since      1.0.0
	 * @param      string    $plugin_name   The name of this plugin.
	 * @param      string    $version    	The version of this plugin.
	*/
	public function __construct($plugin_name, $version){
		# plugin name 
		$this->plugin_name 		= $plugin_name;
		# Plugin version 
		$this->version 			= $version;
		# Active plugins 
		$this->active_plugins 	= get_option('active_plugins');
		# Create an instance of the Wootrello_Helpers class
		$this->helpers = new Wootrello_Helpers();
		# Create an instance of the Wootrello_Helpers class
		$this->trelloApi = new Wootrello_Trello_API();
	}

	/**
	 * Admin notice function;
	 * @since    1.0.0
	*/
	public function wootrello_hooks_notice(){
		# testing is in Here 
		// echo"<pre>";
			
		// echo"</pre>";
	}

	/**
	 * WooCommerce Order  HOOK's callback function
	 * woocommerce_order_status_changed hook callback function 
	 * @since    1.0.0
	 * @param     int     $order_id     Order ID
	*/
	public function wootrello_woocommerce_order_status_changed( $order_id, $this_status_transition_from, $this_status_transition_to ){
		# Getting Order Details by Order ID
		$order 	=  wc_get_order($order_id);
		if(! $order){
			return;
		}
		# Order Data Holder;
		$order_data 								=  array();
		$order_data['orderID'] 						= ( method_exists( $order, 'get_id' ) 			  		AND	    is_int( $order->get_id()))					? 	$order->get_id()						 : 	"";
		$order_data['cart_tax'] 					= ( method_exists( $order, 'get_cart_tax' ) 	  		AND 	is_string( $order->get_cart_tax()  ))		? 	$order->get_cart_tax() 					 : 	"";
		$order_data['currency'] 					= ( method_exists( $order, 'get_currency' ) 	  		AND 	is_string( $order->get_currency()  ))		? 	$order->get_currency() 					 :	"";
		$order_data['discount_tax'] 				= ( method_exists( $order, 'get_discount_tax' )   		AND 	is_string( $order->get_discount_tax() ))	?	$order->get_discount_tax() 				 :	"";
		$order_data['discount_total'] 				= ( method_exists( $order, 'get_discount_total' ) 		AND 	is_string( $order->get_discount_total() ))	? 	$order->get_discount_total()			 :	"";
		$order_data['fees'] 						= ( method_exists( $order, 'get_fees' ) 		  		AND    ! empty( $order->get_fees() ) AND is_array( $order->get_fees()) ) 	?   json_encode( $order->get_fees()) 	:   "";
		$order_data['shipping_method'] 				= ( method_exists( $order, 'get_shipping_method' )		AND 	is_string( $order->get_shipping_method() ))	? 	$order->get_shipping_method() 			 :	"";
		$order_data['shipping_tax'] 				= ( method_exists( $order, 'get_shipping_tax' ) 		AND 	is_string( $order->get_shipping_tax()  ))	? 	$order->get_shipping_tax() 				 :	"";
		$order_data['shipping_total'] 				= ( method_exists( $order, 'get_shipping_total' ) 		AND 	is_string( $order->get_shipping_total()  ))	? 	$order->get_shipping_total()			 :	"";
		$order_data['subtotal'] 					= ( method_exists( $order, 'get_subtotal' ) 			AND 	is_float( $order->get_subtotal()  ))		? 	$order->get_subtotal()					 :	"";
		$order_data['subtotal_to_display'] 			= ( method_exists( $order, 'get_subtotal_to_display') 	AND 	is_string( $order->get_subtotal_to_display()))? $order->get_subtotal_to_display() 		 : 	"";
		$order_data['tax_totals'] 					= ( method_exists( $order, 'get_tax_totals' ) 			AND    ! empty($order->get_tax_totals()) 	AND is_array( $order->get_tax_totals())) ?  json_encode( $order->get_tax_totals()) 	: ""; 
		$order_data['taxes'] 						= ( method_exists( $order, 'get_taxes' ) 				AND    ! empty($order->get_taxes()) 		AND is_array( $order->get_taxes()) ) 	 ?  json_encode( $order->get_taxes()) 		: "";  
		$order_data['total'] 						= ( method_exists( $order, 'get_total' ) 				AND 	is_string( $order->get_total() ))			 ?  $order->get_total() 		 			 :	"";
		$order_data['total_discount'] 				= ( method_exists( $order, 'get_total_discount' ) 		AND 	is_float( $order->get_total_discount()  ))   ?  $order->get_total_discount() 			 :	"";
		$order_data['total_tax'] 					= ( method_exists( $order, 'get_total_tax'  ) 			AND 	is_string( $order->get_total_tax() ))		 ? 	$order->get_total_tax() 	 			 :	"";
		$order_data['total_refunded'] 				= ( method_exists( $order, 'get_total_refunded' ) 		AND 	is_float( $order->get_total_refunded() ))	 ? 	$order->get_total_refunded() 			 :	"";
		$order_data['total_tax_refunded'] 			= ( method_exists( $order, 'get_total_tax_refunded' ) 	AND 	is_int( $order->get_total_tax_refunded()))	 ?  $order->get_total_tax_refunded()		 :	"";
		$order_data['total_shipping_refunded'] 		= ( method_exists( $order, 'get_total_shipping_refunded')AND    is_int( $order->get_total_shipping_refunded() ))?  $order->get_total_shipping_refunded() :	"";
		$order_data['item_count_refunded'] 			= ( method_exists( $order, 'get_item_count_refunded' ) 	AND 	is_int( $order->get_item_count_refunded() )) ?  $order->get_item_count_refunded() 		 :	"";
		$order_data['total_qty_refunded'] 			= ( method_exists( $order, 'get_total_qty_refunded' ) 	AND 	is_int( $order->get_total_qty_refunded() ))  ?  $order->get_total_qty_refunded() 		 :	"";
		$order_data['remaining_refund_amount']  	= ( method_exists( $order, 'get_remaining_refund_amount')AND    is_string($order->get_remaining_refund_amount()))?  $order->get_remaining_refund_amount():	"";
		# Order Item process Starts
		if(is_array( $order->get_items())){ 
			$items = array();
			$cart_items_weight = array();
			foreach ( $order->get_items() as $item_id => $item_data ) {
				$items[$item_id]['product_id']		  =  ( method_exists( $item_data, "get_product_id" ) 	AND 	is_int( $item_data->get_product_id() ) ) 	?  	$item_data->get_product_id() 	: ""; 
				$items[$item_id]['variation_id']	  =  ( method_exists( $item_data, "get_variation_id" )  AND 	is_int( $item_data->get_variation_id() ) ) 	?  	$item_data->get_variation_id() 	: ""; 
				$items[$item_id]['product_sku']	  	  =  ( empty( $items[$item_id]['variation_id']) ) 		?  	get_post_meta( $items[$item_id]['product_id'], '_sku', true ) : get_post_meta( $items[$item_id]['variation_id'], '_sku', true ); 
				$items[$item_id]['product_name']	  =  ( method_exists( $item_data, "get_name" ) 	   	AND 	is_string( $item_data->get_name() ) ) 		?  	$item_data->get_name() 			: ""; 
				$items[$item_id]['qty'] 			  =  ( method_exists( $item_data, "get_quantity" ) 	AND 	is_int( $item_data->get_quantity() ) ) 		?   $item_data->get_quantity() 		: ""; 
				$items[$item_id]['product_qty_price'] =  ( method_exists( $item_data, "get_total" ) 		AND 	is_string( $item_data->get_total() ) ) 		? 	$item_data->get_total() 		: ""; 
				$items[$item_id]['product_weight'] 	  =  ( method_exists( $item_data->get_product(), 'get_weight') AND !empty($item_data->get_product()->get_weight()) ) ? $item_data->get_product()->get_weight()  *  $items[$item_id]['qty'] :  "";
				# getting total weight
				$cart_items_weight[] 				  =  ( method_exists( $item_data->get_product(), 'get_weight') AND !empty($item_data->get_product()->get_weight()) ) ? $item_data->get_product()->get_weight()  *  $items[$item_id]['qty'] :  "";
			}
		}
		# Order Item process Ends
		$order_data['items'] 						=  ( $items ) ? $items : "" ;
		$order_data['cart_items_weight'] 			=  array_sum($cart_items_weight);
		$order_data['item_count'] 			    	=  ( method_exists( $order, 'get_item_count') 			AND 	is_int($order->get_item_count() )) 			? 	$order->get_item_count() : "";
		$order_data['downloadable_items'] 			=  ( method_exists( $order, 'get_downloadable_items' ) 	AND ! empty($order->get_downloadable_items())AND  is_array(  $order->get_downloadable_items()) ) 	? json_encode( $order->get_downloadable_items()) : "" ;   // Need To Change
		#
		$order_data['date_created'] 				=  ( method_exists( $order, 'get_date_created' ) 		AND ! empty($order->get_date_created()) 	AND	is_string( $order->get_date_created()->date("F j, Y, g:i:s A T") )) 	? 	$order->get_date_created()->date("F j, Y, g:i:s A T") 		: ""; 
		$order_data['date_modified'] 				=  ( method_exists( $order, 'get_date_modified' ) 		AND ! empty($order->get_date_modified())  	AND	is_string( $order->get_date_modified()->date("F j, Y, g:i:s A T")) ) 	? 	$order->get_date_modified()->date("F j, Y, g:i:s A T") 		: ""; 
		$order_data['date_completed'] 				=  ( method_exists( $order, 'get_date_completed' ) 		AND ! empty($order->get_date_completed())  	AND	is_string( $order->get_date_completed()->date("F j, Y, g:i:s A T"))) 	? 	$order->get_date_completed()->date("F j, Y, g:i:s A T") 	: "";
		$order_data['date_paid'] 					=  ( method_exists( $order, 'get_date_paid' ) 			AND ! empty($order->get_date_paid()) 	  	AND	is_string( $order->get_date_paid()->date("F j, Y, g:i:s A T")) ) 	 	? 	$order->get_date_paid()->date("F j, Y, g:i:s A T") 			: "";
		#
		$order_data['user'] 						=  ( method_exists( $order, 'get_user')  				AND  ! empty($order->get_user()) AND is_object( $order->get_user()) ) ? 	$order->get_user()->user_login  . " - " . $order->get_user()->user_email 	: "";
		$order_data['customer_id'] 					=  ( method_exists( $order, 'get_customer_id' ) 		AND 	is_int( $order->get_customer_id() )) 			? 	$order->get_customer_id() 			: "";
		$order_data['user_id'] 						=  ( method_exists( $order, 'get_user_id' ) 			AND 	is_int( $order->get_user_id() )) 				? 	$order->get_user_id()				: "";
		$order_data['customer_ip_address'] 			=  ( method_exists( $order, 'get_customer_ip_address')  AND 	is_string( $order->get_customer_ip_address())) 	? 	$order->get_customer_ip_address()	: "";
		$order_data['customer_user_agent'] 			=  ( method_exists( $order, 'get_customer_user_agent')  AND 	is_string( $order->get_customer_user_agent()))	? 	$order->get_customer_user_agent()	: "";
		$order_data['created_via'] 					=  ( method_exists( $order, 'get_created_via' ) 		AND 	is_string( $order->get_created_via() ))			? 	$order->get_created_via()			: "";
		$order_data['customer_note'] 				=  ( method_exists( $order, 'get_customer_note' ) 		AND 	is_string( $order->get_customer_note() ))		? 	$order->get_customer_note()			: "";
		$order_data['billing_first_name'] 			=  ( method_exists( $order, 'get_billing_first_name' )  AND 	is_string( $order->get_billing_first_name() ))	? 	$order->get_billing_first_name()	: "";
		$order_data['billing_last_name'] 			=  ( method_exists( $order, 'get_billing_last_name' ) 	AND 	is_string( $order->get_billing_last_name() ))	? 	$order->get_billing_last_name()		: "";
		$order_data['billing_company'] 				=  ( method_exists( $order, 'get_billing_company' ) 	AND 	is_string( $order->get_billing_company() ))		? 	$order->get_billing_company()		: "";
		$order_data['billing_address_1'] 			=  ( method_exists( $order, 'get_billing_address_1' ) 	AND 	is_string( $order->get_billing_address_1() ))	? 	$order->get_billing_address_1()		: "";
		$order_data['billing_address_2'] 			=  ( method_exists( $order, 'get_billing_address_2' ) 	AND 	is_string( $order->get_billing_address_2() ))	? 	$order->get_billing_address_2()		: "";
		$order_data['billing_city'] 				=  ( method_exists( $order, 'get_billing_city' ) 		AND 	is_string( $order->get_billing_city() ))		? 	$order->get_billing_city()			: "";
		$order_data['billing_state'] 				=  ( method_exists( $order, 'get_billing_state' ) 		AND 	is_string( $order->get_billing_state() )) 		? 	$order->get_billing_state()			: "";
		$order_data['billing_postcode'] 			=  ( method_exists( $order, 'get_billing_postcode' ) 	AND 	is_string( $order->get_billing_postcode() ))	? 	$order->get_billing_postcode()		: "";
		$order_data['billing_country'] 				=  ( method_exists( $order, 'get_billing_country' ) 	AND 	is_string( $order->get_billing_country() ))		? 	$order->get_billing_country()		: "";
		$order_data['billing_email'] 				=  ( method_exists( $order, 'get_billing_email' ) 		AND 	is_string( $order->get_billing_email() ))		? 	$order->get_billing_email()			: "";
		$order_data['billing_phone'] 				=  ( method_exists( $order, 'get_billing_phone' ) 		AND 	is_string( $order->get_billing_phone()))		? 	$order->get_billing_phone()			: "";
		$order_data['shipping_first_name'] 			=  ( method_exists( $order, 'get_shipping_first_name' ) AND 	is_string( $order->get_shipping_first_name())) 	? 	$order->get_shipping_first_name()	: "";
		$order_data['shipping_last_name'] 			=  ( method_exists( $order, 'get_shipping_last_name' )  AND 	is_string( $order->get_shipping_last_name() ))	? 	$order->get_shipping_last_name()	: "";
		$order_data['shipping_company'] 			=  ( method_exists( $order, 'get_shipping_company' ) 	AND 	is_string( $order->get_shipping_company() ))	?	$order->get_shipping_company()		: "";
		$order_data['shipping_address_1'] 			=  ( method_exists( $order, 'get_shipping_address_1' )  AND 	is_string( $order->get_shipping_address_1() ))	? 	$order->get_shipping_address_1()	: "";
		$order_data['shipping_address_2'] 			=  ( method_exists( $order, 'get_shipping_address_2' )  AND 	is_string( $order->get_shipping_address_2() ))	? 	$order->get_shipping_address_2()	: "";
		$order_data['shipping_city'] 				=  ( method_exists( $order, 'get_shipping_city' ) 		AND 	is_string( $order->get_shipping_city() ))		? 	$order->get_shipping_city()			: "";
		$order_data['shipping_state'] 				=  ( method_exists( $order, 'get_shipping_state' ) 	 	AND 	is_string( $order->get_shipping_state() )) 		? 	$order->get_shipping_state()		: "";
		$order_data['shipping_postcode'] 			=  ( method_exists( $order, 'get_shipping_postcode' ) 	AND 	is_string( $order->get_shipping_postcode() ))	? 	$order->get_shipping_postcode()		: "";
		$order_data['shipping_country'] 			=  ( method_exists( $order, 'get_shipping_country' ) 	AND 	is_string( $order->get_shipping_country() )) 	? 	$order->get_shipping_country()		: "";
		$order_data['address'] 						=  ( method_exists( $order,	'get_address' ) 	 		AND 	is_array(  $order->get_address()) ) 			? 	json_encode( $order->get_address()) : "";
		$order_data['shipping_address_map_url'] 	=  ( method_exists( $order, 'get_shipping_address_map_url' ) 	 AND	is_string( $order->get_shipping_address_map_url()))		?	$order->get_shipping_address_map_url()		: "";
		$order_data['formatted_billing_full_name'] 	=  ( method_exists( $order, 'get_formatted_billing_full_name' )  AND is_string( $order->get_formatted_billing_full_name() ))	?	$order->get_formatted_billing_full_name()	: "";
		$order_data['formatted_shipping_full_name']	=  ( method_exists( $order, 'get_formatted_shipping_full_name' ) AND is_string( $order->get_formatted_shipping_full_name() ))?	$order->get_formatted_shipping_full_name()		: "";
		$order_data['formatted_billing_address'] 	=  ( method_exists( $order, 'get_formatted_billing_address' ) 	 AND is_string( $order->get_formatted_billing_address() ))	?	$order->get_formatted_billing_address()			: "";
		$order_data['formatted_shipping_address'] 	=  ( method_exists( $order, 'get_formatted_shipping_address' )   AND is_string( $order->get_formatted_shipping_address() ))	?	$order->get_formatted_shipping_address()		: "";
		#
		$order_data['payment_method'] 				=  ( method_exists( $order, 'get_payment_method' ) 				AND 	is_string( $order->get_payment_method() ))				?	$order->get_payment_method()				: "";
		$order_data['payment_method_title'] 		=  ( method_exists( $order, 'get_payment_method_title' ) 		AND 	is_string( $order->get_payment_method_title() ))		? 	$order->get_payment_method_title()			: "";
		$order_data['transaction_id'] 				=  ( method_exists( $order, 'get_transaction_id' ) 				AND 	is_string( $order->get_transaction_id() ))				? 	$order->get_transaction_id()				: "";
		#
		$order_data['checkout_payment_url'] 		=  ( method_exists( $order, 'get_checkout_payment_url' ) 		AND	is_string( $order->get_checkout_payment_url() ))			? 	$order->get_checkout_payment_url()			: "";
		$order_data['checkout_order_received_url'] 	=  ( method_exists( $order, 'get_checkout_order_received_url') 	AND 	is_string( $order->get_checkout_order_received_url() )) ? 	$order->get_checkout_order_received_url()	: "";
		$order_data['cancel_order_url'] 			=  ( method_exists( $order, 'get_cancel_order_url' ) 			AND 	is_string( $order->get_cancel_order_url() ))			? 	$order->get_cancel_order_url()				: "";
		$order_data['cancel_order_url_raw'] 		=  ( method_exists( $order, 'get_cancel_order_url_raw' ) 		AND 	is_string( $order->get_cancel_order_url_raw()))			? 	$order->get_cancel_order_url_raw()			: "";
		$order_data['cancel_endpoint'] 				=  ( method_exists( $order, 'get_cancel_endpoint' ) 			AND 	is_string( $order->get_cancel_endpoint() ))				? 	$order->get_cancel_endpoint()				: "";
		$order_data['view_order_url'] 				=  ( method_exists( $order, 'get_view_order_url' ) 				AND 	is_string( $order->get_view_order_url() ))				? 	$order->get_view_order_url()				: "";
		$order_data['edit_order_url'] 				=  ( method_exists( $order, 'get_edit_order_url' ) 				AND 	is_string( $order->get_edit_order_url() )) 				? 	$order->get_edit_order_url()				: "";
		#
		$order_data['status'] 						=  ( empty( $this_status_transition_to ) ) ?  $order->get_status()  :   $this_status_transition_to ;
		
		if($order_id){
			$r = $this->trelloApi->wootrello_create_trello_card( $this_status_transition_to, $order_data );
			return $r;
		}
	}

    /**
	 * woocommerce_new_orders New Order  HOOK's callback function
	 * I'M USE THIS FOR ADMIN FRONT -> woocommerce_thankyou HOOK for FRONT END
	 * @since     1.0.0
	 * @param     int     $order_id     Order ID
	*/
	public function wootrello_woocommerce_new_order_admin( $order_id ){
		# Getting Order Details by Order ID
		$order 	=  wc_get_order( $order_id );
		if(! $order){
			$this->helpers->wootrello_log('wootrello_woocommerce_new_order_checkout', 701, 'ERROR: $order is false.');
			return;
		}
		# if not admin returns
		if($order->get_created_via() != 'admin'){
			return;
		}
		# Order Data Holder;
		$order_data 								=  array();
		$order_data['orderID'] 						= ( method_exists( $order, 'get_id' ) 			  		AND		is_int( $order->get_id()))					? 	$order->get_id()							: 	"";
		$order_data['cart_tax'] 					= ( method_exists( $order, 'get_cart_tax' ) 	  		AND 	is_string( $order->get_cart_tax()  ))		? 	$order->get_cart_tax() 						: 	"";
		$order_data['currency'] 					= ( method_exists( $order, 'get_currency' ) 	  		AND 	is_string( $order->get_currency()  ))		? 	$order->get_currency() 						:	"";
		$order_data['discount_tax'] 				= ( method_exists( $order, 'get_discount_tax' )   		AND 	is_string( $order->get_discount_tax() ))	?	$order->get_discount_tax() 					:	"";
		$order_data['discount_total'] 				= ( method_exists( $order, 'get_discount_total' ) 		AND 	is_string( $order->get_discount_total() ))	? 	$order->get_discount_total()				:	"";
		$order_data['fees'] 						= ( method_exists( $order, 'get_fees' ) 		  		AND  	! empty( $order->get_fees() ) AND is_array( $order->get_fees()) ) 	?   json_encode($order->get_fees())   :   "";
		$order_data['shipping_method'] 				= ( method_exists( $order, 'get_shipping_method' )		AND 	is_string( $order->get_shipping_method() ))	? 	$order->get_shipping_method() 				:	"";
		$order_data['shipping_tax'] 				= ( method_exists( $order, 'get_shipping_tax' ) 		AND 	is_string( $order->get_shipping_tax()  ))	? 	$order->get_shipping_tax() 					:	"";
		$order_data['shipping_total'] 				= ( method_exists( $order, 'get_shipping_total' ) 		AND 	is_string( $order->get_shipping_total()  ))	? 	$order->get_shipping_total()				:	"";
		$order_data['subtotal'] 					= ( method_exists( $order, 'get_subtotal' ) 			AND 	is_float( $order->get_subtotal()  ))		? 	$order->get_subtotal()						:	"";
		$order_data['subtotal_to_display'] 			= ( method_exists( $order, 'get_subtotal_to_display') 	AND 	is_string( $order->get_subtotal_to_display()))? $order->get_subtotal_to_display() 			:   "";
		$order_data['tax_totals'] 					= ( method_exists( $order, 'get_tax_totals' ) 			AND  	! empty($order->get_tax_totals()) AND is_array( $order->get_tax_totals()) ) 	?  json_encode( $order->get_tax_totals()) 	: ""; 
		$order_data['taxes'] 						= ( method_exists( $order, 'get_taxes' ) 				AND  	! empty($order->get_taxes()) 	 AND is_array( $order->get_taxes()) ) 			?  json_encode( $order->get_taxes()) 	  	: "";  
		$order_data['total'] 						= ( method_exists( $order, 'get_total' ) 				AND 	is_string( $order->get_total() ))			 ?  $order->get_total() 		 				:	"";
		$order_data['total_discount'] 				= ( method_exists( $order, 'get_total_discount' ) 		AND 	is_float( $order->get_total_discount()  ))   ?  $order->get_total_discount() 				:	"";
		$order_data['total_tax'] 					= ( method_exists( $order, 'get_total_tax'  ) 			AND 	is_string( $order->get_total_tax() ))		 ? 	$order->get_total_tax() 	 				:	"";
		$order_data['total_refunded'] 				= ( method_exists( $order, 'get_total_refunded' ) 		AND 	is_float( $order->get_total_refunded() ))	 ? 	$order->get_total_refunded() 				:	"";
		$order_data['total_tax_refunded'] 			= ( method_exists( $order, 'get_total_tax_refunded' ) 	AND 	is_int( $order->get_total_tax_refunded()))	 ?  $order->get_total_tax_refunded()			:	"";
		$order_data['total_shipping_refunded'] 		= ( method_exists( $order, 'get_total_shipping_refunded')AND 	is_int( $order->get_total_shipping_refunded() )) ?  $order->get_total_shipping_refunded() 	:	"";
		$order_data['item_count_refunded'] 			= ( method_exists( $order, 'get_item_count_refunded' ) 	AND 	is_int( $order->get_item_count_refunded() )) 	 ?  $order->get_item_count_refunded() 		:	"";
		$order_data['total_qty_refunded'] 			= ( method_exists( $order, 'get_total_qty_refunded' ) 	AND 	is_int( $order->get_total_qty_refunded() ))  	 ?  $order->get_total_qty_refunded() 		:	"";
		$order_data['remaining_refund_amount']  	= ( method_exists( $order, 'get_remaining_refund_amount')AND 	is_string($order->get_remaining_refund_amount()))?  $order->get_remaining_refund_amount()	:	"";
		# Order Item process Starts
		if ( is_array( $order->get_items()) ){ 
			$items = array();
			$cart_items_weight = array();
			foreach ( $order->get_items() as $item_id => $item_data ) {
				$items[$item_id]['product_id']		  =  ( method_exists( $item_data, "get_product_id" ) 	AND 	is_int( $item_data->get_product_id() ) ) 	?  	$item_data->get_product_id() 	: ""; 
				$items[$item_id]['variation_id']	  =  ( method_exists( $item_data, "get_variation_id" ) 	AND 	is_int( $item_data->get_variation_id() ) ) 	?  	$item_data->get_variation_id() 	: ""; 
				$items[$item_id]['product_sku']	  	  =  ( empty( $items[$item_id]['variation_id']) ) 	     ?      get_post_meta( $items[$item_id]['product_id'], '_sku', true ) : get_post_meta( $items[$item_id]['variation_id'], '_sku', true ); 
				$items[$item_id]['product_name']	  =  ( method_exists( $item_data, "get_name" ) 	   		AND 	is_string( $item_data->get_name() ) ) 		?  	$item_data->get_name() 			: ""; 
				$items[$item_id]['qty'] 			  =  ( method_exists( $item_data, "get_quantity" ) 		AND 	is_int( $item_data->get_quantity() ) ) 		?   $item_data->get_quantity() 		: ""; 
				$items[$item_id]['product_qty_price'] =  ( method_exists( $item_data, "get_total" ) 		AND 	is_string( $item_data->get_total() ) ) 		? 	$item_data->get_total() 		: ""; 
				$items[$item_id]['product_weight'] 	  =  ( method_exists( $item_data->get_product(), 'get_weight') AND !empty($item_data->get_product()->get_weight()) ) ? $item_data->get_product()->get_weight()  *  $items[$item_id]['qty'] :  "";
				# getting total weight
				$cart_items_weight[] 				  =  ( method_exists( $item_data->get_product(), 'get_weight') AND !empty($item_data->get_product()->get_weight()) ) ? $item_data->get_product()->get_weight()  *  $items[$item_id]['qty'] :  "";
			}
		}
		# Order Item process Ends
		$order_data['items'] 						=  ( $items ) ? $items : "" ;
		$order_data['cart_items_weight'] 			=  array_sum($cart_items_weight);
		$order_data['item_count'] 			    	=  ( method_exists( $order, 'get_item_count') 			AND 	is_int($order->get_item_count() )) 			? 	$order->get_item_count() : "";
		$order_data['downloadable_items'] 			=  ( method_exists( $order, 'get_downloadable_items' ) 	AND 	! empty($order->get_downloadable_items())	AND	is_array(  $order->get_downloadable_items()) ) 	? 	json_encode( $order->get_downloadable_items()) 	: "";   // Need To Change
		#
		$order_data['date_created'] 				=  ( method_exists( $order, 'get_date_created' ) 		AND 	! empty($order->get_date_created()) 	  	AND	is_string( $order->get_date_created()->date("F j, Y, g:i:s A T") ) ) ? 	$order->get_date_created()->date("F j, Y, g:i:s A T") 	: ""; 
		$order_data['date_modified'] 				=  ( method_exists( $order, 'get_date_modified' ) 		AND 	! empty($order->get_date_modified())   		AND	is_string( $order->get_date_modified()->date("F j, Y, g:i:s A T")) ) ? 	$order->get_date_modified()->date("F j, Y, g:i:s A T") 	: ""; 
		$order_data['date_completed'] 				=  ( method_exists( $order, 'get_date_completed' ) 		AND 	! empty($order->get_date_completed())  		AND	is_string( $order->get_date_completed()->date("F j, Y, g:i:s A T"))) ? 	$order->get_date_completed()->date("F j, Y, g:i:s A T") : "";
		$order_data['date_paid'] 					=  ( method_exists( $order, 'get_date_paid' ) 			AND 	! empty($order->get_date_paid()) 	  		AND	is_string( $order->get_date_paid()->date("F j, Y, g:i:s A T")) ) 	 ? 	$order->get_date_paid()->date("F j, Y, g:i:s A T") 		: "";
		#
		$order_data['user'] 						=  ( method_exists( $order, 'get_user')  				AND  	! empty($order->get_user()) AND is_object( $order->get_user()) ) ? 	$order->get_user()->user_login  . " - " . $order->get_user()->user_email 	: "";
		$order_data['customer_id'] 					=  ( method_exists( $order, 'get_customer_id' ) 		AND 	is_int( $order->get_customer_id() )) 			? 	$order->get_customer_id() 			: "";
		$order_data['user_id'] 						=  ( method_exists( $order, 'get_user_id' ) 			AND 	is_int( $order->get_user_id() )) 				? 	$order->get_user_id()				: "";
		$order_data['customer_ip_address'] 			=  ( method_exists( $order, 'get_customer_ip_address')  AND 	is_string( $order->get_customer_ip_address())) 	? 	$order->get_customer_ip_address()	: "";
		$order_data['customer_user_agent'] 			=  ( method_exists( $order, 'get_customer_user_agent')  AND 	is_string( $order->get_customer_user_agent()))	? 	$order->get_customer_user_agent()	: "";
		$order_data['created_via'] 					=  ( method_exists( $order, 'get_created_via' ) 		AND 	is_string( $order->get_created_via() ))			? 	$order->get_created_via()			: "";
		$order_data['customer_note'] 				=  ( method_exists( $order, 'get_customer_note' ) 		AND 	is_string( $order->get_customer_note() ))		? 	$order->get_customer_note()			: "";
		$order_data['billing_first_name'] 			=  ( method_exists( $order, 'get_billing_first_name' )  AND 	is_string( $order->get_billing_first_name() ))	? 	$order->get_billing_first_name()	: "";
		$order_data['billing_last_name'] 			=  ( method_exists( $order, 'get_billing_last_name' ) 	AND 	is_string( $order->get_billing_last_name() ))	? 	$order->get_billing_last_name()		: "";
		$order_data['billing_company'] 				=  ( method_exists( $order, 'get_billing_company' ) 	AND 	is_string( $order->get_billing_company() ))		? 	$order->get_billing_company()		: "";
		$order_data['billing_address_1'] 			=  ( method_exists( $order, 'get_billing_address_1' ) 	AND 	is_string( $order->get_billing_address_1() ))	? 	$order->get_billing_address_1()		: "";
		$order_data['billing_address_2'] 			=  ( method_exists( $order, 'get_billing_address_2' ) 	AND 	is_string( $order->get_billing_address_2() ))	? 	$order->get_billing_address_2()		: "";
		$order_data['billing_city'] 				=  ( method_exists( $order, 'get_billing_city' ) 		AND 	is_string( $order->get_billing_city() ))		? 	$order->get_billing_city()			: "";
		$order_data['billing_state'] 				=  ( method_exists( $order, 'get_billing_state' ) 		AND 	is_string( $order->get_billing_state() )) 		? 	$order->get_billing_state()			: "";
		$order_data['billing_postcode'] 			=  ( method_exists( $order, 'get_billing_postcode' ) 	AND 	is_string( $order->get_billing_postcode() ))	? 	$order->get_billing_postcode()		: "";
		$order_data['billing_country'] 				=  ( method_exists( $order, 'get_billing_country' ) 	AND 	is_string( $order->get_billing_country() ))		? 	$order->get_billing_country()		: "";
		$order_data['billing_email'] 				=  ( method_exists( $order, 'get_billing_email' ) 		AND 	is_string( $order->get_billing_email() ))		? 	$order->get_billing_email()			: "";
		$order_data['billing_phone'] 				=  ( method_exists( $order, 'get_billing_phone' ) 		AND 	is_string( $order->get_billing_phone()))		? 	$order->get_billing_phone()			: "";
		$order_data['shipping_first_name'] 			=  ( method_exists( $order, 'get_shipping_first_name' ) AND 	is_string( $order->get_shipping_first_name())) 	? 	$order->get_shipping_first_name()	: "";
		$order_data['shipping_last_name'] 			=  ( method_exists( $order, 'get_shipping_last_name' )  AND 	is_string( $order->get_shipping_last_name() ))	? 	$order->get_shipping_last_name()	: "";
		$order_data['shipping_company'] 			=  ( method_exists( $order, 'get_shipping_company' ) 	AND 	is_string( $order->get_shipping_company() ))	?	$order->get_shipping_company()		: "";
		$order_data['shipping_address_1'] 			=  ( method_exists( $order, 'get_shipping_address_1' )  AND 	is_string( $order->get_shipping_address_1() ))	? 	$order->get_shipping_address_1()	: "";
		$order_data['shipping_address_2'] 			=  ( method_exists( $order, 'get_shipping_address_2' )  AND 	is_string( $order->get_shipping_address_2() ))	? 	$order->get_shipping_address_2()	: "";
		$order_data['shipping_city'] 				=  ( method_exists( $order, 'get_shipping_city' ) 		AND 	is_string( $order->get_shipping_city() ))		? 	$order->get_shipping_city()			: "";
		$order_data['shipping_state'] 				=  ( method_exists( $order, 'get_shipping_state' ) 	 	AND 	is_string( $order->get_shipping_state() )) 		? 	$order->get_shipping_state()		: "";
		$order_data['shipping_postcode'] 			=  ( method_exists( $order, 'get_shipping_postcode' ) 	AND 	is_string( $order->get_shipping_postcode() ))	? 	$order->get_shipping_postcode()		: "";
		$order_data['shipping_country'] 			=  ( method_exists( $order, 'get_shipping_country' ) 	AND 	is_string( $order->get_shipping_country() )) 	? 	$order->get_shipping_country()		: "";
		$order_data['address'] 						=  ( method_exists( $order,	'get_address' ) 	 		AND 	! empty( $order->get_address()) AND is_array(  $order->get_address()) ) 	? 	json_encode( $order->get_address()) : "";
		$order_data['shipping_address_map_url'] 	=  ( method_exists( $order, 'get_shipping_address_map_url' ) 	AND	is_string( $order->get_shipping_address_map_url()))			?	$order->get_shipping_address_map_url()			: "";
		$order_data['formatted_billing_full_name'] 	=  ( method_exists( $order, 'get_formatted_billing_full_name' ) AND is_string( $order->get_formatted_billing_full_name() ))		?	$order->get_formatted_billing_full_name()		: "";
		$order_data['formatted_shipping_full_name']	=  ( method_exists( $order, 'get_formatted_shipping_full_name') AND is_string( $order->get_formatted_shipping_full_name() ))	?	$order->get_formatted_shipping_full_name()		: "";
		$order_data['formatted_billing_address'] 	=  ( method_exists( $order, 'get_formatted_billing_address' ) 	AND is_string( $order->get_formatted_billing_address() ))		?	$order->get_formatted_billing_address()			: "";
		$order_data['formatted_shipping_address'] 	=  ( method_exists( $order, 'get_formatted_shipping_address' )  AND is_string( $order->get_formatted_shipping_address() ))		?	$order->get_formatted_shipping_address()		: "";
		#
		$order_data['payment_method'] 				=  ( method_exists( $order, 'get_payment_method' ) 				AND 	is_string( $order->get_payment_method() ))				?	$order->get_payment_method()					: "";
		$order_data['payment_method_title'] 		=  ( method_exists( $order, 'get_payment_method_title' ) 		AND 	is_string( $order->get_payment_method_title() ))		? 	$order->get_payment_method_title()				: "";
		$order_data['transaction_id'] 				=  ( method_exists( $order, 'get_transaction_id' ) 				AND 	is_string( $order->get_transaction_id() ))				? 	$order->get_transaction_id()					: "";
		#
		$order_data['checkout_payment_url'] 		=  ( method_exists( $order, 'get_checkout_payment_url' ) 		AND	is_string( $order->get_checkout_payment_url() ))			? 	$order->get_checkout_payment_url()				: "";
		$order_data['checkout_order_received_url'] 	=  ( method_exists( $order, 'get_checkout_order_received_url') 	AND 	is_string( $order->get_checkout_order_received_url() )) ? 	$order->get_checkout_order_received_url()		: "";
		$order_data['cancel_order_url'] 			=  ( method_exists( $order, 'get_cancel_order_url' ) 			AND 	is_string( $order->get_cancel_order_url() ))			? 	$order->get_cancel_order_url()					: "";
		$order_data['cancel_order_url_raw'] 		=  ( method_exists( $order, 'get_cancel_order_url_raw' ) 		AND 	is_string( $order->get_cancel_order_url_raw()))			? 	$order->get_cancel_order_url_raw()				: "";
		$order_data['cancel_endpoint'] 				=  ( method_exists( $order, 'get_cancel_endpoint' ) 			AND 	is_string( $order->get_cancel_endpoint() ))				? 	$order->get_cancel_endpoint()					: "";
		$order_data['view_order_url'] 				=  ( method_exists( $order, 'get_view_order_url' ) 				AND 	is_string( $order->get_view_order_url() ))				? 	$order->get_view_order_url()					: "";
		$order_data['edit_order_url'] 				=  ( method_exists( $order, 'get_edit_order_url' ) 				AND 	is_string( $order->get_edit_order_url() )) 				? 	$order->get_edit_order_url()					: "";
		#s
		$order_data['status'] 						=  $order->get_status();
		# if Order had ID;
		if ( $order_id ){
			$r = $this->trelloApi->wootrello_create_trello_card( $order_data['status'], $order_data );
			return $r;
		}
	}

	/**
	 * woocommerce_thankyou  Order  HOOK's callback function
	 * I"M USE THIS FOR  Checkout page -> woocommerce_thankyou HOOK for FRONT END
	 * @since    1.0.0
	 * @param     int     $order_id     Order ID
	*/
	public function wootrello_woocommerce_new_order_checkout( $order_id ){
		# Getting Order Details by Order ID
		$order 	=  wc_get_order( $order_id );
		if(! $order){
			$this->helpers->wootrello_log('wootrello_woocommerce_new_order_checkout', 701, 'ERROR: $order is false.');
			return;
		}
		# if not checkout returns.
		if(! in_array($order->get_created_via(), array('checkout', 'store-api'))) {
			return;
		}
		# Order Data Holder;
		$order_data 								=  array();
		$order_data['orderID'] 						= ( method_exists( $order, 'get_id' ) 			  		AND		is_int( $order->get_id()))						? 	$order->get_id()						    : 	"";
		$order_data['cart_tax'] 					= ( method_exists( $order, 'get_cart_tax' ) 	  		AND 	is_string( $order->get_cart_tax()  ))			? 	$order->get_cart_tax() 						: 	"";
		$order_data['currency'] 					= ( method_exists( $order, 'get_currency' ) 	  		AND 	is_string( $order->get_currency()  ))			? 	$order->get_currency() 						:	"";
		$order_data['discount_tax'] 				= ( method_exists( $order, 'get_discount_tax' )   		AND 	is_string( $order->get_discount_tax() ))		?	$order->get_discount_tax() 					:	"";
		$order_data['discount_total'] 				= ( method_exists( $order, 'get_discount_total' ) 		AND 	is_string( $order->get_discount_total() ))		? 	$order->get_discount_total()				:	"";
		$order_data['fees'] 						= ( method_exists( $order, 'get_fees' ) 		  		AND 	is_array(  $order->get_fees()) ) 				?   json_encode( $order->get_fees())			:   "";
		$order_data['shipping_method'] 				= ( method_exists( $order, 'get_shipping_method' )		AND 	is_string( $order->get_shipping_method() ))		? 	$order->get_shipping_method() 				:	"";
		$order_data['shipping_tax'] 				= ( method_exists( $order, 'get_shipping_tax' ) 		AND 	is_string( $order->get_shipping_tax()  ))		? 	$order->get_shipping_tax() 					:	"";
		$order_data['shipping_total'] 				= ( method_exists( $order, 'get_shipping_total' ) 		AND 	is_string( $order->get_shipping_total()  ))		? 	$order->get_shipping_total()				:	"";
		$order_data['subtotal'] 					= ( method_exists( $order, 'get_subtotal' ) 			AND 	is_float( $order->get_subtotal()  ))		  	? 	$order->get_subtotal()						:	"";
		$order_data['subtotal_to_display'] 			= ( method_exists( $order, 'get_subtotal_to_display') 	AND 	is_string( $order->get_subtotal_to_display()))	?   $order->get_subtotal_to_display() 			:   "";
		$order_data['tax_totals'] 					= ( method_exists( $order, 'get_tax_totals' ) 			AND  	! empty($order->get_tax_totals()) 	AND is_array(   $order->get_tax_totals()) ) ?  json_encode( $order->get_tax_totals())   : ""; 
		$order_data['taxes'] 						= ( method_exists( $order, 'get_taxes' ) 				AND  	! empty($order->get_taxes()) 		AND is_array(   $order->get_taxes()) ) 		?  json_encode( $order->get_taxes()) 		: "";  
		$order_data['total'] 						= ( method_exists( $order, 'get_total' ) 				AND 	is_string( $order->get_total() ))			 	?   $order->get_total() 		 				:	"";
		$order_data['total_discount'] 				= ( method_exists( $order, 'get_total_discount' ) 		AND 	is_float( $order->get_total_discount()  ))   	?   $order->get_total_discount() 				:	"";
		$order_data['total_tax'] 					= ( method_exists( $order, 'get_total_tax'  ) 			AND 	is_string( $order->get_total_tax() ))		 	? 	$order->get_total_tax() 	 				:	"";
		$order_data['total_refunded'] 				= ( method_exists( $order, 'get_total_refunded' ) 		AND 	is_float( $order->get_total_refunded() ))	 	? 	$order->get_total_refunded() 				:	"";
		$order_data['total_tax_refunded'] 			= ( method_exists( $order, 'get_total_tax_refunded' ) 	AND 	is_int( $order->get_total_tax_refunded()))	 	?  	$order->get_total_tax_refunded()			:	"";
		$order_data['total_shipping_refunded'] 		= ( method_exists( $order,'get_total_shipping_refunded')AND 	is_int( $order->get_total_shipping_refunded() ))?  	$order->get_total_shipping_refunded() 		:	"";
		$order_data['item_count_refunded'] 			= ( method_exists( $order, 'get_item_count_refunded' ) 	AND 	is_int( $order->get_item_count_refunded() )) 	?  	$order->get_item_count_refunded() 			:	"";
		$order_data['total_qty_refunded'] 			= ( method_exists( $order, 'get_total_qty_refunded' ) 	AND 	is_int( $order->get_total_qty_refunded() ))  	?  	$order->get_total_qty_refunded() 			:	"";
		$order_data['remaining_refund_amount']  	= ( method_exists( $order,'get_remaining_refund_amount')AND 	is_string($order->get_remaining_refund_amount()))?  $order->get_remaining_refund_amount()		:	"";
		# Order Item process Starts.
		if(is_array( $order->get_items())){ 
			$items = array();
			$cart_items_weight = array();
			foreach ( $order->get_items() as $item_id => $item_data ) {
				$items[$item_id]['product_id']		  =  (  method_exists( $item_data, "get_product_id" ) 	AND 	is_int( $item_data->get_product_id() ) ) 	?  	$item_data->get_product_id() 	: ""; 
				$items[$item_id]['variation_id']	  =  (  method_exists( $item_data, "get_variation_id" ) AND 	is_int( $item_data->get_variation_id() ) ) 	?  	$item_data->get_variation_id() 	: ""; 
				$items[$item_id]['product_sku']	  	  =  (  empty( $items[$item_id]['variation_id']) ) 		?  		get_post_meta( $items[$item_id]['product_id'], '_sku', true ) : get_post_meta( $items[$item_id]['variation_id'], '_sku', true )  ; 
				$items[$item_id]['product_name']	  =  (  method_exists( $item_data, "get_name" ) 	   	AND 	is_string( $item_data->get_name() ) ) 		?  	$item_data->get_name() 			: ""; 
				$items[$item_id]['qty'] 			  =  (  method_exists( $item_data, "get_quantity" ) 	AND 	is_int( $item_data->get_quantity() ) ) 		?   $item_data->get_quantity() 		: ""; 
				$items[$item_id]['product_qty_price'] =  (  method_exists( $item_data, "get_total" ) 		AND 	is_string( $item_data->get_total() ) ) 		? 	$item_data->get_total() 		: ""; 
				$items[$item_id]['product_weight'] 	  =  ( method_exists( $item_data->get_product(), 'get_weight') AND !empty($item_data->get_product()->get_weight()) ) ? $item_data->get_product()->get_weight()  *  $items[$item_id]['qty'] :  "";
				# getting total weight
				$cart_items_weight[] 				  =  ( method_exists( $item_data->get_product(), 'get_weight') AND !empty($item_data->get_product()->get_weight()) ) ? $item_data->get_product()->get_weight()  *  $items[$item_id]['qty'] :  "";
			}
		}
		# Order Item process Ends.
		$order_data['items'] 						=  ( $items ) ? $items : "";
		$order_data['cart_items_weight'] 			=  array_sum($cart_items_weight);
		$order_data['item_count'] 			    	=  ( method_exists( $order, 'get_item_count') 			AND 	is_int($order->get_item_count() )) 			? 	$order->get_item_count() : "";
		$order_data['downloadable_items'] 			=  ( method_exists( $order, 'get_downloadable_items' ) 	AND 	! empty($order->get_downloadable_items()) 	AND is_array(  $order->get_downloadable_items()) ) 	? 	json_encode( $order->get_downloadable_items()) 	: "" ;   // Need To Change
		#
		$order_data['date_created'] 				=  ( method_exists( $order, 'get_date_created' ) 		AND 	! empty($order->get_date_created()) 	  	AND	is_string( $order->get_date_created()->date("F j, Y, g:i:s A T") ) ) ? 	$order->get_date_created()->date("F j, Y, g:i:s A T") 	: ""; 
		$order_data['date_modified'] 				=  ( method_exists( $order, 'get_date_modified' ) 		AND 	! empty($order->get_date_modified())   		AND	is_string( $order->get_date_modified()->date("F j, Y, g:i:s A T")) ) ? 	$order->get_date_modified()->date("F j, Y, g:i:s A T") 	: ""; 
		$order_data['date_completed'] 				=  ( method_exists( $order, 'get_date_completed' ) 		AND 	! empty($order->get_date_completed())  		AND	is_string( $order->get_date_completed()->date("F j, Y, g:i:s A T"))) ? 	$order->get_date_completed()->date("F j, Y, g:i:s A T") : "";
		$order_data['date_paid'] 					=  ( method_exists( $order, 'get_date_paid' ) 			AND 	! empty($order->get_date_paid()) 	  		AND	is_string( $order->get_date_paid()->date("F j, Y, g:i:s A T")) ) 	 ? 	$order->get_date_paid()->date("F j, Y, g:i:s A T") 		: "";
		#
		$order_data['user'] 						=  ( method_exists( $order, 'get_user')  				AND  	! empty($order->get_user()) AND is_object( $order->get_user()) ) ? 	$order->get_user()->user_login  . " - " . $order->get_user()->user_email 	: "";
		$order_data['customer_id'] 					=  ( method_exists( $order, 'get_customer_id' ) 		AND 	is_int( $order->get_customer_id() )) 			? 	$order->get_customer_id() 			: "";
		$order_data['user_id'] 						=  ( method_exists( $order, 'get_user_id' ) 			AND 	is_int( $order->get_user_id() )) 				? 	$order->get_user_id()				: "";
		$order_data['customer_ip_address'] 			=  ( method_exists( $order, 'get_customer_ip_address')  AND 	is_string( $order->get_customer_ip_address())) 	? 	$order->get_customer_ip_address()	: "";
		$order_data['customer_user_agent'] 			=  ( method_exists( $order, 'get_customer_user_agent')  AND 	is_string( $order->get_customer_user_agent()))	? 	$order->get_customer_user_agent()	: "";
		$order_data['created_via'] 					=  ( method_exists( $order, 'get_created_via' ) 		AND 	is_string( $order->get_created_via() ))			? 	$order->get_created_via()			: "";
		$order_data['customer_note'] 				=  ( method_exists( $order, 'get_customer_note' ) 		AND 	is_string( $order->get_customer_note() ))		? 	$order->get_customer_note()			: "";
		$order_data['billing_first_name'] 			=  ( method_exists( $order, 'get_billing_first_name' )  AND 	is_string( $order->get_billing_first_name() ))	? 	$order->get_billing_first_name()	: "";
		$order_data['billing_last_name'] 			=  ( method_exists( $order, 'get_billing_last_name' ) 	AND 	is_string( $order->get_billing_last_name() ))	? 	$order->get_billing_last_name()		: "";
		$order_data['billing_company'] 				=  ( method_exists( $order, 'get_billing_company' ) 	AND 	is_string( $order->get_billing_company() ))		? 	$order->get_billing_company()		: "";
		$order_data['billing_address_1'] 			=  ( method_exists( $order, 'get_billing_address_1' ) 	AND 	is_string( $order->get_billing_address_1() ))	? 	$order->get_billing_address_1()		: "";
		$order_data['billing_address_2'] 			=  ( method_exists( $order, 'get_billing_address_2' ) 	AND 	is_string( $order->get_billing_address_2() ))	? 	$order->get_billing_address_2()		: "";
		$order_data['billing_city'] 				=  ( method_exists( $order, 'get_billing_city' ) 		AND 	is_string( $order->get_billing_city() ))		? 	$order->get_billing_city()			: "";
		$order_data['billing_state'] 				=  ( method_exists( $order, 'get_billing_state' ) 		AND 	is_string( $order->get_billing_state() )) 		? 	$order->get_billing_state()			: "";
		$order_data['billing_postcode'] 			=  ( method_exists( $order, 'get_billing_postcode' ) 	AND 	is_string( $order->get_billing_postcode() ))	? 	$order->get_billing_postcode()		: "";
		$order_data['billing_country'] 				=  ( method_exists( $order, 'get_billing_country' ) 	AND 	is_string( $order->get_billing_country() ))		? 	$order->get_billing_country()		: "";
		$order_data['billing_email'] 				=  ( method_exists( $order, 'get_billing_email' ) 		AND 	is_string( $order->get_billing_email() ))		? 	$order->get_billing_email()			: "";
		$order_data['billing_phone'] 				=  ( method_exists( $order, 'get_billing_phone' ) 		AND 	is_string( $order->get_billing_phone()))		? 	$order->get_billing_phone()			: "";
		$order_data['shipping_first_name'] 			=  ( method_exists( $order, 'get_shipping_first_name' ) AND 	is_string( $order->get_shipping_first_name())) 	? 	$order->get_shipping_first_name()	: "";
		$order_data['shipping_last_name'] 			=  ( method_exists( $order, 'get_shipping_last_name' )  AND 	is_string( $order->get_shipping_last_name() ))	? 	$order->get_shipping_last_name()	: "";
		$order_data['shipping_company'] 			=  ( method_exists( $order, 'get_shipping_company' ) 	AND 	is_string( $order->get_shipping_company() ))	?	$order->get_shipping_company()		: "";
		$order_data['shipping_address_1'] 			=  ( method_exists( $order, 'get_shipping_address_1' )  AND 	is_string( $order->get_shipping_address_1() ))	? 	$order->get_shipping_address_1()	: "";
		$order_data['shipping_address_2'] 			=  ( method_exists( $order, 'get_shipping_address_2' )  AND 	is_string( $order->get_shipping_address_2() ))	? 	$order->get_shipping_address_2()	: "";
		$order_data['shipping_city'] 				=  ( method_exists( $order, 'get_shipping_city' ) 		AND 	is_string( $order->get_shipping_city() ))		? 	$order->get_shipping_city()			: "";
		$order_data['shipping_state'] 				=  ( method_exists( $order, 'get_shipping_state' ) 	 	AND 	is_string( $order->get_shipping_state() )) 		? 	$order->get_shipping_state()		: "";
		$order_data['shipping_postcode'] 			=  ( method_exists( $order, 'get_shipping_postcode' ) 	AND 	is_string( $order->get_shipping_postcode() ))	? 	$order->get_shipping_postcode()		: "";
		$order_data['shipping_country'] 			=  ( method_exists( $order, 'get_shipping_country' ) 	AND 	is_string( $order->get_shipping_country() )) 	? 	$order->get_shipping_country()		: "";
		$order_data['address'] 						=  ( method_exists( $order,	'get_address' ) 	 		AND 	! empty( $order->get_address() ) AND is_array(  $order->get_address()) ) ? 	json_encode( $order->get_address()) : "";
		$order_data['shipping_address_map_url'] 	=  ( method_exists( $order, 'get_shipping_address_map_url' ) 	AND	is_string( $order->get_shipping_address_map_url()))			?	$order->get_shipping_address_map_url()		: "";
		$order_data['formatted_billing_full_name'] 	=  ( method_exists( $order, 'get_formatted_billing_full_name')  AND is_string( $order->get_formatted_billing_full_name() ))		?	$order->get_formatted_billing_full_name()	: "";
		$order_data['formatted_shipping_full_name']	=  ( method_exists( $order, 'get_formatted_shipping_full_name') AND is_string( $order->get_formatted_shipping_full_name() ))	?	$order->get_formatted_shipping_full_name()	: "";
		$order_data['formatted_billing_address'] 	=  ( method_exists( $order, 'get_formatted_billing_address' ) 	AND is_string( $order->get_formatted_billing_address() ))		?	$order->get_formatted_billing_address()		: "";
		$order_data['formatted_shipping_address'] 	=  ( method_exists( $order, 'get_formatted_shipping_address' )  AND is_string( $order->get_formatted_shipping_address() ))		?	$order->get_formatted_shipping_address()	: "";
		#
		$order_data['payment_method'] 				=  ( method_exists( $order, 'get_payment_method' ) 				AND 	is_string( $order->get_payment_method() ))				?	$order->get_payment_method()				: "";
		$order_data['payment_method_title'] 		=  ( method_exists( $order, 'get_payment_method_title' ) 		AND 	is_string( $order->get_payment_method_title() ))		? 	$order->get_payment_method_title()			: "";
		$order_data['transaction_id'] 				=  ( method_exists( $order, 'get_transaction_id' ) 				AND 	is_string( $order->get_transaction_id() ))				? 	$order->get_transaction_id()				: "";
		#
		$order_data['checkout_payment_url'] 		=  ( method_exists( $order, 'get_checkout_payment_url' ) 		AND	is_string( $order->get_checkout_payment_url() ))		? 	$order->get_checkout_payment_url()				: "";
		$order_data['checkout_order_received_url'] 	=  ( method_exists( $order, 'get_checkout_order_received_url') 	AND 	is_string( $order->get_checkout_order_received_url() )) ? 	$order->get_checkout_order_received_url()	: "";
		$order_data['cancel_order_url'] 			=  ( method_exists( $order, 'get_cancel_order_url' ) 			AND 	is_string( $order->get_cancel_order_url() ))			? 	$order->get_cancel_order_url()				: "";
		$order_data['cancel_order_url_raw'] 		=  ( method_exists( $order, 'get_cancel_order_url_raw' ) 		AND 	is_string( $order->get_cancel_order_url_raw()))			? 	$order->get_cancel_order_url_raw()			: "";
		$order_data['cancel_endpoint'] 				=  ( method_exists( $order, 'get_cancel_endpoint' ) 			AND 	is_string( $order->get_cancel_endpoint() ))				? 	$order->get_cancel_endpoint()				: "";
		$order_data['view_order_url'] 				=  ( method_exists( $order, 'get_view_order_url' ) 				AND 	is_string( $order->get_view_order_url() ))				? 	$order->get_view_order_url()				: "";
		$order_data['edit_order_url'] 				=  ( method_exists( $order, 'get_edit_order_url' ) 				AND 	is_string( $order->get_edit_order_url() )) 				? 	$order->get_edit_order_url()				: "";
		#
		$order_data['status'] 						= 'new_order';
		# If Order had Order ID
		if($order_id){
			$r = $this->trelloApi->wootrello_create_trello_card( $order_data['status'], $order_data );
			return $r;
		}
	}

	/**
	 * This This AJAX Function Will Create new card from Single Order Page.
	 * @since     3.2.0
	*/
	public function wootrello_ajax_single_order(){
		# Security Check Bro; No way !
		if(wp_verify_nonce($_POST['security'], 'wootrello-ajax-nonce')){
			# Testing is set POST items or not;
			if(isset($_POST['orderID'], $_POST['relatedSettings'])){
				# getting the AJAX value
				$orderID 		 = sanitize_text_field( trim( $_POST['orderID'] ));
				$relatedSettings = sanitize_text_field( trim( $_POST['relatedSettings'] ));
				# if order id and related settings is not empty;
				if($orderID AND $relatedSettings){
					$ret = $this->wootrello_woocommerce_order_status_changed( $orderID, "none", $relatedSettings );
					if ( (is_array( $ret ) AND isset( $ret[0])) AND $ret[0] ){
						echo json_encode( $ret, TRUE );
					}else{
						echo json_encode( $ret, TRUE );
					}
				} 
			} else {
				echo json_encode( array( FALSE, "orderID or relatedSettings is not set." ), TRUE );
			}
		}
		# exit from AJAX query
		exit();
	}
}

// ==================   Notice : this part is for programmers Not for joe's   ==================
// Hello, What are you doing here ? copying code or changing code or What? Looking for Trello API implementation ?
// What about the code quality?  let me know, if possible leave a 5 star review 

// I am from Dhaka, Bangladesh.
// What i know !
// I kow  golang, python, PHP and wordpress and javascript too 
// How may you contact me! my email is jaedmah@gmail.com
// Beautiful Code  is changed by freemius code formatter. sorry for that !
//===============================================================================================