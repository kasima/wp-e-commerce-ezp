<?php
/**
 * WP eCommerce edit and add variation group page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_display_variations_page() {
	$columns = array(
		'title' => TXT_WPSC_NAME,
		'edit' => TXT_WPSC_EDIT,
	);
	register_column_headers('display-variations-list', $columns);	
	
	?>
	<script language='javascript' type='text/javascript'>
		function conf() {
			var check = confirm("<?php echo TXT_WPSC_SURETODELETEPRODUCT;?>");
			if(check) {
				return true;
			} else {
				return false;
			}
		}
		
		<?php

		?>
	</script>
	<noscript>
	</noscript>
	<div class="wrap">
		<?php// screen_icon(); ?>
		<h2><?php echo wp_specialchars( TXT_WPSC_DISPLAYVARIATIONS ); ?> </h2>
		<p>	
				<?php echo TXT_WPSC_DISPLAYVARIATIONSDESCRIPTION;?>
		</p>
  
		
		<?php if (isset($_GET['deleted']) || isset($_GET['message'])) { ?>
			<div id="message" class="updated fade">
				<p>
				<?php		
				if (isset($_GET['message']) ) {
					$message = absint( $_GET['message'] );
					$messages[1] =  __( 'Product updated.' );
					echo $messages[$message];
					unset($_GET['message']);
				}
				
				$_SERVER['REQUEST_URI'] = remove_query_arg( array('deleted', 'message'), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
		<?php } ?>
				
		<div id="col-container" class=''>
			<div id="col-right">			
				<div id='poststuff' class="col-wrap">
					<form id="modify-variation-groups" method="post" action="" enctype="multipart/form-data" >
					<?php
						//$product_id = absint($_GET['product_id']);
						//wpsc_display_product_form($product_id);
						wpsc_admin_variation_forms($_GET['variation_id']);
					?>
					</form>
				</div>
			</div>
			
			<div id="col-left">
				<div class="col-wrap">		
					<?php
						wpsc_admin_variation_group_list($category_id);
					?>
				</div>
			</div>
		</div>
				
				
	</div>
	<?php
}

function wpsc_admin_variation_group_list() {
  global $wpdb;
	$variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` ORDER BY `id`",ARRAY_A);
	?>
		<table class="widefat page fixed" id='wpsc_variation_list' cellspacing="0">
			<thead>
				<tr>
					<?php print_column_headers('display-variations-list'); ?>
				</tr>
			</thead>
		
			<tfoot>
				<tr>
					<?php print_column_headers('display-variations-list', false); ?>
				</tr>
			</tfoot>
		
			<tbody>
				<?php
				foreach((array)$variations as $variation) {
					?>
						<tr class="variation-edit" id="variation-<?php echo $product['id']?>">
								<td class="variation-name"><?php echo htmlentities(stripslashes($variation['name']), ENT_QUOTES, 'UTF-8'); ?></td>
								<td class="edit-variation">
								<a href='<?php echo add_query_arg('variation_id', $variation['id']); ?>'><?php echo TXT_WPSC_EDIT; ?></a>
								</td>
						</tr>
					<?php
				}
				?>			
			</tbody>
		</table>
		<?php
}



function wpsc_admin_variation_forms($variation_id =  null) {
  global $wpdb;
  $variation_value_count = 0;
  $variation_name = '';
  if($variation_id > 0 ) {
    $variation_id = absint($variation_id);
		$variation_name = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` WHERE `id`='$variation_id' LIMIT 1") ;
  
		$variation_values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id`='$variation_id' ORDER BY `id` ASC",ARRAY_A);
		$variation_value_count = count($variation_values);
  }
  
  // if people add more than 90 variations, bad things may happen, like servers dying if more than one such variation group is put on a product. 90*90 = 8100 combinations
  if(($_GET['valuecount'] > 0) && ($_GET['valuecount'] <= 90)) { 
    $value_form_count = absint($_GET['valuecount']);
  } else {
    $value_form_count = 2;
    remove_query_arg( array('valuecount'), $_SERVER['REQUEST_URI'] );
  }
  if($variation_name != '') {
    ?>
    <h3><?php echo TXT_WPSC_EDITVARIATION;?><span> (<a href="admin.php?page=wpsc-edit-variations">Add new Variation Set</a>)</span></h3>
    <?php
  } else {
    ?>
    <h3><?php echo TXT_WPSC_ADDVARIATION;?></h3>
    <?php 
  }
  ?>
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
  <?php
}


?>