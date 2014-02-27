<?php
/*
Plugin Name: PMPro Add PayPal Express
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-add-paypal-express/
Description: Add PayPal Express as a Second Option at Checkout
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

/*
	You must have your PayPal Express API key, username, and password set in the PMPro Payment Settings for this plugin to work.
	After setting those settings and clicking save, you can switch to your primary gateway and set those settings. 
	The PayPal Express settings will be remembered "in the background".

	You do not need to activate this plugin with PayPal Website Payments Pro. PayPal Express is automatically an option at checkout with that gateway.
	
	This plugin will only work when the primary gateway is an onsite gateway. At this time, this includes:
	* Stripe
	* Braintree
	* Authorize.net
	* PayPal Payflow Pro
	* Cybersource
*/

/*
	Make PayPal Express a valid gateway.
*/
function pmproappe_pmpro_valid_gateways($gateways)
{
    //if already using paypal, ignore this
	$setting_gateway = get_option("pmpro_gateway");
	if($setting_gateway == "paypal")
		return $gateways;
	
	$gateways[] = "paypalexpress";
    return $gateways;
}
add_filter("pmpro_valid_gateways", "pmproappe_pmpro_valid_gateways");

/*
	Add toggle to checkout page.
*/
function pmproappe_pmpro_checkout_after_tos_fields()
{
	//if already using paypal, ignore this
	$setting_gateway = get_option("pmpro_gateway");
	if($setting_gateway == "paypal")
		return;
		
	global $pmpro_requirebilling, $gateway, $pmpro_review;
	
	//only show this if we're not reviewing
	if(empty($pmpro_review))
	{
	?>
	<table id="pmpro_payment_method" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0" border="0" <?php if(!$pmpro_requirebilling) { ?>style="display: none;"<?php } ?>>
	<thead>
		<tr>
			<th><?php _e('Choose your Payment Method', 'pmpro');?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div>
					<input type="radio" name="gateway" value="<?php echo esc_attr($setting_gateway);?>" <?php if(!$gateway || $gateway == $setting_gateway) { ?>checked="checked"<?php } ?> />
						<a href="javascript:void(0);" class="pmpro_radio"><?php _e('Check Out with a Credit Card Here', 'pmpro');?></a> &nbsp;
					<input type="radio" name="gateway" value="paypalexpress" <?php if($gateway == "paypalexpress") { ?>checked="checked"<?php } ?> />
						<a href="javascript:void(0);" class="pmpro_radio"><?php _e('Check Out with PayPal', 'pmpro');?></a> &nbsp;					
				</div>
			</td>
		</tr>
	</tbody>
	</table>
	<?php //here we draw the PayPal Express button, which gets moved in place by JavaScript ?>
	<span id="pmpro_paypalexpress_checkout" style="display: none;">
		<input type="hidden" name="submit-checkout" value="1" />		
		<input type="image" value="<?php _e('Check Out with PayPal', 'pmpro');?> &raquo;" src="<?php echo apply_filters("pmpro_paypal_button_image", "https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif");?>" />
	</span>
	<script>	
		//choosing payment method
		jQuery(document).ready(function() {		
			//move paypal express button into submit box
			jQuery('#pmpro_paypalexpress_checkout').appendTo('div.pmpro_submit');
			
			function showPayPalExpressCheckout()
			{
				jQuery('#pmpro_billing_address_fields').hide();
				jQuery('#pmpro_payment_information_fields').hide();			
				jQuery('#pmpro_submit_span').hide();
				jQuery('#pmpro_paypalexpress_checkout').show();
				
				pmpro_require_billing = false;		
			}
			
			function showCreditCardCheckout()
			{
				jQuery('#pmpro_paypalexpress_checkout').hide();
				jQuery('#pmpro_billing_address_fields').show();
				jQuery('#pmpro_payment_information_fields').show();			
				jQuery('#pmpro_submit_span').show();
				
				pmpro_require_billing = true;
			}
			
			//detect gateway change
			jQuery('input[name=gateway]').click(function() {		
				if(jQuery(this).val() != 'paypalexpress')
				{
					showCreditCardCheckout();
				}
				else
				{			
					showPayPalExpressCheckout();
				}
			});
			
			//update radio on page load
			if(jQuery('input[name=gateway]:checked').val() != 'paypalexpress')
			{
				showCreditCardCheckout();
			}
			else
			{			
				showPayPalExpressCheckout();
			}
			
			//select the radio button if the label is clicked on
			jQuery('a.pmpro_radio').click(function() {
				jQuery(this).prev().click();
			});
		});
	</script>
	<?php
	}	
}
add_action("pmpro_checkout_after_tos_fields", "pmproappe_pmpro_checkout_after_tos_fields");