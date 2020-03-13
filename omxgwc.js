/** 
 * JS functions for OMX Graphics Woocommerce customizations plugin
 * Version 0.54
 * (version above is equal with main plugin file version when this file was updated)
 */

// Let's have it tested first (will remove this after a while)
jQuery(document).ready(function() { console.log('JS Loaded - v31'); });

// === START adding some stuff to do when document.ready:
jQuery(document).ready(function() {
	// Move the price and add to cart button to the bottom of the screen
	if(jQuery("body").hasClass("single-product")) {
		jQuery("dl.rightpress_product_price_live_update dt").remove();
		jQuery("<div/>", {id:"omx_add_to_cart"}).appendTo("form.cart");
		jQuery("dl.rightpress_product_price_live_update").appendTo("button[name='add-to-cart']");
		jQuery("button[type='submit']").prependTo("div#omx_add_to_cart");
		jQuery("div.quantity").prependTo("div#omx_add_to_cart");
		// Add the DOM elements needed to display the price at the beginning of the product form
		jQuery("<div/>", {id:"omx_dynamic_price_wrapper"}).prependTo("form.cart");
		jQuery("<div class='dynamic_price_label'>Price: </div>").prependTo("div#omx_dynamic_price_wrapper");
		// Try to Add the initial price at the beginning of the product form every 10th second for 10 seconds
		var startPriceDuplicateTimer = (new Date()).getTime();
		var timer_id = setInterval(function(){
			var currentPriceDuplicateTimer = (new Date()).getTime();
			if((currentPriceDuplicateTimer - startPriceDuplicateTimer)/1000 > 10) clearInterval(timer_id);
			// console.log('Duplicating ... ');
			jQuery(".dynamic_price_value").html(jQuery(".rightpress_product_price_live_update").html());
		}, 100);
		// Then at any form change do as follows:
		jQuery("form.cart").change(function() { 
			jQuery(".wccf_field_container").stop(false,true); // Stop fading fields in and out (and whatever else is doing, just do it quick and preserve the queue - thus "false,true")
			// Try to update the price every 10th second for 10 seconds
			var startPriceUpdateTimer = (new Date()).getTime();
			var timer_id = setInterval(function(){
				var currentPriceUpdateTimer = (new Date()).getTime();
				if((currentPriceUpdateTimer - startPriceUpdateTimer)/1000 > 10) clearInterval(timer_id);
				// console.log('Updating ... ');
				jQuery(".dynamic_price_value").html(jQuery(".rightpress_product_price_live_update").html());
			}, 100);
		});
	}
	// Call the plus_minus function here for the initial setup
	quantity_plus_minus();
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

// Add the plus/minus button to Quantity box
function quantity_plus_minus() {
	jQuery("<div class='plus'>+</div>").appendTo("div.quantity");
	jQuery("<div class='minus'>-</div>").prependTo("div.quantity");
	jQuery('div.quantity .minus').click(function () {
		var $input = jQuery(this).parent().find('input');
		var count = parseInt($input.val()) - 1;
		count = count < 1 ? 1 : count;
		$input.val(count);
		$input.change();
		return false;
	});
	jQuery('div.quantity .plus').click(function () {
		var $input = jQuery(this).parent().find('input');
		$input.val(parseInt($input.val()) + 1);
		$input.change();
		return false;
	});
};
// Call the plus_minus function at each cart update
jQuery(document.body).on('updated_cart_totals', function() { quantity_plus_minus(); });

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

// Dynamically scroll the checkout so the Place Order/Proceed to Paypal button gets in the view at gateway change
jQuery("ul.payment_methods input").change(function(e) {
	if (jQuery("body").hasClass("admin-bar")) { var small_scroll_unit = 160; big_scroll_unit = 400; } else { var small_scroll_unit = 120; big_scroll_unit = 360; }
	setTimeout(function() {
		if (jQuery(window).width() < 961) {
			jQuery("html").animate({ scrollTop: jQuery("#place_order").position().top-jQuery(window).height()+small_scroll_unit},250);
		} else {
			jQuery("html").animate({ scrollTop: jQuery("#place_order").position().top-jQuery(window).height()+big_scroll_unit},250);
		}
	}, 250);
});

