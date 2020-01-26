<?php
/**
 * Plugin Name: OMX Graphics Woocommerce customizations

 * Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * Description: A custom plugin to add required customizations to OMX Graphics Woocommerce shop and to style the front end as required. Works based on WooCommerce Custom Fields plugin by RightPress and requires Woocommerce and Astra theme. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 0.30
 * Author: Alex Moise
 * Author URI: https://moise.pro
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0);}

// === Various WC Customizations below:
// Display a debug text, for control
add_action( 'woocommerce_product_meta_end', 'moomx_display_dbg_for_products', 90 );
function moomx_display_dbg_for_products() { echo 'DBG 18'; }

// === Increase image quality a bit, so all the straight lines appears smooth
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

// Change places of breadcrumbs and result count
add_action( 'init', 'moomx_remove_result_count' );
function moomx_remove_result_count() {
	// get rid of them first
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
	// then add the breadcrumbs in result count's place
	add_action( 'woocommerce_before_shop_loop', 'woocommerce_breadcrumb', 20, 0);
}

?>
