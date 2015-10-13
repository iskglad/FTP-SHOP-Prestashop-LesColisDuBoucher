<?php

class Product extends ProductCore
{
	public $tricks;
	public $breeder;
	public $abo;
	public $unusual_product;
	public $product_type_cook;
	public $product_type_bio;
	public $product_type_wtlamb;
	public $product_type_wtpork;
	public $serving;
	public $id_lcdb_import;
    public $limit_date;
	public $date_start;
	public $date_end;
    public $combinations;
    public $features;

	public static $definition = array(
		'table' => 'product',
		'primary' => 'id_product',
		'multilang' => true,
		'multilang_shop' => true,
		'fields' => array(
			/* Classic fields */
			'id_shop_default' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_manufacturer' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_supplier' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'reference' => 					array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32),
			'supplier_reference' => 		array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32),
			'location' => 					array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 64),
			'width' => 						array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
			'height' => 					array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
			'depth' => 						array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
			'weight' => 					array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
			'quantity_discount' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'ean13' => 						array('type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => 13),
			'upc' => 						array('type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => 12),
			'cache_is_pack' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'cache_has_attachments' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'is_virtual' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'abo' =>						array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'unusual_product' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'product_type_cook' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'product_type_bio' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'product_type_wtlamb' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'product_type_wtpork' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'serving' => 					array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 50),
			'id_lcdb_import' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'limit_date' => 			    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'date_start' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
			'date_end' => 					array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),

			/* Shop fields */
			'id_category_default' => 		array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'),
			'id_tax_rules_group' => 		array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'),
			'on_sale' => 					array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
			'online_only' => 				array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
			'ecotax' => 					array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'),
			'minimal_quantity' => 			array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
			'price' => 						array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'required' => true),
			'wholesale_price' => 			array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'),
			'unity' => 						array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isString'),
			'unit_price_ratio' => 			array('type' => self::TYPE_FLOAT, 'shop' => true),
			'additional_shipping_cost' => 	array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'),
			'customizable' => 				array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
			'text_fields' => 				array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
			'uploadable_files' => 			array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
			'active' => 					array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
			'redirect_type' => 				array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isString'),
			'id_product_redirected' => 		array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'),
			'available_for_order' => 		array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
			'available_date' => 			array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat'),
			'condition' => 					array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isGenericName', 'values' => array('new', 'used', 'refurbished'), 'default' => 'new'),
			'show_price' => 				array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
			'indexed' => 					array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
			'visibility' => 				array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isProductVisibility', 'values' => array('both', 'catalog', 'search', 'none'), 'default' => 'both'),
			'cache_default_attribute' => 	array('type' => self::TYPE_INT, 'shop' => true),
			'advanced_stock_management' => 	array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
			'date_add' => 					array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat'),
			'date_upd' => 					array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat'),

			/* Lang fields */
			'meta_description' => 			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
			'meta_keywords' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
			'meta_title' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
			'link_rewrite' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 128),
			'name' => 						array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
			'description' => 				array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'description_short' => 			array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'tricks' => 					array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'breeder' => 					array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'available_now' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
			'available_later' => 			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'IsGenericName', 'size' => 255),
		),
		'associations' => array(
			'manufacturer' => 				array('type' => self::HAS_ONE),
			'supplier' => 					array('type' => self::HAS_ONE),
			'default_category' => 			array('type' => self::HAS_ONE, 'field' => 'id_category_default', 'object' => 'Category'),
			'tax_rules_group' => 			array('type' => self::HAS_ONE),
			'categories' =>					array('type' => self::HAS_MANY, 'field' => 'id_category', 'object' => 'Category', 'association' => 'category_product'),
			'stock_availables' =>			array('type' => self::HAS_MANY, 'field' => 'id_stock_available', 'object' => 'StockAvailable', 'association' => 'stock_availables'),
		),
	);

   public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);

       //get combinations
        $this->combinations = Product::getProductCombinations($this->id);
        $this->features = Product::getAdvancedFeaturesStatic($this->id, $id_lang);
    }

    /**
     * Get all available products
     *
     * @param integer $id_lang Language id
     * @param integer $start Start number
     * @param integer $limit Number of products to return
     * @param string $order_by Field for ordering
     * @param string $order_way Way for ordering (ASC or DESC)
     * @return array Products details
     */
    public static function getProducts($id_lang, $start, $limit, $order_by, $order_way, $id_category = false,
                                       $only_active = false, Context $context = null, $withPrices = true)
    {
        if (!$context)
            $context = Context::getContext();

        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
            $front = false;

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
            die (Tools::displayError());
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd')
            $order_by_prefix = 'p';
        else if ($order_by == 'name')
            $order_by_prefix = 'pl';
        else if ($order_by == 'position')
            $order_by_prefix = 'c';
        if (strpos($order_by, '.') > 0)
        {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }
        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
				FROM `'._DB_PREFIX_.'product` p
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (s.`id_supplier` = p.`id_supplier`)'.
            ($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)' : '').'
				WHERE pl.`id_lang` = '.(int)$id_lang.
            ($id_category ? ' AND c.`id_category` = '.(int)$id_category : '').
            ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
            ($only_active ? ' AND product_shop.`active` = 1' : '').'
				ORDER BY '.(isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way).
            ($limit > 0 ? ' LIMIT '.(int)$start.','.(int)$limit : '');
        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($order_by == 'price')
            Tools::orderbyPrice($rq, $order_way);

        foreach ($rq as &$row){
            $row = Product::getTaxesInformations($row);

            if ($withPrices){
                $row['price_tax_inc'] = Product::getPriceStatic($row['id_product'], true, null, 2);
                $row['price_tax_exc'] = Product::getPriceStatic($row['id_product'], false, null, 2);
            }

            $row['combinations'] = Product::getProductCombinations($row['id_product']);
            $row['cover'] = Product::getCover($row['id_product']);
            $row['features'] = Product::getAdvancedFeaturesStatic((int)$row['id_product'], $id_lang);
        }

        return ($rq);
    }

    /**
     * Shop has available promo
     *
     * Check if the shop is currently making some promotions
     * @return true/false
     */
    public static function shopHasAvailablePromoProducts(){
        $orderBy = Tools::getProductsOrder('by');
        $orderWay = Tools::getProductsOrder('way');
        $all_products = Product::getProducts((int)Context::getContext()->language->id, 0, 0, $orderBy, $orderWay, ID_CATEGORY_MAIN, true, null, false);
        foreach ($all_products as $product){
            if (Product::combinationListHasPromo($product['combinations']))
                return true;
        }
        return false;
    }
	/**
	 * Delete product recipes
	 *
	 * @return mixed Deletion result
	 */
	public function deleteRecipes()
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'product_recipe` WHERE `id_product` = '.(int)$this->id);
	}

	/**
	 * Get product recipes (only names)
	 *
	 * @param integer $id_lang Language id
	 * @param integer $id_product Product id
	 * @return array Product recipes
	 */
	public static function getRecipesLight($id_lang, $id_product, Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();

		$sql = 'SELECT r.`id_recipe`, rl.`title`
				FROM `'._DB_PREFIX_.'product_recipe` pr
				LEFT JOIN `'._DB_PREFIX_.'recipe` r ON (r.`id_recipe`= pr.`id_recipe`)
				'.Shop::addSqlAssociation('recipe', 'r').'
				LEFT JOIN `'._DB_PREFIX_.'recipe_lang` rl ON (
					r.`id_recipe` = rl.`id_recipe`
					AND rl.`id_lang` = '.(int)$id_lang.'
				)
				WHERE pr.`id_product` = '.(int)$id_product;

		return Db::getInstance()->executeS($sql);
	}

	/**
	 * Get product recipes
	 *
	 * @param integer $id_lang Language id
	 * @return array Product recipes
	 */
	public function getRecipes($id_lang, $active = true, Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();

		$sql = 'SELECT r.*, rl.*
				FROM `'._DB_PREFIX_.'product_recipe` pr
				LEFT JOIN `'._DB_PREFIX_.'recipe` r ON r.`id_recipe` = pr.`id_recipe`
				'.Shop::addSqlAssociation('recipe', 'r').'
				LEFT JOIN `'._DB_PREFIX_.'recipe_lang` rl ON (
					r.`id_recipe` = rl.`id_recipe`
					AND rl.`id_lang` = '.(int)$id_lang.'
				)
				WHERE pr.`id_product` = '.(int)$this->id;

		if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql))
			return false;

		return $result;
	}

	public static function getRecipeById($recipe_id)
	{
		return Db::getInstance()->getRow('SELECT `id_recipe`, `title` FROM `'._DB_PREFIX_.'recipe_lang` WHERE `id_recipe` = '.(int)$recipe_id);
	}

	/**
	 * Link recipes with product
	 *
	 * @param array $recipes_id Recipes ids
	 */
	public function changeRecipes($recipes_id)
	{
		foreach ($recipes_id as $id_recipe)
			Db::getInstance()->insert('product_recipe', array(
				'id_product' => (int)$this->id,
				'id_recipe' => (int)$id_recipe
			));
	}

    public static function getAdvancedFeaturesStatic($id_product, $id_lang)
    {
        if (!Feature::isFeatureActive())
            return array();

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT p.`id_feature`, p.`id_product`, p.`id_feature_value`, fvl.`value`
			FROM `'._DB_PREFIX_.'feature_product` p
			LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` fvl ON (p.id_feature_value = fvl.id_feature_value) AND fvl.`id_lang` = '.(int)$id_lang.'
			WHERE p.`id_product` = '.(int)$id_product
        );

        return $result;
    }

	public static function getProductCategoriesFull($id_product = '', $id_lang = null)
	{
		if (!$id_lang)
			$id_lang = Context::getContext()->language->id;

		$ret = array();
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT cp.`id_category`, cl.`name`, cl.`link_rewrite`, c.`level_depth` FROM `'._DB_PREFIX_.'category_product` cp
			LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.id_category = cp.id_category)
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cp.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').')
			'.Shop::addSqlAssociation('category', 'c').'
			WHERE cp.`id_product` = '.(int)$id_product.'
				AND cl.`id_lang` = '.(int)$id_lang
		);

		foreach ($row as $val)
			$ret[$val['id_category']] = $val;

		return $ret;
	}

    /**
     * Get all available attribute groups for product
     *
     * @param integer $id_lang Language id
     * @return array Attribute groups
     */
    public function getProductAttributesGroups($id_product, $id_lang)
    {
        if (!Combination::isFeatureActive())
            return array();
       $sql = 'SELECT ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name,
					a.`id_attribute`, al.`name` AS attribute_name, a.`color` AS attribute_color, pa.`id_product_attribute`,
					IFNULL(stock.quantity, 0) as quantity, product_attribute_shop.`price`, product_attribute_shop.`ecotax`, pa.`weight`,
					product_attribute_shop.`default_on`, pa.`reference`, product_attribute_shop.`unit_price_impact`,
					pa.`minimal_quantity`, pa.`available_date`, ag.`group_type`
				FROM `'._DB_PREFIX_.'product_attribute` pa
				'.Shop::addSqlAssociation('product_attribute', 'pa').'
				'.Product::sqlStock('pa', 'pa').'
				LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group`
				'.Shop::addSqlAssociation('attribute', 'a').'
				WHERE pa.`id_product` = '.(int)$id_product.'
					AND al.`id_lang` = '.(int)$id_lang.'
					AND agl.`id_lang` = '.(int)$id_lang.'
				GROUP BY id_attribute_group, id_product_attribute
				ORDER BY ag.`position` ASC, a.`position` ASC';

        return Db::getInstance()->executeS($sql);
    }
    /*
      * Return 1 if combination contains attributes "promo" with value != 'none'
      */
    public static function isCombinationPromo($combination){
        $attrs = $combination['attributes'];
        foreach ($attrs as $attr){
            if ($attr['group_name'] == 'promo' &&
                $attr['value'] != 'none')
                return 1;
        }
        return 0;
    }
    /*
     * Return 1 if combination contains attributes "pro"
     */
    public static function isCombinationPro($combination){
        $attrs = $combination['attributes'];
        foreach ($attrs as $attr){
            if ($attr['group_name'] == 'card version' &&
                $attr['value'] == 'pro')
                return 1;
        }
        return 0;
    }
    /*
    * Return label name or empty string
    */
    public static function getCombinationLabelName($combination){
        $attrs = $combination['attributes'];
        foreach ($attrs as $attr){
            if ($attr['group_name'] == 'label')
                return $attr['value'];
        }
        return '';
    }
    /*
   * Return label name or empty string
   */
    public static function getCombinationColisName($combination){
        $attrs = $combination['attributes'];
        foreach ($attrs as $attr){
            if ($attr['group_name'] == 'colis_surprise')
                return $attr['value'];
        }
        return '';
    }
    /*
  * Return promo name or empty string
  */
    public static function getCombinationPromoName($combination){
        $attrs = $combination['attributes'];
        foreach ($attrs as $attr){
            if ($attr['group_name'] == 'promo' &&
                $attr['value'] != "none")
                return $attr['value'];
        }
        return '';
    }
    /*
     * Return true if combination is out of date (date 00-00-0000 mean "forever")
     */
    public static function isCombinationOutOfDate($combination){
        $end_date = strtotime($combination['available_date']);

        if ($end_date < 0) //date before 1970-1-1 (unix timeStamp)
            return 0; //not out of date
        else if (time() < $end_date)
            return 0;
        return 1;
    }


    /*Delete all elements declinaison that have promo copy
    ie: [0] Entrecote Bio, [1] Entrecote Salers Promo Supplier, [2] Entrecote Salers
    Becomes => [0] Entrecote Bio, [1] Entrecote Salers Promo Supplier
        return a clean combination_list
    */
    public static  function  clearPromoDuplications($combination_list){
        foreach ($combination_list as $combination){
            if ($combination['isPromo']){
                //find the "none promo" equivalent
                foreach ($combination_list as $comb_tmp){
                    if ($combination['label_name'] == $comb_tmp['label_name'] &&
                        $combination['isPro'] == $comb_tmp['isPro'] &&
                        !$comb_tmp['isPromo']) {
                        // Remove the "none promo" equivalent from array
                        $pos = array_search($comb_tmp, $combination_list);
                        unset($combination_list[$pos]);
                    }
                }
            }
        }
        return $combination_list;
    }

    /*
     * Get product attributes
     */
    public static function getProductAttributes($id_product_attribute){
        $sql = 'SELECT  pac.`id_attribute` AS id,
                            a.id_attribute_group AS id_group,
                            al.`name` AS value,
                            agl.`name` AS group_name
                    FROM    `'._DB_PREFIX_.'product_attribute_combination` pac
                    LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                    LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON al.`id_attribute` = pac.`id_attribute`
                    LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON agl.`id_attribute_group` = a.`id_attribute_group`
                    WHERE pac.`id_product_attribute` = '.(int)$id_product_attribute.'
           ';
        $attributes = Db::getInstance()->executeS($sql);
        return $attributes;
    }

    /**
     * *Get products combination (declinaison)
     */
    public static function getProductCombinations($id_product){

        if ($id_product <= 0 || $id_product == '')
            return 0;

        $tax_rate = Tax::getProductTaxRate($id_product);
        //get card version
        if (Customer::isCurrentCustomerPro())
            $id_card_version = ID_ATTRIBUTE_CARD_PRO;//card pro = 32, see /config/settings.inc.php
        else
            $id_card_version = ID_ATTRIBUTE_CARD_NORMAL;//card normal = 31", see in /config/settings.inc.php

        //get combinations with current card version (card pro || card normal)
        $sql = 'SELECT pa.*
                FROM `'._DB_PREFIX_.'product_attribute` pa
                LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                WHERE pa.`id_product` = '.(int)$id_product.'
                    AND pac.`id_attribute` = '.(int)$id_card_version.'';
        $product_combinations_tmp = Db::getInstance()->executeS($sql);

        $product_combinations = array();
        //get and add attributes values (label, promo, cardversion, etc) for each combination found
        foreach ($product_combinations_tmp as $combination){
            $combination['attributes'] = Product::getProductAttributes((int)$combination['id_product_attribute']);
            //get available quantity
            $combination['available_quantity'] = StockAvailable::getQuantityAvailableByProduct((int)$id_product, $combination['id_product_attribute']);
            if ($combination['available_quantity'] < 0)
                $combination['available_quantity'] = NB_STOCK_INFINITE;//valeur arbritraire pour le front end. quantity < 0 => infinite @See config/setting.inc
            //set Promo value
            $combination['isPromo'] = Product::isCombinationPromo($combination);
            if ($combination['available_quantity'] == 0)
                $combination['isPromo'] = 0;
            //set Pro value
            $combination['isPro'] = Product::isCombinationPro($combination);
            //get Labels name Array
            $combination['label_name'] = Product::getCombinationLabelName($combination);
            //get Colis name (used for Colis surprise Only)
            $combination['colis_name'] = Product::getCombinationColisName($combination);
            //set price impact (for readability)
            $combination['price_impact'] = $combination['price']  * (1 + $tax_rate/100);
            //Add if not Out of date
            if (!Product::isCombinationOutOfDate($combination)){
                $product_combinations[] = $combination;
            }
        }
        $product_combinations = Product::clearPromoDuplications($product_combinations);
        // Tools::testVar($product_combinations);
        return $product_combinations;
    }

    /*return true if the combination list content a product promo*/
    public static function combinationListHasPromo($list){
        foreach ($list as $comb){
            if ($comb['isPromo'] == 1){
                return 1;
            }
        }
        return 0;
    }
    /**
     * Update a product attribute
     *
     * @deprecated since 1.5
     * @see updateAttribute() to use instead
     * @see ProductSupplier for manage supplier reference(s)
     *
     */
    public function updateProductAttribute($id_product_attribute, $wholesale_price, $price, $weight, $unit, $ecotax,
                                           $id_images, $reference, $id_supplier = null, $ean13, $default, $location = null, $upc = null, $minimal_quantity, $available_date, $begin_date)
    {
        Tools::displayAsDeprecated();

        $return = $this->updateAttribute(
            $id_product_attribute, $wholesale_price, $price, $weight, $unit, $ecotax,
            $id_images, $reference, $ean13, $default, $location = null, $upc = null, $minimal_quantity, $available_date, $begin_date
        );
        $this->addSupplierReference($id_supplier, $id_product_attribute);

        return $return;
    }
    /**
     * Update a product attribute
     *
     * @param integer $id_product_attribute Product attribute id
     * @param float $wholesale_price Wholesale price
     * @param float $price Additional price
     * @param float $weight Additional weight
     * @param float $unit
     * @param float $ecotax Additional ecotax
     * @param integer $id_image Image id
     * @param string $reference Reference
     * @param string $ean13 Ean-13 barcode
     * @param int $default Default On
     * @param string $upc Upc barcode
     * @param string $minimal_quantity Minimal quantity
     * @return array Update result
     */
    public function updateAttribute($id_product_attribute, $wholesale_price, $price, $weight, $unit, $ecotax,
                                    $id_images, $reference, $ean13, $default, $location = null, $upc = null, $minimal_quantity = null, $available_date = null, $begin_date = null, $update_all_fields = true, array $id_shop_list = array())
    {
        $combination = new Combination($id_product_attribute);

        if (!$update_all_fields)
            $combination->setFieldsToUpdate(array(
                'price' => !is_null($price),
                'wholesale_price' => !is_null($wholesale_price),
                'ecotax' => !is_null($ecotax),
                'weight' => !is_null($weight),
                'unit_price_impact' => !is_null($unit),
                'default_on' => !is_null($default),
                'minimal_quantity' => !is_null($minimal_quantity),
                'available_date' => !is_null($available_date),
                'begin_date' => !is_null($begin_date),
            ));

        $price = str_replace(',', '.', $price);
        $weight = str_replace(',', '.', $weight);

        $combination->price = (float)$price;
        $combination->wholesale_price = (float)$wholesale_price;
        $combination->ecotax = (float)$ecotax;
        $combination->weight = (float)$weight;
        $combination->unit_price_impact = (float)$unit;
        $combination->reference = pSQL($reference);
        $combination->location = pSQL($location);
        $combination->ean13 = pSQL($ean13);
        $combination->upc = pSQL($upc);
        $combination->default_on = (int)$default;
        $combination->minimal_quantity = (int)$minimal_quantity;
        $combination->available_date = $available_date ? pSQL($available_date) : '0000-00-00';
        $combination->begin_date = $begin_date ? pSQL($begin_date) : '0000-00-00';

        if (count($id_shop_list))
            $combination->id_shop_list = $id_shop_list;

        $combination->save();

        if (!empty($id_images))
            $combination->setImages($id_images);

        Product::updateDefaultAttribute($this->id);

        Hook::exec('actionProductAttributeUpdate', array('id_product_attribute' => $id_product_attribute));

        return true;
    }

	
	public static function getProductProperties($id_lang, $row, Context $context = null)
	{
		$row = parent::getProductProperties($id_lang, $row, $context);
		$row['combinations'] = Product::getProductCombinations($row['id_product']);
		return $row;
	}
	
}

