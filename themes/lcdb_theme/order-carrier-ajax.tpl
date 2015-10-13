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

{include file="$tpl_dir./errors.tpl"}
{assign var='order_total_flag' value='Cart::BOTH'|constant}
{assign var='order_total_flag_without' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}

<div class="bloc content-delivery-mode" id="carrier_area">
		<div class="delivery_options_address bloc content-delivery-modee" data-id-current-zone = {$id_zone}>
			<h2>Modes de livraison</h2>
			{if isset($delivery_option_list)}
				{foreach $delivery_option_list as $id_address => $option_list}
					<ul>
						{foreach $option_list as $key => $option}
							<li id="delivery-home-li">
								{if $option.unique_carrier}
                                    {foreach $option.carrier_list as $carrier}
                                        <p>
                                            <label class="radio" for="delivery_option_{$id_address}_{$option@index}">
                                                <input
                                                        {if $carrier.instance->name|lower == "livraison en point relais"}
                                                            class="choose-relay_"
                                                        {elseif $carrier.instance->name|lower == "retrait en magazin"}
                                                            class="retrait-lcdb_"
                                                        {/if}
                                                        idaddress="{$id_address}" type="radio" name="delivery" value="{$carrier.instance->id}" onchange="updateExtraCarrier('{$key}', {$id_address});" id="delivery_option_{$id_address}_{$option@index}"/><!-- {if $carrier.instance->id_reference == $ID_RELAY_CARRIER}{/if}-->
                                                <span class="delivery_option_title bold">{$carrier.instance->name}</span>
                                                |	<span class="">
															{if $option.total_price_with_tax && !$free_shipping}
                                                                {if $use_taxes == 1}
                                                                    {convertPrice price=$option.total_price_with_tax} {l s='(tax incl.)'}
                                                                {else}
                                                                    {convertPrice price=$option.total_price_without_tax} {l s='(tax excl.)'}
                                                                {/if}
                                                            {else}
                                                                {l s='Free!'}
                                                            {/if}
														</span></label>
                                        </p>
                                    {/foreach}

                                    <!--Choose relay button-->
                                    {if ($carrier.instance->name|lower == "livraison en point relais")}
                                        <a id="button_choose_carrier_relay" onclick="$('.choose-relay_').click()">
                                            {l s='» Choisir un lieu de retrait'}
                                        </a>
                                    {/if}
									{if isset($carrier.instance->description[$cookie->id_lang])}
										<p class="description delivery_option_delay">{$carrier.instance->description[$cookie->id_lang]}</p>
									{/if}
								{/if}
							</li>
						{/foreach}
					</ul>
					{foreachelse}
						<p class="warning" id="noCarrierWarning">
							{foreach $cart->getDeliveryAddressesWithoutCarriers(true) as $address}
								{if empty($address->alias)}
									{l s='No carriers available.'}
								{else}
									{l s='No carriers available for the address "%s".' sprintf=$address->alias}
								{/if}
								{if !$address@last}
								<br />
								{/if}
							{/foreach}
						</p>
				{/foreach}
			{/if}
		</div>
		<div id="colis-cadeau-wrapper" style="display:none">
			<hr class="dashed" />
			<label for="colis-cadeau" id="colis-cadeau-toggle" class="checkbox"><input value="1" name="gift" type="checkbox" id="colis-cadeau"/> Je souhaite que ma commande soit envoyée par <a href="#">colis cadeau</a> <span class="price">+ <span id="sup">1,60</span> &euro;</span></label>
			<textarea name="gift_message" placeholder="Saisissez le message qui sera joint au cadeau" id="gift_message">{$cart->gift_message|escape:'htmlall':'UTF-8'}</textarea>
		</div>
		<hr class="dashed" />
		<p id="total-price">Le montant TTC de votre commande est de <span class="price"><span data-price="{$cart->getOrderTotal(true, $order_total_flag)}" id="final-price">{$cart->getOrderTotal(true, $order_total_flag)}</span> &euro;</span></p>
		<div id="error-price">
			<p><span class="bold">Minimum de commande non atteint.</span><br> Nous vous invitons &agrave; continuer vos achats.<br />Pour une livraison dans le <span id="error-postal">{$postcode}</span>, le montant de votre commande doit &ecirc;tre au minimum de <span id="error-minimum-price">{$minimum_order}</span> &euro; pour ce mode de livraison. <br>Il est actuellement de <span class="bold">{$cart->getOrderTotal(true, $order_total_flag_without)}</span> &euro;.</p>
		</div>
		<input type="hidden" class="hidden" name="step" value="2" />
		<input type="hidden" name="back" value="{$back}" />
    <script>
        //choose the first elem
        $('.delivery_options_address label').first().click();
        $('.delivery_options_address label').first().addClass('checked');
        //updateMinimumOrderError({$minimum_order},{$cart->getOrderTotal(true, $order_total_flag_without)+$cart->getOrderTotal(true, 2)})
    </script>
    <input type="hidden" class="hidden" name="step" value="2" />
    <input type="hidden" name="back" value="{$back}" />
</div>