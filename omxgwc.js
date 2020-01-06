/** 
 * JS functions for OMX Graphics Woocommerce customizations plugin
 * Version 0.8
 * (version above is equal with main plugin file version when this file was updated)
 */

// Let's have it tested first (will remove this after a while)
jQuery(document).ready(function() { console.log('JS Loaded - 19'); });


// START adding some stuff to do when document.ready:
jQuery(document).ready(function() {
	// Add the span elements needed to add the styled name and number
	jQuery('#wccf_product_field_name_style_container li > label').after('<span class="styled ridername" style="margin-left:10px"></span>');
	jQuery('#wccf_product_field_number_style_container li > label').after('<span class="styled ridernumber" style="margin-left:10px"></span>');
	// Insert the initial colors in color pickers (DO WE STILL NEED THESE??)
	var iniColor = '#3a3a3a'; jQuery('#wccf_product_field_number_color').val(iniColor).css("border-left-color",iniColor);
	var plateColor = '#dfdfdf'; jQuery('#wccf_product_field_number_plate_color').val(plateColor).css("border-left-color",plateColor);
	// Add a class to body after scrolling a bit
	var scrollPosition = window.scrollY;
	var logoContainer = document.getElementsByTagName("body")[0];
	window.addEventListener('scroll', function() {
		scrollPosition = window.scrollY;
		if (scrollPosition >= 175) {
			logoContainer.classList.add('omx-scrolled');
		} else {
			logoContainer.classList.remove('omx-scrolled');
		}
	});
	// Just for product pages: Add the font CSS style for each styled name and number
	// Rider *name* styles
	jQuery('#wccf_product_field_name_style_container li > input').each(function() {
		eachStyle = jQuery(this).attr('value');
		jQuery('#wccf_product_field_name_style_container li > input[value="'+eachStyle+'"]').siblings('span.styled').css("font-family",eachStyle);
	});
	// Rider *number* styles
	jQuery('#wccf_product_field_number_style_container li > input').each(function() {
		eachStyle = jQuery(this).attr('value');
		jQuery('#wccf_product_field_number_style_container li > input[value="'+eachStyle+'"]').siblings('span.styled').css("font-family",eachStyle);
	});
	// "Small" containers open-close
	jQuery( ".wccf_field_container small" ).click(function() {
		jQuery( this ).toggleClass( "omx_expanded" );
	});
});
// END adding some stuff to do when document.ready:

// Dynamically change the font of Rider Name and Number in its input field
jQuery('#wccf_product_field_name_style_container input').change(function(e) {
	var chosenstyle = jQuery(this).val();
	jQuery('#wccf_product_field_rider_name').css('font-family', chosenstyle );
});
jQuery('#wccf_product_field_number_style_container input').change(function(e) {
	var chosenstyle = jQuery(this).val();
	jQuery('#wccf_product_field_rider_number').css('font-family', chosenstyle );	
});

// Dynamically change the Rider Name in styles list
jQuery("#wccf_product_field_rider_name").on('input', function(e) {
	setTimeout(function() {
		jQuery(".styled.ridername").empty().text( jQuery("#wccf_product_field_rider_name").val() );
	}, 1500);
});
// Dynamically change the Rider Number in styles list
jQuery("#wccf_product_field_rider_number").on('input', function(e) {
	setTimeout(function() {
		jQuery(".styled.ridernumber").empty().text( jQuery("#wccf_product_field_rider_number").val() );
	}, 1500);
});

// Dynamically change the background color of rider number
jQuery('#wccf_product_field_number_plate_color_container input').change(function(e) {
	var chosennumcolor = jQuery(this).val();
	jQuery('#wccf_product_field_rider_number').css('background-color', chosennumcolor );
});
// Dynamically change the color of number plate
jQuery('#wccf_product_field_number_color_container input').change(function(e) {
	var chosenbkgcolor = jQuery(this).val();
	jQuery('#wccf_product_field_rider_number').css('color', chosenbkgcolor );
});
