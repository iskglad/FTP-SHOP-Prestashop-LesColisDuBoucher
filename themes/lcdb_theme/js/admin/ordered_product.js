/**
 * Created by gladisk on 1/7/15.
 */
$(function(){
    //==========================================
    //CLIENT LIST AND PRODUCT RECAP SLIDE TOGGLE
    //==========================================
    //close all accordion on page load
    $('.buyers, .products-recap').slideUp(0);

    //bind click event
    $('.product-row, .order-row').click(function(e) {
        $(this).next('.buyers, .products-recap')
            .fadeToggle("fast");
        return true;
    });
    //==========================================
    //UPDATE HOURS
    //==========================================
    $('.updatable').click(function(event){
        event.stopPropagation();
    });
    //Update Hours
    $('.delivery_hours.updatable').change(function(){
       var id_order = $(this).attr('data_id_order');
       $.post( "#", { update: 1, hours: $(this).val(), id_order : id_order }).
           done(function( data ) {
               if (data != "ok")
                alert( "Erreur:" + data);
       });
    });
    //Update carrier
    $('select.carrier.updatable').change(function(){
        var id_order = $(this).attr('data_id_order');
        $.post( "#", { update: 1, id_carrier: $(this).val(), id_order : id_order }).
            done(function( data ) {
                if (data != "ok")
                    alert( "Erreur:" + data);
            });
    });
    //==========================================
    //SUBMIT EVENTS
    //==========================================
    function exportCvs(){
        $('input#exportCsv').val("1");

        //submit
        $('#submitFilterButtonorder').click();

        //back to initial value after download
        $('input#exportCsv').val("0");
    }

    function carrierFileExport(){
        if ($(this).hasClass('export_etiquette')){
            $('input#export_carrier_file_type').val("carrier_etiquette");
        }
        if ($(this).hasClass('export_csv'))
            $('input#export_carrier_file_type').val("carrier_csv");

        //set DataSystemFile to Call (usually will be "ecolos" or "jet"
        $('input#data_system_file').val($(this).attr('jsData_data_system_file'));
        //submit
        $('#submitFilterButtonorder').click();

        //back to initial value after download
        $('input#export_carrier_file_type').val("0");
    }

    function resetFilter(){
        $('table.order input').val(""); //empty all input
        $('table.order select').val(""); //empty all select

        //submit
        $('#submitFilterButtonorder').click();
    }

    $('table.order .filter').keypress(function(event){
        formSubmit(event, 'submitFilterButtonorder')
    });

    $("#resetFilterButton").click(resetFilter); //on click, call resetFilterFunc
    $("#exportCsvButton").click(exportCvs); //on click, call exportCvs
    $(".carrier_file_export").click(carrierFileExport); //on click, call carrierFileExport

    //==========================================
    //DATE PICKING
    //==========================================
    $(".datepicker").change(function(){
        //if changing interval_begin AND not using interval
        if ($(this).hasClass('interval_begin') &&
            !($('.use_date_interval').is(':checked'))){
            $('.datepicker.interval_end').val($(this).val()); //set same date for interval_end
        }

        //set date on hidden input
            //interval begin
        $("input[name='date_interval_begin']").val($('.datepicker.interval_begin').val());
            //interval end
        $("input[name='date_interval_end']").val($('.datepicker.interval_end').val());
            //use date interval
        $("input[name='use_date_interval']").val($('.use_date_interval').is(':checked'));

        //make click on filter button to submit
        $("#submitFilterButtonorder").click();
    });

    $(".dateorder .datepicker").datepicker({
        prevText: '',
        nextText: '',
        dateFormat: 'yy-mm-dd'
    });
    if ($(".datepicker").length > 0)
        $(".datepicker").datepicker({
            prevText: '',
            nextText: '',
            dateFormat: 'yy-mm-dd'
        });

    //Hide/Display <interval_end datepicker> according to use_date_interval checkbox
    if ($('.use_date_interval').is(':checked'))
        $('.datepicker.interval_end').removeClass('hidden');
    else
        $('.datepicker.interval_end').addClass('hidden');

    //Toggle <interval_end datepicker> display on checkbox change
    $('.use_date_interval').change(function(){
        $('.datepicker.interval_end').toggleClass('hidden');
    });
});
