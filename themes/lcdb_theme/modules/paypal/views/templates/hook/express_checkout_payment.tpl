<div id="paypal">
	<!-- <label class="radio" for="acc-paypal">
		<input type="radio" id="acc-paypal" name="payment" value="paypal"/> Paypal
	</label> -->
	<p>Paiement en ligne sécurisé à l'aide du système Paypal en protocol SSL.<p>
	<p>Paypal étant un outil de paiement payant, <span class="bold">un surcoût de 3%</span>
	du montant total de votre commande sera ajouté pour l'utilisation de ce mode de paiement.
	Le montant final que vous aurez alors à payer sur Paypal sera donc de 
	<span id="total-paypal" class="bold">244,63€</span>.</p>
	<p>Nous vous remercions par avance de votre compréhension</p>
	<a href="javascript:void(0)" onclick="$('#paypal_payment_form').submit();" id="paypal_process_payment" rel="nofollow">accéder</a>
</div>

<form id="paypal_payment_form" action="{$base_dir_ssl}modules/paypal/express_checkout/payment.php" data-ajax="false" title="{l s='Pay with PayPal' mod='paypal'}" method="post">
	<input type="hidden" name="express_checkout" value="{$PayPal_payment_type|escape:'htmlall':'UTF-8'}"/>
	<input type="hidden" name="current_shop_url" value="{$PayPal_current_page|escape:'htmlall':'UTF-8'}" />
</form>