/**
 * Created by gladisk on 2/11/15.
 */
//=========================================================================================
//Get product that are out of date on the deliveryDate choosen (on delivery date front page)
//=========================================================================================
//@Return out of date products array, Object=>phpClass Product

function get_out_of_date_products(date, callback){
    //Ajax
    //Get product that are out of date on the deliveryDate choosen
    //php function: see override/controller/front/OrderController.php line 196
    console.log('====> Getting outOfDeliveryDate products...');
    $.ajax({
        type: 'POST',
        url: 'http://lescolisduboucher.com/commande',
        data: {ajax: 1, method: "getOutOfDeliveryDateProducts", date_delivery: date},
        dataType: 'json',
        success: function(out_of_date_products){
                console.log('====> Success: ' + out_of_date_products.length + " outdated products found.");
                callback(out_of_date_products);
        },
            error: function(jqXHR, exception) {
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
                callback(-1);
            }
    });
}