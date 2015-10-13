<?php

/*
** Zip Code Zone
** MARICHAL Emmanuel
** emmanuel.marichal@gmail.com
*/

class Address extends AddressCore
{
    /** @var string Code (acces code to the building) */
    public $code;

    /** @var string floor (i.e 7eme etage, 2em, rez de chaussez, etc...) */
    public $floor;


    public static $definition = array(
        'table' => 'address',
        'primary' => 'id_address',
        'fields' => array(
            'id_customer' => 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_manufacturer' => 	array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_supplier' => 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_warehouse' => 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_country' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_state' => 			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
            'alias' => 				array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'company' => 			array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64),
            'lastname' => 			array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'firstname' => 			array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'vat_number' =>	 		array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'address1' => 			array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'required' => true, 'size' => 128),
            'address2' => 			array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128),
            'postcode' => 			array('type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 12),
            'city' => 				array('type' => self::TYPE_STRING, 'validate' => 'isCityName', 'required' => true, 'size' => 64),
            'other' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 300),
            'phone' => 				array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 16),
            'phone_mobile' => 		array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 16),
            'dni' => 				array('type' => self::TYPE_STRING, 'validate' => 'isDniLite', 'size' => 16),
            'deleted' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'date_add' => 			array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'copy_post' => false),
            'date_upd' => 			array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'copy_post' => false),
            //custom
            'code' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 32),
            'floor' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 32),
        ),
    );

    /**
     * Returns id_address for a given id_carrier_relay
     * @since 1.5.0
     * @param int $id_carrier_relay
     * @return int $id_address
     */
    public static function getAddressIdByCarrierRelayId($id_carrier_relay)
    {
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('id_carrier_relay = '.(int)$id_carrier_relay);
        $query->where('deleted = 0');
        $query->where('id_customer = 0');
        $query->where('id_manufacturer = 0');
        $query->where('id_warehouse = 0');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

//    module zip code zone
    public static function getZoneById_zipcodezone($id_address)
    {
        $address = new Address((int)$id_address);

        $zone = Db::getInstance()->getValue('
		SELECT id_zone
		FROM '._DB_PREFIX_.'zip_code_zone
		WHERE id_country = '.(int)$address->id_country.'
		AND min <= '.(int)$address->postcode.' AND max >= '.(int)$address->postcode);

        return $zone ? (int)$zone : 0;//(int)parent::getZoneById((int)$id_address);
    }
    public static function getZoneById($id_address)
    {
        if (isset(self::$_idZones[$id_address])){
            return self::$_idZones[$id_address];
        }

        $cp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT a.`postcode`
		FROM `'._DB_PREFIX_.'address` a
		WHERE a.`id_address` = '.(int)$id_address);
        self::$_idZones[$id_address] = self::getZoneByZipCode($cp);
        return self::$_idZones[$id_address];
    }

    /**
     * Get zone id for a given zipcode
     *
     * @param string $zipcode
     * @return integer Zone id
     */
    public static function getZoneByZipCode($cp)
    {
        /*$zone = Db::getInstance()->getValue('
                SELECT id_zone
                FROM '._DB_PREFIX_.'zip_code_zone
                WHERE min <= '.(int)$cp.' AND max >= '.(int)$cp);
                */
        $idZone = 0;
        if (!$cp)
            die(ERROR_MSG_EMPTY_POSTCODE);
        if (ZoneCustom::isProche($cp)){
            $idZone = ID_ZONE_JET; // Proche banlieue
        } else if (ZoneCustom::isGrande($cp)) {
            $idZone = ID_ZONE_ECOLOTRANS; // Grande banlieue
        } else {
            $idZone = ID_ZONE_UPS; // Province
        }
        return $idZone;
    }
}