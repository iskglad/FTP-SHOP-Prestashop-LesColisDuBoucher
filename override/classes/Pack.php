<?php

class Pack extends PackCore
{
	public static function getItemTable($id_product, $id_lang, $full = false)
	{
		if (!Pack::isFeatureActive())
			return array();

		$sql = 'SELECT p.*, product_shop.*, pl.*, image_shop.`id_image`, il.`legend`, cl.`name` AS category_default, a.quantity AS pack_quantity, product_shop.`id_category_default`, a.id_product_pack
				FROM `'._DB_PREFIX_.'pack` a
				LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = a.id_product_item
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON p.id_product = pl.id_product
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
					ON product_shop.`id_category_default` = cl.`id_category`
					AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
				WHERE product_shop.`id_shop` = '.(int)Context::getContext()->shop->id.'
				AND ((image_shop.id_image IS NOT NULL OR i.id_image IS NULL) OR (image_shop.id_image IS NULL AND i.cover=1))
				AND a.`id_product_pack` = '.(int)$id_product;
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		foreach ($result as &$row)
			$row = Product::getTaxesInformations($row);
			
		if (!$full)
			return $result;

		$array_result = array();
		foreach ($result as $prow)
			if (!Pack::isPack($prow['id_product']))
				$array_result[] = Product::getProductProperties($id_lang, $prow);
		return $array_result;
	}
}

