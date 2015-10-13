<tr class="products-recap">
    <td colspan="7" class="pointer row_hover">
        <!--============
          CLIENT INFOS
         ============-->
        <div class="recap_infos">
            <address class="delivery_address">
                Livraison: <br>
                {$order.delivery_first_name} {$order.delivery_last_name|@upper}<br>
                {if $order.delivery_company}
                    {$order.delivery_company}<br>
                {/if}

                {$order.delivery_address1} <br>
                {if $order.delivery_address2}
                    {$order.delivery_address2} <br>
                {/if}

                {$order.delivery_postcode} {$order.delivery_city}<br>

                {if $order.delivery_phone}
                    Tel: {$order.delivery_phone}<br>
                {/if}

                {if $order.delivery_phone_mobile}
                    Mobile: {$order.delivery_phone_mobile}<br>
                {/if}
            </address>

            {if $order.is_new_customer}
                <div class="new_client">Nouveau client !</div>
            {/if}

            {if $order.is_payment_done}
                <div class="payment">Paiment effectué</div>
            {/if}
            {if !$order.valid}
                <div class="payment invalid">Commande invalide</div>
            {/if}

            {if $order.client_note || $order.message}
                <div class="note">
                    {if $order.client_note}
                        Note: <br>
                        {$order.client_note}
                    {/if}
                    {if $order.message}
                        {if $order.client_note}<br>{/if}
                        <strong>Message du client: </strong><br>
                        {$order.message}
                    {/if}
                </div>
            {/if}
        </div>

        <!--============
        PRODUCTS
         ============-->
        <table class="recap_products" cellpadding="0" cellspacing="0">
            <colgroup>
                <col width="300px"> <!--produit-->
                <col width="100px"> <!--label-->
                <col width="200px"> <!--descriptio-->
                <col width="5px"> <!--qte-->
                <!--<col width="200px"><!--poids-->

                <!--============
                HEAD
                ============-->
                <thead>
                <th>Produit</th>
                <th>Label</th>
                <th>Colis suprise</th>
                <th>Description</th>
                <th class="center">Qté</th>
                <!--<th class="center">Poids</th>-->
                </thead>

                <!--============
               BODY
               ============-->
                <tbody>
                {foreach $order.products as $product}


                    <tr class="row_hover {if $product@last} last {/if}">
                        <td class="product_name">
                            {$product.product_name}
                        </td>
                        <td>{$product.label_name}</td>
                        <td>{$product.colis_name}</td>
                        <td>{$product.description_short}</td>
                        <td class="center">
                            {$product.quantity}
                        </td>
                        <!--
                        <td class="center">
                            {$product.weight * $product.quantity} kg ({$product.weight|string_format:"%.2f"} kg/piece)
                        </td>
                        -->
                    </tr>

                {/foreach}
                </tbody>
        </table>
    </td>
</tr>