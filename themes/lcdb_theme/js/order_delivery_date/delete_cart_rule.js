/**
 * Created by gladisk on 11/17/15.
 */
//=========================================================================================
//Delete cart rule from cart ajax
//=========================================================================================
//@Ajax Function See:
//Function override/Controller/OrderController line 204

function deleteCartRuleFromList(id_cart_rule){
    //avoid error
    if (typeof cart_rules === 'undefined') //@see order-delivery-date.tpl line 14
        return;

    //make tmp to loop through

    //find rules
    var array_length = cart_rules.length;
    for (var i = 0; i < array_length; i++) {
        //if rule found
        if (cart_rules[i].id === id_cart_rule) {
            cart_rules.splice(i, 1); //delete element from array
            //decrement as we delete an element
            array_length--;
            i--;
        }
    }
}

$(function(){
    //On click event on delete_promo Button
    $('#delete_promo').click(function(){
        //get Id cart rule
        var id_cart_rule = $('#delete_promo').attr('id-cart-rule');
        console.log('====> Deleting promo #' + id_cart_rule + '...');

        //Hide error (that might be shown previously)
        $('#deleting_promo_error').addClass('hidden');

        //Display loading message
        $('#deleting_promo_loading_msg').removeClass('hidden');

        //Call ajax method to delete cart rule
        $.getJSON(
            LCDB_BASE_URL + "/commande",
            {ajax: 1, method: "deleteCartRuleFromCart", "id_cart_rule": id_cart_rule})
            .done(function(res){
                //if php func succeed AND Js cart rules are defined (to avoid undefined var error)
                if (res.success == 1 && typeof cart_rules !== 'undefined') { //@see order-delivery-date.tpl line 14
                    //Delete rule from JS rules list
                    deleteCartRuleFromList(id_cart_rule);

                    //Display info
                    $('#info_cart_rule_deleted #new_order_total').html(res.new_order_total_without_shipping);
                    $('#info_cart_rule_deleted').removeClass('hidden');

                    //Trigger click on current date to relaunch processes
                    console.log("====> Success: Promo deleted");
                    $("#calendar .core.selected").click();
                }
                else {
                    //Display error
                    console.log("====> Error: Can't delete promo");
                    $('#deleting_promo_error').removeClass('hidden');
                }
                //Remove loading message
                $('#deleting_promo_loading_msg').addClass('hidden');

            })
            .fail(function(jqXHR, exception) {
                if (jqXHR.status === 0)
                    alert('Not connect.\n Verify Network.');
                else if (jqXHR.status == 404)
                    alert('Requested page not found. [404]');
                else if (jqXHR.status == 500)
                    alert('Internal Server Error [500].');
                else if (exception === 'parsererror')
                    alert('Requested JSON parse failed.');
                else if (exception === 'timeout')
                    alert('Time out error.');
                else if (exception === 'abort')
                    alert('Ajax request aborted.');
                else
                    alert('Uncaught Error.\n' + jqXHR.responseText);
                //Display error
                $('#deleting_promo_error').removeClass('hidden');
                //Remove loading message
                $('#deleting_promo_loading_msg').addClass('hidden');
            });
    })
});
