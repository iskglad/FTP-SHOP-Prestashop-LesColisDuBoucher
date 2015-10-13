<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel='stylesheet' href="{$path_css_folder}admin/etiquettes/ecolotrans.css" type="text/css"/>
</head>
<body>

<!--Ecolos Header-->
<header>
    <img class="ecolotrans_header" src="{$path_img_folder}admin/ecolotrans_header.png">
</header>
{foreach $orders as $order}

<div class="margin_rect"></div>

<!--Head infos-->
<div class="expedition_infos">
    <p class="right">Client: LES COLIS DU BOUCHER</p>
    <p>Date: {$order.delivery_date|date_format:"d/m/Y"}</p>
    <p>
        Horraire:
        {if count($order.hours_array)}
            {$order.hours}
        {else}
            Aucun créneau
        {/if}
    </p>
</div>
<!--Table expedition details-->
<table>
    <th>
        <tr>
            <td class="expediteur-row">Expéditeur:</td>
            <td>Destinataire:</td>
        </tr>
    </th>
    <tbody>
    <tr valign="top">
        <td>
            <p class="LCDB_infos">
                Les colis du boucher<br/>
                Tel : 09 72 42 51 66
            </p>
        </td>
       <td colspan="10">
            <p class="name">
                {$order.delivery_first_name} {$order.delivery_last_name|@strtoupper}
                {if $order.delivery_company}
                    <i>- Société: {$order.delivery_company}</i>
                {/if}
            </p>
            <p class="address">
                {$order.delivery_address1}<br>
                {if $order.delivery_address2} {$order.delivery_address2}<br>{/if}
                {if $order.delivery_code || $order.delivery_floor}
                    {if $order.delivery_code}
                        Accès: {$order.delivery_code}
                    {/if}
                    {if $order.delivery_floor && $order.delivery_code}
                        /
                    {/if}
                    {if $order.delivery_floor}
                        Étage: {$order.delivery_floor}
                    {/if}
                {/if}
            </p>
            <p class="ville"><span class="bp">{$order.delivery_postcode}</span> {$order.delivery_city}</p>
            <p class="nb_colis">Nombre de colis: 1</p>
            <p class="poids">Poids (kg): {$order.package_weight}</p>
            <p>TEL1: <span class="tel1">{$order.delivery_phone}</span></p>
            <p>TEL2: <span class="tel2">{$order.delivery_phone_mobile}</span></p>
        </td>
    </tr>
    </tbody>
</table>

{/foreach}
</body>
</html>