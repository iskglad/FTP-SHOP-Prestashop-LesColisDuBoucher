
<div id="columns" class="content clearfix">
	<div id="left_column">
		
		<form id="form-search" method="get" action="{$link->getPageLink('search', true)}">
			<input type="text" id="search" name="search" placeholder="Recherche..." />
			<input type="hidden" name="orderby" value="position" />
			<input type="hidden" name="controller" value="search" />
			<input type="hidden" name="orderway" value="desc" />
			<button type="submit" name="submit">OK</button>
		</form>
		<nav class="secondary-menu small-bloc">
			<ul id="category-leftcol">
				<li class="secondary-menu-item item-active first">
					<a href="javascript:void(0);" title="Produits à la carte">Recettes</a>
					<ul class="submenu">
						{foreach from=$left_col item=maincat name=foo}
							<li class="submenu-item {if $recipe_category->id_parent eq $maincat.id_recipe_category}item-active{/if} {if $smarty.foreach.foo.first}first {/if}{if $smarty.foreach.foo.last}last {/if}">
								<a href="{$link->getRecipeCategoryLink($maincat.id_recipe_category, $maincat.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$maincat.name}"><span class="img-{$maincat.name|lower}"></span>{$maincat.name}</a>
								{if $maincat.subcats}
								<ul class="hasSubCat">
									{foreach from=$maincat.subcats item=cat name=foo2}
										<li class="{if $recipe_category->id_recipe_category eq $cat.id_recipe_category}item-active{/if}">
											<a href="{$link->getRecipeCategoryLink($cat.id_recipe_category, false)|escape:'htmlall':'UTF-8'}" title="{$cat.name}">{$cat.name}</a>
										</li>
									{/foreach}
								</ul>
								{/if}
							</li>
						{/foreach}
					</ul>
				</li>
			</ul>
		</nav>
	</div>
	
	{if isset($recipe)}
		{include file="$tpl_dir./recipe-content.tpl"}
	{elseif isset($recipe_category)}
		{include file="$tpl_dir./recipe-category.tpl"}
	{else}
		<div id="center_column">
			<div class="error">
				{l s='This page does not exist.'}
			</div>
		</div>
	{/if}
	
</div>
