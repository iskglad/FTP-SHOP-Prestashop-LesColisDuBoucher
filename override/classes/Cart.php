<?php

class Cart extends CartCore
{
	public $date_delivery;
	public $hour_delivery;
	public $custom_relay;
    public $adjustment_min_price_to_get_free_shipping_discount;
    public $id_order_to_adjust;
    public $message;

    /**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'cart',
		'primary' => 'id_cart',
		'fields' => array(
			'id_shop_group' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_shop' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_address_delivery' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_address_invoice' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_carrier' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_currency' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_customer' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_guest' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_lang' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'recyclable' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'gift' => 					array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'gift_message' => 			array('type' => self::TYPE_STRING, 'validate' => 'isMessage'),
			'delivery_option' => 		array('type' => self::TYPE_STRING),
			'secure_key' => 			array('type' => self::TYPE_STRING, 'size' => 32),
			'allow_seperated_package' =>array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'date_add' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
			'date_upd' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
			'date_delivery' => 			array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
			'hour_delivery' => 			array('type' => self::TYPE_STRING),
			'custom_relay' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order_to_adjust'=>      array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'adjustment_min_price_to_get_free_shipping_discount'=>  array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'message' => 			    array('type' => self::TYPE_STRING, 'validate' => 'isMessage'),
        ),
	);

    /**
     * Return useful informations for cart
     *
     * @return array Cart details
     */
    public function getSummaryDetails($id_lang = null, $refresh = false)
    {
        $context = Context::getContext();
        if (!$id_lang)
            $id_lang = $context->language->id;

        $delivery = new Address((int)$this->id_address_delivery);
        $invoice = new Address((int)$this->id_address_invoice);

        // New layout system with personalization fields
        $formatted_addresses['invoice'] = AddressFormat::getFormattedLayoutData($invoice);
        $formatted_addresses['delivery'] = AddressFormat::getFormattedLayoutData($delivery);

        $total_tax = $this->getOrderTotal() - $this->getOrderTotal(false);

        if ($total_tax < 0)
            $total_tax = 0;

        $currency = new Currency($this->id_currency);

        $products = $this->getProducts($refresh);
        $gift_products = array();
        $cart_rules = $this->getCartRules();
        $total_shipping = $this->getTotalShippingCost();
        $total_shipping_tax_exc = $this->getTotalShippingCost(null, false);
        $total_products_wt = $this->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $total_products = $this->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        $total_discounts = $this->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        $total_discounts_tax_exc = $this->getOrderTotal(false, Cart::ONLY_DISCOUNTS);

        // The cart content is altered for display
        foreach ($cart_rules as &$cart_rule)
        {
            // If the cart rule is automatic (wihtout any code) and include free shipping, it should not be displayed as a cart rule but only set the shipping cost to 0
            if ($cart_rule['free_shipping'])
            {
                $cart_rule['value_real'] -= $total_shipping;
                $cart_rule['value_tax_exc'] = $total_shipping_tax_exc;

                // Update total shipping
                $total_shipping = 0;
                $total_shipping_tax_exc = 0;
            }
            if ($cart_rule['gift_product'])
            {
                foreach ($products as $key => &$product)
                    if (empty($product['gift']) && $product['id_product'] == $cart_rule['gift_product'] && $product['id_product_attribute'] == $cart_rule['gift_product_attribute'])
                    {
                        // Update total products
                        $total_products_wt = Tools::ps_round($total_products_wt - $product['price_wt'], (int)$context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $total_products = Tools::ps_round($total_products - $product['price'], (int)$context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);

                        // Update total discounts
                        $total_discounts = Tools::ps_round($total_discounts - $product['price_wt'], (int)$context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $total_discounts_tax_exc = Tools::ps_round($total_discounts_tax_exc - $product['price'], (int)$context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);

                        // Update cart rule value
                        $cart_rule['value_real'] = Tools::ps_round($cart_rule['value_real'] - $product['price_wt'], (int)$context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $cart_rule['value_tax_exc'] = Tools::ps_round($cart_rule['value_tax_exc'] - $product['price'], (int)$context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);

                        // Update product quantity
                        $product['total_wt'] = Tools::ps_round($product['total_wt'] - $product['price_wt'], (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $product['total'] = Tools::ps_round($product['total'] - $product['price'], (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $product['cart_quantity']--;

                        if (!$product['cart_quantity'])
                            unset($products[$key]);

                        // Add a new product line
                        $gift_product = $product;
                        $gift_product['cart_quantity'] = 1;
                        $gift_product['price'] = 0;
                        $gift_product['price_wt'] = 0;
                        $gift_product['total_wt'] = 0;
                        $gift_product['total'] = 0;
                        $gift_product['gift'] = true;
                        $gift_products[] = $gift_product;

                        break; // One gift product per cart rule
                    }
            }
        }


        foreach ($cart_rules as $key => &$cart_rule)
            if ($cart_rule['value_real'] == 0)
                unset($cart_rules[$key]);


        return array(
            'delivery' => $delivery,
            'delivery_state' => State::getNameById($delivery->id_state),
            'invoice' => $invoice,
            'invoice_state' => State::getNameById($invoice->id_state),
            'formattedAddresses' => $formatted_addresses,
            'products' => array_values($products),
            'gift_products' => $gift_products,
            'discounts' => $cart_rules,
            'is_virtual_cart' => (int)$this->isVirtualCart(),
            'total_discounts' => $total_discounts,
            'total_discounts_tax_exc' => $total_discounts_tax_exc,
            'total_wrapping' => $this->getOrderTotal(true, Cart::ONLY_WRAPPING),
            'total_wrapping_tax_exc' => $this->getOrderTotal(false, Cart::ONLY_WRAPPING),
            'total_shipping' => $total_shipping,
            'total_shipping_tax_exc' => $total_shipping_tax_exc,
            'total_products_wt' => $total_products_wt,
            'total_products' => $total_products,
            'total_price' => $this->getOrderTotal(),
            'total_tax' => $total_tax,
            'total_price_without_tax' => $this->getOrderTotal(false),
            'is_multi_address_delivery' => $this->isMultiAddressDelivery() || ((int)Tools::getValue('multi-shipping') == 1),
            'free_ship' => $total_shipping ? 0 : 1,
            'carrier' => new Carrier($this->id_carrier, $id_lang),
        );
    }

    /**
     * Update product quantity
     *
     * @param integer $quantity Quantity to add (or substract)
     * @param integer $id_product Product ID
     * @param integer $id_product_attribute Attribute ID if needed
     * @param string $operator Indicate if quantity must be increased or decreased
     */
    public function updateQty($quantity, $id_product, $id_product_attribute = null, $id_customization = false,
                              $operator = 'up', $id_address_delivery = 0, Shop $shop = null, $auto_add_cart_rule = true)
    {
        if (!$shop)
            $shop = Context::getContext()->shop;

        if (Context::getContext()->customer->id)
        {
            if ($id_address_delivery == 0 && (int)$this->id_address_delivery) // The $id_address_delivery is null, use the cart delivery address
                $id_address_delivery = $this->id_address_delivery;
            elseif ($id_address_delivery == 0) // The $id_address_delivery is null, get the default customer address
                $id_address_delivery = (int)Address::getFirstCustomerAddressId((int)Context::getContext()->customer->id);
            elseif (!Customer::customerHasAddress(Context::getContext()->customer->id, $id_address_delivery)) // The $id_address_delivery must be linked with customer
                $id_address_delivery = 0;
        }

        $quantity = (int)$quantity;
        $id_product = (int)$id_product;
        $id_product_attribute = (int)$id_product_attribute;
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);

        if ($id_product_attribute)
        {
            $combination = new Combination((int)$id_product_attribute);
            if ($combination->id_product != $id_product)
                return false;
        }

        /* If we have a product combination, the minimal quantity is set with the one of this combination */
        if (!empty($id_product_attribute))
            $minimal_quantity = (int)Attribute::getAttributeMinimalQty($id_product_attribute);
        else
            $minimal_quantity = (int)$product->minimal_quantity;

        if (!Validate::isLoadedObject($product))
            die(Tools::displayError());

        if (isset(self::$_nbProducts[$this->id]))
            unset(self::$_nbProducts[$this->id]);

        if (isset(self::$_totalWeight[$this->id]))
            unset(self::$_totalWeight[$this->id]);

        if ((int)$quantity <= 0)
            return $this->deleteProduct($id_product, $id_product_attribute, (int)$id_customization);
        elseif (!$product->available_for_order || Configuration::get('PS_CATALOG_MODE'))
            return false;
        else
        {
            /* Check if the product is already in the cart */
            $result = $this->containsProduct($id_product, $id_product_attribute, (int)$id_customization, (int)$id_address_delivery);

            /* Update quantity if product already exist */
            if ($result)
            {
                if ($operator == 'up')
                {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
							FROM '._DB_PREFIX_.'product p
							'.Product::sqlStock('p', $id_product_attribute, true, $shop).'
							WHERE p.id_product = '.$id_product;

                    $result2 = Db::getInstance()->getRow($sql);
                    $product_qty = (int)$result2['quantity'];
                    // Quantity for product pack
                    if (Pack::isPack($id_product))
                        $product_qty = Pack::getQuantity($id_product, $id_product_attribute);
                    $new_qty = (int)$result['quantity'] + (int)$quantity;
                    $qty = '+ '.(int)$quantity;

                    //==============================
                    //Custom code
                    //==============================
                    //Managing quantity for attributes
                    if ($id_product_attribute){
                        $product_qty = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
                        if ($new_qty > (int)$product_qty){
                            return false;
                        }
                    }

                    if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock']))
                        if ($new_qty > (int)$product_qty){
                            return false;
                        }
                }
                else if ($operator == 'down')
                {
                    $qty = '- '.(int)$quantity;
                    $new_qty = (int)$result['quantity'] - (int)$quantity;
                    if ($new_qty < $minimal_quantity && $minimal_quantity > 1)
                        return -1;
                }
                else
                    return false;

                /* Delete product from cart */
                if ($new_qty <= 0)
                    return $this->deleteProduct((int)$id_product, (int)$id_product_attribute, (int)$id_customization);
                else if ($new_qty < $minimal_quantity)
                    return -1;
                else
                    Db::getInstance()->execute('
						UPDATE `'._DB_PREFIX_.'cart_product`
						SET `quantity` = `quantity` '.$qty.', `date_add` = NOW()
						WHERE `id_product` = '.(int)$id_product.
                        (!empty($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
						AND `id_cart` = '.(int)$this->id.(Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ? ' AND `id_address_delivery` = '.(int)$id_address_delivery : '').'
						LIMIT 1'
                    );
            }
            /* Add product to the cart */
            elseif ($operator == 'up')
            {
                $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
						FROM '._DB_PREFIX_.'product p
						'.Product::sqlStock('p', $id_product_attribute, true, $shop).'
						WHERE p.id_product = '.$id_product;

                $result2 = Db::getInstance()->getRow($sql);

                // Quantity for product pack
                if (Pack::isPack($id_product))
                    $result2['quantity'] = Pack::getQuantity($id_product, $id_product_attribute);

                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock']))
                    if ((int)$quantity > $result2['quantity'])
                        return false;

                if ((int)$quantity < $minimal_quantity)
                    return -1;

                $result_add = Db::getInstance()->insert('cart_product', array(
                    'id_product' => 			(int)$id_product,
                    'id_product_attribute' => 	(int)$id_product_attribute,
                    'id_cart' => 				(int)$this->id,
                    'id_address_delivery' => 	(int)$id_address_delivery,
                    'id_shop' => 				$shop->id,
                    'quantity' => 				(int)$quantity,
                    'date_add' => 				date('Y-m-d H:i:s')
                ));

                if (!$result_add)
                    return false;
            }
        }

        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update(true);
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        if ($auto_add_cart_rule)
            CartRule::autoAddToCart($context);

        if ($product->customizable)
            return $this->_updateCustomizationQuantity((int)$quantity, (int)$id_customization, (int)$id_product, (int)$id_product_attribute, (int)$id_address_delivery, $operator);
        else
            return true;
    }

    //=================================================================
    //CUSTOM FREE SHIPPING RULES
    //=================================================================
    /*
     * Add Custom Free Shipping rule
        -Used when the total products price reach the zone free_shipping price
    */
    public function addCustomFreeShippingRule(){
        //free shipping code (unique pattern: Prefix+ID)
        $rule_code = PREFIX_CART_RULE_FREE_SHIPPING.(int)$this->id;
        $rule_name = RULE_NAME_FREE_SHIPPING;
        //get id of current cart freeShipping rule
        $id_cart_rule = CartRule::getIdByCode($rule_code);

        //if doesnt exist already
        if (!$id_cart_rule)
        {
            //Create a new free_shipping rule add it
                //init
            $cart_rule = new CartRule();

                //set values
            $cart_rule->code = $rule_code;
            $cart_rule->name = $rule_name;
            $cart_rule->id_customer = (int)$this->id_customer;
            $cart_rule->quantity = 1;
            $cart_rule->quantity_per_user = 1;
            $cart_rule->priority = 5;
            $cart_rule->free_shipping = true;
            $cart_rule->minimum_amount_currency = (int)$this->id_currency;
            $cart_rule->reduction_currency = (int)$this->id_currency;
            $cart_rule->date_from = date('Y-m-d H:i:s', time());
            $cart_rule->date_to = date('Y-m-d H:i:s', time() + 1800);
            $cart_rule->active = 1;

                //add to DB
            $cart_rule->add();
                //add to Cart
            $this->addCartRule((int)$cart_rule->id);
        }
    }

    /*
    * Remove Custom Free Shipping rule
        -Used when the total products price doesnt reach the zone free_shipping price
        -It delete the freeShipping if exist
   */
    public function deleteCustomFreeShippingRule(){
        //Free shipping code (unique pattern: Prefix+ID)
        $rule_code = PREFIX_CART_RULE_FREE_SHIPPING.(int)$this->id;
        //Get id of current cart freeShipping rule
        $id_cart_rule = CartRule::getIdByCode($rule_code);

        //if exist
        if ($id_cart_rule){
            //get CartRule
            $cart_rule = new CartRule($id_cart_rule);

            //remove from cart
            $this->removeCartRule((int)$cart_rule->id);

            //Delete from db
            $cart_rule->delete();
        }
    }

    //=================================================================
    //CUSTOM SHIPPING REFUND
    //=================================================================
    /*
   * Add Custom Free Shipping rule
      -Used when the total products price reach the zone free_shipping price
  */
    public function addCustomShippingRefund($amount){
        //discount shipping code (unique pattern: Prefix+ID, see config/settings.inc.php)
        $rule_code = PREFIX_CART_REFUND_SHIPPING.(int)$this->id;
        $rule_name = RULE_NAME_REFUND_SHIPPING;
        //get id of current cart DiscountShipping rule
        $id_cart_rule = CartRule::getIdByCode($rule_code);

        //if doesnt exist already
        if (!$id_cart_rule)
        {
            //Create a new free_shipping rule add it
            //init
            $cart_rule = new CartRule();

            //set values
            $cart_rule->code = $rule_code;
            $cart_rule->name = $rule_name;
            $cart_rule->id_customer = (int)$this->id_customer;
            $cart_rule->quantity = 1;
            $cart_rule->quantity_per_user = 1;
            $cart_rule->priority = 5;
            $cart_rule->reduction_currency = (int)$this->id_currency;
            $cart_rule->reduction_tax = true;
            $cart_rule->reduction_amount = $amount;
            $cart_rule->date_from = date('Y-m-d H:i:s', time());
            $cart_rule->date_to = date('Y-m-d H:i:s', time() + 1800);
            $cart_rule->active = 1;

            //add to DB
            $cart_rule->add();

            //add to Cart
            $this->addCartRule((int)$cart_rule->id);
        }
        else{
            //Update amount
            $cart_rule = new CartRule($id_cart_rule);
            $cart_rule->reduction_amount = $amount;
            $cart_rule->date_to = date('Y-m-d H:i:s', time() + 1800);

            $cart_rule->update();
        }
    }

    /*
       * Remove Custom Shipping Discount rule
           -Used when the Adjustment total products price doesnt allows a refunding of the previous order shipping
           -It delete the ShippingRefund if exist
      */
    public function deleteCustomShippingRefund(){
        //Refund code (unique pattern: Prefix+ID)
        $rule_code = PREFIX_CART_REFUND_SHIPPING.(int)$this->id;
        //Get id of current cart freeShipping rule
        $id_cart_rule = CartRule::getIdByCode($rule_code);

        //if exist
        if ($id_cart_rule){
            //get CartRule
            $cart_rule = new CartRule($id_cart_rule);

            //remove from cart
            $this->removeCartRule((int)$cart_rule->id);

            //Delete from db
            $cart_rule->delete();
        }
    }

    //=================================================================
    //GETTERS
    //=================================================================

	public function getPackageList($flush = false)
	{
		static $cache = false;
		if ($cache !== false && !$flush)
			return $cache;

		$product_list = $this->getProducts();
		// Step 1 : Get product informations (warehouse_list and carrier_list), count warehouse
		// Determine the best warehouse to determine the packages
		// For that we count the number of time we can use a warehouse for a specific delivery address
		$warehouse_count_by_address = array();
		$warehouse_carrier_list = array();

		$stock_management_active = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');

		foreach ($product_list as &$product)
		{
			if ((int)$product['id_address_delivery'] == 0)
				$product['id_address_delivery'] = (int)$this->id_address_delivery;

			if (Tools::getValue('id_address_delivery')) {
				$product['id_address_delivery'] = Tools::getValue('id_address_delivery');
			}

			if (!isset($warehouse_count_by_address[$product['id_address_delivery']]))
				$warehouse_count_by_address[$product['id_address_delivery']] = array();

			$product['warehouse_list'] = array();

			if ($stock_management_active &&
				((int)$product['advanced_stock_management'] == 1 || Pack::usesAdvancedStockManagement((int)$product['id_product'])))
			{
				$warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute'], $this->id_shop);
				if (count($warehouse_list) == 0)
					$warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute']);
				// Does the product is in stock ?
				// If yes, get only warehouse where the product is in stock

				$warehouse_in_stock = array();
				$manager = StockManagerFactory::getManager();

				foreach ($warehouse_list as $key => $warehouse)
				{
					$product_real_quantities = $manager->getProductRealQuantities(
						$product['id_product'],
						$product['id_product_attribute'],
						array($warehouse['id_warehouse']),
						true
					);

					if ($product_real_quantities > 0 || Pack::isPack((int)$product['id_product']))
						$warehouse_in_stock[] = $warehouse;
				}

				if (!empty($warehouse_in_stock))
				{
					$warehouse_list = $warehouse_in_stock;
					$product['in_stock'] = true;
				}
				else
					$product['in_stock'] = false;
			}
			else
			{
				//simulate default warehouse
				$warehouse_list = array(0);
				$product['in_stock'] = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']) > 0;
			}

			foreach ($warehouse_list as $warehouse)
			{
				if (!isset($warehouse_carrier_list[$warehouse['id_warehouse']]))
				{
					$warehouse_object = new Warehouse($warehouse['id_warehouse']);
					$warehouse_carrier_list[$warehouse['id_warehouse']] = $warehouse_object->getCarriers();
				}

				$product['warehouse_list'][] = $warehouse['id_warehouse'];
				if (!isset($warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']]))
					$warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']] = 0;

				$warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']]++;
			}
		}

		arsort($warehouse_count_by_address);

		// Step 2 : Group product by warehouse
		$grouped_by_warehouse = array();
		foreach ($product_list as &$product)
		{
			if (!isset($grouped_by_warehouse[$product['id_address_delivery']]))
				$grouped_by_warehouse[$product['id_address_delivery']] = array(
					'in_stock' => array(),
					'out_of_stock' => array(),
				);
			
			$product['carrier_list'] = array();
			$id_warehouse = 0;
			foreach ($warehouse_count_by_address[$product['id_address_delivery']] as $id_war => $val)
			{
				$product['carrier_list'] = array_merge($product['carrier_list'], Carrier::getAvailableCarrierList(new Product($product['id_product']), $id_war, $product['id_address_delivery'], null, $this));
				if (in_array((int)$id_war, $product['warehouse_list']) && $id_warehouse == 0)
					$id_warehouse = $id_war;
			}

			if (!isset($grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse]))
			{
				$grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse] = array();
				$grouped_by_warehouse[$product['id_address_delivery']]['out_of_stock'][$id_warehouse] = array();
			}

			if (!$this->allow_seperated_package)
				$key = 'in_stock';
			else
				$key = (!$product['out_of_stock']) ? 'in_stock' : 'out_of_stock';

			if (empty($product['carrier_list']))
				$product['carrier_list'] = array(0);

			$grouped_by_warehouse[$product['id_address_delivery']][$key][$id_warehouse][] = $product;
		}

		// Step 3 : grouped product from grouped_by_warehouse by available carriers
		$grouped_by_carriers = array();
		foreach ($grouped_by_warehouse as $id_address_delivery => $products_in_stock_list)
		{
			if (!isset($grouped_by_carriers[$id_address_delivery]))
				$grouped_by_carriers[$id_address_delivery] = array(
					'in_stock' => array(),
					'out_of_stock' => array(),
				);
			foreach ($products_in_stock_list as $key => $warehouse_list)
			{
				if (!isset($grouped_by_carriers[$id_address_delivery][$key]))
					$grouped_by_carriers[$id_address_delivery][$key] = array();
				foreach ($warehouse_list as $id_warehouse => $product_list)
				{
					if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse]))
						$grouped_by_carriers[$id_address_delivery][$key][$id_warehouse] = array();

					foreach ($product_list as $product)
					{
						$package_carriers_key = implode(',', $product['carrier_list']);

						if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key]))
							$grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key] = array(
								'product_list' => array(),
								'carrier_list' => $product['carrier_list'],
								'warehouse_list' => $product['warehouse_list']
							);

						$grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key]['product_list'][] = $product;
					}
				}
			}
		}

		$package_list = array();
		// Step 4 : merge product from grouped_by_carriers into $package to minimize the number of package
		foreach ($grouped_by_carriers as $id_address_delivery => $products_in_stock_list)
		{
			if (!isset($package_list[$id_address_delivery]))
				$package_list[$id_address_delivery] = array(
					'in_stock' => array(),
					'out_of_stock' => array(),
				);

			foreach ($products_in_stock_list as $key => $warehouse_list)
			{
				if (!isset($package_list[$id_address_delivery][$key]))
					$package_list[$id_address_delivery][$key] = array();
				// Count occurance of each carriers to minimize the number of packages
				$carrier_count = array();
				foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers)
				{
					foreach ($products_grouped_by_carriers as $data)
					{
						foreach ($data['carrier_list'] as $id_carrier)
						{
							if (!isset($carrier_count[$id_carrier]))
								$carrier_count[$id_carrier] = 0;
							$carrier_count[$id_carrier]++;
						}
					}
				}
				arsort($carrier_count);
				foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers)
				{
					if (!isset($package_list[$id_address_delivery][$key][$id_warehouse]))
						$package_list[$id_address_delivery][$key][$id_warehouse] = array();
					foreach ($products_grouped_by_carriers as $data)
					{
						foreach ($carrier_count as $id_carrier => $rate)
						{
							if (in_array($id_carrier, $data['carrier_list']))
							{
								if (!isset($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]))
									$package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier] = array(
										'carrier_list' => $data['carrier_list'],
										'warehouse_list' => $data['warehouse_list'],
										'product_list' => array(),
									);
								$package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'] =
									array_intersect($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'], $data['carrier_list']);

								$package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'] =
									array_merge($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'], $data['product_list']);

								break;
							}
						}
					}
				}
			}
		}

		// Step 5 : Reduce depth of $package_list
		$final_package_list = array();
		foreach ($package_list as $id_address_delivery => $products_in_stock_list)
		{
			if (!isset($final_package_list[$id_address_delivery]))
				$final_package_list[$id_address_delivery] = array();

			foreach ($products_in_stock_list as $key => $warehouse_list)
				foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers)
					foreach ($products_grouped_by_carriers as $data)
					{
						$final_package_list[$id_address_delivery][] = array(
							'product_list' => $data['product_list'],
							'carrier_list' => $data['carrier_list'],
							'warehouse_list' => $data['warehouse_list'],
							'id_warehouse' => $id_warehouse,
						);
					}
		}
		$cache = $final_package_list;
		return $final_package_list;
	}






	/**
	 * Return package shipping cost
	 *
	 * @param integer $id_carrier Carrier ID (default : current carrier)
	 * @param booleal $use_tax
	 * @param Country $default_country
	 * @param Array $product_list
	 * @param array $product_list List of product concerned by the shipping. If null, all the product of the cart are used to calculate the shipping cost
	 *
	 * @return float Shipping total
	 */
	public function getPackageShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null)
	{
		if ($this->isVirtualCart())
			return 0;

		if (!$default_country)
			$default_country = Context::getContext()->country;

		$complete_product_list = $this->getProducts();
		if (is_null($product_list))
			$products = $complete_product_list;
		else
			$products = $product_list;

		if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice')
			$address_id = (int)$this->id_address_invoice;
		elseif (count($product_list))
		{
			$prod = current($product_list);
			$address_id = (int)$prod['id_address_delivery'];
		}
		else
			$address_id = null;
		if (!Address::addressExists($address_id))
			$address_id = null;

		$cache_id = 'getPackageShippingCost_'.(int)$this->id.'_'.(int)$address_id.'_'.(int)$id_carrier.'_'.(int)$use_tax.'_'.(int)$default_country->id;
		if ($products)
			foreach ($products as $product)
				$cache_id .= '_'.(int)$product['id_product'].'_'.(int)$product['id_product_attribute'];

		if (Cache::isStored($cache_id))
			return Cache::retrieve($cache_id);

		// Order total in default currency without fees
		$order_total = $this->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING, $product_list);

		// Start with shipping cost at 0
		$shipping_cost = 0;
		// If no product added, return 0
		if (!count($products))
		{
			Cache::store($cache_id, $shipping_cost);
			return $shipping_cost;
		}
		// Get id zone
		if (!$this->isMultiAddressDelivery()
			&& isset($this->id_address_delivery) // Be carefull, id_address_delivery is not usefull one 1.5
			&& $this->id_address_delivery
			&& Customer::customerHasAddress($this->id_customer, $this->id_address_delivery
		)){
			$id_zone = Address::getZoneById((int)$this->id_address_delivery);
			if (Tools::getValue('id_address_delivery')) { // custom
				$id_zone = Address::getZoneById((int)Tools::getValue('id_address_delivery'));
			}

	}
		else
		{
			if (!Validate::isLoadedObject($default_country))
				$default_country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));

			$id_zone = (int)$default_country->id_zone;
		}

		if ($id_carrier && !$this->isCarrierInRange((int)$id_carrier, (int)$id_zone))
			$id_carrier = '';

		if (empty($id_carrier) && $this->isCarrierInRange((int)Configuration::get('PS_CARRIER_DEFAULT'), (int)$id_zone))
			$id_carrier = (int)Configuration::get('PS_CARRIER_DEFAULT');

		if (empty($id_carrier))
		{
			if ((int)$this->id_customer)
			{
				$customer = new Customer((int)$this->id_customer);
				$result = Carrier::getCarriers((int)Configuration::get('PS_LANG_DEFAULT'), true, false, (int)$id_zone, $customer->getGroups());
				unset($customer);
			}
			else
				$result = Carrier::getCarriers((int)Configuration::get('PS_LANG_DEFAULT'), true, false, (int)$id_zone);

			foreach ($result as $k => $row)
			{
				if ($row['id_carrier'] == Configuration::get('PS_CARRIER_DEFAULT'))
					continue;

				if (!isset(self::$_carriers[$row['id_carrier']]))
					self::$_carriers[$row['id_carrier']] = new Carrier((int)$row['id_carrier']);

				$carrier = self::$_carriers[$row['id_carrier']];

				// Get only carriers that are compliant with shipping method
				if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight((int)$id_zone) === false)
				|| ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice((int)$id_zone) === false))
				{
					unset($result[$k]);
					continue;
				}

				// If out-of-range behavior carrier is set on "Desactivate carrier"
				if ($row['range_behavior'])
				{
					$check_delivery_price_by_weight = Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $this->getTotalWeight(), (int)$id_zone);

					$total_order = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, $product_list);
					$check_delivery_price_by_price = Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $total_order, (int)$id_zone, (int)$this->id_currency);

					// Get only carriers that have a range compatible with cart
					if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT && !$check_delivery_price_by_weight)
					|| ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE && !$check_delivery_price_by_price))
					{
						unset($result[$k]);
						continue;
					}
				}

				if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
					$shipping = $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), (int)$id_zone);
				else
					$shipping = $carrier->getDeliveryPriceByPrice($order_total, (int)$id_zone, (int)$this->id_currency);

				if (!isset($min_shipping_price))
					$min_shipping_price = $shipping;

				if ($shipping <= $min_shipping_price)
				{
					$id_carrier = (int)$row['id_carrier'];
					$min_shipping_price = $shipping;
				}
			}
		}

		if (empty($id_carrier))
			$id_carrier = Configuration::get('PS_CARRIER_DEFAULT');

		if (!isset(self::$_carriers[$id_carrier]))
			self::$_carriers[$id_carrier] = new Carrier((int)$id_carrier, Configuration::get('PS_LANG_DEFAULT'));

		$carrier = self::$_carriers[$id_carrier];

		if (!Validate::isLoadedObject($carrier))
			die(Tools::displayError('Fatal error: "no default carrier"'));

		if (!$carrier->active)
		{
			Cache::store($cache_id, $shipping_cost);
			return $shipping_cost;
		}

		// Free fees if free carrier
		if ($carrier->is_free == 1)
		{
			Cache::store($cache_id, 0);
			return 0;
		}

		// Select carrier tax
		if ($use_tax && !Tax::excludeTaxeOption())
		{
			$address = Address::initialize((int)$address_id);
			$carrier_tax = $carrier->getTaxesRate($address);
		}

		$configuration = Configuration::getMultiple(array(
			'PS_SHIPPING_FREE_PRICE',
			'PS_SHIPPING_HANDLING',
			'PS_SHIPPING_METHOD',
			'PS_SHIPPING_FREE_WEIGHT'
		));

		// Free fees
		$free_fees_price = 0;
		if (isset($configuration['PS_SHIPPING_FREE_PRICE']))
			$free_fees_price = Tools::convertPrice((float)$configuration['PS_SHIPPING_FREE_PRICE'], Currency::getCurrencyInstance((int)$this->id_currency));
		$orderTotalwithDiscounts = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false);
		if ($orderTotalwithDiscounts >= (float)($free_fees_price) && (float)($free_fees_price) > 0)
		{
			Cache::store($cache_id, $shipping_cost);
			return $shipping_cost;
		}

		if (isset($configuration['PS_SHIPPING_FREE_WEIGHT'])
			&& $this->getTotalWeight() >= (float)$configuration['PS_SHIPPING_FREE_WEIGHT']
			&& (float)$configuration['PS_SHIPPING_FREE_WEIGHT'] > 0)
		{
			Cache::store($cache_id, $shipping_cost);
			return $shipping_cost;
		}

		// Get shipping cost using correct method
		if ($carrier->range_behavior)
		{
			// Get id zone
			if (isset($this->id_address_delivery)
				&& $this->id_address_delivery
				&& Customer::customerHasAddress($this->id_customer, $this->id_address_delivery))
				$id_zone = Address::getZoneById((int)$this->id_address_delivery);
			else
				$id_zone = (int)$default_country->id_zone;

			$check_delivery_price_by_weight = Carrier::checkDeliveryPriceByWeight((int)$carrier->id, $this->getTotalWeight(), (int)$id_zone);

			// Code Review V&V TO FINISH
			$check_delivery_price_by_price = Carrier::checkDeliveryPriceByPrice(
				$carrier->id,
				$this->getOrderTotal(
					true,
					Cart::BOTH_WITHOUT_SHIPPING,
					$product_list
				),
				$id_zone,
				(int)$this->id_currency
			);
			if ((
					$carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT
					&& !$check_delivery_price_by_weight
				) || (
					$carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE
					&& !$check_delivery_price_by_price
			))
				$shipping_cost += 0;
			else
			{
				if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
					$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), $id_zone);
				else // by price
					$shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)$this->id_currency);
			}
		}
		else
		{
			if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
				$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), $id_zone);
			else
				$shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)$this->id_currency);

		}
		// Adding handling charges
		if (isset($configuration['PS_SHIPPING_HANDLING']) && $carrier->shipping_handling)
			$shipping_cost += (float)$configuration['PS_SHIPPING_HANDLING'];

		// Additional Shipping Cost per product
		foreach ($products as $product)
			if (!$product['is_virtual'])
				$shipping_cost += $product['additional_shipping_cost'] * $product['cart_quantity'];

		$shipping_cost = Tools::convertPrice($shipping_cost, Currency::getCurrencyInstance((int)$this->id_currency));
		//get external shipping cost from module
		if ($carrier->shipping_external)
		{
			$module_name = $carrier->external_module_name;
			$module = Module::getInstanceByName($module_name);

			if (Validate::isLoadedObject($module))
			{
				if (array_key_exists('id_carrier', $module))
					$module->id_carrier = $carrier->id;
				if ($carrier->need_range)
					if (method_exists($module, 'getPackageShippingCost'))
						$shipping_cost = $module->getPackageShippingCost($this, $shipping_cost, $products);
					else
						$shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
				else
					$shipping_cost = $module->getOrderShippingCostExternal($this);

				// Check if carrier is available
				if ($shipping_cost === false)
				{
					Cache::store($cache_id, false);
					return false;
				}
			}
			else
			{
				Cache::store($cache_id, false);
				return false;
			}
		}

		// Apply tax
		if ($use_tax && isset($carrier_tax))
			$shipping_cost *= 1 + ($carrier_tax / 100);

		$shipping_cost = (float)Tools::ps_round((float)$shipping_cost, 2);
		Cache::store($cache_id, $shipping_cost);

		return $shipping_cost;
	}


	/**
	 * Return cart products
	 *
	 * @result array Products
	 */
	public function getProducts($refresh = false, $id_product = false, $id_country = null)
	{
		if (!$this->id)
			return array();
		// Product cache must be strictly compared to NULL, or else an empty cart will add dozens of queries
		if ($this->_products !== null && !$refresh)
		{
			// Return product row with specified ID if it exists
			if (is_int($id_product))
			{
				foreach ($this->_products as $product)
					if ($product['id_product'] == $id_product)
						return array($product);
				return array();
			}
			return $this->_products;
		}

		// Build query
		$sql = new DbQuery();

		// Build SELECT
		$sql->select('cp.`id_product_attribute`, cp.`id_product`, cp.`quantity` AS cart_quantity, cp.id_shop, pl.`name`, p.`is_virtual`,
						pl.`description_short`, pl.`available_now`, pl.`available_later`, p.`id_product`, product_shop.`id_category_default`, p.`id_supplier`,
						p.`id_manufacturer`, product_shop.`on_sale`, product_shop.`ecotax`, product_shop.`additional_shipping_cost`, product_shop.`available_for_order`, product_shop.`price`, p.`weight`,
						stock.`quantity` quantity_available, p.`width`, p.`height`, p.`depth`, stock.`out_of_stock`, product_shop.`active`, p.`date_add`,
						p.`date_upd`,p.`date_start`, p.`limit_date`,p.`date_end`, p.`unusual_product`, IFNULL(stock.quantity, 0) as quantity, pl.`link_rewrite`, cl.`link_rewrite` AS category,
						CONCAT(cp.`id_product`, cp.`id_product_attribute`, cp.`id_address_delivery`) AS unique_id, cp.id_address_delivery,
						product_shop.`wholesale_price`, product_shop.advanced_stock_management');

		// Build FROM
		$sql->from('cart_product', 'cp');

		// Build JOIN
		$sql->leftJoin('product', 'p', 'p.`id_product` = cp.`id_product`');
		$sql->innerJoin('product_shop', 'product_shop', '(product_shop.id_shop=cp.id_shop AND product_shop.id_product = p.id_product)');
		$sql->leftJoin('product_lang', 'pl', '
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('pl', 'cp.id_shop')
		);
		

		$sql->leftJoin('category_lang', 'cl', '
			product_shop.`id_category_default` = cl.`id_category`
			AND cl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('cl', 'cp.id_shop')
		);

		// @todo test if everything is ok, then refactorise call of this method
		$sql->join(Product::sqlStock('cp', 'cp'));

		// Build WHERE clauses
		$sql->where('cp.`id_cart` = '.(int)$this->id);
		if ($id_product)
			$sql->where('cp.`id_product` = '.(int)$id_product);
		$sql->where('p.`id_product` IS NOT NULL');

		// Build GROUP BY
		$sql->groupBy('unique_id');

		// Build ORDER BY
		$sql->orderBy('p.id_product, cp.id_product_attribute, cp.date_add ASC');

		if (Customization::isFeatureActive())
		{
			$sql->select('cu.`id_customization`, cu.`quantity` AS customization_quantity');
			$sql->leftJoin('customization', 'cu',
				'p.`id_product` = cu.`id_product` AND cp.`id_product_attribute` = cu.id_product_attribute AND cu.id_cart='.(int)$this->id);
		}
		else
			$sql->select('NULL AS customization_quantity, NULL AS id_customization');

		if (Combination::isFeatureActive())
		{
			$sql->select('
				product_attribute_shop.`price` AS price_attribute, product_attribute_shop.`ecotax` AS ecotax_attr,
				IF (IFNULL(pa.`reference`, \'\') = \'\', p.`reference`, pa.`reference`) AS reference,
				IF (IFNULL(pa.`supplier_reference`, \'\') = \'\', p.`supplier_reference`, pa.`supplier_reference`) AS supplier_reference,
				(p.`weight`+ pa.`weight`) weight_attribute,
				IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`) AS ean13,
				IF (IFNULL(pa.`upc`, \'\') = \'\', p.`upc`, pa.`upc`) AS upc,
				pai.`id_image` as pai_id_image, il.`legend` as pai_legend,
				IFNULL(product_attribute_shop.`minimal_quantity`, product_shop.`minimal_quantity`) as minimal_quantity
			');

			$sql->leftJoin('product_attribute', 'pa', 'pa.`id_product_attribute` = cp.`id_product_attribute`');
			$sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.id_shop=cp.id_shop AND product_attribute_shop.id_product_attribute = pa.id_product_attribute)');
			$sql->leftJoin('product_attribute_image', 'pai', 'pai.`id_product_attribute` = pa.`id_product_attribute`');
			$sql->leftJoin('image_lang', 'il', 'il.id_image = pai.id_image AND il.id_lang = '.(int)$this->id_lang);
		}
		else
			$sql->select(
				'p.`reference` AS reference, p.`supplier_reference` AS supplier_reference, p.`ean13`,
				p.`upc` AS upc, product_shop.`minimal_quantity` AS minimal_quantity'
			);
		$result = Db::getInstance()->executeS($sql);

		// Reset the cache before the following return, or else an empty cart will add dozens of queries
		$products_ids = array();
		$pa_ids = array();
		if ($result)
			foreach ($result as $row)
			{
				$products_ids[] = $row['id_product'];
				$pa_ids[] = $row['id_product_attribute'];
			}
		// Thus you can avoid one query per product, because there will be only one query for all the products of the cart
		Product::cacheProductsFeatures($products_ids);
		Cart::cacheSomeAttributesLists($pa_ids, $this->id_lang);

		$this->_products = array();
		if (empty($result))
			return array();

		$cart_shop_context = Context::getContext()->cloneContext();
		foreach ($result as &$row)
		{
			if (isset($row['ecotax_attr']) && $row['ecotax_attr'] > 0)
				$row['ecotax'] = (float)$row['ecotax_attr'];

			$row['stock_quantity'] = (int)$row['quantity'];
			// for compatibility with 1.2 themes
			$row['quantity'] = (int)$row['cart_quantity'];

			if (isset($row['id_product_attribute']) && (int)$row['id_product_attribute'] && isset($row['weight_attribute']))
				$row['weight'] = (float)$row['weight_attribute'];

			if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice')
				$address_id = (int)$this->id_address_invoice;
			else
				$address_id = (int)$row['id_address_delivery'];
			if (!Address::addressExists($address_id))
				$address_id = null;

			if ($cart_shop_context->shop->id != $row['id_shop'])
				$cart_shop_context->shop = new Shop((int)$row['id_shop']);

			if ($this->_taxCalculationMethod == PS_TAX_EXC)
			{
				$row['price'] = Product::getPriceStatic(
					(int)$row['id_product'],
					false,
					isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
					2,
					null,
					false,
					true,
					(int)$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$specific_price_output,
					true,
					true,
					$cart_shop_context
				); // Here taxes are computed only once the quantity has been applied to the product price

				$row['price_wt'] = Product::getPriceStatic(
					(int)$row['id_product'],
					true,
					isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
					2,
					null,
					false,
					true,
					(int)$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$null,
					true,
					true,
					$cart_shop_context
				);

				$tax_rate = Tax::getProductTaxRate((int)$row['id_product'], (int)$address_id);

				$row['total_wt'] = Tools::ps_round($row['price'] * (float)$row['cart_quantity'] * (1 + (float)$tax_rate / 100), 2);
				$row['total'] = $row['price'] * (int)$row['cart_quantity'];
			}
			else
			{
				$row['price'] = Product::getPriceStatic(
					(int)$row['id_product'],
					false,
					(int)$row['id_product_attribute'],
					2,
					null,
					false,
					true,
					$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$specific_price_output,
					true,
					true,
					$cart_shop_context
				);

				$row['price_wt'] = Product::getPriceStatic(
					(int)$row['id_product'],
					true,
					(int)$row['id_product_attribute'],
					2,
					null,
					false,
					true,
					$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$null,
					true,
					true,
					$cart_shop_context
				);
				
				// In case when you use QuantityDiscount, getPriceStatic() can be return more of 2 decimals
				$row['price_wt'] = Tools::ps_round($row['price_wt'], 2);
				$row['total_wt'] = $row['price_wt'] * (int)$row['cart_quantity'];
				$row['total'] = Tools::ps_round($row['price'] * (int)$row['cart_quantity'], 2);
			}

			if (!isset($row['pai_id_image']) || $row['pai_id_image'] == 0)
			{
				$row2 = Db::getInstance()->getRow('
					SELECT image_shop.`id_image` id_image, il.`legend`
					FROM `'._DB_PREFIX_.'image` i'.
					Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
					LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$this->id_lang.')
					WHERE i.`id_product` = '.(int)$row['id_product'].' AND image_shop.`cover` = 1'
				);

				if (!$row2)
					$row2 = array('id_image' => false, 'legend' => false);
				else
					$row = array_merge($row, $row2);
			}
			else
			{
				$row['id_image'] = $row['pai_id_image'];
				$row['legend'] = $row['pai_legend'];
			}

			$row['reduction_applies'] = ($specific_price_output && (float)$specific_price_output['reduction']);
			$row['quantity_discount_applies'] = ($specific_price_output && $row['cart_quantity'] >= (int)$specific_price_output['from_quantity']);
			$row['id_image'] = Product::defineProductImage($row, $this->id_lang);
			$row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
			$row['features'] = Product::getAdvancedFeaturesStatic((int)$row['id_product'], $this->id_lang);

			if (array_key_exists($row['id_product_attribute'].'-'.$this->id_lang, self::$_attributesLists))
				$row = array_merge($row, self::$_attributesLists[$row['id_product_attribute'].'-'.$this->id_lang]);

			$row = Product::getTaxesInformations($row, $cart_shop_context);

			$this->_products[] = $row;
		}

		return $this->_products;
	}

    //get and set total product price with tax
    public function getTotalProductsPriceWithTax(){

        $total_with_tax = 0;
        $products = $this->getProducts();
        foreach ($products as $product){
            $total_with_tax += $product['total_wt'];

        }
        return $total_with_tax;
    }

    //get order to adjust
    public function getOrderToAdjust(){
        //init vars
        $order_to_adjust = null;
        $id_order_to_adjust = $this->id_order_to_adjust;

        //Get order to adjust (PS: loop to find the order if adjustment of adjustment)
        while ($order_to_adjust == null){
            //get order
            $order_to_adjust = new Order($id_order_to_adjust);
            //if order is adjustment
            if ($order_to_adjust->id_order_to_adjust > 0){
                //make the loop continu
                $id_order_to_adjust = $order_to_adjust->id_order_to_adjust;
                $order_to_adjust = null;
            }
        }
        return $order_to_adjust;
    }


}

