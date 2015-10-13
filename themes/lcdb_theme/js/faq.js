$(function ($) {

	/*page FAQ*/
	$(window).load(function() {
	
		/*clic sur un lien"*/
		$(".liste_FAQ li a").click(function(e) {
			e.preventDefault();
			var $this = $(this);
			if(!$this.parent().hasClass("FAQ_open")) {
				$this.parent().find(".content").css("display", "block");
				$this.parent().addClass("FAQ_open");
			} else {
				$this.parent().find(".content").css("display", "none");
				$this.parent().removeClass("FAQ_open");
			}
			return false;
		});
		
	});
	
});
