<?php
$wpsc_currency_data = array();
$wpsc_title_data = array();
if(WPSC_DEBUG === true) {
	function microtime_float() {
		list($usec, $sec) = explode(" ", microtime()); 
		return ((float)$usec + (float)$sec);
	}
	
	function wpsc_debug_start_subtimer($name, $action, $loop = false) {	
		global $wpsc_debug_sections,$loop_debug_increment;
		
		if($loop === true) {
			if ($action == 'start') {
				$loop_debug_increment[$name]++;
				$wpsc_debug_sections[$name.$loop_debug_increment[$name]][$action] = microtime_float();
			} else if($action == 'stop') {
				$wpsc_debug_sections[$name.$loop_debug_increment[$name]][$action] = microtime_float();
			}
		} else {
			$wpsc_debug_sections[$name][$action] = microtime_float();		
		}
	}
	
  $wpsc_start_time = microtime_float();
} else {
	function wpsc_debug_start_subtimer($name) {
		return null;
	}
}


if(isset($_SESSION['nzshpcrt_cart'])) {
  foreach((array)$_SESSION['nzshpcrt_cart'] as $key => $item) {
      if(get_class($item) == "__PHP_Incomplete_Class") {
          $_SESSION['nzshpcrt_cart'] = unserialize($_SESSION['nzshpcrt_serialized_cart']);
    }
  }
} else {
  if(isset($_SESSION['nzshpcrt_cart'])) {
    $_SESSION['nzshpcrt_cart'] = unserialize($_SESSION['nzshpcrt_serialized_cart']);
  }
}


if(is_numeric($_GET['sessionid'])) {
  $sessionid = $_GET['sessionid'];
  $cart_log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1");
  if(is_numeric($cart_log_id)) {
    $_SESSION['nzshpcrt_cart'] = null;
    $_SESSION['nzshpcrt_serialized_cart'] = null;
    }
  }



$GLOBALS['nzshpcrt_imagesize_info'] = TXT_WPSC_IMAGESIZEINFO;
$nzshpcrt_log_states[0]['name'] = TXT_WPSC_RECEIVED;
$nzshpcrt_log_states[1]['name'] = TXT_WPSC_PROCESSING;
$nzshpcrt_log_states[2]['name'] = TXT_WPSC_PROCESSED;





function nzshpcrt_style() {
  global $wpdb,$wp_query;
  return;
  if(function_exists('xili_display4mobile')) {  //check for the function before using it
    if (xili_display4mobile() === true) {
      // instead of wrapping the whole block of code in brackets, resulting in mysterious brackets, simply break out of the function here.
      return null;
    }
  }
  
  
	if(is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['product_category']) || is_numeric(get_option('wpsc_default_category'))) {
		if(is_numeric($wp_query->query_vars['product_category'])) {
			$category_id = $wp_query->query_vars['product_category'];
		} else if(is_numeric($_GET['category'])) {
			$category_id = $_GET['category'];
		} else { 
			$category_id = get_option('wpsc_default_category');
		}
	}

	$category_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1",ARRAY_A);
	
	
	if($category_data['display_type'] != '') {
		$display_type = $category_data['display_type'];
	} else {
		$display_type = get_option('product_view');
	}
  ?>
  <style type="text/css" media="screen">
  
	<?php
	if(!defined('WPSC_DISABLE_IMAGE_SIZE_FIXES') || (constant('WPSC_DISABLE_IMAGE_SIZE_FIXES') != true)) {
    if(($display_type == 'default') ||  ($display_type == '')) {
      $thumbnail_width = get_option('product_image_width');
      if($thumbnail_width <= 0) {
        $thumbnail_width = 96;
      }
      $thumbnail_height = get_option('product_image_height'); 
      if($thumbnail_height <= 0) { 
        $thumbnail_height = 96; 
      }
      
      
    ?>
      div.default_product_display div.textcol{
        margin-left: <?php echo $thumbnail_width + 10; ?>px !important;
        _margin-left: <?php echo ($thumbnail_width/2) + 5; ?>px !important;
        min-height: <?php echo $thumbnail_height;?>px;
        _height: <?php echo $thumbnail_height;?>px;
      }
        
        
      div.default_product_display  div.textcol div.imagecol{
        position:absolute;
        top:0px;
        left: 0px;
        margin-left: -<?php echo $thumbnail_width + 10; ?>px !important;
      }
      
      div.default_product_display  div.textcol div.imagecol a img {
        width: <?php echo $thumbnail_width; ?>px;
        height: <?php echo $thumbnail_height; ?>px;
      }
      
    <?php
    }
        
      
    $single_thumbnail_width = get_option('single_view_image_width');
    $single_thumbnail_height = get_option('single_view_image_height');
    if($single_thumbnail_width <= 0) {
      $single_thumbnail_width = 128;
    }
    ?>
      div.single_product_display div.textcol{
        margin-left: <?php echo $single_thumbnail_width + 10; ?>px !important;
        _margin-left: <?php echo ($single_thumbnail_width/2) + 5; ?>px !important;
        min-height: <?php echo $single_thumbnail_height;?>px;
        _height: <?php echo $single_thumbnail_height;?>px;
      }
        
        
      div.single_product_display  div.textcol div.imagecol{
        position:absolute;
        top:0px;
        left: 0px;
        margin-left: -<?php echo $single_thumbnail_width + 10; ?>px !important;
      }
      
      div.single_product_display  div.textcol div.imagecol a img {
        width: <?php echo $single_thumbnail_width; ?>px;
        height: <?php echo $single_thumbnail_height; ?>px;
      }
      
    <?php
    $product_ids = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `thumbnail_state` IN(0,2,3)"); 
    foreach($product_ids as $product_id) {
      $individual_thumbnail_height = get_product_meta($product_id, 'thumbnail_height'); 
      $individual_thumbnail_width = get_product_meta($product_id, 'thumbnail_width');     
      if($individual_thumbnail_height> $thumbnail_height) { 
        echo "    div.default_product_display.product_view_$product_id div.textcol{\n\r"; 
        echo "            min-height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
        echo "            _height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
        echo "      }\n\r";
      } 
      if($individual_thumbnail_width> $thumbnail_width) {
          echo "      div.default_product_display.product_view_$product_id div.textcol{\n\r";
          echo "            margin-left: ".($individual_thumbnail_width + 10)."px !important;\n\r";
          echo "            _margin-left: ".(($individual_thumbnail_width/2) + 5)."px !important;\n\r";
          echo "      }\n\r";
  
          echo "      div.default_product_display.product_view_$product_id  div.textcol div.imagecol{\n\r";
          echo "            position:absolute;\n\r";
          echo "            top:0px;\n\r";
          echo "            left: 0px;\n\r";
          echo "            margin-left: -".($individual_thumbnail_width + 10)."px !important;\n\r";
          echo "      }\n\r";
  
          echo "      div.default_product_display.product_view_$product_id  div.textcol div.imagecol a img{\n\r";
          echo "            width: ".$individual_thumbnail_width."px;\n\r";
          echo "            height: ".$individual_thumbnail_height."px;\n\r";
          echo "      }\n\r";
        }
      }	
    }
    
  if(is_numeric($_GET['brand']) || (get_option('show_categorybrands') == 3)) {
    $brandstate = 'block';
    $categorystate = 'none';
  } else {
    $brandstate = 'none';
    $categorystate = 'block';
  }
      
    ?>
    div#categorydisplay{
    display: <?php echo $categorystate; ?>;
    }
    
    div#branddisplay{
    display: <?php echo $brandstate; ?>;
    }
  </style>
  <?php
  }
  
function nzshpcrt_javascript()
  {
      return null;
  $siteurl = get_option('siteurl'); 
  if(function_exists('xili_display4mobile')) {  //check for the function before using it
    if (xili_display4mobile() === true) {
      // instead of wrapping the whole block of code in brackets, resulting in mysterious brackets, simply break out of the function here.
    }
  }
  if(($_SESSION['nzshpcrt_cart'] == null) && (get_option('show_sliding_cart') == 1)) {
		?>
			<style type="text/css" media="screen">
		div#sliding_cart{
			display: none;
			}
		</style>
		<?php
	} else {
		?>
			<style type="text/css" media="screen">
		div#sliding_cart{
			display: block;
			}
		</style>
	<?php
	}
  ?>
<?php if (get_option('product_ratings') == 1){ ?>
<link href='<?php echo WPSC_URL; ?>/js/product_rater.css' rel="stylesheet" type="text/css" />
<?php } ?>
<link href='<?php echo WPSC_URL; ?>/js/thickbox.css' rel="stylesheet" type="text/css" />
<?php if (get_option('catsprods_display_type') == 1){ ?>
  <script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/slideMenu.js"></script>
<?php } ?>
<script language='JavaScript' type='text/javascript'>
jQuery.noConflict();
/* base url */
var base_url = "<?php echo $siteurl; ?>";
var WPSC_URL = "<?php echo WPSC_URL; ?>";
var WPSC_IMAGE_URL = "<?php echo WPSC_IMAGE_URL; ?>";
var WPSC_DIR_NAME = "<?php echo WPSC_DIR_NAME; ?>";
/* LightBox Configuration start*/
var fileLoadingImage = "<?php echo WPSC_URL; ?>/images/loading.gif";
var fileBottomNavCloseImage = "<?php echo WPSC_URL; ?>/images/closelabel.gif";
var fileThickboxLoadingImage = "<?php echo WPSC_URL; ?>/images/loadingAnimation.gif";
var resizeSpeed = 9;  // controls the speed of the image resizing (1=slowest and 10=fastest)
var borderSize = 10;  //if you adjust the padding in the CSS, you will need to update this variable
jQuery(document).ready( function() {
  <?php
  if(get_option('show_sliding_cart') == 1) {
    if(is_numeric($_SESSION['slider_state'])) {
      if($_SESSION['slider_state'] == 0) {
        ?>
        //jQuery("#sliding_cart").css({ display: "none"});
        <?php
			} else {
        ?>
        //jQuery("#sliding_cart").css({ display: "block"});  
        <?php
			}
    } else {
			if($_SESSION['nzshpcrt_cart'] == null) {
				?>
				//jQuery("#sliding_cart").css({ display: "none"});  
				<?php
			} else {
				?>
				//jQuery("#sliding_cart").css({ display: "block"});  
				<?php
			}
		}
	}
  ?>
});
</script>

<script src="<?php echo WPSC_URL; ?>/ajax.js" language='JavaScript' type="text/javascript"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/jquery.jeditable.pack.js"></script>
<script src="<?php echo WPSC_URL; ?>/user.js" language='JavaScript' type="text/javascript"></script>

<?php
  $theme_path = WPSC_FILE_PATH. '/themes/';
  if((get_option('wpsc_selected_theme') != '') && (file_exists($theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css") )) {    
    ?>    
<link href='<?php echo WPSC_URL; ?>/themes/<?php echo get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css"; ?>' rel="stylesheet" type="text/css" />
    <?php
    } else {
    ?>    
<link href='<?php echo WPSC_URL; ?>/themes/default/default.css' rel="stylesheet" type="text/css" />
    <?php
    }
    ?>    
<link href='<?php echo WPSC_URL; ?>/themes/compatibility.css' rel="stylesheet" type="text/css" />
    <?php
  }





function wpsc_admin_css() {
  $siteurl = get_option('siteurl'); 
//    exit('<pre>'.print_r($_SERVER, true).'</pre>');
    if($_SERVER['REQUEST_URI'] == ''){
	    $site_request_uri = $_SERVER['ORIG_PATH_INFO'].$_SERVER['QUERY_STRING'];
    }else{
    	$site_request_uri = $_SERVER['REQUEST_URI'];
    }

  if((strpos($_SERVER['REQUEST_URI'], WPSC_DIR_NAME) !== false) || ($_GET['mass_upload'] == 'true') || ((strpos($_SERVER['REQUEST_URI'], 'wp-admin/admin.php') !== false) && !isset($_GET['page']))) {
  	if(function_exists('add_object_page')) {
  		echo "<link href='".WPSC_URL."/admin_2.7.css' rel='stylesheet' type='text/css' />";
  	} else {
  		echo "<link href='".WPSC_URL."/admin.css' rel='stylesheet' type='text/css' />";
  	}
?>

<link href='<?php echo WPSC_URL; ?>/js/jquery.ui.tabs.css' rel="stylesheet" type="text/css" />
<?php
if (($_GET['page'] == WPSC_DIR_NAME.'/display-log.php') || ($_GET['page'] == WPSC_DIR_NAME.'/gold_cart_files/affiliates.php') || ($_GET['page'] == WPSC_DIR_NAME.'/wpsc-admin/display-sales-logs.php')) {
	?>
		<link href='<?php echo $siteurl; ?>/wp-admin/css/dashboard.css?ver=2.6' rel="stylesheet" type="text/css" />
	<?php
}
?>
<!-- <link href='<?php echo WPSC_URL; ?>/thickbox.css' rel="stylesheet" type="text/css" /> -->
<script src="<?php echo WPSC_URL; ?>/ajax.js" language='JavaScript' type="text/javascript"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/jquery.tooltip.js"></script>
<!--		<script src="http://dev.jquery.com/view/tags/ui/latest/ui/ui.core.js"></script>
<script src="http://dev.jquery.com/view/tags/ui/latest/ui/ui.sortable.js"></script>-->
<script language='JavaScript' type='text/javascript'>
//<![CDATA[
/* base url */
var base_url = "<?php echo $siteurl; ?>";
var WPSC_URL = "<?php echo WPSC_URL; ?>";
var WPSC_IMAGE_URL = "<?php echo WPSC_IMAGE_URL; ?>";
var WPSC_DIR_NAME = "<?php echo WPSC_DIR_NAME; ?>";
/* LightBox Configuration start*/
var fileLoadingImage = "<?php echo WPSC_URL; ?>/images/loading.gif";
var fileBottomNavCloseImage = "<?php echo WPSC_URL; ?>/images/closelabel.gif";
var fileThickboxLoadingImage = "<?php echo WPSC_URL; ?>/images/loadingAnimation.gif";

var resizeSpeed = 9;  

var borderSize = 10;
/* LightBox Configuration end*/
/* custom admin functions start*/
<?php
	$hidden_boxes = get_option('wpsc_hidden_box');
	$hidden_boxes = implode(',', (array)$hidden_boxes);
	echo "var hidden_boxes = '".$hidden_boxes."';";
	echo "var IS_WP27 = '".IS_WP27."';";
    echo "var TXT_WPSC_DELETE = '".TXT_WPSC_DELETE."';\n\r";
    echo "var TXT_WPSC_TEXT = '".TXT_WPSC_TEXT."';\n\r";
    echo "var TXT_WPSC_EMAIL = '".TXT_WPSC_EMAIL."';\n\r";
    echo "var TXT_WPSC_COUNTRY = '".TXT_WPSC_COUNTRY."';\n\r";
    echo "var TXT_WPSC_TEXTAREA = '".TXT_WPSC_TEXTAREA."';\n\r";
    echo "var TXT_WPSC_HEADING = '".TXT_WPSC_HEADING."';\n\r";
    echo "var TXT_WPSC_COUPON = '".TXT_WPSC_COUPON."';\n\r";
    echo "var HTML_FORM_FIELD_TYPES =\"<option value='text' >".TXT_WPSC_TEXT."</option>";
    echo "<option value='email' >".TXT_WPSC_EMAIL."</option>";
    echo "<option value='address' >".TXT_WPSC_ADDRESS."</option>";
    echo "<option value='city' >".TXT_WPSC_CITY."</option>";
    echo "<option value='country'>".TXT_WPSC_COUNTRY."</option>";
    echo "<option value='delivery_address' >".TXT_WPSC_DELIVERY_ADDRESS."</option>";
    echo "<option value='delivery_city' >".TXT_WPSC_DELIVERY_CITY."</option>";
    echo "<option value='delivery_country'>".TXT_WPSC_DELIVERY_COUNTRY."</option>";
    echo "<option value='textarea' >".TXT_WPSC_TEXTAREA."</option>";    
    echo "<option value='heading' >".TXT_WPSC_HEADING."</option>";
    echo "<option value='coupon' >".TXT_WPSC_COUPON."</option>\";\n\r";
    
    echo "var TXT_WPSC_LABEL = '".TXT_WPSC_LABEL."';\n\r";
    echo "var TXT_WPSC_LABEL_DESC = '".TXT_WPSC_LABEL_DESC."';\n\r";
    echo "var TXT_WPSC_ITEM_NUMBER = '".TXT_WPSC_ITEM_NUMBER."';\n\r";
    echo "var TXT_WPSC_LIFE_NUMBER = '".TXT_WPSC_LIFE_NUMBER."';\n\r";
    echo "var TXT_WPSC_PRODUCT_CODE = '".TXT_WPSC_PRODUCT_CODE."';\n\r";
    echo "var TXT_WPSC_PDF = '".TXT_WPSC_PDF."';\n\r";
    
    echo "var TXT_WPSC_AND_ABOVE = '".TXT_WPSC_AND_ABOVE."';\n\r";
    echo "var TXT_WPSC_IF_PRICE_IS = '".TXT_WPSC_IF_PRICE_IS."';\n\r";
    echo "var TXT_WPSC_IF_WEIGHT_IS = '".TXT_WPSC_IF_WEIGHT_IS."';\n\r";
?>
//]]>
/* custom admin functions end*/
</script>
<!--<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/thickbox.js"></script>-->
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/jquery.tooltip.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/dimensions.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/admin.js"></script>
<?php if($_GET['page'] == 'trunk/display-coupons.php') { ?>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/ui.datepicker.js"></script>
<?php } ?>
  <style type="text/css" media="screen">
  <?php
  
    // $flash = true;
    // if ( false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac') && apache_mod_loaded('mod_security') )
    // 	$flash = false;
    
    if(get_option('wpsc_use_flash_uploader') == 1) {
      ?>
      table.flash-image-uploader {
        display: block;
      }
      
      table.browser-image-uploader {
        display: none;
      }
      <?php
    } else {
      ?>
      table.flash-image-uploader {
        display: none;
      }
      
      table.browser-image-uploader {
        display: block;
      }
      <?php
    
    }
  ?>
  </style>
<?php
	}
}


function nzshpcrt_submit_ajax()
  {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();  
  if(get_option('permalink_structure') != '') {
    $seperator ="?";
	} else {
		$seperator ="&amp;";
	}
   
   $cartt = $_SESSION['nzshpcrt_cart'];
   $cartt1=$cartt[0]->product_id;
   
  // if is an AJAX request, cruddy code, could be done better but getting approval would be impossible
if(($_POST['ajax'] == "true") || ($_GET['ajax'] == "true")) {

	if ($_POST['metabox'] == 'true') {
		$output .= "<div class='meta_box'>";
		if (get_option('multi_add')=='1')
			$output .= TXT_WPSC_QUANTITY.": <input type='text' name='quantity[]' size='3'><br>";
		if (get_option('time_requested')=='1')
			$output .= TXT_WPSC_DATE_REQUESTED.": <input type='text' class='time_requested' name='time_requested[]' size='10'><br>";
		if (get_option('commenting')=='1')
			$output .= TXT_WPSC_COMMENT.":<br><textarea type='text' name='comment[]'></textarea><br>";
			
		$output .= TXT_WPSC_LABEL.":<br><textarea type='text' name='label[]'></textarea><br>";
		$output .= "</div>";
		exit($output);
	}
	

	
	
	if ($_POST['submittogoogle']) {
		$newvalue=$_POST['value'];
		$amount=$_POST['amount'];
		$reason=$_POST['reason'];
		$comment=$_POST['comment'];
		$message=$_POST['message'];
		$amount=number_format($amount, 2, '.', '');
		$log_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = '".$_POST['id']."' LIMIT 1",ARRAY_A);  
		if (($newvalue==2) && function_exists('wpsc_member_activate_subscriptions')){
			wpsc_member_activate_subscriptions($_POST['id']);
		}
		$google_status = unserialize($log_data['google_status']);
		
		switch($newvalue) {
			case "Charge":
				if ($google_status[0]!='CANCELLED_BY_GOOGLE') {
					if ($amount=='') {
						$google_status['0']='Partially Charged';
					} else {
						$google_status['0']='CHARGED';
						$google_status['partial_charge_amount']=$amount;
					}
				}
				break;
				
			case "Cancel":
				if ($google_status[0]!='CANCELLED_BY_GOOGLE')
				$google_status[0]='CANCELLED';
				if ($google_status[1]!='DELIVERED')
					$google_status[1]='WILL_NOT_DELIVER';
				break;
				
			case "Refund":
				if ($amount=='') {
					$google_status['0']='Partially Refund';
				} else {
					$google_status['0']='REFUND';
					$google_status['partial_refund_amount']=$amount;
				}
				break;
				
			case "Ship":
				if ($google_status[1]!='WILL_NOT_DELIVER')
					$google_status[1]='DELIVERED';
				break;
				
			case "Archive":
				$google_status[1]='ARCHIVED';
				break;
		}
		$google_status_sql="UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET google_status='".serialize($google_status)."' WHERE `id` = '".$_POST['id']."' LIMIT 1";
		$wpdb->query($google_status_sql);
		$merchant_id = get_option('google_id');
		$merchant_key = get_option('google_key');
		$server_type = get_option('google_server_type');
		$currency = get_option('google_cur');
		$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type,$currency);
		$google_order_number=$wpdb->get_var("SELECT google_order_number FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = '".$_POST['id']."' LIMIT 1");
		switch ($newvalue) {
			case 'Charge':
				$Grequest->SendChargeOrder($google_order_number,$amount);
				break;
				
			case 'Ship':
				$Grequest->SendDeliverOrder($google_order_number);
				break;
				
			case 'Archive':
				$Grequest->SendArchiveOrder($google_order_number);
				break;
			
			case 'Refund':
				$Grequest->SendRefundOrder($google_order_number,$amount,$reason);
				break;
				
			case 'Cancel':
				$Grequest->SendCancelOrder($google_order_number,$reason,$comment);
				break;
			
			case 'Send Message':
				$Grequest->SendBuyerMessage($google_order_number,$message);
				break;
		}
		$newvalue++;
		$update_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '".$newvalue."' WHERE `id` = '".$_POST['id']."' LIMIT 1";  
		//$wpdb->query($update_sql);
		
		exit();
	}
  /*
	if(($_GET['user'] == "true") && is_numeric($_POST['prodid'])) {
		if(function_exists('wpsc_members_init')) {
			$memberstatus = get_product_meta($_POST['prodid'],'is_membership',true);
		}

		if(($memberstatus=='1') && ($_SESSION['nzshopcrt_cart']!=NULL)){
		} else{
			$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$_POST['prodid']."' LIMIT 1";
			$item_data = $wpdb->get_results($sql,ARRAY_A);
			if ($_POST['quantity']!='') {
				$add_quantity = $_POST['quantity'];
			}
			$item_quantity = 0;
			if($_SESSION['nzshpcrt_cart'] != null) {
				foreach($_SESSION['nzshpcrt_cart'] as $cart_key => $cart_item) {
					if (($memberstatus[0]!='1')&&($_SESSION['nzshpcrt_cart']!=NULL)){
						if($cart_item->product_id == $_POST['prodid']) {
							if(($_SESSION['nzshpcrt_cart'][$cart_key]->product_variations === $_POST['variation'])&&($_SESSION['nzshpcrt_cart'][$cart_key]->extras === $_POST['extras'])) {
								$item_quantity += $_SESSION['nzshpcrt_cart'][$cart_key]->quantity;
								$item_variations = $_SESSION['nzshpcrt_cart'][$cart_key]->product_variations;
							}
						}
					}
				}
			}
		  
		  $item_stock = null;
		  $variation_count = count($_POST['variation']);
		  if($variation_count >= 1) {
				foreach($_POST['variation'] as $value_id) {
					if(is_numeric($value_id)) {
						$value_ids[] = (int)$value_id;
					}
				}
				
        if(count($value_ids) > 0) {
          $variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$value_ids)."')");
          asort($variation_ids);
          $all_variation_ids = implode(",", $variation_ids);
        
        
          $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '".(int)$_POST['prodid']."' AND `value_id` IN ( '".implode("', '",$value_ids )."' )  AND `all_variation_ids` IN('$all_variation_ids')  GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($value_ids)."' LIMIT 1");
          
          $variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
          
          $item_stock = $variation_stock_data['stock'];
        }				
			}

			
		if($item_stock === null) {
			$item_stock = $item_data[0]['quantity'];
		}
		
			if((($item_data[0]['quantity_limited'] == 1) && ($item_stock > 0) && ($item_stock > $item_quantity)) || ($item_data[0]['quantity_limited'] == 0)) {
				$cartcount = count($_SESSION['nzshpcrt_cart']);
				if(is_array($_POST['variation'])) {  $variations = $_POST['variation'];  }  else  { $variations = null; }
				//if(is_array($_POST['extras'])) {  $extras = $_POST['extras'];  }  else  { $extras = null; }
				$updated_quantity = false;
				if($_SESSION['nzshpcrt_cart'] != null) {
					foreach($_SESSION['nzshpcrt_cart'] as $cart_key => $cart_item) {
						if ((!($memberstatus[0]=='1')&&(count($_SESSION['nzshpcrt_cart'])>0))) {
							if((int)$cart_item->product_id === (int)$_POST['prodid']) {  // force both to integer before testing for identicality
								if(($_SESSION['nzshpcrt_cart'][$cart_key]->extras === $extras)&&($_SESSION['nzshpcrt_cart'][$cart_key]->product_variations === $variations) && ((int)$_SESSION['nzshpcrt_cart'][$cart_key]->donation_price == (int)$_POST['donation_price'])) {
									if ($_POST['quantity'] != ''){
									  if(is_array($_POST['quantity'])) {
											foreach ((array)$_POST['quantity'] as $qty) {
												$_SESSION['nzshpcrt_cart'][$cart_key]->quantity += (int)$qty;
											}
										} else {
											$_SESSION['nzshpcrt_cart'][$cart_key]->quantity += (int)$_POST['quantity'];
										}
									} else {
										$_SESSION['nzshpcrt_cart'][$cart_key]->quantity++;
									}
									$_SESSION['nzshpcrt_cart'][$cart_key]->comment = $_POST['comment'];
									foreach((array)$_POST['label'] as $key => $label) {
										if ($label != '') {
											if (array_key_exists($label, $_SESSION['nzshpcrt_cart'][$cart_key]->meta)) {
												$_SESSION['nzshpcrt_cart'][$cart_key]->meta[$label]+=(int)$_POST['quantity'][$key];
												$_SESSION['nzshpcrt_cart'][$cart_key]->time_requested[$label] = $_POST['time_requested'][$key];
											} else {
												$_SESSION['nzshpcrt_cart'][$cart_key]->meta[$label] = $_POST['quantity'][$key];
												$_SESSION['nzshpcrt_cart'][$cart_key]->time_requested[$label] = $_POST['time_requested'][$key];
											}
										}
									}
									$updated_quantity = true;
								}
							}
						}
					}
				}
				if($item_data[0]['donation'] == 1) {
					$donation = $_POST['donation_price'];
				} else {
					$donation = false;
				}
				if(!(($memberstatus=='1')&&(count($_SESSION['nzshpcrt_cart'])>0))){
					$status = get_product_meta($cartt1, 'is_membership', true);
					if (function_exists('wpsc_members_init') && ( $status=='1')){
						exit();
					}	
					$parameters = array();
					if($updated_quantity === false) {
						$parameters['variation_values'] = $variations;
						$parameters['provided_price'] = $donation;
						$parameters['meta']=null;
						if($_POST['quantity'] != '') {
							$total_qty = 0;
							foreach ($_POST['quantity'] as $key=>$qty) {
								$total_qty+=$qty;
								$label[$_POST['label'][$key]] = $qty;
								$time_requested[$_POST['label'][$key]] = $_POST['time_requested'][$key];
							}
							$parameters['quantity'] = $total_qty;
							//$new_cart_item = new wpsc_cart_item($_POST['prodid'],$variations,$total_qty, $donation,$_POST['comment'],$time_requested,$label);
						} else {
							$parameters['quantity'] = 1;
						}
						//mail('tom@instinct.co.nz', 'stuff', print_r($parameters,true));
						$new_cart_item = new wpsc_cart_item($_POST['prodid'],$parameters);
						$_SESSION['nzshpcrt_cart'][] = $new_cart_item;
					}
				}
			} else {
				$quantity_limit = true;
			}
		
			$cart = $_SESSION['nzshpcrt_cart'];
		
			if (($memberstatus[0]=='1')&&(count($cart)>1)) {
			} else {
				$status = get_product_meta($cartt1, 'is_membership', true);
				if (function_exists('wpsc_members_init') && ( $status=='1')){
					exit('st');
				}

				//require_once(WPSC_FILE_PATH."/shopping_cart_functions.php"); 
			  echo  "if(document.getElementById('shoppingcartcontents') != null)
					  {
					  document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(nzshpcrt_shopping_basket_internals($cart,$quantity_limit))). "\";
					  }
					";

			  
			  if($_SESSION['slider_state'] == 0) {
				//echo  'jQuery("#sliding_cart").css({ display: "none"});'."\n\r";
				} else {
				//echo  'jQuery("#sliding_cart").css({ display: "block"});'."\n\r";
				}
			}
		}
      exit();
		} else if(($_POST['user'] == "true") && ($_POST['emptycart'] == "true")) {
			$_SESSION['nzshpcrt_cart'] = '';			
			$_SESSION['nzshpcrt_cart'] = Array();      
			echo  "if(document.getElementById('shoppingcartcontents') != null) {   
			document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "", addslashes(nzshpcrt_shopping_basket_internals($cart))). "\";
			}\n\r";
			
			if($_POST['current_page'] == get_option('shopping_cart_url')) {
			  echo "window.location = '".get_option('shopping_cart_url')."';\n\r"; // if we are on the checkout page, redirect back to it to clear the non-ajax cart too
			}
			exit();
		}*/

	if ($_POST['store_list']=="true") {
		$map_data['address'] = $_POST['addr'];
		$map_data['city'] = $_POST['city'];
		$map_data['country'] = 'US';
		$map_data['zipcode']='';
		$map_data['radius'] = '50000';
		$map_data['state'] = '';
		$map_data['submit'] = 'Find Store';
		$stores = getdistance($map_data);
		$i=0;
		while($rows = mysql_fetch_array($stores)) {
			//echo "<pre>".print_r($rows,1)."</pre>";
			if ($i==0) {
				$closest_store = $rows[5];
			}
			$i++;
			$store_list[$i] = $rows[5];
		}
	foreach ($store_list as $store){
		$output.="<option value='$store'>$store</option>";
	}
	echo $output;
	exit();
	}
    
            
    
    if(is_numeric($_POST['currencyid'])){
      $currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$_POST['currencyid']."' LIMIT 1",ARRAY_A) ;
      $price_out = null;
      if($currency_data[0]['symbol'] != '') {
        $currency_sign = $currency_data[0]['symbol_html'];
			} else {
				$currency_sign = $currency_data[0]['code'];
			}
      echo $currency_sign;
      exit();
		}
		
	if($_POST['buynow'] == "true") {
		if(is_numeric($_REQUEST['product_id']) && is_numeric($_REQUEST['price'])) {
			$id = $wpdb->escape((int)$_REQUEST['product_id']);
			$price = $wpdb->escape((float)$_REQUEST['price']);
			$downloads = get_option('max_downloads');
			$product_info = $wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = ".$id." LIMIT 1", ARRAY_A);
			if(count($product_info) > 0) {
				$sessionid = (mt_rand(100,999).time());
				$sql = "INSERT INTO `".WPSC_TABLE_PURCHASE_LOGS."` ( `totalprice` , `sessionid` , `date`, `billing_country`, `shipping_country`,`shipping_region`, `user_ID`, `discount_value` ) VALUES ( '".$price."', '".$sessionid."', '".time()."', 'BuyNow', 'BuyNow', 'BuyNow' , NULL , 0)";
				$wpdb->query($sql) ;
				$log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` IN('".$sessionid."') LIMIT 1") ;
				$cartsql = "INSERT INTO `".WPSC_TABLE_CART_CONTENTS."` ( `prodid` , `purchaseid`, `price`, `pnp`, `gst`, `quantity`, `donation`, `no_shipping` ) VALUES ('".$id."', '".$log_id."','".$price."','0', '0','1', '".$donation."', '1')";
				$wpdb->query($cartsql);
				$wpdb->query("INSERT INTO `".WPSC_TABLE_DOWNLOAD_STATUS."` ( `fileid` , `purchid` , `downloads` , `active` , `datetime` ) VALUES ( '".$product_info['file']."', '".$log_id."', '$downloads', '0', NOW( ));");
			}
		}
		exit();
	}

	
    
    /* rate item */    
    if(($_POST['rate_item'] == "true") && is_numeric($_POST['product_id']) && is_numeric($_POST['rating'])) {
      $nowtime = time();
      $prodid = $_POST['product_id'];
      $ip_number = $_SERVER['REMOTE_ADDR'];
      $rating = $_POST['rating'];
      
      $cookie_data = explode(",",$_COOKIE['voting_cookie'][$prodid]);
      
      if(is_numeric($cookie_data[0]) && ($cookie_data[0] > 0)) {
        $vote_id = $cookie_data[0];
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_RATING."` SET `rated` = '".$rating."' WHERE `id` ='".$vote_id."' LIMIT 1 ;");
			} else {
				$insert_sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_RATING."` ( `ipnum`  , `productid` , `rated`, `time`) VALUES ( '".$ip_number."', '".$prodid."', '".$rating."', '".$nowtime."');";
				$wpdb->query($insert_sql);
				
				$data = $wpdb->get_results("SELECT `id`,`rated` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `ipnum`='".$ip_number."' AND `productid` = '".$prodid."'  AND `rated` = '".$rating."' AND `time` = '".$nowtime."' ORDER BY `id` DESC LIMIT 1",ARRAY_A) ;
				
				$vote_id = $data[0]['id'];
				setcookie("voting_cookie[$prodid]", ($vote_id.",".$rating),time()+(60*60*24*360));
			}
      
      
      
      $output[1]= $prodid;
      $output[2]= $rating;
      echo $output[1].",".$output[2];
      exit();
		}
//written by allen
	if ($_REQUEST['save_tracking_id'] == "true"){
		$id = $_POST['id'];
		$value = $_POST['value'];
		$update_sql = "UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET track_id = '".$value."' WHERE id=$id";
		$wpdb->query($update_sql);
		exit();
	}
      
	if(($_POST['get_updated_price'] == "true") && is_numeric($_POST['product_id'])) {
		$notax = $wpdb->get_var("SELECT `notax` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN('".$_POST['product_id']."') LIMIT 1");
		foreach((array)$_POST['variation'] as $variation) {
			if(is_numeric($variation)) {
				$variations[] = (int)$variation;
			}
		}
		$pm=$_POST['pm'];
		echo "product_id=".(int)$_POST['product_id'].";\n";
		
		echo "price=\"".nzshpcrt_currency_display(calculate_product_price((int)$_POST['product_id'], $variations,'stay',$extras), $notax, true)."\";\n";
		echo "numeric_price=\"".number_format(calculate_product_price((int)$_POST['product_id'], $variations,'stay',$extras), 2)."\";\n";
				//exit(print_r($extras,1));
		exit(" ");
  }
      
      
      
      
   
 


// 	if(($_POST['redisplay_variation_values'] == "true")) {
// 		$variation_processor = new nzshpcrt_variations();
// 		$variations_selected = array_values(array_unique(array_merge((array)$_POST['new_variation_id'], (array)$_POST['variation_id'])));		
// 		foreach($variations_selected as $variation_id) {
// 		  // cast everything to integer to make sure nothing nasty gets in.
// 		  $variation_list[] = (int)$variation_id;
// 		}
// 		echo $variation_processor->variations_add_grid_view((array)$variation_list);
// 		//echo "/*\n\r".print_r(array_values(array_unique($_POST['variation_id'])),true)."\n\r*/";
// 		exit();
// 	}
// 	


      
      
      /*
       * function for handling the checkout billing address
       */      
		if(preg_match("/[a-zA-Z]{2,4}/", $_POST['billing_country'])) {
			if($_SESSION['selected_country'] == $_POST['billing_country']) {
				$do_not_refresh_regions = true;
			} else {
				$do_not_refresh_regions = false;
				$_SESSION['selected_country'] = $_POST['billing_country'];
			}
      if(is_numeric($_POST['form_id'])) {
        $form_id = $_POST['form_id'];
        $html_form_id = "region_country_form_$form_id";
			} else {
				$html_form_id = 'region_country_form';
			}
        
			if(is_numeric($_POST['billing_region'])) {
				$_SESSION['selected_region'] = $_POST['billing_region'];
			}
      $cart =& $_SESSION['nzshpcrt_cart'];
			if (($memberstatus[0]=='1')&&(count($cart)>0)){
				echo "\n\r";
			} else {
				if ($status[0]=='1'){
					exit();
				}
			  echo  "if(document.getElementById('shoppingcartcontents') != null)
					  {
					  document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(nzshpcrt_shopping_basket_internals($cart,$quantity_limit))). "\";
					  }\n\r";
		
			  if($do_not_refresh_regions == false) {
					$region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".$_POST['billing_country']."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
				  if($region_list != null) {
						$output .= "<select name='collected_data[".$form_id."][1]' class='current_region' onchange='set_billing_country(\\\"$html_form_id\\\", \\\"$form_id\\\");'>";
						//$output .= "<option value=''>None</option>";
						foreach($region_list as $region) {
							if($_SESSION['selected_region'] == $region['id']) {
								$selected = "selected='true'";
							} else {
								$selected = "";
							}
							$output .= "<option value='".$region['id']."' $selected>".$region['name']."</option>";
						}
						$output .= "</select>";
						echo  "if(document.getElementById('region_select_$form_id') != null)
							{
							document.getElementById('region_select_$form_id').innerHTML = \"".$output."\";
							}\n\r";
					} else {
						echo  "if(document.getElementById('region_select_$form_id') != null)
						{
						document.getElementById('region_select_$form_id').innerHTML = \"\";
						}\n\r";
					}
				}
			}
		if ($_POST['changetax'] == "true") {
				if (isset($_POST['billing_region'])){
					$billing_region=$_POST['billing_region'];
				} else {
					$billing_region=$_SESSION['selected_region'];
				}
				$billing_country=$_POST['billing_country'];
				$price = 0;
				$tax = 0;
				foreach((array)$cart as $cart_item) {
					$product_id = $cart_item->product_id;
					$quantity = $cart_item->quantity;
					//echo("<pre>".print_r($cart_item->product_variations,true)."</pre>");
					$product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
				
					if($product['donation'] == 1) {
						$price += $quantity * $cart_item->donation_price;
					} else {
						$product_price = $quantity * calculate_product_price($product_id, $cart_item->product_variations);
						if($product['notax'] != 1) {
							$tax += nzshpcrt_calculate_tax($product_price, $billing_country, $billing_region) - $product_price;
						}
						$price += $product_price;
						$all_donations = false;
					}
		
					if($_SESSION['delivery_country'] != null) {
						$total_shipping += nzshpcrt_determine_item_shipping($product['id'], $quantity, $_SESSION['delivery_country']);
					}
				}
				
				$total_shipping +=  nzshpcrt_determine_base_shipping(0, $_SESSION['delivery_country']);
				
				$total = number_format(($tax+$price+$total_shipping), 2);
				
				
					if($tax > 0) {
						echo  "jQuery(\"tr.total_tax td\").show();\n\r";
					} else {
						echo  "jQuery(\"tr.total_tax td\").hide();\n\r";
					}
					$tax = number_format($tax,2);
					echo  "jQuery('#checkout_tax').html(\"<span class='pricedisplay'>\${$tax}</span>\");\n\r";
					echo  "jQuery('#checkout_total').html(\"<span class='pricedisplay'>\${$total}</span><input id='shopping_cart_total_price' type='hidden' value='\${$total}'>\");\n\r";
			}  
			exit();
		}
    
    if(($_POST['get_country_tax'] == "true") && preg_match("/[a-zA-Z]{2,4}/",$_POST['country_id'])) {
      $country_id = $_POST['country_id'];
      $region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".$country_id."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
      if($region_list != null) {
        echo "<select name='base_region'>\n\r";
        foreach($region_list as $region) {
          if(get_option('base_region')  == $region['id']) {
            $selected = "selected='true'";
					} else {
						$selected = "";
					}
          echo "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
				}
        echo "</select>\n\r";    
			}  else { echo "&nbsp;"; }
      exit();
		}
    /* fill product form */    
    if(($_POST['set_slider'] == "true") && is_numeric($_POST['state'])) {
      $_SESSION['slider_state'] = $_POST['state'];
      exit();
		}  /* fill category form */
      
      
     
      
    if($_GET['action'] == "register")
      {
      $siteurl = get_option('siteurl');       
      require_once( ABSPATH . WPINC . '/registration-functions.php');
      if(($_POST['action']=='register') && get_settings('users_can_register'))
        {        
        //exit("fail for testing purposes");
        $user_login = sanitize_user( $_POST['user_login'] );
        $user_email = $_POST['user_email'];
        
        $errors = array();
          
        if ( $user_login == '' )
          exit($errors['user_login'] = __('<strong>ERROR</strong>: Please enter a username.'));
      
        /* checking e-mail address */
        if ($user_email == '') {
          exit(__('<strong>ERROR</strong>: Please type your e-mail address.'));
        } else if (!is_email($user_email)) {
          exit( __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
          $user_email = '';
        }
      
        if ( ! validate_username($user_login) ) {
          $errors['user_login'] = __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.');
          $user_login = '';
        }
      
        if ( username_exists( $user_login ) )
          exit( __('<strong>ERROR</strong>: This username is already registered, please choose another one.'));
      
        /* checking the email isn't already used by another user */
        $email_exists = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
        if ( $email_exists)
          die (__('<strong>ERROR</strong>: This email address is already registered, please supply another.'));
      
      
      
        
        if ( 0 == count($errors) ) {
          $password = substr( md5( uniqid( microtime() ) ), 0, 7);
          //xit('there?');      
          $user_id = wp_create_user( $user_login, $password, $user_email );
          if ( !$user_id ) {
            exit(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_settings('admin_email')));
          } else {
            wp_new_user_notification($user_id, $password);
            ?>
<div id="login"> 
  <h2><?php _e('Registration Complete') ?></h2>
  <p><?php printf(__('Username: %s'), "<strong>" . wp_specialchars($user_login) . "</strong>") ?><br />
  <?php printf(__('Password: %s'), '<strong>' . __('emailed to you') . '</strong>') ?> <br />
  <?php printf(__('E-mail: %s'), "<strong>" . wp_specialchars($user_email) . "</strong>") ?></p>
</div>
<?php
            }
          }
        }
        else
          {
          // onsubmit='submit_register_form(this);return false;'
          echo "<div id='login'>
    <h2>Register for this blog</h2>
    <form id='registerform' action='index.php?ajax=true&amp;action=register'  onsubmit='submit_register_form(this);return false;' method='post'>
      <p><input type='hidden' value='register' name='action'/>
      <label for='user_login'>Username:</label><br/> <input type='text' value='' maxlength='20' size='20' id='user_login' name='user_login'/><br/></p>
      <p><label for='user_email'>E-mail:</label><br/> <input type='text' value='' maxlength='100' size='25' id='user_email' name='user_email'/></p>
      <p>A password will be emailed to you.</p>
      <p class='submit'><input type='submit' name='submit_form' id='submit' value='".TXT_WPSC_REGISTER." »'/><img id='register_loading_img' src='".WPSC_URL."/images/loading.gif' alt='' title=''></p>

      
    </form>
    </div>";
         }
      
      exit();
      } 
      
    }
    /*
    * AJAX stuff stops here, I would put an exit here, but it may screw up other plugins
    //exit();
    */
    }
  
    
    
  if(($_GET['rss'] == "true") && ($_GET['action'] == "product_list")) {
    $siteurl = get_option('siteurl');    
    if(is_numeric($_GET['limit'])) {
      $limit = "LIMIT ".$_GET['limit']."";
		} else {
      $limit = '';
		}
    
    // LIMIT $startnum
    if(is_numeric($_GET['product_id'])) {
      $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN('1') AND `id` IN('".$_GET['product_id']."') LIMIT 1";
      } else if($_GET['random'] == 'true') {
      $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN('1') ORDER BY RAND() $limit";
      } else if(is_numeric($_GET['category_id'])) {
      /* man, this is a hard to read SQL statement */
      $sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.*, `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id`,`".WPSC_TABLE_PRODUCT_ORDER."`.`order`, IF(ISNULL(`".WPSC_TABLE_PRODUCT_ORDER."`.`order`), 0, 1) AS `order_state` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` ON ( ( `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_PRODUCT_ORDER."`.`product_id` ) AND ( `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_ORDER."`.`category_id` ) ) WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`active` = '1' AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` IN ('".$_GET['category_id']."') ORDER BY `order_state` DESC,`".WPSC_TABLE_PRODUCT_ORDER."`.`order` ASC $limit";      
    } else {
      $sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN('1') ORDER BY `id` DESC $limit";
    }
    
//     include_once(WPSC_FILE_PATH."/product_display_functions.php");
    
    
		if(isset($_GET['category_id']) and is_numeric($_GET['category_id'])){
			$selected_category = "&amp;category_id=".$_GET['category']."";
		}
		$self = get_option('siteurl')."/index.php?rss=true&amp;action=product_list$selected_category";
    
    $product_list = $wpdb->get_results($sql,ARRAY_A);
    header("Content-Type: application/xml; charset=UTF-8"); 
    header('Content-Disposition: inline; filename="E-Commerce_Product_List.rss"');
    $output = "<?xml version='1.0'?>\n\r";
    $output .= "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom' xmlns:product='http://www.buy.com/rss/module/productV2/'>\n\r";    
    $output .= "  <channel>\n\r";
    $output .= "    <title>".get_option('blogname')." Products</title>\n\r";
    $output .= "    <link>".get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php</link>\n\r";
    $output .= "    <description>This is the WP E-Commerce Product List RSS feed</description>\n\r";
    $output .= "    <generator>WP E-Commerce Plugin</generator>\n\r";
    $output .= "    <atom:link href='$self' rel='self' type='application/rss+xml' />";
    foreach($product_list as $product) {
      $purchase_link = wpsc_product_url($product['id']);
      $output .= "    <item>\n\r";
      $output .= "      <title>".htmlentities(stripslashes($product['name']), ENT_NOQUOTES, 'UTF-8')."</title>\n\r";
      $output .= "      <link>$purchase_link</link>\n\r";
      //$output .= "      <description>".htmlentities(stripslashes($product['description']), ENT_NOQUOTES, 'UTF-8')."</description>\n\r";
      $output .= "      <description><![CDATA[\n".htmlentities(stripslashes($product['description']), ENT_NOQUOTES, 'UTF-8')."\n]]></description>\n\r";
      $output .= "      <pubDate>".date("r")."</pubDate>\n\r";
      $output .= "      <guid>$purchase_link</guid>\n\r"; 
      if($product['thumbnail_image'] != null) {
        $image_file_name = $product['thumbnail_image'];
        } else {
        $image_file_name = $product['image'];
        }      
      $image_path = WPSC_THUMBNAIL_DIR.$image_file_name;
      if(is_file($image_path) && (filesize($image_path) > 0)) {
        $image_data = @getimagesize($image_path); 
        $image_link = WPSC_THUMBNAIL_URL.$product['image'];
        $output .= "      <enclosure url='$image_link' length='".filesize($image_path)."' type='".$image_data['mime']."' width='".$image_data[0]."' height='".$image_data[1]."' />\n\r"; 
        }
      $output .= "      <product:price>".$product['price']."</product:price>\n\r";
      $output .= "    </item>\n\r";
      }
    $output .= "  </channel>\n\r";
    $output .= "</rss>";
    echo $output;
    exit();
    }
    
  
if($_GET['termsandconds'] === 'true'){
	echo stripslashes(get_option('terms_and_conditions'));
	exit();
}

      


function nzshpcrt_download_file() {
  global $wpdb,$user_level,$wp_rewrite; 
  get_currentuserinfo();  
  function readfile_chunked($filename, $retbytes = true) {
    $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
    $buffer = '';
    $cnt = 0;
    $handle = fopen($filename, 'rb');
    if($handle === false) {
      return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			ob_flush();
			flush();
			if($retbytes)	{
				$cnt += strlen($buffer);
			}
		}
    $status = fclose($handle);
    if($retbytes && $status) {
      return $cnt; // return num. bytes delivered like readfile() does.
		}
    return $status;
	}  
  
  if(isset($_GET['downloadid'])) {
    // strip out anything that isnt 'a' to 'z' or '0' to '9'
    //ini_set('max_execution_time',10800);
    $downloadid = preg_replace("/[^a-z0-9]+/i",'',strtolower($_GET['downloadid']));
    
		$download_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `uniqueid` = '".$downloadid."' AND `downloads` > '0' AND `active`='1' LIMIT 1",ARRAY_A);
		
		if(($download_data == null) && is_numeric($downloadid)) {
		  $download_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `id` = '".$downloadid."' AND `downloads` > '0' AND `active`='1' AND `uniqueid` IS NULL LIMIT 1",ARRAY_A);
		}
		
		if((get_option('wpsc_ip_lock_downloads') == 1) && ($_SERVER['REMOTE_ADDR'] != null)) {
		  $ip_number = $_SERVER['REMOTE_ADDR'];
		  if($download_data['ip_number'] == '') {
		    // if the IP number is not set, set it
		    $wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `ip_number` = '{$ip_number}' WHERE `id` = '{$download_data['id']}' LIMIT 1");
		  } else if($ip_number != $download_data['ip_number']) {
		    // if the IP number is set but does not match, fail here.
// 				return false;
				exit(WPSC_DOWNLOAD_INVALID);
		  }
		}
		
    //exit("<pre>".print_r($download_data,true)."</pre>");
   
    if($download_data != null) {
      if($download_data['product_id'] > 0) {
				$product_file_id = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$download_data['product_id']."' LIMIT 1");
				$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_file_id."' LIMIT 1", ARRAY_A);
      } else {
				$old_file_data = $wpdb->get_row("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1", ARRAY_A);
				
				$product_file_id = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$old_file_data['product_id']."' LIMIT 1");
				$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_file_id."' LIMIT 1", ARRAY_A);
			}
      
      if((int)$download_data['downloads'] >= 1) {
        $download_count = (int)$download_data['downloads'] - 1;
      } else {
        $download_count = 0;
      }
          $wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `downloads` = '{$download_count}' WHERE `id` = '{$download_data['id']}' LIMIT 1");
	  $cart_contents = $wpdb->get_results('SELECT `'.WPSC_TABLE_CART_CONTENTS.'`.*,`'.WPSC_TABLE_PRODUCT_LIST.'`.`file` FROM `'.WPSC_TABLE_CART_CONTENTS.'` LEFT JOIN `'.WPSC_TABLE_PRODUCT_LIST.'` ON `'.WPSC_TABLE_CART_CONTENTS.'`.`prodid`= `'.WPSC_TABLE_PRODUCT_LIST.'`.`id` WHERE `purchaseid` ='.$download_data['purchid'], ARRAY_A);
	    $dl = 0;

      foreach($cart_contents as $cart_content){
      	if($cart_content['file'] == 1){
      		$dl++;
      	}
      }
      if(count($cart_contents) == $dl){
    //  	exit('called');
         $wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '4' WHERE `id` = '".$download_data['purchid']."' LIMIT 1");
      }

	  //exit('<pre>'.print_r($cart_contents,true).'</pre>');
   
      if(is_file(WPSC_FILE_DIR.$file_data['idhash'])) {
        header('Content-Type: '.$file_data['mimetype']);      
        header('Content-Length: '.filesize(WPSC_FILE_DIR.$file_data['idhash']));
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.stripslashes($file_data['filename']).'"');
        if(isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != '')) {
          /*
          There is a bug in how IE handles downloads from servers using HTTPS, this is part of the fix, you may also need:
            session_cache_limiter('public');
            session_cache_expire(30);
          At the start of your index.php file or before the session is started
          */
          header("Pragma: public");
          header("Expires: 0");      
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: public"); 
				} else {
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');       
				}        
        $filename = WPSC_FILE_DIR.$file_data['idhash'];
        // destroy the session to allow the file to be downloaded on some buggy browsers and webservers
        session_destroy();
        readfile_chunked($filename);   
        exit();
			}
		} else {
			exit(WPSC_DOWNLOAD_INVALID);
		}
	} else {
		if(($_GET['admin_preview'] == "true") && is_numeric($_GET['product_id']) && current_user_can('edit_plugins')) {
			$product_id = $_GET['product_id'];
			$product_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
			if(is_numeric($product_data[0]['file']) && ($product_data[0]['file'] > 0)) {
				$file_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_data[0]['file']."' LIMIT 1",ARRAY_A) ;
				$file_data = $file_data[0];
				if(is_file(WPSC_FILE_DIR.$file_data['idhash'])) {
					header('Content-Type: '.$file_data['mimetype']);
					header('Content-Length: '.filesize(WPSC_FILE_DIR.$file_data['idhash']));
					header('Content-Transfer-Encoding: binary');
					if($_GET['preview_track'] != 'true') {
						header('Content-Disposition: attachment; filename="'.$file_data['filename'].'"');
					} else {
						header('Content-Disposition: inline; filename="'.$file_data['filename'].'"');
					}
					if(isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != '')) {
						header("Pragma: public");
						header("Expires: 0");      
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Cache-Control: public"); 
					} else {
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');       
					}             
					$filename = WPSC_FILE_DIR.$file_data['idhash'];  
					session_destroy();
					readfile_chunked($filename);   
					exit();
				}            
			}
    }
  }
}




function nzshpcrt_product_rating($prodid)
      {
      global $wpdb;
      $get_average = $wpdb->get_results("SELECT AVG(`rated`) AS `average`, COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `productid`='".$prodid."'",ARRAY_A);
      $average = floor($get_average[0]['average']);
      $count = $get_average[0]['count'];
      $output .= "  <span class='votetext'>";
      for($l=1; $l<=$average; ++$l)
        {
        $output .= "<img class='goldstar' src='". WPSC_URL."/images/gold-star.gif' alt='$l' title='$l' />";
        }
      $remainder = 5 - $average;
      for($l=1; $l<=$remainder; ++$l)
        {
        $output .= "<img class='goldstar' src='". WPSC_URL."/images/grey-star.gif' alt='$l' title='$l' />";
        }
      $output .=  "<span class='vote_total'>&nbsp;(<span id='vote_total_$prodid'>".$count."</span>)</span> \r\n";
      $output .=  "</span> \r\n";
      return $output;
      }

// this appears to have some star rating code in it
function nzshpcrt_product_vote($prodid, $starcontainer_attributes = '')
      {
      global $wpdb;
      $output = null;
      $useragent = $_SERVER['HTTP_USER_AGENT'];
      $visibility = "style='display: none;'";
      
      preg_match("/(?<=Mozilla\/)[\d]*\.[\d]*/", $useragent,$rawmozversion );
      $mozversion = $rawmozversion[0];
      if(stristr($useragent,"opera"))
        {
        $firstregexp = "Opera[\s\/]{1}\d\.[\d]+";
        }
        else
          {
          $firstregexp = "MSIE\s\d\.\d";
          }
      preg_match("/$firstregexp|Firefox\/\d\.\d\.\d|Netscape\/\d\.\d\.\d|Safari\/[\d\.]+/", $useragent,$rawbrowserinfo);
      $browserinfo = preg_split("/[\/\s]{1}/",$rawbrowserinfo[0]);
      $browsername = $browserinfo[0];
      $browserversion = $browserinfo[1];  
      
      //exit($browsername . " " . $browserversion);
       
      if(($browsername == 'MSIE') && ($browserversion < 7.0))
        {
        $starimg = ''. get_option('siteurl').'/wp-content/plugins/'.WPSC_DIR_NAME.'/images/star.gif';
        $ie_javascript_hack = "onmouseover='ie_rating_rollover(this.id,1)' onmouseout='ie_rating_rollover(this.id,0)'";
        }
        else 
          {
          $starimg = ''. get_option('siteurl').'/wp-content/plugins/'.WPSC_DIR_NAME.'/images/24bit-star.png';
          $ie_javascript_hack = '';
          }
       
      $cookie_data = explode(",",$_COOKIE['voting_cookie'][$prodid]);
       
      if(is_numeric($cookie_data[0]))
        {
        $vote_id = $cookie_data[0];
        }
      
      $chkrate = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `id`='".$vote_id."' LIMIT 1",ARRAY_A);
      //$output .= "<pre>".print_r($chkrate,true)."</pre>";
      if($chkrate[0]['rated'] > 0)
        {
        $rating = $chkrate[0]['rated'];
        $type = 'voted';
        }
        else
          {
          $rating = 0;
          $type = 'voting';
          }
      //$output .= "<pre>".print_r($rating,true)."</pre>";
      $output .=  "<div class='starcontainer' $starcontainer_attributes >\r\n";
      for($k=1; $k<=5; ++$k)
        {
        $style = '';
        if($k <= $rating)
          {
          $style = "style='background: url(". WPSC_URL."/images/gold-star.gif)'";
          }
        $output .= "      <a id='star".$prodid."and".$k."_link' onclick='rate_item(".$prodid.",".$k.")' class='star$k' $style $ie_javascript_hack ><img id='star".$prodid."and".$k."' class='starimage' src='$starimg' alt='$k' title='$k' /></a>\r\n";
        }
      $output .=  "   </div>\r\n";
      $output .= "";
      $voted = TXT_WPSC_CLICKSTARSTORATE;
      
      switch($ratecount[0]['count'])
        {
        case 0:
        $votestr = TXT_WPSC_NOVOTES;
        break;
        
        case 1:
        $votestr = TXT_WPSC_1VOTE;
        break;
        
        default:
        $votestr = $ratecount[0]['count']." ".TXT_WPSC_VOTES2;
        break;
        }
        
      for($i= 5; $i>= 1; --$i)
         {
        //$tmpcount = $this->db->GetAll("SELECT COUNT(*) AS 'count' FROM `pxtrated` WHERE `pxtid`=".$dbdat['rID']." AND `rated`=$i");
            
         switch($tmpcount[0]['count'])
           {
           case 0:
           $othervotes .= "";
           break;
           
           case 1:
           $othervotes .= "<br />". $tmpcount[0]['count'] . " ".TXT_WPSC_PERSONGIVEN." $i ".TXT_WPSC_PERSONGIVEN2;
           break;
           
           default:
           $othervotes .= "<br />". $tmpcount[0]['count'] . " ".TXT_WPSC_PEOPLEGIVEN." $i ".TXT_WPSC_PEOPLEGIVEN2;
           break;
           }  
         } /*
      $output .=  "</td><td class='centerer2'>&nbsp;</td></tr>\r\n";
      $output .= "<tr><td colspan='3' class='votes' >\r\n";//id='startxtmove'
      $output .= "   <p class='votes'> ".$votestr."<br />$voted <br />
      $othervotes</p>";*/
      
      return Array($output,$type);
      } //*/

function get_brand($brand_id) {  }


function filter_input_wp($input) {
  // if the input is numeric, then its probably safe
  if(is_numeric($input)) {
    $output = $input;
	} else {
		// if its not numeric, then make it safe
		if(!get_magic_quotes_gpc()) {
			$output = mysql_real_escape_string($input);
		} else {
			$output = mysql_real_escape_string(stripslashes($input));
		}
	}
	return $output;
}
    
function make_csv($array) {
  $count = count($array);
  $num = 1;
  foreach($array as $value) {
    $output .= "'$value'";
    if($num < $count) {
      $output .= ",";
		}
    $num++;
	}
  return $output;
}   
  
function nzshpcrt_product_log_rss_feed() {
  echo "<link type='application/rss+xml' href='".get_option('siteurl')."/wp-admin/index.php?rss=true&amp;rss_key=key&amp;action=purchase_log&amp;type=rss' title='WP E-Commerce Purchase Log RSS' rel='alternate'/>";
}
  
function nzshpcrt_product_list_rss_feed() {
  if(isset($_GET['category']) and is_numeric($_GET['category'])){
    $selected_category = "&amp;category_id=".$_GET['category']."";
	}
  echo "<link rel='alternate' type='application/rss+xml' title='".get_option('blogname')." Product List RSS' href='".get_option('siteurl')."/index.php?rss=true&amp;action=product_list$selected_category'/>";
}

  
 
 
function nzshpcrt_listdir($dirname) {
  /*
  lists the merchant directory
  */
  $dir = @opendir($dirname);
  $num = 0;
  while(($file = @readdir($dir)) !== false) {
    //filter out the dots and any backup files, dont be tempted to correct the "spelling mistake", its to filter out a previous spelling mistake.
    if(($file != "..") && ($file != ".") && !stristr($file, "~") && !stristr($file, "Chekcout") && !( strpos($file, ".") === 0 )) {
      $dirlist[$num] = $file;
      $num++;
    }
  }
  if($dirlist == null) {
    $dirlist[0] = "paypal.php";
    $dirlist[1] = "testmode.php";
  }
  return $dirlist; 
}
 
function wpsc_include_css_and_javascript() {
  // This must be weapped in a function in order to selectively prevent it from running using filters
  if(!apply_filters( 'wpsc_override_header', false)) {
    // expects false in order to to include the css and javascript
    add_action('wp_head', 'nzshpcrt_style');
    add_action('wp_head', 'nzshpcrt_javascript');
  }
}

//add_action('init', 'wpsc_include_css_and_javascript');
add_action('wp_head', 'nzshpcrt_product_list_rss_feed');



add_action('admin_head', 'wpsc_admin_css');
if($_GET['page'] == WPSC_DIR_NAME."/display-log.php") {
  add_action('admin_head', 'nzshpcrt_product_log_rss_feed');
}

if(($_POST['submitwpcheckout'] == 'true')) {
  //add_action('init', 'nzshpcrt_submit_checkout');
}
add_action('init', 'nzshpcrt_submit_ajax');
add_action('init', 'nzshpcrt_download_file');

if(stristr($_GET['page'], WPSC_DIR_NAME)) {
  add_action('admin_notices', 'wpsc_admin_notices');
}

function wpsc_admin_notices() {
  global $wpdb;
//  exit(get_option('wpsc_default_category'));
  if(get_option('wpsc_default_category') != 'all+list' && get_option('wpsc_default_category') != 'all' && get_option('wpsc_default_category') != 'list') {
		if((get_option('wpsc_default_category') < 1) || $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('".get_option('wpsc_default_category')."') AND `active` NOT IN ('1');")) {  // if there is no default category or it is deleted
			if(!$_POST['wpsc_default_category']) { // if we are not changing the default category
				echo "<div id='message' class='updated fade' style='background-color: rgb(255, 251, 204);'>";
				echo "<p>".TXT_WPSC_NO_DEFAULT_PRODUCTS."</p>";
				echo "</div>\n\r";
			}
		}
  }
}


//this adds all the admin pages, before the code was a mess, now it is slightly less so.

// pe.{
if((get_option('wpsc_share_this') == 1) && (get_option('product_list_url') != '')) {
  if(stristr(("http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), get_option('product_list_url'))){
    include_once(WPSC_FILE_PATH."/share-this.php");
  }
}
 
/*
add_filter('option_update_plugins', 'wpsc_plugin_no_upgrade');
function wpsc_plugin_no_upgrade($option) {
	$this_plugin = plugin_basename(__FILE__);
  //echo "<pre>".print_r($option->response[ $this_plugin ],true)."</pre>";
	if( isset($option->response[ $this_plugin ]) ) {
		$option->response[ $this_plugin ]->package = '';
	}
	return $option;
}
*/

// if(get_option('cat_brand_loc') != 0) {
//   add_action('wp_list_pages', 'show_cats_brands');
//   }
// }.pe
add_action('plugins_loaded', 'widget_wp_shopping_cart_init', 10);


// refresh page urls when permalinks are turned on or altered
add_filter('mod_rewrite_rules', 'wpsc_refresh_page_urls');

// refresh the page URL's when permalinks are turned off
// the plugin hook used just above doesnt run when they are turned off
// if(stristr($_POST['_wp_http_referer'], 'options-permalink.php')) {
// 	add_filter('admin_head', 'wpsc_refresh_page_urls');
// }


if(strpos($_SERVER['SCRIPT_NAME'], "wp-admin") === false) {
  //wp_enqueue_script( 'jQuery', WPSC_URL.'/js/jquery.js', false, '1.2.3');
	//wp_enqueue_script('ngg-thickbox',WPSC_URL.'/js/thickbox.js', 'jQuery', 'Instinct_e-commerce');
} else {

	//wp_enqueue_script('thickbox');
	if(function_exists('wp_enqueue_style')) {  // DO NOT ALTER THIS!! This function is not present on older versions of wordpress
	//	wp_enqueue_style( 'thickbox' );
	}
//	wp_enqueue_script('jEditable',WPSC_URL.'/js/jquery.jeditable.pack.js', array('jquery'), '2.7.4');
}
if(strpos($_SERVER['REQUEST_URI'], WPSC_DIR_NAME.'') !== false) {
		if($_GET['page'] == 'wpsc-edit-products') {
		}
}




switch(get_option('cart_location')) {
  case 1:
  add_action('wp_list_pages','nzshpcrt_shopping_basket');
  break;
  
  case 2:
  add_action('the_content', 'nzshpcrt_shopping_basket' , 14);
  break;
  
  case 4:
  break;
  
  case 5:
  break;
  
  case 3:
  //add_action('the_content', 'nzshpcrt_shopping_basket');
  //<?php nzshpcrt_shopping_basket(); ?/>   
  break;
  
  default:
  add_action('the_content', 'nzshpcrt_shopping_basket', 14);
  break;
}





function thickbox_variation() {
	global $wpdb, $wpsc_siteurl;
	$variations_processor = new nzshpcrt_variations;
	echo "<head>";
	echo "<link rel='stylesheet' href='{$wpsc_siteurl}/wp-admin/wp-admin.css?ver=2.6.3' type='text/css' media='all' />
	<link rel='stylesheet' href='{$wpsc_siteurl}/wp-admin/css/colors-fresh.css?ver=2.6.3' type='text/css' media='all' />
	<link href='{$wpsc_siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/admin.css' rel='stylesheet' type='text/css'/>
	<link rel='stylesheet' href='{$wpsc_siteurl}/wp-admin/css/global.css?ver=2.6.3' type='text/css' media='all' />";
	echo "<script type='text/javascript' src='{$wpsc_siteurl}/wp-includes/js/jquery/jquery.js?ver=1.2.6'></script>";
	echo "<script type='text/javascript' src='{$wpsc_siteurl}/wp-includes/js/thickbox/thickbox.js?ver=3.1-20080430'></script>
	<script language='JavaScript' type='text/javascript' src='{$wpsc_siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/js/jquery.tooltip.js'></script>
<script type='text/javascript' src='{$wpsc_siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/js/jquery-ui.js?ver=1.6'></script>
<script type='text/javascript' src='{$wpsc_siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/js/jquery.jeditable.pack.js?ver=2.7.4'></script>
<script type='text/javascript' src='{$wpsc_siteurl}/wp-includes/js/swfupload/swfupload.js?ver=2.0.2-20080430'></script>
";
	echo "<script language='JavaScript' type='text/javascript'>
			var base_url = '".$wpsc_siteurl."';
			var WPSC_URL = '".WPSC_URL."';
			var WPSC_IMAGE_URL = '".WPSC_IMAGE_URL."';";
		echo "var TXT_WPSC_DELETE = '".TXT_WPSC_DELETE."';\n\r";
		echo "var TXT_WPSC_TEXT = '".TXT_WPSC_TEXT."';\n\r";
		echo "var TXT_WPSC_EMAIL = '".TXT_WPSC_EMAIL."';\n\r";
		echo "var TXT_WPSC_COUNTRY = '".TXT_WPSC_COUNTRY."';\n\r";
    echo "var TXT_WPSC_TEXTAREA = '".TXT_WPSC_TEXTAREA."';\n\r";
    echo "var TXT_WPSC_HEADING = '".TXT_WPSC_HEADING."';\n\r";
    echo "var TXT_WPSC_COUPON = '".TXT_WPSC_COUPON."';\n\r";
    echo "var HTML_FORM_FIELD_TYPES =\"<option value='text' >".TXT_WPSC_TEXT."</option>";
    echo "<option value='email' >".TXT_WPSC_EMAIL."</option>";
    echo "<option value='address' >".TXT_WPSC_ADDRESS."</option>";
    echo "<option value='city' >".TXT_WPSC_CITY."</option>";
    echo "<option value='country'>".TXT_WPSC_COUNTRY."</option>";
    echo "<option value='delivery_address' >".TXT_WPSC_DELIVERY_ADDRESS."</option>";
    echo "<option value='delivery_city' >".TXT_WPSC_DELIVERY_CITY."</option>";
    echo "<option value='delivery_country'>".TXT_WPSC_DELIVERY_COUNTRY."</option>";
    echo "<option value='textarea' >".TXT_WPSC_TEXTAREA."</option>";
    echo "<option value='heading' >".TXT_WPSC_HEADING."</option>";
    echo "<option value='coupon' >".TXT_WPSC_COUPON."</option>\";\n\r";
		
	echo	"</script>";
		
	echo "<script language='JavaScript' type='text/javascript' src='".WPSC_URL."/wpsc_admin/js/jquery.livequery.js'></script>";
	echo "<script language='JavaScript' type='text/javascript' src='".WPSC_URL."/wpsc_admin/js/admin.js'></script>";
	echo "<script language='JavaScript' type='text/javascript' src='".WPSC_URL."/wpsc_admin/js/variations.js'></script>";
	
	echo "</head>";
	if($_POST){
				if($_POST['submit_action'] == "add") {
    //exit("<pre>".print_r($_POST,true)."</pre>");
    $variation_sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_VARIATIONS."` (`name`, `variation_association`) VALUES ( '".$_POST['name']."', 0);";
    if($wpdb->query($variation_sql)) {
      $variation_id = $wpdb->get_results("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` LIMIT 1",ARRAY_A);
      $variation_id = $variation_id[0]['id'];
      $variation_values = $_POST['variation_values'];
      $variation_value_sql ="INSERT INTO `".WPSC_TABLE_VARIATION_VALUES."` ( `name` , `variation_id` ) VALUES ";
      $num = 0;
      foreach($variation_values as $variation_value) {
        switch($num) {
          case 0:
          $comma = '';
          break;
          
          default:
          $comma = ', ';
          break;
				}
        $variation_value_sql .= "$comma( '".$wpdb->escape(trim($variation_value))."', '".$variation_id."')";
        $num++;
			}
      $variation_value_sql .= ";";
      $wpdb->query($variation_value_sql);
      echo "<head>";
		echo "
		<script language='JavaScript' type='text/javascript' src='".WPSC_URL."/admin.js'></script>
		<script language='JavaScript' type='text/javascript'>
				parent.jQuery('#add_product_variations').html(\"".nl2br($variations_processor->list_variations())."\");
				parent.tb_remove();
		</script>";
	
		echo "</head>";

      echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASBEENADDED."</p></div>";
		} else {
			echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASNOTBEENADDED."</p></div>";
		}
	}

	}
		echo "  <table id='productpage'>\n\r";
		echo "    <tr>";
		echo "      <td class='secondcol'>\n\r";
		echo "        <div id='productform'>";
		echo "  <div class='categorisation_title'>\n\r";
		echo "		<strong class='form_group'>".TXT_WPSC_EDITVARIATION."</strong>\n\r";
		echo "	</div>\n\r";

		echo "<form method='POST'  enctype='multipart/form-data' name='editproduct$num'>";
		echo "        <div id='formcontent'>\n\r";
		echo "        </div>\n\r";
		echo "</form>";
		echo "        </div>";
		?>
		<div id='additem'>
  <div class="categorisation_title">
		<strong class="form_group"><?php echo TXT_WPSC_ADDVARIATION;?></strong>
	</div>
  <form method='POST' action='admin.php?thickbox_variations=true&amp;width=550'>
 <table class='category_forms'>
    <tr>
      <td>
        <?php echo TXT_WPSC_NAME;?>:
      </td>
      <td>
        <input type='text'  class="text" name='name' value='<?php echo $variation_name; ?>' />
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_VARIATION_VALUES;?>:
      </td>
      <td>
				<div id='variation_values'>
					<?php 
						if($variation_value_count > 0) {
							$num = 0;
							foreach($variation_values as $variation_value) {
								?>
								<div class='variation_value'>
								<input type='text' class='text' name='variation_values[<?php echo $variation_value['id']; ?>]' value='<?php echo htmlentities(stripslashes($variation_value['name']), ENT_QUOTES, 'UTF-8'); ?>' />
								<input type='hidden' class='variation_values_id' name='variation_values_id[]' value='<?php echo $variation_value['id']; ?>' />
								<?php if($variation_value_count > 1): ?>
									<a class='image_link delete_variation_value' href='#'>
									  <img src='<?php echo WPSC_URL; ?>/images/trash.gif' alt='<?php echo TXT_WPSC_DELETE; ?>' title='<?php echo TXT_WPSC_DELETE; ?>' />
									</a>
								<?php endif; ?>
								</div>
								<?php
								$num++;
							}
						} else {
							for($i = 0; $i <= $value_form_count; $i++) {
								?>
								<div class='variation_value'>
									<input type='text' class="text" name='new_variation_values[]' value='' />
										<a class='image_link delete_variation_value' href='#'>
											<img src='<?php echo WPSC_URL; ?>/images/trash.gif' alt='<?php echo TXT_WPSC_DELETE; ?>' title='<?php echo TXT_WPSC_DELETE; ?>' />
										</a>
								</div>
								<?php 
							}
					}
				?>
				</div>
				<a href='#' class='add_variation_item_form'>+ <?php _e('Add Value'); ?></a>
      </td>
    </tr>
    <tr>
      <td>
      </td>
      <td>
				<?php wp_nonce_field('edit-variation', 'wpsc-edit-variation'); ?>
        <input type='hidden' name='wpsc_admin_action' value='wpsc-variation-set' />
				
				<?php if($variation_id > 0) { ?>
					<input type='hidden' name='variation_id' value='<?php echo $variation_id; ?>' />
					<input type='hidden' name='submit_action' value='edit' />
					<input class='button' style='float:left;'  type='submit' name='submit' value='<?php echo TXT_WPSC_EDIT; ?>' />
					<a class='button delete_button' href='<?php echo wp_nonce_url("admin.php?wpsc_admin_action=wpsc-delete-variation-set&amp;deleteid={$variation_id}", 'delete-variation'); ?>' onclick="return conf();" ><?php echo TXT_WPSC_DELETE; ?></a>
					
					
				<?php } else { ?>
					<input type='hidden' name='submit_action' value='add' />
					<input class='button'  type='submit' name='submit' value='<?php echo TXT_WPSC_ADD;?>' />
				<?php } ?>
        
        
      </td>
    </tr>
  </table>
  </form>
</div>
<?php
echo "      </td></tr>\n\r";
echo "     </table>\n\r";
		
		exit();
	}
	
	if ($_GET['thickbox_variations']) {
		add_action('admin_init','thickbox_variation');
	}




add_filter('favorite_actions', 'wpsc_fav_action');
function wpsc_fav_action($actions) {
    // remove the "Add new page" link
    // unset($actions['page-new.php']);
  	// add quick link to our favorite plugin
    $actions['admin.php?page=wpsc-edit-products'] = array('New Product', 'manage_options');
    return $actions;
}

//add_action('init', 'save_hidden_box');
?>