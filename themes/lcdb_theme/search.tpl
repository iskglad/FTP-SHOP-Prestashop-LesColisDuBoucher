
{capture name=path}{l s='Search'}{/capture}

<div id="columns" class="content clearfix">
	<div id="center_column" class="single">
		<div class="big-bloc">
			<div class="title_list_product">
				<h1>
					{l s='Recherche'}&nbsp;{if $nbProducts > 0}"{if isset($search_query) && $search_query}{$search_query|escape:'htmlall':'UTF-8'}{elseif $search_tag}{$search_tag|escape:'htmlall':'UTF-8'}{elseif $ref}{$ref|escape:'htmlall':'UTF-8'}{/if}"{/if}
				</h1>
				{include file="$tpl_dir./errors.tpl"}
			</div>
			{* include file="./product-sort.tpl" *}
			<div class="list-product">
				<div class="category">

					{if !$nbProducts}
						<p class="warning">
							{if isset($search_query) && $search_query}
								{l s='No results found for your search'}&nbsp;"{if isset($search_query)}{$search_query|escape:'htmlall':'UTF-8'}{/if}"
							{elseif isset($search_tag) && $search_tag}
								{l s='No results found for your search'}&nbsp;"{$search_tag|escape:'htmlall':'UTF-8'}"
							{else}
								{l s='Please type a search keyword'}
							{/if}
						</p>
					{else}
						<h3 class="nbresult"><span class="big">{if $nbProducts == 1}{l s='%d result has been found.' sprintf=$nbProducts|intval}{else}{l s='%d results have been found.' sprintf=$nbProducts|intval}{/if}</span></h3>

						{include file="$tpl_dir./product-list.tpl" products=$search_products search="true"}
					{/if}


				</div>
				<div class="more-product">
					<p class="blod">Vous cherchez un produit particulier que nous ne proposons pas ?</p>
					<p>
						<a href="{$link->getPageLink('contact', true)}" title="Contactez-nous !">Contactez-nous !</a>
						Nos éleveurs ont sûrement ce dont vous avez besoin.
					</p>
				</div>
			</div>
		</div>

	</div><!-- end #center_column -->
</div>
