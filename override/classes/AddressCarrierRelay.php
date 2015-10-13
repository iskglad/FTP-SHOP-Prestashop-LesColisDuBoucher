<?php
/**
 * Created by PhpStorm.
 * User: gladisk
 * Date: 11/12/14
 * Time: 5:29 PM
 */

class AddressCarrierRelay extends Address {
    /** @var integer Customer id which address belongs to */
    public $id_carrier_relay = null;

    /**
     * @see ObjectModel::$definition
     */
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
            //custom
            'id_carrier_relay'=>    array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'firstname' => 			array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => false, 'size' => 32),
            //end custom
            'alias' => 				array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'company' => 			array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64),
            'lastname' => 			array('type' => self::TYPE_STRING, 'validate' => 'isCarrierName', 'required' => true, 'size' => 32),
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
    protected $webserviceParameters = array(
        'objectsNodeName' => 'addresses',
        'fields' => array(
            'id_customer' => array('xlink_resource'=> 'customers'),
            'id_manufacturer' => array('xlink_resource'=> 'manufacturers'),
            //custom
            'id_carrier_relay' => array('xlink_resource'=> 'carriers_relay'),
            //end custom
            'id_supplier' => array('xlink_resource'=> 'suppliers'),
            'id_warehouse' => array('xlink_resource'=> 'warehouse'),
            'id_country' => array('xlink_resource'=> 'countries'),
            'id_state' => array('xlink_resource'=> 'states'),
        ),
    );
} 