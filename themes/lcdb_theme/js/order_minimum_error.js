/**
 * Created by gladisk on 11/18/14.
 */

/*
mimimum: the minimum price to make a commande with a delivery option
actual: the current command price TTC

Compare minimum and price and disable submit button if necessary
 */


function updateMinimumOrderError(minimum,actual) {
    console.log('--> Updating minimum price to ' + minimum + 'euros (current basket at ' + actual + 'euros)');
    //set text minimum price in error message
    $('#error-minimum-price').html(minimum);

    if (actual > minimum || minimum == 0) {
        $('#error-price').hide()
        $('#total-price').show()
        //Enable submit
        $('#submit-address').removeAttr('disabled');
        $('#submit-address').removeClass('disabled-button');
    } else {
        $('#error-price').show()
        $('#total-price').hide()
        //Disable submit
        $('#submit-address').attr('disabled', 'true');
        $('#submit-address').addClass('disabled-button');
    }
};
$(function(){
    //on label click
    $('label').live('click', function(){
        //total_price and minimum_order_zone_proche are declared in order-carrier.tpl
        var total = total_price;
        var min_zone = minimum_order_current_zone;

        //no minimum order for "livraison en point relais"
        // (it will be check later in checkout.js l.333)
        if ($(this).find('.choose-relay_').length){
            updateMinimumOrderError(0, total);
        }
        //check error with current zone min order
        else
            updateMinimumOrderError(min_zone, total);
    });
});