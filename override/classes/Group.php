<?php

class Group extends GroupCore
{
	
	public $is_group;
	
	public static $definition = array(
		'table' => 'group',
		'primary' => 'id_group',
		'multilang' => true,
		'fields' => array(
			'reduction' => 				array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
			'price_display_method' => 	array('type' => self::TYPE_INT, 'validate' => 'isPriceDisplayMethod', 'required' => true),
			'show_prices' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'date_add' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'is_group' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),

			// Lang fields
			'name' => 					array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
		),
	);

    public static function getGroups($id_lang, $id_shop = false)
    {
        $shop_criteria = '';
        if ($id_shop)
            $shop_criteria = Shop::addSqlAssociation('group', 'g');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT DISTINCT g.`id_group`, g.`reduction`, g.`is_group`, g.`price_display_method`, gl.`name`
		FROM `'._DB_PREFIX_.'group` g
		LEFT JOIN `'._DB_PREFIX_.'group_lang` AS gl ON (g.`id_group` = gl.`id_group` AND gl.`id_lang` = '.(int)$id_lang.')
		'.$shop_criteria.'
		ORDER BY g.`id_group` ASC');
    }
}

