<?php
/**
 * Plugin Name: OMX Graphics Woocommerce customizations

 * Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * Description: A custom plugin to add required customizations to OMX Graphics Woocommerce shop and to style the front end as required. Works based on WooCommerce Custom Fields plugin by RightPress and requires Woocommerce and Astra theme. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 1.2.51
 * Author: Alex Moise
 * Author URI: https://moise.pro
 * WC requires at least: 3.0.0
 * WC tested up to: 4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0);}

// Increase image quality a bit, so all the straight lines appears smooth
add_filter('jpeg_quality', function($arg){return 92;});

// Load our own JS
add_action( 'wp_enqueue_scripts', 'moomx_adding_scripts', 9999999 );
function moomx_adding_scripts() {
	wp_register_script('omxgwc-script', plugins_url('omxgwc.js', __FILE__), array('jquery'), '', true);
	wp_enqueue_script('omxgwc-script');
}
// Load our own CSS
add_action( 'wp_enqueue_scripts', 'moomx_adding_styles', 9999999 );
function moomx_adding_styles() {
	wp_register_style('omxgwc-styles', plugins_url('omxgwc.css', __FILE__));
	wp_enqueue_style('omxgwc-styles');
}
// Add title to Blog archive page 
add_action( astra_primary_content_top, moomx_add_blog_title );
function moomx_add_blog_title() {
	if ( is_home() ) {
		echo '<H1 class="blog-title">'.single_post_title( '', false ).'</H1>';
	}
}
// Add a small JS function to automatically replace spaces and dashes with underscores in Options - so the fields edits faster
add_action('in_admin_footer', 'moomx_only_dashes_in_custom_fields_options');
function moomx_only_dashes_in_custom_fields_options() {	
	global $pagenow;
	if ( $pagenow == 'post.php' ) {
		echo '
		<script>
			jQuery(document).ready(function() {
				jQuery(".wccf_post_options").on("keyup", "input.wccf_post_config_options_key", function(event) {
					this.value = this.value.replace(/ /g, "_");
				});
				jQuery(".wccf_post_options").on("keyup", "input.wccf_post_config_options_key", function(event) {
					this.value = this.value.replace(/-/g, "_");
				});
				jQuery(".wccf_post_conditions").on("keyup", "input.wccf_condition_text", function(event) {
					this.value = this.value.replace(/ /g, "_");
				});
				jQuery(".wccf_post_conditions").on("keyup", "input.wccf_condition_text", function(event) {
					this.value = this.value.replace(/-/g, "_");
				});
				jQuery(".wccf_post_conditions").on("keyup", "input.wccf_condition_text", function(event) {
					jQuery(this).val(jQuery(this).val().toLowerCase());
				});
				jQuery(".wccf_post_settings").on("keyup", "input#wccf_post_config_key", function(event) {
					this.value = this.value.replace(/ /g, "_");
				});
				jQuery(".wccf_post_settings").on("keyup", "input#wccf_post_config_key", function(event) {
					this.value = this.value.replace(/-/g, "_");
				});
			});
		</script>
		';
	}
}
// Add few CSS rules to admin header to improve Products Bulk Edit tables
add_action('admin_head', 'moomx_admin_styles');
function moomx_admin_styles() {
	global $pagenow;
	if ( $pagenow == 'post.php' ) {
		echo '
		<style>
			div#myGrid input[type="text"] {
				min-height: unset;
			}
			div#myGrid input:focus {
				border: 0px solid transparent;
				box-shadow: none;
			}
		<style>
		';
	}
}
// Stop Safari from zooming in on fields. Also stop Androids zoomig at all
add_action('wp_head', 'moomx_output_viewport_meta_tag', 1); // Prio 1 is there so it comes after the other viewport header 
function moomx_output_viewport_meta_tag() {
	// echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">';
	echo '<meta name="viewport" content="width=device-width, user-scalable=no" />';
}
// Remove hover zoom
add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );
// Remove the product price
add_filter( 'woocommerce_get_price_html', 'moomx_return_false', 10 );
add_filter( 'woocommerce_variable_price_html', 'moomx_return_false', 10 );
add_filter( 'woocommerce_grouped_price_html', 'moomx_return_false', 10 );
add_filter( 'woocommerce_variable_sale_price_html', 'moomx_return_false', 10 );
function moomx_return_false($price) { return false; }

// === OMX custom price display functions ===
if ( !is_admin() ) { add_filter( 'woocommerce_get_price_html', 'moomx_omx_price'); }
function moomx_omx_price($price) {
	// the whole thing below will only work in front end as only there we can access global $product - but will break in Gutenberg for example, when adding Handpicked Products - so we IF it accordingly:
	if (is_product_category() || is_product() || is_shop() ) {
		// get product object first
		global $product; 
		// get default product attributes
		$default_attributes = $product->get_default_attributes();
		// then try to get product variations
		$variations=$product->get_children();
		// so, if we have more than 0 variations ...
		if (count($variations) > 0) {
			// ...then loop through them and:
			foreach ($variations as $variationID) {
				// get variation object
				$product_variation = new WC_Product_Variation($variationID);
				// ...then pick the attributes
				$var_attributes = $product_variation->get_variation_attributes();
				// to compare variation attributes with product default attributes just array_diff them and count the differences
				$ck_var_atts = array_diff($var_attributes, $default_attributes);
				$ck_var_atts_count = count($ck_var_atts );
				// if there's no difference that means we've hit the default configuration for the product ...
				if ($ck_var_atts_count == 0) {
					// ... so we can just pick the ID of that variation for later use
					$chosen_variation_id = $variationID;
					// also keep the prices of it in the possible prices arrays, so we don't mess with variation object later (which can be huge) 
					$possible_reg_prices[$variationID] = $product_variation->regular_price;
					$possible_sale_prices[$variationID] = $product_variation->sale_price;
					// then break out of the foreach as the rest are not needed
					break;
				} else { 
					// ... otherwise we just add the prices to prices arrays so we can pick them later based on the amounts
					$possible_reg_prices[$variationID] = $product_variation->regular_price;
					$possible_sale_prices[$variationID] = $product_variation->sale_price;
				}
			}
			// if we don't have a choosen variation already means that there's no default variation match, so let's pick the one with the
			// *** BIGGEST ***
			// regular price (by using MAX below):
			if (!$chosen_variation_id) { $chosen_variation_id = max(array_keys($possible_reg_prices)); }
			// at this moment we're sure to have a choosen variation ID and its price in possible regular prices array; so extract that regular price from that array ...
			$chosen_reg_price = $possible_reg_prices[$chosen_variation_id];
			// ... and now let's plck the sale price if it exists:		
			$chosen_sale_price = $possible_sale_prices[$chosen_variation_id];
		} else {
			// ... but if no variation is found, just extract the simple product prices:
			$chosen_reg_price = $product->get_regular_price();
			$chosen_sale_price = $product->get_sale_price();
		}
		// At this point we *have* the regular price. Sale price might be missing if not defined though, but we're sure we searched for it thoroughly, so let's proceed composing the price html based on that:
		if ($chosen_sale_price) {
			// so if there's a sale price let's calculate the percentage of savings and display all 3 nicely ...
			$saved_percent_raw = ($chosen_reg_price - $chosen_sale_price) / $chosen_reg_price * 100;
			$saved_percent = round($saved_percent_raw, 0);
			$omx_price = '<div class="omx_price"><del class="reg_price">'.wc_price($chosen_reg_price).'</del><span class="price_separator">|</span><span class="saved_percent">'.$saved_percent.'</span><span class="percent_off">% Off</span><ins class="sale_price">'.wc_price($chosen_sale_price).'</ins></div>';
		} else {
			// else let's just display the regular price
			$omx_price = '<div class="omx_price"><ins class="reg_price">'.wc_price($chosen_reg_price).'</ins></div>';
		}
		// return the newly composed $omx_price instead of the regular $price:
		return $omx_price; 
	} else {
		// ... otherwise we return the good ol' $price untouched:
		return $price;
	}
}
// Make sure we'll have 2 decimals all over the shop
add_filter( 'wc_get_price_decimals', 'moomx_change_prices_decimals', 20, 1 );
function moomx_change_prices_decimals( $decimals ){
    $decimals = 2;
    return $decimals;
}

// Change places of woocommerce elements as needed
add_action( 'template_redirect', 'moomx_rearrange_woocomemrce_features' );
function moomx_rearrange_woocomemrce_features() {
	// get rid of the result count and breadcrumbs first
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
	// then add the breadcrumbs in result count's place
	add_action( 'woocommerce_before_shop_loop', 'woocommerce_breadcrumb', 20, 0);
	// additionally remove coupons feature from checkout
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	// remove Sale flash banner from single products and from loops
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	// Change position of Proceed to Checkout button (lower, so Angelleye Paypal button - on prio 22 - gets on top of it)
	remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
	add_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 90 );
}

// Remove Checkout button in Mini Cart Widget (by emptying its HTML) 
if ( ! function_exists( 'woocommerce_widget_shopping_cart_proceed_to_checkout' ) ) {
	function woocommerce_widget_shopping_cart_proceed_to_checkout() { echo ''; }
}

// Empty the Clear reset variations link
add_filter('woocommerce_reset_variations_link', '__return_empty_string');

// Head to cart as soon as add to cart is hit
add_filter('woocommerce_add_to_cart_redirect', 'moomx_goto_cart');
function moomx_goto_cart() {
	global $woocommerce;
	$checkout_url = wc_get_cart_url();
	return $checkout_url;
}
// Head to home page instead of Shop as soon as the Return to Shop button is clicked 
add_filter( 'woocommerce_return_to_shop_redirect', 'moomx_change_empty_cart_button_url' );
function moomx_change_empty_cart_button_url() {
	return get_site_url();
}
// Change "Product has been added to your cart" message since we go directly to Cart anyway
add_filter( 'wc_add_to_cart_message_html', 'moomx_change_addtocart_notice' );
function moomx_change_addtocart_notice($products) {
	$addtocart_notice = 'Congrats! You have a great taste. <br>Just one more step to get your order on its way.';
	return $addtocart_notice;
}

// Add Save & Share Cart link in Cart form, right after Update Cart button
// add_action( 'woocommerce_cart_actions', 'moomx_save_share_cart_link');
function moomx_save_share_cart_link() {
	$save_share_cart_link_html = '<a class="save_share_cart_link" href="/cart/#email-cart">Save &amp; Share Cart</a>';
	echo $save_share_cart_link_html;
}
// Add Save and Share cart button right after Proceed to Checkout button in Cart
add_action( 'woocommerce_proceed_to_checkout', 'moomx_save_cart_button', 100);
function moomx_save_cart_button() {
	echo '<a href="/cart/#email-cart" class="save_share_cart-button button alt wc-forward">Save & Share Cart</a>';
}

// Stripe filters for Payment Request buttons
function moomx_return_true() { return true; }
add_filter('wc_stripe_hide_payment_request_on_product_page', 'moomx_return_true');
// add_filter('wc_stripe_show_payment_request_on_checkout', 'moomx_return_true');

// Change Proceed to Checkout button text
if ( ! function_exists( 'woocommerce_button_proceed_to_checkout' ) ) {
	function woocommerce_button_proceed_to_checkout() {
		$checkout_url = WC()->cart->get_checkout_url();
		?>
		<a href="<?php echo $checkout_url; ?>" class="checkout-button button alt wc-forward"><?php _e( 'Regular checkout' ); ?></a>
		<?php
	}
}

// Add Agree to Terms in Cart
add_action( 'woocommerce_proceed_to_checkout', 'moomx_cart_privacy_policy_checkbox', 1 );
function moomx_cart_privacy_policy_checkbox() {
	echo 'By clicking payment button I agree to the <a href="/terms-conditions/" target="_blank">Terms and Conditions</a>';
}

// Translate/change some strings as needed
add_filter( 'gettext', 'moomx_translate_woocommerce_strings', 999, 3 );
function moomx_translate_woocommerce_strings( $translated, $text, $domain ) {
$translated = str_ireplace( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'Lost your password? Or need to create a brand new one? Please enter your username or email address. You will receive a link to create a new password via email.', $translated );
$translated = str_ireplace( 'Reset password', 'Get password setting link', $translated );
$translated = str_ireplace( 'Cart totals', 'Shipping & Totals', $translated );
$translated = str_ireplace( 'Change address', 'Deliver to different address', $translated );
$translated = str_ireplace( 'Undo?', 'Tap here to undo!', $translated );
$translated = str_ireplace( 'An error occurred, please try again or try an alternate form of payment.', 'We are sorry, but your current payment method could not be processed. Please use <a class="error_paypal_link" href="https://test.omxgraphics.com/cart/#omx-custom-onepage-cart-shipping">PayPal</a> to finish your transaction. No PayPal account is needed and all credit cards are accepted.', $translated );
$translated = str_ireplace( 'Proceed to PayPal', 'Place order', $translated );
$translated = str_ireplace( 'An error occurred while processing the card.', 'We are sorry, but your current payment method could not be processed. Please use <a class="error_paypal_link" href="https://test.omxgraphics.com/cart/#omx-custom-onepage-cart-shipping">PayPal</a> to finish your transaction. No PayPal account is needed and all credit cards are accepted.', $translated );
$translated = str_ireplace( 'The card was declined.', 'We are sorry, but your current payment method could not be processed. Please use <a class="error_paypal_link" href="https://test.omxgraphics.com/cart/#omx-custom-onepage-cart-shipping">PayPal</a> to finish your transaction. No PayPal account is needed and all credit cards are accepted.', $translated );
return $translated;
}

// Woocommerce templates overrides
add_filter( 'woocommerce_locate_template', 'moomx_replace_woocommerce_templates', 20, 3 );
function moomx_replace_woocommerce_templates( $template, $template_name, $template_path ) {
	global $woocommerce;
	$_template = $template;
	if ( ! $template_path ) { $template_path = $woocommerce->template_url; }
	$plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/woocommerce/';
	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			$template_path . $template_name,
			$template_name
		)
	);
	if ( ! $template && file_exists( $plugin_path . $template_name ) ) { $template = $plugin_path . $template_name; }
	if ( ! $template ) { $template = $_template; }
	return $template;
}

// === Sale/New/Featured flash banners adjustments below
// Empty the Sale Flash HTML, 2 ways, normally unused
// add_filter('woocommerce_sale_flash', 'moomx_empty_sale_html', 10, 3);
// function moomx_empty_sale_html() { $sale_html = ''; return $sale_html; }
// add_filter('woocommerce_sale_flash', 'moomx_custom_hide_sales_flash');
// function moomx_custom_hide_sales_flash() { return false; }

// Add the New flash banner to products in Archive pages
add_action( 'woocommerce_before_shop_loop_item_title','moomx_new_product_flash', 1 );
function moomx_new_product_flash() {
	// get product object first
	global $product; 
	// get default product attributes
	$default_attributes = $product->get_default_attributes();
	// then try to get product variations
	$variations=$product->get_children();
	// so, if we have more than 0 variations ...
	if (count($variations) > 0) {
		// ...then loop through them and:
		foreach ($variations as $variationID) {
			// get variation object
			$product_variation = new WC_Product_Variation($variationID);
			// ...then pick the attributes
			$var_attributes = $product_variation->get_variation_attributes();
			// to compare variation attributes with product default attributes just array_diff them and count the differences
			$ck_var_atts = array_diff($var_attributes, $default_attributes);
			$ck_var_atts_count = count($ck_var_atts );
			// if there's no difference that means we've hit the default configuration for the product ...
			if ($ck_var_atts_count == 0) {
				// ... so get its sale price ...
				$the_sale_price = $product_variation->sale_price;
				// ... and break out of the foreach as the rest are not needed
				break;
			} 
		}
	} else {
		// if it's a simple product then get the sale price
		$the_sale_price = $product->get_sale_price();
	}
	// now based on sale price variable, display either Sale or New flag
	if (!$the_sale_price) {
		$display_new_badge = get_field( 'display_new_badge' );
		if ($display_new_badge == '1') {
			// we only want to display the New badge in the first 14 days since the last Update, so let's calculate that first
			$date_modified = new DateTime($product->get_date_created()->date('Y-m-d'));
			$date_current = new DateTime(date('Y-m-d'));
			$days_since_modified  = $date_current->diff($date_modified)->format('%a');
			// now, only if no more than 14 days has passed since last update ...
			if ($days_since_modified < 15) {
				// ... echo the "New" badge over the product
				echo '<span class="new_product">' . esc_html__( 'New', 'woocommerce' ) . '</span>';
			}
		}
	} else {
		echo '<span class="onsale">' . esc_html__( 'Sale!', 'woocommerce' ) . '</span>';
	}
}

// Output the Category Pre-Footer in 'product_cat' pages and 'pwb-brand' pages. 
// !! Field is added with ACF plugin
add_action( 'astra_content_after', 'moomx_category_pre_footer_output' );
function moomx_category_pre_footer_output() {
	if ( is_tax('product_cat') || is_tax('pwb-brand') ) {
		$product_cat_object = get_queried_object();
		if(get_field( 'category_pre_footer', 'product_cat_'.$product_cat_object->term_id)) {
			echo '<div class="category_pre_footer">';
			the_field( 'category_pre_footer', 'product_cat_'.$product_cat_object->term_id);
			echo '</div>';
		}
		
	}
}

// Output the SHOP Page Pre-Footer 
// !! Field is added with ACF plugin
add_action( 'astra_content_after', 'moomx_shop_pre_footer_output' );
function moomx_shop_pre_footer_output() {
	if ( is_shop() ) {
		$postid = get_option( 'woocommerce_shop_page_id' ); 
		if( get_field('shop_pre_footer', $postid) ) {
			echo '<div class="category_pre_footer">';
			the_field( 'shop_pre_footer', $postid );
			echo '</div>';
		}
	}
}

// Adjust shop and gallery thumbnails to match the new shop design
add_filter('woocommerce_get_image_size_gallery_thumbnail', function($size) {
	return array (
		'width' => 200,
		'height' => 200,
		'crop' => 1,
	);
});
add_filter('woocommerce_gallery_thumbnail_size', function($size) {
	return array (
		'width' => 200,
		'height' => 200,
		'crop' => 1,
	);
});

// Remove Additional Information tab from Single Product pages
add_filter( 'woocommerce_product_tabs', 'moomx_remove_product_tabs', 9999 );
function moomx_remove_product_tabs( $tabs ) {
    unset( $tabs['additional_information'] ); 
    return $tabs;
}

// Send failed Order email also to Customer
// add_filter( 'woocommerce_email_recipient_cancelled_order', 'moomx_cancelled_order_add_customer_email', 10, 2 );
add_filter( 'woocommerce_email_recipient_failed_order', 'moomx_cancelled_order_add_customer_email', 10, 2 );
function moomx_cancelled_order_add_customer_email( $recipient, $order ){
     return $recipient . ',' . $order->billing_email;
}

// Change tag of site title - Add conditions later if needed
add_filter( 'astra_site_title_tag', 'moomx_astra_change_site_title_tag' );
function moomx_astra_change_site_title_tag( $tag ) {
    $tag = 'span';
    return $tag;
}

// Change PayPal icon in checkout
add_filter( 'woocommerce_paypal_icon', 'moomx_replace_paypal_icon' );
function moomx_replace_paypal_icon() {
   return 'https://omxgraphics.com/files/static/payments-methods-01.png';
}

// Display "Free" as cost when shipping cost is zero
add_filter( 'woocommerce_cart_shipping_method_full_label', 'moomx_add_0_to_shipping_label', 10, 2 );
function moomx_add_0_to_shipping_label( $label, $method ) {
	if ( ! ( $method->cost > 0 ) ) {
		// $label .= ': ' . wc_price(0);
		$label .= ': <strong>Free</strong>';
	}
	return $label;
}

// === Create User Account for Guest ordering customers
// Trigger this function to the thank you page
add_action( 'woocommerce_thankyou', 'moomx_register_guests', 10, 1 );
function moomx_register_guests( $order_id ) {
  // get all the order data and extract billing email
  $order = new WC_Order($order_id);
  $order_email = $order->billing_email;
  // check if the user already exixts
  $email = email_exists( $order_email );  
  // $user = username_exists( $order_email ); // not necessary as email is unique anyway
  // if the Order Email doesn't exist in User table then it's a guest checkout
  if( $email == false ){
    // get a random password 
    $random_password = wp_generate_password();
    // create new user with email as username & newly created pw
    $user_id = wp_create_user( $order_email, $random_password, $order_email );
    // WC guest customer identification
    update_user_meta( $user_id, 'guest', 'yes' );
    // Set user billing data
    update_user_meta( $user_id, 'billing_address_1', $order->billing_address_1 );
    update_user_meta( $user_id, 'billing_address_2', $order->billing_address_2 );
    update_user_meta( $user_id, 'billing_city', $order->billing_city );
    update_user_meta( $user_id, 'billing_company', $order->billing_company );
    update_user_meta( $user_id, 'billing_country', $order->billing_country );
    update_user_meta( $user_id, 'billing_email', $order->billing_email );
    update_user_meta( $user_id, 'billing_first_name', $order->billing_first_name );
    update_user_meta( $user_id, 'billing_last_name', $order->billing_last_name );
    update_user_meta( $user_id, 'billing_phone', $order->billing_phone );
    update_user_meta( $user_id, 'billing_postcode', $order->billing_postcode );
    update_user_meta( $user_id, 'billing_state', $order->billing_state );
    // Set user shipping data
    update_user_meta( $user_id, 'shipping_address_1', $order->shipping_address_1 );
    update_user_meta( $user_id, 'shipping_address_2', $order->shipping_address_2 );
    update_user_meta( $user_id, 'shipping_city', $order->shipping_city );
    update_user_meta( $user_id, 'shipping_company', $order->shipping_company );
    update_user_meta( $user_id, 'shipping_country', $order->shipping_country );
    update_user_meta( $user_id, 'shipping_first_name', $order->shipping_first_name );
    update_user_meta( $user_id, 'shipping_last_name', $order->shipping_last_name );
    update_user_meta( $user_id, 'shipping_method', $order->shipping_method );
    update_user_meta( $user_id, 'shipping_postcode', $order->shipping_postcode );
    update_user_meta( $user_id, 'shipping_state', $order->shipping_state );
    // link past orders to this newly created customer
    wc_update_new_customer_past_orders( $user_id );
	// Also update newly created user with First Name and Last Name
	wp_update_user([
		'ID' => $user_id, 
		'first_name' => $order->billing_first_name,
		'last_name' => $order->billing_last_name,
	]);
	// And sent the New User notification email as well
	// wp_new_user_notification( $user_id, null, 'user' );
  }
}
// If order is made as Guest but Billing email matches an existing user then assign current order to that existing user
add_action( 'woocommerce_thankyou', 'moomx_assign_new_order', 11, 1 );
function moomx_assign_new_order( $order_id ) {
	$order = new WC_Order($order_id);
	$user = $order->get_user();
	if( !$user ){
		$userdata = get_user_by( 'email', $order->get_billing_email() );
		if(isset( $userdata->ID )){
			update_post_meta($order_id, '_customer_user', $userdata->ID );
		}
	}
}

?>
