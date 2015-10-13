/**
 * Created by gladisk on 12/1/14.
 */
$(function(){
    //hide second product link
    $('.infos .product-link:odd').toggle();

    //keep link text in Tmp (accordion button)
    $('.action-product').slideUp(0);

    //bind click event
    $('.infos').click(function(e) {
        //Close all <div> but the <div> right after the clicked <a>
        //$(e.target).next('div').siblings('div').slideUp();
        //Toggle open/close on the <div> after the <a>, opening it if not open.
        //$(e.target).next('div').slideToggle();
        var link_text = $(this).find('.product-link').html();

        $(this).find('.product-link').toggle();
        $(this).next('.action-product')
            .slideToggle(500, "easeOutExpo");
        return false;
    });

});