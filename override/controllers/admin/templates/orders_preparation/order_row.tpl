<!--Colors-->
<tr class="order-row row_hover {cycle values="odd,alt_row"}">
    <!--ID-->
    <td class="pointer center">
        <a class='cmd_link' href="index.php?controller=AdminOrders&amp;id_order={$order.id_order}&amp;vieworder&amp;token={$admin_cmd_token}">
            {$order.id_order}
        </a>
    </td>

    <!--Date-->
    <td class="pointer center" >
        {$order.delivery_date|date_format:"%a %e %b %y"}
    </td>

    <!--Client-->
    <td class="pointer" >
        {if $order.is_new_customer}
            <span class="new_client">Nouveau</span>
        {/if}
        {$order.client_name}
    </td>

    <!--Zone-->
    <td class="pointer">
        {$order.zone}
    </td>

    <!--Carrier-->
    <td class="pointer">
	    <select data_id_order='{$order.id_order}' class="carrier updatable">
            {foreach $shop_carriers as $carrier}
                <option value="{$carrier.id_carrier}" {if $carrier.name == $order.carrier}selected="selected"{/if}>
                    {$carrier.name}
                </option>
            {/foreach}
        </select>
    </td>

    <!--Hour-->
    <td class="pointer left">
        <input data_id_order='{$order.id_order}' class='delivery_hours updatable' value='{$order.hours}'/>
        <a class="cmd_link updatable validate_button">Ok</a>
    </td>

    <!--Action See-->
    <td class="center action" style="white-space: nowrap;">
        <a href="#" title="Voir">
            <img src="../img/admin/details.gif" alt="Voir">
        </a>
    </td>
</tr>