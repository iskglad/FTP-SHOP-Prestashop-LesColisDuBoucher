
<div class="big-bloc">
	<a href="javascript:history.back();" title="Retourner aux produits">&lt; Retourner aux produits</a>
	
	{if isset($adminActionDisplay) && $adminActionDisplay}
	<div id="admin-action">
		<p>{l s='This product is not visible to your customers.'}
		<input type="hidden" id="admin-action-product-id" value="{$product->id}" />
		<input type="submit" value="{l s='Publish'}" class="exclusive" onclick="submitPublishProduct('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 0, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
		<input type="submit" value="{l s='Back'}" class="exclusive" onclick="submitPublishProduct('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 1, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
		</p>
		<p id="admin-action-result"></p>
		</p>
	</div>
	{/if}

	{if isset($confirmation) && $confirmation}
	<p class="confirmation">
		{$confirmation}
	</p>
	{/if}
	
	<div id="item" itemscope itemtype="http://schema.org/Product">
		<div class="clearfix">
			<div id="product-image">
				{assign var=logo_category value="product_autre"}
				{foreach from=$product_categories item=category}
					{if $category.level_depth == 3}
							{assign var=logo_category value="product_{$category.link_rewrite}"}
					{/if}
				{/foreach}

				{if isset($images) && count($images) > 0}
					{foreach from=$images item=image name=thumbnails}
					{if $smarty.foreach.thumbnails.first}
						{assign var=imageIds value="`$product->id`-`$image.id_image`"}
						<img alt='{$image.legend|htmlspecialchars}' src="{$link->getImageLink($product->link_rewrite, $imageIds)}">
					{/if}
					{/foreach}
				{else}
					<img src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/{$logo_category}.png" alt="{$category.name}" />
				{/if}
				
			</div>
			<div id="main-product-infos">
				<h1 itemprop="name">{$product->name|escape:'htmlall':'UTF-8'}</h1>
				{if isset($product->description_short)}
					<div itemprop="description">{$product->description_short}</div>
				{/if}
				{if isset($product->description)}
					<div class="full-description">{$product->description}</div>
				{/if}
			</div>
		</div>
		<div class="clearfix product-default price-info">

            <!--ACTION (add number, add to basket, etc...)-->
            <div class="action-product">
                {if count($product->combinations)}
                    {foreach from=$product->combinations item=combination}
                        <!--Declainaison #debug:{$combination.label_name}-->
                        <form class="form-panier declinaison {$combination.label_name|str_replace:' ':'-'}" method="post" action="{$link->getPageLink('cart')}" >
                            <!--Label image-->
                            {$label_image = [
                            'selection'     =>  'les_colis_du_boucher_logo_2.png',
                            'salers'     =>  'les_colis_du_boucher_logo_2.png',
                            'bio'           =>  'logo-bio-simple-wide.png',
                            'label rouge'   =>  'label_rouge_logo_full.png',
                            'Le Bourdonnec'   =>  'product_le-bourdonnec.png'
                            ]}
                            <span class="label">
										{if in_array($combination.label_name, $label_image)}
                                            <img alt='logo-{$combination.label_name}' class='logo-label' src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/{$label_image[{$combination.label_name}]}">
										{/if}
                                           {if $combination.label_name =! 'Le Bourdonnec'} <span class="name">{$combination.label_name|@ucfirst}</span> {/if}
                                        </span>
                            {if $combination.isPromo}
                                <span class="promo">Promo</span>
                            {/if}
                            <!--Price-->

                                        <span class="price-kg">
                                            {convertPrice price=(($productPrice/$product->unit_price_ratio) + $combination.unit_price_impact)}/{$product->unity}
                                        </span>
                            <span class="selling_price">{convertPrice price=($productPrice + $combination.price_impact)}</span>
                            <!--Quantity-->
                            <button class="moreless minus" name="minus" type="button">-</button>
                            <input class="quantity" type="text" name="qty" value="1" maxlength="2">
                            <button class="moreless plus" name="plus" type="button">+</button>

                            <!--Hidden input => Id-product #debug({$product->id_product|intval})| Id-declinaison #debug({$combination.id_product_attribute})-->
                            <input type="hidden" name="token" value="{$static_token}" />
                            <input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
                            <input type="hidden" name="add" value="1" />
                            <input type="hidden" name="id_product_attribute" value="{$combination.id_product_attribute}"/>

                            {if isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS}{$HOOK_PRODUCT_ACTIONS}{/if}

                            {if isset($HOOK_PRODUCT_FOOTER) && $HOOK_PRODUCT_FOOTER}{$HOOK_PRODUCT_FOOTER}{/if}



                            <!--Button add-->
                            <button class="green-button gradient" name="submit" type="submit">Ajouter</button>

                            <!--Warning message-->
                            {if $combination.available_quantity < 5 and $combination.available_quantity > 0}
                                <p class="warning" itemscope itemtype="http://schema.org/Offer">
                                    Plus que {$combination.available_quantity} {$product->name|escape:'htmlall':'UTF-8'|truncate:15:'[...]'} {$combination.label_name} restants.
                                </p>
                            {/if}
                            {if $combination.available_date != "0000-00-00"}
                                <p class="warning" itemscope itemtype="http://schema.org/Offer">

                                    {if $combination.isPromo == 1}
                                        Promo
                                    {else}
                                        Produit
                                    {/if}
                                    disponible
                                    {if $combination.begin_date != "0000-00-00"}
                                        du <span itemprop="availabilityEnds">{$combination.begin_date|date_format:"d M"}</span> au
                                    {else}
                                        jusqu'au
                                    {/if}
                                    <span itemprop="availabilityEnds">{$combination.available_date|date_format:"d M"}</span>
                                </p>
                            {/if}
                        </form>
                    {/foreach}
                {else}
                    Aucune declinaison pour ce produit.
                {/if}
            </div>
		</div>
		
		<hr />
		<div class="misc-infos clearfix">
			{foreach from=$features item=feature}
				{if $feature.id_feature == $id_feature_number_of }
					<p class="portions"><span class="img-portions"></span> {$feature.value} <span class="colis-portions">portions</span></p>
				{/if}
				{if $feature.id_feature == $id_feature_preservation }
					<p class="jours"><span class="img-jours"></span> {$feature.value} <span class="colis-jours">jours</span></p>
				{/if}
				{if $feature.id_feature == $id_feature_baking }
					<p class="cuisson"><span class="img-cuisson"></span> {$feature.value} <span class="mode-cuisson">min</span></p>
				{/if}
			{/foreach}
		</div>
		
		{if isset($product->tricks) && ($product->tricks != null)}
			<hr />
			<div id="trucs-et-astuces">
				<h2><span class="img-trucs-astuces"></span>Trucs et astuces des Colis du Boucher</h2>
				<div>{$product->tricks}</div>
			</div>
		{/if}
		
		{if isset($product->breeder) && ($product->breeder != null)}
			<hr />
			<div id="mot-eleveur">
				<h2><span class="img-mot-eleveur"></span>Le mot de l'éleveur</h2>
				<div>{$product->breeder}</div>
			</div>
		{/if}

		{if isset($recipes) && ($recipes != null)}
			<hr />
			<div id="idees-recettes">
				<h2><span class="img-idees-recettes"></span>Idées recettes</h2>
				<ul>
					{foreach from=$recipes item=recipe name=list_3_recipe}
                        {if $smarty.foreach.list_3_recipe.index < 3 }
                            <li itemscope itemtype="http://schema.org/Recipe">
                                <a href="#" title="voir la recette" class="recipe-link">voir la recette</a>
                                <h3 itemprop="name">{$recipe.title}</h3>
                                <p class="clearfix"><span class="intitule">difficulté</span> <span class="difficulte_level difficulte_{$recipe.difficulty}">{$recipe.difficulty}/5</span></p>
                                <div class="recipe-details hidden">
                                    <h4>Ingrédients :</h4>
                                    <div class="ingredients clearfix">
                                        {$recipe.ingredients_content}
                                    </div>
                                    <h4>Recette :</h4>
                                    <div class="recette">
                                        {$recipe.recipe_content}
                                    </div>
                                </div>
                                <hr class="dashed" />
                            </li>
                        {/if}
					{/foreach}
				</ul>
			</div>
		{/if}
		
	</div>
</div>


{if $category.name == 'Le Bourdonnec'}
<!-- YMLB -->
<style>
.content{
	background: #000000!important;
  }
.quantity{
	color: #a08858!important
  }
.big-bloc{
	background-color: rgba(255,255,255,0.0)!important;
	color:#a08858!important;
  }
.small-bloc{
	background: url('/themes/lcdb_theme/img/asset/textures/fond-papier-clair.jpg') repeat-y 0 0!important;
	color: #342110!important;
  }
.footer-top{
	background-image: linear-gradient( to top, transparent 50%, #000000)!important;
  }
.list-product .block-product .identification h3 {
  color: rgb(159, 33 , 41);
}
</style>
{/if}