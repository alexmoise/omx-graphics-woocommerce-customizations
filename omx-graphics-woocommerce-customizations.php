<?php
/**
 * Plugin Name: OMX Graphics Woocommerce customizations

 * Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * Description: A custom plugin to add required customizations to OMX Graphics Woocommerce shop and to style the front end as required. Works based on WooCommerce Custom Fields plugin by RightPress and requires Woocommerce and Astra theme. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 0.50
 * Author: Alex Moise
 * Author URI: https://moise.pro
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
add_filter( 'woocommerce_get_price_html', function ($price) { return ''; } );
// Add the "Select options" link under the products. 
// !! "Add To Cart" button needs to be disabled in theme customizer
add_action( 'astra_woo_shop_title_after', 'moomx_product_button_archive' );
function moomx_product_button_archive() {
	echo '<a href="'; 
	echo the_permalink(); 
	echo '" class="archive_select_options">Select options</a>';
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
}
// Head to cart as soon as add to cart is hit
add_filter('woocommerce_add_to_cart_redirect', 'moomx_goto_checkout');
function moomx_goto_checkout() {
	global $woocommerce;
	$checkout_url = wc_get_checkout_url();
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

?>
