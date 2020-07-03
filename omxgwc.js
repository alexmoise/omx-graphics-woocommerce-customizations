/** 
 * JS functions for OMX Graphics Woocommerce customizations plugin
 * Version 1.2.3
 * (version above is equal with main plugin file version when this file was updated)
 */

// === START adding some stuff to do when document.ready:
jQuery(document).ready(function() {
	// Move the price and add to cart button to the bottom of the screen
	if(jQuery("body").hasClass("single-product")) {
		jQuery("dl.rightpress_product_price_live_update dt").remove();
		jQuery("<div/>", {id:"omx_add_to_cart"}).appendTo("form.cart");
		jQuery("dl.rightpress_product_price_live_update").appendTo("button.single_add_to_cart_button.button.alt");
		jQuery("button[type='submit']").prependTo("div#omx_add_to_cart");
		jQuery("div.quantity").prependTo("div#omx_add_to_cart");
	}
	// Call the plus_minus function here for the initial setup, but only for single-product and cart/checkout combined pages
	if(jQuery("body").hasClass("single-product") || jQuery("body").hasClass("woocommerce-cart")) {
		quantity_plus_minus();
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
	// Add "placeholder" strings for font sampling, synced with the defined placeholder
	num_placeholder_val = jQuery("#wccf_product_field_rider_number").attr('placeholder');
	jQuery(".styled.ridernumber").empty().text(num_placeholder_val);
	name_placeholder_val = jQuery("#wccf_product_field_rider_name").attr('placeholder');
	jQuery(".styled.ridername").empty().text(name_placeholder_val);
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
	if(jQuery("div.quantity .plus").length == 0) {
		jQuery("<div class='plus'>+</div>").appendTo("div.quantity");
	}
	if(jQuery("div.quantity .minus").length == 0) {
		jQuery("<div class='minus'>-</div>").prependTo("div.quantity");
	}
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

// Remove all Payment Request Buttons except the first one (used in one-page cart & checkout page)
jQuery('#wc-stripe-payment-request-button').bind('DOMSubtreeModified', function() {
  jQuery('#wc-stripe-payment-request-button > #wc-stripe-branded-button:not(:first-child)').remove();
});

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
	}, 200);
});
// Dynamically change the Rider Number in styles list
jQuery("#wccf_product_field_rider_number").on('input', function(e) {
	setTimeout(function() {
		jQuery(".styled.ridernumber").empty().text( jQuery("#wccf_product_field_rider_number").val() );
	}, 200);
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
jQuery('#payment.woocommerce-checkout-payment').live('focusin', 'li.wc_payment_method input', (function(event) {
	setTimeout(function() {
		jQuery(".form-row.place-order")[0].scrollIntoView({ behavior: "smooth", block: "end" });
	}, 350);
}));

// Change payment method to PayPal when clicking on PayPal link in custom error message
jQuery(document).on("click", ".error_paypal_link", function(){
  jQuery("#payment_method_paypal").click(); 
  jQuery("#payment_method_paypal").click();
});

// Trigger Ajax update checkout when updating shippin method
jQuery("#shipping_method input").on("change", function() {
	jQuery(document.body).trigger("update_checkout");
});