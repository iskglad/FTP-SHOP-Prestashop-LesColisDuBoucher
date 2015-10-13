
{capture name=path}{l s='Your addresses'}{/capture}

<script type="text/javascript">
// <![CDATA[
idSelectedCountry = {if isset($smarty.post.id_state)}{$smarty.post.id_state|intval}{else}{if isset($address->id_state)}{$address->id_state|intval}{else}false{/if}{/if};
countries = new Array();
countriesNeedIDNumber = new Array();
countriesNeedZipCode = new Array();
{foreach from=$countries item='country'}
	{if isset($country.states) && $country.contains_states}
		countries[{$country.id_country|intval}] = new Array();
		{foreach from=$country.states item='state' name='states'}
			countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.id_state}', 'name' : '{$state.name|addslashes}'{rdelim});
		{/foreach}
	{/if}
	{if $country.need_identification_number}
		countriesNeedIDNumber.push({$country.id_country|intval});
	{/if}
	{if isset($country.need_zip_code)}
		countriesNeedZipCode[{$country.id_country|intval}] = {$country.need_zip_code};
	{/if}
{/foreach}
$(function(){ldelim}
	$('.id_state option[value={if isset($smarty.post.id_state)}{$smarty.post.id_state|intval}{else}{if isset($address->id_state)}{$address->id_state|intval}{/if}{/if}]').attr('selected', true);
{rdelim});
{if $vat_management}
{literal}
	$(document).ready(function() {
		$('#company').blur(function(){
			vat_number();
		});
		vat_number();
		function vat_number()
		{
			if ($('#company').val() != '')
				$('#vat_number').show();
			else
				$('#vat_number').hide();
		}
	});
{/literal}
{/if}
//]]>
</script>

<div id="columns" class="content clearfix">
	<div id="left_column">
		{include file="./account-left-col.tpl"}
	</div><!-- / #left_column -->
	<div id="center_column" class="address">
		<div class="big-bloc">
			<h1>Mes adresses</h1>
			<p>Pour ajouter une adresse, veuillez remplir le formulaire ci-dessous.</p>
			{include file="$tpl_dir./errors.tpl"}
			<p class="information">Les champs suivis d'un astérisque (<span class="asterisque">*</span>) sont obligatoires.</p>
			<div class="warning"></div>
			<form action="{$link->getPageLink('address', true)}" method="post">
				<div>
					{assign var="stateExist" value="false"}
					{foreach from=$ordered_adr_fields item=field_name}
						{if $field_name eq 'firstname'}
							<label for="firstname">Prénom <span class="asterisque">*</span></label>
							<input type="text" id="firstname" name="firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{else}{if isset($address->firstname)}{$address->firstname}{/if}{/if}" required />
						{/if}
						{if $field_name eq 'lastname'}
							<label for="lastname">Nom <span class="asterisque">*</span></label>
							<input type="text" id="lastname" name="lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{else}{if isset($address->lastname)}{$address->lastname}{/if}{/if}" required />
						{/if}
						{if $field_name eq 'company'}
							<input type="hidden" name="token" value="{$token}" />
							<label for="company">{l s='Company'}</label>
							<input type="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{else}{if isset($address->company)}{$address->company}{/if}{/if}" />
						{/if}
						{if $field_name eq 'address1'}
							<label for="address1">Adresse <span class="asterisque">*</span></label>
							<input type="text" id="address1" name="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{else}{if isset($address->address1)}{$address->address1}{/if}{/if}" required />
						{/if}
						{if $field_name eq 'address2'}
							<label for="address2">Complément d'adresse</label>
							<input type="text" id="address2" name="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{else}{if isset($address->address2)}{$address->address2}{/if}{/if}" />
						{/if}
						{if $field_name eq 'postcode'}
							<label for="postal_code">Code postal <span class="asterisque">*</span></label>
							<input type="text"  id="postcode" name="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{if isset($address->postcode)}{$address->postcode}{/if}{/if}" onkeyup="$('#postcode').val($('#postcode').val().toUpperCase());" required />
						{/if}
						{if $field_name eq 'city'}
							<label for="city">Ville <span class="asterisque">*</span></label>
							<input type="text"  name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{else}{if isset($address->city)}{$address->city}{/if}{/if}" required />
						{/if}
						{if $field_name eq 'Country:name' || $field_name eq 'country'}
							<div class="select">
								<label for="id_country">Pays :</label>
								<select id="id_country" name="id_country">{$countries_list}</select>
							</div>
						{/if}
                        {if $field_name eq 'code'}
                            <label for="code">Code d'accès</label>
                            <input type="text"  name="code" id="code" value="{if isset($smarty.post.code)}{$smarty.post.code}{else}{if isset($address->code)}{$address->code}{/if}{/if}" />
                        {/if}
                        {if $field_name eq 'floor'}
                            <label for="floor">Étage</label>
                            <input type="text"  name="floor" id="floor" value="{if isset($smarty.post.floor)}{$smarty.post.floor}{else}{if isset($address->floor)}{$address->floor}{/if}{/if}" />
                        {/if}
					{/foreach}
					
					<label for="comment">Informations supplémentaires</label>
					<textarea id="comment" name="other">{if isset($smarty.post.other)}{$smarty.post.other}{else}{if isset($address->other)}{$address->other}{/if}{/if}</textarea>
					<label for="address_title">Donnez un titre à cette adresse pour la retrouver plus facilement <span class="asterisque">*</span></label>
					<input type="text" id="address_title" name="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{else if isset($address->alias)}{$address->alias}{/if}" required />
					
				</div>
				<div>
					<p class="information">Entrez au minimun un numéro de téléphone <span class="asterisque">*</span></p>
					<label for="phone">Téléphone Fixe</label>
					<input type="text"  id="phone" name="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{else}{if isset($address->phone)}{$address->phone}{/if}{/if}"/>
					<label for="mobile">Téléphone Portable</label>
					<input type="text" id="phone_mobile" name="phone_mobile" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{else}{if isset($address->phone_mobile)}{$address->phone_mobile}{/if}{/if}" />
				</div>
				<div class="action">
					{if isset($id_address)}<input type="hidden" name="id_address" value="{$id_address|intval}" />{/if}
					{if isset($back)}<input type="hidden" name="back" value="{$back}" />{/if}
					{if isset($mod)}<input type="hidden" name="mod" value="{$mod}" />{/if}
					{if isset($select_address)}<input type="hidden" name="select_address" value="{$select_address|intval}" />{/if}
					<button type="submit" name="submitAddress" id="submitAddress" class="red-button gradient">Valider cette adresse</button>
				</div>
			</form>
		</div>
	</div><!-- / #center_column -->
</div>
