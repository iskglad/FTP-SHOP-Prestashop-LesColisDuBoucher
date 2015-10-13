
{if !$recipe->active}
	<br />
	<div id="admin-action-recipe">
		<p>{l s='This Recipe page is not visible to your customers.'}
		<input type="hidden" id="admin-action-recipe-id" value="{$recipe->id}" />
		<input type="submit" value="{l s='Publish'}" class="exclusive" onclick="submitPublishRecipe('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 0, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
		<input type="submit" value="{l s='Back'}" class="exclusive" onclick="submitPublishRecipe('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 1, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
		</p>
		<div class="clear" ></div>
		<p id="admin-action-result"></p>
		</p>
	</div>
{/if}

<div id="center_column" class="recipe_card">
	<div class="backlink">
		<a href="javascript:history.back()" title="retourner à la liste des recettes">Retourner à la liste des recettes</a>
	</div>
	<div itemscope itemtype="http://schema.org/Recipe">
		<div class="title_print_recipe">
			<h1 class="title_list" itemprop="name">{$recipe->title}</h1>
			<a href="javascript:window.print()" title="imprimer" class="red-button">Imprimer</a>
		</div>
		<div id="presentation">
			<ul class="presentation">
				<li>
					<span id="difficulte">Difficulté</span>
					<span class="difficulte_level difficulte_{$recipe->difficulty}"><span>{$recipe->difficulty}/5</span></span>
				</li>
				<li>
					<span id="preparation">Préparation</span>
					<span itemprop="prepTime">{$recipe->duration}</span>
				</li>
				<li>
					<span id="cuisson">Cuisson</span>
					<span itemprop="cookTime">{$recipe->cooking_time}</span>
				</li>
				<li>
					<span id="quantite">Quantite</span>
					<span itemprop="recipeYield">{$recipe->number_people} pers.</span>
				</li>
			</ul>
		</div>
		<div id="content_recipe">
			<div id="recipe_intro" class="content">{$recipe->prior_content}</div>
			<div id="recipe_ingredients" class="content">
				<h2>Ingredients</h2>
				<div>{$recipe->ingredients_content}</div>
			</div>
			<div id="recipe_detail" class="content">
				<h2>Recette</h2>
				<div itemprop="recipeInstructions">
					{$recipe->recipe_content}
				</div>
			</div>
			<div id="recipe_council" class="content">
				<h2>Le conseil du boucher</h2>
				<div>
					{$recipe->tips_content}
				</div>
			</div>
		</div>
	</div>
	<div class="backlink">
		<a href="javascript:history.back()" title="retourner à la liste des recettes">
			Retourner à la liste des recettes
		</a>
	</div>
</div>