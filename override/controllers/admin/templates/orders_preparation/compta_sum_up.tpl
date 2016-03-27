<section>
    <table class="table">
        <!--HEAD-->
        <thead>
            <tr>
                <th colspan="4">Sommaire Comptabilité</th>
            </tr>
            <tr>
                <th></th>
                <th>HT</th>
                <th>TVA</th>
                <th>TTC</th>
            </tr>
        </thead>

        <!--BODY-->
        <tbody>
            <!--Produits-->
            <tr class="odd alt_row">
                <th>Produits</th>
                <td>{$orders_total_products_ht|string_format:"%.2f"} &euro;</td>
                <td>{$orders_total_products_tva|string_format:"%.2f"} &euro;</td>
                <td>{$orders_total|string_format:"%.2f"} &euro;</td>
            </tr>

            <!--Reduction-->
            <tr>
                <th>Réductions</th>
                <td>{($total_discounts_ht * -1)|string_format:"%.2f"} &euro;</td>
                <td>{($total_discounts_tva * -1)|string_format:"%.2f"} &euro;</td>
                <td>{($total_discounts_wt * -1)|string_format:"%.2f"} &euro;</td>
            </tr>

            <!--Livraison-->
            <tr class="odd alt_row">
                <th>Livraisons</th>
                <td>{$total_shipping_ht|string_format:"%.2f"} &euro;</td>
                <td>{$total_shipping_tva|string_format:"%.2f"} &euro;</td>
                <td>{$total_shipping_wt|string_format:"%.2f"} &euro;</td>
            </tr>

            <!--Total-->
            <tr class="last">
                <th>Total encaissé</th>
                <td>{($orders_total_products_ht - $total_discounts_ht + $total_shipping_ht)|string_format:"%.2f"} &euro;</td>
                <td>{($orders_total_products_tva - $total_discounts_tva + $total_shipping_tva)|string_format:"%.2f"} &euro;</td>
                <td>{($orders_total - $total_discounts_wt + $total_shipping_wt)|string_format:"%.2f"} &euro;</td>
            </tr>
        </tbody>
    </table>
    <legend>* Les produits offerts ne sont pas comptés</legend>
    <legend>Calcul manuel comparatif: Produit HT = Produit TTC / 1.055 = {$orders_total} / 1.055 = {$orders_total/1.055}</legend>
</section>