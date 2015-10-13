
<div class="big-bloc colis" itemscope itemtype="http://schema.org/Product">
	<div class="content-title">
		<h1 itemprop="name">{$product->name}</h1>
		{if $product->breeder != null}	
			<p>{$product->breeder}</p>
		{/if}
	</div>
	
	{if isset($confirmation) && $confirmation}
		<p class="confirmation">{$confirmation}</p>
	{/if}
	
	{if isset($adminActionDisplay) && $adminActionDisplay}
	<div id="admin-action">
		<p>{l s='This product is not visible to your customers.'}
		<input type="hidden" id="admin-action-product-id" value="{$product->id}" />
		<input type="submit" value="{l s='Publish'}" class="exclusive" onclick="submitPublishProduct('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 0, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
		<input type="submit" value="{l s='Back'}" class="exclusive" onclick="submitPublishProduct('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 1, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
		</p>
		<p id="admin-action-result"></p>
		</p>
	</div>
	{/if}
	<hr />
	<div class="price-infos clearfix" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<img src="{$img_dir}asset/img_solo/colis-cadeau.png" title="colis cadeau"/>
		<div class="add-to-basket-form">
			<div class="price-details">
				
				{if !$priceDisplay || $priceDisplay == 2}
					{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, $priceDisplayPrecision)}
					{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
				{elseif $priceDisplay == 1}
					{assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, $priceDisplayPrecision)}
					{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
				{/if}
				
				<div class="detailed-price">
					{if $priceDisplay >= 0 && $priceDisplay <= 2}
						<span id="our_price_display" class="price" itemprop="price">{convertPrice price=$productPrice}</span>
					{/if}
				</div>
			</div>
			
			<form class="form-panier clearfix" action="{$link->getPageLink('cart')}" method="post">
				<!-- input hidden -->
				<input type="hidden" name="token" value="{$static_token}" />
				<input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
				<input type="hidden" name="add" value="1" />
				<input type="hidden" name="id_product_attribute" id="idCombination" value="" />
				<!-- select -->
				<button type="button" name="minus" class="moreless minus">-</button>
				<input class="quantity" type="text" maxlength="2" value="0" name="qty" id="quantity_wanted" disabled>
				<button type="button" name="plus" class="moreless plus">+</button>
				<!-- button -->
				<button type="submit" name="submit" class="ajout-panier green-button gradient">ajouter au panier</button>
			</form>
		</div><!-- / .add-to-basket-form -->
	</div><!-- / .price-infos -->
	<hr />
	<div class="misc-infos clearfix">
		<p class="portions"><span class="img-portions"></span> 10 à 12 <span class="colis-portions">portions</span></p>
		<p class="jours"><span class="img-jours"></span> 7 à 14 <span class="colis-jours">jours</span></p>
	</div>
	{if $packItems|@count > 0}
		<div class="colis-composition">
			<p class="green-title">La composition du colis pré-composé du mois</p>
			<p itemprop="description">
				{foreach from=$packItems item=packItem}
					<span style="display:block;">{$packItem.name|escape:'htmlall':'UTF-8'}</span> 
				{/foreach}
			</p>
		</div>
	{/if}
</div><!-- / .colis -->
