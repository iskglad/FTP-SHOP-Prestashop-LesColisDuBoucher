/**
 * Created by gladisk on 2/15/15.
 */
//=========================================================================================
//Display out of cart rules error
//=========================================================================================

function display_out_of_cart_rules_date_error(date_string, cart_rules){
    //init vars
    var from = null;
    var to = null;
    var date = new Date(date_string);

    //hide hours select section
    $('#selected-hours').addClass('hidden');

    //Hide adjustment infos
    $(".adjustment_link").addClass('hidden');
    $(".adjustment_infos").addClass('hidden');

    //Disable validation button
    $('#validate_order').prop("disabled",true);

    console.log("Discount Limits");

    //find rules that are out of date
    var array_length = cart_rules.length;
    for (var i = 0; i < array_length; i++) {

        console.log("====>" + cart_rules[i].name + "...");

        //Convert to Js Date
        from = new Date(cart_rules[i].from.substr(0, 10));
        to = new Date(cart_rules[i].to.substr(0, 10));

        //if limit set AND not freeShipping setted automatically
        if (!isNaN(from.getTime()) && !isNaN(to.getTime()) && cart_rules[i].name != "Frais de livraison offerts") {
             //if date out of limit
             if (date.getTime() < from.getTime() ||
                 date.getTime() > to.getTime()) {

                //Display error
                $('#delete_promo').attr('id-cart-rule', cart_rules[i].id);
                $('#warning_out_of_cart_rule_date .cart_rule_name').html(cart_rules[i].name);
                $('#warning_out_of_cart_rule_date .cart_rule_action').html(cart_rules[i].action);
                $('#warning_out_of_cart_rule_date .cart_rule_from').html(cart_rules[i].from_fr_string);
                $('#warning_out_of_cart_rule_date .cart_rule_to').html(cart_rules[i].to_fr_string);
                $('#warning_out_of_cart_rule_date').removeClass('hidden');

                console.log("Failed : " + cart_rules[i].from_fr_string + "-" + cart_rules[i].to_fr_string + "required");
                return 1;
            }
        }
        console.log("OK");
    }

    //Show back hours select section
    $('#selected-hours').removeClass('hidden');

    //Enable validation button
    $('#date-livraison').find('.action button').removeAttr('disabled');

    //hide error
    $('#warning_out_of_cart_rule_date').addClass('hidden');

    console.log("All rules allowed");
    return 0;
}