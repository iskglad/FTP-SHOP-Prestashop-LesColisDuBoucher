<?php

class CartRule extends CartRuleCore
{
    public $date_shipping_from;
    public $date_shipping_to;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cart_rule',
        'primary' => 'id_cart_rule',
        'multilang' => true,
        'fields' => array(
            'id_customer' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_from' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'date_to' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'description' => 			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 65534),
            'quantity' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'quantity_per_user' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'priority' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'partial_use' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'code' => 					array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 254),
            'minimum_amount' => 		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'minimum_amount_tax' => 	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'minimum_amount_currency' =>array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'minimum_amount_shipping' =>array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'country_restriction' =>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'carrier_restriction' => 	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'group_restriction' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'cart_rule_restriction' => 	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'product_restriction' => 	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'shop_restriction' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'free_shipping' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'reduction_percent' => 		array('type' => self::TYPE_FLOAT, 'validate' => 'isPercentage'),
            'reduction_amount' => 		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'reduction_tax' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'reduction_currency' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'reduction_product' => 		array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'gift_product' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'gift_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'highlight' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

            // Lang fields
            'name' => 					array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 254),

            //custom
            'date_shipping_from' =>     array('type' => self::TYPE_DATE),
            'date_shipping_to' => 		array('type' => self::TYPE_DATE),
        ),
    );

    /*
     * Check shipping validity
     * @return BOOL
     */
    public function checkShippingValidity($shipping_date, $display_error = false){
        if (strtotime($this->date_from) > $shipping_date)
            return (!$display_error) ? false : Tools::displayError('This voucher is not valid yet');
        if (strtotime($this->date_to) < $shipping_date)
            return (!$display_error) ? false : Tools::displayError('This voucher has expired');
        return true;
    }
}

