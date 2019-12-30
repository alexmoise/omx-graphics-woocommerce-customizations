<?php
/**
 * Plugin Name: OMX Graphics Woocommerce customizations

 * Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/omx-graphics-woocommerce-customizations
 * Description: A custom plugin to add required customizations to OMX Graphics Woocommerce shop and to style the front end as required. Works based on WooCommerce Custom Fields plugin by RightPress and requires Woocommerce and Astra theme. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 0.6
 * Author: Alex Moise
 * Author URI: https://moise.pro
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0);}

// === Increase image quality a bit, so all the straight lines appears smooth
add_filter('jpeg_quality', function($arg){return 92;});

// === Various WC Customizations below:
// Display a debug text, for control
add_action( 'woocommerce_product_meta_end', 'moomx_display_dbg_for_products', 90 );
function moomx_display_dbg_for_products() { echo 'DBG 15'; }
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
// Add the breadcrumbs in the right place (by default displayed in Product Summary, so removed in Customizer in the first place)
// add_action('template_redirect', 'moomx_prod_breadcrumbs', 10 );
// function moomx_prod_breadcrumbs(){ add_action( 'woocommerce_before_single_product', 'woocommerce_breadcrumb', 1 ); }
// Remove the product price
add_filter( 'woocommerce_get_price_html', function ($price) { return ''; } );
// Remove hover zoom
add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );



?>
