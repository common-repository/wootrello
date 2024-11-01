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
# Include the helper class
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wootrello-helpers.php';
class Wootrello_Trello_API {
    // Property to hold the instance of Wootrello_Helpers
    protected $helpers;

    /**
     * trello Application key of this plugin.
     * @since    1.0.0
     * @access   private
     * @var      string    $key    trello Application key of this plugin.
     */
    private $key = '7385fea630899510fd036b6e89b90c60';

    /**
     * Initialize the class and set its properties.
     * @since      1.0.0
     * @param      string    $plugin_name   The name of this plugin.
     * @param      string    $version    	The version of this plugin.
     */
    public function __construct() {
        # Create an instance of the Wootrello_Helpers class
        $this->helpers = new Wootrello_Helpers();
    }

    /**
     * getting Open Boards
     * @since    2.0.0s
     */
    public function wootrello_trello_boards( $token = '' ) {
        # is there a Token ?
        if ( empty( $token ) ) {
            return array(0, "ERROR: Empty trello token");
        }
        # Constructed URL
        $url = 'https://api.trello.com/1/members/me/boards?&filter=open&key=' . $this->key . '&token=' . $token . '';
        # Remote request
        $trello_returns = wp_remote_get( $url, array() );
        # Boards Holder
        $boards = array();
        # Check & Balance
        if ( !is_wp_error( $trello_returns ) and isset( $trello_returns['response']['code'], $trello_returns['body'] ) and $trello_returns['response']['code'] == 200 ) {
            foreach ( json_decode( $trello_returns['body'], TRUE ) as $key => $value ) {
                $boards[$value['id']] = $value['name'];
            }
            # # return array with two value first one is Bool and second one is data array about boards
            return array($trello_returns['response']['code'], $boards);
        } else {
            # ERROR Log
            $this->helpers->wootrello_log( 'wootrello_trello_boards', 702, 'ERROR: ' . json_encode( $trello_returns, TRUE ) );
            # return two thing First one is Bool and second one is Empty []
            return array(410, array());
        }
    }

    /**
     * Getting Lists calling func
     * @since    2.0.0
     */
    public function wootrello_board_lists( $token = '', $board_id = '', $callingFunc = '' ) {
        #
        if ( empty( $token ) or empty( $board_id ) ) {
            return array(420, 'ERROR: Token or Board id is Empty!');
        }
        # URL
        $url = 'https://api.trello.com/1/boards/' . $board_id . '/lists?filter=open&key=' . $this->key . '&token=' . $token . '';
        # Remote request
        $trello_returns = wp_remote_get( $url, array() );
        $lists = array();
        # Check and Balance
        if ( isset( $trello_returns['response']['code'], $trello_returns['body'] ) and $trello_returns['response']['code'] == 200 ) {
            foreach ( json_decode( $trello_returns['body'], TRUE ) as $key => $value ) {
                $lists[$value['id']] = $value['name'];
            }
        } else {
            # ERROR Log
            $this->helpers->wootrello_log( 'wootrello_board_lists >> ' . $callingFunc, 703, 'ERROR: ' . json_encode( $trello_returns, TRUE ) );
        }
        # return array with two value first one is Bool and second one is data array about board's list
        return array($trello_returns['response']['code'], $lists);
    }

    /**
     * new create trello card;
     * @since    1.0.0
     * @param     string     $order_status     order_status
     * @param     array     $order_info        order_info
     */
    public function wootrello_create_trello_card( $order_status = "", $order_info = array() ) {
        # stop repetition starts OR don't Create Duala Card ;-)
        $wootrello_status = get_post_meta( $order_info['orderID'], 'wootrello_status', TRUE );
        if ( $wootrello_status and isset( $wootrello_status[$order_status] ) ) {
            # getting current timeStamp
            $timestamp = current_time( 'timestamp' );
            # if WP timeStamp is Empty
            if ( empty( $timestamp ) or !is_numeric( $timestamp ) ) {
                $timestamp = time();
            }
            # checking the Thing
            if ( $timestamp - end( $wootrello_status[$order_status] ) < 40 ) {
                $this->helpers->wootrello_log( 'wootrello_create_trello_card', 704, "ERROR: Already created a card on order " . $order_info['orderID'] . " !  Stop duplicate card for new order form checkout page orders. " );
                return array(FALSE, "Stop duplicate card  !");
            }
        }
        # Getting Trello API key
        $wootrello_trello_API = get_option( "wootrello_trello_API" );
        # Getting Related Connection form WP Custom  POST, database Global instance
        global $wpdb;
        # run Query # Getting saved integrations by there title
        $savedIntegration = $wpdb->get_results( "SELECT * FROM `" . $wpdb->posts . "` WHERE post_title  = 'wootrello_" . $order_status . "' AND post_type = 'wootrello';", OBJECT );
        # Check status , trello_board , trello_list have or not OR Decoding Saved Data
        $settings = ( isset( $savedIntegration[0]->post_excerpt ) ? json_decode( $savedIntegration[0]->post_excerpt, TRUE ) : "" );
        # Check card
        $card = ( isset( $savedIntegration[0]->post_excerpt ) ? json_decode( $savedIntegration[0]->post_content, TRUE ) : "" );
        # Check API key Empty or NOt
        if ( empty( $wootrello_trello_API ) ) {
            $this->helpers->wootrello_log( 'wootrello_create_trello_card', 705, "ERROR: No API key  of the User " );
            return array(FALSE, "No API key  of the User !");
        }
        # Check is There Any Saved Data or Not
        if ( !isset( $savedIntegration, $savedIntegration[0], $savedIntegration[0]->post_status ) or empty( $savedIntegration ) ) {
            return array(FALSE, "No data saved on this Status ! ");
        }
        # check status
        if ( !$settings or empty( $settings ) ) {
            $this->helpers->wootrello_log( 'wootrello_create_trello_card', 707, "ERROR: settings Maybe empty. wootrello_" . $order_status );
            return array(FALSE, "settings , card  Maybe empty ! ");
        }
        # Check card is
        if ( !$card or empty( $card ) ) {
            $this->helpers->wootrello_log( 'wootrello_create_trello_card', 708, "ERROR: card  Maybe empty." );
            return array(FALSE, "settings , card  is empty ! ");
        }
        # check settings, trello_board, trello_list is set or not
        if ( !isset( $settings['trello_board'], $settings['trello_list'] ) ) {
            $this->helpers->wootrello_log( 'wootrello_create_trello_card', 709, "ERROR: status , trello_board , trello_list Maybe  not set !" );
            return array(FALSE, "status , trello_board , trello_list Maybe  not set.");
        }
        # intermigration status aka saved post status
        if ( $savedIntegration[0]->post_status != "publish" ) {
            return array(FALSE, "Post status is pending, Not publish");
        }
        # Check settings trello_board is empty or not
        if ( empty( $settings['trello_board'] ) ) {
            $this->helpers->wootrello_log( 'wootrello_create_trello_card', 711, "ERROR: trello_board is empty." );
            return array(FALSE, "trello_board is empty !");
        }
        # Check settings trello_list is empty or not
        if ( empty( $settings['trello_list'] ) ) {
            $this->helpers->wootrello_log( 'wootrello_create_trello_card', 712, "ERROR: trello_list is empty." );
            return array(FALSE, "trello_list is empty !");
        }
        # if Not Professional Stop creating card for Other Event
        if ( !wootrello_freemius()->can_use_premium_code() and $order_status != 'new_order' ) {
            return array(FALSE, "Sorry status change card creation is Professional version only.");
        }
        # trello card title
        $title = ( isset( $order_info['orderID'] ) ? $order_info['orderID'] : "" );
        $title .= ( $card["date"] ? ' # ' . date( "Y/m/d" ) : "" );
        $description = ' ** Order ID :** ' . urlencode( $order_info["orderID"] );
        $description .= ' %0A ** Order status :** ' . urlencode( $order_info["status"] );
        $description .= ( (isset( $card["customer_name"] ) and $card["customer_name"]) ? ' %0A ** Customer name :** ' . urlencode( $order_info["billing_first_name"] ) . " " . urlencode( $order_info["billing_last_name"] ) : "" );
        # billing address
        $description .= ( $card["billing_address"] ? ' %0A ** Billing address :**  %0A ' . urlencode( $order_info["billing_address_1"] ) : '' );
        $description .= ( ($card["billing_address"] and isset( $order_info["billing_address_2"] ) and !empty( $order_info["billing_address_2"] )) ? '  %0A ' . urlencode( $order_info["billing_address_2"] ) : '' );
        $description .= ( ($card["billing_address"] and isset( $order_info["billing_city"] ) and !empty( $order_info["billing_city"] )) ? '  %0A ' . urlencode( $order_info["billing_city"] ) : '' );
        $description .= ( ($card["billing_address"] and isset( $order_info["billing_state"] ) and !empty( $order_info["billing_state"] )) ? '  %0A ' . urlencode( $order_info["billing_state"] ) : '' );
        $description .= ( ($card["billing_address"] and isset( $order_info["billing_postcode"] ) and !empty( $order_info["billing_postcode"] )) ? '  %0A ' . urlencode( $order_info["billing_postcode"] ) : '' );
        $description .= ( ($card["billing_address"] and isset( $order_info["billing_country"] ) and !empty( $order_info["billing_country"] )) ? '  %0A ' . urlencode( $order_info["billing_country"] ) : '' );
        # shipping address
        $description .= ( $card["shipping_address"] ? ' %0A ** Shipping address :**   %0A ' . urlencode( $order_info["shipping_address_1"] ) : '' );
        $description .= ( ($card["shipping_address"] and isset( $order_info["shipping_address_2"] ) and !empty( $order_info["shipping_address_2"] )) ? '  %0A ' . urlencode( $order_info["shipping_address_2"] ) : '' );
        $description .= ( ($card["shipping_address"] and isset( $order_info["shipping_city"] ) and !empty( $order_info["shipping_city"] )) ? '  %0A ' . urlencode( $order_info["shipping_city"] ) : '' );
        $description .= ( ($card["shipping_address"] and isset( $order_info["shipping_state"] ) and !empty( $order_info["shipping_state"] )) ? '  %0A ' . urlencode( $order_info["shipping_state"] ) : '' );
        $description .= ( ($card["shipping_address"] and isset( $order_info["shipping_postcode"] ) and !empty( $order_info["shipping_postcode"] )) ? '  %0A ' . urlencode( $order_info["shipping_postcode"] ) : '' );
        $description .= ( ($card["shipping_address"] and isset( $order_info["shipping_country"] ) and !empty( $order_info["shipping_country"] )) ? '  %0A ' . urlencode( $order_info["shipping_country"] ) : '' );
        # Payment Details
        $description .= ( (isset( $card["payment_method"] ) and $card["payment_method"]) ? ' %0A ** Payment method :** ' . urlencode( $order_info["payment_method"] ) : '' );
        $description .= ( (isset( $card["order_total"] ) and $card["order_total"]) ? ' %0A ** Order total  :** ' . urlencode( $order_info["total"] ) . " " . urlencode( $order_info['currency'] ) : "" );
        #  Order Meta Information And Previous order History ends
        #3rd party orders starts  "Checkout Field Editor (Checkout Manager) for WooCommerce" | "https://wordpress.org/plugins/woo-checkout-field-editor-pro/"
        $woo_checkout_field_editor_fields = $this->helpers->wootrello_woo_checkout_field_editor_pro_fields();
        #3rd party orders ends  "Checkout Field Editor ( Checkout Manager ) for WooCommerce"
        # URL
        $card_url = 'https://api.trello.com/1/cards?name=' . urlencode( $title ) . '&desc=' . $description . '&pos=top&idList=' . $settings['trello_list'] . '&keepFromSource=all&key=' . $this->key . '&token=' . $wootrello_trello_API . '';
        # Execute Remote request
        $trello_response = wp_remote_post( $card_url, array() );
        #
        if ( !is_wp_error( $trello_response ) and isset( $trello_response['response']['code'], $trello_response['body'] ) and $trello_response['response']['code'] == 200 ) {
            # Request is successful
            # Getting the New Created Card Id ;
            $trello_response_body = json_decode( $trello_response['body'], TRUE );
            # check and balance
            if ( isset( $trello_response_body['id'] ) and $trello_response_body['id'] ) {
                # URL builder
                $check_list_url = 'https://api.trello.com/1/cards/' . $trello_response_body['id'] . '/checklists?name=Order Items&pos=top&key=' . $this->key . '&token=' . $wootrello_trello_API . '';
                # Remote request for trello check list
                $trello_checklist_response = wp_remote_post( $check_list_url, array() );
                # if request has error or body is not set
                if ( is_wp_error( $trello_checklist_response ) or !isset( $trello_checklist_response['body'] ) ) {
                    # keeping log
                    $this->helpers->wootrello_log( 'wootrello_create_trello_card', 714, "ERROR: trello_checklist_response has error or response body is not set. " );
                    # return false
                    return array("FALSE", "ERROR: trello_checklist_response has error or response body is not set");
                }
                # JSON Decode the trello check list body.
                $trello_checklist_response_body = json_decode( $trello_checklist_response['body'], TRUE );
                # Now Creating Product Check list Items;
                if ( isset( $trello_checklist_response_body['id'] ) and $trello_checklist_response_body['id'] and !empty( $order_info['items'] ) ) {
                    # Insert The Checklist Items trello_checklist_response
                    $i = 1;
                    foreach ( $order_info['items'] as $order_item ) {
                        # URL builder
                        $url = '(' . get_edit_post_link( $order_item["product_id"] ) . ')';
                        $product = "";
                        $product .= ( $card["product_serial_number"] ? $i . ' - ' : "" );
                        $product .= ( $card["product_id"] ? $order_item["product_id"] . ' - ' : "" );
                        # product name
                        $product .= '[' . urlencode( $order_item["product_name"] ) . '](' . get_permalink( $order_item["product_id"] ) . ')';
                        # Product QTY
                        $product .= ( $card["product_qty"] ? ' qty - ' . $order_item["qty"] . ',' : "" );
                        // Here will be Custom Code Stats
                        // Get Prodcts Extra Information from Other DB Table
                        // Appand That To [$product .=] variable
                        //
                        # URL
                        $trello_list_item_url = 'https://api.trello.com/1/checklists/' . $trello_checklist_response_body['id'] . '/checkItems?name=' . $product . '&pos=top&checked=false&key=' . $this->key . '&token=' . $wootrello_trello_API . '';
                        # Requesting
                        wp_remote_post( $trello_list_item_url, array() );
                        $i++;
                    }
                } else {
                    # inserting log OR if array or object convert to JSON
                    if ( is_array( $trello_checklist_response ) or is_object( $trello_checklist_response ) ) {
                        $this->helpers->wootrello_log( 'wootrello_create_trello_card', 714, 'ERROR: trello_checklist_response_body-id is not set or Empty. or  order_info - items are empty. ' . json_encode( $trello_checklist_response ) );
                    } else {
                        $this->helpers->wootrello_log( 'wootrello_create_trello_card', 714, 'ERROR: trello_checklist_response_body-id is not set or Empty. or  order_info - items are empty. ' . $trello_checklist_response );
                    }
                }
                # write order status ok and Current timestamp on the order meta
                $this->helpers->wootrello_write_status_on_order_meta( $order_info['orderID'], $order_info["status"] );
                # SuccessFully Card Created
                $this->helpers->wootrello_log( 'wootrello_create_trello_card', 200, 'SUCCESS: card created successfully!' );
                # return true
                return array('TRUE', "it seems everything is okay! ");
            } else {
                # Inserting Log
                $this->helpers->wootrello_log( 'wootrello_create_trello_card', 715, 'ERROR: Trello response is Empty' );
                # return true
                return array('FALSE', "Trello response is Empty!");
            }
        } else {
            # New Code Starts
            $this->helpers->wootrello_write_status_on_order_meta( $order_info['orderID'], "wootrello_error" );
            # New Code ends
            $this->helpers->wootrello_log( 'wootrello_create_trello_card', 716, 'ERROR: ' . json_encode( $trello_response ) );
            # return true
            return array('FALSE', "Trello wootrello_error!");
        }
    }

}
