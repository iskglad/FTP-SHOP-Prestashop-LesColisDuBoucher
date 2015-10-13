
{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
	<script type="text/javascript">
		function saveCustomerNote()
		{
			$('#note_feedback').html('<img src="../img/loader.gif" alt="" />').show();
			var noteContent = $('#noteContent').val();
	
			$.ajax({
				type: "POST",
				url: "index.php",
				data: "token={getAdminToken tab='AdminCustomers'}&tab=AdminCustomers&ajax=1&action=updateCustomerNote&id_customer={$customer->id}&note="+noteContent,
				async : true,
				success: function(r) {
					$('#note_feedback').html('').hide();
					if (r == 'ok')
					{
						$('#note_feedback').html("<b style='color:green'>{l s='Your note has been saved'}</b>").fadeIn(400);
						$('#submitCustomerNote').attr('disabled', true);
					}
					else if (r == 'error:validation')
						$('#note_feedback').html("<b style='color:red'>({l s='Error: your note is not valid'}</b>").fadeIn(400);
					else if (r == 'error:update')
						$('#note_feedback').html("<b style='color:red'>{l s='Error: cannot save your note'}</b>").fadeIn(400);
					$('#note_feedback').fadeOut(3000);
				}
			});
		}
	</script>

<div id="container-customer">

	<div class="info-customer-left">
			<div style="float: right">
			<a href="{$current}&updatecustomer&id_customer={$customer->id}&token={$token}">
				<img src="../img/admin/edit.gif" />
			</a>
		</div>
		<span style="font-size: 14px;">
			{$customer->firstname} {$customer->lastname}
		</span>
		<img src="{$gender_image}" style="margin-bottom: 5px" /><br />
		<a href="mailto:{$customer->email}" style="text-decoration: underline; color:#268CCD;">{$customer->email}</a>
		<br /><br />
		{l s='ID:'} {$customer->id|string_format:"%06d"}<br />
		{l s='Registration date:'} {$registration_date}<br />
		{l s='Last visit:'} {if $customer_stats['last_visit']}{$last_visit}{else}{l s='never'}{/if}<br />
		{if $count_better_customers != '-'}{l s='Rank: #'} {$count_better_customers}<br />{/if}
		{if $shop_is_feature_active}{l s='Shop:'} {$name_shop}<br />{/if}
	</div>
	
	<div class="info-customer-right">
		<div style="float: right">
			<a href="{$current}&addcustomer&id_customer={$customer->id}&token={$token}">
				<img src="../img/admin/edit.gif" />
			</a>
		</div>
		{l s='Newsletter:'} {if $customer->newsletter}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}<br />
		{l s='Opt-in:'} {if $customer->optin}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}<br />
		{l s='Age:'} {$customer_stats['age']} {if isset($customer->birthday['age'])}({$customer_birthday}){else}{l s='unknown'}{/if}<br /><br />
		{l s='Last update:'} {$last_update}<br />
		{l s='Status:'} {if $customer->active}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}
	
		{if $customer->isGuest()}
			<div>
				{l s='This customer is registered as'} <b>{l s='guest'}</b>
				{if !$customer_exists}
					<form method="post" action="index.php?tab=AdminCustomers&id_customer={$customer->id}&token={getAdminToken tab='AdminCustomers'}">
						<input type="hidden" name="id_lang" value="{$id_lang}" />
						<p class="center"><input class="button" type="submit" name="submitGuestToCustomer" value="{l s='Transform to customer account'}" /></p>
						{l s='This feature generates a random password and sends an e-mail to the customer'}
					</form>
				{else}
					</div><div><b style="color:red;">{l s='A registered customer account already exists with this e-mail address'}</b>
				{/if}
			</div>
		{/if}

	</div>

	<div class="clear"></div>
	<div class="separation"></div>

	<div>

		<div class="info-customer-left">
			<h2>Groupement</h2>
			...
		</div>
		
		<div class="info-customer-right">
			<h2>Abonnement</h2>
			...
		</div>
	</div>

	<div class="clear"></div>
	<div class="separation"></div>
	
	<div>
		<h2>
			<img src="../img/admin/cms.gif" /> {l s='Add a private note'}
		</h2>
		<p>{l s='This note will be displayed to all employees but not to the customer.'}</p>
		<form action="ajax.php" method="post" onsubmit="saveCustomerNote();return false;" id="customer_note">
			<textarea name="note" id="noteContent" style="width:600px;height:100px" onkeydown="$('#submitCustomerNote').removeAttr('disabled');">{$customer_note}</textarea><br />
			<input type="submit" id="submitCustomerNote" class="button" value="{l s='   Save   '}" style="float:left;margin-top:5px" disabled="disabled" />
			<span id="note_feedback" style="position:relative; top:10px; left:10px;"></span>
		</form>
	</div>
	<div class="clear"></div>
	<div class="separation"></div>
	
	
	<h2>{l s='Messages'} ({count($messages)})</h2>
	{if count($messages)}
		<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
			<tr>
				<th class="center">{l s='Status'}</th>
				<th class="center">{l s='Message'}</th>
				<th class="center">{l s='Sent on'}</th>
			</tr>
			{foreach $messages AS $message}
				<tr>
					<td>{$message['status']}</td>
					<td>
						<a href="index.php?tab=AdminCustomerThreads&id_customer_thread={$message.id_customer_thread}&viewcustomer_thread&token={getAdminToken tab='AdminCustomerThreads'}">
							{$message['message']}...
						</a>
					</td>
					<td>{$message['date_add']}</td>
				</tr>
			{/foreach}
		</table>
		<div class="clear">&nbsp;</div>
	{else}
		{l s='%1$s %2$s has never contacted you' sprintf=[$customer->firstname, $customer->lastname]}
	{/if}
	
	{* display hook specified to this page : AdminCustomers *}
	<div>{hook h="displayAdminCustomers" id_customer=$customer->id}</div>
	
	<div class="clear">&nbsp;</div>
	
	<h2>
		{l s='Groups'} ({count($groups)})
		<a href="{$current}&addcustomer&id_customer={$customer->id}&token={$token}">
			<img src="../img/admin/edit.gif" />
		</a>
	</h2>
	{if $groups AND count($groups)}
		<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
			<colgroup>
				<col width="10px">
				<col width="">
				<col width="70px">
			</colgroup>
			<tr>
				<th height="39px" class="right">{l s='ID'}</th>
				<th class="center">{l s='Name'}</th>
				<th class="center">{l s='Actions'}</th>
			</tr>
		{foreach $groups AS $key => $group}
			<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminGroups&id_group={$group['id_group']}&viewgroup&token={getAdminToken tab='AdminGroups'}'">
				<td class="center">{$group['id_group']}</td>
				<td>{$group['name']}</td>
				<td align="center"><a href="?tab=AdminGroups&id_group={$group['id_group']}&viewgroup&token={getAdminToken tab='AdminGroups'}"><img src="../img/admin/details.gif" /></a></td>
			</tr>
		{/foreach}
		</table>
	{/if}
	<div class="clear">&nbsp;</div>
	
	
	<h2>{l s='Orders'} ({count($orders)})</h2>
	{if $orders AND count($orders)}
		{assign var=count_ok value=count($orders_ok)}
		{if $count_ok}
			<div>
				<h3 style="color:green;font-weight:700">
					{l s='Valid orders:'} {$count_ok} {l s='for'} {$total_ok}
				</h3>
				<table cellspacing="0" cellpadding="0" class="table" style="width:100%; text-align:left;">
					<colgroup>
						<col width="10px">
						<col width="100px">
						<col width="100px">
						<col width="">
						<col width="50px">
						<col width="80px">
						<col width="70px">
					</colgroup>
					<tr>
						<th height="39px" class="center">{l s='ID'}</th>
						<th class="left">{l s='Date'}</th>
						<th class="left">{l s='Payment'}</th>
						<th class="left">{l s='State'}</th>
						<th class="left">{l s='Products'}</th>
						<th class="left">{l s='Total spent'}</th>
						<th class="center">{l s='Actions'}</th>
					</tr>

                    {$janvier = false}
                    {$fevrier = false}
                    {$mars = false}
                    {$avril = false}
                    {$mai = false}
                    {$juin = false}
                    {$juillet = false}
                    {$aout = false}
                    {$septembre = false}
                    {$octobre = false}
                    {$novembre = false}
                    {$decembre = false}
                    {foreach $orders_ok AS $key => $order}
                        {for $i=2010 to 2100}
                        {if isset($orders_mois_classement[$i]["01"]) AND $janvier == false}
                        {$mois = "janvier"}
                        {$mois_num = "01"}
                        {$janvier = true}
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                            <td></td>
                            <td></td>
                            <td><td>
                        </tr>
                    {/if}
                        {if isset($orders_mois_classement[$i]["02"]) AND $fevrier == false}
                            {$mois = "fevrier"}
                            {$fevrier = true}
                            {$mois_num = "02"}

                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["03"]) AND $mars == false}
                            {$mois = "mars"}
                            {$fevrier = true}
                            {$mois_num = "03"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["04"]) AND $avril == false}
                            {$mois = "avril"}
                            {$avril = true}
                            {$mois_num = "04"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>

                                </td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["05"]) AND $mai == false}
                            {$mois = "mai"}
                            {$mai = true}
                            {$mois_num = "05"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["06"]) AND $juin == false}
                            {$mois = "juin"}
                            {$juin = true}
                            {$mois_num = "06"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["07"]) AND $juillet == false}
                            {$mois = "juillet"}
                            {$juillet = true}
                            {$mois_num = "07"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["08"]) AND $aout == false}
                            {$mois = "aout"}
                            {$aout = true}
                            {$mois_num = "08"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["09"]) AND $septembre == false}
                            {$mois = "septembre"}
                            {$septembre = true}
                            {$mois_num = "09"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["10"]) AND $octobre == false}
                            {$mois = "octobre"}
                            {$octobre = true}
                            {$mois_num = "10"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {if isset($orders_mois_classement[$i]["11"]) AND $novembre == false}
                        {$mois = "novembre"}
                        {$novembre = true}
                        {$mois_num = "11"}
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                            <td></td>
                            <td></td>
                            <td><td>
                        </tr>
                    {/if}
                        {if isset($orders_mois_classement[$i]["12"]) AND $decembre == false}
                            {$mois = "decmebre"}
                            {$decembre = true}
                            {$mois_num = "12"}
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight:bold;">Facture du mois de {$mois}-{$i} - <a href="http://lescolisduboucher.com/php/facturemois/facturemois.php?id_customer={$id_customer}&mois_annee={$i}-{$mois_num}">Télécharger</a></td>
                                <td></td>
                                <td></td>
                                <td><td>
                            </tr>
                        {/if}
                        {/for}
						<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
							<td class="center">{$order['id_order']}</td>
							<td>{$order['date_add']}</td>
							<td>{$order['payment']}</td>
							<td>{$order['order_state']}</td>
							<td align="right">{$order['nb_products']}</td>
							<td align="right">{$order['total_paid_real']}</td>
							<td align="center"><a href="?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}"><img src="../img/admin/details.gif" /></a></td>
						</tr>

					{/foreach}
				</table>
			</div>
		{/if}
		{assign var=count_ko value=count($orders_ko)}
		{if $count_ko}
			<div>
				<h3 style="color:red;font-weight:normal;">{l s='Invalid orders:'} {$count_ko}</h3>
				<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
					<colgroup>
						<col width="10px">
						<col width="100px">
						<col width="">
						<col width="">
						<col width="100px">
						<col width="100px">
						<col width="52px">
					</colgroup>
					<tr>
						<th height="39px" class="center">{l s='ID'}</th>
						<th class="center">{l s='Date'}</th>
						<th class="center">{l s='Payment'}</th>
						<th class="center">{l s='State'}</th>
						<th class="center">{l s='Products'}</th>
						<th class="center">{l s='Total spent'}</th>
						<th class="center">{l s='Actions'}</th>
					</tr>

					{foreach $orders_ko AS $key => $order}

                        <tr>
                            <td>ok</td>
                            <td>ok</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><td>
                        </tr>
						<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
							<td class="center">{$order['id_order']}</td>
							<td>{$order['date_add']}</td>
							<td>{$order['payment']}</td>
							<td>{$order['order_state']}</td>
														<td align="right">{$order['nb_products']}</td>
							<td align="right">{$order['total_paid_real']}</td>
							<td align="center"><a href="?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}"><img src="../img/admin/details.gif" /></a></td>
						</tr>
					{/foreach}
				</table>
			</div>
			<div class="clear">&nbsp;</div>
		{/if}
	{else}
		{l s='%1$s %2$s has not placed any orders yet' sprintf=[$customer->firstname, $customer->lastname]}
	{/if}
	
	{if $products AND count($products)}
	<div class="clear">&nbsp;</div>
		<h2>{l s='Products'} ({count($products)})</h2>
		<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
					<colgroup>
						<col width="50px">
						<col width="">
						<col width="60px">
						<col width="70px">
					</colgroup>
			<tr>
				<th height="39px" class="center">{l s='Date'}</th>
				<th class="center">{l s='Name'}</th>
				<th class="center">{l s='Quantity'}</th>
				<th class="center">{l s='Actions'}</th>
			</tr>
			{foreach $products AS $key => $product}
				<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminOrders&id_order={$product['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
					<td>{$product['date_add']}</td>
					<td>{$product['product_name']}</td>
					<td align="right">{$product['product_quantity']}</td>
					<td align="center"><a href="?tab=AdminOrders&id_order={$product['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}"><img src="../img/admin/details.gif" /></a></td>
				</tr>
			{/foreach}
		</table>
	{/if}
	<div class="clear">&nbsp;</div>
	
	<h2>{l s='Addresses'} ({count($addresses)})</h2>
	{if count($addresses)}
		<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
					<colgroup>
						<col width="120px">
						<col width="120px">
						<col width="">
						<col width="100px">
						<col width="170px">
						<col width="70px">
					</colgroup>
			<tr>
				<th height="39px">{l s='Company'}</th>
				<th>{l s='Name'}</th>
				<th>{l s='Address'}</th>
				<th>{l s='Country'}</th>
				<th>{l s='Phone number(s)'}</th>
				<th>{l s='Actions'}</th>
			</tr>
			{foreach $addresses AS $key => $address}
				<tr {if $key %2}class="alt_row"{/if}>
					<td>{if $address['company']}{$address['company']}{else}--{/if}</td>
					<td>{$address['firstname']} {$address['lastname']}</td>
					<td>{$address['address1']} {if $address['address2']}{$address['address2']}{/if} {$address['postcode']} {$address['city']}</td>
					<td>{$address['country']}</td>
					<td class="right">
						{if $address['phone']}
							{$address['phone']}
							{if $address['phone_mobile']}<br />{$address['phone_mobile']}{/if}
						{else}
							{if $address['phone_mobile']}<br />{$address['phone_mobile']}{else}--{/if}
						{/if}
					</td>
					<td align="center">
						<a href="?tab=AdminAddresses&id_address={$address['id_address']}&addaddress&token={getAdminToken tab='AdminAddresses'}"><img src="../img/admin/edit.gif" /></a>
						<a href="?tab=AdminAddresses&id_address={$address['id_address']}&deleteaddress&token={getAdminToken tab='AdminAddresses'}"><img src="../img/admin/delete.gif" /></a>
					</td>
				</tr>
			{/foreach}
		</table>
	{else}
		{l s='%1$s %2$s has not registered any addresses yet' sprintf=[$customer->firstname, $customer->lastname]}
	{/if}
	
	<div class="clear">&nbsp;</div>
	<h2>{l s='Vouchers'} ({count($discounts)})</h2>
	{if count($discounts)}
		<table cellspacing="0" cellpadding="0" class="table">
			<tr>
				<th>{l s='ID'}</th>
				<th>{l s='Code'}</th>
				<th>{l s='Name'}</th>
				<th>{l s='Status'}</th>
				<th>{l s='Actions'}</th>
			</tr>
		{foreach $discounts AS $key => $discount}
			<tr {if $key %2}class="alt_row"{/if}>
				<td align="center">{$discount['id_cart_rule']}</td>
				<td>{$discount['code']}</td>
				<td>{$discount['name']}</td>
				<td align="center"><img src="../img/admin/{if $discount['active']}enabled.gif{else}disabled.gif{/if}" alt="{l s='Status'}" title="{l s='Status'}" /></td>
				<td align="center">
					<a href="?tab=AdminCartRules&id_cart_rule={$discount['id_cart_rule']}&addcart_rule&token={getAdminToken tab='AdminCartRules'}"><img src="../img/admin/edit.gif" /></a>
					<a href="?tab=AdminCartRules&id_cart_rule={$discount['id_cart_rule']}&deletecart_rule&token={getAdminToken tab='AdminCartRules'}"><img src="../img/admin/delete.gif" /></a>
				</td>
			</tr>
		{/foreach}
		</table>
	{else}
		{l s='%1$s %2$s has no discount vouchers' sprintf=[$customer->firstname, $customer->lastname]}.
	{/if}
	<div class="clear">&nbsp;</div>
	
	<div>
		<h2>{l s='Carts'} ({count($carts)})</h2>
		{if $carts AND count($carts)}
			<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
				<colgroup>
					<col width="50px">
					<col width="150px">
					<col width="">
					<col width="70px">
					<col width="50px">
				</colgroup>
				<tr>
					<th height="39px" class="center">{l s='ID'}</th>
					<th class="center">{l s='Date'}</th>
					<th class="center">{l s='Carrier'}</th>
					<th class="center">{l s='Total'}</th>
					<th class="center">{l s='Actions'}</th>
				</tr>
				{foreach $carts AS $key => $cart}
					<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminCarts&id_cart={$cart['id_cart']}&viewcart&token={getAdminToken tab='AdminCarts'}'">
						<td class="center">{$cart['id_cart']}</td>
						<td>{$cart['date_add']}</td>
						<td>{$cart['name']}</td>
						<td align="right">{$cart['total_price']}</td>
						<td align="center"><a href="index.php?tab=AdminCarts&id_cart={$cart['id_cart']}&viewcart&token={getAdminToken tab='AdminCarts'}"><img src="../img/admin/details.gif" /></a></td>
					</tr>
				{/foreach}
			</table>
		{else}
			{l s='No cart available'}.
		{/if}
	</div>
	
	{if count($interested)}
		<div>
		<h2>{l s='Products'} ({count($interested)})</h2>
			<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
				<colgroup>
					<col width="10px">
					<col width="">
					<col width="50px">
				</colgroup>
				{foreach $interested as $key => $p}
					<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '{$p['url']}'">
						<td>{$p['id']}</td>
						<td>{$p['name']}</td>
						<td align="center"><a href="{$p['url']}"><img src="../img/admin/details.gif" /></a></td>
					</tr>
				{/foreach}
			</table>
		</div>
	{/if}
				
	<div class="clear">&nbsp;</div>
	
	{* Last connections *}
	{if count($connections)}
		<h2>{l s='Last connections'}</h2>
		<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
				<colgroup>
					<col width="150px">
					<col width="100px">
					<col width="100px">
					<col width="">
					<col width="150px">
				</colgroup>
			<tr>
				<th height="39px;">{l s='Date'}</th>
				<th>{l s='Pages viewed'}</th>
				<th>{l s='Total time'}</th>
				<th>{l s='Origin'}</th>
				<th>{l s='IP Address'}</th>
			</tr>
			{foreach $connections as $connection}
				<tr>
					<td>{$connection['date_add']}</td>
					<td>{$connection['pages']}</td>
					<td>{$connection['time']}</td>
					<td>{$connection['http_referer']}</td>
					<td>{$connection['ipaddress']}</td>
				</tr>
			{/foreach}
		</table>
		<div class="clear">&nbsp;</div>
	{/if}
	
	{if count($referrers)}
		<h2>{l s='Referrers'}</h2>
		<table cellspacing="0" cellpadding="0" class="table">
			<tr>
				<th style="width: 200px">{l s='Date'}</th>
				<th style="width: 200px">{l s='Name'}</th>
				{if $shop_is_feature_active}<th style="width: 200px">{l s='Shop'}</th>{/if}
			</tr>
			{foreach $referrers as $referrer}
				<tr>
					<td>{$referrer['date_add']}</td>
					<td>{$referrer['name']}</td>
					{if $shop_is_feature_active}<td>{$referrer['shop_name']}</td>{/if}
				</tr>
			{/foreach}
		</table>
	{/if}
{/block}
</div>	
		<div class="clear">&nbsp;</div>
