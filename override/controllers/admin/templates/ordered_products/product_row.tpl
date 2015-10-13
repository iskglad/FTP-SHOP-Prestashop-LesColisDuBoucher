<!--Colors-->
<tr class="product-row row_hover {cycle values="odd,alt_row"}">
    <!--Supplier-->
    <td class="pointer center">
        {$product.supplier}
    </td>

    <!--Category-->
    <td class="pointer center" >
        {$product.category}
    </td>

    <!--Product-->
    <td class="pointer">
        {$product.product_name}
    </td>

    <!--Label-->
    <td class="pointer center">
        <span class="color_field {$product.label_name}">
		    {$product.label_name}
		</span>
    </td>

    <!--Short description-->
    <td class="pointer left">
        {$product.description_short}
    </td>


    <!--Quantity-->
    <td class="pointer center">
        {$product.quantity}
    </td>

    <!--Action See-->
    <td class="center" style="white-space: nowrap;">
        <a href="index.php?controller=AdminOrderedproducts&amp;id_order=83&amp;vieworder&amp;token=21c1685656e58fc7c7877f8159c30502" title="Voir">
            <img src="../img/admin/details.gif" alt="Voir">
        </a>
    </td>
</tr>