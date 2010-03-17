<?php
/**
 * Ezprints integration functions
 *
 * @package wp-e-commerce
**/

function buildEzprintsOrderRequest($purchaseLogId) {
  global $wpsc_cart, $wpdb;  
  
  // $purchaseLogId = 21;
  
  if ($purchaseLogId != null) {
    $orderId = $purchaseLogId;
    // Build customer data hash
    $purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`= ".$purchaseLogId." LIMIT 1",ARRAY_A);
    // error_log(print_r($wpsc_cart, true));
    $form_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase_log['id']."'";
    $raw_form_data = $wpdb->get_results($form_sql, ARRAY_A);
    $customer_data = array();
    if($raw_form_data != null) {
      foreach($raw_form_data as $form_field) {
        $form_field_label = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `id` = '".$form_field['form_id']."' LIMIT 1", ARRAY_A);
        $customer_data[$form_field_label['unique_name']] = $form_field['value'];
      }
      // look up state abbreviation
    	$billingRegion = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` =".$wpdb->escape($purchase_log['billing_region']),ARRAY_A);
      $customer_data['billingregion_code'] = $billingRegion['code'];
    	$shippingRegion = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` = ".$wpdb->escape($purchase_log['shipping_region']),ARRAY_A);
      $customer_data['shippingregion_code'] = $shippingRegion['code'];

      error_log(print_r($customer_data, true));
    }
    
    if (preg_match("/\(([A-Z]{2})\)/", $purchase_log['shipping_option'], $matches)) {
      $shippingMethod = $matches[1];
    }
    else {
      error_log("====== NO EZPRINTS SHIPPING METHOD FOUND: ".$purchase_log['shipping_option']);
    }
    
    $shippingMethodNode = "<shippingmethod>{$shippingMethod}</shippingmethod>";
    
    // Build list of items from purchase log
    $items = array();
    $cartItems = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='{$purchaseLogId}'",ARRAY_A);
    foreach($cartItems as $cartItem) {
      // error_log(">>>>ITEM: ".print_r($cartItem, true));
      $row = array();
      $row['product_id'] = $cartItem['prodid'];
      $row['quantity'] = $cartItem['quantity'];
      // $wpdb->query("INSERT INTO `".WPSC_TABLE_CART_ITEM_VARIATIONS."` ( `cart_id` , `variation_id` , `value_id` ) VALUES ( '".$cart_id."', '".$variation_row['variation_id']."', '".$variation_row['id']."' );");
      $variation = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`= ".$cartItem['id']." LIMIT 1",ARRAY_A);
      $row['variation_set_id'] = $variation['variation_id'];
      $row['variation_value_id'] = $variation['value_id'];
      $variationValue = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`=".$row['variation_value_id'],ARRAY_A);
      $row['name'] = $variationValue['name'];
      $row['sku'] = $variationValue['sku'];
      
      array_push($items, $row);
    }
    // error_log(">>>>ITEMS".print_r($items, true));
    
  }
  else {
    
    $orderId = '';
    $customer_data = null;
    $shippingMethodNode = '';
    
    // build list of items from cart
    $items = array();
    if ($wpsc_cart != null && $wpsc_cart->cart_items != null) {
      foreach($wpsc_cart->cart_items as $cartItem) {
        $row = array();
        $row['product_id'] = $cartItem->product_id;
        $row['quantity'] = $cartItem->quantity;
        $row['name'] = $cartItem->variation_data[0]['name'];
        $row['sku'] = $cartItem->variation_data[0]['sku'];
        $row['variation_set_id'] = $cartItem->variation_data[0]['variation_id'];
        $row['variation_value_id'] = $cartItem->variation_data[0]['id'];
        array_push($items, $row);
      }
    }
    // error_log(">>>>ITEMS".print_r($items, true));
  }
  // error_log("CUST_DATA: ".print_r($customer_data, true));
  // error_log(print_r($wpsc_cart['cart_items'], true));
    
  $ezprintsId = get_option("ezprintsid");
  // $session_id = "1234";
  // $session_date = "1/8/2010 11:53:54 AM";
  $sessionDate = date("m/j/Y g:i:s A");
  $request =
  '<?xml version="1.0" encoding="UTF-8"?>'.
  '<orders partnerid="' . $ezprintsId . '" version="1">'.
      _buildImages($items) .
      '<ordersession>' .
          '<sessionid>' . $orderId . '</sessionid>' .
          '<sessiondate>' . $sessionDate . '</sessiondate>' .
          _buildCustomer($customer_data) .
          '<order>' .
              '<orderid>' . $orderId . '</orderid>' .
              _buildShippingAddress($customer_data) .
              $shippingMethodNode .
              _buildOrderLines($items) .
          '</order>' .
      '</ordersession>' .
  '</orders>';
  error_log($request);
  return $request;
}

function _buildImages($cartItems) {
  global $wpdb;
  $image_nodes = array();
  foreach($cartItems as $row => $cartItem) {
    $product_id = $cartItem['product_id'];
    $variation_set_id = $cartItem['variation_set_id'];
    $variation_value_id = $cartItem['variation_value_id'];
    
    $price_and_stock_id =  $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product_id}' AND `variation_id` = '{$variation_set_id}' AND `value_id` = '{$variation_value_id}' LIMIT 1");
    $variation_hires_url = $wpdb->get_var("SELECT `hires_url` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$price_and_stock_id}' LIMIT 1");
    $hires_url = $variation_hires_url;
    if ($hires_url == '') {
      $hires_url = $wpdb->get_var("SELECT `meta_value` FROM ".WPSC_TABLE_PRODUCTMETA." WHERE `meta_key` = 'hires_url' AND `product_id` = '{$product_id}' LIMIT 1");
    }    
    $node = '<uri id="'.$row.'">'.$hires_url.'</uri>';
    array_push($image_nodes, $node);
  }
  return '<images>'.join($image_nodes).'</images>';
}

function _buildCustomer($customerData) {
  if ($customerData != null) {
    $countryCode = _twoToThreeLetterCountryCode($customerData['billingcountry']);
    return "<customer>
        <firstname>{$customerData['billingfirstname']}</firstname>
        <lastname>{$customerData['billinglastname']}</lastname>
        <address1>{$customerData['billingaddress']}</address1>
        <city>{$customerData['billingcity']}</city>
        <state>{$customerData['billingregion_code']}</state>
        <zip>{$customerData['billingpostcode']}</zip>
        <countrycode>{$countryCode}</countrycode>
        <phone>{$customerData['billingphone']}</phone>
    </customer>";
  }
  else {
    return '';
  }
}

function _twoToThreeLetterCountryCode($two_code) {
  if ($two_code == '') {
    return '';
  }
  if ($two_code == 'UK') {
    $two_code = 'GB';
  }
  $i = new I18N_ISO_3166($two_code);
  return $i->getThreeLetterCode();
}

function _buildShippingAddress($customerData) {
    if ($customerData == null) {
      // return '<shippingaddress>
      //     <firstname>Janet</firstname>
      //     <lastname>Doe</lastname>
      //     <address1>555 De Haro St</address1>
      //     <city>San Francisco</city>
      //     <state>CA</state>
      //     <zip>94107</zip>
      //     <countrycode>USA</countrycode>
      //     <phone>678-405-5000</phone>
      // </shippingaddress>';
      if (isset($_POST['country'])) {
        $country = $_POST['country'];
        $_SESSION['wpsc_delivery_country'] = $country;
      } else {
        $country = $_SESSION['wpsc_delivery_country'];
      }
      
      return '<shippingaddress>'.
        '<countrycode>'._twoToThreeLetterCountryCode($country).'</countrycode>'.
        '<state>'.$_POST['region'].'</state>'.
        '<zip>'.$_POST['zipcode'].'</zip>'.
      '</shippingaddress>';
    }
    else {
      // return '<shippingaddress>
      //     <state>MD</state>
      //     <zip>20902</zip>
      //     <countrycode>THA</countrycode>
      // </shippingaddress>';
      $country = $customerData['billingcountry'];
      $countryCode = _twoToThreeLetterCountryCode($country);
      return "<shippingaddress>
          <firstname>{$customerData['shippingfirstname']}</firstname>
          <lastname>{$customerData['shippinglastname']}</lastname>
          <address1>{$customerData['shippingaddress']}</address1>
          <city>{$customerData['shippingcity']}</city>
          <state>{$customerData['shippingregion_code']}</state>
          <zip>{$customerData['shippingpostcode']}</zip>
          <countrycode>{$countryCode}</countrycode>
          <phone>{$customerData['shippingphone']}</phone>
      </shippingaddress>";
    }
}

function _buildOrderLines($cartItems) {
  // return '<orderline productid="10127" imageid="7">
  //     <description>Puzzle</description>
  //     <quantity>3</quantity>
  //     <position>crop</position>
  // </orderline>';
  $orderLineNodes = array();
  foreach($cartItems as $row => $cartItem) {
    $sku = $cartItem['sku'];
    $name = $cartItem['name'];
    $quantity = $cartItem['quantity'];
    $node = "<orderline productid=\"{$sku}\" imageid=\"{$row}\">
      <description>{$name}</description>
      <quantity>{$quantity}</quantity>
      <position>crop</position>
    </orderline>";
    array_push($orderLineNodes, $node);
  }
  return join($orderLineNodes);
}

function sendEzprintsShippingRequest() {
  $ezprints_server = 'www.ezprints.com';
  $api_path = '/ezpartners/shippingcalculator/xmlshipcalc.asp';
  $url = 'http://'.$ezprints_server . $api_path;
  $xml =  buildEzprintsOrderRequest(null);
  error_log(">>>> EZP SHIPPING REQ: ".$xml);
  return _sendEzprintsRequest($url, $xml);
}

function sendEzprintsOrder($purchaseLogId) {
  $ezprintsId = get_option("ezprintsid");
  $ezprints_server = 'order.ezprints.com';
  $api_path = '/PostXmlOrder.axd';
  $params = "PartnerNumber={$ezprintsId}&PartnerReference={$purchaseLogId}";
  $url = 'http://'.$ezprints_server . $api_path . '?' . $params;
  $xml = buildEzPrintsOrderRequest($purchaseLogId);
  error_log(">>>> EZP ORDER REQ: ".$xml);
  return _sendEzprintsRequest($url, $xml);
}

function _sendEzprintsRequest($url, $request) {
  $xml_payload = str_replace(array("\r", "\n", "\t") , "", $request);
  
  // error_log($xml_payload);
  // return

  $post_fields = 'xml=' . $xml_payload;
  // error_log($post_fields);
  $ch=curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Content-type: text/xml"));
  curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

  $body = curl_exec($ch);
  error_log("<<<<<< EZP RETURN BODY: ".$body);
  //exit('<pre>'.print_r($body, true).'</pre>');
  curl_close($ch);
  return $body;
}

?>
