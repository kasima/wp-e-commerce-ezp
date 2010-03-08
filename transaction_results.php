<?php
global $wpdb, $user_ID, $nzshpcrt_gateways;
//$curgateway = get_option('payment_gateway');

$sessionid = $_GET['sessionid'];
if($_GET['gateway'] == 'google'){
	wpsc_google_checkout_submit();
	unset($_SESSION['wpsc_sessionid']);
}elseif($_GET['gateway'] == 'noca'){
	wpsc_submit_checkout();
}
if($_SESSION['wpsc_previous_selected_gateway'] == 'paypal_certified'){
	$sessionid = $_SESSION['paypalexpresssessionid'];
}

//exit("test!");
$errorcode = '';
$transactid = '';
if($_REQUEST['eway']=='1') {
	$sessionid = $_GET['result'];
}elseif($_REQUEST['eway']=='0'){
	echo $_SESSION['eway_message'];
}elseif ($_REQUEST['payflow']=='1') {	
	echo $_SESSION['payflow_message'];
	$_SESSION['payflow_message']='';
}
	//exit('getting here?<pre>'.print_r($_SESSION[[wpsc_previous_selected_gateway], true).'</pre>'.get_option('payment_gateway'));
if(($_SESSION['wpsc_previous_selected_gateway'] == 'paypal_certified') && ($_SESSION['paypalExpressMessage'] != '')){
	echo $_SESSION['paypalExpressMessage'];

} else {
	if($_SESSION['wpsc_previous_selected_gateway']== 'dps') {
		$sessionid = decrypt_dps_response();
		//exit($sessionid);
		if($sessionid != ''){
		//exit('<pre>'.print_r($sessionid, true).'</pre>');
			transaction_results($sessionid, true); 
		}else{
			_e('Sorry your transaction was not accepted.<br /><a href='.get_option("shopping_cart_url").'>Click here to go back to checkout page.</a>');
		}
	} else {

		echo transaction_results($sessionid, true);
	}
}
?>