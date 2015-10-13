
<form method="get" action="{$link->getPageLink('search', true)}" id="form-search">
	<input type="hidden" name="orderby" value="position" />
	<input type="hidden" name="controller" value="search" />
	<input type="hidden" name="orderway" value="desc" />
	<input class="search_query" type="text" id="search" name="search_query" placeholder="{if isset($smarty.get.search_query)}{$smarty.get.search_query|htmlentities:$ENT_QUOTES:'utf-8'|stripslashes}{else}Votre recherche...{/if}" />
	<button type="submit" name="submit">OK</button>
</form>
{foreach from=$left_col item=maincat name=foo}
	<nav class="secondary-menu small-bloc">
		<ul id="category-leftcol">
				<li class="secondary-menu-item first last {if $maincat.subcats|@count lt 1}bottom {/if}">
					{if $maincat.subcats|@count gt 0}
						<a title="{$maincat.name}" class="{if $maincat.subcats|@count gt 0}bottom {/if}">{$maincat.name}</a>
					{else}
						<a href="{$link->getCategoryLink($maincat.id_category, $maincat.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$maincat.name}" class="{if $maincat.subcats|@count gt 0}bottom {/if}">{$maincat.name}</a>
					{/if}
					{if $maincat.subcats|@count gt 0}
						<ul class="submenu">
							{foreach from=$maincat.subcats item=cat name=foo2}
								<li class="submenu-item {if $smarty.foreach.foo2.first}first {/if}{if $smarty.foreach.foo2.last}last {/if}">
									<a href="{$link->getCategoryLink($cat.id_category, $cat.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$cat.name}"><span class="img-{$cat.name|lower|replace:' ':'-'}"></span>{$cat.name}</a>
								</li>
							{/foreach}
						</ul>
					{/if}
				</li>
		</ul>
	</nav>
{/foreach}