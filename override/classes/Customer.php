<?php

class Customer extends CustomerCore
{

    public $id_lcdb_import;

    public static $definition = array(
        'table' => 'customer',
        'primary' => 'id_customer',
        'fields' => array(
            'id_lcdb_import' => 			array('type' => self::TYPE_INT),
            'secure_key' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMd5', 'copy_post' => false),
            'lastname' => 					array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'firstname' => 					array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'email' => 						array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128),
            'passwd' => 					array('type' => self::TYPE_STRING, 'validate' => 'isPasswd', 'required' => true, 'size' => 32),
            'last_passwd_gen' =>			array('type' => self::TYPE_STRING, 'copy_post' => false),
            'id_gender' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'birthday' => 					array('type' => self::TYPE_DATE, 'validate' => 'isBirthDate'),
            'newsletter' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'newsletter_date_add' =>		array('type' => self::TYPE_DATE,'copy_post' => false),
            'ip_registration_newsletter' =>	array('type' => self::TYPE_STRING, 'copy_post' => false),
            'optin' => 						array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'website' =>					array('type' => self::TYPE_STRING, 'validate' => 'isUrl'),
            'company' =>					array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'siret' =>						array('type' => self::TYPE_STRING, 'validate' => 'isSiret'),
            'ape' =>						array('type' => self::TYPE_STRING, 'validate' => 'isApe'),
            'outstanding_allow_amount' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'copy_post' => false),
            'show_public_prices' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'id_risk' =>					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'copy_post' => false),
            'max_payment_days' =>			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'copy_post' => false),
            'active' => 					array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'deleted' => 					array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'note' => 						array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 65000, 'copy_post' => false),
            'is_guest' =>					array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'id_shop' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'id_shop_group' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'id_default_group' => 			array('type' => self::TYPE_INT, 'copy_post' => false),
            'date_add' => 					array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => 					array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    //@true: the current customer is a Pro (default group setted to "Pro")
    //@false: The current customer is not a Pro
    public static function  isCurrentCustomerPro(){
        //get current customer
        $context = Context::getContext();
        $customer = $context->customer;
        if ($customer){
            //get customer default group
            $group = new Group($customer->id_default_group);
            foreach ($group->name as $name){
                //return true if the name is equal to "Pro"
                if (strcasecmp($name, 'Pro') == 0)
                    return true;
            }
        }
        return false;
    }

    //@true: Customer has more than one command, he is not new.
    //@false: Customer has one (or none) command, he is new
    public static function  isNewCustomer($id_customer){
        $nb = Order::getCustomerNbOrders($id_customer);
        if ($nb < 2)
            return true;
        return false;
    }

    /**
     * Return customer addresses
     *
     * @param integer $id_lang Language ID
     * @param integer $add_carrier_relay_addresses Language ID
     * @return array Addresses
     */
    public function getAddresses($id_lang, $add_carrier_relay_addresses = false)
    {
        $sql = 'SELECT a.*, cl.`name` AS country, s.name AS state, s.iso_code AS state_iso
				FROM `'._DB_PREFIX_.'address` a
				LEFT JOIN `'._DB_PREFIX_.'country` c ON (a.`id_country` = c.`id_country`)
				LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country`)
				LEFT JOIN `'._DB_PREFIX_.'state` s ON (s.`id_state` = a.`id_state`)
				'.(Context::getContext()->shop->getGroup()->share_order ? '' : Shop::addSqlAssociation('country', 'c')).'
				WHERE (`id_lang` = '.(int)$id_lang.' AND `id_customer` = '.(int)$this->id.' AND a.`deleted` = 0)';
        if ($add_carrier_relay_addresses)
            $sql .= ' OR (`id_lang` = '.(int)$id_lang.' AND `id_carrier_relay` != 0 AND a.`deleted` = 0 AND a.active = 1)';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }


}

