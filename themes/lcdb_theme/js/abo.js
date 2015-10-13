$(document).ready(function() {

    var $price = $('#step1 .price'),
        porc = false,
        agneau = false,
        bio = false,
        facile = false;

    $('#composition-sans-porc').change(function(){
        if ($('#composition-sans-porc').is(':checked')) {
            $price.text(parseInt($price.text())+5);
            porc = true;
        } else if (porc) {
            $price.text(parseInt($price.text())-5);
        }
    });

    $('#composition-sans-agneau').change(function(){
        if ($('#composition-sans-agneau').is(':checked')) {
            $price.text(parseInt($price.text())+4);
            agneau = true;
        } else if (agneau) {
            $price.text(parseInt($price.text())-4);
        }
    });

    $('#composition-bio').change(function(){
        if ($('#composition-bio').is(':checked')) {
            $price.text(parseInt($price.text())+5);
            bio = true;
        } else if (bio) {
            $price.text(parseInt($price.text())-5);
        }
    });

    $('#composition-cuisine-facile').change(function(){
        if ($('#composition-cuisine-facile').is(':checked')) {
            $price.text(parseInt($price.text())+5);
            facile = true;
        } else if (facile) {
            $price.text(parseInt($price.text())-5);
        }
    });

});
