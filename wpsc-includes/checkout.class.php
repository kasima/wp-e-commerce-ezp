<?php
/**
 * WP eCommerce checkout class
 *
 * These are the class for the WP eCommerce checkout
 * The checkout class handles dispaying the checkout form fields
 *
 * @package wp-e-commerce
 * @subpackage wpsc-checkout-classes 
*/
function wpsc_google_checkout_submit(){
	global $wpdb,  $wpsc_cart, $current_user;
	$wpsc_checkout = new wpsc_checkout();
	$purchase_log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` IN('".$_SESSION['wpsc_sessionid']."') LIMIT 1") ;
	//$purchase_log_id = 1;
	get_currentuserinfo();
	//	exit('<pre>'.print_r($current_user, true).'</pre>');
	if($current_user->display_name != ''){
		foreach($wpsc_checkout->checkout_items as $checkoutfield){
		//	exit(print_r($checkoutfield,true));
			if($checkoutfield->unique_name == 'billingfirstname'){
				$checkoutfield->value = $current_user->display_name;
			}
		}	
	}
	if($current_user->user_email != ''){
		foreach($wpsc_checkout->checkout_items as $checkoutfield){
		//	exit(print_r($checkoutfield,true));
			if($checkoutfield->unique_name == 'billingemail'){
				$checkoutfield->value = $current_user->user_email;
			}
		}	
	}

	$wpsc_checkout->save_forms_to_db($purchase_log_id);
	$wpsc_cart->save_to_db($purchase_log_id);
	$wpsc_cart->submit_stock_claims($purchase_log_id);

}
function wpsc_have_checkout_items() {
	global $wpsc_checkout;
	return $wpsc_checkout->have_checkout_items();
}

function wpsc_the_checkout_item() {
	global $wpsc_checkout;
	return $wpsc_checkout->the_checkout_item();
}
function wpsc_is_shipping_details(){
	global $wpsc_checkout;
	if($wpsc_checkout->checkout_item->unique_name == 'delivertoafriend' && get_option('shippingsameasbilling') == '1'){
		return true;
	}else{
		return false;
	}
	
}
function wpsc_shipping_details(){
	global $wpsc_checkout;
	if(stristr($wpsc_checkout->checkout_item->unique_name, 'shipping') != false){

	return ' wpsc_shipping_forms';
	}else{
	return "";
	}
	
}
function wpsc_the_checkout_item_error_class($as_attribute = true) {
	global $wpsc_checkout;
	if($_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id] != '') {
	  $class_name = 'validation-error';
	}
	if(($as_attribute == true)){
	 $output = "class='".$class_name.wpsc_shipping_details()."'";
	} else {
		$output = $class_name;
	}
	return $output;
}

function wpsc_the_checkout_item_error() {
	global $wpsc_checkout;
	$output = false;
	if($_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id] != '') {
	  $output = $_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id];
	}
	
	return $output;
}
function wpsc_the_checkout_CC_validation(){
	$output = '';
	//exit('<pre>'.print_r($_SESSION['wpsc_gateway_error_messages'],true).'</pre>');
	if ($_SESSION['wpsc_gateway_error_messages']['card_number'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['card_number'];
	//	$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';
	}
	return $output;
}
function wpsc_the_checkout_CC_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['card_number'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;

}
function wpsc_the_checkout_CCexpiry_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['expdate'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;

}
function wpsc_the_checkout_CCexpiry_validation(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['expdate'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['expdate'];
	//	$_SESSION['wpsc_gateway_error_messages']['expdate'] = '';
	}
	return $output;

}
function wpsc_the_checkout_CCcvv_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['card_code'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;

}
function wpsc_the_checkout_CCcvv_validation(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['card_code'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['card_code'];
	//	$_SESSION['wpsc_gateway_error_messages']['card_code'] = '';
	}
	return $output;

}
function wpsc_the_checkout_CCtype_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['cctype'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;
}
function wpsc_the_checkout_CCtype_validation(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['cctype'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['cctype'];
		//$_SESSION['wpsc_gateway_error_messages']['cctype'] ='';
	}
	return $output;

}
function wpsc_checkout_form_is_header() {
	global $wpsc_checkout;
	if($wpsc_checkout->checkout_item->type == 'heading') {
	  $output = true;
	} else {
	  $output = false;
	}
	return $output;
}


function wpsc_checkout_form_name() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_name();
}
function wpsc_checkout_form_element_id() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_element_id();
}

function wpsc_checkout_form_field() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_field();
}


function wpsc_shipping_region_list($selected_country, $selected_region, $shippingdetails = false){
global $wpdb;
  
		//$region_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_REGION_TAX."` WHERE country_id='136'",ARRAY_A);
	$region_data = $wpdb->get_results("SELECT `regions`.* FROM `".WPSC_TABLE_REGION_TAX."` AS `regions` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `country`.`id` = `regions`.`country_id` WHERE `country`.`isocode` IN('".$wpdb->escape($selected_country)."')",ARRAY_A);
	$js = '';
	if(!$shippingdetails){
		$js = "onchange='submit_change_country();'";
	}
	if (count($region_data) > 0) {
		$output .= "<select name='region'  id='region' ".$js." >";
		foreach ($region_data as $region) {
			$selected ='';
			if($selected_region == $region['id']) {
				$selected = "selected='selected'";
			}
			$output .= "<option $selected value='{$region['id']}'>".htmlspecialchars($region['name'])."</option>";
		}
		$output .= "";
		
		$output .= "</select>";
	} else {
		$output .= " ";
	}
	return $output;
}

function wpsc_shipping_country_list($shippingdetails = false) {
	global $wpdb, $wpsc_shipping_modules;
	$js='';
	if(!$shippingdetails){
		$output = "<input type='hidden' name='wpsc_ajax_actions' value='update_location' />";
		$js ="  onchange='submit_change_country();'";
	}
	$selected_country = $_SESSION['wpsc_delivery_country'];
	$selected_region = $_SESSION['wpsc_delivery_region'];
	if($selected_country == null) {
		$selected_country = get_option('base_country');
	}
	if($selected_region == null) {
		$selected_region = get_option('base_region');
	}
	$country_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
	$output .= "<select name='country' id='current_country' ".$js." >";
	foreach ($country_data as $country) {
	// 23-02-09 fix for custom target market by jeffry
	// recon this should be taken out and put into a function somewhere maybe,,,
	 if($country['visible'] == '1'){
			$selected ='';
			if($selected_country == $country['isocode']) {
				$selected = "selected='selected'";
			}
			$output .= "<option value='".$country['isocode']."' $selected>".htmlspecialchars($country['country'])."</option>";
		}
	}

	$output .= "</select>";
	
	$output .= wpsc_shipping_region_list($selected_country, $selected_region, $shippingdetails);

	if($_POST['wpsc_update_location'] == 'true') {
	  $_SESSION['wpsc_update_location'] = true;
	} else {
		$_SESSION['wpsc_update_location'] = false;
	}
	
	if(isset($_POST['zipcode'])) {
		if ($_POST['zipcode']=='') {
			$zipvalue = '';
			$_SESSION['wpsc_zipcode'] = '';
		} else {
			$zipvalue = $_POST['zipcode'];
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
		}
	} else if(isset($_SESSION['wpsc_zipcode']) && ($_SESSION['wpsc_zipcode'] != '')) {
		$zipvalue = $_SESSION['wpsc_zipcode'];
	} else {
		$zipvalue = '';
		$_SESSION['wpsc_zipcode'] = '';
	}
	
	if(($zipvalue != '') && ($zipvalue != 'Your Zipcode')) {
		$color = '#000';
	} else {
		$zipvalue = 'Your Zipcode';
		$color = '#999';
	}
	
	$uses_zipcode = false;
	$custom_shipping = get_option('custom_shipping_options');
	foreach((array)$custom_shipping as $shipping) {
		if($wpsc_shipping_modules[$shipping]->needs_zipcode == true) {
			$uses_zipcode = true;
		}
	}
	
	if($uses_zipcode == true) {
		$output .= " <input type='text' style='color:".$color.";' onclick='if (this.value==\"Your Zipcode\") {this.value=\"\";this.style.color=\"#000\";}' onblur='if (this.value==\"\") {this.style.color=\"#999\"; this.value=\"Your Zipcode\"; }' value='".$zipvalue."' size='10' name='zipcode' id='zipcode'>";
	}
	return $output;
}









/**
 * The WPSC Checkout class
 */
class wpsc_checkout {
	// The checkout loop variables
	var $checkout_items = array();
	var $checkout_item;
	var $checkout_item_count = 0;
	var $current_checkout_item = -1;
	var $in_the_loop = false;
   
	/**
	* wpsc_checkout method, gets the tax rate as a percentage, based on the selected country and region
	* @access public
	*/
  function wpsc_checkout() {
    global $wpdb;
    $this->checkout_items = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' ORDER BY `order`;");
    $this->checkout_item_count = count($this->checkout_items);
  }
  
  function form_name() {
		if($this->form_name_is_required() && ($this->checkout_item->type != 'heading')){
			return $this->checkout_item->name.' * ';
		}else{
			return $this->checkout_item->name;
		}
  }  
   
	function form_name_is_required(){
		if($this->checkout_item->mandatory == 0){
			return false;
		}else{
			return true;
		}
	}
	/**
	* form_element_id method, returns the form html ID
	* @access public
	*/
  function form_element_id() {
		return 'wpsc_checkout_form_'.$this->checkout_item->id;
	}  
	
	/**
	* form_field method, returns the form html
	* @access public
	*/
  function form_field() {
		global $wpdb, $user_ID;
		
 		if((count($_SESSION['wpsc_checkout_saved_values']) <= 0) && ($user_ID > 0)) {
 			$_SESSION['wpsc_checkout_saved_values'] = get_usermeta($user_ID, 'wpshpcrt_usr_profile');
 		}
		
		$saved_form_data = htmlentities(stripslashes($_SESSION['wpsc_checkout_saved_values'][$this->checkout_item->id]), ENT_QUOTES);
		switch($this->checkout_item->type) {
			case "address":
			case "delivery_address":
			case "textarea":
			$output = "<textarea title='".$this->checkout_item->unique_name."' class='text' id='".$this->form_element_id()."' name='collected_data[{$this->checkout_item->id}]' rows='3' cols='40' >".$saved_form_data."</textarea>";
			break;
			
			case "country":
			$output = wpsc_country_region_list($this->checkout_item->id , false, $_SESSION['wpsc_selected_country'], $_SESSION['wpsc_selected_region'], $this->form_element_id());
			break;

			case "delivery_country":
			if(wpsc_uses_shipping()){
			$country_name = $wpdb->get_var("SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".$_SESSION['wpsc_delivery_country']."' LIMIT 1");
			$output = "<input title='".$this->checkout_item->unique_name."' type='hidden' id='".$this->form_element_id()."' class='shipping_country' name='collected_data[{$this->checkout_item->id}]' value='".$_SESSION['wpsc_delivery_country']."' size='4' /><span class='shipping_country_name'>".$country_name."</span> ";
			}else{
			$checkoutfields = true;
			//$output = wpsc_shipping_country_list($checkoutfields);
			$output = wpsc_country_region_list($this->checkout_item->id , false, $_SESSION['wpsc_selected_country'], $_SESSION['wpsc_selected_region'], $this->form_element_id(), $checkoutfields);
			}
			break;
			
			case "text":
			case "city":
			case "delivery_city":
			case "email":
			case "coupon":
			default:

			$output = "<input title='".$this->checkout_item->unique_name."' type='text' id='".$this->form_element_id()."' class='text' value='".$saved_form_data."' name='collected_data[{$this->checkout_item->id}]' />";
			break;
		}
		return $output;
	}
  
	/**
	* validate_forms method, validates the input from the checkout page
	* @access public
	*/
  function validate_forms() {
   global $wpdb, $current_user, $user_ID;
   $any_bad_inputs = false;
   // Credit Card Number Validation for Paypal Pro and maybe others soon
   	  if(isset($_POST['card_number'])){
   		if($_POST['card_number'] != ''){
   			$ccregex='/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/';
   			if(!preg_match($ccregex, $_POST['card_number'])){
   				$any_bad_inputs = true;
				$bad_input = true;
				$_SESSION['wpsc_gateway_error_messages']['card_number'] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower('card number') . ".";
				$_SESSION['wpsc_checkout_saved_values']['card_number'] = '';
   			}else{
   				$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';
   			}
   		
   		}else{


   			$any_bad_inputs = true;
			$bad_input = true;
			$_SESSION['wpsc_gateway_error_messages']['card_number'] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower('card number') . ".";
			$_SESSION['wpsc_checkout_saved_values']['card_number'] = '';

   		}   	
   }else{
   		$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';
   }
      	if(isset($_POST['card_number1']) && isset($_POST['card_number2']) && isset($_POST['card_number3']) && isset($_POST['card_number4'])){
   		if($_POST['card_number1'] != '' && $_POST['card_number2'] != '' && $_POST['card_number3'] != '' && $_POST['card_number4'] != '' && is_numeric($_POST['card_number1']) && is_numeric($_POST['card_number2']) && is_numeric($_POST['card_number3']) && is_numeric($_POST['card_number4'])){
      		$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';	
   	}else{
   	
   			$any_bad_inputs = true;
			$bad_input = true;
			$_SESSION['wpsc_gateway_error_messages']['card_number'] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower('card number') . ".";
			$_SESSION['wpsc_checkout_saved_values']['card_number'] = '';

   	}
   	}
    if(isset($_POST['expiry'])){
	   	if(($_POST['expiry']['month'] != '') && ($_POST['expiry']['month'] != '') && is_numeric($_POST['expiry']['month']) && is_numeric($_POST['expiry']['year'])){
	   		$_SESSION['wpsc_gateway_error_messages']['expdate'] = '';
	   	}else{
			$any_bad_inputs = true;
			$bad_input = true;
			$_SESSION['wpsc_gateway_error_messages']['expdate'] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower('Expiry Date') . ".";
			$_SESSION['wpsc_checkout_saved_values']['expdate'] = '';
	   	}
	
   
   }
   if(isset($_POST['card_code'])){
   	if(($_POST['card_code'] == '') || (!is_numeric($_POST['card_code']))){
   		$any_bad_inputs = true;
		$bad_input = true;
		$_SESSION['wpsc_gateway_error_messages']['card_code'] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower('CVV') . ".";
		$_SESSION['wpsc_checkout_saved_values']['card_code'] = '';
   	}else{
   		$_SESSION['wpsc_gateway_error_messages']['card_code'] = '';
   	}
   
   }
   if(isset($_POST['cctype'])){
   	if($_POST['cctype'] == ''){
   	   	$any_bad_inputs = true;
		$bad_input = true;
		$_SESSION['wpsc_gateway_error_messages']['cctype'] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower('CVV') . ".";
		$_SESSION['wpsc_checkout_saved_values']['cctype'] = '';
   	}else{
		$_SESSION['wpsc_gateway_error_messages']['cctype'] = '';
   	}
   
   }
   	if(isset($_POST['log']) || isset($_POST['pwd']) || isset($_POST['user_email']) ) {
			$results = wpsc_add_new_user($_POST['log'], $_POST['pwd'], $_POST['user_email']);
			$_SESSION['wpsc_checkout_user_error_messages'] = array();
			if(is_callable(array($results, "get_error_code")) && $results->get_error_code()) {
				foreach ( $results->get_error_codes() as $code ) {
					foreach ( $results->get_error_messages($code) as $error ) {
						$_SESSION['wpsc_checkout_user_error_messages'][] = $error;
					}
				
					$any_bad_inputs = true;
				}
			}
			//exit('<pre>'.print_r($results, true).'</pre>');
				if($results->ID > 0) {
					$our_user_id = $results->ID;
				} else {
					$any_bad_inputs = true;		
				}
	}
	if($our_user_id < 1) {
	  $our_user_id = $user_ID;
	}
	// check we have a user id
	if( $our_user_id > 0 ){
		$user_ID = $our_user_id;
	}

    		//Basic Form field validation for billing and shipping details
  		foreach($this->checkout_items as $form_data) {
			$value = $_POST['collected_data'][$form_data->id];
		  	$value_id = (int)$value_id;
			$_SESSION['wpsc_checkout_saved_values'][$form_data->id] = $value;
			$bad_input = false;
			if(($form_data->mandatory == 1) || ($form_data->type == "coupon")) {
				switch($form_data->type) {
					case "email":
					if(!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/",$value)) {
						$any_bad_inputs = true;
						$bad_input = true;
					}
					break;

					case "delivery_country":
					case "country":
					case "heading":
					break;
					
					default:
					if($value == null) {
						$any_bad_inputs = true;
						$bad_input = true;
					}
					break;
				}
				if($bad_input === true) {
					$_SESSION['wpsc_checkout_error_messages'][$form_data->id] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower($form_data->name) . ".";
					$_SESSION['wpsc_checkout_saved_values'][$form_data->id] = '';
				}
			}
		}
	

 		//exit('UserID >><pre>'.print_r($user_ID, true).'</pre>');
		if(($any_bad_inputs == false) && ($user_ID > 0)) {
			$saved_data_sql = "SELECT * FROM `".$wpdb->usermeta."` WHERE `user_id` = '".$user_ID."' AND `meta_key` = 'wpshpcrt_usr_profile';";
			$saved_data = $wpdb->get_row($saved_data_sql,ARRAY_A);
			//echo "<pre>".print_r($meta_data,true)."</pre>";
			$new_meta_data = serialize($_POST['collected_data']);
			if($saved_data != null) {
				$sql ="UPDATE `".$wpdb->usermeta."` SET `meta_value` =  '$new_meta_data' WHERE `user_id` IN ('$user_ID') AND `meta_key` IN ('wpshpcrt_usr_profile');";
				$wpdb->query($sql);
				$changes_saved = true;
				//exit($sql);
			} else {
				$sql = "INSERT INTO `".$wpdb->usermeta."` ( `user_id` , `meta_key` , `meta_value` ) VALUES ( ".$user_ID.", 'wpshpcrt_usr_profile', '$new_meta_data');";
				$wpdb->query($sql);
				$changes_saved = true;
				//exit($sql);
			}
		}

		return array('is_valid' => !$any_bad_inputs, 'error_messages' => $bad_input_message);
  }
  
	/**
	* validate_forms method, validates the input from the checkout page
	* @access public
	*/
  function save_forms_to_db($purchase_id) {
   global $wpdb;
   
		foreach($this->checkout_items as $form_data) {
		
		  $value = $_POST['collected_data'][$form_data->id];
		  if($value == ''){
		  	$value = $form_data->value;
		  }	
		 // echo '<pre>'.print_r($form_data,true).'</pre>';
		  if(is_array($value)){
		  	$value = $value[0];
		  }	  
		  if($form_data->type != 'heading') {
				//echo "INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` ( `log_id` , `form_id` , `value` ) VALUES ( '{$purchase_id}', '".(int)$form_data->id."', '".$value."');<br />";
				
				$prepared_query = $wpdb->query($wpdb->prepare("INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` ( `log_id` , `form_id` , `value` ) VALUES ( %d, %d, %s)", $purchase_id, $form_data->id, $value));
				
 			}
		}
  }
  
  /**
	 * checkout loop methods
	*/ 
  
  function next_checkout_item() {
		$this->current_checkout_item++;
		$this->checkout_item = $this->checkout_items[$this->current_checkout_item];
		return $this->checkout_item;
	}

  
  function the_checkout_item() {
		$this->in_the_loop = true;
		$this->checkout_item = $this->next_checkout_item();
		if ( $this->current_checkout_item == 0 ) // loop has just started
			do_action('wpsc_checkout_loop_start');
	}

	function have_checkout_items() {
		if ($this->current_checkout_item + 1 < $this->checkout_item_count) {
			return true;
		} else if ($this->current_checkout_item + 1 == $this->checkout_item_count && $this->checkout_item_count > 0) {
			do_action('wpsc_checkout_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_checkout_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_checkout_items() {
	  $_SESSION['wpsc_checkout_error_messages'] = array();
		$this->current_checkout_item = -1;
		if ($this->checkout_item_count > 0) {
			$this->checkout_item = $this->checkout_items[0];
		}
	}    
  
}


/**
 * The WPSC Gateway functions
 */


function wpsc_gateway_count() {
	global $wpsc_gateway;
	return $wpsc_gateway->gateway_count;
}

function wpsc_have_gateways() {
	global $wpsc_gateway;
	return $wpsc_gateway->have_gateways();
}

function wpsc_the_gateway() {
	global $wpsc_gateway;
	return $wpsc_gateway->the_gateway();
}

function wpsc_gateway_name() {
	global $wpsc_gateway;
	$payment_gateway_names = get_option('payment_gateway_names');
	if($payment_gateway_names[$wpsc_gateway->gateway['internalname']] != '') {
		$display_name = $payment_gateway_names[$wpsc_gateway->gateway['internalname']];					    
	} else {
		switch($selected_gateway_data['payment_type']) {
			case "paypal";
				$display_name = "PayPal";
			break;
			
			case "manual_payment":
				$display_name = "Manual Payment";
			break;
			
			case "google_checkout":
				$display_name = "Google Checkout";
			break;
			
			case "credit_card":
			default:
				$display_name = "Credit Card";
			break;
		}
	}
	return $display_name;
}

function wpsc_gateway_internal_name() {
	global $wpsc_gateway;
	return $wpsc_gateway->gateway['internalname'];
}

function wpsc_gateway_is_checked() {
	global $wpsc_gateway;
	$is_checked = false;
	if(isset($_SESSION['wpsc_previous_selected_gateway'])) {
	  if($wpsc_gateway->gateway['internalname'] == $_SESSION['wpsc_previous_selected_gateway']) {
	    $is_checked = true;	  
	  }
	} else {
	  if($wpsc_gateway->current_gateway == 0) {
	    $is_checked = true;
	  }
	}
	if($is_checked == true) {
	  $output = 'checked="checked"';
	} else {
		$output = '';
	}
	return $output;
}
function wpsc_gateway_cc_check(){


}
function wpsc_gateway_form_fields() {
	global $wpsc_gateway, $gateway_checkout_form_fields;
	//sprintf on paypalpro module
	if($wpsc_gateway->gateway['internalname'] == 'paypal_pro'){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate'],
						   wpsc_the_checkout_CCcvv_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_code'],	
						   wpsc_the_checkout_CCtype_validation_class(), $_SESSION['wpsc_gateway_error_messages']['cctype']
		);
		return $output;
	}
	if($wpsc_gateway->gateway['internalname'] == 'authorize' || $wpsc_gateway->gateway['internalname'] == 'paypal_payflow'){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate'],
						   wpsc_the_checkout_CCcvv_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_code']
		);
		return $output;
	}

	if($wpsc_gateway->gateway['internalname'] == 'eway' || $wpsc_gateway->gateway['internalname'] == 'bluepay' ){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate']
		);
		return $output;
	}
	if($wpsc_gateway->gateway['internalname'] == 'linkpoint'){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate']
		);
		return $output;
	}
	//$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] , $size['width'], $size['height']);
	return $gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']];

}


function wpsc_gateway_form_field_style() {
	global $wpsc_gateway;
	$is_checked = false;
	if(isset($_SESSION['wpsc_previous_selected_gateway'])) {
	  if($wpsc_gateway->gateway['internalname'] == $_SESSION['wpsc_previous_selected_gateway']) {
	    $is_checked = true;	  
	  }
	} else {
	  if($wpsc_gateway->current_gateway == 0) {
	    $is_checked = true;
	  }
	}
	if($is_checked == true) {
	  $output = 'checkout_forms';
	} else {
		$output = 'checkout_forms_hidden';
	}
	return $output;
}

/**
 * The WPSC Gateway class
 */

class wpsc_gateways {
  var $wpsc_gateways;
	var $gateway;
	var $gateway_count = 0;
	var $current_gateway = -1;
	var $in_the_loop = false;
  
  function wpsc_gateways() {
		global $nzshpcrt_gateways;
		
		$gateway_options = get_option('custom_gateway_options');
		foreach($nzshpcrt_gateways as $gateway) {
			if(array_search($gateway['internalname'], (array)$gateway_options) !== false) {
				$this->wpsc_gateways[] = $gateway;
			}		
		}
		$this->gateway_count = count($this->wpsc_gateways);
  }

  /**
	 * checkout loop methods
	*/ 
  
  function next_gateway() {
		$this->current_gateway++;
		$this->gateway = $this->wpsc_gateways[$this->current_gateway];
		return $this->gateway;
	}

  
  function the_gateway() {
		$this->in_the_loop = true;
		$this->gateway = $this->next_gateway();
		if ( $this->current_gateway == 0 ) // loop has just started
			do_action('wpsc_checkout_loop_start');
	}

	function have_gateways() {
		if ($this->current_gateway + 1 < $this->gateway_count) {
			return true;
		} else if ($this->current_gateway + 1 == $this->gateway_count && $this->gateway_count > 0) {
			do_action('wpsc_checkout_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_gateways();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_gateways() {
		$this->current_gateway = -1;
		if ($this->gateway_count > 0) {
			$this->gateway = $this->wpsc_gateways[0];
		}
	}    

}


?>