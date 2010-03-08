<?php
/*
 * Some parts of this code were inspired by the shopp plugin and their paypal pro module. 
 * and copyright Ingenesis Limited, 19 August, 2008.
 */
$nzshpcrt_gateways[$num]['name'] = 'Paypal Payments Pro';
$nzshpcrt_gateways[$num]['internalname'] = 'paypal_pro';
$nzshpcrt_gateways[$num]['function'] = 'gateway_paypal_pro';
$nzshpcrt_gateways[$num]['form'] = "form_paypal_pro";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_paypal_pro";
$nzshpcrt_gateways[$num]['payment_type'] = "credit_card";

if(in_array('paypal_pro',(array)get_option('custom_gateway_options'))) {
	$gateway_checkout_form_fields[$nzshpcrt_gateways[$num]['internalname']] = "
	<tr %s>
		<td>Credit Card Number *</td>
		<td>
			<input type='text' value='' name='card_number' />
			<p class='validation-error'>%s</p>
		</td>
	</tr>
	<tr %s>
		<td>Credit Card Expiry *</td>
		<td>
			<input type='text' size='2' value='' maxlength='2' name='expiry[month]' />/<input type='text' size='4'  maxlength='4' value='' name='expiry[year]' />
			<p class='validation-error'>%s</p>
		</td>
	</tr>
	<tr %s>
		<td>CVV *</td>
		<td><input type='text' size='4' value='' maxlength='4' name='card_code' />
		<p class='validation-error'>%s</p>
		</td>
	</tr>
	<tr %s>
		<td>Card Type *</td>
		<td>
		<select name='cctype'>
			<option value='Visa'>Visa</option>
			<option value='Mastercard'>MasterCard</option>
			<option value='Discover'>Discover</option>
			<option value='Amex'>Amex</option>
		</select>
		<p class='validation-error'>%s</p>
		</td>
	</tr>
";
}
  
function gateway_paypal_pro($seperator, $sessionid){
	global $wpdb, $wpsc_cart;
	$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
	$usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value, `".WPSC_TABLE_CHECKOUT_FORMS."`.`name`, `".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN `".WPSC_TABLE_SUBMITED_FORM_DATA."` ON `".WPSC_TABLE_CHECKOUT_FORMS."`.id = `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE  `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$purchase_log['id']." ORDER BY `".WPSC_TABLE_CHECKOUT_FORMS."`.`order`";
		//exit($usersql);
		$userinfo = $wpdb->get_results($usersql, ARRAY_A);
		//exit('<pre>'.print_r($userinfo, true).'</pre>');
	//BUILD DATA TO SEND TO PayPal 
	
	$data = array();
	$data['USER'] 					= get_option('paypal_pro_username');
	$data['PWD'] 					= get_option('paypal_pro_password');
	$data['SIGNATURE']				= get_option('paypal_pro_signature');		
	
	$data['VERSION']				= "52.0";
	$data['METHOD']					= "DoDirectPayment";
	$data['PAYMENTACTION']			= "Sale";
	$data['IPADDRESS']				= $_SERVER["REMOTE_ADDR"];
	$data['RETURNFMFDETAILS']		= "1"; // optional - return fraud management filter data

	foreach((array)$userinfo as $key => $value){
		if(($value['unique_name']=='billingfirstname') && $value['value'] != ''){
			$data['FIRSTNAME']	= $value['value'];
		}
		if(($value['unique_name']=='billinglastname') && $value['value'] != ''){
			$data['LASTNAME']	= $value['value'];
		}
		if(($value['unique_name']=='billingemail') && $value['value'] != ''){
			$data['EMAIL']	= $value['value'];
		}
		if(($value['unique_name']=='billingphone') && $value['value'] != ''){
			$data['PHONENUM']	= $value['value'];
		}
		if(($value['unique_name']=='billingaddress') && $value['value'] != ''){
			$data['STREET']	= $value['value'];
		}
		if(($value['unique_name']=='billingcity') && $value['value'] != ''){
			$data['CITY']	= $value['value'];
		}
		if(($value['unique_name']=='billingstate') && $value['value'] != ''){
			$sql = "SELECT `code` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `name` ='".absint($value['value'])."' LIMIT 1";
			$data['STATE'] = $wpdb->get_var($sql);
		}else{
			$data['STATE']='CA';
		}
		if(($value['unique_name']=='billingcountry') && $value['value'] != ''){
		
			$data['COUNTRYCODE']	= $value['value'];
		}		
		if(($value['unique_name']=='billingpostcode') && $value['value'] != ''){
			$data['ZIP']	= $value['value'];
		}

		//
		
		if((($value['unique_name']=='shippingfirstname') && $value['value'] != '')){
			$data1['SHIPTONAME1']	= $value['value'];
		}
		if((($value['unique_name']=='shippinglastname') && $value['value'] != '')){
			$data1['SHIPTONAME2']	= $value['value'];
		}
		if(($value['unique_name']=='shippingaddress') && $value['value'] != ''){
			$data['SHIPTOSTREET']	= $value['value'];
		}	
		if(($value['unique_name']=='shippingcity') && $value['value'] != ''){
			$data['SHIPTOCITY']	= $value['value'];
		}	
			//$data['SHIPTOCITY'] = 'CA';
		if(($value['unique_name']=='shippingstate') && $value['value'] != ''){
		//	$data['SHIPTOSTATE'] = $value['value'];
			$sql = "SELECT `code` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `name` ='".absint($value['value'])."' LIMIT 1";
			$data['SHIPTOSTATE'] = $wpdb->get_var($sql);
		}else{
		}	
		if(($value['unique_name']=='shippingcountry') && $value['value'] != ''){
			$data['SHIPTOCOUNTRY']	= $value['value'];
		}	
		if(($value['unique_name']=='shippingpostcode') && $value['value'] != ''){
			$data['SHIPTOZIP']	= $value['value'];
		}	
		//exit($key.' > '.print_r($value,true));
	}
	$data['SHIPTONAME'] = $data1['SHIPTONAME1'].' '.$data1['SHIPTONAME2'];

	if( ($data['SHIPTONAME'] == null) || ($data['SHIPTOSTREET'] == null) || ($data['SHIPTOCITY'] == null) ||
			($data['SHIPTOSTATE'] == null) || ($data['SHIPTOCOUNTRY'] == null) || ($data['SHIPTOZIP'] == null)) {
			// if any shipping details are empty, the order will simply fail, this deletes them all if one is empty
			unset($data['SHIPTONAME']);
			unset($data['SHIPTOSTREET']);
			unset($data['SHIPTOCITY']);
			unset($data['SHIPTOSTATE']);
			unset($data['SHIPTOCOUNTRY']);
			unset($data['SHIPTOZIP']);
	} 


	
	$data['CREDITCARDTYPE'] = $_POST['cctype'];
	$data['ACCT']			= $_POST['card_number'];
	$data['EXPDATE']		= $_POST['expiry']['month'].$_POST['expiry']['year'];
	$data['CVV2']			= $_POST['card_code'];
	
	$data['AMT']			= number_format($wpsc_cart->total_price,2);
	$data['ITEMAMT']		= number_format($wpsc_cart->subtotal,2);
	$data['SHIPPINGAMT']	= number_format($wpsc_cart->base_shipping,2);
	$data['TAXAMT']			= number_format($wpsc_cart->total_tax, 2);
	
	// Ordered Items
	$discount = $wpsc_cart->coupons_amount;
	//exit($discount);
	if(($discount > 0)) {
		$i = 1;
		$data['AMT']			= number_format(sprintf("%01.2f", $wpsc_cart->calculate_total_price()),2,'.','');

		$data['ITEMAMT']		= number_format(sprintf("%01.2f", $wpsc_cart->calculate_total_price()),2,'.','');

		$data['SHIPPINGAMT']	= 0;
		$data['TAXAMT']			= 0;
		$data['L_NAME'.$i] = "Your Shopping Cart";
		$data['L_AMT'.$i] = number_format(sprintf("%01.2f", $wpsc_cart->calculate_total_price()),2,'.','');
		$data['L_QTY'.$i] = 1;
		// $data['item_number_'.$i] = 0;
		$data['L_TAXAMT'.$i] = 0;
	} else {

	foreach($wpsc_cart->cart_items as $i => $Item) {
		$data['L_NAME'.$i]			= $Item->product_name;
		$data['L_AMT'.$i]			= number_format($Item->unit_price,2);
		$data['L_NUMBER'.$i]		= $i;
		$data['L_QTY'.$i]			= $Item->quantity;
		$data['L_TAXAMT'.$i]		= 0;//number_format($Item->tax,2);
	}
	}
	$transaction = "";
	foreach($data as $key => $value) {
		if (is_array($value)) {
			foreach($value as $item) {
				if (strlen($transaction) > 0) $transaction .= "&";
				$transaction .= "$key=".urlencode($item);
			}
		} else {
			if (strlen($transaction) > 0) $transaction .= "&";
			$transaction .= "$key=".urlencode($value);
		}
	}
//exit($transaction);
	$response = send($transaction);
//	exit('<pre>'.print_r($response, true).'</pre><pre>'.print_r($data, true).'</pre>');
	if($response->ack == 'Success' || $response->ack == 'SuccessWithWarning'){
		//redirect to  transaction page and store in DB as a order with accepted payment
		$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '2' WHERE `sessionid`=".$sessionid;
		$wpdb->query($sql);
		$transact_url = get_option('transact_url');
		unset($_SESSION['WpscGatewayErrorMessage']);
		$_SESSION['paypalpro'] = 'success';
		header("Location: ".get_option('transact_url').$seperator."sessionid=".$sessionid);
		exit(); // on some servers, a header that is not followed up with an exit does nothing.
	}else{
		//redirect back to checkout page with errors
		$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '5' WHERE `sessionid`=".$sessionid;
		$wpdb->query($sql);
		$transact_url = get_option('checkout_url');

		$paypal_account_error = false;
		
		$paypal_error_codes = array('10500','10501','10507','10548','10549','10550','10552','10758','10760','15003');
		foreach($paypal_error_codes as $error_code) {
				if(in_array($error_code, $response->errorcodes)) {
					$paypal_account_error = true;
					break;
				}
		}
		if($paypal_account_error == true) {
			$_SESSION['wpsc_checkout_misc_error_messages'][] = __('There is a problem with your PayPal account configuration, please contact PayPal for further information.', 'wpsc');
			foreach($response->longerror as $paypal_error) {
				$_SESSION['wpsc_checkout_misc_error_messages'][] = $paypal_error;
			}
		} else {
			$_SESSION['wpsc_checkout_misc_error_messages'][] = __('Sorry your transaction did not go through to Paypal successfully, please correct any errors and try again.', 'wpsc');
			foreach($response->longerror as $paypal_error) {
				$_SESSION['wpsc_checkout_misc_error_messages'][] = $paypal_error;
			}
		}
		$_SESSION['paypalpro'] = 'fail';
	}
	//exit('<pre>'.print_r($response, true).'</pre>');
}

function send ($transaction) {
	$connection = curl_init();
	if (get_option('paypal_pro_testmode') == "on"){
		curl_setopt($connection,CURLOPT_URL,"https://api-3t.sandbox.paypal.com/nvp"); // Sandbox testing
	}else{
		curl_setopt($connection,CURLOPT_URL,"https://api-3t.paypal.com/nvp"); // Live
	}
	$useragent = 'WP e-Commerce plugin';
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0); 
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0); 
	curl_setopt($connection, CURLOPT_NOPROGRESS, 1); 
	curl_setopt($connection, CURLOPT_VERBOSE, 1); 
	curl_setopt($connection, CURLOPT_FOLLOWLOCATION,0); 
	curl_setopt($connection, CURLOPT_POST, 1); 
	curl_setopt($connection, CURLOPT_POSTFIELDS, $transaction); 
	curl_setopt($connection, CURLOPT_TIMEOUT, 30); 
	curl_setopt($connection, CURLOPT_USERAGENT, $useragent); 
	curl_setopt($connection, CURLOPT_REFERER, "https://".$_SERVER['SERVER_NAME']); 
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	$buffer = curl_exec($connection);
	curl_close($connection);
	//echo $buffer;
	$Response = response($buffer);
	return $Response;
}
function response ($buffer) {
	$_ = new stdClass();
	$r = array();
	$pairs = split("&",$buffer);
	foreach($pairs as $pair) {
		list($key,$value) = split("=",$pair);
		
		if (preg_match("/(\w*?)(\d+)/",$key,$matches)) {
			if (!isset($r[$matches[1]])) $r[$matches[1]] = array();
			$r[$matches[1]][$matches[2]] = urldecode($value);
		} else $r[$key] = urldecode($value);
	}
	
	$_->ack = $r['ACK'];
	$_->errorcodes = $r['L_ERRORCODE'];
	$_->shorterror = $r['L_SHORTMESSAGE'];
	$_->longerror = $r['L_LONGMESSAGE'];
	$_->severity = $r['L_SEVERITYCODE'];
	$_->timestamp = $r['TIMESTAMP'];
	$_->correlationid = $r['CORRELATIONID'];
	$_->version = $r['VERSION'];
	$_->build = $r['BUILD'];
	
	$_->transactionid = $r['TRANSACTIONID'];
	$_->amt = $r['AMT'];
	$_->avscode = $r['AVSCODE'];
	$_->cvv2match = $r['CVV2MATCH'];

	return $_;
}
function submit_paypal_pro(){
 //exit('<pre>'.print_r($_POST, true).'</pre>');
 if($_POST['PayPalPro']['username'] != null) {
    update_option('paypal_pro_username', $_POST['PayPalPro']['username']);
 }
 if($_POST['PayPalPro']['password'] != null) {
    update_option('paypal_pro_password', $_POST['PayPalPro']['password']);
 }
 if($_POST['PayPalPro']['signature'] != null) {
    update_option('paypal_pro_signature', $_POST['PayPalPro']['signature']);
 }
 if($_POST['PayPalPro']['testmode'] != null) {
    update_option('paypal_pro_testmode', $_POST['PayPalPro']['testmode']);
 }
  return true;
}  

function form_paypal_pro(){
if(get_option('paypal_pro_testmode') == "on"){ 
	$selected = 'checked="checked"';
}else{ 
	$selected = '';
}
$output = '
<tr>
	<td>
		<label for="paypal_pro_username">'.__('API Username:').'</label>
	</td>
	<td>
		<input type="text" name="PayPalPro[username]" id="paypal_pro_username" value="'.get_option("paypal_pro_username").'" size="30" />
	</td>
</tr>
<tr>
	<td>
		<label for="paypal_pro_password">'.__('API Password:').'</label>
	</td>
	<td>
		<input type="password" name="PayPalPro[password]" id="paypal_pro_password" value="'.get_option('paypal_pro_password').'" size="16" />
	</td>
</tr>
<tr>
	<td>
		<label for="paypal_pro_signature">'.__('API Signature:').'</label>
	</td>
	<td>
		<input type="text" name="PayPalPro[signature]" id="paypal_pro_signature" value="'.get_option('paypal_pro_signature').'" size="48" />
	</td>
</tr>
<tr>
	<td>
		<label for="paypal_pro_testmode">'.__('Test Mode Enabled:').'</label>
	</td>
	<td>
		<input type="hidden" name="PayPalPro[testmode]" value="off" /><input type="checkbox" name="PayPalPro[testmode]" id="paypal_pro_testmode" value="on" '.$selected.' />					
	</td>
</tr>';
return $output;
}
?>