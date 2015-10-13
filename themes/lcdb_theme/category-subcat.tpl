
<div class="big-bloc">
    <div class="title_list_product">
        {if $category->level_depth == 3}
            {assign var=logo value="big_{$category->link_rewrite}"}
        {else if $category->level_depth == 4}
            {if $category->id_category == $id_category_boeuf}
                {assign var=logo value="big_cow"}
            {else}
                {assign var=logo value="big_{$category->link_rewrite}"}
            {/if}
        {/if}
        <span class="big_image" id="{$logo}"></span>
        {if $category->name|lower == 'label rouge'}
            <span class="label_viande"></span>
        {elseif $category->name|lower == 'label bio'}
            <span class="label_bio"></span>
        {/if}

        <h1 class="{if $category->name == 'Promotions'}bon_plan_eleveurs{/if}{if $category->name == 'Le Bourdonnec'}ymlb-hide{/if}">

            {strip}
                {if $category->name == 'Poisson'}
                    Poisson d’Auvergne !
                {else}
                    {if $category->name == 'Promotions'}
                        Bons Plans des Eleveurs
                    {else}
                        {$category->name|escape:'htmlall':'UTF-8'}
                    {/if}
                    {if isset($categoryNameComplement)}
                        {$categoryNameComplement|escape:'htmlall':'UTF-8'}
                    {/if}
                {/if}
            {/strip}
        </h1>
        {include file="$tpl_dir./errors.tpl"}
        {if isset($category->description)}
            <p>{$category->description}</p>
        {/if}
    </div>
    {include file="./product-sort.tpl"}
    <div class="list-product">
        {if $category->name == 'Le Bourdonnec'}
            {include file="./product-list.tpl" products=$ymlb_products ymlb=true}
        {else if $category->name == 'Promotions'}
            {include file="./product-list.tpl" products=$promo_products}
        {else}
            {if isset($subcategories)}
                {foreach $subcategories as $id_subcat => $subcat}
                    {if ($type == '' or $type == 'undefined') or
                    $type == {$subcat.name|lower|substr:3}}
                        {if $subcat.products}
                            <div class="grill subcat{$id_subcat} category">
                                {if {$subcat.name|lower} != "pas de sous-categorie"}
                                    <div class="category-title">
                                        {if $subcat.name == "à rôtir"}
                                            <span id="rotir"></span>
                                        {else}
                                            <span id="{$subcat.name|lower|substr:3}"></span>
                                        {/if}

                                        {if $subcat.name == "à mijoter" || $subcat.name == "à rôtir" || $subcat.name == "à griller"}
                                            <h2>À {$subcat.name|lower|substr:3}</h2>
                                        {else}
                                            <h2>{$subcat.name}</h2>
                                        {/if}

                                        <div class="voir_tout voir{$id_subcat}">Voir tous les détails</div>
                                        <div class="masquer_tout masquer{$id_subcat}">Masquer tous les détails</div>
                                        <script src="{$js_dir}afficher_tout.js"></script>
                                    </div>
                                {/if}
                                {include file="./product-list.tpl" products=$subcat.products}
                            </div>
                        {/if}
                    {/if}
                {/foreach}
            {/if}
        {/if}
        <div class="more-product">
            <p class="blod">Vous cherchez un produit particulier que nous ne proposons pas ?</p>
            <p>
                <a href="{$link->getPageLink('contact', true)}" title="Contactez-nous !">Contactez-nous !</a>
                Nos éleveurs ont sûrement ce dont vous avez besoin.
            </p>
        </div>
    </div>
</div>

{if $category->name == 'Le Bourdonnec' || $category->name == 'Viandes maturées' }
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
	background: url('themes/lcdb_theme/img/asset/textures/fond-papier-clair.jpg') repeat-y 0 0!important;
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