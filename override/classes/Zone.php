<?php

class Zone extends ZoneCore
{

	public $horaire;
	public $h_start;
	public $h_end;
    public $h_auto_close;
	public $tranche;
	public $creneau;
	public $calendar;
	public $minimum_order;
	public $abonnement;
    public $free_shipping;

	public static $definition = array(
		'table' => 'zone',
		'primary' => 'id_zone',
		'fields' => array(
			'name' => 	array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
			'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'abonnement' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'horaire' => array('type' => self::TYPE_BOOL, 'validate' => 'isInt'),
			'h_start' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
			'h_end' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'h_auto_close' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'tranche' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
			'creneau' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'minimum_order' => array('type' => self::TYPE_FLOAT,'validate' => 'isFloat'),
            'free_shipping' => array('type' => self::TYPE_FLOAT,'validate' => 'isFloat'),
			'calendar' => array('type' => self::TYPE_STRING),
		),
	);

	/**
	 * Get a minimum order from a zone ID
	 *
	 * @param integer $id_zone
	 * @return integer minimum_order
	 */
	public static function getMinimumOrderById($id_zone)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `minimum_order`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `id_zone` = \''.pSQL($id_zone).'\'
		');
	}

	/**
	 * Get a all info for date delivery
	 *
	 * @param integer $id_zone
	 * @return all
	 * NEED ALREADY REGISTERED DELIVERY
	 */
	public static function getZoneCustomInfos($id_zone)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT `id_zone`, `name`, `abonnement`, `calendar`, `horaire`, `tranche`, `creneau`, `h_start`, `h_end`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `id_zone` = \''.pSQL($id_zone).'\'
		');
	}
}

