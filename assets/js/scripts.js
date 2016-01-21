jQuery(document).ready(function (e) {
    jQuery(".edd_gift_product_btn").click(function () {

        jQuery(".edd_gift_product_div").fadeIn(300);

        jQuery(".edd_gift_product_btn").hide();

        return false;
    });

    jQuery("#edd-gift-purchase").click(function(){
        if(jQuery(this).is(":checked")) {
            jQuery("#edd-purchase-button").hide();
            jQuery(".edd_gift_product_btn").show();

            jQuery("#edd-gift-purchase-button").clone().appendTo(".edd_gift_product_div");
            jQuery("#edd-gift-purchase-button").show();
        } else {
            jQuery(".edd_gift_product_div").fadeOut();
            jQuery("#edd-gift-purchase-button").hide();

            jQuery("#edd-purchase-button").show();
            jQuery(".edd_gift_product_btn").hide();
        }
    });
});

jQuery(".div").append('<div></div>');