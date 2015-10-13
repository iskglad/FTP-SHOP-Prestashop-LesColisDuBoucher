<?php

class AdminProductsController extends AdminProductsControllerCore
{

    public function __construct()
    {

        parent::__construct();

        if ($this->context->shop->getContext() != Shop::CONTEXT_GROUP){
            $this->available_tabs = array_merge(array(
                'Informations' => 0,
                'Pack' => 7,
                'Prices' => 1,
                'Quantities' => 6,
                'Seo' => 2,
				'Images' => $this->l('Images'),
                'Associations' => 3,
                'Shipping' => 4,
                'Features' => 11,
                'Combinations' => 10,
                'Suppliers' => 13,
            ));
        }

        $this->_select .= ',
			(SELECT fvl.value FROM `'._DB_PREFIX_.'feature_value_lang` fvl
				LEFT JOIN '._DB_PREFIX_.'feature_product fp ON fvl.id_feature_value = fp.id_feature_value
				WHERE fp.id_product = a.id_product AND fp.id_feature=12 AND fvl.id_lang=1) as label_rouge,
			(SELECT fvl.value FROM `'._DB_PREFIX_.'feature_value_lang` fvl
				LEFT JOIN '._DB_PREFIX_.'feature_product fp ON fvl.id_feature_value = fp.id_feature_value
				WHERE fp.id_product = a.id_product AND fp.id_feature=11 AND fvl.id_lang=1) as label_bio,
			(SELECT fvl.value FROM `'._DB_PREFIX_.'feature_value_lang` fvl
				LEFT JOIN '._DB_PREFIX_.'feature_product fp ON fvl.id_feature_value = fp.id_feature_value
				WHERE fp.id_product = a.id_product AND fp.id_feature=7 AND fvl.id_lang=1) as count,
			(SELECT fvl.value FROM `'._DB_PREFIX_.'feature_value_lang` fvl
				LEFT JOIN '._DB_PREFIX_.'feature_product fp ON fvl.id_feature_value = fp.id_feature_value
				WHERE fp.id_product = a.id_product AND fp.id_feature=13 AND fvl.id_lang=1) as weight,
			(a.price / a.unit_price_ratio) as unitPrice';


        $this->fields_list = array();
        $this->fields_list['id_product'] = array(
            'title' => $this->l('ID'),
            'align' => 'center',
            'width' => 20
        );
        $this->fields_list['name'] = array(
            'title' => $this->l('Name'),
            'filter_key' => 'b!name',
            'width' => "auto"
        );
        $this->fields_list['name_category'] = array(
            'title' => $this->l('Category'),
            'width' => "auto",
            'filter_key' => 'cl!name',
        );
        $this->fields_list['count'] = array(
            'title' => $this->l('Personnes'),
            'width' => 60,
            'havingFilter' => true
        );
        $this->fields_list['weight'] = array(
            'title' => $this->l('Poids'),
            'width' => 60,
            'havingFilter' => true
        );
        $this->fields_list['unitPrice'] = array(
            'title' => $this->l('Price/kg'),
            'width' => 90,
            'type' => 'price',
            'align' => 'right',
            'filter_key' => 'a!price'
        );
        $this->fields_list['price_final'] = array(
            'title' => $this->l('Price'),
            'width' => 90,
            'type' => 'price',
            'align' => 'right',
            'havingFilter' => true,
            'orderby' => false
        );
        if ((int)Tools::getValue('id_category'))
            $this->fields_list['position'] = array(
                'title' => $this->l('Position'),
                'width' => 70,
                'filter_key' => 'cp!position',
                'align' => 'center',
                'position' => 'position'
            );
    }

    public function initFormInformations($product)
    {
        $data = $this->createTemplate($this->tpl_form);

        $currency = $this->context->currency;
        $data->assign('languages', $this->_languages);
        $data->assign('currency', $currency);
        $this->object = $product;
        $this->display = 'edit';
        $data->assign('product_name_redirected', Product::getProductName((int)$product->id_product_redirected, null, (int)$this->context->language->id));
        /*
        * Form for adding a virtual product like software, mp3, etc...
        */
        $product_download = new ProductDownload();
        if ($id_product_download = $product_download->getIdFromIdProduct($this->getFieldValue($product, 'id')))
            $product_download = new ProductDownload($id_product_download);
        $product->{'productDownload'} = $product_download;

        $cache_default_attribute = (int)$this->getFieldValue($product, 'cache_default_attribute');

        $product_props = array();
        // global informations
        array_push($product_props, 'reference', 'ean13', 'upc',
            'available_for_order', 'show_price', 'online_only',
            'id_manufacturer'
        );

        // specific / detailled information
        array_push($product_props,
            // physical product
            'width', 'height', 'weight', 'active', 'is_subscription',
            // virtual product
            'is_virtual', 'cache_default_attribute',
            // customization
            'uploadable_files', 'text_fields'
        );
        // prices
        array_push($product_props,
            'price', 'wholesale_price', 'id_tax_rules_group', 'unit_price_ratio', 'on_sale',
            'unity', 'minimum_quantity', 'additional_shipping_cost',
            'available_now', 'available_later', 'available_date'
        );

        if (Configuration::get('PS_USE_ECOTAX'))
            array_push($product_props, 'ecotax');

        foreach ($product_props as $prop)
            $product->$prop = $this->getFieldValue($product, $prop);

        $product->name['class'] = 'updateCurrentText';
        if (!$product->id)
            $product->name['class'] .= ' copy2friendlyUrl';

        $images = Image::getImages($this->context->language->id, $product->id);

        foreach ($images as $k => $image)
            $images[$k]['src'] = $this->context->link->getImageLink($product->link_rewrite[$this->context->language->id], $product->id.'-'.$image['id_image'], 'small_default');
        $data->assign('images', $images);
        $data->assign('imagesTypes', ImageType::getImagesTypes('products'));

        $product->tags = Tag::getProductTags($product->id);

        $data->assign('product_type', (int)Tools::getValue('type_product', $product->getType()));

        $check_product_association_ajax = false;
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL)
            $check_product_association_ajax = true;

        $features = $product->getFeatures();

        foreach ($features as $k => $tab_features)
        {
            $values = FeatureValue::getFeatureValueLang($features[$k]['id_feature_value']);
            $features[$k]['val'] = $values[0];
        }

        $data->assign('features', $features);

        // TinyMCE
        $iso_tiny_mce = $this->context->language->iso_code;
        $iso_tiny_mce = (file_exists(_PS_JS_DIR_.'tiny_mce/langs/'.$iso_tiny_mce.'.js') ? $iso_tiny_mce : 'en');
        $data->assign('ad', dirname($_SERVER['PHP_SELF']));
        $data->assign('iso_tiny_mce', $iso_tiny_mce);
        $data->assign('check_product_association_ajax', $check_product_association_ajax);
        $data->assign('id_lang', $this->context->language->id);
        $data->assign('product', $product);
        $data->assign('token', $this->token);
        $data->assign('currency', $currency);
        $data->assign($this->tpl_form_vars);
        $data->assign('link', $this->context->link);
        $data->assign('PS_PRODUCT_SHORT_DESC_LIMIT', Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') ? Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') : 400);
        $this->tpl_form_vars['product'] = $product;
        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function initFormAssociations($obj)
    {
        $product = $obj;
        $data = $this->createTemplate($this->tpl_form);
        // Prepare Categories tree for display in Associations tab
        $root = Category::getRootCategory();
        $default_category = Tools::getValue('id_category', Context::getContext()->shop->id_category);

        if (!$product->id || !$product->isAssociatedToShop())
            $selected_cat = Category::getCategoryInformations(Tools::getValue('categoryBox', array($default_category)), $this->default_form_language);
        else
        {
            if (Tools::isSubmit('categoryBox'))
                $selected_cat = Category::getCategoryInformations(Tools::getValue('categoryBox', array($default_category)), $this->default_form_language);
            else
                $selected_cat = Product::getProductCategoriesFull($product->id, $this->default_form_language);
        }

        // Multishop block
        $data->assign('feature_shop_active', Shop::isFeatureActive());
        $helper = new HelperForm();
        if ($this->object && $this->object->id)
            $helper->id = $this->object->id;
        else
            $helper->id = null;
        $helper->table = $this->table;
        $helper->identifier = $this->identifier;

        // Accessories block
        $accessories = Product::getAccessoriesLight($this->context->language->id, $product->id);
        if ($post_accessories = Tools::getValue('inputAccessories'))
        {
            $post_accessories_tab = explode('-', Tools::getValue('inputAccessories'));
            foreach ($post_accessories_tab as $accessory_id)
                if (!$this->haveThisAccessory($accessory_id, $accessories) && $accessory = Product::getAccessoryById($accessory_id))
                    $accessories[] = $accessory;
        }
        $data->assign('accessories', $accessories);

        // recipe block
        $recipes = Product::getRecipesLight($this->context->language->id, $product->id);
        if ($post_recipes = Tools::getValue('inputRecipes'))
        {
            $post_recipes_tab = explode('-', Tools::getValue('inputRecipes'));
            foreach ($post_recipes_tab as $recipe_id){
                if (!$this->haveThisRecipe($recipe_id, $recipes) && $recipe = Product::getRecipeById($recipe_id)){
                    $recipes[] = $recipe;
                }
            }

        }
        $data->assign('recipes', $recipes);

        $product->manufacturer_name = Manufacturer::getNameById($product->id_manufacturer);

        $tab_root = array('id_category' => $root->id, 'name' => $root->name);
        $helper = new Helper();
        $category_tree = $helper->renderCategoryTree($tab_root, $selected_cat, 'categoryBox', false, true, array(), false, true);
        $data->assign(array('default_category' => $default_category,
            'selected_cat_ids' => implode(',', array_keys($selected_cat)),
            'selected_cat' => $selected_cat,
            'id_category_default' => $product->getDefaultCategory(),
            'category_tree' => $category_tree,
            'product' => $product,
            'link' => $this->context->link,
            'is_shop_context' => Shop::getContext() == Shop::CONTEXT_SHOP
        ));

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function updateRecipes($product)
    {

        $product->deleteRecipes();
        if ($recipes = Tools::getValue('inputRecipes'))
        {
            $recipes_id = array_unique(explode('-', $recipes));
            if (count($recipes_id))
            {
                array_pop($recipes_id);
                $product->changeRecipes($recipes_id);
            }
        }
    }

    public function haveThisRecipe($recipe_id, $recipes)
    {
        foreach ($recipes as $recipe)
            if ((int)$recipe['id_recipe'] == (int)$recipe_id)
                return true;
        return false;
    }

    public function renderForm()
    {
        // This nice code (irony) is here to store the product name, because the row after will erase product name in multishop context
        $this->product_name = $this->object->name[$this->context->language->id];

        if (!method_exists($this, 'initForm'.$this->tab_display))
            return;

        $product = $this->object;
        $this->product_exists_in_shop = true;
        if ($this->display == 'edit' && Validate::isLoadedObject($product) && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP && !$product->isAssociatedToShop($this->context->shop->id))
        {
            $this->product_exists_in_shop = false;
            if ($this->tab_display == 'Informations')
                $this->displayWarning($this->l('Warning: this product does not exist in this shop.'));

            $default_product = new Product();
            $fields_to_copy = array('minimal_quantity',
                'price',
                'additional_shipping_cost',
                'wholesale_price',
                'on_sale',
                'online_only',
                'unity',
                'unit_price_ratio',
                'ecotax',
                'active',
                'available_for_order',
                'available_date',
                'show_price',
                'indexed',
                'id_tax_rules_group',
                'advanced_stock_management');
            foreach ($fields_to_copy as $field)
                $product->$field = $default_product->$field;
        }

        // Product for multishop
        $this->context->smarty->assign('bullet_common_field', '');
        if (Shop::isFeatureActive() && $this->display == 'edit')
        {
            if (Shop::getContext() != Shop::CONTEXT_SHOP)
            {
                $this->context->smarty->assign(array(
                    'display_multishop_checkboxes' => true,
                    'multishop_check' => Tools::getValue('multishop_check'),
                ));
            }

            if (Shop::getContext() != Shop::CONTEXT_ALL)
            {
                $this->context->smarty->assign('bullet_common_field', '<img src="themes/'.$this->context->employee->bo_theme.'/img/bullet_orange.png" style="vertical-align: bottom" />');
                $this->context->smarty->assign('display_common_field', true);
            }
        }

        $this->tpl_form_vars['tabs_preloaded'] = $this->available_tabs;

        $this->tpl_form_vars['product_type'] = (int)Tools::getValue('type_product', $product->getType());

        $this->getLanguages();

        $this->tpl_form_vars['id_lang_default'] = Configuration::get('PS_LANG_DEFAULT');

        $this->tpl_form_vars['currentIndex'] = self::$currentIndex;
        $this->tpl_form_vars['display_multishop_checkboxes'] = (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP && $this->display == 'edit');
        $this->fields_form = array('');
        $this->display = 'edit';
        $this->tpl_form_vars['token'] = $this->token;
        $this->tpl_form_vars['combinationImagesJs'] = $this->getCombinationImagesJs();
        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        $this->tpl_form_vars['post_data'] = Tools::jsonEncode($_POST);
        $this->tpl_form_vars['save_error'] = !empty($this->errors);

        // autoload rich text editor (tiny mce)
        $this->tpl_form_vars['tinymce'] = true;
        $iso = $this->context->language->iso_code;
        $this->tpl_form_vars['iso'] = file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en';
        $this->tpl_form_vars['ad'] = dirname($_SERVER['PHP_SELF']);

        if (Validate::isLoadedObject(($this->object)))
            $id_product = (int)$this->object->id;
        else
            $id_product = (int)Tools::getvalue('id_product');

        $this->tpl_form_vars['form_action'] = $this->context->link->getAdminLink('AdminProducts').'&amp;'.($id_product ? 'id_product='.(int)$id_product : 'addproduct');
        $this->tpl_form_vars['id_product'] = $id_product;

        // Transform configuration option 'upload_max_filesize' in octets
        $upload_max_filesize = Tools::getOctets(ini_get('upload_max_filesize'));

        // Transform configuration option 'upload_max_filesize' in MegaOctets
        $upload_max_filesize = ($upload_max_filesize / 1024) / 1024;

        $this->tpl_form_vars['upload_max_filesize'] = $upload_max_filesize;
        $this->tpl_form_vars['country_display_tax_label'] = $this->context->country->display_tax_label;
        $this->tpl_form_vars['has_combinations'] = $this->object->hasAttributes();

        // let's calculate this once for all
        if (!Validate::isLoadedObject($this->object) && Tools::getValue('id_product'))
            $this->errors[] = 'Unable to load object';
        else
        {
            $this->_displayDraftWarning($this->object->active);

            // if there was an error while saving, we don't want to lose posted data
            if (!empty($this->errors))
                $this->copyFromPost($this->object, $this->table);

            $this->initPack($this->object);
            $this->{'initForm'.$this->tab_display}($this->object);
            $this->tpl_form_vars['product'] = $this->object;
            if ($this->ajax)
                if (!isset($this->tpl_form_vars['custom_form']))
                    throw new PrestaShopException('custom_form empty for action '.$this->tab_display);
                else
                    return $this->tpl_form_vars['custom_form'];
        }
        $parent = parent::renderForm();
        $this->addJqueryPlugin(array('autocomplete', 'fancybox', 'typewatch'));
        return $parent;
    }

    public function initFormPrices($obj)
    {
        $data = $this->createTemplate($this->tpl_form);
        $product = $obj;
        if ($obj->id)
        {
            $shops = Shop::getShops();
            $countries = Country::getCountries($this->context->language->id);
            $groups = Group::getGroups($this->context->language->id);
            $currencies = Currency::getCurrencies();
            $attributes = $obj->getAttributesGroups((int)$this->context->language->id);
            $combinations = array();
            foreach ($attributes as $attribute)
            {
                $combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
                if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
                    $combinations[$attribute['id_product_attribute']]['attributes'] = '';
                $combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';

                $combinations[$attribute['id_product_attribute']]['price'] = Tools::displayPrice(
                    Tools::convertPrice(
                        Product::getPriceStatic((int)$obj->id, false, $attribute['id_product_attribute']),
                        $this->context->currency
                    ), $this->context->currency
                );
            }
            foreach ($combinations as &$combination)
                $combination['attributes'] = rtrim($combination['attributes'], ' - ');
            $data->assign('specificPriceModificationForm', $this->_displaySpecificPriceModificationForm(
                    $this->context->currency, $shops, $currencies, $countries, $groups)
            );

            $data->assign('ecotax_tax_excl', $obj->ecotax);
            $this->_applyTaxToEcotax($obj);

            $data->assign(array(
                'shops' => $shops,
                'admin_one_shop' => count($this->context->employee->getAssociatedShops()) == 1,
                'currencies' => $currencies,
                'countries' => $countries,
                'groups' => $groups,
                'combinations' => $combinations,
                'product' => $product,
                'multi_shop' => Shop::isFeatureActive(),
                'link' => new Link()
            ));
        }
        else
            $this->displayWarning($this->l('You must save this product before adding specific prices'));

        // prices part
        $data->assign(array(
            'link' => $this->context->link,
            'currency' => $currency = $this->context->currency,
            'tax_rules_groups' => TaxRulesGroup::getTaxRulesGroups(true),
            'taxesRatesByGroup' => TaxRulesGroup::getAssociatedTaxRatesByIdCountry($this->context->country->id),
            'ecotaxTaxRate' => Tax::getProductEcotaxRate(),
            'tax_exclude_taxe_option' => Tax::excludeTaxeOption(),
            'ps_use_ecotax' => Configuration::get('PS_USE_ECOTAX'),
            'ecotax_tax_excl' => 0
        ));

        $product->price = Tools::convertPrice($product->price, $this->context->currency, true, $this->context);
        if ($product->unit_price_ratio != 0)
            $data->assign('unit_price', Tools::ps_round($product->price / $product->unit_price_ratio, 2));
        else
            $data->assign('unit_price', 0);
        $data->assign('ps_tax', Configuration::get('PS_TAX'));

        $data->assign('country_display_tax_label', $this->context->country->display_tax_label);
        $data->assign(array(
            'currency', $this->context->currency,
            'product' => $product,
            'token' => $this->token
        ));

        // display gap
        $gap = $product->price - $product->wholesale_price ;
        $data->assign(array(
            'gap' => $gap
        ));

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function processAdd()
    {
        $this->checkProduct();

        if (!empty($this->errors))
        {
            $this->display = 'add';
            return false;
        }

        $this->object = new $this->className();
        $this->_removeTaxFromEcotax();
        $this->copyFromPost($this->object, $this->table);

        if ($this->object->add())
        {
            $this->addCarriers();
            $this->updateAccessories($this->object);
            $this->updateRecipes($this->object);
            $this->updatePackItems($this->object);
            $this->updateDownloadProduct($this->object);

            if (empty($this->errors))
            {
                $languages = Language::getLanguages(false);
                if ($this->isProductFieldUpdated('category_box') && !$this->object->updateCategories(Tools::getValue('categoryBox')))
                    $this->errors[] = Tools::displayError('An error occurred while linking object.').' <b>'.$this->table.'</b> '.Tools::displayError('To categories');
                elseif (!$this->updateTags($languages, $this->object))
                    $this->errors[] = Tools::displayError('An error occurred while adding tags.');
                else
                {
                    Hook::exec('actionProductAdd', array('product' => $this->object));
                    if (in_array($this->object->visibility, array('both', 'search')) && Configuration::get('PS_SEARCH_INDEXATION'))
                        Search::indexation(false, $this->object->id);
                }

                // Save and preview
                if (Tools::isSubmit('submitAddProductAndPreview'))
                {
                    $preview_url = $this->context->link->getProductLink(
                        $this->getFieldValue($this->object, 'id'),
                        $this->getFieldValue($this->object, 'link_rewrite', $this->context->language->id),
                        Category::getLinkRewrite($this->getFieldValue($this->object, 'id_category_default'), $this->context->language->id),
                        null,
                        null,
                        Context::getContext()->shop->id,
                        0,
                        (bool)Configuration::get('PS_REWRITING_SETTINGS')
                    );

                    if (!$this->object->active)
                    {
                        $admin_dir = dirname($_SERVER['PHP_SELF']);
                        $admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);
                        $preview_url .= '&adtoken='.$this->token.'&ad='.$admin_dir.'&id_employee='.(int)$this->context->employee->id;
                    }

                    $this->redirect_after = $preview_url;
                }

                // Save and stay on same form
                if ($this->display == 'edit')
                    $this->redirect_after = self::$currentIndex.'&id_product='.(int)$this->object->id
                        .(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '')
                        .'&updateproduct&conf=3&key_tab='.Tools::safeOutput(Tools::getValue('key_tab')).'&token='.$this->token;
                else
                    // Default behavior (save and back)
                    $this->redirect_after = self::$currentIndex
                        .(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '')
                        .'&conf=3&token='.$this->token;
            }
            else
                $this->object->delete();
            // if errors : stay on edit page
            $this->display = 'edit';
        }
        else
            $this->errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.'</b>';

        return $this->object;
    }

    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);

        $object->abo = (int)Tools::getValue('abo');
        $object->unusual_product = (int)Tools::getValue('unusual_product');

        $object->product_type_bio = (int)Tools::getValue('product_type_bio');
        $object->product_type_cook = (int)Tools::getValue('product_type_cook');
        $object->product_type_wtlamb = (int)Tools::getValue('product_type_wtlamb');
        $object->product_type_wtpork = (int)Tools::getValue('product_type_wtpork');

        $object->limit_date = (int)Tools::getValue('limit_date');
    }

    public function processUpdate()
    {
        $this->checkProduct();

        if (!empty($this->errors))
        {
            $this->display = 'edit';
            return false;
        }

        $id = (int)Tools::getValue('id_'.$this->table);
        /* Update an existing product */
        if (isset($id) && !empty($id))
        {
            $object = new $this->className((int)$id);
            $this->object = $object;

            if (Validate::isLoadedObject($object))
            {
                $this->_removeTaxFromEcotax();
                $this->copyFromPost($object, $this->table);
                $object->indexed = 0;

                if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP)
                    $object->setFieldsToUpdate((array)Tools::getValue('multishop_check'));

                if ($object->update())
                {
                    if (in_array($this->context->shop->getContext(), array(Shop::CONTEXT_SHOP, Shop::CONTEXT_ALL)))
                    {

                        if ($this->isTabSubmitted('Shipping'))
                            $this->addCarriers();
                        if ($this->isTabSubmitted('Associations')){
                            $this->updateAccessories($object);
                            $this->updateRecipes($object);
                        }
                        if ($this->isTabSubmitted('Suppliers'))
                            $this->processSuppliers();
                        if ($this->isTabSubmitted('Features'))
                            $this->processFeatures();
                        if ($this->isTabSubmitted('Combinations'))
                            $this->processProductAttribute();
                        if ($this->isTabSubmitted('Prices'))
                        {
                            $this->processPriceAddition();
                            $this->processSpecificPricePriorities();
                            $this->object->id_tax_rules_group = (int)Tools::getValue('id_tax_rules_group');
                        }
                        if ($this->isTabSubmitted('Customization'))
                            $this->processCustomizationConfiguration();
                        if ($this->isTabSubmitted('Attachments'))
                            $this->processAttachments();

                        $this->updatePackItems($object);
                        $this->updateDownloadProduct($object, 1);
                        $this->updateTags(Language::getLanguages(false), $object);

                        if ($this->isProductFieldUpdated('category_box') && !$object->updateCategories(Tools::getValue('categoryBox')))
                            $this->errors[] = Tools::displayError('An error occurred while linking object.').' <b>'.$this->table.'</b> '.Tools::displayError('To categories');
                    }

                    if ($this->isTabSubmitted('Warehouses'))
                        $this->processWarehouses();
                    if (empty($this->errors))
                    {
                        Hook::exec('actionProductUpdate', array('product' => $object));

                        if (in_array($object->visibility, array('both', 'search')) && Configuration::get('PS_SEARCH_INDEXATION'))
                            Search::indexation(false, $object->id);

                        // Save and preview
                        if (Tools::isSubmit('submitAddProductAndPreview'))
                        {
                            $preview_url = $this->context->link->getProductLink(
                                $this->getFieldValue($object, 'id'),
                                $this->getFieldValue($object, 'link_rewrite', $this->context->language->id),
                                Category::getLinkRewrite($this->getFieldValue($object, 'id_category_default'), $this->context->language->id),
                                null,
                                null,
                                Context::getContext()->shop->id,
                                0,
                                (bool)Configuration::get('PS_REWRITING_SETTINGS')
                            );

                            if (!$object->active)
                            {
                                $admin_dir = dirname($_SERVER['PHP_SELF']);
                                $admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);
                                if (strpos($preview_url, '?') === false)
                                    $preview_url .= '?';
                                else
                                    $preview_url .= '&';
                                $preview_url .= 'adtoken='.$this->token.'&ad='.$admin_dir.'&id_employee='.(int)$this->context->employee->id;
                            }
                            $this->redirect_after = $preview_url;
                        }
                        else
                        {
                            // Save and stay on same form
                            if ($this->display == 'edit')
                            {
                                $this->confirmations[] = $this->l('Update successful');
                                $this->redirect_after = self::$currentIndex.'&id_product='.(int)$this->object->id
                                    .(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '')
                                    .'&updateproduct&conf=4&key_tab='.Tools::safeOutput(Tools::getValue('key_tab')).'&token='.$this->token;
                            }
                            else
                                // Default behavior (save and back)
                                $this->redirect_after = self::$currentIndex.(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '').'&conf=4&token='.$this->token;
                        }
                    }
                    // if errors : stay on edit page
                    else
                        $this->display = 'edit';
                }
                else
                    $this->errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.'</b> ('.Db::getInstance()->getMsgError().')';
            }
            else
                $this->errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.'</b> ('.Tools::displayError('Cannot load object').')';
            return $object;
        }
    }

    public function processProductAttribute()
    {
        // Don't process if the combination fields have not been submitted
        if (!Combination::isFeatureActive() || !Tools::getValue('attribute_combination_list'))
            return;

        if (Validate::isLoadedObject($product = $this->object))
        {
            if ($this->isProductFieldUpdated('attribute_price') && (!Tools::getIsset('attribute_price') || Tools::getIsset('attribute_price') == null))
                $this->errors[] = Tools::displayError('Attribute price required.');
            if (!Tools::getIsset('attribute_combination_list') || Tools::isEmpty(Tools::getValue('attribute_combination_list')))
                $this->errors[] = Tools::displayError('You must add at least one attribute.');

            if (!count($this->errors))
            {
                if (!isset($_POST['attribute_wholesale_price'])) $_POST['attribute_wholesale_price'] = 0;
                if (!isset($_POST['attribute_price_impact'])) $_POST['attribute_price_impact'] = 0;
                if (!isset($_POST['attribute_weight_impact'])) $_POST['attribute_weight_impact'] = 0;
                if (!isset($_POST['attribute_ecotax'])) $_POST['attribute_ecotax'] = 0;
                if (Tools::getValue('attribute_default'))
                    $product->deleteDefaultAttributes();
                // Change existing one
                if (($id_product_attribute = (int)Tools::getValue('id_product_attribute')) || ($id_product_attribute = $product->productAttributeExists(Tools::getValue('attribute_combination_list'), false, null, true, true)))
                {
                    if ($this->tabAccess['edit'] === '1')
                    {

                        $end_date = Tools::getValue('available_date_attribute');
                        if ($this->isProductFieldUpdated('available_date_attribute') && !Validate::isDateFormat(Tools::getValue('available_date_attribute'))){
                            $end_date = '0000-00-00' ;
                        }
                        $begin_date = Tools::getValue('begin_date_attribute');
                        if ($this->isProductFieldUpdated('begin_date_attribute') && !Validate::isDateFormat($begin_date)){
                            $begin_date = '0000-00-00';
                        }


                        //$this->errors[] = Tools::displayError('Invalid date format.');

                        $product->updateAttribute((int)$id_product_attribute,
                            $this->isProductFieldUpdated('attribute_wholesale_price') ? Tools::getValue('attribute_wholesale_price') : null,
                            $this->isProductFieldUpdated('attribute_price_impact') ? Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact') : null,
                            $this->isProductFieldUpdated('attribute_weight_impact') ? Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact') : null,
                            $this->isProductFieldUpdated('attribute_unit_impact') ? Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact') : null,
                            $this->isProductFieldUpdated('attribute_ecotax') ? Tools::getValue('attribute_ecotax') : null,
                            Tools::getValue('id_image_attr'),
                            Tools::getValue('attribute_reference'),
                            Tools::getValue('attribute_ean13'),
                            $this->isProductFieldUpdated('attribute_default') ? Tools::getValue('attribute_default') : null,
                            Tools::getValue('attribute_location'),
                            Tools::getValue('attribute_upc'),
                            $this->isProductFieldUpdated('attribute_minimal_quantity') ? Tools::getValue('attribute_minimal_quantity') : null,
                            $this->isProductFieldUpdated('available_date_attribute') ? $end_date : null,
                            $this->isProductFieldUpdated('begin_date_attribute') ? $begin_date : null,
                            false);
                        StockAvailable::setProductDependsOnStock((int)$product->id, $product->depends_on_stock, null, (int)$id_product_attribute);
                        StockAvailable::setProductOutOfStock((int)$product->id, $product->out_of_stock, null, (int)$id_product_attribute);

                    }
                    else
                        $this->errors[] = Tools::displayError('You do not have permission to add here.');
                }
                // Add new
                else
                {
                    if ($this->tabAccess['add'] === '1')
                    {
                        if ($product->productAttributeExists(Tools::getValue('attribute_combination_list')))
                            $this->errors[] = Tools::displayError('This combination already exists.');
                        else
                        {
                            $id_product_attribute = $product->addCombinationEntity(
                                Tools::getValue('attribute_wholesale_price'),
                                Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact'),
                                Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact'),
                                Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact'),
                                Tools::getValue('attribute_ecotax'),
                                0,
                                Tools::getValue('id_image_attr'),
                                Tools::getValue('attribute_reference'),
                                null,
                                Tools::getValue('attribute_ean13'),
                                Tools::getValue('attribute_default'),
                                Tools::getValue('attribute_location'),
                                Tools::getValue('attribute_upc'),
                                Tools::getValue('attribute_minimal_quantity')
                            );
                            StockAvailable::setProductDependsOnStock((int)$product->id, $product->depends_on_stock, null, (int)$id_product_attribute);
                            StockAvailable::setProductOutOfStock((int)$product->id, $product->out_of_stock, null, (int)$id_product_attribute);
                        }
                    }
                    else
                        $this->errors[] = Tools::displayError('You do not have permission to').'<hr>'.Tools::displayError('Edit here.');
                }
                if (!count($this->errors))
                {
                    $combination = new Combination((int)$id_product_attribute);
                    $combination->setAttributes(Tools::getValue('attribute_combination_list'));
                    $product->checkDefaultAttributes();
                }
                if (!count($this->errors))
                {
                    if (!$product->cache_default_attribute)
                        Product::updateDefaultAttribute($product->id);
                }
            }
        }
    }
    /**
     * postProcess handle every checks before saving products information
     *
     * @return void
     */
    public function postProcess()
    {
        if (!$this->redirect_after)
            parent::postProcess();

        if ($this->display == 'edit' || $this->display == 'add')
        {
            $this->addjQueryPlugin(array(
                'autocomplete',
                'tablednd',
                'thickbox',
                'ajaxfileupload',
                'date'
            ));

            $this->addJqueryUI(array(
                'ui.core',
                'ui.widget',
                'ui.accordion',
                'ui.slider',
                'ui.datepicker'
            ));

            $this->addJS(array(
                _PS_JS_DIR_.'productTabsManager.js',
                _PS_JS_DIR_.'admin-products.js',
                //override: allow to fill Product Combinaison begin_date
                _PS_JS_DIR_.'override-admin-products.js',
                _PS_JS_DIR_.'attributesBack.js',
                _PS_JS_DIR_.'price.js',
                _PS_JS_DIR_.'tiny_mce/tiny_mce.js',
                _PS_JS_DIR_.'tinymce.inc.js',
                _PS_JS_DIR_.'fileuploader.js',
                _PS_JS_DIR_.'admin-dnd.js',
                _PS_JS_DIR_.'jquery/plugins/treeview-categories/jquery.treeview-categories.js',
                _PS_JS_DIR_.'jquery/plugins/treeview-categories/jquery.treeview-categories.async.js',
                _PS_JS_DIR_.'jquery/plugins/treeview-categories/jquery.treeview-categories.edit.js',
                _PS_JS_DIR_.'admin-categories-tree.js',
                _PS_JS_DIR_.'jquery/ui/jquery.ui.progressbar.min.js',
                _PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js'
            ));

            $this->addCSS(array(
                _PS_JS_DIR_.'jquery/plugins/treeview-categories/jquery.treeview-categories.css',
                _PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css',
            ));
        }
    }

}

