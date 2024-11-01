<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h1><?php 
esc_attr_e( 'Wootrello settings page !', 'WpAdminStyle' );
?></h1>
    <!-- Data Processing Code Starts -->
    <?php 
# 3rd party Plugin Fields
$woo_checkout_field_editor_fields = $this->helpers->wootrello_woo_checkout_field_editor_pro_fields();
# Security Check & Status ID Check
if ( isset( $_POST['wootrello_nonce'], $_POST['statusID'] ) and wp_verify_nonce( $_POST['wootrello_nonce'], 'wootrello_nonce_value' ) and !empty( $_POST['statusID'] ) ) {
    # security sanitize Post ID
    $statusID = sanitize_text_field( $_POST['statusID'] );
    $settings = array();
    $settings['trello_board'] = ( (isset( $_POST['trello_board'] ) and !empty( $_POST['trello_board'] )) ? sanitize_text_field( $_POST['trello_board'] ) : FALSE );
    $settings['trello_list'] = ( (isset( $_POST['trello_list'] ) and !empty( $_POST['trello_list'] )) ? sanitize_text_field( $_POST['trello_list'] ) : FALSE );
    $settings['trello_due_date'] = ( (isset( $_POST['trello_due_date'] ) and !empty( $_POST['trello_due_date'] )) ? sanitize_text_field( $_POST['trello_due_date'] ) : FALSE );
    $settings['trello_label_colour'] = ( (isset( $_POST['trello_label_colour'] ) and !empty( $_POST['trello_label_colour'] )) ? sanitize_text_field( $_POST['trello_label_colour'] ) : FALSE );
    #
    $card = array();
    $card['date'] = ( (isset( $_POST['date'] ) and !empty( $_POST['date'] )) ? TRUE : FALSE );
    $card['customer_name_in_title'] = ( (isset( $_POST['customer_name_in_title'] ) and !empty( $_POST['customer_name_in_title'] )) ? TRUE : FALSE );
    $card['order_total_in_title'] = ( (isset( $_POST['order_total_in_title'] ) and !empty( $_POST['order_total_in_title'] )) ? TRUE : FALSE );
    # order_total_in_title
    $card['order_url'] = ( (isset( $_POST['order_url'] ) and !empty( $_POST['order_url'] )) ? TRUE : FALSE );
    $card['customer_name'] = ( (isset( $_POST['customer_name'] ) and !empty( $_POST['customer_name'] )) ? TRUE : FALSE );
    $card['customer_note'] = ( (isset( $_POST['customer_note'] ) and !empty( $_POST['customer_note'] )) ? TRUE : FALSE );
    $card['billing_address'] = ( (isset( $_POST['billing_address'] ) and !empty( $_POST['billing_address'] )) ? TRUE : FALSE );
    $card['billing_email'] = ( (isset( $_POST['billing_email'] ) and !empty( $_POST['billing_email'] )) ? TRUE : FALSE );
    $card['billing_phone'] = ( (isset( $_POST['billing_phone'] ) and !empty( $_POST['billing_phone'] )) ? TRUE : FALSE );
    $card['shipping_address'] = ( (isset( $_POST['shipping_address'] ) and !empty( $_POST['shipping_address'] )) ? TRUE : FALSE );
    $card['payment_method'] = ( (isset( $_POST['payment_method'] ) and !empty( $_POST['payment_method'] )) ? TRUE : FALSE );
    $card['shipping_method'] = ( (isset( $_POST['shipping_method'] ) and !empty( $_POST['shipping_method'] )) ? TRUE : FALSE );
    $card['shipping_total'] = ( (isset( $_POST['shipping_total'] ) and !empty( $_POST['shipping_total'] )) ? TRUE : FALSE );
    $card['discount_total'] = ( (isset( $_POST['discount_total'] ) and !empty( $_POST['discount_total'] )) ? TRUE : FALSE );
    $card['cart_items_weight'] = ( (isset( $_POST['cart_items_weight'] ) and !empty( $_POST['cart_items_weight'] )) ? TRUE : FALSE );
    $card['order_total'] = ( (isset( $_POST['order_total'] ) and !empty( $_POST['order_total'] )) ? TRUE : FALSE );
    $card['previous_orders'] = ( (isset( $_POST['previous_orders'] ) and !empty( $_POST['previous_orders'] )) ? TRUE : FALSE );
    #
    $card['product_serial_number'] = ( (isset( $_POST['serial_number'] ) and !empty( $_POST['serial_number'] )) ? TRUE : FALSE );
    $card['product_id'] = ( (isset( $_POST['product_id'] ) and !empty( $_POST['product_id'] )) ? TRUE : FALSE );
    $card['product_sku'] = ( (isset( $_POST['product_sku'] ) and !empty( $_POST['product_sku'] )) ? TRUE : FALSE );
    $card['product_qty'] = ( (isset( $_POST['product_qty'] ) and !empty( $_POST['product_qty'] )) ? TRUE : FALSE );
    $card['product_weight'] = ( (isset( $_POST['product_weight'] ) and !empty( $_POST['product_weight'] )) ? TRUE : FALSE );
    $card['product_qty_price'] = ( (isset( $_POST['product_qty_price'] ) and !empty( $_POST['product_qty_price'] )) ? TRUE : FALSE );
    # Post status  publish or pending
    $post_status = ( (isset( $_POST['status'] ) and $_POST['status'] = 'publish') ? "publish" : "pending" );
    # database Global instance
    global $wpdb;
    # run Query # Getting saved integrations by there title
    $savedIntegration = $wpdb->get_results( "SELECT * FROM `" . $wpdb->posts . "` WHERE post_title  = '" . $statusID . "' AND post_type = 'wootrello';", OBJECT );
    # creating input data array
    $postData = array(
        'post_title'   => wp_strip_all_tags( $statusID ),
        'post_content' => json_encode( $card ),
        'post_excerpt' => json_encode( $settings ),
        'post_status'  => $post_status,
        'post_type'    => 'wootrello',
    );
    # If There are No Settings than Insert if Have settings Update
    if ( isset( $savedIntegration[0], $savedIntegration[0]->ID ) and !empty( $savedIntegration[0]->ID ) ) {
        $postData['ID'] = $savedIntegration[0]->ID;
        $r = wp_update_post( $postData );
    } else {
        $r = wp_insert_post( $postData );
    }
    # unset aka clearing some memory; losing some weight
    unset($_POST);
    unset($settings);
    unset($card);
    unset($metaKeysTMP);
}
# Saving Trello API Starts
if ( isset( $_POST['wootrello_api_nonce'], $_POST['trello_api'] ) and wp_verify_nonce( $_POST['wootrello_api_nonce'], 'wootrello_settings_api_nonce_value' ) ) {
    $trello_API = ( (isset( $_POST['trello_api'] ) and !empty( $_POST['trello_api'] )) ? sanitize_text_field( trim( $_POST['trello_api'] ) ) : FALSE );
    update_option( 'wootrello_trello_API', $trello_API );
}
# getting data from database;
$wootrello_trello_API = get_option( "wootrello_trello_API" );
# Trello Board list;
$trello_boards = $this->trelloApi->wootrello_trello_boards( $wootrello_trello_API );
?>
    <!-- Data Processing Code Ends -->
  
    <div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<!-- main content -->
			<div id="post-body-content">
                <!-- Forms starts -->
				<?php 
# This Variable will Count all the connections;
$total_connection = 0;
# Looping the order statuses
foreach ( $this->order_statuses as $status_key => $status_value ) {
    # database Global instance
    global $wpdb;
    # run Query # Getting saved integrations by there title
    $result = $wpdb->get_results( "SELECT * FROM `" . $wpdb->posts . "` WHERE post_type = 'wootrello' AND post_title = 'wootrello_" . $status_key . "';", ARRAY_A );
    #
    # new code, if not set or empty skype this
    if ( isset( $result[0] ) and !empty( $result[0] ) ) {
        $saved_value = $result[0];
    } else {
        $saved_value = array();
    }
    # Empty Holders
    $saved_settings = "";
    $saved_card = "";
    //
    if ( !empty( $saved_value ) ) {
        $saved_settings = @json_decode( $saved_value['post_excerpt'], TRUE );
        $saved_card = @json_decode( $saved_value['post_content'], TRUE );
        # Counting Total integrations
        if ( isset( $saved_settings['trello_board'], $saved_settings['trello_list'] ) and $saved_value['post_status'] == 'publish' and !empty( $saved_settings['trello_board'] ) and !empty( $saved_settings['trello_list'] ) ) {
            $total_connection++;
        }
    }
    echo "<form name='wootrello_settings' method='POST' action='' >";
    # Nonce fields
    echo "<input type='hidden'  name='wootrello_nonce' value='" . wp_create_nonce( 'wootrello_nonce_value' ) . "'>";
    echo "<input type='hidden'  name='statusID' value='wootrello_" . $status_key . "'>";
    # প্যানেল স্টার্ট
    echo "<div class='meta-box-sortables ui-sortable'>";
    echo "<div class='postbox'>";
    // echo "<button type='button' class='handlediv' aria-expanded='true' >";
    //     echo "<span class='screen-reader-text'>Toggle panel</span>";
    //     echo "<span class='toggle-indicator' aria-hidden='true'></span>";
    // echo "</button>";
    // <!-- Toggle -->
    echo "<h2 class='hndle'><span> " . $status_value . " </span></h2>";
    echo "<div class='inside'>";
    //  Form Fields Starts from Here ;
    echo "<table class='widefat' style='width: 100%; border-collapse: collapse;' >";
    echo "<thead>";
    echo "<tr>";
    echo "<th class='row-title'>";
    echo "Enable | Disable ";
    echo "</th>";
    echo "<th>";
    echo "Trello Boards";
    echo "</th>";
    echo "<th>";
    echo "Trello List";
    echo "</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    echo "<tr class='alternate'>";
    echo "<td>";
    # Enable & Disable
    if ( isset( $saved_value['post_status'] ) and $saved_value['post_status'] == 'publish' ) {
        echo "<input  type='checkbox' name='status' id='" . $status_key . "_status' value='publish' checked  />";
    } else {
        echo "<input  type='checkbox' name='status' id='" . $status_key . "_status' value='pending' />";
    }
    echo "</td>";
    echo "<td>";
    # Select Trello Board
    echo "<select  name='trello_board' id='wootrello_" . $status_key . "' class='trelloBoard'>";
    echo "<option value=''> -- Select Trello Board -- </option>";
    if ( $trello_boards[0] == 200 and !empty( $trello_boards[1] ) ) {
        foreach ( $trello_boards[1] as $key => $value ) {
            if ( isset( $saved_settings['trello_board'] ) and $saved_settings['trello_board'] == $key ) {
                echo "<option value='" . $key . "' selected > " . addslashes( $value ) . "</option>";
            } else {
                echo "<option value='" . $key . "'> " . addslashes( $value ) . "</option>";
            }
        }
    }
    echo "</select>";
    echo "</td>";
    echo "<td>";
    # Select Trello List
    echo "<select  name='trello_list'  id='wootrello_" . $status_key . "_trello_list' class='trello_list'>";
    if ( isset( $saved_settings['trello_board'] ) and $saved_settings['trello_board'] ) {
        $lists = $this->trelloApi->wootrello_board_lists( $wootrello_trello_API, $saved_settings['trello_board'], 'Settings Display page' );
        # if Request is OK
        if ( $lists[0] == 200 ) {
            echo "<option selected='selected' value=''> -- Select Trello List -- </option>";
            foreach ( $lists[1] as $listKey => $listName ) {
                if ( isset( $saved_settings, $saved_settings['trello_list'] ) and $saved_settings['trello_list'] == $listKey ) {
                    echo "<option value='" . $listKey . "' selected>" . $listName . "</option>";
                } else {
                    echo "<option value='" . $listKey . "'>" . $listName . "</option>";
                }
            }
        } else {
            echo "<option selected='selected' value=''> -- Select Trello List - </option>";
        }
    } else {
        echo "<option selected='selected' value=''> -- Select Trello List -- </option>";
    }
    echo "</select>";
    echo "</td>";
    echo "</tr>";
    echo "</tbody>";
    echo "</table>";
    echo "<br class='clear'>";
    # Another Table Starts
    echo "<table class='widefat'>";
    echo "<tbody>";
    echo "<tr class='alternate'>";
    echo "<td class='row-title'><label for='tablecell'> Title of the card </label></td>";
    echo "<td>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Date </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['date'] ) and $saved_card['date'] ) {
        echo "<input type='checkbox' name='date'  id='" . $status_key . "_date' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='date'  id='" . $status_key . "_date' value='1' />";
    }
    echo "<span> Date </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='row-title'><label for='tablecell'> Description of the card </label></td>";
    echo "<td>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Customer name </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['customer_name'] ) and $saved_card['customer_name'] ) {
        echo "<input type='checkbox' name='customer_name'  id='" . $status_key . "_customer_name' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='customer_name'  id='" . $status_key . "_customer_name' value='1' />";
    }
    echo "<span> Customer name </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Billing Address  </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['billing_address'] ) and $saved_card['billing_address'] ) {
        echo "<input type='checkbox' name='billing_address'  id='" . $status_key . "_billing_address' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='billing_address'  id='" . $status_key . "_billing_address' value='1' />";
    }
    echo "<span> Billing Address </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Shipping Address  </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['shipping_address'] ) and $saved_card['shipping_address'] ) {
        echo "<input type='checkbox' name='shipping_address'  id='" . $status_key . "_shipping_address' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='shipping_address'  id='" . $status_key . "_shipping_address' value='1' />";
    }
    echo "<span> Shipping Address </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Payment method </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['payment_method'] ) and $saved_card['payment_method'] ) {
        echo "<input type='checkbox' name='payment_method'  id='" . $status_key . "_payment_method' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='payment_method'  id='" . $status_key . "_payment_method' value='1' />";
    }
    echo "<span> Payment method  </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Order total </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['order_total'] ) and $saved_card['order_total'] ) {
        echo "<input type='checkbox' name='order_total'  id='" . $status_key . "_order_total' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='order_total'  id='" . $status_key . "_order_total' value='1' />";
    }
    echo "<span>  Order total </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "</td>";
    echo "</tr>";
    echo "<tr class='alternate'>";
    echo "<td class='row-title'> Product check list </td>";
    echo "<td>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Display Serial Number  </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['product_serial_number'] ) and $saved_card['product_serial_number'] ) {
        echo "<input type='checkbox' name='serial_number'  id='" . $status_key . "_serial_number' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='serial_number'  id='" . $status_key . "_serial_number' value='1' />";
    }
    echo "<span> Display Serial Number  </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Display Product ID </span> </legend>";
    echo "<label for='users_can_register'>";
    if ( isset( $saved_card['product_id'] ) and $saved_card['product_id'] ) {
        echo "<input type='checkbox' name='product_id'  id='" . $status_key . "_display_product_id' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='product_id'  id='" . $status_key . "_display_product_id' value='1' />";
    }
    echo "<span> Display Product ID </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'> <span> Display Product Qty </span> </legend>";
    echo "<label for='product_qty'>";
    if ( isset( $saved_card['product_qty'] ) and $saved_card['product_qty'] ) {
        echo "<input type='checkbox' name='product_qty'  id='" . $status_key . "_product_qty' checked value='1' />";
    } else {
        echo "<input type='checkbox' name='product_qty'  id='" . $status_key . "_product_qty' value='1' />";
    }
    echo "<span> Display Product Qty </span>";
    echo "</label>";
    echo "</fieldset>";
    echo "</td>";
    echo "</tr>";
    //  Fields Ends Here
    echo "</tbody>";
    echo "</table>";
    echo "<br class='clear'>";
    # Form Submit button;
    echo "<input class='button-secondary'  type='submit' id='" . $status_key . "_save_settings' name='save_settings' value='Save " . $status_value . " settings'  />";
    echo "</div>";
    // <!-- .inside -->
    echo "</div>";
    // <!-- .postbox -->
    echo "</div>";
    # প্যানেল  এন্ড্স
    echo "</form>";
}
?>
			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<!-- <button type="button" class="handlediv" aria-expanded="true" >
							<span class="screen-reader-text">Toggle panel</span>
							<span class="toggle-indicator" aria-hidden="true"></span> 
                        </button> -->
                        
						<!-- Toggle -->
						<h2 class="hndle"><span><?php 
esc_attr_e( 'Trello access code', 'WpAdminStyle' );
?></span></h2>

						<div class="inside">
							<p>
                                <form name='trello_api_form' method='POST' action=''>
								<?php 
# Nonce fields
wp_nonce_field( 'wootrello_settings_api_nonce_value', 'wootrello_api_nonce' );
#
if ( isset( $wootrello_trello_API ) and !empty( $wootrello_trello_API ) ) {
    echo " <input type='text' name='trello_api' id='trello_api'  value='" . $wootrello_trello_API . "' style='width: 100%; height: 3em;' />";
    echo "<br class='clear' />";
    echo "<br class='clear' />";
    echo "<input class='button-secondary'  type='submit' id='save_settings' name='save_settings' value='save'  />";
    echo " &#32; &#32; <span title='to remove the Trello access code, empty the input field then save the empty field.' class='dashicons dashicons-editor-help'></span>";
} else {
    echo "<a href='https://trello.com/1/authorize?expiration=never&name=Wootrello&scope=read%2Cwrite&response_type=token&key=7385fea630899510fd036b6e89b90c60'  style='margin-left:150px; text-decoration: none; ' target='_blank'>Trello access code</a>";
    echo " <input type='text' name='trello_api' id='trello_api' placeholder='Pest trello access code here.'  value='' style='width: 100%; height: 3em;' />";
    echo "<br class='clear' />";
    echo "<br class='clear' />";
    echo "<input class='button-secondary'  type='submit' id='save_settings' name='save_settings' value='save access code'  />";
}
?>
                                </form>
                            </p>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

                <div class="meta-box-sortables">

					<div class="postbox">
						<div class="inside">
							<p> <?php 
echo "<b>Active Connection : <code> " . $total_connection . " </code>  </b>";
?> </p>
                            <?php 
if ( $total_connection ) {
    echo "<code> <a href=" . admin_url( 'admin.php?page=wootrello&action=removeInt' ) . " style='opacity: 0.5; color: red;'> Remove all integrations </a> </code>";
}
?>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->
				</div>
				<!-- .meta-box-sortables -->

                <div class="meta-box-sortables">

					<div class="postbox">

						<!-- <button type="button" class="handlediv" aria-expanded="true" >
							<span class="screen-reader-text">Toggle panel</span>
							<span class="toggle-indicator" aria-hidden="true"></span>
                        </button> -->
                        
						<!-- Toggle -->
						<h2 class="hndle"><span><?php 
esc_attr_e( 'Hello, Howdy', 'WpAdminStyle' );
?></span> <span class="dashicons dashicons-smiley"></span></h2>

						<div class="inside">
							<p>
                                <i>
                                    This Plugin has <b> 17 </b> files and  <b>3,145‬</b> lines of code, Trello changed its API in many ways, so I follow with the new API.
                                    Development, Testing, and Debugging takes a lot of time & patience. 
                                    I hope you will appreciate my effort. 
                                    
                                    <!-- For Paid User  -->
                                    <?php 
?>
                                        
                                    <!-- for Free and Trial  user  -->
                                    <?php 
if ( wootrello_freemius()->is_trial() or wootrello_freemius()->is_not_paying() ) {
    ?>
                                        If possible Please purchase the <?php 
    echo '<a style="text-decoration: none;" href="' . wootrello_freemius()->get_upgrade_url() . '">' . __( ' Professional copy', 'my-text-domain' ) . '</a>';
    ?>, 
                                        if not please leave a <a style='text-decoration: none;' href='https://wordpress.org/support/plugin/wootrello/reviews/?filter=5'> 5-star review </a>, It will inspire me to add more awesome feature . 
                                            
                                        <br> 
                                        <br> 
                                        thank you & best regards.
                                        <br>
                                        <br>
                                        <b>P.S :</b> <a style="text-decoration: none;" href=" <?php 
    echo admin_url( 'admin.php?page=wootrello-contact' );
    ?> "> let me know your questions & thoughts.</a> 
                                    <?php 
}
?>

                                </i>
                            </p>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->
				</div>
				<!-- .meta-box-sortables -->

                <!-- Basic Version error note -->
                <?php 
if ( wootrello_freemius()->is_trial() or wootrello_freemius()->is_not_paying() ) {
    ?>
                    <span> 
                        <i> Wootrello use <a style='text-decoration: none;' href='https://github.com/woocommerce/woocommerce/blob/master/templates/checkout/thankyou.php'> woocommerce_thankyou </a> Hook for Checkout Page order so it will  <b>  not </b> work 
                        without any thank you page. please make sure you have a thank you page. 
                        </i> 
                    </span> 
                    <br>
                    <br>
                <?php 
}
?>

                <span style='float:right; padding-right:25px;'> <a href="<?php 
echo admin_url( 'admin.php?page=wootrello&action=log' );
?>" style='text-decoration: none;font-style: italic;'  > log for good ! log page.  </a> </span>
                <!-- Log Code Ends -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->
		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
</div> <!-- .wrap -->

<!-- Link for help  -->
<!-- https://wordpress.stackexchange.com/questions/58834/echo-all-meta-keys-of-a-custom-post-type  -->
<!-- https://codepen.io/rajatsansar/pen/NZJMoW -->
<!-- https://www.cssscript.com/multiselect-dropdown-list-checkboxes-multiselect-js/ -->
