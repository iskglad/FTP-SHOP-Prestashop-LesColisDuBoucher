{assign var='current_step' value='shipping'}
<div id="columns" class="content clearfix">
{assign var=calendarJson value=$calendar|json_decode:1}
<script>
	var place_delivery = "{$name}";
	var satSelectable = '{$calendar}';
	var horaire = '{$horaire}';
	var hours_interval = '{$creneau}';
	var tranche = '{$tranche}';
	var hStart = '{$h_start}';
	var hEnd = '{$h_end}';
	var date_only = {if $date_only}true{else}false{/if};
	var limitedDays = {if $limitedDays}true{else}false{/if};
    var cart_rules = {$cart_rules_json};

    var selectableDateRange = {$selectableDateRange};
	$(function() {
		$('form').on('submit', function(e) {
			var horaire = 'Entre '+$('[name=start_delivery_hour_0]',this).val()+' et '+$('[name=end_delivery_hour_1]',this).val();
			if ($('[name=start_delivery_hour_2]',this).val() != '-' && $('[name=end_delivery_hour_3]',this).val() != '-') {
				horaire+= ' ou entre '+$('[name=start_delivery_hour_2]',this).val()+' et '+$('[name=end_delivery_hour_3]',this).val();
			};
			$('#hour_delivery').val(horaire);
		});
	})
</script>
	<div class="bloc-checkout">
			{include file="./order-steps.tpl"}
		<div class="content-checkout">
			<h1>{l s='Shopping cart summary'}</h1>
			<div class="content-checkout">
				<h1>Date de livraison</h1>
				<div class="bloc-time">
					<form action="{$link->getPageLink('order', true, NULL)}" method="post" id="date-livraison">
                        <p>Choisissez votre date de livraison:</p>
                        <div id="selected-date-hours">
							<div id="selected-date">
								<p>Date sélectionnée :</p>
								<input type="text" name="mydate" id="mydate" gldp-id="mydate" value="-" readonly />
							</div>
                            <!--Info deleting reduction-->
                            <div id="info_cart_rule_deleted" class="success hidden">
                                <p>La réduction a été retirée du panier. Votre commande est de <b><span id="new_order_total">54.00</span> euros</b></p>
                            </div>
                            <!--Warning-->
                            <div id='warning_out_of_cart_rule_date' class="hidden">
                                <div class="warning">
                                    <p>La date choisie est hors de la limite d'une de vos réductions</p>
                                </div>
                                <div class="cart_rule_details">
                                    <p>
                                        <b class="cart_rule_name"></b>
                                        (<span class="cart_rule_action"></span>)<br/>
                                        Livraison entre le <span class="cart_rule_from"></span>
                                        et le <span class="cart_rule_to"></span><br/>
                                    </p>
                                    <p>
                                        Veuillez choisir une autre date ou <br/><a id="delete_promo" id-cart-rule="">retirer la réduction</a>.
                                    </p>
                                    <i id="deleting_promo_loading_msg" class="hidden">Suppression de la reduction...</i>
                                    <i id="deleting_promo_error" class="hidden">La réduction ne peut être supprimée</i>
                                </div>
                            </div>
                            <!--Hours infos-->
							<div id="selected-hours">
                                <!--Filled in -->
                                {if $carrier_name && $carrier_description}
                                    <div class="relay_infos">
                                        <p><strong>{$carrier_name}</strong></p>
                                        <p>{$carrier_description}</p>
                                    </div>
                                {/if}
                                <div class="hours hidden"></div> <!--@Filled in main.js line 610-->
                                <div class="adjustment_infos hidden">
                                    <p>Une commande est déjâ enregistrer pour cette date.<p>
                                    <p>Commande #<span class="id_order"></span><p>
                                    <p class="delivery_hours">Entre 10h00 et 12h00<p>
                                    <p class="delivery_infos">
                                        Cp <span class="delivery_postcode"></span> -
                                        <span class="carrier_name"></span>
                                    </p>
                                    <a base_url="{$link->getBaseLink()}" href="" class="green-button adjustment_link">
                                        Ajuster cette commande
                                    </a>
                                </div>
							</div>
						</div>
						<div id="calendar">
							<div gldp-el="mydate" style="width:515px; height:300px;" id="block-calendar" class="clearfix"></div>
							<div id="legend">
								<ul>
									<li class="impossible"><span></span><span>Livraison impossible</span></li>
									<li class="possible"><span></span><span>Livraison possible</span></li>
									<li class="already-rec"><span></span><span>Livraison déjà enregistrée</span></li>
								</ul>
							</div>
						</div>
                        <!--Warning product out of date-->
                        <div id='warning_out_of_date_products' class="warning hidden">
                            <p>
                                Les produit suivants ne seront plus disponibles à la date choisie: <br/>
                                <span class="products_name"></span>.<br/><br/>
                                Veuillez choisir une autre date ou retirer ces produits de la carte.
                            </p>
                        </div>
						<div class="action">

							<input type="hidden" name="step" value="3" />
							<input type="hidden" name="back" value="{$back}" />
                            <script>
                                // Force la saisie de l'heure
                                var auto_refresh = setInterval(
                                        function ()
                                        {
                                            selector = $(".sbSelector");
                                            selector0 = selector[0];
                                            selector1 = selector[1];
                                            if($(".relay_infos").html() == null) {
                                                if ($(selector0).html() != "-" && $(selector1).html() != "-" && $(".error").html() == ""
                                                        && $('#warning_out_of_cart_rule_date').hasClass('hidden')
                                                        && $('#warning_out_of_date_products').hasClass('hidden'))
                                                {
                                                    console.log("pas disabled");
                                                    $('#validate_order').prop("disabled", false);
                                                }
                                                else
                                                {
                                                    $('#validate_order').prop("disabled", true);
                                                    console.log("disabled");
                                                }
                                            }
                                        }, 100);
                            </script>
                            <p style="text-align: left; height: 50px;">
							    <button class='green-button validate_order' id='validate_order' name="processCarrier" type="submit" disabled>Valider ma date de livraison</button>
                            </p>
                            <p style="text-align: right;">
                                <a class='red-button continue_shopping' href="{$link->getPageLink('order', false)}" title="Continuer mes achats">
                                    Continuer mes achats
                                </a>
                            </p>
                            <input type="hidden" value="" id="hour_delivery" name="hour_delivery">
							<input type="hidden" value="" id="date_delivery" name="date_delivery">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
