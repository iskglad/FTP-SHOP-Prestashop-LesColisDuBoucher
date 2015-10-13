
{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='My vouchers'}{/capture}


<div id="columns" class="content clearfix">
	<div id="left_column">
		{include file="./account-left-col.tpl"}
	</div><!-- / #left_column -->
	<div id="center_column" class="reduction">
		<div class="big-bloc">
			<h1>Mes réductions</h1>
			<p>Vous trouverez ici tous vos codes de réductions.</p>
			{if isset($cart_rules) && count($cart_rules) && $nb_cart_rules}
				<div id="bloc-reduction">
					<p class="information">Vous disposez actuellement de <span class="bold">{$cart_rules|count} bon(s) de réduction</span></p>
					{foreach from=$cart_rules item=discountDetail name=myLoop}
						<div>
							<p class="bold code">
								{$discountDetail.code} 
								<span> ( 
									{if $discountDetail.id_discount_type == 1}
										- {$discountDetail.value|escape:'htmlall':'UTF-8'}%
									{elseif $discountDetail.id_discount_type == 2}
										- {convertPrice price=$discountDetail.value}
									{elseif $discountDetail.id_discount_type == 3}
										{l s='Free shipping'}
									{else}
										-
									{/if}
								 )</span>
							</p>
							<div>
								<p>{$discountDetail.name}</p>
							</div>
						</div>
					{/foreach}
				</div>
			{else}
				<div id="bloc-reduction" class="no-code">
					<p class="bold"><span>Vous ne disposez actuellement d’aucun code de réduction.</span></p>
				</div>
			{/if}
			<div id="get-reduction">
				<p class="bold title">Comment obtenir des codes de réductions ?</p>
				<p>Les bons de réductions sont accordés et envoyés par E-mail par les Colis du Boucher.</p>
				<p>Il s’agit principalement de bons de réduction récompensant votre fidélité ou bien d’avoirs émis suite 
				à l’annulation d’une commandes et au remboursement d’un ou plusieurs produits.</p>
			</div>
		</div>
	</div><!-- / #center_column -->
</div><!-- / .content -->