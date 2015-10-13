
<!-- Breadcrumb -->
{if isset($smarty.capture.path)}{assign var='path' value=$smarty.capture.path}{/if}
<div class="breadcrumb">
    <a href="{$base_dir}" title="{l s='return to Home'}">{l s='Home'}</a>
    {if isset($path) AND $path}
        <span class="navigation-pipe" {if isset($category) && isset($category->id_category) && $category->id_category == 1}style="display:none;"{/if}>{$navigationPipe|escape:html:'UTF-8'}</span>
        {if !$path|strpos:'span'}
            <span class="navigation_page">{$path}</span>
        {else}
            {$path}
        {/if}
    {/if}
    {if $page_name=='delivery'}

    
            <span class="navigation-pipe">&gt;</span>
       <span class="navigation_end"><a href="{$link->getCMSCategoryLink(3)}">Infos pratiques</a></span><span class="navigation-pipe">&gt;</span> <span class="Livraison">Livraison</span>
      

    {/if}
        {if $page_name=='guestbook'}

    
            <span class="navigation-pipe">&gt;</span>
       <span class="navigation_end"><a href="{$link->getCMSCategoryLink(3)}">Infos pratiques</a></span><span class="navigation-pipe">&gt;</span> <span class="Témoignages">Témoignages</span>
      

    {/if}
    <a href="{$link->getCategoryLink(3)|escape:'htmlall':'UTF-8'}" title="Commander nos viandes" class="our_order"><span></span>Commander nos viandes<span></span></a>
	
</div>
<!-- /Breadcrumb -->