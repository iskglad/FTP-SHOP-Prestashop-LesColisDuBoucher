
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7 lt-ie6 " lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8 ie7" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9 ie8" lang="en"> <![endif]-->
<!--[if gt IE 8]> <html lang="fr" class="no-js ie9" lang="en"> <![endif]-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}">
	<head>
		<!--[if gte IE 9]
        <style type="text/css">
        .gradient { filter: none; }
        </style>
        <![endif]-->

{if isset($meta_title) AND $meta_title}
        <title>{$meta_title|escape:'htmlall':'UTF-8'}</title> 
{else}
        <title>Les colis du boucher – Livraison de viande Bio</title>
{/if}

{if isset($meta_description) AND $meta_description}
		<meta name="description" content="{$meta_description|escape:html:'UTF-8'}" />
{/if}
{if isset($meta_keywords) AND $meta_keywords}
		<meta name="keywords" content="{$meta_keywords|escape:html:'UTF-8'}" />
{/if }
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
		<meta http-equiv="content-language" content="{$meta_language}" />
		<meta name="generator" content="PrestaShop" />
		<meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" />
		<meta property="og:title" content="Les Colis du Boucher, la livraison de viande Label Rouge et bio à domicile" />
		<meta property="og:image" content="http://www.lescolisduboucher.com/img/facebook/logo_fb.png" />
		<meta property="og:type" content="Website" />
		<meta property="og:description" content="Les Colis du Boucher est une boutique en ligne qui est spécialisée dans la vente de viande bio et de viande Label Rouge livrée à votre domicile." />
		<meta property="og:url" content="http://www.lescolisduboucher.com" />
		<meta property="og:site_name" content="Les Colis du Boucher" />
		
		<link rel="icon" type="image/vnd.microsoft.icon" href="{$img_dir}favicon.ico?{$img_update_time}" />
		<link rel="shortcut icon" type="image/x-icon" href="{$img_dir}favicon.ico?{$img_update_time}" />
		<script type="text/javascript">
			var baseDir = '{$content_dir}';
			var baseUri = '{$base_uri}';
			var static_token = '{$static_token}';
			var token = '{$token}';
			var priceDisplayPrecision = {$priceDisplayPrecision*$currency->decimals};
			var priceDisplayMethod = {$priceDisplay};
			var roundMode = {$roundMode};
		</script>
		
		{if isset($css_files)}
		{foreach from=$css_files key=css_uri item=media}
		<link href="{$css_uri}" rel="stylesheet" type="text/css" media="{$media}" />
		{/foreach}
		{/if}
		{if isset($js_files)}
		{foreach from=$js_files item=js_uri}
		<script type="text/javascript" src="{$js_uri}"></script>
		{/foreach}
		{/if}

		{$HOOK_HEADER}

        <link rel="stylesheet" href="{$js_dir}jquery-ui/jquery-ui.css">
        <script src="{$js_dir}jquery-ui/jquery-ui.js"></script>


    </head>
	
	<body {if isset($page_name)}id="{$page_name|escape:'htmlall':'UTF-8'}"{/if} class="{if $hide_left_column}hide-left-column{/if} {if $hide_right_column}hide-right-column{/if} {if $content_only} content_only {/if}">



	{if !$content_only}
	
		{if isset($restricted_country_mode) && $restricted_country_mode}
		<div id="restricted-country">
			<p>{l s='You cannot place a new order from your country.'} <span class="bold">{$geolocation_country}</span></p>
		</div>
		{/if}
					
		<div id="page">
			<span class="ombre"></span>
			<header>
				<div class="link-logo">
					<span class="logo"></span>
					<a href="{$base_dir}" title="Les Colis du Boucher - Accueil">
						<h1>La meilleure viande bio et label rouge livrée chez vous !</h1>
					</a>
				</div>
				{$HOOK_TOP}
				
				<nav>
					<span></span>
					<ul>
                        <li class="produits1"><a href="{$link->getCategoryLink($menu_cats[0].id_category, $menu_cats[0].link_rewrite)|escape:'htmlall':'UTF-8'}" title="Nos produits">Nos produits</a>
                            <ul>
                                {foreach from=$menu_cats item=cat name=foo}
                                    <li class="{if $smarty.foreach.foo.first}first1 {/if}{if $smarty.foreach.foo.last}last1 {/if}">
                                        <a href="{$link->getCategoryLink($cat.id_category, $cat.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$cat.name}">{$cat.name}</a>
                                    </li>
                                {/foreach}
                            </ul>
                        </li>

                        <li class="demarche"><a href="#" title="Notre démarche">Notre démarche</a>
							<ul>
								{foreach from=$menu_approach item=page name=foo}
									<li class="{if $smarty.foreach.foo.first}first {/if}{if $smarty.foreach.foo.last}last {/if}"><a href="{$link->getCMSLink($page.id_cms, $page.link_rewrite)}" title="{$page.meta_title}">{$page.meta_title}</a></li>
								{/foreach}
							</ul>
						</li>
						<li class="recettes"><a href="#" title="Recettes">Recettes</a>
							<ul>
								{foreach from=$menu_recipe item=cat name=foo}
									
									{if isset($cat.subcats[0].id_recipe_category)}
										{$id_cat_url = $cat.subcats[0].id_recipe_category}
									{else}
										{$id_cat_url = $cat.id_recipe_category}
									{/if}
									
									<li class="{if $smarty.foreach.foo.first}first {/if}{if $smarty.foreach.foo.last}last {/if}">
                                        <a href="{$link->getRecipeCategoryLink($id_cat_url, false)|escape:'htmlall':'UTF-8'}" title="Veau">
                                            {$cat.name}
                                        </a>
                                    </li>
								{/foreach}
							</ul>
						</li>
						<li class="center"></li>
						<li class="village"><a href="#" title="Le village">Le village</a>
							<ul>
								<li class="first"><a href="{$link->getPageLink('guestbook', true)}" title="Parrainage">Témoignages</a></li>
							
								<li class=""><a href="{$link->getPageLink('post', true)}" title="Parrainage">Presse</a></li>
								<li class="last"><a href="{$base_dir}?fc=module&module=referralprogram&controller=program" title="Parrainage">Parrainage</a></li>
							</ul>
						</li>
						<li class="infos"><a href="#" title="Infos pratiques">Infos pratiques</a>
							<ul>
								{foreach from=$menu_infos item=page name=foo}
									<li class="{if $smarty.foreach.foo.first}first {/if}"><a href="{$link->getCMSLink($page.id_cms, $page.link_rewrite)}" title="{$page.meta_title}">{$page.meta_title}</a></li>
								{/foreach}
								<li><a href="{$link->getPageLink('delivery', true)}">Livraison</a></li>
								<li class="last"><a href="{$link->getCMSCategoryLink(4)}">Questions fréquentes</a></li>
							</ul>
						</li>
						<li class="contact"><a href="{$link->getPageLink('contact', true)}">Contact</a></li>
					</ul>
				</nav>
				{if $page_name!="index"}
					{include file="$tpl_dir./breadcrumb.tpl"}
				{/if}
			</header>

	{/if}
