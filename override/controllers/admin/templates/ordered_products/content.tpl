<div class="toolbar-placeholder">
    <div class="toolbarBox toolbarHead">

        <ul class="cc_button">
            <li id="exportCsvButton">
                <a id="desc-order-export" class="toolbar_btn" title="Export products">
                    <span class="process-icon-export "></span>
                    <div>Export products</div>
                </a>
            </li>
            <li class="help-context-AdminOrders">
                <a id="desc-AdminOrders-help" class="toolbar_btn" href="#" onclick="showHelp('http://help.prestashop.com',
                         'AdminOrders',
                         'fr',
                         '1.5.4.1',
                         '17051343',
                         'FR');" title="">
                    <span class="process-icon-help"></span>
                    <div>Aide</div>
                </a>
            </li>
        </ul>

        <script language="javascript" type="text/javascript">

            function submitSort(sort_by, sort_way){
                //set hidden input val
                $('#sort_by').val(sort_by);
                $('#sort_way').val(sort_way);

                //submit
                $('#submitFilterButtonorder').click();
            }


            //<![CDATA[
            var submited = false
            $(function() {
                //get reference on save link
                btn_save = $('span[class~="process-icon-save"]').parent();

                //get reference on form submit button
                btn_submit = $('#order_form_submit_btn');

                if (btn_save.length > 0 && btn_submit.length > 0)
                {
                    //get reference on save and stay link
                    btn_save_and_stay = $('span[class~="process-icon-save-and-stay"]').parent();

                    //get reference on current save link label
                    lbl_save = $('#desc-order-save div');

                    //override save link label with submit button value
                    if (btn_submit.val().length > 0)
                        lbl_save.html(btn_submit.attr("value"));

                    if (btn_save_and_stay.length > 0)
                    {

                        //get reference on current save link label
                        lbl_save_and_stay = $('#desc-order-save-and-stay div');

                        //override save and stay link label with submit button value
                        if (btn_submit.val().length > 0 && lbl_save_and_stay && !lbl_save_and_stay.hasClass('locked'))
                        {
                            lbl_save_and_stay.html(btn_submit.val() + " et rester ");
                        }

                    }

                    //hide standard submit button
                    btn_submit.hide();
                    //bind enter key press to validate form
                    $('#order_form').keypress(function (e) {
                        if (e.which == 13 && e.target.localName != 'textarea')
                            $('#desc-order-save').click();
                    });
                    //submit the form

                    btn_save.click(function() {
                        // Avoid double click
                        if (submited)
                            return false;
                        submited = true;

                        //add hidden input to emulate submit button click when posting the form -> field name posted
                        btn_submit.before('<input type="hidden" name="'+btn_submit.attr("name")+'" value="1" />');

                        $('#order_form').submit();
                        return false;
                    });

                    if (btn_save_and_stay)
                    {
                        btn_save_and_stay.click(function() {
                            //add hidden input to emulate submit button click when posting the form -> field name posted
                            btn_submit.before('<input type="hidden" name="'+btn_submit.attr("name")+'AndStay" value="1" />');

                            $('#order_form').submit();
                            return false;
                        });
                    }

                }
            });
            //]]>
        </script>

        <div class="pageTitle">
            <h3>
				<span id="current_obj" style="font-weight: normal;">
					<span class="breadcrumb item-0 ">
                       Cumul des produits à prévoir
					</span>

				</span>
            </h3>
            <p>
                 <span class="toolbar_btn calendar_img">
                    <span class="process-icon-save-calendar"></span>
                </span>

                <input class="datepicker date interval_begin" value="{$date_interval_begin}"/>
                <input class="datepicker date interval_end" value="{$date_interval_end}"/><br/>

                <label class='interval_label' for="use_date_interval">
                    <input id='use_date_interval' class='use_date_interval' type="checkbox" {if $use_date_interval == "true"}checked="checked"{/if}/>
                    Intervalle de date
                </label>
            </p>
        </div>
    </div>
</div>


<div class="leadin"></div>






<fieldset>
    <ul>
        <li>Nombre de produits: <strong>{$products|@count}</strong></li>
    </ul>
</fieldset>
<br>







<form method="post" action="index.php?controller=AdminOrderedproducts&amp;date={$date}&amp;token={$token}#order" class="form">
<input type="hidden" id="submitFilterorder" name="submitFilterorder" value="0">

<table class="table_grid" name="list_table">
<tbody><tr>
    <td style="vertical-align: bottom;">
        <a class='left button' id="sildeUpButton" onclick="$('.buyers').fadeIn('fast');">
            Afficher les details
        </a>

        <a class='left button' id="slideDownButton" onclick="$('.buyers').fadeOut('fast');">
            Cacher les details
        </a>
					<span style="float: right;">
                        <input type="hidden" name="date_interval_begin" value="{$date_interval_begin}"/>
                        <input type="hidden" name="date_interval_end" value="{$date_interval_end}"/>
                        <input type="hidden" name="use_date_interval" value="{$use_date_interval}"/>

                        <input type="hidden" id="exportCsv" name="export_csv" value=0/>

                        <input type="submit" id="submitFilterButtonorder" name="submitFilter" value="Filtre" class="button"/>
						<input type="button" id="resetFilterButton" name="submitResetorder" value="Réinitialiser" class="button""/>

                    </span>
        <span class="clear"></span>
    </td>
</tr>
<tr>
<td>
<table class="table order" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom:10px;">
    <colgroup>
    <col width="150px"> <!--Supplier-->
    <col width="65px"> <!--Category-->
    <col width="180px"> <!--Product-->
    <col width="100px"><!--Label-->
    <col width="280px"><!--Short description-->
    <col width="20px"><!--Quantity-->
    <col width="20px"><!--Action-->
</colgroup>
<thead>
<!--====================
    ORDER BY
    ====================-->
<input type="hidden" id="sort_by" name="SortBy" value="{$SortBy}"/>
<input type="hidden" id="sort_way" name="SortWay" value="{$SortWay}"/>
<tr class="nodrag nodrop" style="height: 40px">
    <!--Order by Supplier-->
    <th class="center">
        <span class="title_box">
			Fournisseur
		</span>
        <br>
        <a onclick="submitSort('supplier', 'asc');">
            <img border="0" src="../img/admin/down.gif">
        </a>
        <a onclick="submitSort('supplier', 'desc');">
            <img border="0" src="../img/admin/up.gif"></a>
    </th>


    <!--Order by Category-->
    <th class="center">
		<span class="title_box">
			Categorie
		</span>
        <br>
        <a onclick="submitSort('category', 'asc');">
            <img border="0" src="../img/admin/down.gif"></a>
        <a onclick="submitSort('category', 'desc');">
            <img border="0" src="../img/admin/up.gif"></a>
    </th>

    <!--Order by Product-->
    <th class="left">
		<span class="title_box">
			Produit
		</span>
        <br>
        <a onclick="submitSort('product_name', 'asc');">
            <img border="0" src="../img/admin/down.gif"></a>
        <a onclick="submitSort('product_name', 'desc');">
            <img border="0" src="../img/admin/up.gif"></a>
    </th>

    <!--Order by Label-->
    <th class="center">
		<span class="title_box">
			Label
		</span>
        <br>
        <a onclick="submitSort('label', 'asc');">
            <img border="0" src="../img/admin/down.gif"></a>
        <a onclick="submitSort('label', 'desc');">
            <img border="0" src="../img/admin/up.gif"></a>
    </th>

    <!--Description-->
    <th class="left">
		<span class="title_box">
			Description
		</span>
        <br>&nbsp;
    </th>

    <!--Quantity-->
    <th class="center">
		<span class="title_box">
			Quantité
		</span>
        <br>
        <a onclick="submitSort('quantity', 'asc');">
            <img border="0" src="../img/admin/down.gif"></a>
        <a onclick="submitSort('quantity', 'desc');">
            <img border="0" src="../img/admin/up.gif"></a>
    </th>

    <!--Action-->
    <th class="center">Actions<br>&nbsp;</th>
</tr>


<!--====================
    FILTER
    ====================-->
<tr class="nodrag nodrop filter row_hover" style="height: 35px;">
    <!--Filter Supplier-->
    <td class="center">
        <select onchange="$('#submitFilterButtonorder').focus();$('#submitFilterButtonorder').click();" name="productFilter_supplier" style="width:150px">
            <option value="" {if $productFilter_supplier == ""} selected="selected" {/if}>--</option>
            {foreach $shop_suppliers as $supplier}
                <option value="{$supplier.name}" {if $productFilter_supplier == $supplier.name} selected="selected" {/if}>
                    {$supplier.name}
                </option>
            {/foreach}
        </select>
    </td>


    <!--Filter Meat Category-->
    <td class="center">
        <select onchange="$('#submitFilterButtonorder').focus();$('#submitFilterButtonorder').click();" name="productFilter_category" style="width:100px">
            <option value="" {if $productFilter_category == ""} selected="selected" {/if}>--</option>
            {foreach $shop_product_categories as $category}
                <option value="{$category.name}" {if $productFilter_category == $category.name} selected="selected" {/if}>
                    {$category.name}
                </option>
            {/foreach}
        </select>
    </td>

    <!--Filter Product name-->
    <td class="left">
        <input type="text" class="filter" name="productFilter_name" value="{$productFilter_name}" style="width:280px">
    </td>

    <!--Filter Label-->
    <td class="center">
        <select onchange="$('#submitFilterButtonorder').focus();$('#submitFilterButtonorder').click();" name="productFilter_label" style="width:100px">
            <option value="" {if $productFilter_label == ""} selected="selected" {/if}>--</option>
            {foreach $shop_labels as $label}
                <option value="{$label.name}" {if $productFilter_label == $label.name} selected="selected" {/if}>
                    {$label.name}
                </option>
            {/foreach}
        </select>
    </td>

    <!--Filter Short Description-->
    <td class="left">
        <input type="text" class="filter" name="productFilter_short_description" value="{$productFilter_short_description}" style="width:280px">
    </td>

    <!--Quantity-->
    <td class="center">
        <input type="text" class="filter" name="productFilter_quantity" value="{$productFilter_quantity}" style="width:20px">
    </td>

    <!--Action-->
    <td class="center">--</td>
</thead>

<tbody>
{foreach $products as $product}

<!--====================
    PRODUCT ROW
    ====================-->
{include file="./product_row.tpl"}


<!--====================
        BUYERS ROW
    ====================-->
{include file="./product-buyers-row.tpl"}


{/foreach}

<tr class="alt_row row_hover">
</tr>
</tbody>


</table>
</td>
</tr>
</tbody></table>
<input type="hidden" name="token" value="{$token}">
</form>