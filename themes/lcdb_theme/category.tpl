<div id="columns" class="content clearfix">
	<div id="left_column">
		{include file="$tpl_dir./category-leftcol.tpl"}
	</div>
	<div id="center_column" class="page-list-product">
		{if isset($category)}
			{if $category->id AND $category->active}
		        {include file="./category-subcat.tpl"}
			{elseif $category->id}
				<p class="warning">{l s='This category is currently unavailable.'}</p>
			{/if}
		{/if}
	</div><!-- end #center_column -->
	<div id="right_column">
		{include file="$tpl_dir./category-rightcol.tpl"}
	</div>
</div>
