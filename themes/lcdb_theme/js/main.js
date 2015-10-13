$(document).ready(function(){
    submenu();

    $.ajax({
        method: 'POST',
        url: $('#form-code-postal_cart').attr('action'),
        data: { 'code_postal' : '7578','bouton_intro':'OK' },
        success: function(response) {

            //$('.bloc_inf_livraison').hide();
            var t = $(response).find('.bloc_inf_livraison');
            $('#form-code-postal_cart').after(t);
            if (parseInt($('#total_price').text()) < $('.rouge').text().substr($('.rouge').text().length-4,2)) {//parseInt(window.lastMinOrder)
                $('th.rouge').addClass('error');
            }

            if (parseInt($('#total_price').text()) > $('#rougerouge').text().substr($('#rougerouge').text().length-4,2)) {//window.lastMinOrder
                $('#validate-cart').removeClass('disabled-button');
                $('#rougerouge').removeClass('error');
            } else {
                //$('#validate-cart').addClass('disabled-button');
                //$('#rougerouge').addClass('error');

            }
        }
    })
    Cufon.replace('.global .footer-top ul li > p.push');
    Cufon.replace('.guestbook .green-button');
    $('#relays').hide();
    $('body').on('click', '#show-map', function (e){


        $('#relay-list li').remove();
        if (google) {
            relayInitialize();
        }

        e.preventDefault();
        e.stopPropagation();
        $('#relays').show();
        $('#relays').css({'height': $(document).height(), 'width': $(document).width()});
        if (google) {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(new google.maps.LatLng(defaultLat, defaultLon));
        }


    });
    $('body').on('click', '.popin-close', function (e){

        $('#relays').hide();
        return false;
    });
    $('#validate-cart').on('click',function(e) {
        // alert($('#validate-cart').hasClass('disabled-button'));
        if ($('#validate-cart').hasClass('disabled-button') == true){
            e.preventDefault();
            e.stopPropagation();

        }
    })

    window.lastMinOrder = undefined;

    if ($('#form-code-postal_cart').length) {
        // setTimeout(function() {
        // 	$('.cart_quantity_up').on('click',function() {
        // 		console.log(checked && parseInt($('#total_price').text()) , lastMinOrder)
        // 		if (checked && parseInt($('#total_price').text()) > lastMinOrder) {
        // 			$('#validate-cart').removeClass('disabled-button');
        // 		} else {
        // 			$('#validate-cart').addClass('disabled-button');
        // 		}
        // 	})
        // },1);

        $('#form-code-postal_cart').on('submit', function(e) {
            e.preventDefault();
            var $this=$(this);
            $input = $('#postal-code',this);
            $.ajax({
                method: 'POST',
                url: $this.attr('action'),
                data: { 'code_postal' : $input.val(), 'ajax': true },
                dataType: 'json',
                success: function(response) {
                    // console.log(response);
                    if (parseInt($('#total_price').text()) >= parseInt(response.minimum_order)) {
                        $('#validate-cart').removeClass('disabled-button');
                    }
                    window.lastMinOrder = parseInt(response.minimum_order);
                }
            })
            $.ajax({
                method: 'POST',
                url: $this.attr('action'),
                data: { 'code_postal' : $input.val(),'bouton_carre':'OK' },
                success: function(response) {
                    $('.bloc_inf_livraison').remove();
                    var t = $(response).find('.bloc_inf_livraison');
                    $('#form-code-postal_cart').after(t);
                    if (parseInt($('#total_price').text()) < parseInt(window.lastMinOrder)) {
                        $('th.rouge').addClass('error');
                    }
                }
            })
        })
    };

    if($("#id_country").length > 0){
        drop_down_list_without_submit($("#id_country"));
    }
    if($(".subject").length > 0){
        drop_down_list_without_submit($(".subject"));
    }
    if ($("#sort").length > 0) {
        drop_down_list_with_submit($("#sort"));
    }
    if ($("#selectPrductSort").length > 0) {
        drop_down_list_with_submit($("#selectPrductSort"));
    }
    if ($('#nombre-portions').length > 0) {
        drop_down_list_without_submit($('#nombre-portions'));
        drop_down_list_without_submit($('#step3 select'));

        var $bimensuelle  	= $('#bi-mensuelle-phrase'),
            $dayNum 		= $('#day-number'),
            $dayName		= $('#day-name'),
            $mensuelle 		= $('#mensuelle-phrase');

        $('#hebdomadaire').change(function(){
            if ($('#hebdomadaire').is(':checked')) {
                $bimensuelle.hide();
                $dayNum.hide();
                $mensuelle.hide();
                $dayName.show();
            }
        });

        $('#bi-mensuelle').change(function(){
            if ($('#bi-mensuelle').is(':checked')) {
                $bimensuelle.show();
                $dayNum.hide();
                $mensuelle.hide();
                $dayName.show();
            }
        });

        $('#mensuelle').change(function(){
            if ($('#mensuelle').is(':checked')) {
                $bimensuelle.hide();
                $dayNum.show();
                $mensuelle.show();
                $dayName.show();
            }
        });

        /* ---------- Vérification du contenu du formulaire ------------*/
        $("#creneau li .sbOptions a").click(function(event){
            var hours_interval 	= 60,
                values 			= new Array(),
                i 				= 0,
                $this 			= $(this),
                $selector 		= $this.parents('li').children('.sbHolder').children('.sbSelector'),
                $button 		= $('#abonnement-submit'),
                $error 			= $('#error');

            $selector.each(function(){
                values[i] = $(this).text().split('h');
                values[i][0] *= 60;
                values[i] =  parseInt(values[i][0]) +  parseInt(values[i][1]);
                i++;
            });
//			if(values[1] - values[0] < hours_interval){
//				$button.attr('disabled', 'disabled').removeClass('red-button').addClass('disabled-button');
//				$error.html("L'intervalle entre les heures n'est pas correct").show();
//			}else if(isNaN(values[1] - values[0])){
//				$error.html("").hide();
//				$button.attr('disabled', 'disabled').removeClass('red-button').addClass('disabled-button');
//			}else{
//				$error.html("").hide();
//				$button.removeAttr('disabled').removeClass('disabled-button').addClass('red-button');
//			}
        });
    }
    if ($('#product').length > 0) {
        drop_down_list_without_submit($('.form-panier select'));
    }
    if ($(".meat-race").length > 0) {
        drop_down_list_without_submit($(".meat-race"));
    }
    if ($('#from-ce').length > 0) {
        drop_down_list_without_submit($('#from-ce select'));

        $('#ce-more').on('click', function(){
            $('#from-ce').stop(true, true).slideDown('slow');
            $('#entreprise').removeAttr('disabled');
            toggle = false;
        });

        $('#ce-less').on('click', function(){
            $('#from-ce').stop(true, true).slideUp('fast');
            $('#entreprise').attr('disabled','disabled');
        });
    }
    if ($('.scrollbar').length > 0) {
        $('.scrollbar').scrollbar();
    }
    if($("#order #form-reduc").length > 0){
        $("#order #form-reduc").submit(function(event){
            event.preventDefault();
            var code = $("#reduction").val();
            var reduc = "8,56";
            var html = '<form method="get"><input type="hidden" name="code" value="'+code+'" readonly />';
            html += '<span><label><span class="code bold">'+code+'</span> <span class="amount bold">(-'+reduc+'€)</span></label></span> ';
            html += '<button type="submit" name="submit">utilisez ce code</button></form>';
            $("#bloc-code").append(html);
        });
    }

    if ($('#form-code-postal').length > 0) {
        $('#form-code-postal').submit(function() {
            var $this 	= $(this),
                $input 	= $this.find('input'),
                $block  = $this.parent();

            if ($input.val().length != 5) {
                $this.find('p.error').remove();
                $this.append('<p class="error">Code invalide</p>');
                return false;
            } else {
                $this.find('p.error').remove();
            }

            $block.find('.response').html('Chargement...');
            $.ajax({
                method: 'POST',
                url: $this.attr('action'),
                dataType: 'json',
                data: { 'code_postal' : $input.val(), 'ajax': 1 }
            }).done(function( data ) {
                var content = '',
                    first = '',
                    head = (data.zone == 1) ? 'Service' : 'Commande';
                $block.find('.header').empty();
                content += '<p class="no-padding">Minimum de commande : '+data.minimum_order+'</p>';
                content += '<table><tbody><tr><th class="col_1">'+head+'</th><th class="col_2">Livraison</th></tr>';
                for (var i = 0; i < data.infos.length; i++) {
                    first = (data.zone == 1) ? data.infos[i].mode : data.infos[i].price;
                    content += '<tr><td class="col_1">'+first+'</td><td class="col_2">'+data.infos[i].ship+'</td></tr>';
                }
                content += '</table>';
                content += data.more;
                $block.find('.response').html(content);

            }).fail(function( jqXHR, textStatus, msg ) {
                alert(msg);
                $this.append('<p class="error">Erreur</p>');
                $block.find('.response').html('');
            }).always(function() {
                $this.find('button').prop('disabled', false);
            });
            return false;
        });
    }

    $('input, textarea').placeholder();

    if($(".guestbook #temoignage").length > 0){
        $(".guestbook #temoignage").css({'display':'none'});
        $(".guestbook #button-witness a").click(function(e){
            e.preventDefault();
            $(".guestbook #temoignage").stop(true, true).slideDown();
        });
        $(".guestbook a#put-witness").click(function(e){
            e.preventDefault();
            var the_id = $(this).attr("href");
            $('html, body').animate({
                scrollTop:$(the_id).offset().top
            }, 'fast');
            $(".guestbook #temoignage").stop(true, true).slideDown('slow');
        });
    }


    // Plus/minus buttons to choose the quantity of items to add to cart
    if ($('.form-panier').length > 0) {
        var $quantity = 0;
        $('.plus').click(function() {
            $quantity = $(this).parent().children('input.quantity');
            var quantity_available = $(this).attr('jsdata_quantity_available');

            //Forbid adding more than availabe quantity
            if ($quantity.val() >= parseInt(quantity_available))
                return;

            value = parseInt($quantity.val()) + 1;
            $quantity.val(value);
            if ($('.product-total-price').length > 0) {
                var $this 	= $(this),
                    unit 	= parseFloat($this.parents('tr').find('.product-unit-price').text().replace(',','.')),
                    qty  	= parseInt($this.siblings('input').val()),
                    total   = unit * qty;

                $this.parents('tr').find('.product-total-price').text(total.toFixed(2).replace('.',','));
                calcTotal();
            }
        });
        $('.minus').click(function() {
            $quantity = $(this).parent().children('input.quantity');
            value = (parseInt($quantity.val()) === 1) ? 1 : parseInt($quantity.val()) - 1;
            $quantity.val(value);
            if ($('.product-total-price').length > 0) {
                var $this 		= $(this),
                    unit 		= parseFloat($this.parents('tr').find('.product-unit-price').text().replace(',','.')),
                    qty  		= parseFloat($this.siblings('input').val()),
                    total   	= unit * qty;

                $this.parents('tr').find('.product-total-price').text(total.toFixed(2).replace('.',','));
                calcTotal();
            }
        });
    }

    function calcTotal() {
        var total = 0.00;
        $.each($('.product-total-price'), function(item, value) {
            total += parseFloat($(value).text().replace(',','.'));
        });
        $('#subtotal').text(total.toFixed(2).replace('.',','));
    }

    if ($('#form-adresse').length > 0) {
        $('#modify-address').click(function(){
            $('#saved-adresse').fadeOut('fast', function(){
                $('#form-adresse').fadeIn('fast');
            });
            $('#modify-address').fadeOut('fast', function(){
                $('#cancel-address').fadeIn('fast');
            });
            return false;
        });
        $('#cancel-address').click(function(){
            $('#form-adresse').fadeOut('fast', function(){
                $('#saved-adresse').fadeIn('fast');
            });
            $('#cancel-address').fadeOut('fast', function(){
                $('#modify-address').fadeIn('fast');
            });
            return false;
        });

        $('#villes').click(function(){
            $('#villes-abonnees').css({'height': $(document).height()});
            $('#villes-abonnees').fadeIn('fast');
            return false;
        });

        $('.popin-close').click(function(){
            $('#villes-abonnees').fadeOut('fast');
            return false;
        });

        $('#address-submit').click(function(){
            //send ajax request to save the address
            return false;
        });
    }

    if ($('.recipe-link').length > 0) {
        $('.recipe-link').click(function() {
            var $this   = $(this),
                $recipe = $this.siblings('.recipe-details');
            $recipe.slideToggle();
            if ($this.text().match('voir') != null) {
                $this.text($this.text().replace('voir', 'masquer'));
            } else {
                $this.text($this.text().replace('masquer', 'voir'));
            }
            return false;
        });
    }

    if($("#form-adress").length > 0){
        var field_require = $(this).find('input[data-required]');
        var field_phone = $(this).find('#phone');
        var field_mobile = $(this).find('#mobile');
        var field_code = $(this).find('#postal_code');
        veriField(field_phone);
        veriField(field_mobile);
        veriField(field_code);
        $("#form-adress").submit(function(e){
            var isRequiredEmpty = false;
            var isEmptyPhone = false;
            var isBadFormat = false;
            var html = "<p class=\"first\">Certains champs obligatoires n'ont pas été rentrés correctement :</p>";
            $(field_require).each(function(){
                if($.trim($(this).val()) == ""){
                    e.preventDefault();
                    isRequiredEmpty = true;
                    html += "<p>Tous les champs marqués d'un astérisque doivent être renseignés.</p>";
                    return false;
                }
            });
            if($.trim(field_phone.val()) == "" && $.trim(field_mobile.val()) == ""){
                e.preventDefault();
                isEmptyPhone = true;
                html += "<p>Vous devez renseigné au moins un numéro de teléphone.</p>";
            }
            if($.trim(field_code.val()).length != 5){
                e.preventDefault();
                var isBadFormat = true;
                html +="<p>Le format du code postal n'est pas correct.</p>";
            }
            if(isRequiredEmpty || isEmptyPhone || isBadFormat){
                html = "<div>"+html+"</div>";
                $("#subscription .address .warning").html(html);
            }
        });
    }

    if($('#form-contact').length > 0 || $('#form-witness').length > 0){
        var field_require = $(this).find('[data-required]');
        var field_emailAddress = $(this).find("#e-mail");
        $("#form-contact, #form-witness").submit(function(e){
            var isRequiredEmpty = false;
            var isInvalidEmail = false;
            var html = "<p class=\"first\">Certains champs obligatoires n'ont pas été rentrés correctement :</p>";
            $(field_require).each(function(){
                if($.trim($(this).val()) == ""){
                    e.preventDefault();
                    isRequiredEmpty = true;
                    html += "<p>Tous les champs marqués d'un astérisque doivent être renseignés.</p>";
                    return false;
                }
            });
            if($.trim(field_emailAddress.val()) != "" && !isValidEmailAddress($.trim(field_emailAddress.val()))){
                e.preventDefault();
                isInvalidEmail = true;
                html += "<p>Le format de l'adresse e-mail est invalide.</p>";
            }
            if(isRequiredEmpty || isInvalidEmail){
                html = "<div>"+html+"</div>";
                $("#static .warning").html(html).fadeIn();
                $("#guestbook .warning").html(html).fadeIn();
            }else if($('#form-witness').length > 0){
                //e.preventDefault();
                var message_success = '<div class="success"><p class="italic green message-success">Message envoyé !</p>';
                message_success += '<p class="thanks">Merci.</p>';
                message_success += '<p>Votre commentaire est soumis à approbation.</p>'
                message_success += '<p>Il peut y avoir un délais entre votre validation et son apparition sur le site.</p></div>';
                $("#guestbook #temoignage").html(message_success);
            }
        });
    }

    /* Permet de vérifier le format de l'adresse e-mail saisie */
    function isValidEmailAddress(emailAddress) {
        var email_regex = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i;
        var isValid = email_regex.test(emailAddress);
        return isValid;
    };

    /* Empeche de rentrer d'autre caractère que des chiffres dans un champs */
    function veriField(field)
    {
        var allow = false;
        var key;
        $(field).keypress(function(event){
            var key = event.keyCode ? event.keyCode : event.charCode;
            var allow;
            if((key != event.which) && event.which == 0){
                allow = true;
            }else{
                allow =false;
            }
            // permettre: backspace, delete, tab, escape, and enter
            if ( key == 46 || key == 8 || key == 9 || key == 27 || key == 13 ||
                // permettre: Ctrl+A
                (key == 65 && event.ctrlKey === true) ||
                // permettre : arrow left, arrow right
                ((key == 37 || key == 39) && allow==true)){
                // ok, on ne fait rien
                return;
            }
            else {
                if((key >=48 && key <=57) || (key >=96 && key <=105)){
                    var accept='0123456789';
                    var car= String.fromCharCode(key);
                    if(accept.indexOf(car) < 0) event.preventDefault();
                }else{
                    event.preventDefault();
                }
            }
        });
    }

    /* ------ Calendrier du checkout ----------*/
    /* Utilisation du plugin : glDatePicker.js (modifier pour s'adapter au projet) : http://glad.github.io/glDatePicker/ */
    //place_delivery: lieu de livraison de l'internaute
    //valeur possible: province, grande_banlieue, proche_banlieue, paris, point_relais

    var selectDays = [2,3,4,5];

    var tabHours = ['-'];



    if ($('#calendar').length > 0) {
        satSelectable = JSON.parse(satSelectable) || {};
        selectableDates = [];
        unselectableDates = [];
        for(year in satSelectable) {
            for (month in satSelectable[year]) {
                for (day in satSelectable[year][month]) {
                    if (satSelectable[year][month][day] == 1){
                        selectableDates.push({date: new Date(year, month-1, day)});
                    }
                    if (satSelectable[year][month][day] == 2){
                        unselectableDates.push({date: new Date(year, month-1, day)});
                    }
                }
            }
        }
        hStart = hStart.split(':');
        hStart = new Date(0,0,0,hStart[0],hStart[1]);
        hEnd = hEnd.split(':');
        hEnd = new Date(0,0,0,hEnd[0],hEnd[1]);

        var d = new Date(hStart.getTime())

        for (var d = new Date(hStart.getTime()); d <= hEnd; d = new Date(d.getTime() + tranche*60000)) {
            var min = d.getMinutes(),
                hours = d.getHours();
            if (min < 10) {
                min = "0"+min;
            };
            if (hours < 10) {
                hours = "0"+hours;
            };
            tabHours.push(hours+'h'+min);
        };
    }
    // console.log(hStart,hEnd)

//	 console.log(selectDays);
//         var tthamdi = [{ from: new Date(2014, 5, 1),to: new Date(2014, 5, 17) },];

    if($('input#mydate').length > 0){
        var selectableDayOfWeek = [2,3,4,5];
        if (place_delivery == 'ups'){
            selectableDayOfWeek = [3,4,5];
        }
        $('input#mydate').glDatePicker({
            showAlways: true,
            selectableDates: selectableDates,
            unselectableDates: unselectableDates,
            selectableDateRange: selectableDateRange,
            allowMonthSelect:false,
            allowYearSelect:false,
            specialDates: get_reserved_delivery_date_sync(), //@see js/get_reserved_delivery_date_sync.js
            prevArrow: '&lt;',
            nextArrow: '&gt;',
            selectableDOW: selectableDayOfWeek,
            selectableMonths: null,
            dowOffset:1,
            onSpecialDateClick: function(el, cell, date, data) {
                el.val(date.toLocaleDateString());

            },
            onClick: (function(el, cell, date, data) {
                date_delivery = date.getFullYear() + '-' +
                    (date.getMonth()+1) + '-' +
                    date.getDate();


                $('#date_delivery').val(date_delivery);

                var monthNames = [ 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' ];
                var month_value = date.getMonth();
                var day_value = date.getDate();
                var year_value = date.getFullYear();
                el.val(day_value+" "+monthNames[month_value]+" "+year_value);

                if (cell.hasClass('special'))
                    reserved_delivery_date_click_event(el, cell, date, data); //@see js/reserved_delivery_date_click_event.js

                //display error and disable submit button if cart has out of date product
                display_out_of_date_products_error(date_delivery, cell.hasClass('special'));


                //special dates doesnt need hours input to be setted, we can return here
                 if (cell.hasClass('special'))
                    return;


                if (!date_only && (!limitedDays || date.getDay() != 6)) { // province or saturday

                    var html = "";
                    var daynum = date.getDay();
                    // if(place_delivery=="paris" || place_delivery=="proche_banlieue"){
                    $("#selected-hours").removeClass().addClass("near");
                    $('.action button').attr('disabled', 'disabled');
                    html = '<div class="infos-hours"><p>Précisez le ou les créneau(x) horaires souhaité(s)</p>';
                    html += '<p>(avec au moins un créneau de '+(hours_interval/60)+'h ou plus)</p></div>';
                    html += '<div class="error"></div>';
                    for (j=0; j<4; j++){
                        if(j%2!=0){
                            var label = " et ";
                            var name = "end_delivery_hour_"+j;
                        }else{
                            var label = (j==0) ? "Entre ":"ou entre ";
                            var name = "start_delivery_hour_"+j;
                            html += '<div class="combobox combobox_'+j+'">';
                        }
                        html += '<label>'+label+'</label>';
                        html = combobox_delivery(tabHours, name, html);
                        if(j==3) html += '<span>(facultatif)</span>';
                        if(j%2!=0) html += '</div>';
                    }
//					html += '<p class="infos-preferences">Votre boucher est plutôt est du matin</p>';

                    //hide adjustment infos block if displayed
                    $('#selected-hours .adjustment_infos').addClass('hidden');
                    //Display hours selector
                    $("#selected-hours .hours").html(html);
                    $("#selected-hours .hours").removeClass('hidden');

                    drop_down_list_without_submit($(".delivery"));

                    /* ---------- Vérification du contenu des formulaires de date------------*/
                    /* Vérification du contenu du premier formulaire de date*/
                    var check_required_date_hours = function(){
                        var values = new  Array();
                        var i = 0;
                        $('.near .combobox_0').children('.sbHolder').children('.sbSelector').each(function(){
                            values[i] = $(this).text().split('h');
                            values[i][0] *= 60;
                            values[i] =  parseInt(values[i][0]) +  parseInt(values[i][1]);
                            i++;
                        });

                        //check interval for hours
                        if(values[1] - values[0] < hours_interval){
                            $('#date-livraison').find('.action button').attr('disabled', 'disabled');
                            $('#selected-hours').find('.error').html("L'interval entre les heures doit être de " + (hours_interval/60) + "h minimum.");
                        }else if(isNaN(values[1] - values[0])){
                            $('#selected-hours').find('.error').html("");
                            $('#date-livraison').find('.action button').attr('disabled', 'disabled');
                        }else{
                            $('#selected-hours').find('.error').html("");
                            $('#date-livraison').find('.action button').removeAttr('disabled');
                        }
                    };
                    /* Vérification du contenu du formulaire facultatif de date*/
                    var check_optional_date_hours = function(){
                        var values = new  Array();
                        var i = 0;
                        $('.near .combobox_2').children('.sbHolder').children('.sbSelector').each(function(){
                            values[i] = $(this).text().split('h');
                            values[i][0] *= 60;
                            values[i] =  parseInt(values[i][0]) +  parseInt(values[i][1]);
                            i++;
                        });

                        //check interval for hours
                        if(values[1] - values[0] < hours_interval){
                            $('#date-livraison').find('.action button').attr('disabled', 'disabled');
                            $('#selected-hours').find('.error').html("L'interval entre les horraires doit être de " + (hours_interval/60) + "h minimum.");
                        }else {
                            if(!isNaN(values[1] - values[0])){
                                check_required_date_hours();
                            }
                        }
                    }
                    /* Setting click event to check forms*/
                    $(".near .combobox_0 .sbOptions a").click(function(event){
                        check_required_date_hours();
                    });
                    $(".near .combobox_2 .sbOptions a").click(function(event){
                        check_optional_date_hours();
                    });
                    /* ---------- #END Vérification du contenu des formulaires de date------------*/
                }

                else if (date_only){
                    $('#date-livraison').find('.action button').removeAttr('disabled');
                }
            })
        });
    }

});

function combobox_delivery(tabHours, name, html){
    html += '<select name="'+name+'" class="delivery">';
    for(i=0; i<tabHours.length; i++){
        html += '<option value="'+tabHours[i]+'">'+tabHours[i]+'</option>';
    }
    html += '</select>';
    return html;
}

/* -------------- Fin calendrier ------------------*/

function drop_down_list_with_submit($el){
    $el.selectbox();
    $(".sbOptions a").click(function(){
        $('form').submit();
    });
}

function drop_down_list_without_submit($el){
    $el.selectbox();
}

/* Animation du sous-menu contenant les différentes catégories de recettes (Boeuf, Veau, Agneau, ...) */
function submenu(){
    var submenu = $('#category-leftcol .secondary-menu-item ul.submenu');
    submenu.children("li").each(function() {
        $(this).children('a').click(function(event) {
            if ($(this).parent().find('.hasSubCat').length > 0) {
                event.preventDefault();
                $('.submenu > li ul').stop(true, true).slideUp("fast");
                if ($(this).parent().find("ul").length > 0) {
                    $(this).parent().find("ul").stop(true, true).slideDown("fast");
                }
            }
        });
    });
}
