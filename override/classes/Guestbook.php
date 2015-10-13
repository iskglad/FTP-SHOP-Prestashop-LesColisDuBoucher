<?php

class Guestbook extends ObjectModel
{
	/** @var string Name */
	public $firstname;
	public $lastname;
	public $email;
	public $city;
	public $message;
	public $active;
	public $id_lcdb_import;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'guestbook',
		'primary' => 'id_guestbook',
		'multilang' => true,
		'fields' => array(
			'active' => 			array('type' => self::TYPE_BOOL),
			'id_lcdb_import' => 	array('type' => self::TYPE_INT),
			'firstname' =>	array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 100),
			'lastname' =>	array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 100),
			'email' =>	array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100),
			// Lang fields
			'city' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
			'message' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
		),
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

	public static function listGuestbook($id_lang = null, $id_block = false, $active = true)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.id_guestbook, c.email
		FROM  '._DB_PREFIX_.'guestbook c
		JOIN '._DB_PREFIX_.'guestbook_lang l ON (c.id_guestbook = l.id_guestbook)
		'.Shop::addSqlAssociation('guestbook', 'c').'
		WHERE l.id_lang = '.(int)$id_lang.($active ? ' AND c.`active` = 1 ' : '').'
		GROUP BY c.id_guestbook');
	}

	public static function getGuestbookPages($id_lang = null, $id_guestbook_category = null, $active = true)
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('guestbook', 'c');
		if ($id_lang)
			$sql->innerJoin('guestbook_lang', 'l', 'c.id_guestbook = l.id_guestbook AND l.id_lang = '.(int)$id_lang);

		if ($active)
			$sql->where('c.active = 1');
		
		return Db::getInstance()->executeS($sql);
	}

	public static function getUrlRewriteInformations($id_guestbook)
	{
	    $sql = 'SELECT l.`id_lang`
				FROM `'._DB_PREFIX_.'guestbook` AS c
				LEFT JOIN  `'._DB_PREFIX_.'guestbook_lang` AS l ON c.`id_lang` = l.`id_lang`
				WHERE c.`id_guestbook` = '.(int)$id_guestbook.'
				AND c.`active` = 1';

		return Db::getInstance()->executeS($sql);
	}

}

