{if isset($products)}
        {if isset($promoFilter) AND ($products|count == 0)}
            <div class="warning">Aucune promotion pour le moment.</div>
        {/if}
        {foreach from=$products item=product name=products}

            {$now = $smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}

            <!--Display IN-Date product only AND product with combinations only-->
            {if (!$product.limit_date or
            (($now >= $product.date_start) && ($now < $product.date_end)))
			AND (count($product.combinations) || isset($search))}

                <!--Init product label variable-->
                {assign var="label" value=""}
                <!--Assign label by looking throught all features-->
                {foreach from=$product.features item=feature name=feature}
                    {if ($feature.id_feature == $id_feature_label_bio) && ($feature.value|lower == "oui")}
                        <!--Assign label to "label-bio"-->
                        {assign var="label" value="label-bio"}
                    {/if}
                    {if ($feature.id_feature == $id_feature_label_rouge) && ($feature.value|lower == "oui")}
                        <!--Assign label to "label-rouge"-->
                        {assign var="label" value="label-rouge"}
                    {/if}
                {/foreach}

                <!--Display product block-->
                <div class="block-product" itemscope itemtype="http://schema.org/Product">

                    <!--INFORMATION-->
                    <div class="{if isset($ymlb) & $ymlb}ymlb-info{else}infos{/if}">
                        <!--Identification-->
                        <div class="identification-description">
                            <div class="identification label">
                                <!--Product Name (link) -->
                                <a href="{$product.link|escape:'htmlall':'UTF-8'}" class="name">
                                    <h3 itemprop="name">{$product.name|escape:'htmlall':'UTF-8'|truncate:35:'...'}</h3>
                                </a>
                                <!--Product Details-->
                                <p itemprop="description">
                                    {$product.description_short|strip_tags:'UTF-8'|truncate:100:'...'}
                                    <!--product link-->
                                    <a href="{$product.link|escape:'htmlall':'UTF-8'}" class="product-link slideDown {if isset($ymlb) & $ymlb}ymlb-hide{/if}">{l s='Commander'}</a>
                                    <!--The seconde link is Hidden in lcdb-theme/js/product-list.js-->
                                    <a href="{$product.link|escape:'htmlall':'UTF-8'}" class="product-link slideUp {if isset($ymlb) & $ymlb}ymlb-hide{/if}">{l s='Réduire'}</a>
                                </p>
                            </div>
                        </div>
                        <!--# identification->
                        <!--Detail (person and presrvations)-->
                        <div class="detail">
                            {foreach from=$product.features item=feature name=feature}
                                {if ($feature.id_feature == $id_feature_number_of)}
                                    <span class="person">x{$feature.value}</span>
                                {/if}
                                {if ($feature.id_feature == $id_feature_preservation)}
                                    <span class="preservation">{$feature.value}j</span>
                                {/if}
                            {/foreach}

                        </div>
                        <!--Price-->
                        <div class="price reduction" itemscope itemtype="http://schema.org/Offer">
                            {if isset($product.reduction) && ($product.reduction != 0)}
                                <!--Reduction price-->
                                {if $product.specific_prices.reduction_type == "percentage"}
                                    <p class="reduction_rate">-{$product.specific_prices.reduction*100}%</p>
                                {else}
                                    <p class="reduction_rate">-{convertPrice price=$product.specific_prices.reduction}</p>
                                {/if}
                            {/if}
                        </div>
                    </div>

                    <!--ACTION (add number, add to basket, etc...)-->
                    <div class="action-product {if isset($ymlb) & $ymlb}ymlb-show{/if}">
                        <!--{if count($product.combinations)}-->
                        {foreach from=$product.combinations item=combination}
                            {if $combination.available_quantity != 0}
								{if $combination.label_name == 'Le Bourdonnec'}
									<form class="form-panier declinaison {$combination.label_name}" method="post" action="{$link->getPageLink('cart')}" >

										<div class="ymlb-product-photo">
											{if isset($product.cover) & !empty($product.cover)}
												{assign var=imageIds value="`$product.id_product`-`$product.cover.id_image`"}
												<img src="{$link->getImageLink($product.link_rewrite, $imageIds)}">
											{else}
												<img src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/product_le-bourdonnec.png" />
											{/if}
										</div>
											
										<div class="ymlb-product-desc">
												{$product.description}
										</div>
											
										<br /><br />
										<!--Price-->
										{if isset($product.price_tax_inc)}
											{assign var="product_price" value=$product.price_tax_inc}
										{else}
											{assign var="product_price" value=$product.price}
										{/if}

										<span class="price-kg price-kg-ymlb">
												{convertPrice price=(($product_price/$product.unit_price_ratio) + $combination.unit_price_impact)}/{$product.unity}
											</span>

										<span class="selling_price">{convertPrice price=($product_price + $combination.price_impact)}</span>
										<br /><br />
										<!--Quantity-->
										<button class="moreless minus" name="minus" type="button">-</button>
										<input class="quantity" type="text" name="qty" value="1" maxlength="2">
										<button jsdata_quantity_available="{$combination.available_quantity}" class="moreless plus" name="plus" type="button">+</button>

										<!--Hidden input => Id-product #debug({$product.id_product|intval})| Id-declinaison #debug({$combination.id_product_attribute})-->
										<input type="hidden" name="token" value="{$static_token}" />
										<input type="hidden" name="id_product" value="{$product.id_product|intval}" id="product_page_product_id" />
										<input type="hidden" name="add" value="1" />
										<input type="hidden" name="id_product_attribute" value="{$combination.id_product_attribute}"/>

										<!--Button add-->
										<button class="green-button gradient" name="submit" type="submit">Ajouter</button>

										<!--Warning message-->
										{if $combination.available_quantity < 5 and $combination.available_quantity > 0}
											<p class="warning" itemscope itemtype="http://schema.org/Offer">
												Plus que {$combination.available_quantity} {$product.name|escape:'htmlall':'UTF-8'|truncate:15:'[...]'} {$combination.label_name} restants.
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
													du <span itemprop="availabilityEnds">{$combination.begin_date|date_format:"%d %B"}</span> au
												{else}
													jusqu'au
												{/if}
												<span itemprop="availabilityEnds">{$combination.available_date|date_format:"%d %B"}</span>
											</p>
										{/if}
									</form>
								{else}
									<!--Declainaison #debug:{$combination.label_name}-->
									<form class="form-panier declinaison {$combination.label_name}" method="post" action="{$link->getPageLink('cart')}" >
										<!--Label image-->
										{$label_image = [
										'selection'     =>  'les_colis_du_boucher_logo_2.png',
										'salers'     =>  'les_colis_du_boucher_logo_2.png',
										'bio'           =>  'logo-bio-simple-wide.png',
										'label rouge'   =>  'label_rouge_logo_full.png',
										'Le Bourdonnec'   =>  'product_le-bourdonnec.png'
										]}
										<span class="label">
												<img alt='logo-{$combination.label_name}' class='logo-label' src="{$base_dir}themes/lcdb_theme/img/asset/img_solo/{$label_image[{$combination.label_name}]}">
												<span class="name">{$combination.label_name|@ucfirst}</span>
											</span>
										{if $combination.isPromo}
											<span class="promo">Promo</span>
										{/if}
										<!--Price-->
										{if isset($product.price_tax_inc)}
											{assign var="product_price" value=$product.price_tax_inc}
										{else}
											{assign var="product_price" value=$product.price}
										{/if}

										<span class="price-kg">
												{convertPrice price=(($product_price/$product.unit_price_ratio) + $combination.unit_price_impact)}/{$product.unity}
											</span>

										<span class="selling_price">{convertPrice price=($product_price + $combination.price_impact)}</span>
										<!--Quantity-->
										<button class="moreless minus" name="minus" type="button">-</button>
										<input class="quantity" type="text" name="qty" value="1" maxlength="2">
										<button jsdata_quantity_available="{$combination.available_quantity}" class="moreless plus" name="plus" type="button">+</button>

										<!--Hidden input => Id-product #debug({$product.id_product|intval})| Id-declinaison #debug({$combination.id_product_attribute})-->
										<input type="hidden" name="token" value="{$static_token}" />
										<input type="hidden" name="id_product" value="{$product.id_product|intval}" id="product_page_product_id" />
										<input type="hidden" name="add" value="1" />
										<input type="hidden" name="id_product_attribute" value="{$combination.id_product_attribute}"/>

										<!--Button add-->
										<button class="green-button gradient" name="submit" type="submit">Ajouter</button>

										<!--Warning message-->
										{if $combination.available_quantity < 5 and $combination.available_quantity > 0}
											<p class="warning" itemscope itemtype="http://schema.org/Offer">
												Plus que {$combination.available_quantity} {$product.name|escape:'htmlall':'UTF-8'|truncate:15:'[...]'} {$combination.label_name} restants.
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
													du <span itemprop="availabilityEnds">{$combination.begin_date|date_format:"%d %B"}</span> au
												{else}
													jusqu'au
												{/if}
												<span itemprop="availabilityEnds">{$combination.available_date|date_format:"%d %B"}</span>
											</p>
										{/if}
									</form>
								{/if} {* fin if ymlb *}
                            {/if}


                        {/foreach}
                        <!--{else}
                        Aucune declinaison pour ce produit.
                    {/if}-->
					{if isset($product.link)}
                        <a class="product-link more-details" href="{$product.link|escape:'htmlall':'UTF-8'}">Plus de détails > </a>
					{/if}
                    </div>
                </div>

            {/if}
        {/foreach}
{/if}