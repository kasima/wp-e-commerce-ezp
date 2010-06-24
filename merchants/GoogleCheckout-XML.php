<?php

require_once('library/googlecart.php');
require_once('library/googleitem.php');
require_once('library/googleshipping.php');
require_once('library/googletax.php');
require_once('library/googleresponse.php');
require_once('library/googlemerchantcalculations.php');
require_once('library/googleresult.php');
require_once('library/googlerequest.php');


$nzshpcrt_gateways[$num]['name'] = 'Google Checkout';
$nzshpcrt_gateways[$num]['internalname'] = 'google';
$nzshpcrt_gateways[$num]['function'] = 'gateway_google';
$nzshpcrt_gateways[$num]['form'] = "form_google";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_google";
$nzshpcrt_gateways[$num]['is_exclusive'] = true;
$nzshpcrt_gateways[$num]['payment_type'] = "google_checkout";

function gateway_google($fromcheckout = false){
	global $wpdb, $wpsc_cart, $wpsc_checkout,$current_user,  $purchlogs;	
	//exit('<pre>'.print_r($fromcheckout, true).'</pre>');
	if(!isset($wpsc_checkout)){
	$wpsc_checkout = new wpsc_checkout();
	}
	if(!isset($_SESSION['wpsc_sessionid'])){
		$sessionid = (mt_rand(100,999).time());
		$_SESSION['wpsc_sessionid'] = $sessionid;
	}
	//exit('<pre>'.print_r($_SESSION,true).'</pre>');
		if($_SESSION['wpsc_delivery_region'] == null && $_SESSION['wpsc_selected_region'] == null){
			$_SESSION['wpsc_delivery_region'] = get_option('base_region');
			$_SESSION['wpsc_selected_region'] = get_option('base_region');
		}

		$wpsc_cart->get_shipping_option();
		$wpsc_cart->get_shipping_quotes();
		$wpsc_cart->get_shipping_method();
		$wpsc_cart->google_shipping_quotes();
		$subtotal = $wpsc_cart->calculate_subtotal();
		$base_shipping = $wpsc_cart->calculate_total_shipping();
		$tax = $wpsc_cart->calculate_total_tax();
		$total = $wpsc_cart->calculate_total_price();
	//	exit('<pre>'.print_r($wpsc_cart, true).'</pre>');
		if($total > 0 ){
			$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `totalprice` = ".$total.", `statusno` = '0',`user_ID`=".(int)$user_ID.", `date`= UNIX_TIMESTAMP() , `gateway`='google', `billing_country`='".$wpsc_cart->delivery_country."', shipping_country='".$wpsc_cart->selected_country."', `base_shipping`= '".$base_shipping."', shipping_method = '".$wpsc_cart->selected_shipping_method."', shipping_option= '".$wpsc_cart->selected_shipping_option."', `plugin_version`= '".WPSC_VERSION."' , `discount_value` = '".$wpsc_cart->coupons_amount."', `discount_data`='".$wpsc_cart->coupons_name."' WHERE `sessionid`=".$_SESSION['wpsc_sessionid']."";
		//	exit($sql);
			$update = $wpdb->query($sql);
			$sql = "SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE sessionid=".$_SESSION['wpsc_sessionid'];
			$purchase_log_id = $wpdb->get_var($sql);
			$sql = "DELETE FROM  `".WPSC_TABLE_CART_CONTENTS."` WHERE purchaseid = ".$purchase_log_id;
			$wpdb->query($sql);
			$wpsc_cart->save_to_db($purchase_log_id);
			if(! $update){
				$sql = "INSERT INTO `".WPSC_TABLE_PURCHASE_LOGS."` (`totalprice`,`statusno`, `sessionid`, `user_ID`, `date`, `gateway`, `billing_country`,`shipping_country`, `base_shipping`,`shipping_method`, `shipping_option`, `plugin_version`, `discount_value`, `discount_data`) VALUES ('$total' ,'0', '".$_SESSION['wpsc_sessionid']."', '".(int)$user_ID."', UNIX_TIMESTAMP(), 'google', '{$wpsc_cart->delivery_country}', '{$wpsc_cart->selected_country}', '{$base_shipping}', '".$wpsc_cart->selected_shipping_method."', '".$wpsc_cart->selected_shipping_option."', '".WPSC_VERSION."', '{$wpsc_cart->coupons_amount}','{$wpsc_cart->coupons_name}')";
				$wpdb->query($sql);
				$sql = "SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE sessionid=".$_SESSION['wpsc_sessionid'];
				$purchase_log_id = $wpdb->get_var($sql);
				$wpsc_cart->save_to_db($purchase_log_id);
			}	
			
			if(get_option('permalink_structure') != '') {
				$seperator = "?";
			} else {
				$seperator = "&";
			}
			Usecase($seperator, $_SESSION['wpsc_sessionid'], $fromcheckout);
			//exit();

		}
		
		
	}

 function Usecase($seperator, $sessionid, $fromcheckout) {
	global $wpdb, $wpsc_cart;
	$purchase_log_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1";
	$purchase_log = $wpdb->get_results($purchase_log_sql,ARRAY_A) ;
	
	$cart_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='".$purchase_log[0]['id']."'";
	$wp_cart = $wpdb->get_results($cart_sql,ARRAY_A) ; 
	$merchant_id = get_option('google_id');
	$merchant_key = get_option('google_key');
	$server_type = get_option('google_server_type');
	$currency = get_option('google_cur');
	$cart = new GoogleCart($merchant_id, $merchant_key, $server_type, $currency);
	$transact_url = get_option('transact_url');
	$returnURL =  $transact_url.$seperator."sessionid=".$sessionid."&gateway=google";
	$cart->SetContinueShoppingUrl($returnURL);
	$cart->SetEditCartUrl(get_option('shopping_cart_url'));
	$no=1;
	//exit("<pre>".print_r($wpsc_cart,true)."</pre>");
	
	//new item code
	$no = 0;
//	$cart = new GoogleCart($merchant_id, $merchant_key, $server_type, $currency);
//	foreach($wpsc_cart->cart_items as $item){
		//google prohibited items not implemented
	    $curr=new CURRENCYCONVERTER();
	    $currency_code = $wpdb->get_results("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A);
	    $local_currency_code = $currency_code[0]['code'];
//	    exit('<pre>'.print_r($_REQUEST,true).'</pre>');
	    $google_curr = get_option('google_cur');
		while (wpsc_have_cart_items()) {
			wpsc_the_cart_item();
			if($google_curr != $local_currency_code) {
			$google_currency_productprice = $curr->convert( wpsc_cart_item_price(false)/wpsc_cart_item_quantity(),$google_curr,$local_currency_code);
			$google_currency_shipping = $curr->convert(  $wpsc_cart->selected_shipping_amount,$google_curr,$local_currency_code);
			
		
			} else {
				$google_currency_productprice = wpsc_cart_item_price(false)/wpsc_cart_item_quantity();
				$google_currency_shipping = $wpsc_cart->selected_shipping_amount;
			}

		//	exit('<pre>'.print_r(wpsc_cart_item_name(),true).'</pre>');
			$cartitem["$no"] = new GoogleItem(wpsc_cart_item_name(),      // Item name
			'', // Item description
			wpsc_cart_item_quantity(), // Quantity
			($google_currency_productprice)); // Unit price
			$cart->AddItem($cartitem["$no"]);
			$no++;
		}
		//If there are coupons applied add coupon as a product with negative price
		if($wpsc_cart->coupons_amount > 0){
			if($google_curr != $local_currency_code) {
				$google_currency_productprice = $curr->convert( $wpsc_cart->coupons_amount,$google_curr,$local_currency_code);	
			} else {
				$google_currency_productprice = $wpsc_cart->coupons_amount;
			}
			$cartitem[$no] = new GoogleItem('Discount',      // Item name
			'Discount Price', // Item description
			1, // Quantity
			('-'.$google_currency_productprice)); // Unit price
			$cart->AddItem($cartitem[$no]);


		}

//	}
	

	// Add shipping options
	if(wpsc_uses_shipping() && $google_currency_shipping >0 ){
		$Gfilter = new GoogleShippingFilters();
		$google_checkout_shipping=get_option("google_shipping_country");
		$googleshippingcountries = count($google_checkout_shipping);
		//exit('<pre>'.print_r($googleshipping, true).'</pre>');
		if($googleshippingcountries == 242){
			$Gfilter->SetAllowedWorldArea(true);
		
		}else{
		if(is_array($google_checkout_shipping)){
			$google_shipping_country_ids = implode(",",$google_checkout_shipping);
		}
			$google_shipping_country = $wpdb->get_col("SELECT `isocode` FROM ".WPSC_TABLE_CURRENCY_LIST." WHERE id IN (".$google_shipping_country_ids.")");
			foreach($google_shipping_country as $isocode){
				//exit($isocode);
				$Gfilter->AddAllowedPostalArea($isocode);
				if($isocode == 'US'){
					$Gfilter->SetAllowedCountryArea('ALL');
	
				}
			}
		}
		
		$Gfilter->SetAllowUsPoBox(false);
		$ship_1 = new GoogleFlatRateShipping('Flat Rate Shipping', $google_currency_shipping);
		$ship_1->AddShippingRestrictions($Gfilter);
		$cart->AddShipping($ship_1);
	}
	//wpsc_google_shipping_quotes();

    // Add tax rules
	//if ($_SESSION['wpsc_selected_country']=='US'){
		//set default tax
		//exit('<pre>'.print_r($_SESSION,true).'</pre>');
		$sql = "SELECT `name`, `tax` FROM ".WPSC_TABLE_REGION_TAX." WHERE id='".$_SESSION['wpsc_selected_region']."'";
		//exit('<pre>'.print_r($sql, true).'</pre>');
		$state_name = $wpdb->get_row($sql, ARRAY_A);
		//exit('<pre>'.print_r($state_name, true).'</pre>');
		$defaultTax = $state_name['tax']/100;
		$tax_rule = new GoogleDefaultTaxRule($defaultTax);
		$sql = "SELECT `code` FROM ".WPSC_TABLE_REGION_TAX." WHERE `country_id`='136' AND `tax` = ".$state_name['tax'];
		$states = $wpdb->get_col($sql);
		//exit('<pre>'.print_r($states, true).'</pre>');
		$tax_rule->SetStateAreas((array)$states);
		$cart->AddDefaultTaxRules($tax_rule);
		//get alternative tax rates
		$sql = "SELECT DISTINCT `tax` FROM ".WPSC_TABLE_REGION_TAX." WHERE `tax` != 0 AND `tax` !=".$state_name['tax']."  AND `country_id`='136' ORDER BY `tax`";
		$othertax = $wpdb->get_col($sql);
		$i = 1;
		//exit('<pre>'.print_r($othertax, true).'</pre>');
		foreach($othertax as $altTax){
			$sql = "SELECT `code` FROM ".WPSC_TABLE_REGION_TAX." WHERE `country_id`='136' AND `tax`=".$altTax;
			$alt = $wpdb->get_col($sql);
			$altTax = $altTax/100;
			$alt_google_tax = new GoogleDefaultTaxRule($altTax);
			$alt_google_tax->SetStateAreas($alt);
			//$g = new GoogleAlternateTaxTable('Alt Tax'.$i);
			//$g->AddAlternateTaxRules($alt_google_tax);
			$cart->AddDefaultTaxRules($alt_google_tax);
//			exit(print_r($alt,true));
			$i++;
		}

	//}
		if (get_option('google_button_size') == '0'){
			$google_button_size = 'BIG';
		} elseif(get_option('google_button_size') == '1') {
			$google_button_size = 'MEDIUM';
		} elseif(get_option('google_button_size') == '2') {
			$google_button_size = 'SMALL';
		}
	// Display Google Checkout button
	 //echo '<pre>'.print_r($cart, true).'</pre>';
	 //unset($_SESSION['wpsc_sessionid']);
	 //if($fromCheckout){
	echo $cart->CheckoutButtonCode($google_button_size);
	//}
}

function wpsc_google_checkout_page(){
	global $wpsc_gateway;
	 $script = "<script type='text/javascript'>
	 				jQuery(document).ready(
  						function()
 						 {
	 						jQuery('div#wpsc_shopping_cart_container h2').hide();
	 						jQuery('div#wpsc_shopping_cart_container .wpsc_cart_shipping').hide();
 							jQuery('.wpsc_checkout_forms').hide();
	 					});
	 			</script>";
	 $options = get_option('payment_gateway');
// exit('HELLO<pre>'.print_r(get_option('custom_gateway_options'), true).'</pre>');

	//foreach((array)get_option('custom_gateway_options') as $gateway){
		if(in_array('google', (array)get_option('custom_gateway_options'))){
			$options = 'google';
		}
//	}
	 if($options == 'google' && isset($_SESSION['gateway'])){
	 	unset($_SESSION['gateway']);
		echo $script;
 		gateway_google(true);
	 }

 
}
add_action('wpsc_before_form_of_shopping_cart', 'wpsc_google_checkout_page');
function submit_google() {
	if($_POST['google_id'] != null) {
		update_option('google_id', $_POST['google_id']);
	}

	if($_POST['google_key'] != null) {
		update_option('google_key', $_POST['google_key']);
	}
	if($_POST['google_cur'] != null) {
		update_option('google_cur', $_POST['google_cur']);
	}
	if($_POST['google_button_size'] != null) {
		update_option('google_button_size', $_POST['google_button_size']);
	}
	if($_POST['google_button_bg'] != null) {
		update_option('google_button_bg', $_POST['google_button_bg']);
	}
	if($_POST['google_server_type'] != null) {
		update_option('google_server_type', $_POST['google_server_type']);
	}
	if($_POST['google_auto_charge'] != null) {
		update_option('google_auto_charge', $_POST['google_auto_charge']);
	}
  return true;
  }
  
function form_google()
  {
	if (get_option('google_button_size') == '0'){
		$button_size1="checked='checked'";
	} elseif(get_option('google_button_size') == '1') {
		$button_size2="checked='checked'";
	} elseif(get_option('google_button_size') == '2') {
		$button_size3="checked='checked'";
	}

	if (get_option('google_server_type') == 'sandbox'){
		$google_server_type1="checked='checked'";
	} elseif(get_option('google_server_type') == 'production') {
		$google_server_type2="checked='checked'";
	}
	
	if (get_option('google_auto_charge') == '1'){
		$google_auto_charge1="checked='checked'";
	} elseif(get_option('google_auto_charge') == '0') {
		$google_auto_charge2="checked='checked'";
	}

	if (get_option('google_button_bg') == 'trans'){
		$button_bg1="selected='selected'";
	} else {
		$button_bg2="selected='selected'";
	}
	$output = "
	<tr>
		<td>Merchant ID		</td>
		<td>
		<input type='text' size='40' value='".get_option('google_id')."' name='google_id' />
		</td>
	</tr>
	<tr>
		<td>Merchant Key
		</td>
		<td>
		<input type='text' size='40' value='".get_option('google_key')."' name='google_key' />
		</td>
	</tr>
	<tr>
		<td>
		Turn on auto charging 
		</td>
		<td>
			<input $google_auto_charge1 type='radio' name='google_auto_charge' value='1' /> Yes
			<input $google_auto_charge2 type='radio' name='google_auto_charge' value='0' /> No
		</td>
	</tr>
	<tr>
		<td>Server Type
		</td>
		<td>
			<input $google_server_type1 type='radio' name='google_server_type' value='sandbox' /> Sandbox (For testing)
			<input $google_server_type2 type='radio' name='google_server_type' value='production' /> Production
		</td>
	</tr>
	  <tr>
		  <td>
		  Select your currency
		  </td>
		  <td>
		  <select name='google_cur'>";
		  	if (get_option('google_cur') == 'USD') {
			$output.=
			"<option selected='selected' value='USD'>USD</option>
		  	<option value='GBP'>GBP</option>";
			} else {
			$output.=
			"<option value='USD'>USD</option>
		  	<option value='GBP' selected='selected'>GBP</option>";
			}
		  $output.="</select>
		  </td>
	</tr>

	<tr>
		<td>
		Select Shipping Countries
		</td>
		<td>
		<a href='".add_query_arg(array("googlecheckoutshipping" =>  1, "page" =>
"wpsc-settings"))."' alt='Set Shipping Options'>Set Shipping countries</a>		</td>
	</tr>

	<tr>
		  <td>Button Styles
		  </td>
			<td><div>Size:
				<input $button_size1 type='radio' name='google_button_size' value='0' /> 180&times;46
				<input $button_size2 type='radio' name='google_button_size' value='1' /> 168&times;44
				<input $button_size3 type='radio' name='google_button_size' value='2' /> 160&times;43
				</div>
				<div>
				Background:
		  <select name='google_button_bg'>
		  <option $button_bg1 value='trans'>Transparent</option>
		  <option $button_bg2 value='white'>White</option>
		  </select>
		  </div>				
			</td>
	</tr>

	<tr>
		<td colspan='2'>
				Note: Please put this link to your Google API callback url field on your Google checkout account: <strong>".get_option('siteurl')."/index.php</strong>
		</td>
	</tr>";
  return $output;
  }

function nzsc_googleResponse() {
	global $wpdb, $user_ID;
	$merchant_id = get_option('google_id');
	$merchant_key = get_option('google_key');
	$server_type = get_option('google_server_type');
	$currency = get_option('google_cur');
	
	define('RESPONSE_HANDLER_ERROR_LOG_FILE', 'library/googleerror.log');
	define('RESPONSE_HANDLER_LOG_FILE', 'library/googlemessage.log');
	if (stristr($_SERVER['HTTP_USER_AGENT'],"Google Checkout Notification Agent")) {
		$Gresponse = new GoogleResponse($merchant_id, $merchant_key);
		$xml_response = isset($HTTP_RAW_POST_DATA)?$HTTP_RAW_POST_DATA:file_get_contents("php://input");
		if (get_magic_quotes_gpc()) {
			$xml_response = stripslashes($xml_response);
		}
		list($root, $data) = $Gresponse->GetParsedXML($xml_response);

		$message = "<pre>".print_r($user_marketing_preference,1)."</pre>";
		
		$sessionid = (mt_rand(100,999).time());
		if ($root == "new-order-notification") {
			$_SESSION['nzshpcrt_cart'] = '';
			$cart_items = $data['new-order-notification']['shopping-cart']['items'];
			$user_marketing_preference=$data['new-order-notification']['buyer-marketing-preferences']['email-allowed']['VALUE'];
			$shipping_name = $data['new-order-notification']['buyer-shipping-address']['contact-name']['VALUE'];
			$shipping_name = explode(" ",$shipping_name);
			$shipping_firstname = $shipping_name[0];
			$shipping_lastname = $shipping_name[count($shipping_name)-1];
			$shipping_country = $data['new-order-notification']['buyer-shipping-address']['country-code']['VALUE'];
			$shipping_address1 = $data['new-order-notification']['buyer-shipping-address']['address1']['VALUE'];
			$shipping_address2 = $data['new-order-notification']['buyer-shipping-address']['address2']['VALUE'];
			$shipping_city = $data['new-order-notification']['buyer-shipping-address']['city']['VALUE'];
			$shipping_region = $data['new-order-notification']['buyer-shipping-address']['region']['VALUE'];
			$billing_name = $data['new-order-notification']['buyer-billing-address']['contact-name']['VALUE'];
			$billing_name = explode(" ",$shipping_name);
			$billing_firstname = $shipping_name[0];
			$billing_lastname = $shipping_name[count($shipping_name)-1];
			$billing_region = $data['new-order-notification']['buyer-billing-address']['region']['VALUE'];
			$billing_country = $data['new-order-notification']['buyer-billing-address']['country-code']['VALUE'];
			$total_price = $data['new-order-notification']['order-total']['VALUE'];
			$billing_email = $data['new-order-notification']['buyer-billing-address']['email']['VALUE'];
			$billing_phone = $data['new-order-notification']['buyer-billing-address']['phone']['VALUE'];
			$billing_address = $data['new-order-notification']['buyer-billing-address']['address1']['VALUE'];
			$billing_address .= " ".$data['new-order-notification']['buyer-billing-address']['address2']['VALUE'];
			$billing_address .= " ". $data['new-order-notification']['buyer-billing-address']['city']['VALUE'];
			$billing_city = $data['new-order-notification']['buyer-billing-address']['city']['VALUE'];
			$google_order_number = $data['new-order-notification']['google-order-number']['VALUE'];
			$pnp = $data['new-order-notification']['order-adjustment']['shipping']['flat-rate-shipping-adjustment']['shipping-cost']['VALUE'];
			$affiliate_id=$data['new-order-notification']['shopping-cart']['merchant-private-data'];
			$affiliate_id=explode('=',$affiliate_id);
			if ($affiliate_id[0]=='affiliate_id') {
				if ($affiliate_id[1] == '') {
					$affiliate_id = null;
				} else {
					$affiliate_id = $affiliate_id[1];
				}
			}
			//$tax = $data['new-order-notification']['order-adjustment'][];
			$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type,$currency);
			$result = $Grequest->SendProcessOrder($google_order_number);
			$region_number = $wpdb->get_var("SELECT id FROM ".WPSC_TABLE_REGION_TAX."` WHERE code ='".$billing_region."'");
			$sql = "INSERT INTO `".WPSC_TABLE_PURCHASE_LOGS."` ( `totalprice` , `sessionid` , `date`, `billing_country`, `shipping_country`,`base_shipping`,`shipping_region`, `user_ID`, `discount_value`,`gateway`, `google_order_number`, `google_user_marketing_preference`, `affiliate_id`) VALUES ( '".$total_price."', '".$sessionid."', '".time()."', '".$billing_country."', '".$shipping_country."', '".$pnp."','".$region_number."' , '".$user_ID."' , '".$_SESSION['wpsc_discount']."','".get_option('payment_gateway')."','".$google_order_number."','".$user_marketing_preference."', '".$affiliate_id."')";
// 			mail('hanzhimeng@gmail.com',"",$sql);
			
			$wpdb->query($sql) ;
			$log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` IN('".$sessionid."') LIMIT 1") ;
			$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET firstname='".$shipping_firstname."', lastname='".$shipping_lastname."', email='".$billing_email."', phone='".$billing_phone."' WHERE id='".$log_id."'";
			$wpdb->query($sql) ;
			if (array_key_exists(0,$cart_items['item'])) {
				$cart_items = $cart_items['item'];
			}
			//logging to submited_form_data
			$billing_fname_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='first_name' LIMIT 1") ;
			$sql = "INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` (log_id, form_id, value) VALUES ('".$log_id."','".$billing_fname_id."','".$billing_firstname."')";
			//$wpdb->query($sql) ;
			$billing_lname_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='last_name' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$billing_lname_id."','".$billing_lastname."')";
			$billing_address_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='address' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$billing_address_id."','".$billing_address."')";
			$billing_city_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='city' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$billing_city_id."','".$billing_city."')";
			$billing_country_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='country' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$billing_country_id."','".$billing_country."')";
			$billing_state_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='state' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$billing_state_id."','".$billing_region."')";
			$shipping_fname_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='delivery_first_name' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$shipping_fname_id."','".$shipping_firstname."')";
			$shipping_lname_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='delivery_last_name' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$shipping_lname_id."','".$shipping_lastname."')";
			$shipping_address_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='delivery_address' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$shipping_address_id."','".$shipping_address1." ".$shipping_address2."')";
			$shipping_city_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='delivery_city' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$shipping_city_id."','".$shipping_city."')";
			$shipping_state_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='delivery_state' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$shipping_state_id."','".$shipping_region."')";
			$shipping_country_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type`='delivery_country' LIMIT 1") ;
			$sql .= ", ('".$log_id."','".$shipping_country_id."','".$shipping_country."')";
			$wpdb->query($sql) ;
			//$variations = $cart_item->product_variations;
			foreach($cart_items as $cart_item) {
				$product_id = $cart_item['merchant-item-id']['VALUE'];
				$item_name = $cart_item['item-name']['VALUE'];
				$item_desc = $cart_item['item-description']['VALUE'];
				$item_unit_price = $cart_item['unit-price']['VALUE'];
				$item_quantity = $cart_item['quantity']['VALUE'];
				$product_info = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE id='".$product_id."' LIMIT 1", ARRAY_A) ;
				$product_info = $product_info[0];
				//mail("hanzhimeng@gmail.com","",print_r($product_info,1));
				if($product_info['notax'] != 1) {
					//$price = nzshpcrt_calculate_tax($item_unit_price, $billing_country, $region_number);
					if(get_option('base_country') == $billing_country) {
						$country_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` IN('".get_option('base_country')."') LIMIT 1",ARRAY_A);
						if(($country_data['has_regions'] == 1)) {
							if(get_option('base_region') == $region_number) {
								$region_data = $wpdb->get_row("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."` WHERE `".WPSC_TABLE_REGION_TAX."`.`country_id` IN('".$country_data['id']."') AND `".WPSC_TABLE_REGION_TAX."`.`id` IN('".get_option('base_region')."') ",ARRAY_A) ;
							}
							$gst =  $region_data['tax'];
						} else {
							$gst =  $country_data['tax'];
						}
					} else {
						$gst = 0;
					}
				} else {
					$gst = 0;
				}
				
				if ($product_info['no_shipping'] == '0') {
					if ($shipping_country == get_option('base_country')) {
						$pnp = $product_info['pnp'];
					} else {
						$pnp = $product_info['international_pnp'];
					}
				} else {
					$pnp=0;
				}
				
				$cartsql = "INSERT INTO `".WPSC_TABLE_CART_CONTENTS."` ( `prodid` , `purchaseid`, `price`, `pnp`, `gst`, `quantity`, `donation`, `no_shipping` ) VALUES ('".$product_id."', '".$log_id."','".$item_unit_price."','".$pnp."', '".$gst."','".$item_quantity."', '".$product_info['donation']."', '".$product_info['no_shipping']."')";
				
				$wpdb->query($cartsql) ;
			}
		}
		
		if ($root == "order-state-change-notification") {
			$google_order_number = $data['order-state-change-notification']['google-order-number']['VALUE'];
			$google_status=$wpdb->get_var("SELECT google_status FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE google_order_number='".$google_order_number."'");
			$google_status = unserialize($google_status);
			if (($google_status[0]!='Partially Charged') && ($google_status[0]!='Partially Refunded')) {
				$google_status[0]=$data['order-state-change-notification']['new-financial-order-state']['VALUE'];
				$google_status[1]=$data['order-state-change-notification']['new-fulfillment-order-state']['VALUE'];
			}
			$google_status = serialize($google_status);
			$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET google_status='".$google_status."' WHERE google_order_number='".$google_order_number."'";
			$wpdb->query($sql) ;
			if (($data['order-state-change-notification']['new-financial-order-state']['VALUE'] == 'CHARGEABLE') && (get_option('google_auto_charge') == '1')) {
				$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type,$currency);
				$result = $Grequest->SendChargeOrder($google_order_number);
				
				$_SESSION['nzshpcrt_cart'] = '';
				unset($_SESSION['coupon_num'], $_SESSION['google_session']);
				$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET processed='2' WHERE google_order_number='".$google_order_number."'";
				$wpdb->query($sql) ;
			}
		}
		
		if ($root == "charge-amount-notification") {
			$google_order_number = $data['charge-amount-notification']['google-order-number']['VALUE'];
			$google_status=$wpdb->get_var("SELECT google_status FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE google_order_number='".$google_order_number."'");
			$google_status = unserialize($google_status);
			$total_charged = $data['charge-amount-notification']['total-charge-amount']['VALUE'];
			$google_status['partial_charge_amount'] = $total_charged;
			$totalprice=$wpdb->get_var("SELECT totalprice FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE google_order_number='".$google_order_number."'");
			if ($totalprice>$total_charged) {
				$google_status[0] = 'Partially Charged';
			} else if ($totalprice=$total_charged) {
				$google_status[0] = 'CHARGED';
			}
			$google_status = serialize($google_status);
			$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET google_status='".$google_status."' WHERE google_order_number='".$google_order_number."'";
			$wpdb->query($sql) ;
		}
		
		if ($root == "refund-amount-notification") {
			$google_order_number = $data['refund-amount-notification']['google-order-number']['VALUE'];
			$google_status=$wpdb->get_var("SELECT google_status FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE google_order_number='".$google_order_number."'");
			$google_status = unserialize($google_status);
			$total_charged = $data['refund-amount-notification']['total-refund-amount']['VALUE'];
			$google_status['partial_refund_amount'] = $total_charged;
			$totalprice=$wpdb->get_var("SELECT totalprice FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE google_order_number='".$google_order_number."'");
			if ($totalprice>$total_charged) {
				$google_status[0] = 'Partially refunded';
			} else if ($totalprice=$total_charged) {
				$google_status[0] = 'REFUNDED';
			}
			$google_status = serialize($google_status);
			$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET google_status='".$google_status."' WHERE google_order_number='".$google_order_number."'";
			$wpdb->query($sql) ;
		}
// 		<avs-response>Y</avs-response>
// 		<cvn-response>M</cvn-response>
		
		if ($root == "risk-information-notification") {
			$google_order_number = $data['risk-information-notification']['google-order-number']['VALUE'];
			$google_status=$wpdb->get_var("SELECT google_status FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE google_order_number='".$google_order_number."'");
			$google_status = unserialize($google_status);
			$google_status['cvn']=$data['risk-information-notification']['risk-information']['cvn-response']['VALUE'];
			$google_status['avs']=$data['risk-information-notification']['risk-information']['avs-response']['VALUE'];
			$google_status['protection']=$data['risk-information-notification']['risk-information']['eligible-for-protection']['VALUE'];
			$google_status = serialize($google_status);
			$google_status=$wpdb->query("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET google_status='".$google_status."' WHERE google_order_number='".$google_order_number."'");
			if ($data['risk-information-notification']['risk-information']['cvn-response']['VALUE'] == 'E') {
				$google_risk='cvn';
			}
			if (in_array($data['risk-information-notification']['risk-information']['avs-response']['VALUE'],array('N','U'))) {
				if (isset($google_risk)) {
					$google_risk = 'cvn+avs';
				} else {
					$google_risk='avs';
				}
			}
			if (isset($google_risk)) {
				$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET google_risk='".$google_risk."' WHERE google_order_number='".$google_order_number."'";
				$wpdb->query($sql);
			}
		}
		
		if ($root == "order-state-change-notification") {
			$google_order_number = $data['order-state-change-notification']['google-order-number']['VALUE'];
			if ($data['order-state-change-notification']['new-financial-order-state']['VALUE'] == "CANCELLED_BY_GOOGLE") {
				$google_status = $wpdb->get_var("SELECT google_status FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE google_order_number='".$google_order_number."'");
				$google_status = unserialize($google_status);
				$google_status[0] = "CANCELLED_BY_GOOGLE";
				$wpdb->get_var("UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET google_status='".serialize($google_status)."' WHERE google_order_number='".$google_order_number."'");
			}
		}
// 		mail('hanzhimeng@gmail.com',"",$root . " <pre>". print_r($data,1)."</pre>");
		exit();
	}
}
add_action('init', 'nzsc_googleResponse');
?>