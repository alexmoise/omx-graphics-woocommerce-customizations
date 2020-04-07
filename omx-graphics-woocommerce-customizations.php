<?php
/**
 * Plugin Name: OMX Graphics Woocommerce customizations

 * Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * Description: A custom plugin to add required customizations to OMX Graphics Woocommerce shop and to style the front end as required. Works based on WooCommerce Custom Fields plugin by RightPress and requires Woocommerce and Astra theme. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 0.76
 * Author: Alex Moise
 * Author URI: https://moise.pro
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0);}

// Display a debug text, for control
// add_action( 'woocommerce_product_meta_end', 'moomx_display_dbg_for_products', 90 );
function moomx_display_dbg_for_products() { echo 'DBG 18'; }

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
// Add a small JS function to automatically replace spaces and dashes with underscores in Options - so the fields edits faster
add_action('in_admin_footer', 'moomx_only_dashes_in_custom_fields_options');
function moomx_only_dashes_in_custom_fields_options() {
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
// Stop Safari from zooming in on fields. Also stop Androids zoomig at all
add_action('wp_head', 'moomx_output_viewport_meta_tag', 0);
function moomx_output_viewport_meta_tag() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">';
}
// Remove hover zoom
add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );
// Remove the product price
// add_filter( 'woocommerce_get_price_html', function ($price) { return ''; } );

// === OMX custom price display functions ===
add_filter( 'woocommerce_get_price_html', 'moomx_omx_price');
function moomx_omx_price($price) { 
	// get product data first
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
			// ...then pick the attributes and prices
			$var_attributes = $product_variation->get_variation_attributes();
			$regular_price = $product_variation->regular_price;
			$sale_price = $product_variation->sale_price;
			// to compare variation attributes with product default attributes just array_diff them and count the differences
			$ck_var_atts = array_diff($var_attributes, $default_attributes);
			$ck_var_atts_count = count($ck_var_atts );
			// if there's no difference that means we're hit the default configuration for the product ...
			if ($ck_var_atts_count == 0) {
				// ... so we can just pick the prices and get out of the foreach (might not be perfect in case there are more attributes and only some of them set as default - need to check later)
				$chosen_reg_price = $regular_price;
				$chosen_sale_price = $sale_price;
				break;
			} else { 
				// ... otherwise we just add the prices to prices arrays so we can pick one later based on the numbers
				$possible_reg_prices[$variationID] = $regular_price;
				$possible_sales_prices[$variationID] = $sale_price;
			 }
		}
		// if we still don't have chosen prices it means they are somewhere in the possible prices arrays, so pick the:
		// *** BIGGEST *** 
		// value from both usinf the "max" function (but can change later if requested)
		if (!$chosen_reg_price) { $chosen_reg_price = max($possible_reg_prices); }
		if (!$chosen_sale_price) { $chosen_sale_price = max($possible_sales_prices); }
	} else {
		// ... but if no variation is found, just extract the simple product prices:
		$chosen_reg_price = $product->get_regular_price();
		$chosen_sale_price = $product->get_sale_price();
	}
	// At this point we *have* the regular and sales prices. Sales might be missing if not defined though, but we're sure we searched for it thoroughly
	if ($chosen_sale_price) {
		// so if there's a sale price let's calculate the percentage of savings and display all 3 nicely ...
		$saved_percent_raw = ($chosen_reg_price - $chosen_sale_price) / $chosen_reg_price * 100;
		$saved_percent = round($saved_percent_raw, 0);
		$omx_price = '<div class="omx_price"><del class="reg_price">'.wc_price($chosen_reg_price).'</del><span class="price_separator">|</span><span class="saved_percent">'.$saved_percent.'</span><span class="percent_off">% Off</span><ins class="sale_price">'.wc_price($chosen_sale_price).'</ins></div>';
	} else {
		// else let's just display the regular price
		$omx_price = '<div class="omx_price"><ins class="reg_price">'.wc_price($chosen_reg_price).'</ins></div>';
	}
	// "$price" not used anymore
	// return the newly composed price, finnaly:
	return $omx_price; 
}

// Display price range in single product page as well
// add_action('woocommerce_single_product_summary', 'moomx_price_range_acf');
function moomx_price_range_acf() {
	if (is_product()) {
		echo '<div class="product_price_range">';
		echo get_field( 'price_range' );
		echo '</div>';
	}
}
// Change places of woocommerce elements as needed
add_action( 'init', 'moomx_rearrange_woocomemrce_features' );
function moomx_rearrange_woocomemrce_features() {
	// get rid of the result count and breadcrumbs first
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
	// then add the breadcrumbs in result count's place
	add_action( 'woocommerce_before_shop_loop', 'woocommerce_breadcrumb', 20, 0);
	// additionally remove coupons feature from checkout
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	// remove Sale flash banner from single products
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
}
// Head to cart as soon as add to cart is hit
add_filter('woocommerce_add_to_cart_redirect', 'moomx_goto_checkout');
function moomx_goto_checkout() {
	global $woocommerce;
	$checkout_url = wc_get_cart_url();
	return $checkout_url;
}
// Change "Product has been added to your cart" message since we go directly to checkout anyway
add_filter( 'wc_add_to_cart_message_html', 'moomx_change_addtocart_notice' );
function moomx_change_addtocart_notice($products) {
	$addtocart_notice = 'You made a great choice! Just few more steps to get your order on its way.';
	return $addtocart_notice;
}
// Translate/change some strings as needed
add_filter( 'gettext', 'moomx_translate_woocommerce_strings', 999, 3 );
function moomx_translate_woocommerce_strings( $translated, $text, $domain ) {
$translated = str_ireplace( 'Undo?', 'Tap here to undo!', $translated );
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
// Add Save and Share cart button right after Proceed to Checkout button in Cart
add_action( 'woocommerce_proceed_to_checkout', 'moomx_save_cart_button', 100);
function moomx_save_cart_button() {
	echo '<a href="https://test.omxgraphics.com/cart/#email-cart" class="save_share_cart-button button alt wc-forward">Save & Share Cart</a>';
}
// Add the New flash banner to products in Archive pages
add_action( 'woocommerce_before_shop_loop_item_title','moomx_new_product_flash', 1 );
function moomx_new_product_flash() {
	global $product;
	// $isonsale = $product->is_on_sale(); // that seems to work only if all variations are on sale; for *some* variations it does not, so we'll check each variation
	$variations = $product->get_available_variations();
	for ($vn = 0; $vn < count($variations); $vn++) {
		$ck_var = wc_get_product($variations[$vn]['variation_id']);
		if ($ck_var->is_on_sale()) {
			$isonsale = 1;
			break;
		}
	}
	$display_new_badge = get_field( 'display_new_badge' );
	if ($display_new_badge == '1' && $isonsale != 1) {
		echo '<span class="new_product">' . esc_html__( 'New', 'woocommerce' ) . '</span>';
	}
}
// Output the Category Pre-Footer in category pages. 
// !! Field is added with ACF plugin
add_action( 'astra_content_after', 'moomx_category_pre_footer_output' );
function moomx_category_pre_footer_output() {
	if (is_product_category()) {
		$product_cat_object = get_queried_object();
		if(get_field( 'category_pre_footer', 'product_cat_'.$product_cat_object->term_id)) {
			echo '<div class="category_pre_footer">';
			the_field( 'category_pre_footer', 'product_cat_'.$product_cat_object->term_id);
			echo '</div>';
		}
	}
}

?>
