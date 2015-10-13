/**
 * Created by gladisk on 11/18/14.
 */
$(function(){
    //on label click
    $('label').live('click', function(){
        //total_price is declared in order-carrier.tpl
        var shipping_price = $(this).find('.shipping_price').attr('data-shipping-price');
        if (!shipping_price)
            shipping_price = 0;
        var total = parseFloat(total_price) + parseFloat(shipping_price);
        //round decimal 2
        total = Math.round(total * 100) / 100;
        console.log("Oder total price-->" + total_price + ' + ' + shipping_price + ' = ' + total);
        $('#final-price').html(total.toString());
        $('#final-price').attr('data-price', total);
    });

    //choose the first elem
    $('.delivery_options_address label').first().click();
    $('.delivery_options_address label').first().addClass('checked');
});