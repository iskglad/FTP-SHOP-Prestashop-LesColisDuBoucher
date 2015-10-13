{*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*

*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}



{assign var='label_bio' value=""}
{assign var='label_rouge' value=""}
{foreach $product.features as $feature}
    {if ($feature.id_feature == $id_feature_label_bio) and ($feature.value|lower == "oui")}
        {assign var='label_bio' value="label-bio"}
    {/if}

    {if ($feature.id_feature == $id_feature_label_rouge) and ($feature.value|lower == "oui")}
        {assign var='label_rouge' value="label-rouge"}
    {/if}
{/foreach}

<tr id="product_{$product.id_product}_{$product.id_product_attribute}_0_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}" class="row {if isset($productLast) && $productLast && (!isset($ignoreProductLast) || !$ignoreProductLast)}last_item{elseif isset($productFirst) && $productFirst}first_item{/if} {if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}alternate_item{/if} cart_item address_{$product.id_address_delivery|intval} {if $odd}odd{else}even{/if}">
	<td class="label cart_product first">
        <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'htmlall':'UTF-8'}">
			<div>
                <!--Label/Colis image-->
                {$images = [
                    'label' => [
                        'selection'     =>  'les_colis_du_boucher_logo_2.png',
                        'salers'     =>  'les_colis_du_boucher_logo_2.png',
                        'bio'           =>  'logo-bio-simple-wide.png',
                        'label rouge'   =>  'label_rouge_logo_full.png',
                        'Le Bourdonnec'   =>  'product_le-bourdonnec.png'
                    ],
                    'colis' => [
                        '100% bio '  => 'colis-surprise-vert-full.png',
                        'Label rouge et bio' => 'colis-surprise.png',
                        'colis sans porc ' => 'colis-surprise.png'
                    ]
                ]}
                {if $product.label_name}
                    <img class='logo label' src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/{$images['label'][{$product.label_name}]}">
                {else}
                    <img class='logo colis' src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/{$images['colis'][{$product.colis_name}]}">
                {/if}

				<span class="product-title">
                     {if $product.isPro}
                         {"PRO "}
                     {/if}
                    {$product.name|escape:'htmlall':'UTF-8'}
                </span>
				<br/>
				<span class="product-details">
                    {if $product.colis_name}
                        {$product.colis_name|escape:'UTF-8'}
                    {/if}
                    {$product.description_short|escape:'UTF-8'}
                </span>
                {if $product.isPromo}
                    <span class="product-details promo">Promo</span>
                {/if}
			</div>

			{$now = $smarty.now|date_format:"%Y-%m-%d"}
			{$start = $product.date_start|date_format:"%Y-%m-%d"}
			{$end = $product.date_end|date_format:"%Y-%m-%d"}
                        <!--
			{if ($product.unusual_product)}
				<span class="product-rare">Produit rare : indisponibilité à prévoir.</span>
            {else}
                {if $product.quantity < 5 and $product.quantity > 0}
                    <span class="product-availability">Plus que {$product.quantity} produits restants.</span>
                {/if}
                {if $product.quantity == 0}
                    <span class="product-availability">Produits indisponible.</span>
                {/if}
                {if $product.quantity < 5 and $product.limit_date}<br/>{/if}
                {if $product.limit_date && isset($product.date_end) && $product.date_end > $now}
                    <span class="product-availability">Livrable jusqu'au {$product.date_end|date_format:"%d/%m/%Y"}</span>
                {/if}
			{/if}
                        -->
		</a>
	</td>
	<td class="cart_unit">
		<span class="price" id="product_price_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
			{if !empty($product.gift)}
				<span class="gift-icon">{l s='Gift!'}</span>
			{else}
				{if isset($product.is_discounted) && $product.is_discounted}
					<span style="text-decoration:line-through;">{convertPrice price=$product.price_without_specific_price}</span><br />
				{/if}
				{if !$priceDisplay}
					{convertPrice price=$product.price_wt}
				{else}
					{convertPrice price=$product.price}
				{/if}
			{/if}
		</span>
	</td>
	<td class="cart_quantity"{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0} style="text-align: center;"{/if}>
		{if isset($cannotModify) AND $cannotModify == 1}
			<span>
				{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}
				{else}
					{$product.cart_quantity-$quantityDisplayed}
				{/if}
			</span>
		{else}
			{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}
				<span id="cart_quantity_custom_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval}" >{$product.customizationQuantityTotal}</span>
			{/if}
			{if !isset($customizedDatas.$productId.$productAttributeId) OR $quantityDisplayed > 0}
				<div class="cart_quantity_button">
				{if ($product.minimal_quantity < ($product.cart_quantity-$quantityDisplayed) OR $product.minimal_quantity <= 1)}
				<a rel="nofollow" class="cart_quantity_down moreless minus" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_0_{$product.id_address_delivery|intval}" href="{$link->getPageLink('cart', true, NULL, "add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery|intval}&amp;op=down&amp;token={$token_cart}")}" title="{l s='Subtract'}">
					-
				</a>
				{else}
				<a class="cart_quantity_down moreless minus nov" style="opacity: 0.3;" href="#" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_0_{$product.id_address_delivery|intval}" title="{l s='You must purchase a minimum of %d of this product.' sprintf=$product.minimal_quantity}">
					-
				</a>
				{/if}
				<input type="hidden" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_0_{$product.id_address_delivery|intval}_hidden" />
				<input size="2" type="text" autocomplete="off" class="quantity cart_quantity_input" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}"  name="quantity_{$product.id_product}_{$product.id_product_attribute}_0_{$product.id_address_delivery|intval}" />
                <a href='#' rel="nofollow" class="cart_quantity_up moreless minus" id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}_0_{$product.id_address_delivery|intval}" title="{l s='Add'}">
                        +
                </a>
				</div>
			{/if}
		{/if}
	</td>
	<td class="cart_total">
		<span class="price" id="total_product_price_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval}{if !empty($product.gift)}_gift{/if}">
			{if !empty($product.gift)}
				<span class="gift-icon">{l s='Gift!'}</span>
			{else}
				{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
					{if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
				{else}
					{if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
				{/if}
			{/if}
		</span>
	</td>
	{if !isset($noDeleteButton) || !$noDeleteButton}
		<td class="cart_delete">
		{if (!isset($customizedDatas.$productId.$productAttributeId) OR $quantityDisplayed) > 0 && empty($product.gift)}
			<div>
				<a rel="nofollow" class="cart_quantity_delete" id="{$product.id_product}_{$product.id_product_attribute}_0_{$product.id_address_delivery|intval}" href="{$link->getPageLink('cart', true, NULL, "delete=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery|intval}&amp;token={$token_cart}")}">{l s='Delete'}</a>
			</div>
		{/if}
		</td>
	{/if}
</tr>
