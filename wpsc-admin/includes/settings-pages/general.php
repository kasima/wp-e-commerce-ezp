<?php
function wpsc_options_general(){
	global $wpdb;
?>
	<form name='cart_options' id='cart_options' method='post' action=''>
	<div id="options_general">
		<h2><?php echo TXT_WPSC_OPTIONS_GENERAL_HEADER; ?></h2>
		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>
		<table class='wpsc_options form-table'>
		<tr>
			<th scope="row"><?php echo TXT_WPSC_BASE_COUNTRY; ?>: </th>
			<td>
				<select name='wpsc_options[base_country]' onchange='submit_change_country();'>
				<?php echo country_list(get_option('base_country')); ?>
				</select>
				<span id='options_country'>
				<?php
				$region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".get_option('base_country')."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
				if($region_list != null) {
				?>
					<select name='wpsc_options[base_region]'>
						<?php
						foreach($region_list as $region) {
							if(get_option('base_region')  == $region['id']) {
								$selected = "selected='selected'";
							} else {
								$selected = "";
							}
						?>
						<option value='<?php echo $region['id']; ?>' <?php echo $selected; ?> ><?php echo $region['name']; ?></option>	<?php	
						} 
						?>
					</select>
				   
		<?php  	 }	?>
				</span>
				<br /><?php echo TXT_WPSC_SELECTYOURBUSINESSLOCATION;?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo TXT_WPSC_TAX_SETTINGS;?>:</th>
			<td>
				<span id='options_region'>
				<?php
				$country_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".get_option('base_country')."' LIMIT 1",ARRAY_A);
				echo $country_data['country'];
				$region_count = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".get_option('base_country')."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`") ;
				if($country_data['has_regions'] == 1) {
					?>&nbsp;&nbsp;&nbsp;&nbsp;<a href='<?php echo add_query_arg(array( 'page' => 'wpsc-settings', 'isocode' => get_option('base_country') )); ?>'><?php echo $region_count ?> Regions</a>
		<?php	} else { ?>
					<input type='hidden' name='country_id' value='<?php echo $country_data['id']; ?>' />
					&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' name='country_tax' class='tax_forms' maxlength='5' size='5' value='<?php echo $country_data['tax']; ?>' />%
		<?php	}	?>
				</span>
			</td>
		</tr>
		<?php	/* START OF TARGET MARKET SELECTION */					
		$countrylist = $wpdb->get_results("SELECT id,country,visible FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY country ASC ",ARRAY_A);
		?>
		<tr>
			<th scope="row">
			<?php echo TXT_WPSC_TM; ?>:
			</th>
			<td>
				<?php
				// check for the suhosin module
				if(@extension_loaded('suhosin') && (@ini_get('suhosin.post.max_vars') > 0) && (@ini_get('suhosin.post.max_vars') < 500)) {
					echo "<em>".__("The Target Markets feature has been disabled because you have the Suhosin PHP extension installed on this server. If you need to use the Target Markets feature then disable the suhosin extension, if you can not do this, you will need to contact your hosting provider.
			",'wpsc')."</em>";
   		
				} else {
					?>
					<span>Select: <a href='<?php echo add_query_arg(array('selected_all' => 'all'))?>' class='wpsc_select_all'>All</a>&nbsp; <a href='<?php echo add_query_arg(array( 'selected_all'=>'none'))?>' class='wpsc_select_none'>None</a></span><br />

					<div id='resizeable' class='ui-widget-content multiple-select'>
						<?php
							foreach((array)$countrylist as $country){
								$country['country'] = htmlspecialchars($country['country']);
								if($country['visible'] == 1){ ?>
									<input type='checkbox' name='countrylist2[]' value='<?php echo $country['id']; ?>'  checked='checked' /><?php echo $country['country']; ?><br />
						<?php	}else{ ?>
									<input type='checkbox' name='countrylist2[]' value='<?php echo $country['id']; ?>'  /><?php echo $country['country']; ?><br />
						<?php	}
									
							}	?>		
					</div><br />
					Select the markets you are selling products to.
				<?php
				}
			?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo TXT_WPSC_LANGUAGE;?>:</th>
			<td>
				<select name='wpsc_options[language_setting]'>
			<?php
			if(get_option('language_setting') != '') {
				$language_setting = get_option('language_setting');
			} else {
				$language_setting = "EN_en.php";
			}
			$languages_directory = WPSC_FILE_PATH.'/languages';
			$language_files = wpsc_list_dir($languages_directory);
			foreach($language_files as $language_file) {
				switch($language_file) {
					case "EN_en.php";
					$language = "English";
					break;
					
					case "DE_de.php";
					$language = "Deutsch";
					break;
					
					case "NL_nl.php";
					$language = "Dutch";
					break;
				
					case "FR_fr.php";
					$language = "Français";
					break;
					
					case "IT_it.php";
					$language = "Italian";
					break;
					
					case "BG_bg.php";
					$language = 'български';
					break;
					
					case "JP_jp.php";
					$language = "日本語";
					break;
					
					case "pt_BR.php";
					$language = "Brazilian Portuguese";
					break;
										
					case "RU_ru.php";
					$language = "Russian";
					break;
					
					case "SP_sp.php";
					$language = "Spanish";
					break;
					
					case "HU_hu.php";
					$language = "Hungarian";
					break;
					
					case "SV_sv.php";
					$language = "Swedish";
					break;
									
					case "TR_tr.php";
					$language = "Türkçe";
					break; 
	
					case "EL_el.php";
					$language = "Ελληνικά";
					break;
	
					case "KO_ko.php";
					$language = "Korean";
					break;
					
					case "zh_CN.php";
					$language = "简体中文";
					break;
					
					case "DK_da.php";
					$language = "Danish";
					break;
					
					case "RO_ro.php";
					$language = "Romanian";
					break;
					
					case "nn_NO.php";
					$language = "Norwegian";
					break;

					
					case "CZ_cz.php";
					$language = "Czech";
					break;
											
					default:
					continue 2;
					break;
				}
				if($language_setting == $language_file) { ?>
					<option selected='selected' value='<?php echo $language_file; ?>'><?php echo $language; ?></option>
		<?php	} else { ?>
					<option value='<?php echo $language_file; ?>'><?php echo $language; ?></option>            
		<?php	}
			}	?>
				</select>
			</td>
		</tr>
		</table> 
							
		<h3 class="form_group"><?php echo TXT_WPSC_CURRENCYSETTINGS;?>:</h3>
		<table class='wpsc_options form-table'>
		<tr>
			<th scope="row"><?php echo TXT_WPSC_CURRENCYTYPE;?>:</th>
			<td>
				<select name='wpsc_options[currency_type]' onchange='getcurrency(this.options[this.selectedIndex].value);'>
				<?php
				$currency_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
				foreach($currency_data as $currency) {
					if(get_option('currency_type') == $currency['id']) {
						$selected = "selected='selected'";
					} else {
						$selected = "";
					} ?>
					<option value='<?php echo $currency['id']; ?>' <?php echo $selected; ?> ><?php echo htmlspecialchars($currency['country']); ?> (<?php echo $currency['currency']; ?>)</option>
		<?php	}  
				$currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A) ;
				if($currency_data['symbol'] != '') {
					$currency_sign = $currency_data['symbol_html'];
				} else {
					$currency_sign = $currency_data['code'];
				}
		?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo TXT_WPSC_CURRENCYSIGNLOCATION;?>:</th>
			<td>
				<?php
				$currency_sign_location = get_option('currency_sign_location');
				$csl1 = "";
				$csl2 = "";
				$csl3 = "";
				$csl4 = "";
				switch($currency_sign_location) {
					case 1:
					$csl1 = "checked ='checked'";
					break;
				
					case 2:
					$csl2 = "checked ='checked'";
					break;
				
					case 3:
					$csl3 = "checked ='checked'";
					break;
				
					case 4:
					$csl4 = "checked ='checked'";
					break;
				}
				?>
				<input type='radio' value='1' name='wpsc_options[currency_sign_location]' id='csl1' <?php echo $csl1; ?> /> 
				<label for='csl1'>100<span id='cslchar1'><?php echo $currency_sign; ?></span></label> &nbsp;
				<input type='radio' value='2' name='wpsc_options[currency_sign_location]' id='csl2' <?php echo $csl2; ?> /> 
				<label for='csl2'>100 <span id='cslchar2'><?php echo $currency_sign; ?></span></label> &nbsp;
				<input type='radio' value='3' name='wpsc_options[currency_sign_location]' id='csl3' <?php echo $csl3; ?> /> 
				<label for='csl3'><span id='cslchar3'><?php echo $currency_sign; ?></span>100</label> &nbsp;
				<input type='radio' value='4' name='wpsc_options[currency_sign_location]' id='csl4' <?php echo $csl4; ?> /> 
				<label for='csl4'><span id='cslchar4'><?php echo $currency_sign; ?></span> 100</label>
			</td>
		</tr>
		</table> 
		<div class="submit">
			<input type='hidden' name='wpsc_admin_action' value='submit_options' />
			<?php wp_nonce_field('update-options', 'wpsc-update-options'); ?>
			<input type="submit" value="<?php echo TXT_WPSC_UPDATE_BUTTON;?>" name="updateoption"/>
		</div>
	</div>
	</form>
<?php						
}					

?>