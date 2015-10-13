<div id="columns" class="content clearfix">
	<div id="center_column" class="single">
		<div class="big-bloc">
			{if isset($cms) && !isset($cms_category)}
				{if !$cms->active}
					<br />
					<div id="admin-action-cms">
						<p>{l s='This CMS page is not visible to your customers.'}
						<input type="hidden" id="admin-action-cms-id" value="{$cms->id}" />
						<input type="submit" value="{l s='Publish'}" class="exclusive" onclick="submitPublishCMS('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 0, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
						<input type="submit" value="{l s='Back'}" class="exclusive" onclick="submitPublishCMS('{$base_dir}{$smarty.get.ad|escape:'htmlall':'UTF-8'}', 1, '{$smarty.get.adtoken|escape:'htmlall':'UTF-8'}')"/>
						</p>
						<div class="clear" ></div>
						<p id="admin-action-result"></p>
						</p>
					</div>
				{/if}
				<h1>{$cms->meta_title}</h1>
				<div class="rte{if $content_only} content_only{/if}">
					{$cms->content}
				</div>
			{elseif isset($cms_category) && $cms_category->id eq 4}
				<div class="block-cms">
					<h1>{$cms_category->name|escape:'htmlall':'UTF-8'}</h1>
					<p class="italique">
						{$cms_category->description} <span class="lien_vert"><a href="{$link->getPageLink('contact')|escape:'htmlall':'UTF-8'}" title="Page de contact">Contactez-nous.</a></span>
					</p>
					{if isset($sub_category) & !empty($sub_category)}
					<div>
						{foreach from=$sub_category item=subcategory}
							{if isset($subcategory.childrens)}	
								<div>
									<h2 class="titre_vert">{$subcategory.name|escape:'htmlall':'UTF-8'}</h2>
									<ul class="liste_FAQ">
										{foreach from=$subcategory.childrens item=page}
											<li>
												<a href="#">{$page.meta_title}</a>
												<div class="content rte{if $content_only} content_only{/if}">
													{$page.content}
												</div>
											</li>
										{/foreach}
									</ul>								
								</div>
							{/if}
						{/foreach}
					</div>
					{/if}
					{if isset($cms_pages) & !empty($cms_pages)}
					<p class="title_block">{l s='List of pages in %s:' sprintf=$cms_category->name}</p>
						<ul class="bullet">
							{foreach from=$cms_pages item=cmspages}
								<li>
									<a href="{$link->getCMSLink($cmspages.id_cms, $cmspages.link_rewrite)|escape:'htmlall':'UTF-8'}">{$cmspages.meta_title|escape:'htmlall':'UTF-8'}</a>
								</li>
							{/foreach}
						</ul>
					{/if}
				</div>
			{elseif isset($cms_category)}
				
				<div class="block-cms">
					<h1><a href="{if $cms_category->id eq 1}{$base_dir}{else}{$link->getCMSCategoryLink($cms_category->id, $cms_category->link_rewrite)}{/if}">{$cms_category->name|escape:'htmlall':'UTF-8'}</a></h1>
					{$cms_category->description}
					{if isset($sub_category) & !empty($sub_category)}	
						<p class="title_block">{l s='List of sub categories in %s:' sprintf=$cms_category->name}</p>
						<div>
							{foreach from=$sub_category item=subcategory}
								{if isset($subcategory.childrens)}	
									<div>
										<h2>{$subcategory.name|escape:'htmlall':'UTF-8'}</h2>
										<ul>
											{foreach from=$subcategory.childrens item=page}
												<li><a href="{$link->getCMSLink($page.id_cms, $page.link_rewrite)|escape:'htmlall':'UTF-8'}">{$page.meta_title}</a></li>
											{/foreach}
										</ul>								
									</div>
								{/if}
							{/foreach}
						</div>
					{/if}
					{if isset($cms_pages) & !empty($cms_pages)}
					<p class="title_block">{l s='List of pages in %s:' sprintf=$cms_category->name}</p>
						<ul class="bullet">
							{foreach from=$cms_pages item=cmspages}
								<li>
									<a href="{$link->getCMSLink($cmspages.id_cms, $cmspages.link_rewrite)|escape:'htmlall':'UTF-8'}">{$cmspages.meta_title|escape:'htmlall':'UTF-8'}</a>
								</li>
							{/foreach}
						</ul>
					{/if}
				</div>
			{else}
				<div class="error">
					{l s='This page does not exist.'}
				</div>
			{/if}
		</div>
	</div><!-- / #center_column -->
</div><!-- / .content -->

