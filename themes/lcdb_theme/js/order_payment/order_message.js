/**
 * Created by gladisk on 2/15/15.
 */
//=========================================================================================
//Update Order message in Current Customer Cart
//=========================================================================================
//@Ajax Function See:
//Function override/Controller/OrderController line 204

function errorUpdateOrderMessage(){
    alert("Erreur: Votre message n'a pas pu être joint. Veuillez renseigner un message");
}

function successUpdateOrderMessage(message){
    //Display message
    alert("Votre message a été joint à la commande.");

    //Change button text
    $("#buttonUpdateOrderMessage").html("Mettre à jour");

    //set message test
    $("#messageAttached span").html(message);
    $("#messageAttached").removeClass("hidden");

    //empty textarea
    $("#payment-message textarea").val("");
}

$(function(){
   $("#buttonUpdateOrderMessage").click(function(){

       //get message in TextArea
       var message = $("#payment-message textarea").val();
       console.log('===> Updating order message : ' + message);

       //Ajax
       //updateOrderMessage
       // @See Function override/Controller/OrderController line 204
       $.post( "", {"ajax" : 1, "method" : "updateOrderMessage", 'message' : message}, function( data ) {
           //Handle error
           if (data == "ko"){
               errorUpdateOrderMessage();
               return;
           }

        successUpdateOrderMessage(message);

       }).error(errorUpdateOrderMessage);
   });

});