

<div id="commande-detail">

    <h3>Commande #{$order->id} - Livraison le {$order->date_delivery|date_format:"%A, %e %B, %Y"|capitalize}</h3>

    <h4>1. Facture et livraison</h4>
    <hr />
    <h5>Montant total</h5>
    <p>{displayWtPriceWithCurrency price=$order->total_paid currency=$currency}</p>

    <table id="facture-detail-table">
        <tbody>
        <tr class="first">
            <td class="first">
                <h5>Mode de paiment</h5>
                <p>{$order->payment|escape:'htmlall':'UTF-8'}</p>
            </td>
            <td>
                <h5>Etat de la commande</h5>
                <p>
                    {if isset($order_state)}
                        {$order_state_name|escape:'htmlall':'UTF-8'}
                    {else}
                        {l s='En cours de traitement'}
                    {/if}
                </p>
            </td>
        </tr>
        <tr>
            <td class="first">
                <h5>Adresse de facturation</h5>
                <ul>
                    {foreach from=$inv_adr_fields name=inv_loop item=field_item}
                        {if $field_item eq "company" && isset($address_invoice->company)}
                            <li class="address_company">
                                {$address_invoice->company|escape:'htmlall':'UTF-8'}
                            </li>
                        {elseif $field_item eq "address2" && $address_invoice->address2}
                            <li class="address_address2">
                                {$address_invoice->address2|escape:'htmlall':'UTF-8'}
                            </li>
                        {elseif $field_item eq "phone_mobile" && $address_invoice->phone_mobile}
                            <li class="address_phone_mobile">
                                {$address_invoice->phone_mobile|escape:'htmlall':'UTF-8'}
                            </li>
                        {else}
                            {assign var=address_words value=" "|explode:$field_item}
                            <li>
                                {foreach from=$address_words item=word_item name="word_loop"}
                                    {if !$smarty.foreach.word_loop.first} {/if}
                                    <span class="address_{$word_item|replace:',':''}">
												{$invoiceAddressFormatedValues[$word_item|replace:',':'']|escape:'htmlall':'UTF-8'}
										</span>
                                {/foreach}
                            </li>
                        {/if}

                    {/foreach}
                </ul>
            </td>
            <td>
                <h5>Adresse de livraison</h5>
                <ul>
                    {foreach from=$dlv_adr_fields name=dlv_loop item=field_item}
                        {if $field_item eq "company" && isset($address_delivery->company)}
                            <li class="address_company">
                                {$address_delivery->company|escape:'htmlall':'UTF-8'}
                            </li>
                        {elseif $field_item eq "address2" && $address_delivery->address2}
                            <li class="address_address2">
                                {$address_delivery->address2|escape:'htmlall':'UTF-8'}
                            </li>
                        {elseif $field_item eq "phone_mobile" && $address_delivery->phone_mobile}
                            <li class="address_phone_mobile">
                                {$address_delivery->phone_mobile|escape:'htmlall':'UTF-8'}
                            </li>
                        {else}
                            {assign var=address_words value=" "|explode:$field_item}
                            <li>
                                {foreach from=$address_words item=word_item name="word_loop"}
                                    {if !$smarty.foreach.word_loop.first} {/if}
                                    <span class="address_{$word_item|replace:',':''}">
												{$deliveryAddressFormatedValues[$word_item|replace:',':'']|escape:'htmlall':'UTF-8'}
										</span>
                                {/foreach}
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </td>
        </tr>
        </tbody>
    </table>

    <h5>Date de livraison</h5>
    <p>{$order->date_delivery|date_format:"%A %e %B %Y"|capitalize}<br />
        {$order->hour_delivery}</p>
    <hr />
    <h5>Mode et frais de livraison</h5>
    {if $carrier->id}
        <p>
            {$carrier->name|escape:'htmlall':'UTF-8'} -
            {if $order->total_shipping == 0}
                Gratuit
            {elseif $order->free_shipping_discount}
                Gratuit
            {else}
                {$order->total_shipping} €
            {/if}
        </p>
    {/if}
    <hr />

    {$HOOK_ORDERDETAILDISPLAYED}

    <h4 class="detail-title">2. Détails de la commande</h4>
    <hr />
    <table id="detail-commande-table">
        <thead>
        <tr>
            <th class="first"></th>
            <th>Prix<br/> unitaire</th>
            <th class="qte">Qté</th>
            <th class="last">Prix<br/> total TTC</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$products item=product name=products}
            {if !isset($product.deleted)}
                {assign var='productId' value=$product.product_id}
                {assign var='productAttributeId' value=$product.product_attribute_id}
                {assign var='productQuantity' value=$product.product_quantity}
                <tr class="first">
                    <td class="first"> {$product.product_name|escape:'htmlall':'UTF-8'}</td>
                    <td>{convertPriceWithCurrency price=$product.unit_price_tax_incl currency=$currency}</td>
                    <td>{$productQuantity|intval}</td>
                    <td class="last">{convertPriceWithCurrency price=$product.total_price_tax_incl currency=$currency}</td>
                </tr>
            {/if}
        {/foreach}
        </tbody>
    </table>

    <div id="total">
        <p class="clearfix"><span class="title bold">Total panier TTC :</span><span class="value bold green">{displayWtPriceWithCurrency price=$order->getTotalProductsWithTaxes() currency=$currency}</span></p>
        {if $order->free_shipping_discount}
            <p class="clearfix"><span class="title">Frais de livraison :</span><span class="value green">Offerts!</span></p>
        {else}
            <p class="clearfix"><span class="title">Frais de livraison :</span><span class="value green">{displayWtPriceWithCurrency price=$order->total_shipping currency=$currency}</span></p>
        {/if}

        {*<p class="clearfix"><span class="title">Option colis cadeau :</span><span class="value green">0,00 €</span></p>*}
        <p class="clearfix total"><span class="title bold">Sous-total TTC :</span><span class="value bold green">{displayWtPriceWithCurrency price=$order->total_paid currency=$currency}</span></p>
    </div>

</div>
