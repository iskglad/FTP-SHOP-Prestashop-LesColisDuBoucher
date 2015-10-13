
{capture name=path}{l s='Contact'}{/capture}

<div id="columns" class="content clearfix contact">
	<div class="bloc">
		<h1 class="green">Contact</h1>

		{if isset($confirmation)}
			<p>{l s='Your message has been successfully sent to our team.'}</p>
			<ul class="footer_links">
				<li><a href="{$base_dir}">{l s='Home'}</a></li>
			</ul>
		{elseif isset($alreadySent)}
			<p>{l s='Your message has already been sent.'}</p>
			<ul class="footer_links">
				<li><a href="{$base_dir}"><img class="icon" alt="" src="{$img_dir}icon/home.gif"/></a><a href="{$base_dir}">{l s='Home'}</a></li>
			</ul>
		{else}
		
			<p class="italic">Des questions ? Des remarques ? Contactez-nous ! Nous nous ferons un plaisir de vous aider.</p>
			<div class="hat">
				<p class="faq">Mais avant cela, pensez à lire les <a href="{$link->getCMSCategoryLink(4)}" title="questions fréquentes" class="green">questions fréquentes</a> ! Vous y trouverez toutes les réponses aux
				questions les plus fréquemment posées.</p>
			</div>
			<h2 class="bold">Vos questions restent sans réponse ?</h2>
			<p class="phone">Contactez-nous par téléphone : <span class="bold">09 72 42 51 66</span></p>
			<p class="mail">
				Ou remplissez le formulaire ci-dessous
				<span class="grey">Les champs suivis d'un astérisque (<span class="asterisque"> * </span>) sont obligatoires.</span>
			</p>
			<div class="warning"></div>
			{include file="$tpl_dir./errors.tpl"}
			
			<form action="{$request_uri|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" id="form-contact" name="form-contact" novalidate>
				
				<label for="name">Nom <span class="asterisque">*</span></label>
				<input type="text" name="name" id="name" data-required="true" />
				<label for="firstname">Prénom <span class="asterisque">*</span></label>
				<input type="text" id="firstname" name="firstname" data-required="true" />
				
				<label for="email">Adresse e-mail <span class="asterisque">*</span></label>
				{if isset($customerThread.email)}
					<input type="text" id="email" name="from" value="{$customerThread.email|escape:'htmlall':'UTF-8'}" readonly="readonly" data-required="true" />
				{else}
					<input type="text" id="email" name="from" value="{$email|escape:'htmlall':'UTF-8'}" data-required="true" />
				{/if}
				
				<div class="select">
				
			
						{foreach from=$contacts item=contact}
						
								<input type="hidden" name="id_contact" value="{$contact.id_contact}" />
						
						{/foreach}
				
				</div>
				
				<label for="message">Votre message <span class="asterisque">*</span></label>
				<textarea id="message" name="message" data-required="true">{if isset($message)}{$message|escape:'htmlall':'UTF-8'|stripslashes}{/if}</textarea>
				
				<div class="action">
					<button type="submit" name="submitMessage" id="submitMessage" class="green-button gradient">Envoyer</button>
				</div>
			</form>
			
		{/if}
	</div>
</div>

