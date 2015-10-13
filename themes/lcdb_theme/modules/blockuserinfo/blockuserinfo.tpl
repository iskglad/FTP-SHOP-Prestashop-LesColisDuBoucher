{if $firstName != ''}<span class="greeting">Bienvenue {$firstName} </span>{/if}
<div class="register-basket clearfix">
	<div class="user-block">
		<div id="connection-register">
			{if !$logged}
				<a href="{$link->getPageLink('my-account', true)}" title="se connecter">Connexion</a> / 
				<a href="{$link->getPageLink('authentication', true)}?create_account=true" title="s'inscrire">Inscription</a>
			{else}
				<a href="{$link->getPageLink('my-account', true)}" title="mon compte">Mon compte</a>
			{/if}
		</div>
		<div id="basket">
			<span class="illustration"></span>
			<p><a href="{$link->getPageLink('order', true)}">Panier (
				<span class="price ajax_cart_total{if $cart_qties == 0}{/if}">
					{if $cart_qties > 0}
						{if $priceDisplay == 1}
							{assign var='blockuser_cart_flag' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
							{convertPrice price=$cart->getOrderTotal(false, $blockuser_cart_flag)}
						{else}
							{assign var='blockuser_cart_flag' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
							{convertPrice price=$cart->getOrderTotal(true, $blockuser_cart_flag)}
						{/if}
					{else}
						vide
					{/if}
				</span>
			)</a></p>
		</div>
	</div>
</div>
