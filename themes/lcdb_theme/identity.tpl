

{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Your personal information'}{/capture}

<div id="columns" class="content clearfix">
	<div id="left_column">
		{include file="./account-left-col.tpl"}
	</div><!-- / #left_column -->
	<div id="center_column">
		<div class="big-bloc">
			<h1>Mes informations</h1>
			{include file="$tpl_dir./errors.tpl"}
			
			{if isset($confirmation) && $confirmation}
				<p class="success">
					{l s='Your personal information has been successfully updated.'}
					{if isset($pwd_changed)}<br />{l s='Your password has been sent to your e-mail:'} {$email}{/if}
				</p>
			{else}
				
				<p>N'hésitez pas à modifier vos informations personnelles si celles-ci ont changé.</p>
				<br>
				<p>Les champs suivis d'un astérisque <span class="asterisque_rouge">*</span> sont obligatoires.</p>
				<hr />
				<form  action="{$link->getPageLink('identity', true)}" method="post" class="mes_informations">
					<p class="labels_infos">Civilité <span class="asterisque_rouge">*</span></p>
					{foreach from=$genders key=k item=gender}
						<label class="radio {if $gender->id > 1}label_radio{/if}" for="id_gender{$gender->id}">
							<input type="radio" name="id_gender" id="id_gender{$gender->id}" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == $gender->id}checked="checked"{/if} value="{$gender->id}" />
							{$gender->name}
						</label>
					{/foreach}


					<p class="labels_infos">
						<label for="prenom">Prénom <span class="asterisque_rouge">*</span></label>
					</p>
					<input type="text" id="firstname" name="firstname" value="{$smarty.post.firstname}" />
					<p class="labels_infos">
						<label for="nom">Nom <span class="asterisque_rouge">*</span></label>
					</p>
					<input type="text" name="lastname" id="lastname" value="{$smarty.post.lastname}" />
					<p class="select">
						<p>{l s='Date of Birth'}</p>
						<select name="days" id="days">
							<option value="">-</option>
							{foreach from=$days item=v}
								<option value="{$v}" {if ($sl_day == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
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
							{foreach from=$months key=k item=v}
								<option value="{$k}" {if ($sl_month == $k)}selected="selected"{/if}>{l s=$v}&nbsp;</option>
							{/foreach}
						</select>
						<select id="years" name="years">
							<option value="">-</option>
							{foreach from=$years item=v}
								<option value="{$v}" {if ($sl_year == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
							{/foreach}
						</select>
					</p>
					<p class="labels_infos">
						<label for="mail">E-mail <span class="asterisque_rouge">*</span></label>
					</p>
					<input type="text" name="email" id="email" value="{$smarty.post.email}" />
					<p class="labels_infos">
						<label for="old_password">Mot de passe actuel <span class="asterisque_rouge">*</span></label>
					</p>
					<input type="password" name="old_passwd" id="old_passwd" />
					<p class="labels_infos">
						<label for="new_password">Nouveau mot de passe <span class="asterisque_rouge">*</span></label>
					</p>
					<input type="password" name="passwd" id="passwd" />
					<p class="labels_infos">
						<label for="confirm_password">Confirmation <span class="asterisque_rouge">*</span></label>
					</p>
					<input type="password" name="confirmation" id="confirmation" />
					{if $newsletter}
						<p class="labels_infos">J'accepte de recevoir par e-mail des offres, anecdotes<br>de la part des Colis du Boucher <span class="asterisque_rouge">*</span><br/><span class="label_italique">Les informations vous concernant ne seront jamais vendues,<br>louées ou cédées à des tiers</span></p>
						<label class="radio" for="newsletter_oui"><input type="radio" name="newsletter" id="newsletter_oui" value="1" {if isset($smarty.post.newsletter) && $smarty.post.newsletter == 1} checked="checked"{/if} />Oui</label>
						<label class="radio label_radio" for="newsletter_non"><input type="radio" name="newsletter" id="newsletter_non" value="0" {if !isset($smarty.post.newsletter) || $smarty.post.newsletter == 0} checked="checked"{/if} />Non</label>
					{/if}
					<br/><br/>
					<hr />
					<input class="red-button gradient" type="submit" value="VALIDER MES INFORMATIONS" id="informations_submit"  name="submitIdentity" />
					<p id="security_informations">
						{l s='[Insert customer data privacy clause or law here, if applicable]'}
					</p>
				</form>
			{/if}
		</div>
	</div><!-- / #center_column -->
	
</div><!-- / .content -->