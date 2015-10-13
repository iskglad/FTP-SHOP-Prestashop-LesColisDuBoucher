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

class ZoneCustom extends ObjectModel
{
    public $cp = null;

    public $minimum = null;

    public $free_shipping = null;

    public $abonnement_by_cp = 0;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'zone_proche',
        'primary' => 'id_zonep',
        'fields' => array(
            'cp' => 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'minimum' => 	array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'free_shipping' => 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'abonnement_by_cp' => 		array('type' => self::TYPE_INT, 'validate' => 'isBool', 'copy_post' => false),
        ),
    );

    protected $_includeVars = array('addressType' => 'table');
    protected $_includeContainer = false;

    protected $webserviceParameters = array(
        'objectsNodeName' => 'addresses',
        'fields' => array(
            'cp' => array('xlink_resource'=> 'cp'),
            'minimum' => array('xlink_resource'=> 'minimum'),
            'free_shipping' => array('xlink_resource'=> 'free_shipping'),
            'abonnement_by_cp' => array('xlink_resource'=> 'abonnement_by_cp'),
        ),
    );

    /**
     * Build an address
     *
     * @param integer $id_address Existing address id in order to load object (optional)
     */
    public	function __construct($id_address = null, $id_lang = null)
    {
        parent::__construct($id_address);

        /* Get and cache address country name */
        if ($this->id)
            $this->country = Country::getNameById($id_lang ? $id_lang : Configuration::get('PS_LANG_DEFAULT'), $this->id_country);
    }


    /**
     * Return customer addresses
     *
     * @return array Addresses
     */
    public function getListProche()
    {
        $sql = 'SELECT a.*
				FROM `'._DB_PREFIX_.'zone_proche` a';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * Return customer addresses
     *
     * @return array Addresses
     */
    public function getListGrande()
    {
        $sql = 'SELECT a.*
				FROM `'._DB_PREFIX_.'zone_grande` a';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * Return true if $cp is in Proche
     *
     * @param integer $cp
     * @return array Addresses
     */
    public static function isProche($cp)
    {
        /*Cp in paris (75) are "proche"*/
        if (intval($cp / 1000) == 75)
            return true;
        /* + some execption listed in zone_proche Table*/
        $sql = 'SELECT a.*
				FROM `'._DB_PREFIX_.'zone_proche` a
				WHERE cp ='.$cp;
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql))
            return true;
        return false;
    }
    /**
     * Return true if $cp is in Grande
     *
     * @param integer $cp
     * @return array Addresses
     */
    public static function isGrande($cp)
    {
        /*All 92, 93, 94*/
        if (intval($cp / 1000) == 92 ||
            intval($cp / 1000) == 93 ||
            intval($cp / 1000) == 94)
            return true;
        /* + some execption listed in zone_grande Table*/
        $sql = 'SELECT a.*
				FROM `'._DB_PREFIX_.'zone_grande` a
				WHERE cp ='.$cp;
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql))
            return true;
        return false;
    }

    /**
     * Return true if $ville is ok for abonnement
     *
     * @param integer $cp
     * @return array Addresses
     */
    public function getAbonnementByCp($cp)
    {
        $sql = 'SELECT a.abonnement_by_cp
				FROM `'._DB_PREFIX_.'zone_grande` a
				WHERE cp ='.$cp;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }



    /**
     * @see ObjectModel::add()
     */
    // public function add($autodate = true, $null_values = false)
    // {
    // 	if (!parent::add($autodate, $null_values))
    // 		return false;

    // 	if (Validate::isUnsignedId($this->id_customer))
    // 		Customer::resetAddressCache($this->id_customer);
    // 	return true;
    // }

    // public function update($null_values = false)
    // {
    // 	// Empty related caches
    // 	if (isset(self::$_idCountries[$this->id]))
    // 		unset(self::$_idCountries[$this->id]);
    // 	if (isset(self::$_idZones[$this->id]))
    // 		unset(self::$_idZones[$this->id]);

    // 	return parent::update($null_values);
    // }

    /**
     * @see ObjectModel::delete()
     */
    // public function delete()
    // {
    // 	if (Validate::isUnsignedId($this->id_customer))
    // 		Customer::resetAddressCache($this->id_customer);

    // 	if (!$this->isUsed())
    // 		return parent::delete();
    // 	else
    // 	{
    // 		$this->deleted = true;
    // 		return $this->update();
    // 	}
    // }

    /**
     * Returns fields required for an address in an array hash
     * @return array hash values
     */
    public static function getFieldsValidate()
    {
        $tmp_addr = new Address();
        $out = $tmp_addr->fieldsValidate;

        unset($tmp_addr);
        return $out;
    }

    /**
     * @see ObjectModel::validateController()
     */
    public function validateController($htmlentities = true)
    {
        $errors = parent::validateController($htmlentities);
        if (!Configuration::get('VATNUMBER_CHECKING'))
            return $errors;
        include_once(_PS_MODULE_DIR_.'vatnumber/vatnumber.php');
        if (class_exists('VatNumber', false))
            return array_merge($errors, VatNumber::WebServiceCheck($this->vat_number));
        return $errors;
    }

}

