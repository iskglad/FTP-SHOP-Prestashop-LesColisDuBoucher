
<div id="center_column">
	<div class="title clearfix">
		<span class="category" id="big_{$parent_recipe_category->name|lower}"></span>
		{if $parent_recipe_category->name|lower|escape:'htmlall':'UTF-8' !== 'accueil'}
			{if $recipe_category->name|lower|escape:'htmlall':'UTF-8' === 'autres'}
			<h1>Les {$recipe_category->name|lower|escape:'htmlall':'UTF-8'} recettes {if $parent_recipe_category->name|lower|strpos:'a'===0 || $parent_recipe_category->name|lower|strpos:'e'===0 || $parent_recipe_category->name|lower|strpos:'i'===0}d'{else}de {/if}{$parent_recipe_category->name|lower}</h1>
			{else}
			<h1>Les recettes {if $parent_recipe_category->name|lower|strpos:'a'===0 || $parent_recipe_category->name|lower|strpos:'e'===0 || $parent_recipe_category->name|lower|strpos:'i'===0}d'{else}de {/if}{$parent_recipe_category->name|lower} {$recipe_category->name|lower|escape:'htmlall':'UTF-8'}</h1>
			{/if}
		{else}
			{if $recipe_category->name|lower|escape:'htmlall':'UTF-8' === 'autres'}
			<h1>Les {$recipe_category->name|lower|escape:'htmlall':'UTF-8'} recettes</h1>
			{else}
			<h1>Les recettes {if $recipe_category->name|lower|strpos:'a'===0 || $recipe_category->name|lower|strpos:'e'===0 || $recipe_category->name|lower|strpos:'i'===0}d'{else}de {/if} {$recipe_category->name|lower|escape:'htmlall':'UTF-8'}</h1>
			{/if}
		{/if}
		<span class="cuisson" id="{$recipe_category->link_rewrite}"></span>
	</div>
	{if isset($recipe_pages) & !empty($recipe_pages)}
	<table>
		<thead>
			<tr>
				<th><span class="border-class"></span></th>
				<th id="difficulte">Difficulté</th>
				<th id="preparation">Préparation</th>
				<th id="cuisson">Cuisson</th>
				<th id="quantite">Quantité</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$recipe_pages item=recipepages}
				<tr itemscope itemtype="http://schema.org/Recipe">
					<td class="title_recipe" itemprop="name"><a href="{$link->getRecipeLink($recipepages.id_recipe)|escape:'htmlall':'UTF-8'}" title="Accéder à la recette">{$recipepages.title|escape:'htmlall':'UTF-8'}</a></td>
					<td class="difficulte_level difficulte_{$recipepages.difficulty}"><span>{$recipepages.difficulty}</span></td>
					<td class="preparation_time" itemprop="prepTime">{$recipepages.duration}</td>
					<td class="cooking_time" itemprop="cookTime">{$recipepages.cooking_time}</td>
					<td class="person_number" itemprop="recipeYield">{$recipepages.number_people}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	{else}
		<p>Il n'y a aucune recette pour l'instant.</p>
	{/if}
</div>