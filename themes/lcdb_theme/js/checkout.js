$(document).ready(function(){
    drop_down_list_without_submit($('.content-address-invoice select'));
    drop_down_list_without_submit($('.content-address-delivery select'));

    $('#modify-address-invoice').click(function(){
        $('.content-address-invoice select').selectbox('disable');
        $('#address_invoice').fadeOut('fast', function(){
            $('#form-address-invoice').fadeIn('fast');
        });
        $('#modify-address-invoice').fadeOut('fast', function(){
            $('#cancel-address-invoice').fadeIn('fast');
        });
        return false;
    });
    $('#cancel-address-invoice').click(function(){
        $('.content-address-invoice select').selectbox('enable');
        $('#form-address-invoice').fadeOut('fast', function(){
            $('#address_invoice').fadeIn('fast');
        });
        $('#cancel-address-invoice').fadeOut('fast', function(){
            $('#modify-address-invoice').fadeIn('fast');
        });
        return false;
    });
    $('#address-submit-invoice').click(function(){
        //send ajax request to save the address
        checkInformations();
        return false;
    });

    $('#modify-address-delivery').click(function(){
        $('.content-address-delivery select').selectbox('disable');
        $('#address_delivery').fadeOut('fast', function(){
            $('#form-address-delivery').fadeIn('fast');
        });
        $('#modify-address-delivery').fadeOut('fast', function(){
            $('#cancel-address-delivery').fadeIn('fast');
        });
        $('#add-address-delivery').fadeOut('fast');
        return false;
    });
    $('#cancel-address-delivery').click(function(){
        $('.content-address-delivery select').selectbox('enable');
        $('#form-address-delivery:visible').fadeOut('fast', function(){
            $('#address_delivery').fadeIn('fast');
        });
        $('#cancel-address-delivery').fadeOut('fast', function(){
            $('#modify-address-delivery').fadeIn('fast');
            $('#add-address-delivery').fadeIn('fast');
        });
        return false;
    });
    $('#cancel-add-address-delivery').click(function(){
        $('.content-address-delivery select').selectbox('enable');
        $('#form-add-address-delivery:visible').fadeOut('fast', function(){
            $('#address_delivery').fadeIn('fast');
            $('#delivery-address .sbHolder').fadeIn('fast');
        });
        $('#cancel-add-address-delivery').fadeOut('fast', function(){
            $('#modify-address-delivery').fadeIn('fast');
            $('#add-address-delivery').fadeIn('fast');
        });
        return false;
    });
    $('#address-submit-delivery').click(function(){
        //send ajax request to save the address
        //change address with the one from ajax response
        checkInformations();
        return false;
    });

    $('#add-address-delivery').click(function(){
        $('.content-address-delivery select').selectbox('disable');
        $('#delivery-address .sbHolder').fadeOut('fast');
        $('#address_delivery').fadeOut('fast', function(){
            $('#form-add-address-delivery').fadeIn('fast', function(){
                $('#new-title').focus();
            });
        });
        $('#modify-address-delivery').fadeOut('fast', function(){
            $('#cancel-add-address-delivery').fadeIn('fast');
        });
        $('#add-address-delivery').fadeOut('fast');
        return false;
    });
    $('#add-address-submit-delivery').click(function(){
        //send ajax request to save the new address
        //change address according to the ajax response
        $('#delivery-address .sbHolder').fadeIn('fast');
        checkInformations();
        return false;
    });

    $('#colis-cadeau').change(function(){
        //checkInformations();
        var $textarea = $('#gift_message'),
            total    = parseFloat($('#final-price').text().replace(',','.')),
            sup      = parseFloat($('#sup').text().replace(',','.'));
        if ($('#colis-cadeau').is(':checked')) {
            $textarea.removeAttr('disabled');
            $textarea.fadeIn('fast');
            var price = total+sup;
            $('#final-price').text(price.toFixed(2).replace('.',','));
            $('#final-price').data('price', price.toFixed(2).replace('.',','));
        } else {
            $textarea.fadeOut('fast', function(){
                $textarea.attr('disabled', 'disabled');
            });
            var price = total-sup;
            $('#final-price').text(price.toFixed(2).replace('.',','));
            $('#final-price').data('price', price.toFixed(2).replace('.',','));
        }
        
    });

    $('#delivery-address .sbOptions li').click(function(){
        checkInformations();
    });


    // logique checkout 2
    $('#home-office').change(function(){
        var $home  = $('#delivery-address'),
            $relay = $('#delivery-relay');
        if ($('#home-office').is(':checked')) {
            $relay.hide();
            $home.show();
            setDeliveryCost('home');
        }
        checkInformations();
    });

    $('#relay').change(function(){
        var $home  = $('#delivery-address');
        var $relay = $('#delivery-relay');
        if ($('#relay').is(':checked')) {
            $home.hide();
            $relay.show();
            $('#show-map').trigger('click');
            setDeliveryCost('relay');
        }
    });

    $('#frozen').change(function(){
        var $home  = $('#delivery-address'),
            $relay = $('#delivery-relay');
        if ($('#home-office').is(':checked')) {
            $relay.hide();
            $home.show();
            setDeliveryCost('frozen');
        }
        checkInformations();
    });

    $('#relays').click(function(event) {
        if ($(event.target).is($('#relays'))) {
            $('.popin-close').trigger('click');
        }
        return false;
    })

    $('.popin-close').click(function() {
        $('#relays').hide();
        return false;
    });


    enablePR = function() {
         $('.choose-relay_').on('click',function(e) {
            e.preventDefault();
            //updae.stopPropagation();
            $('#relays').show();
            $('#relays').css({'height': $(document).height(), 'width': $(document).width()});
            if (google) {
                google.maps.event.trigger(map, 'resize');
                map.setCenter(new google.maps.LatLng(defaultLat, defaultLon));
            }
        });
    }
    enablePR();

    function checkInformations() {
        // var postalCode = $('#address_delivery .address_postcode').text();
            
        // $('#colis-cadeau-wrapper').hide();
        // $('#gift_message').attr('disabled', 'disabled');


        // if (postalCode.substring(0,2) === "75") {
        //     //Paris
        //     $('#colis-cadeau-wrapper').show();
        //     $('#gift_message').attr('disabled', '');
        // } else {
        //     $('#colis-cadeau-wrapper').hide();
        //     $('#gift_message').attr('disabled', 'disabled');
        // }
        //     priceLogic(40);
        //     setDeliveryCost('home');
        //     $('#delivery-home-li').show();
        //     $('#delivery-home-li .radio').trigger('click');
        //     $('#delivery-relay-li').show();
        //     $('#delivery-frozen-li').hide();
        //     $('#colis-cadeau-wrapper').show();
        //     $('#gift_message').removeAttr('disabled');
        // } else if ($.inArray(postalCode.substring(0,2), zipCodes.proche) != -1 || $.inArray(postalCode, zipCodes.proche) != -1) {
        //     //Proche banlieue
        //     priceLogic(40); // à changer
        //     setDeliveryCost('home');
        //     $('#delivery-home-li').show();
        //     $('#delivery-home-li .radio').trigger('click');
        //     $('#delivery-frozen-li, #delivery-relay-li').hide();
        // } else if ($.inArray(postalCode.substring(0,2), zipCodes.grande) != -1 || $.inArray(postalCode, zipCodes.grande) != -1) {
        //     //Grande banlieue
        //     priceLogic(65);
        //     setDeliveryCost('frozen');
        //     $('#delivery-frozen-li').show();
        //     $('#delivery-frozen-li label').trigger('click');
        //     $('#delivery-frozen-li label').trigger('click');
        //     $('#delivery-home-li, #delivery-relay-li').hide();
        // } else {
        //     //Province
        //     priceLogic(65);
        //     setDeliveryCost('frozen');
        //     $('#delivery-frozen-li').show();
        //     $('#delivery-frozen-li .radio').trigger('click');
        //     $('#delivery-frozen-li .radio').trigger('click');
        //     $('#delivery-home-li, #delivery-relay-li').hide();
        // }

    }

    function priceLogic(min) {
        var price      = parseFloat($('#final-price').text().replace(',','.')),
            postalCode = $('.postal-code.check').text(),
            error      = $('#error-price'),
            current    = $('#final-price').text();

        if (price < min) {
            $('#error-postal').text(postalCode);
            $('#error-minimum-price').text(min);
            $('#error-current-price').text(current);
            error.show();
            disableSubmit();
        } else {
            error.hide();
            enableSubmit();
        }
    }

    function setDeliveryCost(mode) {
        var price = parseFloat($('#final-price').text().replace(',','.')),
            fdp   = 0;

        switch (mode) {
            case 'home':
                if (price < 50) {
                    $('#delivery-home-li .delivery-cost').text('5€');
                    fdp = 5;
                } else {
                    $('#delivery-home-li .delivery-cost').addClass('free-delivery').text('livraison offerte');
                    fdp = 0;
                }
                break;
            case 'relay':
                $('#delivery-relay-li .delivery-cost').addClass('free-delivery').text('livraison gratuite');
                fdp = 0;
                break;
            case 'frozen':
                if (price >= 65 && price < 90) {
                    $('#delivery-frozen-li .delivery-cost').text('20€');
                    fdp = 20;
                } else if (price >= 90 && price < 220) {
                    $('#delivery-frozen-li .delivery-cost').text('14€');
                    fdp = 14;
                } else {
                    $('#delivery-frozen-li .delivery-cost').addClass('free-delivery').text('livraison offerte');
                    fdp = 0;
                }
                break;
        }
        var t = parseFloat($('#final-price').data('price').toString().replace(',','.')) + fdp;
        $('#final-price').text(t.toFixed(2).replace('.',','));
        
    }

    function enableSubmit() {
        $('#submit-address').removeAttr('disabled');
        $('#submit-address').removeClass('disabled-button').addClass('red-button');
    }

    function disableSubmit() {
        // $('#submit-address').attr('disabled', 'disabled');
        // $('#submit-address').removeClass('red-button').addClass('disabled-button');
    }
    if (google) {
        relayInitialize();
    }

    // $('#id_address_delivery').on('change',function() {
    //     $('.content-address-delivery').removeClass('address_disabled');
    // });

    // $('.bloc-checkout').on('click','[name=delivery],[name=id_address_delivery]',function() {
    //     $('.content-address-delivery').removeClass('address_disabled');
    // });

    $('.choose-relay').on('click', function(e) {
        e.preventDefault();
        var relay = $(this).parents('li').data('relay');
        // $('.content-address-delivery').addClass('address_disabled');
        $('label.radio.checked').removeClass('checked');
        $('.choose-relay_').parent().addClass('checked');
        // $('.choose-relay_').val(relay.id_carrier);
        $('#custom_relay').val(relay.id_carrier);
        $('.choose-relay_').attr('checked','checked');
        $('#address_delivery .add-new-addr').hide();
        $('#address_delivery .address_address1').html(relay.name);
        $('#address_delivery .address_address2').html(relay.address[0]); // bcs facultatif
        $('#address_delivery .address_postcode').html(relay.postcode + ' ' + relay.city);
        $('#address_delivery .address_country').html(relay.address[1]);
        $('#address_delivery .address_phone').html(relay.phone);
        $('#address_delivery .address_update').html(relay.mention);

        //set description
        $(".choose-relay_").parentsUntil('ul').
            find('.description').
            html('<span class="relay-name">' + relay.name + "</span><br/><span class='relay-description'>" + relay.description);

        $('.popin-close').trigger('click');

        //check minimum order
        if (relay.name == "Les Colis du Boucher")
            updateMinimumOrderError(0, total_price); //total_price declared in order-carrier.tpl
        else
            updateMinimumOrderError(minimum_order_zone_proche, total_price); //minimum_zone and total_price declared in order-carrier.tpl
    });

    checkInformations();
    setDeliveryCost('relay');
});

function choose_relay2(div) {
    var relay = $(div).parents('div').data('relay');
    // $('.content-address-delivery').addClass('address_disabled');
    $('label.radio.checked').removeClass('checked');
    $('.choose-relay_').parent().addClass('checked');
    // $('.choose-relay_').val(relay.id_carrier);
    $('#custom_relay').val(relay.id_carrier);
    $('.choose-relay_').attr('checked','checked');
    $('#address_delivery .add-new-addr').hide();
    $('#address_delivery .address_address1').html(relay.name);
    $('#address_delivery .address_address2').html(relay.address[0]); // bcs facultatif
    $('#address_delivery .address_postcode').html(relay.postcode + ' ' + relay.city);
    $('#address_delivery .address_country').html(relay.address[1]);
    $('#address_delivery .address_phone').html(relay.phone);
    $('#address_delivery .address_update').html(relay.mention);

    //set description
    $(".choose-relay_").parentsUntil('ul').
        find('.description').
        html('<span class="relay-name">' + relay.name + "</span><br/><span class='relay-description'>" + relay.description);

    $('.popin-close').trigger('click');

    //check minimum order
    if (relay.name == "Les Colis du Boucher")
        updateMinimumOrderError(0, total_price); //total_price declared in order-carrier.tpl
    else
        updateMinimumOrderError(minimum_order_zone_proche, total_price); //minimum_zone and total_price declared in order-carrier.tpl

}