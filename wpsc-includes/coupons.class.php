<?php

/**
* uses coupons function, no parameters
* @return boolean if true, all items in the cart do use shipping
*/
function wpsc_uses_coupons() {
	global $wpsc_coupons;
	return $wpsc_coupons->uses_coupons();
}
function wpsc_coupons_error(){
	global $wpsc_coupons;
	//exit('<pre>'.print_r($wpsc_coupons, true).'</pre>');
	if(isset($wpsc_coupons->errormsg) && $wpsc_coupons->errormsg == false){
		return true;
	}else{
		return false;
	}
}
/**
 * Coupons class.
 *
 * Conditional coupons use an 'ALL' logic. Now admins can achieve an 'ANY' logic by adding multiple coupons.
 *
 * TODO: Implement 'ANY' logic of conditional coupons.
 *
 * @package wp-e-commerce
 * @since 3.7
 */
class wpsc_coupons {
	var $code;
	var $value;
	var $is_percentage;
	var $conditions;
	var $start_date;
	var $active;
	var $every_product ;
	var $end_date;
	var $use_once;
	var $is_used;
	
	var $discount;
		//for error message
	var $errormsg;
	/**
	 * Coupons constractor
	 *
	 * Instantiate a coupons object with optional variable $code;
	 *
	 * @param string code (optional) the coupon code you would like to use.
	 * @return bool True if coupon code exists, False otherwise.
	 */
	function wpsc_coupons($code = ''){
		global $wpdb;
	
		if ($code == '') {
			return false;
		} else {
			$this->code = $wpdb->escape($code);
			
			$coupon_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` WHERE coupon_code='$code' LIMIT 1", ARRAY_A);
			
			if (($coupon_data == '') || ($coupon_data == null) || (strtotime($coupon_data['expiry']) < time()) ) {
				$this->errormsg = false;
				return false;
			} else {
				$this->value = $coupon_data['value'];
				$this->is_percentage = $coupon_data['is-percentage'];
				$this->conditions = unserialize($coupon_data['condition']);
				$this->is_used = $coupon_data['is-used'];
				$this->active = $coupon_data['active'];
				$this->use_once = $coupon_data['use-once'];
				$this->start_date = $coupon_data['start'];
				$this->end_date = $coupon_data['expiry'];
				$this->every_product = $coupon_data['every_product'];
				$this->errormsg = true;
				$valid = $this->validate_coupon();
				return $valid;
			}
		}
	}
	
	/**
	 * Coupons validator
	 *
	 * Checks if the current coupon is valid to use (Expiry date, Active, Used).
	 *
	 * @return bool True if coupon is not expried, used and still active, False otherwise.
	 */
	function validate_coupon() {
		$now = date("Y-m-d H:i:s");
		$now = strtotime($now);
		
		if ( ($this->active=='1') && !(($this->use_once == '1') && ($this->is_used=='1'))){
			if ((strtotime($this->start_date) < $now)&&(strtotime($this->end_date) > $now)){
				return true;
			}
		}
		return false;
	}
	
	
	function calculate_discount() {
		global $wpdb, $wpsc_cart;
		
		$wpsc_cart->clear_cache();

		//Calculates the discount for the whole cart if there is no condition on this coupon.
		if ($this->conditions == '' || count($this->conditions) == 0) {

			// $this->is_percentage == '2' means "Free Shipping"
			if ($this->is_percentage == '2'){
				return $wpsc_cart->calculate_total_shipping();	
			}

			// $this->is_percentage == '1' means "%" discount
			if ($this->is_percentage == '1') {
			  
				$total_price = $wpsc_cart->calculate_subtotal();
				$this->discount = $total_price*$this->value/100;
				return $this->discount;

			// Anything else means "Fixed amount" discount
			} else {

			  if($this->every_product == 1) {
					$item_count = (int)wpsc_cart_item_count();
					return ($this->value * $item_count);
			  } else {
					return $this->value;
				}
			}

		// The coupon has conditions so may not apply to all items
		} else {
		
			//Loop throught all products in the shopping cart, apply coupons on the ones match the conditions. 
			$cart  =& $wpsc_cart->have_cart_items();
			
				foreach ($wpsc_cart->cart_items as $key => $item) {
					
					$product_data = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id='{$item->product_id}'");
					$product_data = $product_data[0];
				
					$match = true;
					foreach ($this->conditions as $c) {
						
						//Check if all the condictions are returning true, so it's an ALL logic, if anyone want to implement a ANY logic please do.
						if (!$this->compare_logic($c, $item)) {
							$match = false;
							break;
						}
					}

					// This product is eligible for discount
					if ($match) {
					
					    if ($this->is_percentage == '1') {
							$this->discount = $item->unit_price*$item->quantity*$this->value/100;
						
							$item->discount = $this->discount;
							if($this->every_product == 1){
								$return += $this->discount;
							}else{
								return $this->discount;
							}
						} else {
							$item->discount = $this->value;
							if($this->every_product == 1){
								$return += $item->discount;
							}else{
								//exit('<pre>'.print_r($this,true).'</pre>');
								return $item->discount;
							}
						}

					// This product is NOT eligible for discount
					}else{
						$this->discount = 0;
						$item->discount = $this->discount;
						$return += $this->discount;
					}
				}
		}
		
		return $return;
	}
	
	
	
	/**
	 * Comparing logic with the product information
	 *
	 * Checks if the product matchs the logic
	 *
	 * @return bool True if all conditions are matched, False otherwise.
	 */
	function compare_logic($c, $product_obj) {
		global $wpdb, $wpsc_cart;
		
		if ($c['property'] == 'item_name') {
			$product_data = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id='{$product_obj->product_id}'");
			$product_data = $product_data[0];
		
			switch($c['logic']) {
				case 'equal': //Checks if the product name is exactly the same as the condition value
				if ($product_data->name == $c['value']) {
					
					return true;
				}
				break;
				
				case 'greater'://Checks if the product name is not the same as the condition value
				if ($product_data->name > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the product name is not the same as the condition value
				if ($product_data->name < $c['value'])
					return true;
				break;
				
				case 'contains'://Checks if the product name contains the condition value
				preg_match("/(.*)".$c['value']."(.*)/", $product_data->name, $match);
				if (!empty($match))
					return true;
				break;
				
				case 'not_contain'://Checks if the product name contains the condition value
				preg_match("/(.*)".$c['value']."(.*)/", $product_data->name, $match);
				if (empty($match))
					return true;
				break;
				
				case 'begins'://Checks if the product name begins with condition value
				preg_match("/^".$c['value']."/", $product_data->name, $match);
				if (!empty($match))
					return true;
				break;
				
				case 'ends'://Checks if the product name ends with condition value
				preg_match("/".$c['value']."$/", $product_data->name, $match);
				if (!empty($match))
					return true;
				break;
				
				default:
				return false;
			}
		} else if ($c['property'] == 'item_quantity'){

			switch($c['logic']) {
				case 'equal'://Checks if the quantity of a product in the cart equals condition value
				if ($product_obj->quantity == (int)$c['value'])
					return true;
				break;
				
				case 'greater'://Checks if the quantity of a product is greater than the condition value
				if ($product_obj->quantity > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the quantity of a product is less than the condition value
				if ($product_obj->quantity < $c['value'])
					return true;
				break;
						
				case 'contains'://Checks if the product name contains the condition value
				preg_match("/(.*)".$c['value']."(.*)/", $product_obj->quantity, $match);
				if (!empty($match))
					return true;
				break;
				
				case 'not_contain'://Checks if the product name contains the condition value
				preg_match("/(.*)".$c['value']."(.*)/",$product_obj->quantity, $match);
				if (empty($match))
					return true;
				break;
				
				case 'begins'://Checks if the product name begins with condition value
				preg_match("/^".$c['value']."/", $product_obj->quantity, $match);
				if (!empty($match))
					return true;
				break;
				
				case 'ends'://Checks if the product name ends with condition value
				preg_match("/".$c['value']."$/",$product_obj->quantity, $match);
				if (!empty($match))
					return true;
				break;
				default:
				return false;
			}
		} else if ($c['property'] == 'total_quantity'){
			$total_quantity = wpsc_cart_item_count();
			//exit('Quantity :'.$total_quantity);
			switch($c['logic']) {
				case 'equal'://Checks if the quantity of products in the cart equals condition value
				if ($total_quantity == $c['value'])
					return true;
				break;
				
				case 'greater'://Checks if the quantity in the cart is greater than the condition value
				if ($total_quantity > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the quantity in the cart is less than the condition value
				if ($total_quantity < $c['value'])
					return true;
				break;
				
				default:
				return false;
			}
		
		} else if ($c['property'] == 'subtotal_amount'){
			$subtotal = wpsc_cart_total(false);
			//exit('<pre>'.print_r($subtotal,true).'</pre>');
			switch($c['logic']) {
				case 'equal'://Checks if the subtotal of products in the cart equals condition value
				if ($subtotal == $c['value'])
					return true;
				break;
				
				case 'greater'://Checks if the subtotal of the cart is greater than the condition value
			//	exit('triggered here'.$subtotal.'>'.$c['value']);
				if ($subtotal > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the subtotal of the cart is less than the condition value
				if ($subtotal < $c['value']){
					//exit('<pre>'.print_r($product_obj->cart->subtotal, true).'</pre>cValue'.$c['value']);
					return true;
				}else{
					return false;
				}

				break;
				
				default:
				return false;
			}
		} else {
			return apply_filters( 'wpsc_coupon_compare_logic', false, $c, $product_obj );
		}
	}
	
	/**
	* uses coupons function, no parameters
	* @return boolean if true, items in the cart do use coupons
	*/
	function uses_coupons() {
		global $wpdb;
		$coupon_info = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` WHERE active='1' ",ARRAY_A);
		if($coupon_info != NULL){
			return true;
		}else{
			return false;
		}
	}
	
		
}
?>
