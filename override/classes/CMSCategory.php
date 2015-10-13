<?php

class CMSCategory extends CMSCategoryCore
{
	
	public function getFullSubCategories($id_lang, $active = true)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
		FROM `'._DB_PREFIX_.'cms_category` c
		LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category` AND `id_lang` = '.(int)$id_lang.')
		WHERE `id_parent` = '.(int)$this->id.'
		'.($active ? 'AND `active` = 1' : '').'
		GROUP BY c.`id_cms_category`
		ORDER BY `name` ASC');

		// Modify SQL result
		foreach ($result as &$row){
			$row['name'] = CMSCategory::hideCMSCategoryPosition($row['name']);
			$row['childrens'] = CMS::getCMSPages($id_lang, (int)($row['id_cms_category']));
		}
		return $result;
	}
	
}

