<div id="columns" class="content clearfix">
	<div id="center_column" class="single">
		<div class="big-bloc">
			<h1 class="green">Livre d'or ! Merci !</h1>
			<p class="legend italic">N'hésitez pas à nous <a id="put-witness" href="#button-witness" title="laisser votre avis" class="green">laisser votre avis !</a></p>
			{include file="$tpl_dir./errors.tpl"}
			{if isset($confirmation)}
				<p>Merci, votre commentaire à bien été envoyé. Il sera publié après modération !</p>
			{/if}
			{if isset($guestbook)}
				{foreach from=$guestbook item=comment}
					<div class="person-witness">
						<div class="witness">
							<span class="startquote">"</span>
							<p>{$comment.message}</p>
							<span class="endquote">"</span>
						</div>
						<p class="name-witness italic">{$comment.firstname} {$comment.lastname|upper|truncate:2:'.'}, {$comment.city}</p>
					</div>
				{/foreach}
			{/if}
			
			{*include file="$tpl_dir./pagination.tpl"*}

			<div id="button-witness">
				<a href="#" title="Laissez votre message" class="green-button gradient">Laissez votre message !</a>
			</div>
			<div class="comment" id="temoignage">
				<div class="warning"></div>
				<form method="post" action="{$request_uri|escape:'htmlall':'UTF-8'}" id="form-witness" name="form-witness">
					<p class="information">Les champs suivis d'un astérisque (<span class="asterisque">*</span>) sont obligatoires</p>
					<p><label for="lastname">Nom <span class="asterisque">*</span></label><input type="text" name="lastname" id="lastname" data-required="true" /></p>
					<p><label for="firstname">Prénom <span class="asterisque">*</span></label><input type="text" id="firstname" name="firstname" data-required="true" /></p>
					<p><label for="city">Ville <span class="asterisque">*</span></label><input type="text" id="city" name="city" data-required="true" /></p>
					<p><label for="email">Adresse e-mail <span class="asterisque">*</span></label><input type="text" name="email" id="email" data-required="true" /></p>
					<p class="last">
						<label for="message">Votre message <span class="asterisque">*</span></label><textarea id="message" name="message" data-required="true"></textarea>
					</p>
					<div class="action">
						<button type="submit" name="submitMessage" id="submitMessage" class="red-button gradient">Envoyer</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>