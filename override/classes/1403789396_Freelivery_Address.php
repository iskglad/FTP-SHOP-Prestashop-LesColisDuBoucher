<?php

class Address extends AddressCore
{

	/**
	 * Get zone id for a given address
	 *
	 * @param integer $id_address Address id for which we want to get zone id
	 * @return string zipcode
	 */
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

		if (substr($cp, 0, 2) == "75") {
			$idZone = ID_ZONE_PARIS; // Paris
		} else if (ZoneCustom::isProche($cp)){
			$idZone = ID_ZONE_PETITE_BANLIEUE; // Proche banlieue
		} else if (ZoneCustom::isGrande($cp)) {
			$idZone = ID_ZONE_GRANDE_BANLIEUE; // Grande banlieue
		} else {
			$idZone = ID_ZONE_PROVINCE; // Province
		}

		return $idZone;

	}
}
