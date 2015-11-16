
{assign var="back_order_page" value="order.php"}

{* Will be deleted for 1.5 version and more *}
{if !isset($formatedAddressFieldsValuesList)}
    {$ignoreList.0 = "id_address"}
    {$ignoreList.1 = "id_country"}
    {$ignoreList.2 = "id_state"}
    {$ignoreList.3 = "id_customer"}
    {$ignoreList.4 = "id_manufacturer"}
    {$ignoreList.5 = "id_supplier"}
    {$ignoreList.6 = "date_add"}
    {$ignoreList.7 = "date_upd"}
    {$ignoreList.8 = "active"}
    {$ignoreList.9 = "deleted"}

    {* PrestaShop 1.4.0.17 compatibility *}
    {if isset($addresses)}
        {foreach from=$addresses key=k item=address}
            {counter start=0 skip=1 assign=address_key_number}
            {$id_address = $address.id_address}
            {foreach from=$address key=address_key item=address_content}
                {if !in_array($address_key, $ignoreList)}
                    {$formatedAddressFieldsValuesList.$id_address.ordered_fields.$address_key_number = $address_key}
                    {$formatedAddressFieldsValuesList.$id_address.formated_fields_values.$address_key = $address_content}
                    {counter}
                {/if}
            {/foreach}
        {/foreach}
    {/if}
{/if}

<script type="text/javascript">
    // <![CDATA[
    {if !$opc}
    var orderProcess = 'order';
    var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
    var currencyRate = '{$currencyRate|floatval}';
    var currencyFormat = '{$currencyFormat|intval}';
    var currencyBlank = '{$currencyBlank|intval}';
    var txtProduct = "{l s='product' js=1}";
    var txtProducts = "{l s='products' js=1}";
    {/if}
    var img_folder = '{$img_dir}';

    //price used in order_minimum_error.js
    var total_price = {$total_products_price_with_tax};
    var total_order_price_with_discount = {$total_order_price_with_tax_and_discounts};
    var minimum_order_zone_proche = {$minimum_order_zone_proche};
    var minimum_order_current_zone = {$minimum_order_current_zone};

    var addressMultishippingUrl = "{$link->getPageLink('address', true, NULL, "back={$back_order_page}?step=1{'&multi-shipping=1'|urlencode}{if $back}&mod={$back|urlencode}{/if}")}";
    var addressUrl = "{$link->getPageLink('address', true, NULL, "back={$back_order_page}?step=1{if $back}&mod={$back}{/if}")}";
    var orderUrl = "{$link->getPageLink('order', true, NULL)}";

    var formatedAddressFieldsValuesList = new Array();

    {foreach from=$formatedAddressFieldsValuesList key=id_address item=type}
    formatedAddressFieldsValuesList[{$id_address}] =
    {ldelim}
        'ordered_fields':[
            {foreach from=$type.ordered_fields key=num_field item=field_name name=inv_loop}
            {if !$smarty.foreach.inv_loop.first},{/if}"{$field_name|replace:'Country:name':'country name'}"
            {/foreach}
        ],
        'formated_fields_values':{ldelim}
        {foreach from=$type.formated_fields_values key=pattern_name item=field_name name=inv_loop}
        {if !$smarty.foreach.inv_loop.first},{/if}"{$pattern_name|replace:'Country:name':'country name'}":"{$field_name}"
        {/foreach}
        {rdelim}
    {rdelim}
    {/foreach}

    function getAddressesTitles()
    {
        return {
            'invoice': "{l s='Your billing address' js=1}",
            'delivery': "{l s='Your delivery address' js=1}"
        };

    }

    function updateAddressSelection(addressType)
    {
        var idAddress_delivery = ($('#id_address_delivery').length === 1 ? $('#id_address_delivery').val() : $('#id_address_delivery').val());
        var idAddress_invoice = ($('#id_address_invoice').length === 1 ? $('#id_address_invoice').val() : ($('#addressesAreEquals:checked').length === 1 ? idAddress_delivery : ($('#id_address_invoice').length === 1 ? $('#id_address_invoice').val() : idAddress_delivery)));

        idAddress = idAddress_delivery;
        if (addressType == 'invoice') {
            idAddress = idAddress_invoice;
        }

        $('#opc_account-overlay').fadeIn('slow');
        $('#opc_delivery_methods-overlay').fadeIn('slow');
        $('#opc_payment_methods-overlay').fadeIn('slow');

        $.ajax({
            type: 'POST',
            url: orderUrl,
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true&method=updateAddressesSelected&id_address_delivery=' + idAddress_delivery + '&id_address_invoice=' + idAddress_invoice + '&token=' + static_token,
            success: function(jsonData)
            {
                if (jsonData.hasError)
                {
                    var errors = '';
                    for(var error in jsonData.errors)
                        //IE6 bug fix
                        if(error !== 'indexOf')
                            errors += jsonData.errors[error] + "\n";
                    alert("error:" + errors);
                }
                else
                {
                    console.log("Id address:" + idAddress_delivery);
                    // Update global var deliveryAddress
                    deliveryAddress = idAddress_delivery;
                    if (addressType == 'delivery'){
                        //disable submit button
                        $('#submit-address').attr('disabled', 'true');
                        $('#submit-address').addClass('disabled-button');
                        $('#submit-address').val("Chargement...");
                        //reload page
                        location.reload();
                    }
                    else {
                        buildAddressBlock(idAddress, addressType, $('#address_'+ addressType));
                        //updateCarrierList(jsonData.carrier_data);
                    }
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                 if (textStatus !== 'abort')
                 alert("TECHNICAL ERROR: unable to save adresses \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                 console.log(XMLHttpRequest.responseText);
                 console.log(XMLHttpRequest);
            }
        });
    }

    var oldAdrDel;
    function updateCarrierList(json)
    {
        var html = json.carrier_block;

        // @todo  check with theme 1.4
        //if ($('#HOOK_EXTRACARRIER').length == 0 && json.HOOK_EXTRACARRIER !== null && json.HOOK_EXTRACARRIER != undefined)
        //	html += json.HOOK_EXTRACARRIER;
        // console.log(html);

        $('#carrier_area').replaceWith(html);
        /* update hooks for carrier module */
        $('#HOOK_BEFORECARRIER').html(json.HOOK_BEFORECARRIER);

        // First let's prepend icons (needed for effects)
        $(".checkbox, .radio").prepend("<span class='icon'></span><span class='icon-to-fade'></span>");
        $(".checkbox, .radio").click(function(){
            setupLabel();
        });
        setupLabel();
        enablePR();
        onchange();
    }

    function onchange() {
        $('#content-wrapper input').on('click',function() {
            if (oldAdrDel.length){
                $('#address_delivery').replaceWith(oldAdrDel.clone());
            }
        });
    }

    function buildAddressBlock(id_address, address_type, dest_comp)
    {
        // var adr_titles_vals = getAddressesTitles();
        var li_content = formatedAddressFieldsValuesList[id_address]['formated_fields_values'];
        var ordered_fields_name = ['title'];

        ordered_fields_name = ordered_fields_name.concat(formatedAddressFieldsValuesList[id_address]['ordered_fields']);
        ordered_fields_name = ordered_fields_name.concat(['update']);

        dest_comp.html('');

        // li_content['title'] = adr_titles_vals[address_type];
        li_content['update'] = '<a href="{$link->getPageLink('address', true, NULL, "id_address")}'+id_address+'&amp;back={$back_order_page}?step=1{if $back}&mod={$back}{/if}" title="{l s='Update' js=1}">&raquo; {l s='Update' js=1}</a>';

        appendAddressList(dest_comp, li_content, ordered_fields_name);
        oldAdrDel = $('#address_delivery').clone();
    }

    function appendAddressList(dest_comp, values, fields_name)
    {
        for (var item in fields_name)
        {
            var name = fields_name[item];
            var value = getFieldValue(name, values);
            if (value != "")
            {
                var new_li = document.createElement('li');
                new_li.className = 'address_'+ name;

                var field_prefix = '';
                if(name == 'code')
                    field_prefix = 'Code: ';
                if(name == 'floor')
                    field_prefix = 'Étage: ';
                if(name == 'phone')
                    field_prefix = 'Tel: ';
                if(name == 'phone_mobile')
                    field_prefix = 'Tel mob: ';


                new_li.innerHTML = field_prefix + getFieldValue(name, values);


                dest_comp.append(new_li);
            }
        }
    }

    function getFieldValue(field_name, values)
    {
        var reg=new RegExp("[ ]+", "g");

        var items = field_name.split(reg);
        var vals = new Array();

        for (var field_item in items)
        {
            items[field_item] = items[field_item].replace(",", "");
            vals.push(values[items[field_item]]);
        }
        return vals.join(" ");
    }

    function checkPointRelais(){

    }
    //]]>
</script>

<script>
    var relays = {$relays};
</script>
{capture name=path}{l s='Addresses'}{/capture}
{assign var='current_step' value='address'}
{assign var='order_total_flag' value='Cart::BOTH'|constant}
{assign var='order_total_flag_without' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
<div id="columns" class="content clearfix">
<div class="bloc-checkout">
{include file="./order-steps.tpl"}
{include file="$tpl_dir./errors.tpl"}
<form action="{$link->getPageLink($back_order_page, true)}" method="post">
<div id="content-wrapper" class="clearfix">
    <div class="bloc content-address-invoice">
        <h2>Adresse de facturation</h2>
        <select name="id_address_invoice" id="id_address_invoice" class="address_select"  onchange="updateAddressSelection('invoice');">
            {foreach from=$addresses key=k item=address}
                <option value="{$address.id_address|intval}" {if $address.id_address == $cart->id_address_delivery}selected="selected"{/if}>{$address.alias|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
        </select>
        <ul id="address_invoice">
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
      </div>
    <div class="bloc content-address-delivery">
        <h2>Adresse de livraison</h2>
        <div id="delivery-address">
            <select name="id_address_delivery" id="id_address_delivery" class="address_select" onchange="updateAddressSelection('delivery');">
                {foreach from=$addresses key=k item=address}
                    <option value="{$address.id_address|intval}"
                            {if $address.id_address == $cart->id_address_delivery}
                                selected="selected"
                            {/if}>
                        {$address.alias|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <ul id="address_delivery">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
           <div class="add-new-addr"><a href="{$link->getPageLink('address', true, NULL, "back={$back_order_page}?step=1{if $back}&mod={$back}{/if}")}" title="ajouter une nouvelle adresse" id="aadd-address-delivery">&rarr;&nbsp;<span>Ajouter une nouvelle adresse</span></a></div>
        </div>
       </div>

    <!--Carrier relay PopUp-->
    <div id="delivery-relay">
        <div id="relays">
            <div class="popin">
                <a href="#" title="Fermer" class="popin-close"></a>
                <p class="popin-title">Choisissez votre point relais</p>
                <div class="clearfix content-wrapper">
                    <div id="left-side">
                        <ul id="relay-list"></ul>
                    </div>
                    <div id="right-side">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Delivery choice bloc-->
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
                                                |&nbsp;<span class="">
															{if $option.total_price_with_tax && !$free_shipping}
                                                                {if $use_taxes == 1}
                                                                   <span class="shipping_price" data-shipping-price="{$option.total_price_with_tax}">
                                                                       {convertPrice price=$option.total_price_with_tax}
                                                                   </span>
                                                                {else}
                                                                    <span class="shipping_price" data-shipping-price="{$option.total_price_without_tax}">
                                                                        {convertPrice price=$option.total_price_without_tax}
                                                                    </span> {l s='(tax excl.)'}
                                                                {/if}
                                                            {elseif $option.total_price_with_tax && $free_shipping &&
                                                                    ($carrier.instance->name == 'Livraison le soir' || $carrier.instance->name == 'Livraison Express') &&
                                                                    !$special_case_free_shipping}
                                                                {if $use_taxes == 1}
                                                                <span class="shipping_price" data-shipping-price="{$option.total_price_with_tax}">
                                                                       {convertPrice price=$option.total_price_with_tax}
                                                                   </span>
                                                                {else}
                                                                    <span class="shipping_price" data-shipping-price="{$option.total_price_without_tax}">
                                                                        {convertPrice price=$option.total_price_without_tax}
                                                                    </span> {l s='(tax excl.)'}
                                                                 {/if}
                                                            {else}
                                                                {l s='Free!'} !
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
                                        <p class="description delivery_option_delay">
                                            {$carrier.instance->description[$cookie->id_lang]}
                                        </p>
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
            <textarea name="gift_message" placeholder="Saisissez le message qui sera joint au cadeau" id="gift_message" class="hidden">{$cart->gift_message|escape:'htmlall':'UTF-8'}</textarea>
        </div>
        <hr class="dashed" />
        <p id="total-price">Le montant TTC de votre commande est de <span class="price"><span data-price="{convertPrice price=$total_order_price_with_tax_and_discounts}" id="final-price">{convertPrice price=$total_order_price_with_tax_and_discounts}</span> &euro;</span></p>
        <div id="error-price" style="display:none">
            <p>
               <span class="bold">
                    Minimum de commande non atteint.
                </span><br>
                Nous vous invitons &agrave; continuer vos achats.<br />
                Le montant total de vos produits doit &ecirc;tre au minimum de <span id="error-minimum-price">{$minimum_order}</span> &euro; pour ce mode de livraison.<br>
                Il est actuellement de
                <span class="bold">
                    {$total_products_price_with_tax}
                </span> &euro;.
            </p>
        </div>
        <input type="hidden" class="hidden" name="step" value="2" />
        <input type="hidden" name="back" value="{$back}" />
    </div>

</div>
<div id="continue-shopping" class="content-right">
    <input type="hidden" name="custom_relay" id="custom_relay" value="0" />
    {if $minimum_order < ($cart->getOrderTotal(true, $order_total_flag_without)+$cart->getOrderTotal(true, 2)) && $id_zone}
        <p style="height:23px;"><input type="submit" value="valider" id="submit-address" class="green-button gradient"  /></p>
    {else}
        <p style="height:23px;"><input type="submit" value="valider" id="submit-address" class="green-button gradient disabled-button" disabled/></p>
    {/if}
    <p style=""><a id='continue-shopping-button' class='red-button' href="index.php?id_category=3&controller=category" title="Continuer mes achats">&nbsp;<span>Continuer mes achats</span></a></p>
</div>
</form>

<script>
    onchange();
    /*var reload_carrier = 1;
    if (reload_carrier == 1){
        updateAddressSelection('delivery');
    }*/
</script>
</div>
</div>