
<input type="hidden" name="submitted_tabs[]" value="Associations" />
<div class="Associations">
	<h4>{l s='Associations'}</h4>

	{include file="controllers/products/multishop/check_fields.tpl" product_tab="Associations"}
	<div class="separation"></div>
		<div id="no_default_category" class="hint">
		{l s='Please select a default category.'}
	</div>
	<table>
		<tr>
			<td class="col-left">
				{include file="controllers/products/multishop/checkbox.tpl" field="category_box" type="category_box"}
				<label for="category_block">{l s='Associated categories:'}</label>
			</td>
			<td class="col-right">
				<div id="category_block">
					{$category_tree}
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td class="col-right">
					<a class="button bt-icon confirm_leave" href="{$link->getAdminLink('AdminCategories')|escape:'htmlall':'UTF-8'}&addcategory">
						<img src="../img/admin/add.gif" alt="{l s='Create new category'}" title="{l s='Create new category'}" />
						<span>{l s='Create new category'}</span>
					</a>
			</td>
		</tr>
		<tr>
			<td class="col-left">
				{include file="controllers/products/multishop/checkbox.tpl" field="id_category_default" type="default"}
				<label for="id_category_default">{l s='Default category:'}</label>
			</td>
			<td class="col-right">
				<select id="id_category_default" name="id_category_default">
					{foreach from=$selected_cat item=cat}
						<option value="{$cat.id_category}" {if $id_category_default == $cat.id_category}selected="selected"{/if} >{$cat.name}</option>
					{/foreach}
				</select>
				<div class="hint" style="display:block;">{l s='The default category is the category which is displayed by default.'}</div>
			</td>
		</tr>
	</table>
<div class="separation"></div>
	<table>
		<tr>
			<td class="col-left"><label>{l s='Recipes:'}</label></td>
			<td style="padding-bottom:5px;">
				<input type="hidden" name="inputRecipes" id="inputRecipes" value="{foreach from=$recipes item=recipe}{$recipe.id_recipe}-{/foreach}" />
				<input type="hidden" name="nameRecipes" id="nameRecipes" value="{foreach from=$recipes item=recipe}{$recipe.title|escape:'htmlall':'UTF-8'}¤{/foreach}" />

				<div id="ajax_choose_recipe">
					<p style="clear:both;margin-top:0;">
						<input type="text" value="" id="recipe_autocomplete_input" />
						{l s='Begin typing the first letters of the recipe name, then select the recipe from the drop-down list'}
					</p>
					<p class="preference_description">{l s='(Do not forget to save the product afterward)'}</p>
				</div>
				<div id="divRecipes">
					{* @todo : donot use 3 foreach, but assign var *}
					{foreach from=$recipes item=recipe}
						{$recipe.title|escape:'htmlall':'UTF-8'}
						<span class="delRecipe" name="{$recipe.id_recipe}" style="cursor: pointer;">
							<img src="../img/admin/delete.gif" class="middle" alt="" />
						</span><br />
					{/foreach}
				</div>
			</td>
		</tr>
	</table>
</div>