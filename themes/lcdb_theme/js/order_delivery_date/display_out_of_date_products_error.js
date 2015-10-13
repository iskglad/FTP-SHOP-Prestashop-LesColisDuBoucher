/**
 * Created by gladisk on 2/11/15.
 */

function display_out_of_date_products_error(date, isSpecial){

    //Disable validation button
    $('#validate_order').prop("disabled",true);
    $(".adjustment_link").addClass('hidden');


    //get out of date products
    get_out_of_date_products(date, function(out_of_date_products){
        if (out_of_date_products == -1)
            return;

        //init vars
        var products_name = '';

        //concat product names
        for (var i = 0; i < out_of_date_products.length; i++){
            products_name += "- " + out_of_date_products[i]['name'];
            if (i + 1 < out_of_date_products.length)
                products_name += "<br/>";
        }

        //if out of date products found
        if (products_name.length > 0){
            //Disable validation button
            $('#date-livraison').find('.action button').attr('disabled', 'disabled');

            if (isSpecial){
                $(".adjustment_link").addClass('hidden');
            }


            //Display error
            $('#warning_out_of_date_products .products_name').html(products_name);
            $('#warning_out_of_date_products').removeClass('hidden');
            return 1;
        }
        else {
            //Enable validation button
            if (isSpecial)
                $(".adjustment_link").removeClass('hidden');
            else
                $('#date-livraison').find('.action button').removeAttr('disabled');

            //hide error
            $('#warning_out_of_date_products').addClass('hidden');
            return 0;
        }
    });
}