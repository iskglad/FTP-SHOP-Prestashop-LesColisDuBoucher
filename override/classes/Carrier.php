<?php

class Carrier extends CarrierCore
{
	
	public $description;
    public $lon;
    public $lat;
    public $address;
    public $phone;
    public $mention;
    public $day_CE;

	/**
	 * @see ObjectModel::$definition
	 */
    public static $definition = array(
        'table' => 'carrier',
        'primary' => 'id_carrier',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            /* Classic fields */
            'id_reference' => 			array('type' => self::TYPE_INT),
            'name' => 					array('type' => self::TYPE_STRING, 'validate' => 'isCarrierName', 'required' => true, 'size' => 64),
            'type_carrier' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'active' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'is_free' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'url' => 					array('type' => self::TYPE_STRING, 'validate' => 'isAbsoluteUrl'),
            'shipping_handling' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'shipping_external' => 		array('type' => self::TYPE_BOOL),
            'range_behavior' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'shipping_method' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'max_width' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'max_height' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'max_depth' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'max_weight' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'grade' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'size' => 1),
            'external_module_name' => 	array('type' => self::TYPE_STRING, 'size' => 64),
            'is_module' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'need_range' => 			array('type' => self::TYPE_BOOL),
            'position' => 				array('type' => self::TYPE_INT),
            'deleted' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'lon' => 			        array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 100),
            'lat' => 			        array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 100),

            /* Lang fields */
            'delay' => 					array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
            'description' => 			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
            'address' => 	    		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
            'phone' => 			        array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 50),
            'mention' => 	    		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
            'day_CE' => 	    		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 30),
        ),
    );

	public function getOrders()
	{
		$sql = 'SELECT *
				FROM `'._DB_PREFIX_.'orders`';
			//		.Shop::addSqlRestriction();
		$result = Db::getInstance()->getRow($sql);

		return $result;
	}

	/**
	 * Get all carriers in a given language
	 *
	 * @param integer $id_lang Language id
	 * @param $modules_filters, possible values:
			PS_CARRIERS_ONLY
			CARRIERS_MODULE
			CARRIERS_MODULE_NEED_RANGE
			PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
			ALL_CARRIERS
	 * @param boolean $active Returns only active carriers when true
	 * @return array Carriers
	 */
	public static function getCarriers($id_lang, $active = false, $delete = false, $id_zone = false, $ids_group = null, $modules_filters = self::PS_CARRIERS_ONLY)
	{
		if (!Validate::isBool($active))
			die(Tools::displayError());
		if ($ids_group)
		{
			$ids = '';
			foreach ($ids_group as $id)
				$ids .= (int)$id.', ';
			$ids = rtrim($ids, ', ');
			if ($ids == '')
				return array();
		}

		$sql = 'SELECT c.*, cl.delay
				FROM `'._DB_PREFIX_.'carrier` c
				LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
				LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`)'.
				($id_zone ? 'LEFT JOIN `'._DB_PREFIX_.'zone` z ON (z.`id_zone` = '.(int)$id_zone.')' : '').'
				'.Shop::addSqlAssociation('carrier', 'c').'
				WHERE c.`deleted` = '.($delete ? '1' : '0').
					($active ? ' AND c.`active` = 1' : '').
					($id_zone ? ' AND cz.`id_zone` = '.(int)$id_zone.'
					AND c.`type_carrier` != 2
					AND z.`active` = 1 ' : ' ');
					//AND c.`type_carrier` != 2 => no point relais
		switch ($modules_filters)
		{
			case 1 :
				$sql .= 'AND c.is_module = 0 ';
			break;
			case 2 :
				$sql .= 'AND c.is_module = 1 ';
			break;
			case 3 :
				$sql .= 'AND c.is_module = 1 AND c.need_range = 1 ';
			break;
			case 4 :
				$sql .= 'AND (c.is_module = 0 OR c.need_range = 1) ';
			break;
			case 5 :
				$sql .= '';
			break;

		}
		$sql .= ($ids_group ? ' AND c.id_carrier IN (SELECT id_carrier FROM '._DB_PREFIX_.'carrier_group WHERE id_group IN ('.$ids.')) ' : '').'
			GROUP BY c.`id_carrier`
			ORDER BY c.`position` ASC';

		$carriers = Db::getInstance()->executeS($sql);

		if (is_array($carriers) && count($carriers))
		{
			foreach ($carriers as $key => $carrier)
				if ($carrier['name'] == '0')
					$carriers[$key]['name'] = Configuration::get('PS_SHOP_NAME');
		}
		else
			$carriers = array();

		return $carriers;
	}

    /**
     * Get all carriers in a given language (without taking carrier Relays)
     *
     * @param integer $id_lang Language id
     * @return array Carriers
     */
    public static function getCarriersWithoutRelays($id_lang){
        //get all active carriers
        $carriers = Carrier::getCarriers($id_lang, true);

        foreach ($carriers as $key => $carrier){
            //if carrier relay
            if ($carrier['type_carrier'] == CARRIER_TYPE_RELAY)
                unset($carriers[$key]);
        }
        return $carriers;
    }

}

