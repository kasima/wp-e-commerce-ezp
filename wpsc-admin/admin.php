<?php
/**
 * WP eCommerce Main Admin functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

		
/// admin includes
require_once(WPSC_FILE_PATH."/wpsc-admin/display-items.page.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/display-groups.page.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/display-variations.page.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/display-upgrades.page.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/includes/display-items-functions.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/includes/product-functions.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/includes/save-data.functions.php");

require_once(WPSC_FILE_PATH."/wpsc-admin/ajax-and-init.php");

require_once(WPSC_FILE_PATH."/wpsc-admin/display-options-settings.page.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/display-sales-logs.php");

if(($_SESSION['wpsc_activate_debug_page'] == true) || (defined('WPSC_ADD_DEBUG_PAGE') && (constant('WPSC_ADD_DEBUG_PAGE') == true))) {
	require_once(WPSC_FILE_PATH."/wpsc-admin/display-debug.page.php");
}

//settings pages include
require_once(WPSC_FILE_PATH."/wpsc-admin/includes/settings-pages/general.php");


/**
	* wpsc_admin_pages function, all the definitons of admin pages are stores here.
	* No parameters, returns nothing
*/
function wpsc_admin_pages(){
  global $userdata;
    /*
     * Fairly standard wordpress plugin API stuff for adding the admin pages, rearrange the order to rearrange the pages
     * The bits to display the options page first on first use may be buggy, but tend not to stick around long enough to be identified and fixed
     * if you find bugs, feel free to fix them.
     *
     * If the permissions are changed here, they will likewise need to be changed for the other sections of the admin that either use ajax
     * or bypass the normal download system.
		*/
		/// Code to enable or disable the debug page
		if(isset($_GET['wpsc_activate_debug_page'])) {
			if($_GET['wpsc_activate_debug_page'] == 'true') {
				// if true, enable it
				$_SESSION['wpsc_activate_debug_page'] = true;
			} else if($_GET['wpsc_activate_debug_page'] == 'false') {
				// if false, disable it
				$_SESSION['wpsc_activate_debug_page'] = false;
			}
		}
		
    if(function_exists('add_options_page')) {
			$base_page = 'wpsc-sales-logs';
					
		if ($userdata->user_level <= 2) {
				if(file_exists(WPSC_UPGRADES_DIR.'gold_cart_files/affiliates.php')) {
					require_once(WPSC_UPGRADES_DIR.'gold_cart_files/affiliates.php');
					add_object_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 0,  WPSC_URL.'/gold_cart_files/affiliates.php','affiliate_page', WPSC_URL."/images/cart.png");
				} else {
					if (function_exists('add_object_page')) {
						add_object_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page,array(), WPSC_URL."/images/cart.png");
					} else {
						add_menu_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page);
					}
				}
			} else {
				if (function_exists('add_object_page')) {
					add_object_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page,array(), WPSC_URL."/images/cart.png");
					
				} else {
					add_menu_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page);
				}
			}

				
			$page_hooks[] =  add_submenu_page($base_page, TXT_WPSC_PURCHASELOG, TXT_WPSC_PURCHASELOG, 7, 'wpsc-sales-logs', 'wpsc_display_sales_logs');



			//echo add_submenu_page($base_page,__("Products"), __("Products"), 7, 'wpsc-edit-products', 'wpsc_display_products_page');
			$edit_products_page = add_submenu_page($base_page,TXT_WPSC_PRODUCTS,TXT_WPSC_PRODUCTS, 7, 'wpsc-edit-products', 'wpsc_display_edit_products_page');
			$page_hooks[] = $edit_products_page;
			
			$page_hooks[] = add_submenu_page($base_page,TXT_WPSC_CATEGORISATION, TXT_WPSC_CATEGORISATION, 7, 'wpsc-edit-groups', 'wpsc_display_groups_page');
			//print_r($page_hooks);
			
			//    add_submenu_page($base_page,TXT_WPSC_VARIATIONS, TXT_WPSC_VARIATIONS, 7, WPSC_DIR_NAME.'/display_variations.php');
			$page_hooks[] = add_submenu_page($base_page,TXT_WPSC_VARIATIONS, TXT_WPSC_VARIATIONS, 7, 'wpsc-edit-variations', 'wpsc_display_variations_page');
			
			
			
			
			
			add_submenu_page('users.php',TXT_WPSC_ECOMMERCE_SUBSCRIBERS, TXT_WPSC_ECOMMERCE_SUBSCRIBERS, 7, WPSC_DIR_NAME.'/display-ecommerce-subs.php');			
			
			
			
			foreach((array)get_option('wpsc_product_page_order') as $box) {
				$boxes[$box] = ucwords(str_replace("_"," ",$box));
			}			//exit('-->'.$help);
			if (function_exists('add_contextual_help')) {

				add_contextual_help(WPSC_DIR_NAME.'/display-log',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/sales/'>About this page</a>");

				add_contextual_help(WPSC_DIR_NAME.'/display-category',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/product-groups/'>About this page</a>");
				add_contextual_help(WPSC_DIR_NAME.'/display_variations',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/variations/'>About this page</a>");
				add_contextual_help(WPSC_DIR_NAME.'/display-coupons',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/marketing/'>About this page</a>");
				add_contextual_help(WPSC_DIR_NAME.'/options',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/shop-settings-general/'>General Settings</a><br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/presentation/'>Presentation Options</a> <br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/admin-settings/'>Admin Options</a> <br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/shipping/'>Shipping Options</a> <br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/payment-option/'>Payment Options</a> <br />");
				add_contextual_help(WPSC_DIR_NAME.'/display-items',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/products/'>About this page</a>");
			}

			add_submenu_page($base_page,TXT_WPSC_MARKETING, TXT_WPSC_MARKETING, 7, WPSC_DIR_NAME.'/display-coupons.php');

			
			$edit_options_page = add_submenu_page($base_page,TXT_WPSC_OPTIONS, TXT_WPSC_OPTIONS, 7, 'wpsc-settings', 'wpsc_display_settings_page');
			$page_hooks[] = $edit_options_page;
			
			$page_hooks[] = add_submenu_page($base_page,TXT_WPSC_UPGRADES_PAGE, TXT_WPSC_UPGRADES_PAGE, 7, 'wpsc-upgrades', 'wpsc_display_upgrades_page');
			//$page_hooks[] = add_submenu_page($base_page,TXT_WPSC_GOLD_OPTIONS, TXT_WPSC_GOLD_OPTIONS, 7, 'wpsc-gold-options','wpsc_gold_shpcrt_options_page');
			
			if(($_SESSION['wpsc_activate_debug_page'] == true) || (defined('WPSC_ADD_DEBUG_PAGE') && (constant('WPSC_ADD_DEBUG_PAGE') == true))) {			  
				$page_hooks[] = add_submenu_page($base_page,__('Debug'), __('Debug'), 9, 'wpsc-debug', 'wpsc_debug_page');
			}

			$page_hooks = apply_filters( 'wpsc_additional_pages', $page_hooks, $base_page);
			do_action('wpsc_add_submenu');
		}
		
		add_action('load-'.WPSC_DIR_NAME.'/display-coupons.php', 'wpsc_admin_include_coupon_js');
		
		// Include the javascript and CSS for this page
		
		foreach($page_hooks as $page_hook) {
			add_action("load-$page_hook", 'wpsc_admin_include_css_and_js');

			switch($page_hook) {
				case $edit_products_page:
					add_action("load-$page_hook", 'wpsc_admin_edit_products_page_js');
				break;

				
				case $edit_options_page:
					add_action("load-$page_hook", 'wpsc_admin_include_optionspage_css_and_js');
				break;
			}

		}

		/// some updating code is run from here, is as good a place as any, and better than some
		if((get_option('wpsc_trackingid_subject') ==  null) && (get_option('wpsc_trackingid_message') ==  null)) {
			update_option('wpsc_trackingid_subject', __('Product Tracking Email', 'wpsc'));
			update_option('wpsc_trackingid_message', __("Track & Trace means you may track the progress of your parcel with our online parcel tracker, just login to our website and enter the following Tracking ID to view the status of your order.\n\nTracking ID: %trackid%\n", 'wpsc'));
		}

		
		return;
  }

function wpsc_admin_include_coupon_js() {
	$version_identifier = WPSC_VERSION.".".WPSC_MINOR_VERSION;
	wp_enqueue_script('datepicker-ui', WPSC_URL."/js/ui.datepicker.js",array('jquery-ui-core'), $version_identifier);
}
  

/**
	* wpsc_admin_css_and_js function, includes the wpsc_admin CSS and JS
	* No parameters, returns nothing
*/
function  wpsc_admin_include_css_and_js() {
  $siteurl = get_option('siteurl');
	if(is_ssl()) {
		$siteurl = str_replace("http://", "https://", $siteurl);
	}
	wp_admin_css( 'dashboard' );
	wp_admin_css( 'media' );
	wp_enqueue_script( 'postbox', '/wp-admin/js/postbox.js', array('jquery'));
	
  $version_identifier = WPSC_VERSION.".".WPSC_MINOR_VERSION;
	wp_enqueue_script('jCrop', WPSC_URL.'/wpsc-admin/js/jquery.Jcrop.min.js', array('jquery'), '0.9.8');
	wp_enqueue_script('livequery', WPSC_URL.'/wpsc-admin/js/jquery.livequery.js', array('jquery'), '1.0.3');
	
	
	wp_enqueue_script('wp-e-commerce-admin-parameters', $siteurl."/wp-admin/admin.php?wpsc_admin_dynamic_js=true", false, $version_identifier);
	wp_enqueue_script('wp-e-commerce-admin', WPSC_URL.'/wpsc-admin/js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), $version_identifier);
	
	
	
	wp_enqueue_script('wp-e-commerce-legacy-ajax', WPSC_URL.'/wpsc-admin/js/ajax.js', false, $version_identifier); // needs removing
	wp_enqueue_script('wp-e-commerce-variations', WPSC_URL.'/wpsc-admin/js/variations.js', array('jquery'), $version_identifier);
	
	
	wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL.'/wpsc-admin/css/admin.css', false, $version_identifier, 'all' );
	wp_enqueue_style( 'wp-e-commerce-admin-dynamic', $siteurl."/wp-admin/admin.php?wpsc_admin_dynamic_css=true" , false, $version_identifier, 'all' );
	wp_localize_script( 'wp-e-commerce-tags', 'postL10n', array(
		'tagsUsed' =>  __('Tags used on this post:'),
		'add' => attribute_escape(__('Add')),
		'addTag' => attribute_escape(__('Add new tag')),
		'separate' => __('Separate tags with commas'),
	));
	if(defined('WPSC_GOLD_DIR_NAME') && WPSC_GOLD_DIR_NAME != ''){
		wp_enqueue_style('gold_cart', WPSC_UPGRADES_URL . 'gold_cart_files/gold_cart.css',false, $version_identifier, 'all');
	}
	//jQuery wysiwyg
	//  	if ( user_can_richedit() ) {
	// 		wp_enqueue_script('editor');
	// 	}
	// 	wp_enqueue_script('media-upload');
	//   wp_enqueue_style('thickbox');

	// Prototype breaks dragging and dropping, I need it gone
	wp_deregister_script('prototype');
	// remove the old javascript and CSS, we want it no more, it smells bad
	remove_action('admin_head', 'wpsc_admin_css');
}
  
  
  
function wpsc_admin_edit_products_page_js() {
	wp_enqueue_script('wp-e-commerce-tags', WPSC_URL.'/wpsc-admin/js/product_tagcloud.js', array('livequery'), $version_identifier);
 	if ( user_can_richedit() ) {
		wp_enqueue_script('editor');
	}
	wp_enqueue_script('media-upload');
  wp_enqueue_style('thickbox');
  
	wp_enqueue_script('swfupload');
	wp_enqueue_script('swfupload-swfobject');
	wp_enqueue_script('swfupload-queue');
	wp_enqueue_script('wpsc-swfupload-handlers', WPSC_URL.'/wpsc-admin/js/wpsc-swfupload-handlers.js', false, $version_identifier);
	
	add_action( 'admin_head', 'wp_tiny_mce' );

  // remove cforms timymce code  from running on the products page, because it breaks tinymce for us
	remove_filter( 'mce_external_plugins', 'cforms_plugin');
	remove_filter( 'mce_buttons', 'cforms_button');
	
	//add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );
	wp_enqueue_script('quicktags');
}
/**
	* wpsc_admin_include_optionspage_css_and_js function, includes the wpsc_admin CSS and JS for the specific options page
	* No parameters, returns nothing
*/

function wpsc_admin_include_optionspage_css_and_js(){
	wp_enqueue_script('wp-e-commerce-js-ajax', WPSC_URL.'/js/ajax.js', false, $version_identifier);

	wp_enqueue_script('wp-e-commerce-js-ui-tabs', WPSC_URL.'/wpsc-admin/js/jquery-ui.js', false, $version_identifier);
	wp_enqueue_script('wp-e-commerce-js-dimensions', WPSC_URL.'/wpsc-admin/js/dimensions.js', false, $version_identifier);
	wp_enqueue_style( 'wp-e-commerce-admin_2.7', WPSC_URL.'/wpsc-admin/css/settingspage.css', false, false, 'all' );
	wp_enqueue_style( 'wp-e-commerce-ui-tabs', WPSC_URL.'/wpsc-admin/css/jquery.ui.tabs.css', false, $version_identifier, 'all' );
}

function wpsc_meta_boxes(){
  $pagename = 'products_page_wpsc-edit-products';
 
	add_meta_box('wpsc_product_category_and_tag_forms', 'Category and Tags', 'wpsc_product_category_and_tag_forms', $pagename, 'normal', 'high');
	add_meta_box('wpsc_product_price_and_stock_forms', 'Price and Stock', 'wpsc_product_price_and_stock_forms', $pagename, 'normal', 'high');
	add_meta_box('wpsc_product_variation_forms', 'Variations', 'wpsc_product_variation_forms', $pagename, 'normal', 'high');
	add_meta_box('wpsc_product_shipping_forms', 'Shipping', 'wpsc_product_shipping_forms', $pagename, 'normal', 'high');
	add_meta_box('wpsc_product_advanced_forms', 'Advanced Settings', 'wpsc_product_advanced_forms', $pagename, 'normal', 'high');
	add_meta_box('wpsc_product_download_forms', 'Product Download', 'wpsc_product_download_forms', $pagename, 'normal', 'high');
	add_meta_box('wpsc_product_image_forms', 'Product Images', 'wpsc_product_image_forms', $pagename, 'normal', 'high');
}

add_action('admin_menu', 'wpsc_meta_boxes');
  
function wpsc_admin_dynamic_js() { 
 	header('Content-Type: text/javascript');
 	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
 	header('Cache-Control: public, must-revalidate, max-age=86400');
 	header('Pragma: public');
    $siteurl = get_option('siteurl'); 
	$hidden_boxes = get_option('wpsc_hidden_box');
	$hidden_boxes = implode(',', (array)$hidden_boxes);
	
	echo "var base_url = '".$siteurl."';\n\r";
	echo "var WPSC_URL = '". WPSC_URL."';\n\r";
	echo "var WPSC_IMAGE_URL = '".WPSC_IMAGE_URL."';\n\r";
	echo "var WPSC_DIR_NAME = '".WPSC_DIR_NAME."';\n\r";
	echo "var WPSC_IMAGE_URL = '".WPSC_IMAGE_URL."';\n\r";
	
	// LightBox Configuration start
	echo "var fileLoadingImage = '".WPSC_URL."/images/loading.gif';\n\r";
	echo "var fileBottomNavCloseImage = '".WPSC_URL."/images/closelabel.gif';\n\r";
	echo "var fileThickboxLoadingImage = '".WPSC_URL."/images/loadingAnimation.gif';\n\r";
	
	echo "var resizeSpeed = 9;\n\r";
	
	echo "var borderSize = 10;\n\r";
	
	echo "var hidden_boxes = '".$hidden_boxes."';\n\r";
	echo "var IS_WP27 = '".IS_WP27."';\n\r";
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

	exit();
}
if($_GET['wpsc_admin_dynamic_js'] == 'true') {
  add_action("admin_init", 'wpsc_admin_dynamic_js');  
}

function wpsc_admin_dynamic_css() { 
 	header('Content-Type: text/css');
 	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
 	header('Cache-Control: public, must-revalidate, max-age=86400');
 	header('Pragma: public');

	$flash = apply_filters('flash_uploader', $flash);
 	
	if($flash = 1) {
		?>
		div.flash-image-uploader {
			display: block;
		}
		
		div.browser-image-uploader {
			display: none;
		}
		<?php
	} else {
		?>
		div.flash-image-uploader {
			display: none;
		}
		
		div.browser-image-uploader {
			display: block;
		}
		<?php
	}
	exit();
}

if($_GET['wpsc_admin_dynamic_css'] == 'true') {
  add_action("admin_init", 'wpsc_admin_dynamic_css');  
}


//add_action("admin_init", 'wpsc_admin_css_and_js');  
add_action('admin_menu', 'wpsc_admin_pages');


/*
 *	Inserts the summary box on the WordPress Dashboard
 */

//if(function_exists('wp_add_dashboard_widget')) {
if( IS_WP27 ) {
    add_action('wp_dashboard_setup','wpsc_dashboard_widget_setup');
} else {
	add_action('activity_box_end', 'wpsc_admin_dashboard_rightnow');
}

function wpsc_admin_latest_activity() {
	global $wpdb;
		$totalOrders = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."`");
	
		 
		/*
		 * This is the right hand side for the past 30 days revenue on the wp dashboard
		 */
		echo "<div id='leftDashboard'>";
		echo "<strong class='dashboardHeading'>".TXT_WPSC_TOTAL_THIS_MONTH."</strong><br />";
		echo "<p class='dashboardWidgetSpecial'>";
		// calculates total amount of orders for the month
		$year = date("Y");
		$month = date("m");
		$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
		$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
		$sql = "SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC";
		$currentMonthOrders = $wpdb->get_var($sql);
		
		//calculates amount of money made for the month
		$currentMonthsSales = nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
		echo $currentMonthsSales;
		echo "<span class='dashboardWidget'>".TXT_WPSC_SALES_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		echo "<span class='pricedisplay'>";
		echo $currentMonthOrders;
		echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_ORDERS_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		//echo "<span class='pricedisplay'>";
		//calculates average sales amount per order for the month
		if($currentMonthOrders > 0){
			$monthsAverage = ((int)admin_display_total_price($start_timestamp, $end_timestamp)/(int)$currentMonthOrders);
			echo nzshpcrt_currency_display($monthsAverage,1);
		}
		//echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_AVGORDER_TITLE."</span>";
		echo "</p>";
		
		
		echo "</div>";
		/*
		 *This is the left side for the total life time revenue on the wp dashboard
		 */
		
		echo "<div id='rightDashboard' >";
		echo "<strong class='dashboardHeading'>".TXT_WPSC_TOTAL_INCOME."</strong><br />";

		echo "<p class='dashboardWidgetSpecial'>";
		echo nzshpcrt_currency_display(admin_display_total_price(),1);
		echo "<span class='dashboardWidget'>".TXT_WPSC_SALES_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		echo "<span class='pricedisplay'>";
		echo $totalOrders;
		echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_ORDERS_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		//echo "<span class='pricedisplay'>";
		//calculates average sales amount per order for the month
		if((admin_display_total_price() > 0) && ($totalOrders > 0) ) {
		$totalAverage = ((int)admin_display_total_price()/(int)$totalOrders);
		} else {
		  $totalAverage = 0;
		}
		echo nzshpcrt_currency_display($totalAverage,1);
		//echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_AVGORDER_TITLE."</span>";
		echo "</p>";
		echo "</div>";
		echo "<div style='clear:both'></div>";


}
add_action('wpsc_admin_pre_activity','wpsc_admin_latest_activity');

/*
 *	Pre-2.7 Dashboard Information
 */

function wpsc_admin_dashboard_rightnow() {
  $user = wp_get_current_user();
	if($user->user_level>9){
		echo "<div>";
		echo "<h3>".TXT_WPSC_E_COMMERCE."</h3>";
		echo "<p>";
		do_action('wpsc_admin_pre_activity');
//		wpsc_admin_latest_activity();
		do_action('wpsc_admin_post_activity');
		echo "</div>";
    }
}
		
/*
 * Dashboard Widget for 2.7 (TRansom)
 */
function wpsc_dashboard_widget_setup() {
	global $current_user;
	get_currentuserinfo();
	if($current_user->wp_capabilities['administrator'] == 1) {
		wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL.'/wpsc-admin/css/admin.css', false, $version_identifier, 'all' );
    wp_add_dashboard_widget('wpsc_dashboard_widget', __('E-Commerce'),'wpsc_dashboard_widget');
	}
}
/*
if(file_exists(WPSC_FILE_PATH."/wpsc-admin/includes/flot_graphs.php")){
	function wpsc_dashboard_quarterly_widget_setup() {
		wp_enqueue_script('flot', WPSC_URL.'/wpsc-admin/js/jquery.flot.pack.js', array('jquery'), '0.9.8');
		wp_enqueue_script('canvas', WPSC_URL.'/wpsc-admin/js/excanvas.pack.js', array('jquery', 'flot'), '0.9.8');

	    wp_add_dashboard_widget('wpsc_quarterly_dashboard_widget', __('Sales by Quarter'),'wpsc_quarterly_dashboard_widget');
	}
	function wpsc_quarterly_dashboard_widget(){
		require_once(WPSC_FILE_PATH."/wpsc-admin/includes/flot_graphs.php");
		$flot = new flot();
	
	}
}
*/
function wpsc_get_quarterly_summary(){
	global $wpdb;
	(int)$firstquarter = get_option('wpsc_first_quart');
	(int)$secondquarter = get_option('wpsc_second_quart');
	(int)$thirdquarter = get_option('wpsc_third_quart');
	(int)$fourthquarter = get_option('wpsc_fourth_quart');
	(int)$finalquarter = get_option('wpsc_final_quart');

	$results[] = admin_display_total_price($thirdquarter+1, $fourthquarter);
	$results[] = admin_display_total_price($secondquarter+1, $thirdquarter);
	$results[] = admin_display_total_price($firstquarter+1, $secondquarter);
	$results[] = admin_display_total_price($finalquarter, $firstquarter);
	return $results;
}
function wpsc_quarterly_dashboard_widget(){
	if(get_option('wpsc_business_year_start') == false){
		?>
		<form action='' method='post'>
		<label for='date_start'>Financial Year End: </label>
  			<input id='date_start' type='text' class='pickdate' size='11' value='<?php echo get_option('wpsc_last_date'); ?>' name='add_start' />
  			   <!--<select name='add_start[day]'>
			   <?php
			   for($i = 1; $i <=31; ++$i) {
			     $selected = '';
			     if($i == date("d")) { $selected = "selected='selected'"; }
			     echo "<option $selected value='$i'>$i</option>";
			     }
			   ?>
			   </select>
			   <select name='add_start[month]'>
			   <?php
			   for($i = 1; $i <=12; ++$i) {
			     $selected = '';
			     if($i == (int)date("m")) { $selected = "selected='selected'"; }
			     echo "<option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
			     }
			   ?>
			   </select>
			   <select name='add_start[year]'>
			   <?php
			   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
			     $selected = '';
			     if($i == date("Y")) { $selected = "selected='true'"; }
			     echo "<option $selected value='$i'>".$i."</option>";
			     }
			   ?>
			   </select>-->
  			<input type='hidden' name='wpsc_admin_action' value='wpsc_quarterly' />
  			<input type='submit' class='button primary' value='Submit' name='wpsc_submit' />
		</form>
		<?php
	if(get_option('wpsc_first_quart') != ''){
		$firstquarter = get_option('wpsc_first_quart');
		$secondquarter = get_option('wpsc_second_quart');
		$thirdquarter = get_option('wpsc_third_quart');
		$fourthquarter = get_option('wpsc_fourth_quart');
		$finalquarter = get_option('wpsc_final_quart');
		$revenue = wpsc_get_quarterly_summary();
		$currsymbol = wpsc_get_currency_symbol();
		foreach($revenue as $rev){
			if($rev == ''){
				$totals[] = '0.00'; 
			}else{
				$totals[] = $rev;
			}
		}
	?>
		<div id='box'>
				<p class='atglance'>
					<span class='wpsc_quart_left'>At a Glance</span>
					<span class='wpsc_quart_right'>Revenue</span>
				</p>
				<div style='clear:both'></div>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>01</strong>&nbsp; (<?php echo date('M Y',$thirdquarter).' - '.date('M Y',$fourthquarter); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol.' '.$totals[0]; ?></span></p>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>02</strong>&nbsp; (<?php echo date('M Y',$secondquarter).' - '.date('M Y',$thirdquarter); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol.' '.$totals[1]; ?></span></p>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>03</strong>&nbsp; (<?php echo date('M Y',$firstquarter).' - '.date('M Y',$secondquarter); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol.' '.$totals[2]; ?></span></p>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>04</strong>&nbsp; (<?php echo date('M Y',$finalquarter).' - '.date('M Y',$firstquarter); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol.' '.$totals[3]; ?></span>
				</p>
			<div style='clear:both'></div>
		</div>
	<?php
	}
	}

}
function wpsc_quarterly_setup(){
	global $current_user;
	get_currentuserinfo();
	if($current_user->wp_capabilities['administrator'] == 1) {
		$version_identifier = WPSC_VERSION.".".WPSC_MINOR_VERSION;
		wp_enqueue_script('datepicker-ui', WPSC_URL."/js/ui.datepicker.js",array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), $version_identifier);
		wp_add_dashboard_widget('wpsc_quarterly_dashboard_widget', __('Sales by Quarter'),'wpsc_quarterly_dashboard_widget');
	}
}

add_action('wp_dashboard_setup', 'wpsc_quarterly_setup');
function wpsc_dashboard_widget() {
	global $current_user;
	get_currentuserinfo();
	if($current_user->wp_capabilities['administrator'] == 1) {
    do_action('wpsc_admin_pre_activity');
    do_action('wpsc_admin_post_activity');
	}
}

/*
 * END - Dashboard Widget for 2.7
 */



?>