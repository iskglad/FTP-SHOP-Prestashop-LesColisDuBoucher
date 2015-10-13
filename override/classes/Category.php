<?php

class Category extends CategoryCore
{
	
	public $id_lcdb_import;
	
	public static $definition = array(
		'table' => 'category',
		'primary' => 'id_category',
		'multilang' => true,
		'multilang_shop' => true,
		'fields' => array(
			'nleft' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'nright' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'level_depth' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'active' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'id_parent' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_shop_default' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'is_root_category' => 	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'position' => 			array('type' => self::TYPE_INT),
			'date_add' => 			array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 			array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'id_lcdb_import' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),

			// Lang fields
			'name' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 64),
			'link_rewrite' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 64),
			'description' => 		array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'meta_title' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
			'meta_description' => 	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
			'meta_keywords' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
		),
	);
	
	public function getSubCategoriesByDepth($id_cat, $depth, $id_lang, $active = true)
	{
		if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$groups = FrontController::getCurrentCustomerGroups();
		$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT c.*, cl.id_lang, cl.name, cl.link_rewrite, cl.meta_title
			FROM `'._DB_PREFIX_.'category` c
			'.Shop::addSqlAssociation('category', 'c').'
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
				ON (c.`id_category` = cl.`id_category`
				AND `id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
			LEFT JOIN `'._DB_PREFIX_.'category_group` cg
				ON (cg.`id_category` = c.`id_category`)
			WHERE `id_parent` = '.(int)$id_cat.'
				'.($active ? 'AND `active` = 1' : '').'
				AND cg.`id_group` '.$sql_groups.'
			GROUP BY c.`id_category`
			ORDER BY `level_depth` ASC, category_shop.`position` ASC
		');
			
		foreach ($result as &$row)
		{
			$row['subcats'] = Category::getSubCategoriesByDepth($row['id_category'], $depth, $id_lang, $active);
		}

		return $result;
	}
	
	public function getFullSubCategories($id_lang, $active = true, $order_by_product = null, $order_way_product = null)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$groups = FrontController::getCurrentCustomerGroups();
		$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
			FROM `'._DB_PREFIX_.'category` c
			'.Shop::addSqlAssociation('category', 'c').'
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
				ON (c.`id_category` = cl.`id_category`
				AND `id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
			LEFT JOIN `'._DB_PREFIX_.'category_group` cg
				ON (cg.`id_category` = c.`id_category`)
			WHERE `id_parent` = '.(int)$this->id.'
				'.($active ? 'AND `active` = 1' : '').'
				AND cg.`id_group` '.$sql_groups.'
			GROUP BY c.`id_category`
			ORDER BY `level_depth` ASC, category_shop.`position` ASC
		');

		foreach ($result as &$row)
		{
			$row['id_image'] = file_exists(_PS_CAT_IMG_DIR_.$row['id_category'].'.jpg') ? (int)$row['id_category'] : Language::getIsoById($id_lang).'-default';
			$row['legend'] = 'no picture';
			// gets products of category
			$category = new Category($row['id_category'] , $id_lang);
			$row['products'] = $category->getProducts($id_lang, 0, 100, $order_by_product, $order_way_product);
		}
		return $result;
	}

	public static function getLeftColumn($lang, $zipcodes = null){

		$parent = new Category(3, $lang);
		$categories_list = $parent->getSubCategoriesByDepth(2, 4, $lang);
		$allowed_zone = false;

		// display gift category only if allowed zone
		if((count($zipcodes) > 0)){
			foreach ($zipcodes as $key => $zip) {
				$zone = Address::getZoneByZipCode($zip);
				if($zone == ID_ZONE_PARIS){
					$allowed_zone = true;
				}
			}
		}

		if($allowed_zone != true){
			foreach ($categories_list as $key => $category){
				if($category['id_category'] == ID_CATEGORY_GIFT){
					unset($categories_list[$key]);
				}
			}
		}
		
		return $categories_list;
	}

	public static function getRightColumn($lang){
		$tips = CMS::getCMSPages($lang, 7);
		return $content = array("tips" => $tips);
	}

    /**
     * Return current category products
     *
     * @param integer $id_lang Language ID
     * @param integer $p Page number
     * @param integer $n Number of products per page
     * @param boolean $get_total return the number of results instead of the results themself
     * @param boolean $active return only active products
     * @param boolean $random active a random filter for returned products
     * @param int $random_number_products number of products to return if random is activated
     * @param boolean $check_access set to false to return all products (even if customer hasn't access)
     * @return mixed Products or number of products
     */
    public function getProducts($id_lang, $p, $n, $order_by = null, $order_way = null, $get_total = false, $active = true, $random = false, $random_number_products = 1, $check_access = true, Context $context = null)
    {
        if (!$context)
            $context = Context::getContext();
        if ($check_access && !$this->checkAccess($context->customer->id))
            return false;

        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
            $front = false;

        if ($p < 1) $p = 1;

        if (empty($order_by))
            $order_by = 'position';
        else
            /* Fix for all modules which are now using lowercase values for 'orderBy' parameter */
            $order_by = strtolower($order_by);

        if (empty($order_way))
            $order_way = 'ASC';
        if ($order_by == 'id_product' || $order_by == 'date_add' || $order_by == 'date_upd')
            $order_by_prefix = 'p';
        elseif ($order_by == 'name')
            $order_by_prefix = 'pl';
        elseif ($order_by == 'manufacturer')
        {
            $order_by_prefix = 'm';
            $order_by = 'name';
        }
        elseif ($order_by == 'position')
            $order_by_prefix = 'cp';

        if ($order_by == 'price')
            $order_by = 'orderprice';

        if (!Validate::isBool($active) || !Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
            die (Tools::displayError());

        $id_supplier = (int)Tools::getValue('id_supplier');

        /* Return only the number of products */
        if ($get_total)
        {
            $sql = 'SELECT COUNT(cp.`id_product`) AS total
					FROM `'._DB_PREFIX_.'product` p
					'.Shop::addSqlAssociation('product', 'p').'
					LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product`
					WHERE cp.`id_category` = '.(int)$this->id.
                ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                ($active ? ' AND product_shop.`active` = 1' : '').
                ($id_supplier ? 'AND p.id_supplier = '.(int)$id_supplier : '');
            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, product_attribute_shop.`id_product_attribute`, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, pl.`description`, pl.`description_short`, pl.`available_now`,
					pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, image_shop.`id_image`,
					il.`legend`, m.`name` AS manufacturer_name, cl.`name` AS category_default,
					DATEDIFF(product_shop.`date_add`, DATE_SUB(NOW(),
					INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).'
						DAY)) > 0 AS new, product_shop.price AS orderprice
				FROM `'._DB_PREFIX_.'category_product` cp
				LEFT JOIN `'._DB_PREFIX_.'product` p
					ON p.`id_product` = cp.`id_product`
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
				ON (p.`id_product` = pa.`id_product`)
				'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
				'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
					ON (product_shop.`id_category_default` = cl.`id_category`
					AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON (p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN `'._DB_PREFIX_.'image` i
					ON (i.`id_product` = p.`id_product`)'.
            Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il
					ON (image_shop.`id_image` = il.`id_image`
					AND il.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
					ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE product_shop.`id_shop` = '.(int)$context->shop->id.'
				AND (pa.id_product_attribute IS NULL OR product_attribute_shop.id_shop='.(int)$context->shop->id.')
				AND (i.id_image IS NULL OR image_shop.id_shop='.(int)$context->shop->id.')
					AND cp.`id_category` = '.(int)$this->id
            .($active ? ' AND product_shop.`active` = 1' : '')
            .($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '')
            .($id_supplier ? ' AND p.id_supplier = '.(int)$id_supplier : '');

        if ($random === true)
        {
            $sql .= ' ORDER BY RAND()';
            $sql .= ' LIMIT 0, '.(int)$random_number_products;
        }
        else
            $sql .= ' ORDER BY '.(isset($order_by_prefix) ? $order_by_prefix.'.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way).'
			LIMIT '.(((int)$p - 1) * (int)$n).','.(int)$n;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($order_by == 'orderprice')
            Tools::orderbyPrice($result, $order_way);

        if (!$result)
            return array();

        foreach($result as &$product){
            $product["combinations"] = Product::getProductCombinations($product["id_product"]);
            //$product["combinations"] = Product::getProductAttributesGroups($product["id_product"], $id_lang);
        }

        /* Modify SQL result */
        return Product::getProductsProperties($id_lang, $result);
    }
}

