-{capture name=path}{l s='Your shopping cart'}{/capture}
<script>
    var relays = {$relays};
    var img_folder = '{$img_dir}';
</script>
<div id="columns" class="content clearfix">
<div id="left_column">
    {include file="$tpl_dir./category-leftcol.tpl"}
</div>
<div id="center_column">
<div class="big-bloc">
<div class="clearfix cart-title">
    {if $is_adjustment}
        <h1>Mon panier d'ajustement</h1>
        <a href="{$link->getBaseLink()}index.php?controller=order&disable_adjust=1" class="cancel-link">
            <img class="cross-img" src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/cross.png"/>
            Annuler l'ajustement
        </a>
    {else}
        <h1>Mon panier</h1>
    {/if}
    <hr class="dashed" />
</div>
{if isset($account_created)}
    <p class="success">
        {l s='Your account has been created.'}
    </p>
{/if}
{include file="$tpl_dir./errors.tpl"}

{if isset($empty)}
    {if $is_adjustment}
        <p class="warning">Merci de choisir vos produits à ajouter à votre commande initiale.</p>
    {else}
        <p class="warning">{l s='Your shopping cart is empty.'}</p>
    {/if}
    <a href="{$link->getCategoryLink(3)|escape:'htmlall':'UTF-8'}" id="empty-basket-command-link" title="commander nos viandes">
        <span class="red-button">Commander nos viandes</span>
    </a>
{elseif $PS_CATALOG_MODE}
    <p class="warning">{l s='This store has not accepted your new order.'}</p>
{else}
    {include file="$tpl_dir./errors.tpl"}

    {if isset($empty)}
        <p class="warning">{l s='Your shopping cart is empty.'}</p>
    {elseif $PS_CATALOG_MODE}
        <p class="warning">{l s='This store has not accepted your new order.'}</p>
    {else}
        <script type="text/javascript">
            // <![CDATA[
            var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
            var currencyRate = '{$currencyRate|floatval}';
            var currencyFormat = '{$currencyFormat|intval}';
            var currencyBlank = '{$currencyBlank|intval}';
            var txtProduct = "{l s='product' js=1}";
            var txtProducts = "{l s='products' js=1}";
            var deliveryAddress = {$cart->id_address_delivery|intval};
            // ]]>
        </script>
        <p style="display:none" id="emptyCartWarning" class="warning">{l s='Your shopping cart is empty.'}</p>
    {/if}
    <table class="form-panier">
        <thead>
        <tr>
            <th>Produit</th>
            <th>Prix unitaire</th>
            <th>Quantité</th>
            <th>Prix Total TTC</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $products as $product}
            {assign var='productId' value=$product.id_product}
            {assign var='productAttributeId' value=$product.id_product_attribute}
            {assign var='quantityDisplayed' value=0}
            {assign var='odd' value=$product@iteration%2}
            {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
            <!-- {* Display the product line *} -->
            {include file="./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
        {/foreach}
        {assign var='last_was_odd' value=$product@iteration%2}
        {foreach $gift_products as $product}
            {assign var='productId' value=$product.id_product}
            {assign var='productAttributeId' value=$product.id_product_attribute}
            {assign var='quantityDisplayed' value=0}
            {assign var='odd' value=($product@iteration+$last_was_odd)%2}
            {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
            {assign var='cannotModify' value=1}
            {* Display the gift product line *}
            {include file="./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
        {/foreach}

        </tbody>




        {if $priceDisplay}
            <tfoot>
            <tr>
                <td class="first"></td>
                <td colspan="2">{if $display_tax_label}{l s='Total products (tax excl.):'}{else}{l s='Total products:'}{/if}</td>
                <td><span id="total_price">{displayPrice price=$total_products}</span></td>
            </tr>
            </tfoot>
        {else}
            <tfoot>
            {if $total_shipping_tax_exc <= 0 && !isset($virtualCart)}
                <!--We no more need to check shipping here (done on next page)
				<tr class="cart_total_delivery hidden" style="">
                       <td class="first"></td>
					<td colspan="2">{l s='Frais de livraisons'}</td>
					<td  class="price" id="total_shipping">{displayPrice price=0} </td>
				</tr>-->
            {else}
                {if $use_taxes}
                    {if $priceDisplay}
                        <tr class="cart_total_delivery hidden">
                            <td class="first"></td>
                            <td colspan="2">{if $display_tax_label}{l s='Frais de livraisons HT'}{else}{l s='Frais de livraisons'}{/if}</td>
                            <td  class="price" id="total_shipping">{displayPrice price=$total_shipping_tax_exc}</td>
                        </tr>
                    {else}
                        <tr class="cart_total_delivery hidden">
                            <td class="first"></td>
                            <td colspan="2">{if $display_tax_label}{l s='Frais de livraisons  TTC'}{else}{l s='Frais de livraisons'}{/if}</td>
                            <td  class="price" id="total_shipping" >{displayPrice price=$total_shipping}</td>
                        </tr>
                    {/if}
                {else}
                    <tr class="cart_total_delivery hidden"{if $total_shipping_tax_exc <= 0} style="display:none;"{/if}>
                        <td class="first"></td>
                        <td colspan="2">{l s='Total shipping'}</td>
                        <td  class="price" id="total_shipping" >{displayPrice price=$total_shipping_tax_exc}</td>
                    </tr>
                {/if}
            {/if}
            <tr>
                <td class="first"></td>
                <td colspan="2">{if $display_tax_label}{l s='Total products (tax incl.):'}{else}{l s='Total products:'}{/if}</td>
                <td><span id="total_price">{displayPrice price=$total_products_wt}</span></td>
            </tr>
            </tfoot>
        {/if}

    </table>
    <!-- =========================/*Reduction==========================-->
    <div id="bloc-reduction">
    <!--td colspan="5" id="cart_voucher" class="cart_voucher"-->
    {if $voucherAllowed}
        <div id="cart_voucher" class="table_block">
        {if isset($errors_discount) && $errors_discount}
            <ul class="error">
                {foreach from=$errors_discount key=k item=error}
                    <li>{$error|escape:'htmlall':'UTF-8'}</li>
                {/foreach}
            </ul>
        {/if}
        {if $voucherAllowed}
            <form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">

                <label for="discount_name">{l s='Vouchers'}</label>
                <input type="text" class="discount_name" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
                <input type="hidden" name="submitDiscount" /><input type="submit" name="submitAddDiscount" value="{l s='OK'}" class="button" style="background-color: #e0ceba;border: 1px solid #fff"/>

            </form>
            {if $displayVouchers}
                <p id="title" class="title_offers">{l s='Take advantage of our offers:'}</p>
                <div id="display_cart_vouchers">
                    {foreach from=$displayVouchers item=voucher}
                        {if $voucher.code != ''}<span onclick="$('#discount_name').val('{$voucher.code}');return false;" class="voucher_name">{$voucher.code}</span> - {/if}{$voucher.name}<br />
                    {/foreach}
                </div>
            {/if}
        {/if}

        {if count($discounts)}
            <form>
                {foreach from=$discounts item=discount name=discountLoop}
                    <span>
                      <label>
                          <!--<span class="code bold">{$discount.name}:</span>-->
                          <span class="cart_discount_description code bold">{$discount.description}</span>
                           <span class="price-discount amount bold" style="color: #4D9810;">
						        {if $discount.value_real > 0}
                                    {if !$priceDisplay}
                                        ({displayPrice price=$discount.value_real*-1})
                                    {else}
                                        ({displayPrice price=$discount.value_tax_exc*-1})
                                    {/if}
                                {/if}
					        </span>
                            <!--<span>
                                {if strlen($discount.code)}
                                    <a href="{if $opc}{$link->getPageLink('order-opc', true)}
                                {else}
                                    {$link->getPageLink('order', true)}

                                {/if}
                                ?deleteDiscount={$discount.id_discount}" class="price_discount_delete" title="{l s='Delete'}">{l s='Delete'}</a>{/if}
                            </span>-->
                      </label>
                   </span>
                {/foreach}
            </form>
            </div>
        {/if}
        </div>
    {/if}


    <!-- =========================End Reduction*/======================-->


    <!-- =========================/*Adjustment INFO==========================-->
    {if $is_adjustment}
        {if $adjust_price_to_free_shipping > 0}

            <p class="info">
                <img class="info-img" src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/infos.png"/>
                <span class="message">
                    Frais de livraison de la commande initial
                    <strong>
                        remboursés à partir de
                        {displayPrice price= $adjust_price_to_free_shipping}
                        euros
                    </strong>
                    d'adjustement !
                </span>
            </p>
        {/if}
    {/if}
    <!-- =========================/*END Adjustment INFO==========================-->

    <div class="clearfix" id="page-buttons">

        {if !$opc}

            <!--Assign next step Value-->
            {assign var="next_step" value="1"}
            {if $is_adjustment}
                {assign var="next_step" value="3"}
            {/if}

            <!--Assign next step link params-->
            {assign var="next_step_link_params" value="step={$next_step}"}
            {if $back}
                {assign var="next_step_link_params" value="step={$next_step}&amp;back={$back}"}
            {/if}

            <!--Assign next step link-->
            {assign var="next_step_link" value="{$link->getPageLink('order', true, NULL, {$next_step_link_params})}"}

            <!--Display button-->
            <p class="content-right"><a href="{$next_step_link}" id="validate-cart"  style="float:none;" class="green-button gradien" title="{l s='Next'}">
                    {l s='Validate my cart'}&nbsp;
                </a></p>

        {/if}
        <p class="content-right">
            <a id="continue-shopping-button" class="red-button" href="{if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, 'order.php')) || isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, 'order-opc') || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('index')}{else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if}" title="{l s='Continue shopping'}">&nbsp;<span>{l s='Continue shopping'}</span></a>
        </p>
    </div>
{/if}
</div>
</div><!-- / #center_column -->
</div>
