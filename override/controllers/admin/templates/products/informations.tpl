
{if $check_product_association_ajax}
{assign var=class_input_ajax value='check_product_name '}
{else}
{assign var=class_input_ajax value=''}
{/if}
<input type="hidden" name="submitted_tabs[]" value="Informations" />
<div id="step1">
	<h4 class="tab">1. {l s='Info.'}</h4>
	<h4>{l s='Product global information'}</h4>

	<script type="text/javascript">
		{if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
			var PS_ALLOW_ACCENTED_CHARS_URL = 1;
		{else}
			var PS_ALLOW_ACCENTED_CHARS_URL = 0;
		{/if}
		{$combinationImagesJs}
		{if $check_product_association_ajax}
				var search_term = '';
				$('document').ready( function() {
					$(".check_product_name")
						.autocomplete(
							'{$link->getAdminLink('AdminProducts', true)}', {
								minChars: 3,
								max: 10,
								width: $(".check_product_name").width(),
								selectFirst: false,
								scroll: false,
								dataType: "json",
								formatItem: function(data, i, max, value, term) {
									search_term = term;
									// adding the little
									if ($('.ac_results').find('.separation').length == 0)
										$('.ac_results').css('background-color', '#EFEFEF')
											.prepend('<div style="color:#585A69; padding:2px 5px">{l s='Use a product from the list'}<div class="separation"></div></div>');
									return value;
								},
								parse: function(data) {
									var mytab = new Array();
									for (var i = 0; i < data.length; i++)
										mytab[mytab.length] = { data: data[i], value: data[i].name };
									return mytab;
								},
								extraParams: {
									ajax: 1,
									action: 'checkProductName',
									id_lang: {$id_lang}
								}
							}
						)
						.result(function(event, data, formatted) {
							// keep the searched term in the input
							$('#name_{$id_lang}').val(search_term);
							jConfirm('{l s='Do you want to use this product?'}&nbsp;<strong>'+data.name+'</strong>', '{l s='Confirmation'}', function(confirm){
								if (confirm == true)
									document.location.href = '{$link->getAdminLink('AdminProducts', true)}&updateproduct&id_product='+data.id_product;
								else
									return false;
							});
						});
				});
		{/if}
	</script>

	{if isset($display_common_field) && $display_common_field}
		<div class="warn" style="display: block">{l s='Warning, if you change the value of fields with an orange bullet %s, the value will be changed for all other shops for this product' sprintf=$bullet_common_field}</div>
	{/if}

	{include file="controllers/products/multishop/check_fields.tpl" product_tab="Informations"}

	<div class="separation"></div>
	<div id="warn_virtual_combinations" class="warn" style="display:none">{l s='You cannot use combinations with a virtual product.'}</div>

	<div style="height: 60px; position: relative;">
		<div>
			<label class="text">{$bullet_common_field} {l s='Type:'}</label>
			<input type="radio" name="type_product" id="simple_product" value="{Product::PTYPE_SIMPLE}" {if $product_type == Product::PTYPE_SIMPLE}checked="checked"{/if} />
			<label class="radioCheck" for="simple_product">{l s='Product'}</label>
			<input type="radio" name="type_product" id="pack_product" value="{Product::PTYPE_PACK}" {if $product_type == Product::PTYPE_PACK}checked="checked"{/if} />
			<label class="radioCheck" for="pack_product">{l s='Pack'}</label>
		</div>
		<div style="position: absolute; top: 0; left: 10px; height: 50px;">
			{foreach from=$features item=feature}
				{if ($feature.id_feature == 12) && ($feature.val.value == "Oui")}
					<img src="../img/admin/logo-label-rouge.png" alt="label rouge " style="height: 60px;">
				{elseif ($feature.id_feature == 11) && ($feature.val.value == "Oui")}
					<img src="../img/admin/logo-label-bio.jpg" alt="label bio" style="height: 60px;">
				{/if}
			{/foreach}
		</div>
	</div>

	<div class="separation"></div>
	<br />
	<table cellpadding="5" style="width: 50%; float: left; margin-right: 20px; border-right: 1px solid #CCCCCC;">
	{* global information *}
		<tr>
			<td class="col-left">
				{include file="controllers/products/multishop/checkbox.tpl" field="name" type="default" multilang="true"}
				<label>{l s='Name:'}</label>
			</td>
			<td style="padding-bottom:5px;" class="translatable">
			{foreach from=$languages item=language}
				<div class="lang_{$language.id_lang}" style="{if !$language.is_default}display: none;{/if} float: left;">
						<input class="{$class_input_ajax}{if !$product->id}copy2friendlyUrl{/if} updateCurrentText" size="43" type="text" {if !$product->id}disabled="disabled"{/if}
						id="name_{$language.id_lang}" name="name_{$language.id_lang}"
						value="{$product->name[$language.id_lang]|htmlentitiesUTF8|default:''}"/><sup> *</sup>
					<span class="hint" name="help_box">{l s='Invalid characters:'} <>;=#{}<span class="hint-pointer">&nbsp;</span>
					</span>
				</div>
			{/foreach}
			</td>
		</tr>
		<tr>
			<td class="col-left"><label>{$bullet_common_field} {l s='Reference:'}</label></td>
			<td style="padding-bottom:5px;">
				<input size="55" type="text" name="reference" value="{$product->reference|htmlentitiesUTF8}" style="width: 130px; margin-right: 44px;" />
				<span class="hint" name="help_box">{l s='Special characters allowed:'}.-_#\<span class="hint-pointer">&nbsp;</span></span>
			</td>
		</tr>
        <tr>
            <td class="col-left">
                {include file="controllers/products/multishop/checkbox.tpl" field="date_limit" type="radio" onclick=""}
                <label class="text">{l s='Date limit:'}</label>
            </td>
            <script>
                $(document).ready(function() {
                    enableLimitDate({$product->limit_date});
                });
            </script>
            <td style="padding-bottom:5px;">
                <ul class="listForm">
                    <li>
                        <input onclick="enableLimitDate(true);" type="radio" name="limit_date" id="limit_date_on" value="1" {if $product->limit_date}checked="checked" {/if} />
                        <label for="limit_date_on" class="radioCheck">{l s='Enabled'}</label>
                    </li>
                    <li>
                        <input onclick="enableLimitDate(false);" type="radio" name="limit_date" id="limit_date_off" value="0" {if !$product->limit_date}checked="checked"{/if} />
                        <label for="limit_date_off" class="radioCheck">{l s='Disabled'}</label>
                    </li>
                </ul>
            </td>
        </tr>
		<tr>
			<td class="col-left"><label>{l s='Date of start:'}</label></td>
			<td style="padding-bottom:5px;">
				<input id="date_start" name="date_start" value="{$product->date_start}" class="datepicker"
					style="text-align: center;" type="text" />
			</td>
		</tr>
		<tr>
			<td class="col-left"><label>{l s='Date of end:'}</label></td>
			<td style="padding-bottom:5px;">
				<input id="date_end" name="date_end" value="{$product->date_end}" class="datepicker"
					style="text-align: center;" type="text" />
			</td>
		</tr>
	</table>
	{* status informations *}
	<table cellpadding="5" style="width: 40%; float: left; margin-left: 10px;">
	<tr>
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="active" type="radio" onclick=""}
			<label class="text">{l s='Status:'}</label>
		</td>
		<td style="padding-bottom:5px;">
			<ul class="listForm">
				<li>
					<input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" type="radio" name="active" id="active_on" value="1" {if $product->active || !$product->isAssociatedToShop()}checked="checked" {/if} />
					<label for="active_on" class="radioCheck">{l s='Enabled'}</label>
				</li>
				<li>
					<input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);"  type="radio" name="active" id="active_off" value="0" {if !$product->active && $product->isAssociatedToShop()}checked="checked"{/if} />
					<label for="active_off" class="radioCheck">{l s='Disabled'}</label>
				</li>
			</ul>
		</td>
	</tr>
	<tr class="redirect_product_options" style="display:none">
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="active" type="radio" onclick=""}
			<label class="text">{l s='Redirect:'}</label>
		</td>
		<td style="padding-bottom:5px;">
			<select name="redirect_type" id="redirect_type">
				<option value="404" {if $product->redirect_type == '404'} selected="selected" {/if}>{l s='No redirect (404)'}</option>
				<option value="301" {if $product->redirect_type == '301'} selected="selected" {/if}>{l s='Redirect permanently (301)'}</option>
				<option value="302" {if $product->redirect_type == '302'} selected="selected" {/if}>{l s='Redirect temporarily (302)'}</option>
			</select>
			<span class="hint" name="help_box">
				{l s='404 : Not Found = Product does not exist and no redirect'}<br/>
				{l s='301 : Moved Permanently = Product Moved Permanently'}<br/>
				{l s='302 : Moved Temporarily = Product moved temporarily'}
			</span>
		</td>
	</tr>
	<tr class="redirect_product_options redirect_product_options_product_choise" style="display:none">
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="active" type="radio" onclick=""}
			<label class="text">{l s='Related product:'}</label>
		</td>
		<td style="padding-bottom:5px;">
			<input type="hidden" value="" name="id_product_redirected" />
			<input value="" id="related_product_autocomplete_input" autocomplete="off" class="ac_input" />
			<p>
				<script>
					var no_related_product = '{l s='No related product'}';
					var id_product_redirected = {$product->id_product_redirected|escape:html:'UTF-8'};
					var product_name_redirected = '{$product_name_redirected|escape:html:'UTF-8'}';
				</script>
				<span id="related_product_name">{l s='No related product'}</span>
				<span id="related_product_remove" style="display:none">
					<a hre="#" onclick="removeRelatedProduct(); return false" id="related_product_remove_link">
						<img src="../img/admin/delete.gif" class="middle" alt="" />
					</a>
				</span>
			</p>
		</td>
	</tr>
	<tr id="product_options" {if !$product->active}style="display:none"{/if} >
		<td class="col-left">
			{if isset($display_multishop_checkboxes) && $display_multishop_checkboxes}
				<div class="multishop_product_checkbox">
					<ul class="listForm">
						<li>{include file="controllers/products/multishop/checkbox.tpl" only_checkbox="true" field="available_for_order" type="default"}</li>
						<li>{include file="controllers/products/multishop/checkbox.tpl" only_checkbox="true" field="show_price" type="show_price"}</li>
						<li>{include file="controllers/products/multishop/checkbox.tpl" only_checkbox="true" field="abo" type="default"}</li>
						<li>{include file="controllers/products/multishop/checkbox.tpl" only_checkbox="true" field="unusual_product" type="default"}</li>
					</ul>
				</div>
			{/if}

			<label>{l s='Options:'}</label>
		</td>

		<td style="padding-bottom:5px;">
			<ul class="listForm">
				<li>
					<input  type="checkbox" name="available_for_order" id="available_for_order" value="1" {if $product->available_for_order}checked="checked"{/if}  />
					<label for="available_for_order" class="t">{l s='available for order'}</label>
				</li>
			<li>
				<input type="checkbox" name="show_price" id="show_price" value="1" {if $product->show_price}checked="checked"{/if} {if $product->available_for_order}disabled="disabled"{/if}/>
				<label for="show_price" class="t">{l s='show price'}</label>
			</li>
			<li>
				<input type="checkbox" name="abo" id="abo" value="1" {if $product->abo}checked="checked"{/if} />
				<label for="abo" class="t">{l s='Abonnement'}</label>
			</li>
			<li>
				<input type="checkbox" name="unusual_product" id="unusual_product" value="1" {if $product->unusual_product}checked="checked"{/if} />
				<label for="unusual_product" class="t">{l s='Unusual product'}</label>
			</li>
			</ul>
		</td>
	</tr>
</table>

<table cellpadding="5" cellspacing="0" border="0" style="width: 100%;"><tr><td><div class="separation"></div></td></tr></table>

<table>
	<tr>
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="active" type="radio" onclick=""}
			<label class="text">{l s='Product type:'}</label>
		</td>
		<td style="padding-bottom:5px;">
			<ul class="listForm">
				<li>
					<input type="checkbox" name="product_type_wtpork" id="product_type_wtpork" value="1" {if $product->product_type_wtpork}checked="checked"{/if} />
					<label for="product_type_wtpork" class="radioCheck">{l s='Without pork'}</label>
				</li>
				<li>
					<input type="checkbox" name="product_type_wtlamb" id="product_type_wtlamb" value="1" {if $product->product_type_wtlamb}checked="checked"{/if} />
					<label for="product_type_wtlamb" class="radioCheck">{l s='Without lamb'}</label>
				</li>
				<li>
					<input type="checkbox" name="product_type_bio" id="product_type_bio" value="1" {if $product->product_type_bio}checked="checked"{/if} />
					<label for="product_type_bio" class="radioCheck">{l s='100 bio'}</label>
				</li>
				<li>
					<input type="checkbox" name="product_type_cook" id="product_type_cook" value="1" {if $product->product_type_cook}checked="checked"{/if} />
					<label for="product_type_cook" class="radioCheck">{l s='Easy cooking'}</label>
				</li>
			</ul>
		</td>
	</tr>
</table>

<table cellpadding="5" cellspacing="0" border="0" style="width: 100%;"><tr><td><div class="separation"></div></td></tr></table>
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td class="col-left">
					{include file="controllers/products/multishop/checkbox.tpl" field="description_short" type="tinymce" multilang="true"}
					<label>{l s='Short description:'}<br /></label>
					<p class="product_description">({l s='appears in the product lists and on the top of the product page'})</p>
				</td>
				<td style="padding-bottom:5px;">
						{include file="controllers/products/textarea_lang.tpl"
						languages=$languages
						input_name='description_short'
						input_value=$product->description_short
						max=$PS_PRODUCT_SHORT_DESC_LIMIT}
					<p class="clear"></p>
				</td>
			</tr>
			<tr>
				<td class="col-left">
					{include file="controllers/products/multishop/checkbox.tpl" field="description" type="tinymce" multilang="true"}
					<label>{l s='Description:'}<br /></label>
					<p class="product_description">({l s='appears in the body of the product page'})</p>
				</td>
				<td style="padding-bottom:5px;">
						{include file="controllers/products/textarea_lang.tpl" languages=$languages
						input_name='description'
						input_value=$product->description
						}
					<p class="clear"></p>
				</td>
			</tr>
			<tr>
				<td class="col-left">
					{include file="controllers/products/multishop/checkbox.tpl" field="tricks" type="tinymce" multilang="true"}
					<label>{l s='tips and Tricks:'}<br /></label>
				</td>
				<td style="padding-bottom:5px;">
						{include file="controllers/products/textarea_lang.tpl" languages=$languages
						input_name='tricks'
						input_value=$product->tricks
						}
					<p class="clear"></p>
				</td>
			</tr>
			<tr>
				<td class="col-left">
					{include file="controllers/products/multishop/checkbox.tpl" field="breeder" type="tinymce" multilang="true"}
					<label>{l s='World of breeder:'}<br /></label>
				</td>
				<td style="padding-bottom:5px;">
						{include file="controllers/products/textarea_lang.tpl" languages=$languages
						input_name='breeder'
						input_value=$product->breeder
						}
					<p class="clear"></p>
				</td>
			</tr>
		{if $images}
			<tr>
				<td class="col-left"><label></label></td>
				<td style="padding-bottom:5px;">
					<div style="display:block;width:620px;" class="hint clear">
						{l s='Do you want an image associated with the product in your description?'}
						<span class="addImageDescription" style="cursor:pointer">{l s='Click here'}</span>.
					</div>
					<p class="clear"></p>
				</td>
			</tr>
			</table>
				<table id="createImageDescription" style="display:none;width:100%">
					<tr>
						<td colspan="2" height="10"></td>
					</tr>
					<tr>
						<td class="col-left"><label>{l s='Select your image:'}</label></td>
						<td style="padding-bottom:5px;">
							<ul class="smallImage">
							{foreach from=$images item=image key=key}
									<li>
										<input type="radio" name="smallImage" id="smallImage_{$key}" value="{$image.id_image}" {if $key == 0}checked="checked"{/if} >
										<label for="smallImage_{$key}" class="t">
											<img src="{$image.src}" alt="{$image.legend}" />
										</label>
									</li>
							{/foreach}
							</ul>
							<p class="clear"></p>
						</td>
					</tr>
					<tr>
						<td class="col-left"><label>{l s='Position:'}</label></td>
						<td style="padding-bottom:5px;">
							<ul class="listForm">
								<li><input type="radio" name="leftRight" id="leftRight_1" value="left" checked>
									<label for="leftRight_1" class="t">{l s='left'}</label>
								</li>
								<li>
									<input type="radio" name="leftRight" id="leftRight_2" value="right">
									<label for="leftRight_2" class="t">{l s='right'}</label>
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td class="col-left"><label>{l s='Select the type of picture:'}</label></td>
						<td style="padding-bottom:5px;">
							<ul class="listForm">
							{foreach from=$imagesTypes key=key item=type}
								<li><input type="radio" name="imageTypes" id="imageTypes_{$key}" value="{$type.name}" {if $key == 0}checked="checked"{/if}>
									<label for="imageTypes_{$key}" class="t">{$type.name} <span>({$type.width}px {l s='by'} {$type.height}px)</span></label>
								</li>
							{/foreach}
							</ul>
							<p class="clear"></p>
						</td>
					</tr>
					<tr>
						<td class="col-left"><label>{l s='Image tag to insert:'}</label></td>
						<td style="padding-bottom:5px;">
							<input type="text" id="resultImage" name="resultImage" />
							<p class="preference_description">{l s='The tag to copy/paste into the description.'}</p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="separation"></div>
						</td>
					</tr>
				</table>
		{/if}
	</table>
	<br />
</div>
