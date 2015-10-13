<tr class="buyers">
    <td colspan="7" class="pointer row_hover">
        <table class="" cellpadding="0" cellspacing="0">
            <colgroup>
                <col width="20px"> <!--Cmd-->
                <col width="300px"> <!--Client-->
                <col width="40px"> <!--Groupe-->
                <col width="20px"><!--quantity-->

                <!--============
                HEAD
                ============-->
                <thead>
                <th class="center">Cmd</th>
                <th>Client livré</th>
                <th class="center">Type produit</th>
                <th class="center">Quantité</th>
                </thead>

                <!--============
               BODY
               ============-->
                <tbody>
                {foreach $product.buyers as $buyer}


                <tr class="row_hover {if $buyer@last} last {/if}">
                    <td class="center">
                        <a class='cmd_link' href="index.php?controller=AdminOrders&amp;id_order={$buyer.id_order}&amp;vieworder&amp;token={$admin_cmd_token}">
                            {$buyer.id_order}
                        </a>
                    </td>
                    <td>
                        <span>{$buyer.last_name}<span>
                        {$buyer.first_name}
                        {if $buyer.company}
                            {$buyer.company}
                        {/if}
                    </td>
                    <td class="center">
                       {if $buyer.is_pro}
                          PRO
                       {/if}
                    </td>
                    <td class="center">{$buyer.quantity}</td>
                </tr>

                {/foreach}
                </tbody>
        </table>
    </td>
</tr>