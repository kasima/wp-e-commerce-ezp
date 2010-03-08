<?php
function wpsc_options_checkout(){
global $wpdb;
$form_types = Array("text","email","address","city","country","delivery_address","delivery_city","delivery_country","textarea","heading","coupon");
?>
<form name='cart_options' id='cart_options' method='post' action=''>
	<div class="wrap">
  		<h2><?php echo TXT_WPSC_FORM_FIELDS;?></h2>  
		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>
		<form method='post' action='' id='chekcout_options_tbl'>
			<input type='hidden' name='checkout_submits' value='true' />
			<table>
			<tr>
				<td><?php echo TXT_WPSC_REQUIRE_REGISTRATION;?>:</td>
				<td>
					<?php
						$require_register = get_option('require_register');
						$require_register1 = "";
						$require_register2 = "";
						switch($require_register) {
							case 0:
							$require_register2 = "checked ='checked'";
							break;
    			
							case 1:
							$require_register1 = "checked ='checked'";
							break;
						}
		        ?>
						<input type='radio' value='1' name='wpsc_options[require_register]' id='require_register1' <?php echo $require_register1; ?> /> 					<label for='require_register1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
						<input type='radio' value='0' name='wpsc_options[require_register]' id='require_register2' <?php echo $require_register2; ?> /> 					<label for='require_register2'><?php echo TXT_WPSC_NO;?></label>
					</td>
					<td>
						<a title='<?php echo TXT_WPSC_ANYONEREGISTER;?>' class='flag_email' href='#' ><img src='<?php echo WPSC_URL; ?>/images/help.png' alt='' /> </a>
					</td>
     		</tr>
     				<tr>
			<?php
				$lock_tax = get_option('lock_tax');

				switch($lock_tax) {
					case 1:
					$lock_tax1 = "checked ='checked'";
					break;
					
					case 0:
					$lock_tax2 = "checked ='checked'";
					break;
				}
			?>
			<td scope="row"><?php echo TXT_WPSC_LOCK_TAX; ?>:</td>
			<td>
				<input type='radio' value='1' name='wpsc_options[lock_tax]' id='lock_tax1' <?php echo $lock_tax1; ?> /> 
				<label for='lock_tax1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[lock_tax]' id='lock_tax2' <?php echo $lock_tax2; ?> /> 
				<label for='lock_tax2'><?php echo TXT_WPSC_NO;?></label>
			</td>
			

			
		</tr>
		<tr>
					<?php
				$shippingBilling = get_option('shippingsameasbilling');

				switch($shippingBilling) {
					case 1:
					$shippingBilling1 = "checked ='checked'";
					break;
					
					case 0:
					$shippingBilling2 = "checked ='checked'";
					break;
				}
			?>
			<td scope="row"><?php echo TXT_WPSC_SHIPPING_SAME_AS_BILLING; ?>:</td>
			<td>
			<input type='radio' value='1' name='wpsc_options[shippingsameasbilling]' id='shippingsameasbilling1' <?php echo $shippingBilling1; ?> /> 
			<label for='shippingsameasbilling1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
			<input type='radio' value='0' name='wpsc_options[shippingsameasbilling]' id='shippingsameasbilling2' <?php echo $shippingBilling2; ?> /> 
			<label for='shippingsameasbilling2'><?php echo TXT_WPSC_NO;?></label>
			</td>
		</tr>
			</table>
  			<p><?php echo TXT_WPSC_CHECKOUT_FORM_FIELDS_DESCRIPTION;?></p>
  			<table id='form_field_table' style='border-collapse: collapse;'>
		    <tr>
		      <th class='namecol'>
		      <?php echo TXT_WPSC_NAME; ?>
		      </th>
		      <th class='typecol'>
		      <?php echo TXT_WPSC_TYPE; ?>
		      </th>
		      <th class='mandatorycol'>
		      <?php echo TXT_WPSC_MANDATORY; ?>
		      </th>
		      <th class='logdisplaycol'>
		       <?php echo TXT_WPSC_DISPLAY_IN_LOG; ?>      
		      </th>
		      <th class='ordercol'>
		       <?php echo TXT_WPSC_ORDER; ?>      
		      </th>
		    </tr>
    		<tr>
      			<td colspan='6' style='padding: 0px;'>
     			 <div id='form_field_form_container'>
			  <?php
			  
			  $email_form_field = $wpdb->get_results("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1",ARRAY_A);
			  $email_form_field = $email_form_field[0];
			  $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' ORDER BY `order`;";
			  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
			  //exit("<pre>".print_r($form_data,true)."</pre>");
			  foreach((array)$form_data as $form_field) {
			    echo "
			    <div id='form_id_".$form_field['id']."'>
			    <table>
			    <tr>\n\r";
			    echo "<td class='namecol'><input type='text' name='form_name[".$form_field['id']."]' value='".$form_field['name']."' /></td>";
			    
			    echo "      <td class='typecol'><select name='form_type[".$form_field['id']."]'>";
			    foreach($form_types as $form_type) {
			      $selected = '';
			      if($form_type === $form_field['type']) {
			        $selected = "selected='selected'";
			      }
			       // define('TXT_WPSC_TEXTAREA', 'Textarea');
			      echo "<option value='".$form_type."' ".$selected.">".constant("TXT_WPSC_".strtoupper($form_type))."</option>";
			    }
			    echo "</select></td>";
			    
			    
			    $checked = "";
			    if($form_field['mandatory']) {
			      $checked = "checked='checked'";
			    }
			    echo "      <td class='mandatorycol' style='text-align: center;'><input $checked type='checkbox' name='form_mandatory[".$form_field['id']."]' value='1' /></td>";
			    $checked = "";
			    if($form_field['display_log']) {
			      $checked = "checked='checked'";
			    }
			    echo "      <td class='logdisplaycol' style='text-align: center;'><input $checked type='checkbox' name='form_display_log[".$form_field['id']."]' value='1' /></td>";
			    
			    echo "      <td class='ordercol'><input type='text' size='3' name='form_order[".$form_field['id']."]' value='".$form_field['order']."' /></td>";
			    
			    echo "      <td style='text-align: center; width: 12px;'><a class='image_link' href='#' onclick='return remove_form_field(\"form_id_".$form_field['id']."\",".$form_field['id'].");'><img src='".WPSC_URL."/images/trash.gif' alt='".TXT_WPSC_DELETE."' title='".TXT_WPSC_DELETE."' /></a>";
			    echo "</td>";
			    
			   
			    if($email_form_field['id'] == $form_field['id']) {
			     echo "<td>";
			      echo "<a title='".TXT_WPSC_RECIEPT_EMAIL_ADDRESS."' class='flag_email' href='#' ><img src='".WPSC_URL."/images/help.png' alt='' /> </a>";
			    }else{
				 echo "<td style='width:16px'>";    
			    	echo "&nbsp;";
			    }
			    echo "</td>";
			    
			    echo "
			    </tr>
			    </table>
			    </div>";
			    }
			  ?>
    </div>
    </td>
  </tr>
    <tr>
      <td colspan='6' style='padding: 2px;'>
        <input type='hidden' name='wpsc_admin_action' value='checkout_settings' />
        
				<?php wp_nonce_field('update-options', 'wpsc-update-options'); ?>
        <input class='button-secondary' type='submit' name='submit' value='<?php echo TXT_WPSC_SAVE_CHANGES;?>' />
        <a href='#' onclick='return add_form_field();'><?php echo TXT_WPSC_ADD_NEW_FORM;?></a>
      </td>
    </tr>
  </table>

  </form>
</div>
</form>
		   <?php
  }
  ?>