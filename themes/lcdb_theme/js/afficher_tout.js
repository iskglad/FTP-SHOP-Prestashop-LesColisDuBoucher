/*
* Action dans category-subcat.php, affiche masque les détails produits
*/

$('.voir_tout').on('click', function(){
    var classes_brut_subcat = $(this).attr('class');
    var classes_subcat = classes_brut_subcat.split(" ");
    var class_subcat = classes_subcat[1];
    var id_subcat = class_subcat.substr(4,2);
    var subcat = ".subcat";
    var class_subcat_final = subcat.concat(id_subcat);
    var classes_subcat_final = class_subcat_final + " .action-product";

    $(classes_subcat_final).css('display', 'block');
    $('.voir_tout').css('display', 'none');
    $('.masquer_tout').css('display', 'inline-block');
});
$('.masquer_tout').on('click', function(){
    var classes_brut_subcat = $(this).attr('class');
    var classes_subcat = classes_brut_subcat.split(" ");
    var class_subcat = classes_subcat[1];
    var id_subcat = class_subcat.substr(7,2);
    var subcat = ".subcat";
    var class_subcat_final = subcat.concat(id_subcat);
    var classes_subcat_final = class_subcat_final + " .action-product";

    $(classes_subcat_final).css('display', 'none');
    $('.voir_tout').css('display', 'inline-block');
    $('.masquer_tout').css('display', 'none');
});