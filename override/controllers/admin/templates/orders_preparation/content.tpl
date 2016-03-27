<div class="toolbar-placeholder">
    <div class="toolbarBox toolbarHead">

        <ul class="cc_button">
            {*{if $productFilter_zone == "Ecolotrans" }*}
                <li>
                    <a jsData_data_system_file='Ecolotrans' class="toolbar_btn carrier_file_export export_etiquette" title="Export orders">
                        <span class="process-icon-ecolotrans-etiquette"></span>
                        <div>Etiquette ecolotrans</div>
                    </a>
                </li>
                <li>
                    <a jsData_data_system_file='Ecolotrans' class="toolbar_btn carrier_file_export export_csv" title="Export orders">
                        <span class="process-icon-ecolotrans-csv"></span>
                        <div>Csv ecolotrans</div>
                    </a>
                </li>
            {*{/if}
            {if $productFilter_zone == "Jet"}*}
                <li>
                    <a jsData_data_system_file='Jet' class="toolbar_btn carrier_file_export export_etiquette jet" title="Export orders">
                        <span class="process-icon-jet-etiquette"></span>
                        <div>Etiquette Jet</div>
                    </a>
                </li>
                <li>
                    <a jsData_data_system_file='Jet' class="toolbar_btn carrier_file_export export_csv jet" title="Export orders">
                        <span class="process-icon-jet-csv"></span>
                        <div>Csv Jet</div>
                    </a>
                </li>
            {*{/if}*}

            <li>
                <a id="exportCsvButton" class="toolbar_btn" title="Export orders">
                    <span class="process-icon-export "></span>
                    <div>Export orders</div>
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
                       Préparation de commandes
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
        <li>Nombre de commandes: <strong>{$orders_count}</strong></li>
        <li>Total encaissé: <strong>{$orders_total_paid|string_format:"%.2f"} euros</strong></li>
        <li>Total produits: <strong>{$orders_total|string_format:"%.2f"} euros</strong></li>
        <li>Panier moyen: <strong>{$orders_total_average|string_format:"%.2f"} euros</strong></li>
        <li>Poids total produits: <strong>{$orders_total_weight} kg</strong></li>
        <li>Poids total Colis (Poids produits + 2.5kg/pain de glace): <strong>{$orders_colis_total_weight} kg</strong></li>
    </ul>
</fieldset>
<br>


<!--====================
    Compta details
====================-->
{include file="./compta_sum_up.tpl"}
<br>

<!--====================
    Order List Section
====================-->

<form method="post" action="index.php?controller=AdminOrdersPreparation&amp;token={$token}" class="form">
<input type="hidden" id="submitFilterorder" name="submitFilterorder" value="0">

<table class="table_grid" name="list_table">
    <tbody><tr>
        <td style="vertical-align: bottom;">
            <a class='left button' id="sildeUpButton" onclick="$('.products-recap').fadeIn('fast');">
                Afficher les details
            </a>

            <a class='left button' id="slideDownButton" onclick="$('.products-recap').fadeOut('fast');">
                Cacher les details
            </a>

					<span style="float: right;">
                        <input type="hidden" name="token" value="{$token}"/>
                        <input type="hidden" id="data_system_file" name="data_system_file" value=""/>
                        <input type="hidden" id="exportCsv" name="export_csv" value=0 />
                        <input type="hidden" id="export_carrier_file_type" name="export_carrier_file_type" value=0  />

                        <input type="hidden" name="date_interval_begin" value="{$date_interval_begin}"/>
                        <input type="hidden" name="date_interval_end" value="{$date_interval_end}"/>
                        <input type="hidden" name="use_date_interval" value="{$use_date_interval}"/>

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
                    <col width="20px"> <!--Id-->
                    <col width="70px"> <!--Date-->
                    <col width="200px"> <!--Client-->
                    <col width="100px"> <!--Zone-->
                    <col width="100px"><!--Carrier-->
                    <col width="100px"><!--Hour-->
                    <col class='action' width="20px"><!--Action-->
                </colgroup>
                <thead>
                <!--====================
                    ORDER BY
                    ====================-->
                <input type="hidden" id="sort_by" name="SortBy" value="{$SortBy}"/>
                <input type="hidden" id="sort_way" name="SortWay" value="{$SortWay}"/>
                <tr class="nodrag nodrop" style="height: 40px">
                    <!--Order by ID-->
                    <th>
                        <span class="title_box">
			                Cmd ID
		                </span>
                        <br>
                        <a onclick="submitSort('id_order', 'asc');">
                            <img border="0" src="../img/admin/down.gif">
                        </a>
                        <a onclick="submitSort('id_order', 'desc');">
                            <img border="0" src="../img/admin/up.gif"></a>
                    </th>


                    <!--Order by Date-->
                    <th class="center">
		                <span class="title_box">
			                Date
		                </span>
                        <br>
                        <a onclick="submitSort('delivery_date', 'asc');">
                            <img border="0" src="../img/admin/down.gif"></a>
                        <a onclick="submitSort('delivery_date', 'desc');">
                            <img border="0" src="../img/admin/up.gif"></a>
                    </th>

                    <!--Order by Client-->
                    <th class="left">
		                <span class="title_box">
			                Client
		                </span>
                        <br>
                        <a onclick="submitSort('client_name', 'asc');">
                            <img border="0" src="../img/admin/down.gif"></a>
                        <a onclick="submitSort('client_name', 'desc');">
                            <img border="0" src="../img/admin/up.gif"></a>
                    </th>

                    <!--Order by Zone-->
                    <th class="">
		                <span class="title_box">
			                Zone
		                </span>
                        <br>
                        <a onclick="submitSort('zone', 'asc');">
                            <img border="0" src="../img/admin/down.gif"></a>
                        <a onclick="submitSort('zone', 'desc');">
                            <img border="0" src="../img/admin/up.gif"></a>
                    </th>

                    <!--Order by Carrier-->
                    <th class="left">
		                <span class="title_box">
			                Livraison
		                </span>
                        <br>
                        <a onclick="submitSort('carrier', 'asc');">
                            <img border="0" src="../img/admin/down.gif"></a>
                        <a onclick="submitSort('carrier', 'desc');">
                            <img border="0" src="../img/admin/up.gif"></a>
                    </th>

                    <!--Order by Hours-->
                    <th class="left">
		                <span class="title_box">
			                Horaires
		                </span>
                        <br>
                        <a onclick="submitSort('hours', 'asc');">
                            <img border="0" src="../img/admin/down.gif"></a>
                        <a onclick="submitSort('hours', 'desc');">
                            <img border="0" src="../img/admin/up.gif"></a>
                    </th>

                    <!--Action-->
                    <th class="action center">Actions<br>&nbsp;</th>
                </tr>


                <!--====================
                    FILTER
                    ====================-->
                <tr class="nodrag nodrop filter row_hover" style="height: 35px;">
                    <!--Filter ID-->
                    <td class="center">
                        <input type="text" class="filter" name="productFilter_id_order" value="{$productFilter_id_order}" style="width:20px">
                    </td>

                    <!--Filter Date-->
                    <td class="left">
                        <!--<input type="text" class="filter" name="productFilter_hours" value="{$productFilter_hours}">-->
                    </td>

                    <!--Filter Client name-->
                    <td class="left">
                        <input type="text" class="filter" name="productFilter_client_name" value="{$productFilter_client_name}">
                    </td>

                    <!--Filter Zone-->
                    <td class="left">
                        <select onchange="$('#submitFilterButtonorder').click();" name="productFilter_zone">
                            <option value="" {if $productFilter_zone == ""} selected="selected" {/if}>--</option>
                            {foreach $shop_zones as $zone}
                                <option value="{$zone.name}" {if $productFilter_zone == $zone.name} selected="selected" {/if}>
                                    {$zone.name}
                                </option>
                            {/foreach}
                        </select>
                    </td>

                    <!--Filter carrier-->
                    <td class="left">
                        <select onchange="$('#submitFilterButtonorder').click();" name="productFilter_carrier">
                            <option value="" {if $productFilter_carrier == ""} selected="selected" {/if}>--</option>
                            {foreach $shop_carriers as $carrier}
                                <option value="{$carrier.name}" {if $productFilter_carrier == $carrier.name} selected="selected" {/if}>
                                    {$carrier.name}
                                </option>
                            {/foreach}
                        </select>
                    </td>

                    <!--Filter Hours-->
                    <td class="left">
                        <input type="text" class="filter" name="productFilter_hours" value="{$productFilter_hours}">
                    </td>

                    <!--Action-->
                    <td class="center">--</td>
                </thead>

                <tbody>
                {foreach $orders as $order}

                    <!--====================
                        PRODUCT ROW
                        ====================-->
                    {include file="./order_row.tpl"}


                    <!--====================
                            BUYERS ROW
                        ====================-->
                    {include file="./products_recap_row.tpl"}


                {/foreach}

                <tr class="alt_row row_hover">
                </tr>
                </tbody>


            </table>
        </td>
    </tr>
    </tbody></table>
</form>