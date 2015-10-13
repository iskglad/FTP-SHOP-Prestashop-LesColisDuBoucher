
{*************************************************************************************************************************************}
{* IMPORTANT : If you change some data here, you have to report these changes in the ./blockcart-json.js (to let ajaxCart available) *}
{*************************************************************************************************************************************}
{if $ajax_allowed}
<script type="text/javascript">
var CUSTOMIZE_TEXTFIELD = {$CUSTOMIZE_TEXTFIELD};
var img_dir = '{$img_dir}';
</script>
{/if}
<script type="text/javascript">
var customizationIdMessage = '{l s='Customization #' mod='blockcart' js=1}';
var removingLinkText = '{l s='remove this product from my cart' mod='blockcart' js=1}';
var freeShippingTranslation = '{l s='Free shipping!' mod='blockcart' js=1}';
var freeProductTranslation = '{l s='Free!' mod='blockcart' js=1}';
var delete_txt = '{l s='Delete' mod='blockcart' js=1}';
</script>

<div class="small-bloc bloc-basket first-bloc">
	<span class="bloc-title ribbon-votre-panier"></span>
	<!-- MODULE Block cart -->
	<div id="cart_block" class="block exclusive">
		<div class="block_content">
			<!-- block summary -->
			<div id="cart_block_summary" class="{if isset($colapseExpandStatus) && $colapseExpandStatus eq 'expanded' || !$ajax_allowed || !isset($colapseExpandStatus)}collapsed{else}expanded{/if}">
				<span class="ajax_cart_quantity" {if $cart_qties <= 0}style="display:none;"{/if}>{$cart_qties}</span>
				<span class="ajax_cart_product_txt_s" {if $cart_qties <= 1}style="display:none"{/if}>{l s='products' mod='blockcart'}</span>
				<span class="ajax_cart_product_txt" {if $cart_qties > 1}style="display:none"{/if}>{l s='product' mod='blockcart'}</span>
				<span class="ajax_cart_total" {if $cart_qties == 0}style="display:none"{/if}>
					{if $cart_qties > 0}
						{if $priceDisplay == 1}
							{convertPrice price=$cart->getOrderTotal(false)}
						{else}
							{convertPrice price=$cart->getOrderTotal(true)}
						{/if}
					{/if}
				</span>
				<span class="ajax_cart_no_product" {if $cart_qties != 0}style="display:none"{/if}>{l s='(empty)' mod='blockcart'}</span>
			</div>
			<!-- block list of products -->
			<div id="cart_block_list" class="{if isset($colapseExpandStatus) && $colapseExpandStatus eq 'expanded' || !$ajax_allowed || !isset($colapseExpandStatus)}expanded{else}collapsed{/if}">
			{if $products}
				<table>
				    <thead>
				        <tr>
				            <th>Qt&eacute;</th>
				            <th>Produit</th>
				            <th class="price">Prix</th>
				        </tr>
				    </thead>
				    <tbody>
				    	{foreach from=$products item='product' name='myLoop'}
				    		{assign var='productId' value=$product.id_product}
				    		{assign var='productAttributeId' value=$product.id_product_attribute}

				    		<tr id="cart_block_product_{$product.id_product}_{if $product.id_product_attribute}{$product.id_product_attribute}{else}0{/if}_{if $product.id_address_delivery}{$product.id_address_delivery}{else}0{/if}" class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">
				    		    <td>{$product.cart_quantity}</td>
				    		    <td title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:13:'...':true|escape:html:'UTF-8'}</td>
				    		    <td class="price">{if $product.total > 0}
				    					{if $priceDisplay == $smarty.const.PS_TAX_EXC}{displayWtPrice p="`$product.total`"}{else}{displayWtPrice p="`$product.total_wt`"}{/if}
				    				{else}
				    					<b>{l s='Free!' mod='blockcart'}</b>
				    				{/if}
				    			</td>
				    		</tr>
				    	{/foreach}
				    </tbody>
				    <tfoot>
				        <tr><td colspan="3">Sous total : <span class="basket-bold">{$product_total}</span></td></tr>
				    </tfoot>
				</table>
				<dl class="products">
				
				</dl>
			{/if}
				<p {if $products}class="hidden"{/if} id="cart_block_no_products">{l s='No products' mod='blockcart'}</p>

				<div id="cart-buttons">
					<div class="basket-links"><a href="{$link->getPageLink("$order_process", true)}" title="{l s='Check out' mod='blockcart'}" class="green-button gradient">Passer ma commande</a></div>
				</div>
			</div>
		</div>
	</div>
	<!-- /MODULE Block cart -->
</div>

