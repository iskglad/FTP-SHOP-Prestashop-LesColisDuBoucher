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

class RecipeCore extends ObjectModel
{
	/** @var string Name */
	public $title;
	public $type_meat;
	public $type_cooking;
	public $difficulty;
	public $number_people;
	public $duration;
	public $cooking_time;
	public $prior_content;
	public $ingredients_content;
	public $recipe_content;
	public $tips_content;
	public $meta_title;
	public $link_rewrite;
	public $id_recipe_category;
	public $position;
	public $active;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'recipe',
		'primary' => 'id_recipe',
		'multilang' => true,
		'fields' => array(
			'id_recipe_category' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'position' => 			array('type' => self::TYPE_INT),
			'active' => 			array('type' => self::TYPE_BOOL),
			
			// Lang fields
			'title' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
			'type_meat' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
			'type_cooking' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
			'difficulty' =>	array('type' => self::TYPE_STRING, 'lang' => true,  'validate' => 'isGenericName', 'size' => 10),
			'number_people' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 50),
			'duration' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 50),
			'cooking_time' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 50),
			'prior_content' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
			'ingredients_content' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
			'recipe_content' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
			'tips_content' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
			'meta_title' =>			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
			'link_rewrite' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 128),
		),
	);

	protected	$webserviceParameters = array(
		'objectNodeName' => 'content',
		'objectsNodeName' => 'content_management_system',
	);

	public function add($autodate = true, $null_values = false)
	{
		$this->position = Recipe::getLastPosition((int)$this->id_recipe_category);
		return parent::add($autodate, true);
	}

	public function update($null_values = false)
	{
		if (parent::update($null_values))
			return $this->cleanPositions($this->id_recipe_category);
		return false;
	}

	public function delete()
	{
	 	if (parent::delete())
			return $this->cleanPositions($this->id_recipe_category);
		return false;
	}

	public static function getLinks($id_lang, $selection = null, $active = true, Link $link = null)
	{
		if (!$link)
			$link = Context::getContext()->link;
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.id_recipe, cl.link_rewrite, cl.title
		FROM '._DB_PREFIX_.'recipe c
		LEFT JOIN '._DB_PREFIX_.'recipe_lang cl ON (c.id_recipe = cl.id_recipe AND cl.id_lang = '.(int)$id_lang.')
		'.Shop::addSqlAssociation('recipe', 'c').'
		WHERE 1
		'.(($selection !== null) ? ' AND c.id_recipe IN ('.implode(',', array_map('intval', $selection)).')' : '').
		($active ? ' AND c.`active` = 1 ' : '').
		'GROUP BY c.id_recipe
		ORDER BY c.`position`');

		$links = array();
		if ($result)
			foreach ($result as $row)
			{
				$row['link'] = $link->getRecipeLink((int)$row['id_recipe'], $row['link_rewrite']);
				$links[] = $row;
			}
		return $links;
	}

	public static function listRecipe($id_lang = null, $id_block = false, $active = true)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.id_recipe, l.title
		FROM  '._DB_PREFIX_.'recipe c
		JOIN '._DB_PREFIX_.'recipe_lang l ON (c.id_recipe = l.id_recipe)
		'.Shop::addSqlAssociation('recipe', 'c').'
		'.(($id_block) ? 'JOIN '._DB_PREFIX_.'block_recipe b ON (c.id_recipe = b.id_recipe)' : '').'
		WHERE l.id_lang = '.(int)$id_lang.(($id_block) ? ' AND b.id_block = '.(int)$id_block : '').($active ? ' AND c.`active` = 1 ' : '').'
		GROUP BY c.id_recipe
		ORDER BY c.`position`');
	}

	public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->executeS('
			SELECT cp.`id_recipe`, cp.`position`, cp.`id_recipe_category`
			FROM `'._DB_PREFIX_.'recipe` cp
			WHERE cp.`id_recipe_category` = '.(int)$this->id_recipe_category.'
			ORDER BY cp.`position` ASC'
		))
			return false;

		foreach ($res as $cms)
			if ((int)$cms['id_recipe'] == (int)$this->id)
				$moved_cms = $cms;

		if (!isset($moved_cms) || !isset($position))
			return false;

		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'recipe`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)$moved_cms['position'].' AND `position` <= '.(int)$position
				: '< '.(int)$moved_cms['position'].' AND `position` >= '.(int)$position).'
			AND `id_recipe_category`='.(int)$moved_cms['id_recipe_category'])
		&& Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'recipe`
			SET `position` = '.(int)$position.'
			WHERE `id_recipe` = '.(int)$moved_cms['id_recipe'].'
			AND `id_recipe_category`='.(int)$moved_cms['id_recipe_category']));
	}

	public static function cleanPositions($id_category)
	{
		$sql = '
		SELECT `id_recipe`
		FROM `'._DB_PREFIX_.'recipe`
		WHERE `id_recipe_category` = '.(int)$id_category.'
		ORDER BY `position`';

		$result = Db::getInstance()->executeS($sql);

		for ($i = 0, $total = count($result); $i < $total; ++$i)
		{
			$sql = 'UPDATE `'._DB_PREFIX_.'recipe`
					SET `position` = '.(int)$i.'
					WHERE `id_recipe_category` = '.(int)$id_category.'
						AND `id_recipe` = '.(int)$result[$i]['id_recipe'];
			Db::getInstance()->execute($sql);
		}
		return true;
	}

	public static function getLastPosition($id_category)
	{
		$sql = '
		SELECT MAX(position) + 1
		FROM `'._DB_PREFIX_.'recipe`
		WHERE `id_recipe_category` = '.(int)$id_category;

		return (Db::getInstance()->getValue($sql));
	}

	public static function getRecipePages($id_lang = null, $id_recipe_category = null, $active = true)
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('recipe', 'c');
		if ($id_lang)
			$sql->innerJoin('recipe_lang', 'l', 'c.id_recipe = l.id_recipe AND l.id_lang = '.(int)$id_lang);

		if ($active)
			$sql->where('c.active = 1');

		if ($id_recipe_category)
			$sql->where('c.id_recipe_category = '.(int)$id_recipe_category);

		$sql->orderBy('position');

		return Db::getInstance()->executeS($sql);
	}

	public static function getUrlRewriteInformations($id_recipe)
	{
	    $sql = 'SELECT l.`id_lang`, c.`link_rewrite`
				FROM `'._DB_PREFIX_.'recipe_lang` AS c
				LEFT JOIN  `'._DB_PREFIX_.'lang` AS l ON c.`id_lang` = l.`id_lang`
				WHERE c.`id_recipe` = '.(int)$id_recipe.'
				AND l.`active` = 1';

		return Db::getInstance()->executeS($sql);
	}
}
