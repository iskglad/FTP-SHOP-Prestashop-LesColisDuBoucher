$(function(){
    //check valid carrier relay
    $("#submit-address").click(function(){
        //"livraison en point relais" checked
       if ($('.choose-relay_').parents('label.radio').hasClass('checked')){
           //no carrier relay choosen (description empty)
           if (!$('.choose-relay_').parents('li').find('.relay-name').length){
                alert('Veuillez choisir un lieu de retrait.');
               $('#button_choose_carrier_relay').click();
                return false;
           }
       }
       return true;
    });
});