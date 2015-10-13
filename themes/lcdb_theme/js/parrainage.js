$(function ($) {

	/*menu parainnage*/
	$(window).load(function() {
	
		//ajuste la hauteur au départ
		$(".conteneur_blocs_parrainge").css("height", $(".parrainage_bloc_first_li").height());
		
		/*clic sur un onglet"*/
		$(".menu_parrainage li").click(function(){
			if(!$(this).hasClass("parrainage_active"))
			{
				//trouve la classe active
				$(".menu_parrainage li").each(function()
				{
					if($(this).hasClass("parrainage_active"))
					{
						$(this).removeClass("parrainage_active");
						$(this).css("background", "none");
						$(this).css("color", "#b1a9a1");
						$(".parrainage_bloc_"+$(this).attr('id')).css("display", "none");
						return false;
					}
				});
				
				$(".parrainage_bloc_"+$(this).attr('id')).css("display", "block");
				$(".conteneur_blocs_parrainge").css("height", $(".parrainage_bloc_"+$(this).attr('id')).height());
				$(this).addClass("parrainage_active");
				$(this).css("background", "#4d9810");
				$(this).css("color", "#ffffff");
			}
		});
		
		
		
		
	});
	
});
