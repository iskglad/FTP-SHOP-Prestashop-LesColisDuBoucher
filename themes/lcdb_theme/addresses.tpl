

{*
** Retro compatibility for PrestaShop version < 1.4.2.5 with a recent theme
*}

{* Two variable are necessaries to display the address with the new layout system *}
{* Will be deleted for 1.5 version and more *}
{if !isset($multipleAddresses)}
	{$ignoreList.0 = "id_address"}
	{$ignoreList.1 = "id_country"}
	{$ignoreList.2 = "id_state"}
	{$ignoreList.3 = "id_customer"}
	{$ignoreList.4 = "id_manufacturer"}
	{$ignoreList.5 = "id_supplier"}
	{$ignoreList.6 = "date_add"}
	{$ignoreList.7 = "date_upd"}
	{$ignoreList.8 = "active"}
	{$ignoreList.9 = "deleted"}

	{* PrestaShop < 1.4.2 compatibility *}
	{if isset($addresses)}
		{$address_number = 0}
		{foreach from=$addresses key=k item=address}
			{counter start=0 skip=1 assign=address_key_number}
			{foreach from=$address key=address_key item=address_content}
				{if !in_array($address_key, $ignoreList)}
					{$multipleAddresses.$address_number.ordered.$address_key_number = $address_key}
					{$multipleAddresses.$address_number.formated.$address_key = $address_content}
					{counter}
				{/if}
			{/foreach}
		{$multipleAddresses.$address_number.object = $address}
		{$address_number = $address_number  + 1}
		{/foreach}
	{/if}
{/if}

{* Define the style if it doesn't exist in the PrestaShop version*}
{* Will be deleted for 1.5 version and more *}
{if !isset($addresses_style)}
	{$addresses_style.company = 'address_company'}
	{$addresses_style.vat_number = 'address_company'}
	{$addresses_style.firstname = 'address_name'}
	{$addresses_style.lastname = 'address_name'}
	{$addresses_style.address1 = 'address_address1'}
	{$addresses_style.address2 = 'address_address2'}
	{$addresses_style.city = 'address_city'}
	{$addresses_style.country = 'address_country'}
	{$addresses_style.phone = 'address_phone'}
	{$addresses_style.phone_mobile = 'address_phone_mobile'}
	{$addresses_style.alias = 'address_title'}
    {$addresses_style.code = 'address_code'}
    {$addresses_style.floor = 'address_floor'}
{/if}

<script type="text/javascript">
//<![CDATA[
	{literal}
	$(document).ready(function()
	{
			resizeAddressesBox();
	});
	{/literal}
//]]>
</script>

{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='My addresses'}{/capture}

<div id="columns" class="content clearfix">
	<div id="left_column">
		{include file="./account-left-col.tpl"}
	</div><!-- / #left_column -->
	<div id="center_column" class="address">
		<div class="big-bloc">
			<h1>Mes adresses</h1>
			<p>Choisissez vos adresses de facturation et de livraison. Ces dernières seront présélectionnées lors de vos 
				commandes. Vous pouvez également ajouter d’autres adresses, ce qui est particulièrement intéressant pour 
				envoyer des cadeaux ou recevoir votre commande au bureau.</p>
			<div id="address-list">
				{if isset($multipleAddresses) && $multipleAddresses}
					{assign var="adrs_style" value=$addresses_style}
					{foreach from=$multipleAddresses item=address name=myLoop}

						<div class="details-address">
							<p class="information title">{$address.object.alias}</p>
							<p>{$address.object.firstname} {$address.object.lastname}</p>
							<p>{$address.object.company}</p>
							<p>{$address.object.address1}</p>
							<p>{$address.object.address2}</p>
							<p>{$address.object.postcode} {$address.object.city}</p>
							<p>{$address.object.country}</p>
							<p>{$address.object.phone}</p>
							<p>{$address.object.phone_mobile}</p>

							<div class="action-link">
								<a href="{$link->getPageLink('address', true, null, "id_address={$address.object.id|intval}")}" title="Modifier cette adresse"><span>&rarr;</span>Modifier cette adresse</a>
								<a href="{$link->getPageLink('address', true, null, "id_address={$address.object.id|intval}&delete")}" onclick="return confirm('{l s='Are you sure?' js=1}');" title="Supprimer"><span>&rarr;</span>Supprimer</a>
							</div>
						</div>
					{/foreach}
				{else}
					<p class="warning">
						{l s='No addresses available.'}&nbsp;
						<a href="{$link->getPageLink('address', true)}">{l s='Add new address'}</a>
					</p>
				{/if}
			</div>
			<div class="action">
				<a href="{$link->getPageLink('address', true)}" title="{l s='Add an address'}" class="red-button gradient">{l s='Add an address'}</a>
			</div>
		</div>
	</div><!-- / #center_column -->
</div><!-- / .content -->