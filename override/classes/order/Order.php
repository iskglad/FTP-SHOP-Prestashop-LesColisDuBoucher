<?php

class Order extends OrderCore
{
	public $id_lcdb_import;
	public $date_delivery;
	public $hour_delivery;
    public $hour_delivery_2;
    public $hour_delivery_3;
    public $custom_relay;
    public $id_order_to_adjust;
    public $free_shipping_discount;
    public $message;
	
	public static $definition = array(
		'table' => 'orders',
		'primary' => 'id_order',
		'fields' => array(
			'id_address_delivery' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_address_invoice' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_cart' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_currency' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_shop_group' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_shop' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_lang' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_customer' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_carrier' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'current_state' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_lcdb_import' => 			array('type' => self::TYPE_INT),
			'secure_key' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMd5'),
			'payment' => 					array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
			'module' => 					array('type' => self::TYPE_STRING),
			'recyclable' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'gift' => 						array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'gift_message' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMessage'),
			'total_discounts' =>			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_discounts_tax_incl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_discounts_tax_excl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_paid' => 				array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_paid_tax_incl' => 		array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_paid_tax_excl' => 		array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_paid_real' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_products' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_products_wt' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_shipping' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_shipping_tax_incl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_shipping_tax_excl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'carrier_tax_rate' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
			'total_wrapping' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_wrapping_tax_incl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_wrapping_tax_excl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'shipping_number' => 			array('type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'),
			'conversion_rate' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
			'invoice_number' => 			array('type' => self::TYPE_INT),
			'delivery_number' => 			array('type' => self::TYPE_INT),
			'invoice_date' => 				array('type' => self::TYPE_DATE),
			'delivery_date' => 				array('type' => self::TYPE_DATE),
			'valid' => 						array('type' => self::TYPE_BOOL),
			'reference' => 					array('type' => self::TYPE_STRING),
			'date_add' => 					array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 					array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_delivery' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
			'hour_delivery' => 				array('type' => self::TYPE_STRING),
            'hour_delivery_2' => 			array('type' => self::TYPE_STRING),
            'hour_delivery_3' => 			array('type' => self::TYPE_STRING),
			'custom_relay' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order_to_adjust' => 	    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'free_shipping_discount'=>      array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'message' => 				    array('type' => self::TYPE_STRING, 'validate' => 'isMessage'),
		),
	);

    public function setAdjustmentDeliveryInfo($id_order_to_adjust){
        $order_to_adjust = new Order($id_order_to_adjust);
        $this->hour_delivery = $order_to_adjust->hour_delivery;
        $this->id_address_delivery = $order_to_adjust->hour_delivery;
        $this->custom_relay = $order_to_adjust->custom_relay;
        $this->id_carrier = $order_to_adjust->id_carrier;
    }
	/**
	 * Return an object of relays
	 */
	public static function getRelays()
	{
		$relays = (object) array();
        $sql = 'SELECT c.id_carrier, name, description, a.id_address, a.address1, a.address2, a.other as mention, a.postcode, a.city, lat, lon, a.phone
                FROM '._DB_PREFIX_.'carrier c
				LEFT join '._DB_PREFIX_.'carrier_lang l on c.id_carrier = l.id_carrier
				LEFT join '._DB_PREFIX_.'address a on c.id_carrier = a.id_carrier_relay
				WHERE c.`type_carrier` = 2
                AND c.`active` = 1
                AND c.`deleted` = 0
                AND a.`active` = 1
				AND a.`deleted` = 0
				ORDER BY name ASC';
		$relays = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		foreach ($relays as &$relay) {
			//$relay['address'] = explode(',', $relay['address']);
            $address1 = $relay['address1'];
            $address2 = $relay['address2'];
            $relay['address'][0] = $address1;
            $relay['address'][1] = $address2;

		}
		return $relays;
	}

    //========================================================================================
    //Get first possible Order date
    //===============
    //@zone
    //  The delivery address Zone
    //@return
    //  first possible order date according to the zone
    //@description:
    //  The first day you can command depend on today day and hour, and delivery zone (UPS or NOT)
    //  $ups_first_dates_correspondances for ups zones
    //  $none_ups_ups_first_dates_correspondances for none ups zone (ecolo and Jet)
    //@arrays
    //  array("TodayDate" => ["none UPS first possible date", "UPS first possible date"]);
    static function getFirstAvailableOrderDate($id_zone){

        $zone = new Zone($id_zone);
        if (!$zone->id)
            return 0;

        //==============================
        //Init Day correspondance Array
        //==============================
        $none_ups_days = array(
            "Monday"    => "Wednesday",
            "Tuesday"   => "Thursday",
            "Wednesday" => "Friday",
            "Thursday"  => "Tuesday",
            "Friday"    => "Tuesday",
            "Saturday"  => "Wednesday",
            "Sunday"    => "Wednesday",
        );
        $ups_days = array(
            "Monday"    => "Thursday",
            "Tuesday"   => "Friday",
            "Wednesday" => "Wednesday",
            "Thursday"  => "Wednesday",
            "Friday"    => "Wednesday",
            "Saturday"  => "Thursday",
            "Sunday"    => "Thursday",
        );
        //==============================
        //Init vars

        $now = time();
        //$now = strtotime('9 Feb 2015') + 3600*15;

        //Trying to order after close hour equal ordering next day
        if (date('H', $now) >= $zone->h_auto_close){
            $now = strtotime('next day', $now);
        }

        //get day of week (in english)
        $day = date('l', $now);

        //UPS
        if ($zone->id == ID_ZONE_UPS)
            $nextAvailableDay = $ups_days[$day];
        //None UPS
        else
            $nextAvailableDay = $none_ups_days[$day];
        //return next "avalaible day" date
        return strtotime('next '.$nextAvailableDay, $now);
    }

	public static function getOrderGiftProductsTotalPrice($order_id, $price_wt = true){
		$order = new Order($order_id);
		if (!$order->id) //if order not found
			return 0;

		$total = 0;

		$rules = $order->getCartRules();
		foreach ($rules as $rule){
			if ($rule['gift_product_attribute'] > 0) { //if rule has a gift product
				$id_product = $rule['gift_product'];
				$id_product_attribute = $rule['gift_product_attribute'];

				$total += Product::getPriceStatic($id_product, $price_wt, $id_product_attribute);
			}
		}

		return $total;
	}

}

