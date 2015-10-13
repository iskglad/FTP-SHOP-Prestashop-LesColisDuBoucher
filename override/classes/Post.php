<?php


class PostCore extends ObjectModel
{
	/** @var string Name */
	public $title;
	public $content;
	public $link;
	public $position;
	public $active;
	public $id_lcdb_import;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'post',
		'primary' => 'id_post',
		'multilang' => true,
		'fields' => array(
			'position' => 			array('type' => self::TYPE_INT),
			'active' => 			array('type' => self::TYPE_BOOL),
			'id_lcdb_import' => 	array('type' => self::TYPE_INT),
			// Lang fields
			'title' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
			'content' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
			'link' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 180),
		),
	);

	protected	$webserviceParameters = array(
		'objectNodeName' => 'content',
		'objectsNodeName' => 'content_management_system',
	);

	public function add($autodate = true, $null_values = false)
	{
		return parent::add($autodate, true);
	}

	public function update($null_values = false)
	{
		if (parent::update($null_values))
			return true;
		return false;
	}

	public function delete()
	{
	 	if (parent::delete())
			return true;
		return false;
	}

	public static function getLinks($id_lang, $selection = null, $active = true, Link $link = null)
	{
		if (!$link)
			$link = Context::getContext()->link;
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.id_post, cl.title
		FROM '._DB_PREFIX_.'post c
		LEFT JOIN '._DB_PREFIX_.'post_lang cl ON (c.id_post = cl.id_post AND cl.id_lang = '.(int)$id_lang.')
		'.Shop::addSqlAssociation('post', 'c').'
		WHERE 1
		'.(($selection !== null) ? ' AND c.id_post IN ('.implode(',', array_map('intval', $selection)).')' : '').
		($active ? ' AND c.`active` = 1 ' : '').
		'GROUP BY c.id_post
		ORDER BY c.`position`');

		$links = array();
		if ($result)
			foreach ($result as $row)
			{
				$row['link'] = $link->getPostLink((int)$row['id_post'], $row['link_rewrite']);
				$links[] = $row;
			}
		return $links;
	}

	public static function listPost($id_lang = null, $id_block = false, $active = true)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.id_post, l.title
		FROM  '._DB_PREFIX_.'post c
		JOIN '._DB_PREFIX_.'post_lang l ON (c.id_post = l.id_post)
		'.Shop::addSqlAssociation('post', 'c').'
		'.(($id_block) ? 'JOIN '._DB_PREFIX_.'block_post b ON (c.id_post = b.id_post)' : '').'
		WHERE l.id_lang = '.(int)$id_lang.(($id_block) ? ' AND b.id_block = '.(int)$id_block : '').($active ? ' AND c.`active` = 1 ' : '').'
		GROUP BY c.id_post
		ORDER BY c.`position`');
	}

	public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->executeS('
			SELECT cp.`id_post`, cp.`position`
			FROM `'._DB_PREFIX_.'post` cp
			ORDER BY cp.`position` ASC'
		))
			return false;

		foreach ($res as $post)
			if ((int)$post['id_post'] == (int)$this->id)
				$moved_post = $post;

		if (!isset($moved_post) || !isset($position))
			return false;

		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'post`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)$moved_post['position'].' AND `position` <= '.(int)$position
				: '< '.(int)$moved_post['position'].' AND `position` >= '.(int)$position))
		&& Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'post`
			SET `position` = '.(int)$position.'
			WHERE `id_post` = '.(int)$moved_post['id_post']));
	}

	public static function cleanPositions($id_category)
	{
		$sql = '
		SELECT `id_post`
		FROM `'._DB_PREFIX_.'post`
		ORDER BY `position`';

		$result = Db::getInstance()->executeS($sql);

		for ($i = 0, $total = count($result); $i < $total; ++$i)
		{
			$sql = 'UPDATE `'._DB_PREFIX_.'post`
					SET `position` = '.(int)$i.'
					WHERE `id_post` = '.(int)$result[$i]['id_post'];
			Db::getInstance()->execute($sql);
		}
		return true;
	}

	public static function getLastPosition($id_category)
	{
		$sql = '
		SELECT MAX(position) + 1
		FROM `'._DB_PREFIX_.'post`';

		return (Db::getInstance()->getValue($sql));
	}

	public static function getPostPages($id_lang = null, $id_post_category = null, $active = true)
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('post', 'c');
		if ($id_lang)
			$sql->innerJoin('post_lang', 'l', 'c.id_post = l.id_post AND l.id_lang = '.(int)$id_lang);

		if ($active)
			$sql->where('c.active = 1');
			
		$sql->orderBy('position');

		return Db::getInstance()->executeS($sql);
	}

	public static function getUrlRewriteInformations($id_post)
	{
	    $sql = 'SELECT l.`id_lang`, c.`link_rewrite`
				FROM `'._DB_PREFIX_.'post_lang` AS c
				LEFT JOIN  `'._DB_PREFIX_.'lang` AS l ON c.`id_lang` = l.`id_lang`
				WHERE c.`id_post` = '.(int)$id_post.'
				AND l.`active` = 1';

		return Db::getInstance()->executeS($sql);
	}
}
