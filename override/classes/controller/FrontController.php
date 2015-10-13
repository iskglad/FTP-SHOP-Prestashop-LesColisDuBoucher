<?php

class FrontController extends FrontControllerCore
{
	public function init()
	{
		parent::init();
		
		$menu_cats = Category::getChildren(2, $this->context->language->id);
		$menu_infos = CMS::getCMSPages($this->context->language->id, 3);
		$menu_approach = CMS::getCMSPages($this->context->language->id, 2);
		$menu_recipe = RecipeCategory::getSubCategoriesByDepth(1, 3, $this->context->language->id);

		$allowed_zone = false;

		// get zipcodes list
		if ($this->context->customer->id){
			$customer_adresses = $this->context->customer->getAddresses($this->context->language->id);
			foreach ($customer_adresses as $key => $address) {
				if(isset($address["postcode"]) && ($address["postcode"] != "")){
					$zipcodes[] = $address["postcode"];
				}
			}
		}

		// display gift category only if allowed zone
		if(isset($zipcodes) && (count($zipcodes) > 0)){
			foreach ($zipcodes as $key => $zip) {
				$zone = Address::getZoneByZipCode($zip);
				if($zone == ID_ZONE_PARIS){
					$allowed_zone = true;
				}
			}
		}

		if($allowed_zone != true){
			foreach ($menu_cats as $key => $category){
				if($category['id_category'] == ID_CATEGORY_GIFT){
					unset($menu_cats[$key]);
				}
			}
		}
		
		$this->context->smarty->assign(array(
			'menu_cats' => $menu_cats,
			'menu_infos' => $menu_infos,
			'menu_recipes' => $menu_cats,
			'menu_approach' => $menu_approach,
			'menu_recipe' => $menu_recipe
		));

        // assign id to smarty
        $this->context->smarty->assign(array(
            'id_zone_paris' => ID_ZONE_PARIS,
            'id_zone_petite_banlieue' => ID_ZONE_PETITE_BANLIEUE,
            'id_zone_grande_banlieue' => ID_ZONE_GRANDE_BANLIEUE,
            'id_zone_province' => ID_ZONE_PROVINCE,
            'id_category_main' => ID_CATEGORY_MAIN,
            'id_category_surprise' => ID_CATEGORY_SURPRISE,
            'id_category_gift' => ID_CATEGORY_GIFT,
            'id_category_boeuf' => ID_CATEGORY_BOEUF,
            'id_product_surprise' => ID_PRODUCT_SURPRISE,
            'id_product_gift' => ID_PRODUCT_GIFT,
            'id_feature_package' => ID_FEATURE_PACKAGE,
            'id_feature_number_of' => ID_FEATURE_NUMBER_OF,
            'id_feature_preservation' => ID_FEATURE_PRESERVATION,
            'id_feature_baking' => ID_FEATURE_BAKING,
            'id_feature_label_bio' => ID_FEATURE_LABEL_BIO,
            'id_feature_label_rouge' => ID_FEATURE_LABEL_ROUGE,
            'id_feature_label_weight' => ID_FEATURE_WEIGHT
        ));

	}

	public function setMedia()
	{
		// if website is accessed by mobile device
		// @see FrontControllerCore::setMobileMedia()
		if ($this->context->getMobileDevice() != false)
		{
			$this->setMobileMedia();
			return true;
		}
		$this->addCSS(_THEME_CSS_DIR_.'global.css', 'all');
		$this->addjquery();
		$this->addJquery('1.9.0');
		$this->addjqueryPlugin('easing');
		// Theme lcdb
		$this->addCSS(_THEME_CSS_DIR_.'lib/normalize.css');
		$this->addJS(_THEME_JS_DIR_.'plugins/glDatePicker.js');
		$this->addJS(_THEME_JS_DIR_.'plugins/cufon.js');
		$this->addJS(_THEME_JS_DIR_.'plugins/font-cufon.js');
		$this->addJS(_THEME_JS_DIR_.'plugins/jquery.placeholder.min.js');
		$this->addJS(_THEME_JS_DIR_.'plugins/modernizr-2.6.2.min.js');
		$this->addJS(_THEME_JS_DIR_.'plugins/custom_checkbox_and_radio.js');
		$this->addJS(_THEME_JS_DIR_.'plugins/jquery.scroll.min.js');
		$this->addJS(_THEME_JS_DIR_.'plugins/jquery.selectbox-0.2.min.js');
		$this->addJS(_THEME_JS_DIR_.'main.js');
		$this->addJS(_THEME_JS_DIR_.'googleAnalytics.js');
        

		if (Tools::isSubmit('live_edit') && Tools::getValue('ad') && Tools::getAdminToken('AdminModulesPositions'.(int)Tab::getIdFromClassName('AdminModulesPositions').(int)Tools::getValue('id_employee')))
		{
			$this->addJqueryUI('ui.sortable');
			$this->addjqueryPlugin('fancybox');
			$this->addJS(_PS_JS_DIR_.'hookLiveEdit.js');
			$this->addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'all'); // @TODO
		}
		if ($this->context->language->is_rtl)
			$this->addCSS(_THEME_CSS_DIR_.'rtl.css');

		// Execute Hook FrontController SetMedia
		Hook::exec('actionFrontControllerSetMedia', array());
	}
}

