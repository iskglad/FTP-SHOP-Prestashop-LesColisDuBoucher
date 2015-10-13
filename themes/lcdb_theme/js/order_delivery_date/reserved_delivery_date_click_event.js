/**
 * Created by gladisk on 1/28/15.
 */
//=========================================================================================
//Event Binding for clicking on red dates (already saved dates) on delivery date calendar
//=========================================================================================
//@CalledFrom main.js 569
// Parameters that are passed to the callback:
    //     el : The input element the date picker is bound to
    //   cell : The cell on the calendar that triggered this event
    //   date : The date associated with the cell
    //   data : Special data associated with the cell (if available, otherwise, null)
function reserved_delivery_date_click_event(el, cell, date, data){
    //Disable validation button
    $('#date-livraison').find('.action button').attr('disabled', 'disabled');

    //hide hours selector
    $('#selected-hours .hours').addClass('hidden');

    //set adjustment infos
    $('#selected-hours .adjustment_infos .id_order').html(data.order.id_order);
    $('#selected-hours .adjustment_infos .delivery_hours').html(data.order.hour_delivery);
    $('#selected-hours .adjustment_infos .carrier_name').html(data.order.carrier_name);
    $('#selected-hours .adjustment_infos .delivery_postcode').html(data.order.delivery_postcode);

    //set adjustment link
    var base_url = $('#selected-hours .adjustment_infos a.adjustment_link').attr('base_url');
    var adjustment_url = base_url + 'index.php?controller=order&adjust=' + data.order.id_order;
    $('#selected-hours .adjustment_infos a.adjustment_link').attr('href', adjustment_url);

    //Display adjustment infos
    $('#selected-hours .adjustment_infos').removeClass('hidden');
}