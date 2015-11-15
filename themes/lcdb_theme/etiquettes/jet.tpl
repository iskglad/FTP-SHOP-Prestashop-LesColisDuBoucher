<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title>Etiquette JET <?php echo $date;?></title>
    <link rel='stylesheet' href="{$path_css_folder}admin/etiquettes/jet.css" type="text/css"/>
</head>
<body>
{foreach $orders as $order}
    <div class="colis">
        <p class="date">Jet Service <?php echo $date;?> </p>
        <ul>
            <li>
                {$order.hours}
            </li>
            {if !$order.is_payment_done}
                <li class="paiement">
                    Paiement à effectuer : {$order.total_paid_wt} euros
                </li>
            {/if}

            <li>
                <b>
                    {$order.delivery_first_name} {$order.delivery_last_name|@strtoupper}
                    {if $order.delivery_company}
                        <i>- Société: {$order.delivery_company}</i>
                    {/if}
                </b>
            </li>
            <li>{$order.delivery_address1}<br>
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
                {/if}</li>
            <li><b>{$order.delivery_postcode} {$order.delivery_city}</b></li>
            <li>
                Tel:
                {$order.delivery_phone}
                {if $order.delivery_phone_mobile} | {$order.delivery_phone_mobile} {/if}
            </li>
            <li class="note">{$order.client_note}</li>
        </ul>
    </div>
{/foreach}
</body>
</html>