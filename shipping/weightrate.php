<?php
class weightrate {
	var $internal_name, $name;
	function weightrate () {
		$this->internal_name = "weightrate";
		$this->name="Weight Rate";
		$this->is_external=false;
		return true;
	}
	
	function getId() {
// 		return $this->usps_id;
}
	
	function setId($id) {
// 		$usps_id = $id;
// 		return true;
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function getForm() {
	//	$output ="<table>";
		$output.="<tr><th>".TXT_WPSC_TOTAL_WEIGHT_IN_POUNDS."</th><th>".TXT_WPSC_SHIPPING_PRICE."</th></tr>";
		$layers = get_option("weight_rate_layers");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr class='rate_row'><td >";
				$output .="<i style='color: grey;'>".TXT_WPSC_IF_WEIGHT_IS."</i><input type='text' value='$key' name='weight_layer[]'size='4'><i style='color: grey;'>".TXT_WPSC_AND_ABOVE."</i></td><td>".wpsc_get_currency_symbol()."<input type='text' value='{$shipping}' name='weight_shipping[]' size='4'>&nbsp;&nbsp;<a href='#' class='delete_button' >".TXT_WPSC_DELETE."</a></td></tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='weight'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a style='cursor:pointer;' id='addweightlayer' >Add Layer</a></td></tr>";
	//	$output .="</table>";
		return $output;
	}
	
	function submit_form() {
		$layers = (array)$_POST['weight_layer'];
		$shippings = (array)$_POST['weight_shipping'];
		if ($shippings != ''){
			foreach($shippings as $key => $price) {
				if ($price == '') {
					unset($shippings[$key]);
					unset($layers[$key]);
				} else {
					$new_layer[$layers[$key]] = $price;
				}
			}
		}
		if ($_POST['checkpage'] == 'weight') {
			update_option('weight_rate_layers',$new_layer);
		}
		return true;
	}
	
	function getQuote() {
		global $wpdb;
		$weight = wpsc_cart_weight_total();
		$layers = get_option('weight_rate_layers');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					return array("Weight Rate"=>$shipping);
				}
			}
		
			return array("Weight Rate"=>array_shift($layers));
		}
	}
	
	function get_item_shipping($unit_price, $quantity, $weight, $product_id) {
	  return 0;
	}
	
	function get_cart_shipping($total_price, $weight) {
		$layers = get_option('weight_rate_layers');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					$output = $shipping;
				}
			}
		}
	  return $output;
	}
}
$weightrate = new weightrate();
$wpsc_shipping_modules[$weightrate->getInternalName()] = $weightrate;