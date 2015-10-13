/**
 * Created by gladisk on 1/28/15.
 */
//=========================================================================================
//Get all reserved date of current user for red dates in calendar (on delivery date front page)
//=========================================================================================
//@CalledFrom main.js line 561
//@Return Array({date, data, cssClass}) used for glDatePicker

function get_reserved_delivery_date_sync(){
    //Ajax sync
    //Get next orders delivery dates to make red square days in calendar
    //php function: see override/controller/front/OrderController.php line 191
    var reserved_delivery_dates = [];
    console.log('====> Getting reserved delivery dates >');
    $.ajax({
        type: 'POST',
        url: 'http://lescolisduboucher.com/commande',
        data: {ajax: 1, method: "getCustomerDeliveryDates"},
        dataType: 'json',
        async:false, //Synchronous
        success: function(jsonRes){

            console.log(JSON.parse(JSON.stringify(jsonRes)));
            orders = jsonRes;
            console.log(orders.length + " orders found");
            for (var i = 0; i < orders.length; i++) {
                console.log(i + "=>");
                console.log(JSON.parse(JSON.stringify(orders[i])));
                if (orders[i].date_delivery_timestamp != -1){
                    var date_string = orders[i].date_delivery.substr(0, 10);
                    console.log('converting date :' + date_string);
                    reserved_delivery_dates.push({
                        date: new Date(date_string),
                        data: { order: orders[i] },
                        cssClass: 'special'
                    });
                    console.log(JSON.stringify(reserved_delivery_dates));
                }
                
            }

        },
        error: function(){
            console.log('Error');
        }
    });
    console.log("Reserved dates count: " + reserved_delivery_dates.length);
    return reserved_delivery_dates;
}