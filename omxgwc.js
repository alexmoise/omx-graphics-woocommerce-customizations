/** 
 * JS functions for OMX Graphics Woocommerce customizations plugin
 * Version 1.2.27
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
	// Update the price situated under the product title in Single Product pages - at any form.cart "change" event
	jQuery("form.cart").change(function(e) { 
		// Doing it ony if it's an "originalEvent" - that means user triggered (because it refreshes at loading as well and we don't want that)
		if (e.originalEvent) {
			jQuery(".wccf_field_container").stop(false,true); // Stop fading fields in and out (and whatever else is doing, just do it quick and preserve the queue - thus "false,true")
				// Try to update the price every 500 miliseconds for 3 seconds
			var startPriceUpdateTimer = (new Date()).getTime();
			var timer_id = setInterval(function(){
				var currentPriceUpdateTimer = (new Date()).getTime();
				if((currentPriceUpdateTimer - startPriceUpdateTimer)/1000 > 3) clearInterval(timer_id);
				// console.log('Updating ... ');
				// Change the class of "ins" container if a Sale Price is involved
				if(jQuery("ins.sale_price")) 		{ jQuery("ins.sale_price").attr('class', 'reg_price'); };
				// Remove Sale Price elements if they exists
				if(jQuery("span.price_separator")) 	{ jQuery("span.price_separator").remove(); };
				if(jQuery("del.reg_price")) 		{ jQuery("del.reg_price").remove(); };
				if(jQuery("span.percent_off")) 		{ jQuery("span.percent_off").remove(); };
				if(jQuery("span.saved_percent")) 	{ jQuery("span.saved_percent").remove(); };
				// Check if the Add to Cart is not disabled
				if ( jQuery("#omx_add_to_cart button.single_add_to_cart_button").is(":not(.disabled)") ) {
					// Update the price finally
					jQuery(".omx_price .reg_price").html(jQuery(".rightpress_product_price_live_update .price").html());
				} else {
					// If Add to Cart button is disabled then display something else (instead of updating the price)
					jQuery(".omx_price .reg_price .woocommerce-Price-amount.amount").html("<span class='choose_an_option'>Please choose an option.</span>");
					clearInterval(timer_id);
				};
			}, 500);
		}
	});
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
		} 
		if (scrollPosition <= 10) {
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
	// Change selected variation based on thumbnail click 
	// ## Attribute slug MUST BE "color_combination" for this to work! ##
	var variation_data = jQuery('.variations_form').data("product_variations");
	jQuery( ".woocommerce-product-gallery img" ).on( "touchend mouseup", function() {
		// a slight delay allowing for WooCommerce functions to assign '.flex-active-slide' class to the new thumb in the first place
		setTimeout(function() {
			var selected_thumb = jQuery('.flex-active-slide').children("a").prop('href');
			jQuery.each(variation_data, function(i, v) {
				if(this.image.full_src == selected_thumb) {
					// console.log(this.attributes.attribute_pa_color_combination);
					jQuery("#pa_color_combination").val(this.attributes.attribute_pa_color_combination).change();
				}
			});
		}, 25);
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