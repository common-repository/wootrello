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
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wootrello-helpers.php';
# Include the helper class
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wootrello-trello-api.php';
#
class Wootrello_Settings {
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
     * WooCommerce Order statuses.
     * @since    1.0.0
     * @access   private
     * @var      string    $key    trello Application key of this plugin.
     */
    private $order_statuses = array(
        'new_order' => 'New checkout page order',
    );

    /**
     * Initialize the class and set its properties.
     * @since      1.0.0
     * @param      string    $plugin_name   The name of this plugin.
     * @param      string    $version    	The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        # plugin name
        $this->plugin_name = $plugin_name;
        # Plugin version
        $this->version = $version;
        # Active plugins
        $this->active_plugins = get_option( 'active_plugins' );
        # Create an instance of the Wootrello_Helpers class
        $this->helpers = new Wootrello_Helpers();
        # Create an instance of the Wootrello_Helpers class
        $this->trelloApi = new Wootrello_Trello_API();
    }

    /**
     * This Function will create Custom post type for saving wpgsi integration and  save wpgsi_ log
     * @since    1.0.0
     */
    public function wootrello_CustomPostType() {
        register_post_type( 'wootrello' );
    }

    /**
     * Register the stylesheets for the admin area.
     * @since    1.0.0
     */
    public function settings_enqueue_styles( $hook ) {
        # Load only WooTrello   ***  important it will stop cross Plugin contamination
        if ( get_current_screen()->id == $hook ) {
            # Plugin CSS link from include folder
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/wootrello-admin.css',
                array(),
                $this->version,
                'all'
            );
            # Multi select CSS file
            wp_enqueue_style(
                'cssForSelect',
                plugin_dir_url( __FILE__ ) . 'css/multiselect.css',
                array(),
                $this->version,
                'all'
            );
        }
        # freemius ends
    }

    /**
     * Register the JavaScript for the admin area.
     * @since    1.0.0
     */
    public function settings_enqueue_scripts( $hook ) {
        # Load only WooTrello only TWO Page OR Admin & Order Edit *** important it will stop cross Plugin contamination
        if ( get_current_screen()->id == $hook or get_current_screen()->id == 'shop_order' ) {
            # Multi Select JS File
            wp_enqueue_script(
                'multiSelectMin',
                plugin_dir_url( __FILE__ ) . 'js/multiselect.min.js',
                array('jquery'),
                $this->version,
                TRUE
            );
            # Default Plugin Scripts
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/wootrello.js',
                array('jquery', 'multiSelectMin'),
                $this->version,
                TRUE
            );
            $wootrello_data = array(
                'wootrelloAjaxURL' => admin_url( 'admin-ajax.php' ),
                'currentPageID'    => get_current_screen()->id,
                'orderID'          => ( isset( $_GET['post'] ) ? $_GET['post'] : FALSE ),
                'security'         => wp_create_nonce( 'wootrello-ajax-nonce' ),
            );
            # Passing Data to the Script
            wp_localize_script( $this->plugin_name, 'wootrello_data', $wootrello_data );
        }
    }

    /**
     * Menu page.
     * @since    1.0.0
     */
    public function Wootrello_menu_pages( $value = '' ) {
        add_menu_page(
            __( 'WooTrello', 'wootrello' ),
            __( 'WooTrello', 'wootrello' ),
            'manage_options',
            'wootrello',
            array($this, 'Wootrello_settings_view'),
            'dashicons-upload'
        );
    }

    /**
     * Menu view Page, URL Router, Log view function, log delete function 
     * This is one of the Most Important function; 
     * @since    2.0.0
     */
    public function Wootrello_settings_view( $value = '' ) {
        # WooTrello Log Status
        $wootrelloLogStatus = get_option( "wootrelloLogStatus" );
        # Wootrello Enable or Disable Logs
        if ( isset( $_GET['action'] ) and $_GET['action'] == 'logStatus' ) {
            if ( $wootrelloLogStatus == 'Enable' ) {
                update_option( "wootrelloLogStatus", "Disable" );
            } else {
                update_option( "wootrelloLogStatus", "Enable" );
            }
            # Then redirect to the Log page Admin with Different URL
            wp_redirect( admin_url( 'admin.php?page=wootrello&action=log' ) );
            exit;
        }
        # if delete log is set than Delete tha Logs
        if ( isset( $_GET['action'] ) and $_GET['action'] == 'deleteLog' ) {
            # Delete the logs
            $wootrello_log = get_posts( array(
                'post_type'      => 'wootrello_log',
                'posts_per_page' => -1,
            ) );
            # Counting Current log
            foreach ( $wootrello_log as $key => $log ) {
                wp_delete_post( $log->ID, TRUE );
            }
            # Then redirect to the Log page Admin with Different URL
            wp_redirect( admin_url( 'admin.php?page=wootrello&action=log' ) );
            exit;
        }
        # Remove all integrations
        if ( isset( $_GET['action'] ) and $_GET['action'] == 'removeInt' ) {
            # getting integrations
            $wootrello_integrations = get_posts( array(
                'post_type'      => 'wootrello',
                'posts_per_page' => -1,
            ) );
            # Counting Current log
            foreach ( $wootrello_integrations as $key => $log ) {
                wp_delete_post( $log->ID, TRUE );
            }
            # Then redirect to the Log page Admin with Different URL
            wp_redirect( admin_url( 'admin.php?page=wootrello' ) );
            exit;
        }
        # This Page will lode Depends on User Request;
        if ( isset( $_GET['action'] ) and $_GET['action'] == 'log' ) {
            # For Log Page
            ?>
				<div class="wrap">
					<h1 class="wp-heading-inline"> Wootrello Log Page 
						<code>last 100 log </code> 
						<?php 
            if ( $wootrelloLogStatus == 'Enable' ) {
                echo " <code><a href='" . admin_url( 'admin.php?page=wootrello&action=logStatus' ) . "' style='opacity: 0.5; color: red;'  >Disable log!</a></code> ";
            } else {
                echo " <code><a href='" . admin_url( 'admin.php?page=wootrello&action=logStatus' ) . "' style='opacity: 0.5; color: green;'  >Enable log</a></code> ";
            }
            ?>
						<code><a style="opacity: 0.5; color: red;" href="<?php 
            echo admin_url( 'admin.php?page=wootrello&action=deleteLog' );
            ?>">remove logs</a></code> 
					</h1>
					<?php 
            if ( $wootrelloLogStatus == 'Disable' ) {
                echo "<h3 style='color:red;' > <span class='dashicons dashicons-dismiss'></span> Log is Disabled ! </h3>";
            }
            $wootrello_log = get_posts( array(
                'post_type'      => 'wootrello_log',
                'order'          => 'DESC',
                'posts_per_page' => -1,
            ) );
            $i = 1;
            foreach ( $wootrello_log as $key => $log ) {
                if ( $log->post_title == 200 ) {
                    echo "<div class='notice notice-success inline'>";
                } else {
                    echo "<div class='notice notice-error inline'>";
                }
                echo "<p><span class='automail-circle'>" . $log->ID;
                echo " .</span>";
                echo "<code>" . $log->post_title . "</code>";
                echo "<code>";
                if ( isset( $log->post_excerpt ) ) {
                    echo $log->post_excerpt;
                }
                echo "</code>";
                echo $log->post_content;
                echo " <code>" . $log->post_date . "</code>";
                echo "</p>";
                echo "</div>";
                $i++;
            }
            ?>
				</div>
			<?php 
        } else {
            # if POST to Log include the Log Page
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wootrello-settings-display.php';
        }
        # Delete log after 100; starts
        $wootrello_log = get_posts( array(
            'post_type'      => 'wootrello_log',
            'posts_per_page' => -1,
        ) );
        # Counting Current log
        if ( (is_array( $wootrello_log ) || is_object( $wootrello_log )) && count( $wootrello_log ) > 100 ) {
            foreach ( $wootrello_log as $key => $log ) {
                if ( $key > 100 ) {
                    wp_delete_post( $log->ID, TRUE );
                }
            }
        }
        # Delete log after 100; ends
    }

    /**
     * Admin notice function;
     * @since    1.0.0
     */
    public function wootrello_settings_notice() {
        if ( isset( get_current_screen()->base ) and get_current_screen()->base == 'toplevel_page_wootrello' ) {
            if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $this->active_plugins ) ) ) {
                echo "<div class='notice notice-error'>";
                echo " <p> <b> <a style='text-decoration: none;' href='https://wordpress.org/plugins/woocommerce'> WooCommerce </a> </b> is not Activate, <a style='text-decoration: none;' href='https://wordpress.org/plugins/wootrello'> WooTrello </a> is for connecting  Woocommerce with Trello ! </p>";
                echo "</div>";
                # ERROR log
                $this->helpers->wootrello_log( 'wootrello_settings_notice', 701, 'ERROR: woocommerce is not installed.' );
            }
        }
        //
    }

    /**
     * wootrello_ajax 
     * @since    1.0.0
     */
    public function wootrello_ajax() {
        # Security Check Bro; No way !
        if ( wp_verify_nonce( $_POST['security'], 'wootrello-ajax-nonce' ) ) {
            # WordPress sanitize_text_field for security
            $boardID = sanitize_text_field( $_POST['boardID'] );
            # Getting Trello API key of the User for Request
            $trello_access_code = get_option( "wootrello_trello_API" );
            # Check and Balance
            if ( empty( $boardID ) or empty( $trello_access_code ) ) {
                # Inserting Log
                $this->helpers->wootrello_log( 'wootrello_ajax', 718, "ERROR: boardID : " . $boardID . " OR access token : " . $trello_access_code . " is empty !" );
                # printing json string
                echo json_encode( array(FALSE, "boardID : " . $boardID . " OR access token : " . $trello_access_code . " is empty !") );
                exit;
            }
            # getting Trello Boards
            $lists = $this->trelloApi->wootrello_board_lists( $trello_access_code, $boardID, 'wootrello_ajax' );
            if ( $lists[0] == 200 ) {
                # Printing Json
                echo json_encode( array(TRUE, $lists[1]), TRUE );
            } else {
                # Inserting Log
                $this->helpers->wootrello_log( 'wootrello_ajax', 719, 'ERROR: ' . json_encode( $lists ) );
                # Printing JSON
                echo json_encode( array(FALSE, "ERROR: Check the log page."), TRUE );
            }
        }
        # End the AJAX request
        exit;
    }

    /**
     *  WooCommerce Orders List Column for letting the User about trello card Status 
     *  @since  3.2.0
     */
    public function wootrello_card_status( $columns ) {
        # if $columns is grater than 5 tha add the item in the number 4 position OR add at the End;
        if ( is_array( $columns ) && count( $columns ) > 5 ) {
            return array_slice(
                $columns,
                0,
                4,
                TRUE
            ) + array(
                'wootrello_card' => 'Trello Info',
            ) + array_slice(
                $columns,
                4,
                count( $columns ) - 1,
                TRUE
            );
        } else {
            $columns['wootrello_card'] = 'Trello Info';
            return $columns;
        }
    }

    /**
     *  Trello card Status Callback function.
     *  @since  3.2.0
     */
    public function wootrello_card_status_callback( $column, $order_id ) {
        if ( 'wootrello_card' === $column ) {
            //
            $order = wc_get_order( $order_id );
            //
            if ( $order ) {
                //
                $metaValue = $order->get_meta( "wootrello_status" );
                # if professional version OR it's an Onclick event
                # echo"<span  data-tip='Click here to create a Trello card for this order.' style='cursor: alias; color: #396b89;'  class='thickbox createCard tips dashicons dashicons-cloud-upload'> </span> ";
                # if error
                if ( isset( $metaValue['wootrello_error'] ) ) {
                    # if there is an error
                    echo " <span data-tip='ERROR: this order &#39; s card is not created in Trello. For more information see the log.' style='cursor: default; color: #FF0038;'  class='tips dashicons dashicons-info-outline'> </span> ";
                    # unset the element
                    unset($metaValue['wootrello_error']);
                }
                # if there is a Successfully card Created
                if ( is_array( $metaValue ) && count( $metaValue ) ) {
                    // $total = array_sum(array_map("count", $metaValue));
                    $total = 0;
                    if ( is_array( $metaValue ) || is_object( $metaValue ) ) {
                        foreach ( $metaValue as $key => $value ) {
                            if ( is_array( $value ) ) {
                                $total += count( $value );
                            }
                        }
                    }
                    //
                    echo " <span data-tip='Trello card created. " . $total . "' style='cursor: default; color: #61B329' class='tips dashicons dashicons-cloud-saved'> </span> ";
                }
            } else {
                # if professional version OR it's an Onclick event
                # echo"<span data-tip='Click here to create a Trello card for this order.' style='cursor: alias; color: #396b89;'  class='tips dashicons dashicons-cloud-upload'> </span>";
            }
        }
    }

    /**
     *  Meta Box inside order detail.
     * @since    3.2.1
     */
    // Creating MetaBox in Single Order Page
    public function wootrello_adding_custom_meta_boxes( $post_type, $post ) {
        if ( 'woocommerce_page_wc-orders' === $post_type ) {
            add_meta_box(
                'wootrello-adding-custom-meta-boxes',
                __( 'Trello Actions', 'wootrello' ),
                array($this, 'wootrello_render_custom_meta_boxes'),
                'woocommerce_page_wc-orders',
                'side',
                'high'
            );
        }
    }

    /**
     *  Meta Box Render.
     * @since    3.2.1
     */
    public function wootrello_render_custom_meta_boxes( $post_or_order_object, $meta_box_info ) {
        #
        $order = ( $post_or_order_object instanceof WP_Post ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object );
        //
        #set order id
        echo "<input type='hidden' id='wootrello_nonce' name='wootrello_nonce' value='" . wp_create_nonce( 'wootrello-ajax-nonce' ) . "'>";
        echo "<input type='hidden' id='wootrello_order_id' name='wootrello_order_id' value='" . $order->get_id() . "'>";
        # Getting Order ID and Trello Order send Status
        if ( function_exists( 'wc_get_order' ) && method_exists( 'WC_Order', 'get_meta' ) ) {
            #
            $Trello_status = $order->get_meta( "wootrello_status" );
            #
            if ( $Trello_status && is_array( $Trello_status ) ) {
                echo "<div class='yeplol' style='background: #efefef; padding: 10px 10px 10px 10px;' >";
                echo " <p style='text-align: center;' ><b> Trello card History </b></p> <br>";
                echo "<span id='trelloHistoryContent'>";
                foreach ( $Trello_status as $statuses => $time_stamps ) {
                    if ( $statuses == 'wootrello_error' ) {
                        echo "<b style='color: #FF0038;'>ERROR: card is not created.</b>";
                    } else {
                        echo "<b>" . $statuses . "</b>";
                    }
                    echo "<br>";
                    if ( is_array( $time_stamps ) ) {
                        foreach ( $time_stamps as $time ) {
                            echo '&nbsp;' . date( 'd/m/Y h:i A', $time );
                            echo "<br>";
                        }
                    }
                }
                echo "<p id='wooTrelloDeleteHistory' style='text-align: center; opacity: 0.4; cursor: pointer;' ><i> Remove History </i></p>";
                echo "</span>";
                echo "</div>";
            }
        }
    }

    /**
     * This is for Processing the Single order page AJAX request 
     * @since     3.2.1
     */
    public function wootrello_ajax_delete_history() {
        //
        if ( function_exists( 'wc_get_order' ) && method_exists( 'WC_Order', 'delete_meta_data' ) ) {
            $order_id = ( isset( $_POST['orderID'] ) ? $_POST['orderID'] : 0 );
            //
            if ( $order_id ) {
                $order = wc_get_order( $order_id );
                if ( $order ) {
                    $order->delete_meta_data( 'wootrello_status' );
                    $order->save();
                    // Log the action
                    $this->helpers->wootrello_log( 'wootrello_ajax_delete_history', 200, 'SUCCESS: ' . $order_id . " This order's Trello history is deleted." );
                    echo json_encode( array(TRUE, "This order's Trello history is deleted."), TRUE );
                } else {
                    echo json_encode( array(FALSE, "Order not found."), TRUE );
                }
            } else {
                echo json_encode( array(FALSE, "Invalid order ID."), TRUE );
            }
        } else {
            echo json_encode( array(FALSE, "Required functions or methods are not available."), TRUE );
        }
        # exit from AJAX query
        exit;
    }

}

// ==================   Notice : this part is for programmers Not for joe's   ==================
// Hello, What are you doing here ? copying code or changing code or What? Looking for Trello API implementation ?
// What about the code quality?  let me know, if possible leave a 5 star review
//
// I am from Dhaka, Bangladesh.
// What i know !
// I kow  golang, python, PHP and wordpress and javascript too
// How may you contact me! my email is jaedmah@gmail.com
// Beautiful Code  is changed by freemius code formatter. sorry for that !
//===============================================================================================