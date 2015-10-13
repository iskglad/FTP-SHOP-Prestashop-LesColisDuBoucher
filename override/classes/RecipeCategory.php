<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class RecipeCategoryCore extends ObjectModel
{
	public $id;

	/** @var integer CMSCategory ID */
	public $id_recipe_category;

	/** @var string Name */
	public $name;

	/** @var boolean Status for display */
	public $active = 1;

	/** @var string Description */
	public $description;

	/** @var integer Parent CMSCategory ID */
	public $id_parent;

	/** @var  integer category position */
	public $position;

	/** @var integer Parents number */
	public $level_depth;

	/** @var string string used in rewrited URL */
	public $link_rewrite;

	/** @var string Meta title */
	public $meta_title;

	/** @var string Meta keywords */
	public $meta_keywords;

	/** @var string Meta description */
	public $meta_description;

	/** @var string Object creation date */
	public $date_add;

	/** @var string Object last modification date */
	public $date_upd;

	protected static $_links = array();

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'recipe_category',
		'primary' => 'id_recipe_category',
		'multilang' => true,
		'fields' => array(
			'active' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'id_parent' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'position' => 			array('type' => self::TYPE_INT),
			'level_depth' => 		array('type' => self::TYPE_INT),
			'date_add' => 			array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 			array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

			// Lang fields
			'name' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 64),
			'link_rewrite' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 64),
			'description' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),
			'meta_title' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
			'meta_description' => 	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
			'meta_keywords' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
		),
	);

	public	function add($autodate = true, $null_values = false)
	{
		$this->position = RecipeCategory::getLastPosition((int)$this->id_parent);
		$this->level_depth = $this->calcLevelDepth();
		foreach ($this->name as $k => $value)
			if (preg_match('/^[1-9]\./', $value))
				$this->name[$k] = '0'.$value;
		$ret = parent::add($autodate, $null_values);
		$this->cleanPositions($this->id_parent);
		return $ret;
	}

	public	function update($null_values = false)
	{
		$this->level_depth = $this->calcLevelDepth();
		foreach ($this->name as $k => $value)
			if (preg_match('/^[1-9]\./', $value))
				$this->name[$k] = '0'.$value;
		return parent::update($null_values);
	}
	

	/**
	  * Recursive scan of subcategories
	  *
	  * @param integer $max_depth Maximum depth of the tree (i.e. 2 => 3 levels depth)
 	  * @param integer $currentDepth specify the current depth in the tree (don't use it, only for rucursivity!)
	  * @param array $excluded_ids_array specify a list of ids to exclude of results
 	  * @param integer $idLang Specify the id of the language used
	  *
 	  * @return array Subcategories lite tree
	  */
	public function recurseLiteCategTree($max_depth = 3, $currentDepth = 0, $id_lang = null, $excluded_ids_array = null, Link $link = null)
	{
		if (!$link)
			$link = Context::getContext()->link;

		if (is_null($id_lang))
			$id_lang = Context::getContext()->language->id;

		// recursivity for subcategories
		$children = array();
		$subcats = $this->getSubCategories($id_lang, true);
		if (($max_depth == 0 || $currentDepth < $max_depth) && $subcats && count($subcats))
			foreach ($subcats as &$subcat)
			{
				if (!$subcat['id_recipe_category'])
					break;
				elseif (!is_array($excluded_ids_array) || !in_array($subcat['id_recipe_category'], $excluded_ids_array))
				{
					$categ = new RecipeCategory($subcat['id_recipe_category'], $id_lang);
					$categ->name = RecipeCategory::hideCMSCategoryPosition($categ->name);
					$children[] = $categ->recurseLiteCategTree($max_depth, $currentDepth + 1, $id_lang, $excluded_ids_array);
				}
			}

		return array(
			'id' => $this->id_recipe_category,
			'link' => $link->getCMSCategoryLink($this->id, $this->link_rewrite),
			'name' => $this->name,
			'desc'=> $this->description,
			'children' => $children
		);
	}

	public static function getRecurseCategory($id_lang = null, $current = 1, $active = 1, $links = 0, Link $link = null)
	{
		if (!$link)
			$link = Context::getContext()->link;
		if (is_null($id_lang))
			$id_lang = Context::getContext()->language->id;

		$sql = 'SELECT c.`id_recipe_category`, c.`id_parent`, c.`level_depth`, cl.`name`, cl.`link_rewrite`
				FROM `'._DB_PREFIX_.'recipe_category` c
				JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON c.`id_recipe_category` = cl.`id_recipe_category`
					WHERE c.`id_recipe_category` = '.(int)$current.'
					AND `id_lang` = '.(int)$id_lang;
		$category = Db::getInstance()->getRow($sql);

		$sql = 'SELECT c.`id_recipe_category`
				FROM `'._DB_PREFIX_.'recipe_category` c
				WHERE c.`id_parent` = '.(int)$current.
					($active ? ' AND c.`active` = 1' : '');
		$result = Db::getInstance()->executeS($sql);
		foreach ($result as $row)
			$category['children'][] = RecipeCategory::getRecurseCategory($id_lang, $row['id_recipe_category'], $active, $links);

		$sql = 'SELECT c.`id_recipe`, cl.`meta_title`, cl.`link_rewrite`
				FROM `'._DB_PREFIX_.'recipe` c
				'.Shop::addSqlAssociation('recipe', 'c').'
				JOIN `'._DB_PREFIX_.'recipe_lang` cl ON c.`id_recipe` = cl.`id_recipe`
				WHERE `id_recipe_category` = '.(int)$current.'
				AND cl.`id_lang` = '.(int)$id_lang.($active ? ' AND c.`active` = 1' : '').'
				GROUP BY c.id_recipe
				ORDER BY c.`position`';
		$category['recipe'] = Db::getInstance()->executeS($sql);
		if ($links == 1)
		{
			$category['link'] = $link->getRecipeCategoryLink($current, $category['link_rewrite']);
			foreach ($category['recipe'] as $key => $recipe)
				$category['recipe'][$key]['link'] = $link->getRecipeLink($recipe['id_recipe'], $recipe['link_rewrite']);
		}
		return $category;
	}

	public static function recurseCMSCategory($categories, $current, $id_recipe_category = 1, $id_selected = 1, $is_html = 0)
	{
		$html = '<option value="'.$id_recipe_category.'"'.(($id_selected == $id_recipe_category) ? ' selected="selected"' : '').'>'
			.str_repeat('&nbsp;', $current['infos']['level_depth'] * 5)
			.RecipeCategory::hideCMSCategoryPosition(stripslashes($current['infos']['name'])).'</option>';
		if ($is_html == 0)
			echo $html;
		if (isset($categories[$id_recipe_category]))
			foreach (array_keys($categories[$id_recipe_category]) as $key)
				$html .= RecipeCategory::recurseCMSCategory($categories, $categories[$id_recipe_category][$key], $key, $id_selected, $is_html);
		return $html;
	}

	/**
	  * Recursively add specified CMSCategory childs to $toDelete array
	  *
	  * @param array &$toDelete Array reference where categories ID will be saved
	  * @param array $id_recipe_category Parent CMSCategory ID
	  */
	protected function recursiveDelete(&$to_delete, $id_recipe_category)
	{
	 	if (!is_array($to_delete) || !$id_recipe_category)
	 		die(Tools::displayError());

		$result = Db::getInstance()->executeS('
		SELECT `id_recipe_category`
		FROM `'._DB_PREFIX_.'recipe_category`
		WHERE `id_parent` = '.(int)$id_recipe_category);
		foreach ($result as $row)
		{
			$to_delete[] = (int)$row['id_recipe_category'];
			$this->recursiveDelete($to_delete, (int)$row['id_recipe_category']);
		}
	}

	public function delete()
	{
		if ($this->id == 1) return false;

		$this->clearCache();

		// Get children categories
		$to_delete = array((int)$this->id);
		$this->recursiveDelete($to_delete, (int)$this->id);
		$to_delete = array_unique($to_delete);

		// Delete CMS Category and its child from database
		$list = count($to_delete) > 1 ? implode(',', $to_delete) : (int)$this->id;
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'recipe_category` WHERE `id_recipe_category` IN ('.$list.')');
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'recipe_category_lang` WHERE `id_recipe_category` IN ('.$list.')');

		RecipeCategory::cleanPositions($this->id_parent);

		// Delete pages which are in categories to delete
		$result = Db::getInstance()->executeS('
		SELECT `id_recipe`
		FROM `'._DB_PREFIX_.'recipe`
		WHERE `id_recipe_category` IN ('.$list.')');
		foreach ($result as $p)
		{
			$product = new Recipe($p['id_recipe']);
			if (Validate::isLoadedObject($product))
				$product->delete();
		}
		return true;
	}

	/**
	 * Delete several categories from database
	 *
	 * return boolean Deletion result
	 */
	public function deleteSelection($categories)
	{
		$return = 1;
		foreach ($categories as $id_category_recipe)
		{
			$category_recipe = new RecipeCategory($id_category_recipe);
			$return &= $category_recipe->delete();
		}
		return $return;
	}

	/**
	  * Get the number of parent categories
	  *
	  * @return integer Level depth
	  */
	public function calcLevelDepth()
	{
		$parentCMSCategory = new RecipeCategory($this->id_parent);
		if (!$parentCMSCategory)
			die('parent CMS Category does not exist');
		return $parentCMSCategory->level_depth + 1;
	}

	/**
	  * Return available categories
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active categories
	  * @return array Categories
	  */
	public static function getCategories($id_lang, $active = true, $order = true)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT *
		FROM `'._DB_PREFIX_.'recipe_category` c
		LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON c.`id_recipe_category` = cl.`id_recipe_category`
		WHERE `id_lang` = '.(int)$id_lang.'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		if (!$order)
			return $result;

		$categories = array();
		foreach ($result as $row)
			$categories[$row['id_parent']][$row['id_recipe_category']]['infos'] = $row;
		return $categories;
	}

	public static function getSimpleCategories($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.`id_recipe_category`, cl.`name`
		FROM `'._DB_PREFIX_.'recipe_category` c
		LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON (c.`id_recipe_category` = cl.`id_recipe_category`)
		WHERE cl.`id_lang` = '.(int)$id_lang.'
		ORDER BY cl.`name`');
	}

	/**
	  * Return current CMSCategory childs
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active categories
	  * @return array Categories
	  */
	public function getSubCategories($id_lang, $active = true)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
		FROM `'._DB_PREFIX_.'recipe_category` c
		LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON (c.`id_recipe_category` = cl.`id_recipe_category` AND `id_lang` = '.(int)$id_lang.')
		WHERE `id_parent` = '.(int)$this->id.'
		'.($active ? 'AND `active` = 1' : '').'
		GROUP BY c.`id_recipe_category`
		ORDER BY `name` ASC');

		// Modify SQL result
		foreach ($result as &$row)
			$row['name'] = RecipeCategory::hideCMSCategoryPosition($row['name']);
		return $result;
	}
	
	public static function getSubCategoriesByDepth($id_cat, $depth, $id_lang, $active = true)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
		FROM `'._DB_PREFIX_.'recipe_category` c
		LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON (c.`id_recipe_category` = cl.`id_recipe_category` AND `id_lang` = '.(int)$id_lang.')
		WHERE `id_parent` = '.(int)$id_cat.'
		'.($active ? 'AND `active` = 1' : '').'
		GROUP BY c.`id_recipe_category`
		ORDER BY `position` ASC');
		
		if(isset($result[0])){
			if($result[0]['level_depth'] != $depth){
				foreach ($result as &$row)
				{
					$row['subcats'] = RecipeCategory::getSubCategoriesByDepth($row['id_recipe_category'], $depth, $id_lang, $active);
				}
			}
		}
		
		return $result;
	}

	/**
	  * Hide CMSCategory prefix used for position
	  *
	  * @param string $name CMSCategory name
	  * @return string Name without position
	  */
	public static function hideCMSCategoryPosition($name)
	{
		return preg_replace('/^[0-9]+\./', '', $name);
	}

	/**
	  * Return main categories
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active categories
	  * @return array categories
	  */
	public static function getHomeCategories($id_lang, $active = true)
	{
		return RecipeCategory::getChildren(1, $id_lang, $active);
	}

	public static function getChildren($id_parent, $id_lang, $active = true)
	{
		if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.`id_recipe_category`, cl.`name`, cl.`link_rewrite`
		FROM `'._DB_PREFIX_.'recipe_category` c
		LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON c.`id_recipe_category` = cl.`id_recipe_category`
		WHERE `id_lang` = '.(int)$id_lang.'
		AND c.`id_parent` = '.(int)$id_parent.'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		// Modify SQL result
		$results_array = array();
		foreach ($result as $row)
		{
			$row['name'] = RecipeCategory::hideCMSCategoryPosition($row['name']);
			$results_array[] = $row;
		}
		return $results_array;
	}

	/**
	  * Check if CMSCategory can be moved in another one
	  *
	  * @param integer $id_parent Parent candidate
	  * @return boolean Parent validity
	  */
	public static function checkBeforeMove($id_recipe_category, $id_parent)
	{
		if ($id_recipe_category == $id_parent) return false;
		if ($id_parent == 1) return true;
		$i = (int)$id_parent;

		while (42)
		{
			$result = Db::getInstance()->getRow('SELECT `id_parent` FROM `'._DB_PREFIX_.'recipe_category` WHERE `id_recipe_category` = '.(int)$i);
			if (!isset($result['id_parent'])) return false;
			if ($result['id_parent'] == $id_recipe_category) return false;
			if ($result['id_parent'] == 1) return true;
			$i = $result['id_parent'];
		}
	}

	public static function getLinkRewrite($id_recipe_category, $id_lang)
	{
		if (!Validate::isUnsignedId($id_recipe_category) || !Validate::isUnsignedId($id_lang))
			return false;

		if (isset(self::$_links[$id_recipe_category.'-'.$id_lang]))
			return self::$_links[$id_recipe_category.'-'.$id_lang];

		$result = Db::getInstance()->getRow('
		SELECT cl.`link_rewrite`
		FROM `'._DB_PREFIX_.'recipe_category` c
		LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON c.`id_recipe_category` = cl.`id_recipe_category`
		WHERE `id_lang` = '.(int)$id_lang.'
		AND c.`id_recipe_category` = '.(int)$id_recipe_category);
		self::$_links[$id_recipe_category.'-'.$id_lang] = $result['link_rewrite'];
		return $result['link_rewrite'];
	}

	public function getLink(Link $link = null)
	{
		if (!$link)
			$link = Context::getContext()->link;
		return $link->getRecipeCategoryLink($this->id, $this->link_rewrite);
	}

	public function getName($id_lang = null)
	{
		$context = Context::getContext();
		if (!$id_lang)
		{
			if (isset($this->name[$context->language->id]))
				$id_lang = $context->language->id;
			else
				$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		}
		return isset($this->name[$id_lang]) ? $this->name[$id_lang] : '';
	}

	/**
	  * Light back office search for categories
	  *
	  * @param integer $id_lang Language ID
	  * @param string $query Searched string
	  * @param boolean $unrestricted allows search without lang and includes first CMSCategory and exact match
	  * @return array Corresponding categories
	  */
	public static function searchByName($id_lang, $query, $unrestricted = false)
	{
		if ($unrestricted === true)
			return Db::getInstance()->getRow('
			SELECT c.*, cl.*
			FROM `'._DB_PREFIX_.'recipe_category` c
			LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON (c.`id_recipe_category` = cl.`id_recipe_category`)
			WHERE `name` LIKE \''.pSQL($query).'\'');
		else
			return Db::getInstance()->executeS('
			SELECT c.*, cl.*
			FROM `'._DB_PREFIX_.'recipe_category` c
			LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON (c.`id_recipe_category` = cl.`id_recipe_category` AND `id_lang` = '.(int)$id_lang.')
			WHERE `name` LIKE \'%'.pSQL($query).'%\' AND c.`id_recipe_category` != 1');
	}

	/**
	  * Retrieve CMSCategory by name and parent CMSCategory id
	  *
	  * @param integer $id_lang Language ID
	  * @param string  $CMSCategory_name Searched CMSCategory name
	  * @param integer $id_parent_CMSCategory parent CMSCategory ID
	  * @return array Corresponding CMSCategory
	  *	@deprecated
	  */
	public static function searchByNameAndParentCMSCategoryId($id_lang, $CMSCategory_name, $id_parent_CMSCategory)
	{
		Tools::displayAsDeprecated();
		return Db::getInstance()->getRow('
		SELECT c.*, cl.*
	    FROM `'._DB_PREFIX_.'recipe_category` c
	    LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON (c.`id_recipe_category` = cl.`id_recipe_category` AND `id_lang` = '.(int)$id_lang.')
	    WHERE `name`  LIKE \''.pSQL($CMSCategory_name).'\'
		AND c.`id_recipe_category` != 1
		AND c.`id_parent` = '.(int)$id_parent_CMSCategory);
	}

	/**
	  * Get Each parent CMSCategory of this CMSCategory until the root CMSCategory
	  *
	  * @param integer $id_lang Language ID
	  * @return array Corresponding categories
	  */
	public function getParentsCategories($id_lang = null)
	{
		if (is_null($id_lang))
			$id_lang = Context::getContext()->language->id;

		$categories = null;
		$id_current = $this->id;
		while (true)
		{
			$query = '
				SELECT c.*, cl.*
				FROM `'._DB_PREFIX_.'recipe_category` c
				LEFT JOIN `'._DB_PREFIX_.'recipe_category_lang` cl ON (c.`id_recipe_category` = cl.`id_recipe_category` AND `id_lang` = '.(int)$id_lang.')
				WHERE c.`id_recipe_category` = '.(int)$id_current.' AND c.`id_parent` != 0
			';
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

			$categories[] = $result[0];
			if (!$result || $result[0]['id_parent'] == 1)
				return $categories;
			$id_current = $result[0]['id_parent'];
		}
	}

	public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->executeS('
			SELECT cp.`id_recipe_category`, cp.`position`, cp.`id_parent`
			FROM `'._DB_PREFIX_.'recipe_category` cp
			WHERE cp.`id_parent` = '.(int)$this->id_parent.'
			ORDER BY cp.`position` ASC'
		))
			return false;
		foreach ($res as $category)
			if ((int)$category['id_recipe_category'] == (int)$this->id)
				$moved_category = $category;

		if (!isset($moved_category) || !isset($position))
			return false;
		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'recipe_category`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)$moved_category['position'].' AND `position` <= '.(int)$position
				: '< '.(int)$moved_category['position'].' AND `position` >= '.(int)$position).'
			AND `id_parent`='.(int)$moved_category['id_parent'])
		&& Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'recipe_category`
			SET `position` = '.(int)$position.'
			WHERE `id_parent` = '.(int)$moved_category['id_parent'].'
			AND `id_recipe_category`='.(int)$moved_category['id_recipe_category']));
	}

	public static function cleanPositions($id_category_parent)
	{
		$result = Db::getInstance()->executeS('
		SELECT `id_recipe_category`
		FROM `'._DB_PREFIX_.'recipe_category`
		WHERE `id_parent` = '.(int)$id_category_parent.'
		ORDER BY `position`');
		$sizeof = count($result);
		for ($i = 0; $i < $sizeof; ++$i)
		{
			$sql = '
			UPDATE `'._DB_PREFIX_.'recipe_category`
			SET `position` = '.(int)$i.'
			WHERE `id_parent` = '.(int)$id_category_parent.'
			AND `id_recipe_category` = '.(int)$result[$i]['id_recipe_category'];
			Db::getInstance()->execute($sql);
		}
		return true;
	}

	public static function getLastPosition($id_category_parent)
	{
		return (Db::getInstance()->getValue('SELECT MAX(position)+1 FROM `'._DB_PREFIX_.'recipe_category` WHERE `id_parent` = '.(int)$id_category_parent));
	}

	public static function getUrlRewriteInformations($id_category)
	{
	    $sql = '
		SELECT l.`id_lang`, c.`link_rewrite`
		FROM `'._DB_PREFIX_.'recipe_category_lang` AS c
		LEFT JOIN  `'._DB_PREFIX_.'lang` AS l ON c.`id_lang` = l.`id_lang`
		WHERE c.`id_recipe_category` = '.(int)$id_category.'
		AND l.`active` = 1';
		$arr_return = Db::getInstance()->executeS($sql);
		return $arr_return;
	}
}


