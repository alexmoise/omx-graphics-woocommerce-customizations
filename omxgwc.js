/** 
 * JS functions for OMX Graphics Woocommerce customizations plugin
 * Version 0.21
 * (version above is equal with main plugin file version when this file was updated)
 */

// Let's have it tested first (will remove this after a while)
jQuery(document).ready(function() { console.log('JS Loaded - v23'); });


// === START adding some stuff to do when document.ready:
jQuery(document).ready(function() {
	// Move the price and add to cart button to the bottom of the screen and make it sticky
	if(jQuery("body").hasClass("single-product")) {
		jQuery("dl.rightpress_product_price_live_update dt").remove();
		jQuery("<div/>", {id:"omx_add_to_cart"}).appendTo("form.cart");
		jQuery("dl.rightpress_product_price_live_update").appendTo("button[name='add-to-cart']");
		jQuery("button[type='submit']").prependTo("div#omx_add_to_cart");
		jQuery("div.quantity").prependTo("div#omx_add_to_cart");
	}
	// Add the span elements needed to add the styled name and number
	jQuery('#wccf_product_field_name_style_container li > label').after('<span class="styled ridername" style="margin-left:10px"></span>');
	jQuery('#wccf_product_field_number_style_container li > label').after('<span class="styled ridernumber" style="margin-left:10px"></span>');
	// Add a class to body after scrolling a bit. Used further in CSS file to throw the main logo over the screen's top to save space
	var scrollPosition = window.scrollY;
	var logoContainer = document.getElementsByTagName("body")[0];
	window.addEventListener('scroll', function() {
		scrollPosition = window.scrollY;
		if (scrollPosition >= 50) {
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
	// Add "omx_read_more" class to <small> containers that have more text inside. Used further in CSS file to add the "after" element with the down/up arrow
	function isEllipsisActive(e) { return (e.offsetWidth < e.scrollWidth); }
	var elementList = document.querySelectorAll('small');
	for(var idx=0; idx < elementList.length; idx++) {
		if ( isEllipsisActive(elementList.item(idx)) ) {
			elementList.item(idx).className = elementList.item(idx).className + "omx_read_more";
		}
	}
	// Opening & closing class of <small> containers that have "omx_read_more" class 
	jQuery( ".wccf_field_container small.omx_read_more" ).click(function() {
		jQuery( this ).toggleClass( "omx_expanded" );
	});
});
// === END adding some stuff to do when document.ready

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

