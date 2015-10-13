/**
 * Created by gladisk on 2/16/15.
 */
//=============================================================================
//Change the display price when selecting product surprise
//=============================================================================
function set_selected_colis_price(){
    var colis_price = $(".attribute_select").find(":selected").attr('jsData_price'); //@See jsData_price @ theme/product-surprise.tpl line 59
    colis_price = Math.round(colis_price * 100) / 100
    console.log('colis price: ' + colis_price);
    $("#our_price_display").html( colis_price + " \u20AC");
}

$(function(){
    set_selected_colis_price();
    //On change
    $(".attribute_select").change(function(){
        set_selected_colis_price();
    });
});