
<script type="text/javascript">
// <![CDATA[
idSelectedCountry = {if isset($smarty.post.id_state)}{$smarty.post.id_state|intval}{else}false{/if};
countries = new Array();
countriesNeedIDNumber = new Array();
countriesNeedZipCode = new Array();
{if isset($countries)}
	{foreach from=$countries item='country'}
		{if isset($country.states) && $country.contains_states}
			countries[{$country.id_country|intval}] = new Array();
			{foreach from=$country.states item='state' name='states'}
				countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.id_state|intval}', 'name' : '{$state.name|addslashes}'{rdelim});
			{/foreach}
		{/if}
		{if $country.need_identification_number}
			countriesNeedIDNumber.push({$country.id_country|intval});
		{/if}
		{if isset($country.need_zip_code)}
			countriesNeedZipCode[{$country.id_country|intval}] = {$country.need_zip_code};
		{/if}
	{/foreach}
{/if}
$(function(){ldelim}
	$('.id_state option[value={if isset($smarty.post.id_state)}{$smarty.post.id_state|intval}{else}{if isset($address)}{$address->id_state|intval}{/if}{/if}]').attr('selected', true);
{rdelim});
//]]>
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
</script>


{capture name=path}{l s='Login'}{/capture}
{assign var='current_step' value='login'}


<div id="columns" class="content clearfix">
	<div class="bloc-checkout">
		{include file="$tpl_dir./errors.tpl"}
		
		{assign var='stateExist' value=false}
		{if !isset($email_create)}
		
			<script type="text/javascript">
				{literal}
				$(document).ready(function(){
					// Retrocompatibility with 1.4
					if (typeof baseUri === "undefined" && typeof baseDir !== "undefined")
					baseUri = baseDir;
					$('#create-account_form').submit(function(){
						submitFunction();
						return false;
					});
					$('#SubmitCreate').click(function(){
						submitFunction();
					});
				});
				function submitFunction()
				{
					$('#create_account_error').html('').hide();
					//send the ajax request to the server
					$.ajax({
						type: 'POST',
						url: baseUri,
						async: true,
						cache: false,
						dataType : "json",
						data: {
							controller: 'authentication',
							SubmitCreate: 1,
							ajax: true,
							email_create: $('#email_create').val(),
							token: token
						},
						success: function(jsonData)
						{
							if (jsonData.hasError)
							{
								var errors = '';
								for(error in jsonData.errors)
									//IE6 bug fix
									if(error != 'indexOf')
										errors += '<li>'+jsonData.errors[error]+'</li>';
								$('#create_account_error').html('<ol>'+errors+'</ol>').show();
							}
							else
							{
								// adding a div to display a transition
								$('#center_column').html('<div id="noSlide">'+$('#center_column').html()+'</div>');
								$('#noSlide').fadeOut('slow', function(){
									$('#noSlide').html(jsonData.page);
									// update the state (when this file is called from AJAX you still need to update the state)
									bindStateInputAndUpdate();
								});
								$('#noSlide').fadeIn('slow');
								document.location = '#account-creation';
							}
						},
						error: function(XMLHttpRequest, textStatus, errorThrown)
						{
							alert("TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
						}
					});
				}
				{/literal}
			</script>
			
			{if isset($inOrderProcess) && $inOrderProcess}
				{include file="./order-steps.tpl"}
				<form action="{$link->getPageLink('authentication', true, NULL, "back=$back")}" method="post" id="new_account_form" class="std clearfix">
					<fieldset>
						<h3>{l s='Instant Checkout'}</h3>
						<div id="opc_account_form" style="display: block; ">
							<!-- Account -->
							<p class="required text">
								<label for="guest_email">{l s='E-mail address'} <sup>*</sup></label>
								<input type="text" class="text" id="guest_email" name="guest_email" value="{if isset($smarty.post.guest_email)}{$smarty.post.guest_email}{/if}">
							</p>
							<p class="radio required">
								<span>{l s='Title'}</span>
								{foreach from=$genders key=k item=gender}
									<input type="radio" name="id_gender" id="id_gender{$gender->id}" value="{$gender->id}" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == $gender->id}checked="checked"{/if} />
									<label for="id_gender{$gender->id}" class="top">{$gender->name}</label>
								{/foreach}
							</p>
							<p class="required text">
								<label for="firstname">{l s='First name'} <sup>*</sup></label>
								<input type="text" class="text" id="firstname" name="firstname" onblur="$('#customer_firstname').val($(this).val());" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{/if}">
								<input type="hidden" class="text" id="customer_firstname" name="customer_firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{/if}">
							</p>
							<p class="required text">
								<label for="lastname">{l s='Last name'} <sup>*</sup></label>
								<input type="text" class="text" id="lastname" name="lastname" onblur="$('#customer_lastname').val($(this).val());" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{/if}">
								<input type="hidden" class="text" id="customer_lastname" name="customer_lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{/if}">
							</p>
							<p class="select">
								<span>{l s='Date of Birth'}</span>
								<select id="days" name="days">
									<option value="">-</option>
									{foreach from=$days item=day}
										<option value="{$day}" {if ($sl_day == $day)} selected="selected"{/if}>{$day}&nbsp;&nbsp;</option>
									{/foreach}
								</select>
							{*
									  {l s='January'}
									  {l s='February'}
									  {l s='March'}
									  {l s='April'}
									  {l s='May'}
									  {l s='June'}
									  {l s='July'}
									  {l s='August'}
									  {l s='September'}
									  {l s='October'}
									  {l s='November'}
									  {l s='December'}
								  *}
								<select id="months" name="months">
									<option value="">-</option>
									{foreach from=$months key=k item=month}
										<option value="{$k}" {if ($sl_month == $k)} selected="selected"{/if}>{l s=$month}&nbsp;</option>
									{/foreach}
								</select>
								<select id="years" name="years">
									<option value="">-</option>
									{foreach from=$years item=year}
										<option value="{$year}" {if ($sl_year == $year)} selected="selected"{/if}>{$year}&nbsp;&nbsp;</option>
									{/foreach}
								</select>
							</p>
							{if isset($newsletter) && $newsletter}
								<p class="checkbox">
									<input type="checkbox" name="newsletter" id="newsletter" value="1" {if isset($smarty.post.newsletter) && $smarty.post.newsletter == '1'}checked="checked"{/if}>
									<label for="newsletter">{l s='Sign up for our newsletter'}</label>
								</p>
								<p class="checkbox">
									<input type="checkbox" name="optin" id="optin" value="1" {if isset($smarty.post.optin) && $smarty.post.optin == '1'}checked="checked"{/if}>
									<label for="optin">{l s='Receive special offers from our partners'}</label>
								</p>
							{/if}
							<h3>{l s='Delivery address'}</h3>
							{foreach from=$dlv_all_fields item=field_name}
								{if $field_name eq "company"}
									<p class="text">
										<label for="company">{l s='Company'}</label>
										<input type="text" class="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{/if}" />
									</p>
									{elseif $field_name eq "vat_number"}
									<div id="vat_number" style="display:none;">
										<p class="text">
											<label for="vat_number">{l s='VAT number'}</label>
											<input type="text" class="text" name="vat_number" value="{if isset($smarty.post.vat_number)}{$smarty.post.vat_number}{/if}" />
										</p>
									</div>
									{elseif $field_name eq "address1"}
									<p class="required text">
										<label for="address1">{l s='Address'} <sup>*</sup></label>
										<input type="text" class="text" name="address1" id="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{/if}">
									</p>
									{elseif $field_name eq "postcode"}
									<p class="required postcode text">
										<label for="postcode">{l s='Zip / Postal Code'} <sup>*</sup></label>
										<input type="text" class="text" name="postcode" id="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{/if}" onblur="$('#postcode').val($('#postcode').val().toUpperCase());">
									</p>
									{elseif $field_name eq "city"}
									<p class="required text">
										<label for="city">{l s='City'} <sup>*</sup></label>
										<input type="text" class="text" name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{/if}">
									</p>
									<!--
										   if customer hasn't update his layout address, country has to be verified
										   but it's deprecated
									   -->
									{elseif $field_name eq "Country:name" || $field_name eq "country"}
									<p class="required select">
										<label for="id_country">{l s='Country'} <sup>*</sup></label>
										<select name="id_country" id="id_country">
											<option value="">-</option>
											{foreach from=$countries item=v}
												<option value="{$v.id_country}" {if ($sl_country == $v.id_country)} selected="selected"{/if}>{$v.name}</option>
											{/foreach}
										</select>
									</p>
									{elseif $field_name eq "State:name"}
									{assign var='stateExist' value=true}

									<p class="required id_state select">
										<label for="id_state">{l s='State'} <sup>*</sup></label>
										<select name="id_state" id="id_state">
											<option value="">-</option>
										</select>
									</p>
									{elseif $field_name eq "phone"}
									<p class="required text">
										<label for="phone">{l s='Phone'} <sup>*</sup></label>
										<input type="text" class="text" name="phone" id="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}">
									</p>
								{/if}
							{/foreach}
							{if $stateExist eq false}
								<p class="required id_state select">
									<label for="id_state">{l s='State'} <sup>*</sup></label>
									<select name="id_state" id="id_state">
										<option value="">-</option>
									</select>
								</p>
							{/if}
							<input type="hidden" name="alias" id="alias" value="{l s='My address'}">
							<input type="hidden" name="is_new_customer" id="is_new_customer" value="0">
							<!-- END Account -->
						</div>
					</fieldset>
					<fieldset class="account_creation dni">
						<h3>{l s='Tax identification'}</h3>

						<p class="required text">
							<label for="dni">{l s='Identification number'}</label>
							<input type="text" class="text" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{/if}" />
							<span class="form_info">{l s='DNI / NIF / NIE'}</span>
						</p>
					</fieldset>
					{$HOOK_CREATE_ACCOUNT_FORM}
					<p class="cart_navigation required submit">
						<span><sup>*</sup>{l s='Required field'}</span>
						<input type="hidden" name="display_guest_checkout" value="1" />
						<input type="submit" class="exclusive" name="submitGuestAccount" id="submitGuestAccount" value="{l s='Continue'}">
					</p>
				</form>
			{else}
				<div class="clearfix">
					<div class="bloc content-register">
						<h2>Première commande chez les Colis du Boucher ?</h2>
						<p>C'est le moment de <span class="bold">créer votre compte</span>.</p>
						<p>Vous bénéficierez ainsi de tous les avantages du village et gagnerez du temps lors de vos prochaines commandes sur le site.</p>
						<p>L'inscription est <span class="bold">simple, rapide et sans engagement de commande</span>.</p>
						<div class="register-button">
							<a href="{$link->getPageLink('authentication', true)}?create_account=true" class="red-button gradient">créer mon compte</a>
						</div>  
					</div>
					<div class="bloc content-login">
						<h2>Vous avez déja commandé ou disposez d’un compte ?</h2>
						<form action="{$link->getPageLink('authentication', true)}" method="post" >
							
							<label for="mail-address">Adresse e-mail</label>
							<input type="text" id="mail-address" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|stripslashes}{/if}" />
							<label for="password">Mot de passe</label>
							<input type="password" id="password" name="passwd" value="{if isset($smarty.post.passwd)}{$smarty.post.passwd|stripslashes}{/if}"/>
							<a href="{$link->getPageLink('password')}" class="green-title" title="Mot de passe oublié ?">Mot de passe oublié ?</a>

							<label class="checkbox" for="remember-me"><input type="checkbox" id="remember-me" name="remember-me" />Se souvenir de moi lors des prochaines visites</label>

							<div class="login-button">
								{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
								<input type="submit" name="SubmitLogin" class="red-button gradient" value="me connecter" />
							</div>
						</form>
					</div>
				</div>
			{/if}
		{else}
			
			<div class="clearfix">
				<div class="bloc content-register-form">
					<h1>Création de compte</h1>
					<p>Inscrivez-vous grâce au formulaire ci-dessous pour pouvoir poursuivre votre commande. Les champs suivis d'un astérisque ( <span class="required">*</span> ) sont obligatoires.</p>
					<form action="{$link->getPageLink('authentication', true)}" method="post" id="account-creation_form" class="std">
						{$HOOK_CREATE_ACCOUNT_TOP}
						
						<div class="left-side">
							<label>Civilité <span class="required">*</span></label>
							{foreach from=$genders key=k item=gender}
								<label class="radio" for="id_gender{$gender->id}">
									<input type="radio" name="id_gender" id="id_gender{$gender->id}" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == $gender->id}checked="checked"{/if} value="{$gender->id}" />
									{$gender->name}
								</label>
							{/foreach}
							<label for="nom">Nom <span class="required">*</span></label>
							<input type="text" id="customer_lastname" name="customer_lastname" value="{if isset($smarty.post.customer_lastname)}{$smarty.post.customer_lastname}{/if}" />
							<label for="prenom">Prénom <span class="required">*</span></label>
							<input type="text" id="customer_firstname" name="customer_firstname" value="{if isset($smarty.post.customer_firstname)}{$smarty.post.customer_firstname}{/if}" />
							<label for="prenom">Date de naissance <span class="required">*</span></label>
							<div class="clearfix">
								<div class="birthdate" id="days">
									<select name="days">
										<option value="">-</option>
										{foreach from=$days item=day}
											<option value="{$day}" {if ($sl_day == $day)} selected="selected"{/if}>{$day}&nbsp;&nbsp;</option>
										{/foreach}
									</select>
								</div>
								<div class="birthdate" id="months">
									{*
										{l s='January'}
										{l s='February'}
										{l s='March'}
										{l s='April'}
										{l s='May'}
										{l s='June'}
										{l s='July'}
										{l s='August'}
										{l s='September'}
										{l s='October'}
										{l s='November'}
										{l s='December'}
									*}
									<select name="months">
										<option value="">-</option>
										{foreach from=$months key=k item=month}
											<option value="{$k}" {if ($sl_month == $k)} selected="selected"{/if}>{l s=$month}&nbsp;</option>
										{/foreach}
									</select>
								</div>
								<div class="birthdate" id="years">
									<select name="years">
										<option value="">-</option>
										{foreach from=$years item=year}
											<option value="{$year}" {if ($sl_year == $year)} selected="selected"{/if}>{$year}&nbsp;&nbsp;</option>
										{/foreach}
									</select>
								</div>

								<script type="text/javascript">
									$(document).ready(function(){
										$('.birthdate select').selectbox();
									});
								</script>
							</div>
							<label for="adresse">Adresse</label>
							<input type="text" id="adresse" name="adresse" value="{if isset($smarty.post.adresse)}{$smarty.post.adresse}{/if}" />
							<label for="adresseplus">Complément d'adresse</label>
							<input type="text" id="adresseplus" name="adresseplus" value="{if isset($smarty.post.adresseplus)}{$smarty.post.adresseplus}{/if}" />
							<label for="codepostal">Code postal</label>
							<input type="text" id="codepostal" name="codepostal" value="{if isset($smarty.post.codepostal)}{$smarty.post.codepostal}{/if}" />
							<label for="ville">Ville</label>
							<input type="text" id="ville" name="ville" value="{if isset($smarty.post.ville)}{$smarty.post.ville}{/if}"  />
							<p>Veuillez saisir au moins un numéro de téléphone</p>
							<label for="telfixe">Téléphone fixe</label>
							<input type="text" id="telfixe" name="telfixe" value="{if isset($smarty.post.telfixe)}{$smarty.post.telfixe}{/if}"  />
							<label for="portable">Téléphone portable</label>
							<input type="text" id="portable" name="portable" value="{if isset($smarty.post.portable)}{$smarty.post.portable}{/if}"  />
						</div>
						
						<div class="right-side">
							<label for="email">Adresse e-mail <span class="required">*</span></label>
							<input type="text" id="email" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email}{/if}" id="email" name="email" />
							<label for="emailconf">Confirmez votre adresse e-mail <span class="required">*</span></label>
							<input type="text" id="emailconf" name="emailconf" value="{if isset($smarty.post.emailconf)}{$smarty.post.emailconf}{/if}" />
							<label for="password">Mot de passe <span class="required">*</span></label>
							<input type="password" name="passwd" id="passwd" />
							<label for="passwordconf">Confirmez votre mot de passe <span class="required">*</span></label>
							<input type="password" id="passwordconf" name="passwordconf" />
							
							<label id="come-from-ce">Venez-vous de la part d'un comité d'entreprise/groupement ?</label>
							<label class="radio" for="ce-oui" id="ce-more">
								<input type="radio" name="ce" id="ce-oui" value="1" {if isset($smarty.post.ce) && $smarty.post.ce == 1} checked="checked"{/if}/>
								Oui
							</label>
							<label class="radio" for="ce-non" id="ce-less">
								<input type="radio" name="ce" id="ce-non" value="0" {if isset($smarty.post.ce) && $smarty.post.ce == 1} checked="checked"{/if}/>
								Non
							</label>
							<div id="from-ce">
								<label for="entreprise">De quel(le) entreprise/groupement ? <span class="required">*</span></label>
								<select id="entreprise" name="groupments" disabled>
                                    {foreach from=$groups item=group}
                                        {if $group.is_group == true}
                                            <option value="{$group.id_group}">{$group.name}</option>
                                        {/if}
                                    {/foreach}
								</select>
							</div>
							
							<label for="parrain">Si vous avez été parrainé, veuillez saisir l'e-mail de votre parrain</label>
							<input type="text" id="referralprogram" name="referralprogram" value="{if isset($smarty.post.referralprogram)}{$smarty.post.referralprogram|escape:'htmlall':'UTF-8'}{/if}"/>
							
							{if $newsletter}
								<label>J'accepte de recevoir par e-mail des offres, anecdotes de la part des Colis du Boucher <span class="required">*</span></label>
								<p class="annotation">Les informations vous concernant ne seront jamais vendues, louées ou cédées à des tiers.</p>
								<label class="radio" for="newsletter-oui">
									<input type="radio" name="newsletter" id="newsletter-oui" value="1" {if isset($smarty.post.newsletter) && $smarty.post.newsletter == 1} checked="checked"{/if} />
									Oui
								</label>
								<label class="radio" for="newsletter-non">
									<input type="radio" name="newsletter" id="newsletter-non" value="0" {if isset($smarty.post.newsletter) && $smarty.post.newsletter == 0} checked="checked"{/if} />
									Non
								</label>
							{/if}

							<script type="text/javascript">
							function openCGV() {
								var url = '{$link->getCMSLink('13', 'Conditions générales de vente')}&content_only=1',
									top = (screen.height/2)-(500/2),
									left = (screen.width/2)-(960/2);
								window.open(url, 'Conditions générales de vente', 'width=960,height=500,scrollbars=yes,top='+top+',left='+left);
							}
							</script>
							
							<label class="checkbox" for="cgu"><input type="checkbox" id="cgu" name="cgu" />J'accepte les <a href="javascript:void(0)" onclick="openCGV();">conditions générales de vente</a> des Colis du Boucher <span class="required">*</span></label>
							
							<div class="register-button">
								<input type="hidden" name="email_create" value="1" />
								<input type="hidden" name="is_new_customer" value="1" />
								{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
								<input type="submit" class="red-button gradient" value="m'inscrire" name="submitAccount" id="submitAccount" />
							</div>  
						</div>
						
						{if isset($PS_REGISTRATION_PROCESS_TYPE) && $PS_REGISTRATION_PROCESS_TYPE}
							<fieldset class="account_creation">
								<h3>{l s='Your address'}</h3>
								{foreach from=$dlv_all_fields item=field_name}
									{if $field_name eq "company"}
										<p class="text">
											<label for="company">{l s='Company'}</label>
											<input type="text" class="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{/if}" />
										</p>
									{elseif $field_name eq "vat_number"}
										<div id="vat_number" style="display:none;">
											<p class="text">
												<label for="vat_number">{l s='VAT number'}</label>
												<input type="text" class="text" name="vat_number" value="{if isset($smarty.post.vat_number)}{$smarty.post.vat_number}{/if}" />
											</p>
										</div>
									{elseif $field_name eq "firstname"}
										<p class="required text">
											<label for="firstname">{l s='First name'} <sup>*</sup></label>
											<input type="text" class="text" id="firstname" name="firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{/if}" />
										</p>
									{elseif $field_name eq "lastname"}
										<p class="required text">
											<label for="lastname">{l s='Last name'} <sup>*</sup></label>
											<input type="text" class="text" id="lastname" name="lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{/if}" />
										</p>
									{elseif $field_name eq "address1"}
										<p class="required text">
											<label for="address1">{l s='Address'} <sup>*</sup></label>
											<input type="text" class="text" name="address1" id="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{/if}" />
											<span class="inline-infos">{l s='Street address, P.O. box, company name, c/o'}</span>
										</p>
									{elseif $field_name eq "address2"}
										<p class="text">
											<label for="address2">{l s='Address (Line 2)'}</label>
											<input type="text" class="text" name="address2" id="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{/if}" />
											<span class="inline-infos">{l s='Apartment, suite, unit, building, floor, etc.'}</span>
										</p>
									{elseif $field_name eq "postcode"}
										<p class="required postcode text">
											<label for="postcode">{l s='Zip / Postal Code'} <sup>*</sup></label>
											<input type="text" class="text" name="postcode" id="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{/if}" onkeyup="$('#postcode').val($('#postcode').val().toUpperCase());" />
										</p>
									{elseif $field_name eq "city"}
										<p class="required text">
											<label for="city">{l s='City'} <sup>*</sup></label>
											<input type="text" class="text" name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{/if}" />
										</p>
										<!--
											if customer hasn't update his layout address, country has to be verified
											but it's deprecated
										-->
									{elseif $field_name eq "Country:name" || $field_name eq "country"}
										<p class="required select">
											<label for="id_country">{l s='Country'} <sup>*</sup></label>
											<select name="id_country" id="id_country">
												<option value="">-</option>
												{foreach from=$countries item=v}
												<option value="{$v.id_country}" {if ($sl_country == $v.id_country)} selected="selected"{/if}>{$v.name}</option>
												{/foreach}
											</select>
										</p>
									{elseif $field_name eq "State:name" || $field_name eq 'state'}
										{assign var='stateExist' value=true}
										<p class="required id_state select">
											<label for="id_state">{l s='State'} <sup>*</sup></label>
											<select name="id_state" id="id_state">
												<option value="">-</option>
											</select>
										</p>
									{/if}
								{/foreach}
								{if $stateExist eq false}
									<p class="required id_state select">
										<label for="id_state">{l s='State'} <sup>*</sup></label>
										<select name="id_state" id="id_state">
											<option value="">-</option>
										</select>
									</p>
								{/if}
								<p class="textarea">
									<label for="other">{l s='Additional information'}</label>
									<textarea name="other" id="other" cols="26" rows="3">{if isset($smarty.post.other)}{$smarty.post.other}{/if}</textarea>
								</p>
								{if $one_phone_at_least}
									<p class="inline-infos">{l s='You must register at least one phone number'}</p>
								{/if}
								<p class="text">
									<label for="phone">{l s='Home phone'}</label>
									<input type="text" class="text" name="phone" id="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}" />
								</p>
								<p class="text">
									<label for="phone_mobile">{l s='Mobile phone'} {if $one_phone_at_least}<sup>*</sup>{/if}</label>
									<input type="text" class="text" name="phone_mobile" id="phone_mobile" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{/if}" />
								</p>
								<p class="required text" id="address_alias">
									<label for="alias">{l s='Assign an address alias for future reference'} <sup>*</sup></label>
									<input type="text" class="text" name="alias" id="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{else}{l s='My address'}{/if}" />
								</p>
							</fieldset>
							<fieldset class="account_creation dni">
								<h3>{l s='Tax identification'}</h3>
								<p class="required text">
									<label for="dni">{l s='Identification number'}</label>
									<input type="text" class="text" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{/if}" />
									<span class="form_info">{l s='DNI / NIF / NIE'}</span>
								</p>
							</fieldset>
						{/if}
												
					</form>
					
					
					<p class="cnil">Les Colis du Boucher s'engagent à protéger les données qui vous concernent. Conformément à la loi « informatique et libertés » du 06/01/1978, modifiée par la loi du 6 août 2004, le traitement de ces informations nominatives a fait l'objet de la part de lescolisduboucher.com d'une déclaration auprès de la Commission Nationale de l'Informatique et des Libertés (CNIL).</p>
					<p class="cnil">Conformément à l'article 34 de cette même loi, vous disposez d'un droit d'accès, de modification, de rectification et de suppression des données qui vous concernent. Vous pourrez modifier vos informations personnelles directement dans la rubrique « Mon Compte » du site et pourrez supprimer vos données personnelles par email ou courrier en indiquant vos nom, prénom, adresse et email ayant servi à votre enregistrement sur le site lescolisduboucher.com.</p>
				</div>
			</div>
		{/if}
		
		
	</div>
</div>