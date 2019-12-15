/** 
 * JS functions for OMX Graphics Woocommerce customizations plugin
 * Version 0.2
 * (version above is equal with main plugin file version when this file was updated)
 */

// Let's have it tested first (will remove this after a while)
jQuery(document).ready(function() { console.log('JS Loaded - 02'); });

// Dynamically change the font of Rider Name in its input field
jQuery('#wccf_product_field_name_style').change(function(e) {
	jQuery('#wccf_product_field_rider_name').css('font-family', (this.value) );
});