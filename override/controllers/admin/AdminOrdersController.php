<?php

class AdminOrdersController extends AdminOrdersControllerCore
{
	public function __construct()
	{
		
		parent::__construct();

        $this->addRowAction('delete');
        
		$this->_select = '
		a.id_currency,
		a.id_order AS id_pdf,
		(SELECT ad.postcode FROM `'._DB_PREFIX_.'address` ad WHERE ad.id_address = a.id_address_delivery) AS zip,
		CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
		osl.`name` AS `osname`,
		os.`color`,
		cr.name as carrier,
		z.id_zone,
		z.name as zone_name,
		IF((SELECT COUNT(so.id_order) FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer) > 1, 0, 1) as new,
		a.total_products_wt as priceWithoutTax';

        $this->_join .= '
            LEFT JOIN `'._DB_PREFIX_.'carrier` cr ON (a.`id_carrier` = cr.`id_carrier`)
            LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz ON (a.`id_carrier` = cz.`id_carrier`)
            LEFT JOIN `'._DB_PREFIX_.'zone` z ON (cz.`id_zone` = z.`id_zone`)'
        ;
        if(Tools::isSubmit('submitFilterorderbydate') || Tools::isSubmit('exportorderbydate')|| Tools::isSubmit('exportorderbydatetocsv')){
                $dates = Tools::getValue('orderFilter_a!date_delivery');
                $date_delivery = $dates[2];

                /*Where clause
                    zone_jet
                        condition:
                            -same as date_delivery
                OR  zone_Ecolos
                        condition:
                            -same as date_delivery
                            -Not 'point relais'
                            (because 'point relais' is also linked to zone ecoloTrans but is delivered by JET)
                OR  zone_UPS
                        condition:
                            -same as (date_delivery + 1 day)
                */
                $this->_where .= ' AND (
                    (z.`id_zone` = '.ID_ZONE_JET.' AND DATE(a.date_delivery) = DATE("'.$date_delivery.'"))
                    OR (z.`id_zone` = '.ID_ZONE_ECOLOTRANS.' AND DATE(a.date_delivery) = DATE("'.$date_delivery.'") AND cr.name != "livraison en point relais")
                    OR (z.`id_zone` = '.ID_ZONE_UPS.' AND DATE(a.date_delivery) = DATE_ADD("'.$date_delivery.'",INTERVAL 1 DAY))
                )';
        }
            $statuses_array = array();
		$statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status)
            $statuses_array[$status['id_order_state']] = $status['name'];

        //get all zones in DB an make array for "dropdown filter"
        $zones_array = array();
        $zones = Zone::getZones();
        foreach ($zones as $zone)
            $zones_array[$zone['id_zone']] = $zone['name'];

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'align' => 'center',
                'width' => 65
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
            'osname' => array(
                'title' => $this->l('Status'),
                'color' => 'color',
                'width' => 280,
                'type' => 'select',
                'list' => $statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int'
            ),
            'payment' => array(
                'title' => $this->l('Payment'),
                'width' => 100
            ),
            'zone_name' => array(
                'title' => $this->l('Zone'),
                'width' => 80,
                'type' => 'select',
                'list' => $zones_array,
                'filter_key' => 'z!id_zone',
                'filter_type' => 'int'
            ),
            'carrier' => array(
                'title' => $this->l('Transport'),
                'align' => 'center',
                'width' => 80,
                'havingFilter' => true
            ),
            'zip' => array(
                'title' => $this->l('Arrondissement / Ville'),
                'align' => 'center',
                'width' => 50,
                'havingFilter' => true
            ),
            'total_paid_tax_incl' => array(
                'title' => $this->l('Total'),
                'width' => 70,
                'align' => 'right',
                'prefix' => '<b>',
                'suffix' => '</b>',
                'type' => 'price',
                'currency' => true
            ),
            'date_add' => array(
                'title' => $this->l('Date commande'),
                'width' => 150,
                'align' => 'right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            ),
            'date_delivery' => array(
                'title' => $this->l('Date livraison'),
                'width' => 150,
                'align' => 'right',
                'type' => 'date',
                'filter_key' => 'a!date_delivery'
            ),
            'id_pdf' => array(
                'title' => $this->l('PDF'),
                'width' => 50,
                'align' => 'center',
                'callback' => 'printPDFIcons',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true)
        );

	}
	
	public function renderList()
	{
		if (!($this->fields_list && is_array($this->fields_list)))
			return false;

		$this->getList($this->context->language->id);

		// Empty list is ok
		if (!is_array($this->_list))
			return false;

		// export
		if (Tools::isSubmit('csv_orders'))
		{
			if (count($this->_list) > 0)
			{
				$this->renderCSV();
				die;
			}
		}

		$helper = new HelperList();

		$this->setHelperDisplay($helper);
		$helper->tpl_vars = $this->tpl_list_vars;
		$helper->tpl_delete_link_vars = $this->tpl_delete_link_vars;

		// For compatibility reasons, we have to check standard actions in class attributes
		foreach ($this->actions_available as $action)
		{
			if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action)
				$this->actions[] = $action;
		}

		$totalOrders = count($this->_list);
		$totalAmount = 0;
		$totalAmountWithoutTax = 0;

		foreach ($this->_list as $key => $order){
			$totalAmount += $order['total_paid_tax_incl'];
			$totalAmountWithoutTax += $order['priceWithoutTax'];
		}

		$cartAverage = $totalAmountWithoutTax/$totalOrders;

		$this->context->smarty->assign(array(
			"totalOrders" => $totalOrders,
			"totalAmount" => $totalAmount,
			"cartAverage" => $cartAverage
		)); 


		$list = $helper->generateList($this->_list, $this->fields_list);
		
		return $list;
	}

	public function initToolbar()
	{

		parent::initToolbar();

		if (!$this->display)
		{
			$this->toolbar_btn['export'] = array(
				'href' => $this->context->link->getAdminLink('AdminOrders').'&amp;csv_orders',
				'desc' => $this->l('Export products')
			);
		}

	}

	protected function renderCSV()
	{

		// exports details for all orders
		if (Tools::isSubmit('csv_orders'))
		{

   			header('Content-type: text/csv');
	        header('Content-Type: application/force-download; charset=UTF-8');
			header('Cache-Control: no-store, no-cache');
	        header('Content-disposition: attachment; filename="'.$this->l('orders_products').'.csv"');

			$ids = array();
			$list_id_order = "("; 
			foreach ($this->_list as $key => $entry){
				if($key != 0){
					$list_id_order .= ",";
				}
				$ids[] = $entry['id_order'];
				$list_id_order .= $entry['id_order']; 
			}
			$list_id_order .= ")"; 

			if (count($ids) <= 0)
				return;

			$keys = array('id_product', 'reference', 'name', 'quantité totale', 'quantité sélection', 'label_rouge', 'label_bio', 'nombre pers.', "poids (kg)");
			$this->RowCSV($keys);

			$number = "";
			$keys = array('p.id_product', 'p.reference', 'pl.name', 'od.product_quantity');

			$queryBase = '
				SELECT p.`id_product`, p.`reference`, pl.`name`, SUM(od.`product_quantity`) AS quantity
			    FROM `lcdb_product` AS p
			    LEFT JOIN `lcdb_product_lang` AS pl ON pl.`id_product` = p.`id_product`
			    LEFT JOIN `lcdb_order_detail` AS od ON p.`id_product` = od.`product_id`
			    LEFT JOIN `lcdb_orders` AS o ON od.`id_order` = o.`id_order`    
			    WHERE o.`id_order` IN '.$list_id_order.'
			    GROUP BY od.`product_id`';

			$resource = Db::getInstance()->query($queryBase);

			while ($row = Db::getInstance()->nextRow($resource)){

				// quantité selection 
				$query = '
					SELECT SUM(od.`product_quantity`), al.`name`
					FROM `lcdb_order_detail` AS od
					LEFT JOIN `lcdb_orders` AS o ON od.`id_order` = o.`id_order`
					LEFT JOIN `lcdb_product_attribute_combination` AS pac ON pac.`id_product_attribute` = od.`product_attribute_id`
					LEFT JOIN `lcdb_attribute_lang` AS al ON al.`id_attribute` = pac.`id_attribute`    
					WHERE o.`id_order` IN '.$list_id_order.'
					AND od.`product_id` = '.$row["id_product"].'
					GROUP BY al.`name`';

				$quantity_selection = Db::getInstance()->executeS($query);
				$row['quantity_selection'] = "";
				foreach ($quantity_selection as $key => $item) {
					if($item['name'] != ""){
						$row['quantity_selection'] .=  $item['SUM(od.`product_quantity`)'] . " => ". $item['name'] . ' / ';
					}
				}

				// features
				$features = array(ID_FEATURE_LABEL_ROUGE, ID_FEATURE_LABEL_BIO, ID_FEATURE_NUMBER_OF, ID_FEATURE_WEIGHT);
				foreach ($features as $key) {
					$query = '
						SELECT fvl.`value`
						FROM `lcdb_product` AS p
						LEFT JOIN `lcdb_feature_product` AS fp ON fp.`id_product` = p.`id_product`
						LEFT JOIN `lcdb_feature_value_lang` AS fvl ON fvl.`id_feature_value` = fp.`id_feature_value`
						WHERE fp.`id_feature` = '.$key.' AND p.`id_product` = '.$row["id_product"];

					$feature = Db::getInstance()->executeS($query);
					$row['feature_'.$key] = $feature[0]['value'];
				}

				$this->RowCSV($row);
			}

		}

	}

	public function RowCSV($content)
	{
		$wraped_data = array_map(array('CSVCore', 'wrap'), $content);
		$new_content = utf8_encode(implode(";", $wraped_data));
        echo sprintf("%s\n", $new_content);
    }

	public function postProcess()
	{
		// If id_order is sent, we instanciate a new Order object
		if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0)
		{
			$order = new Order(Tools::getValue('id_order'));
			if (!Validate::isLoadedObject($order))
				throw new PrestaShopException('Can\'t load Order object');
		}

		/* Update shipping number */
		if (Tools::isSubmit('submitShippingNumber') && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$order_carrier = new OrderCarrier(Tools::getValue('id_order_carrier'));
				if (!Validate::isLoadedObject($order_carrier))
					$this->errors[] = Tools::displayError('Order carrier ID is invalid');
				elseif (!Validate::isTrackingNumber(Tools::getValue('tracking_number')))
					$this->errors[] = Tools::displayError('Tracking number is incorrect');
				else
				{
					// update shipping number
					// Keep these two following lines for backward compatibility, remove on 1.6 version
					$order->shipping_number = Tools::getValue('tracking_number');
					$order->update();

					// Update order_carrier
					$order_carrier->tracking_number = pSQL(Tools::getValue('tracking_number'));
					if ($order_carrier->update())
					{
						// Send mail to customer
						$customer = new Customer((int)$order->id_customer);
						$carrier = new Carrier((int)$order->id_carrier, $order->id_lang);
						if (!Validate::isLoadedObject($customer))
							throw new PrestaShopException('Can\'t load Customer object');
						if (!Validate::isLoadedObject($carrier))
							throw new PrestaShopException('Can\'t load Carrier object');
						$templateVars = array(
							'{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
							'{firstname}' => $customer->firstname,
							'{lastname}' => $customer->lastname,
							'{id_order}' => $order->id,
							'{order_name}' => $order->getUniqReference()
						);
						if (@Mail::Send((int)$order->id_lang, 'in_transit', Mail::l('Package in transit', (int)$order->id_lang), $templateVars,
							$customer->email, $customer->firstname.' '.$customer->lastname, null, null, null, null,
							_PS_MAIL_DIR_, true, (int)$order->id_shop))
						{
							Hook::exec('actionAdminOrdersTrackingNumberUpdate', array('order' => $order));
							Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
						}
						else
							$this->errors[] = Tools::displayError('An error occurred while sending e-mail to the customer.');
					}
					else
						$this->errors[] = Tools::displayError('Order carrier can\'t be updated');
				}
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
		}

		/* Change order date delivery OVERRIDE */
		elseif (Tools::isSubmit('submitDateDelivery') && isset($order))
        {
            if ($this->tabAccess['edit'] === '1')
            {

                $date_delivery = Tools::getValue('date_delivery');
                $pattern = '/^\d{2}[,]{1}\d{2}[,]{1}\d{4}+$/';
                if (!preg_match($pattern, $date_delivery))
                {
                    $this->errors[] = Tools::displayError('Format date dd/mm/yyyy example: 03,06,2014');
                }
                else{
                    $date_deliveryValue = DateTime::createFromFormat('d,m,Y',$date_delivery);
                    //print_r($date_deliveryValue);
                    $order->date_delivery = $date_deliveryValue->format('Y-m-d');
                    $order->update();
                }
            }
        }
		/* Change order state, add a new entry in order history and send an e-mail to the customer if needed */
		elseif (Tools::isSubmit('submitState') && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$order_state = new OrderState(Tools::getValue('id_order_state'));

				if (!Validate::isLoadedObject($order_state))
					$this->errors[] = Tools::displayError('Invalid new order status');
				else
				{
					$current_order_state = $order->getCurrentOrderState();
					if ($current_order_state->id != $order_state->id)
					{
						// Create new OrderHistory
						$history = new OrderHistory();
						$history->id_order = $order->id;
						$history->id_employee = (int)$this->context->employee->id;

						$use_existings_payment = false;
						if (!$order->hasInvoice())
							$use_existings_payment = true;
						$history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);

						$carrier = new Carrier($order->id_carrier, $order->id_lang);
						$templateVars = array();
						if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number)
							$templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
						// Save all changes
						if ($history->addWithemail(true, $templateVars))
						{
							// synchronizes quantities if needed..
							if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
							{
								foreach ($order->getProducts() as $product)
								{
									if (StockAvailable::dependsOnStock($product['product_id']))
										StockAvailable::synchronize($product['product_id'], (int)$product['id_shop']);
								}
							}

							Tools::redirectAdmin(self::$currentIndex.'&id_order='.(int)$order->id.'&vieworder&token='.$this->token);
						}
						$this->errors[] = Tools::displayError('An error occurred while changing the status or was unable to send e-mail to the customer.');
					}
					else
						$this->errors[] = Tools::displayError('This order is already assigned this status');
				}
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
		}

		/* Add a new message for the current order and send an e-mail to the customer if needed */
		elseif (Tools::isSubmit('submitMessage') && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$customer = new Customer(Tools::getValue('id_customer'));
				if (!Validate::isLoadedObject($customer))
					$this->errors[] = Tools::displayError('Customer is invalid');
				elseif (!Tools::getValue('message'))
					$this->errors[] = Tools::displayError('Message cannot be blank');
				else
				{
					/* Get message rules and and check fields validity */
					$rules = call_user_func(array('Message', 'getValidationRules'), 'Message');
					foreach ($rules['required'] as $field)
						if (($value = Tools::getValue($falseield)) == false && (string)$value != '0')
							if (!Tools::getValue('id_'.$this->table) || $field != 'passwd')
								$this->errors[] = sprintf(Tools::displayError('field %s is required.'), $field);
					foreach ($rules['size'] as $field => $maxLength)
						if (Tools::getValue($field) && Tools::strlen(Tools::getValue($field)) > $maxLength)
							$this->errors[] = sprintf(Tools::displayError('field %1$s is too long (%2$d chars max).'), $field, $maxLength);
					foreach ($rules['validate'] as $field => $function)
						if (Tools::getValue($field))
							if (!Validate::$function(htmlentities(Tools::getValue($field), ENT_COMPAT, 'UTF-8')))
								$this->errors[] = sprintf(Tools::displayError('field %s is invalid.'), $field);

					if (!count($this->errors))
					{
						//check if a thread already exist
						$id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder($customer->email, $order->id);
						if (!$id_customer_thread)
						{
							$customer_thread = new CustomerThread();
							$customer_thread->id_contact = 0;
							$customer_thread->id_customer = (int)$order->id_customer;
							$customer_thread->id_shop = (int)$this->context->shop->id;
							$customer_thread->id_order = (int)$order->id;
							$customer_thread->id_lang = (int)$this->context->language->id;
							$customer_thread->email = $customer->email;
							$customer_thread->status = 'open';
							$customer_thread->token = Tools::passwdGen(12);
							$customer_thread->add();
						}
						else
							$customer_thread = new CustomerThread((int)$id_customer_thread);

						$customer_message = new CustomerMessage();
						$customer_message->id_customer_thread = $customer_thread->id;
						$customer_message->id_employee = (int)$this->context->employee->id;
						$customer_message->message = htmlentities(Tools::getValue('message'), ENT_COMPAT, 'UTF-8');
						$customer_message->private = Tools::getValue('visibility');

						if (!$customer_message->add())
							$this->errors[] = Tools::displayError('An error occurred while saving message');
						elseif ($customer_message->private)
							Tools::redirectAdmin(self::$currentIndex.'&id_order='.(int)$order->id.'&vieworder&conf=11&token='.$this->token);
						else
						{
							$message = $customer_message->message;
							if (Configuration::get('PS_MAIL_TYPE') != Mail::TYPE_TEXT)
								$message = Tools::nl2br($customer_message->message);

							$varsTpl = array(
								'{lastname}' => $customer->lastname,
								'{firstname}' => $customer->firstname,
								'{id_order}' => $order->id,
								'{order_name}' => $order->getUniqReference(),
								'{message}' => $message
							);
							if (@Mail::Send((int)$order->id_lang, 'order_merchant_comment',
								Mail::l('New message regarding your order', (int)$order->id_lang), $varsTpl, $customer->email,
								$customer->firstname.' '.$customer->lastname, null, null, null, null, _PS_MAIL_DIR_, true, (int)$order->id_shop))
								Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=11'.'&token='.$this->token);
						}
						$this->errors[] = Tools::displayError('An error occurred while sending e-mail to the customer.');
					}
				}
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to delete here.');
		}

		/* Partial refund from order */
		elseif (Tools::isSubmit('partialRefund') && isset($order))
		{
			if ($this->tabAccess['edit'] == '1')
			{
				if (is_array($_POST['partialRefundProduct']))
				{
					$amount = 0;
					$order_detail_list = array();
					foreach ($_POST['partialRefundProduct'] as $id_order_detail => $amount_detail)
					{
						$order_detail_list[$id_order_detail]['quantity'] = (int)$_POST['partialRefundProductQuantity'][$id_order_detail];

						if (empty($amount_detail))
						{
							$order_detail = new OrderDetail((int)$id_order_detail);
							$order_detail_list[$id_order_detail]['amount'] = $order_detail->unit_price_tax_incl * $order_detail_list[$id_order_detail]['quantity'];
						}
						else
							$order_detail_list[$id_order_detail]['amount'] = (float)str_replace(',', '.', $amount_detail);
						$amount += $order_detail_list[$id_order_detail]['amount'];

						$order_detail = new OrderDetail((int)$id_order_detail);
						if (!$order->hasBeenDelivered() || ($order->hasBeenDelivered() && Tools::isSubmit('reinjectQuantities')) && $order_detail_list[$id_order_detail]['quantity'] > 0)
							$this->reinjectQuantity($order_detail, $order_detail_list[$id_order_detail]['quantity']);
					}

					$shipping_cost_amount = (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost'));
					if ($shipping_cost_amount > 0)
						$amount += $shipping_cost_amount;

					if ($amount > 0)
					{
						if (!OrderSlip::createPartialOrderSlip($order, $amount, $shipping_cost_amount, $order_detail_list))
							$this->errors[] = Tools::displayError('Cannot generate partial credit slip');

						// Generate voucher
						if (Tools::isSubmit('generateDiscountRefund') && !count($this->errors))
						{
							$cart_rule = new CartRule();
							$cart_rule->description = sprintf($this->l('Credit Slip for order #%d'), $order->id);
							$languages = Language::getLanguages(false);
							foreach ($languages as $language)
								// Define a temporary name
								$cart_rule->name[$language['id_lang']] = sprintf('V0C%1$dO%2$d', $order->id_customer, $order->id);

							// Define a temporary code
							$cart_rule->code = sprintf('V0C%1$dO%2$d', $order->id_customer, $order->id);
							$cart_rule->quantity = 1;
							$cart_rule->quantity_per_user = 1;

							// Specific to the customer
							$cart_rule->id_customer = $order->id_customer;
							$now = time();
							$cart_rule->date_from = date('Y-m-d H:i:s', $now);
							$cart_rule->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25)); /* 1 year */
							$cart_rule->active = 1;

							$cart_rule->reduction_amount = $amount;
							$cart_rule->reduction_tax = true;
							$cart_rule->minimum_amount_currency = $order->id_currency;
							$cart_rule->reduction_currency = $order->id_currency;

							if (!$cart_rule->add())
								$this->errors[] = Tools::displayError('Cannot generate voucher');
							else
							{
								// Update the voucher code and name
								foreach ($languages as $language)
									$cart_rule->name[$language['id_lang']] = sprintf('V%1$dC%2$dO%3$d', $cart_rule->id, $order->id_customer, $order->id);
								$cart_rule->code = sprintf('V%1$dC%2$dO%3$d', $cart_rule->id, $order->id_customer, $order->id);

								if (!$cart_rule->update())
									$this->errors[] = Tools::displayError('Cannot generate voucher');
								else
								{
									$currency = $this->context->currency;
									$customer = new Customer((int)($order->id_customer));
									$params['{lastname}'] = $customer->lastname;
									$params['{firstname}'] = $customer->firstname;
									$params['{id_order}'] = $order->id;
									$params['{order_name}'] = $order->getUniqReference();
									$params['{voucher_amount}'] = Tools::displayPrice($cart_rule->reduction_amount, $currency, false);
									$params['{voucher_num}'] = $cart_rule->code;
									$customer = new Customer((int)$order->id_customer);
									@Mail::Send((int)$order->id_lang, 'voucher', sprintf(Mail::l('New voucher regarding your order %s', (int)$order->id_lang), $order->reference),
										$params, $customer->email, $customer->firstname.' '.$customer->lastname, null, null, null,
										null, _PS_MAIL_DIR_, true, (int)$order->id_shop);
								}
							}
						}
					}
					else
						$this->errors[] = Tools::displayError('You have to write an amount if you want to do a partial credit slip');

					// Redirect if no errors
					if (!count($this->errors))
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=30&token='.$this->token);
				}
				else
					$this->errors[] = Tools::displayError('Partial refund data is incorrect');
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to delete here.');
		}

		/* Cancel product from order */
		elseif (Tools::isSubmit('cancelProduct') && isset($order))
		{
		 	if ($this->tabAccess['delete'] === '1')
			{
				if (!Tools::isSubmit('id_order_detail'))
					$this->errors[] = Tools::displayError('You must select a product');
				elseif (!Tools::isSubmit('cancelQuantity'))
					$this->errors[] = Tools::displayError('You must enter a quantity');
				else
				{
					$productList = Tools::getValue('id_order_detail');
					if ($productList)
						$productList = array_map('intval', $productList);
					
					$customizationList = Tools::getValue('id_customization');
					if ($customizationList)
						$customizationList = array_map('intval', $customizationList);
						
					$qtyList = Tools::getValue('cancelQuantity');
					if ($qtyList)
						$qtyList = array_map('intval', $qtyList);
						
					$customizationQtyList = Tools::getValue('cancelCustomizationQuantity');
					if ($customizationQtyList)
						$customizationQtyList = array_map('intval', $customizationQtyList);

					$full_product_list = $productList;
					$full_quantity_list = $qtyList;

					if ($customizationList)
						foreach ($customizationList as $key => $id_order_detail)
						{
							$full_product_list[(int)$id_order_detail] = $id_order_detail;
							$full_quantity_list[(int)$id_order_detail] += $customizationQtyList[$key];
						}

					if ($productList || $customizationList)
					{
						if ($productList)
						{
							$id_cart = Cart::getCartIdByOrderId($order->id);
							$customization_quantities = Customization::countQuantityByCart($id_cart);

							foreach ($productList as $key => $id_order_detail)
							{
								$qtyCancelProduct = abs($qtyList[$key]);
								if (!$qtyCancelProduct)
									$this->errors[] = Tools::displayError('No quantity selected for product.');

								$order_detail = new OrderDetail($id_order_detail);
								$customization_quantity = 0;
								if (array_key_exists($order_detail->product_id, $customization_quantities) && array_key_exists($order_detail->product_attribute_id, $customization_quantities[$order_detail->product_id]))
									$customization_quantity = (int)$customization_quantities[$order_detail->product_id][$order_detail->product_attribute_id];

								if (($order_detail->product_quantity - $customization_quantity - $order_detail->product_quantity_refunded - $order_detail->product_quantity_return) < $qtyCancelProduct)
									$this->errors[] = Tools::displayError('Invalid quantity selected for product.');

							}
						}
						if ($customizationList)
						{
							$customization_quantities = Customization::retrieveQuantitiesFromIds(array_keys($customizationList));

							foreach ($customizationList as $id_customization => $id_order_detail)
							{
								$qtyCancelProduct = abs($customizationQtyList[$id_customization]);
								$customization_quantity = $customization_quantities[$id_customization];

								if (!$qtyCancelProduct)
									$this->errors[] = Tools::displayError('No quantity selected for product.');

								if ($qtyCancelProduct > ($customization_quantity['quantity'] - ($customization_quantity['quantity_refunded'] + $customization_quantity['quantity_returned'])))
									$this->errors[] = Tools::displayError('Invalid quantity selected for product.');
							}
						}

						if (!count($this->errors) && $productList)
							foreach ($productList as $key => $id_order_detail)
							{
								$qty_cancel_product = abs($qtyList[$key]);
								$order_detail = new OrderDetail((int)($id_order_detail));

								if (!$order->hasBeenDelivered() || ($order->hasBeenDelivered() && Tools::isSubmit('reinjectQuantities')) && $qty_cancel_product > 0)
									$this->reinjectQuantity($order_detail, $qty_cancel_product);
								
								// Delete product
								$order_detail = new OrderDetail((int)$id_order_detail);
								if (!$order->deleteProduct($order, $order_detail, $qtyCancelProduct))
									$this->errors[] = Tools::displayError('An error occurred during deletion of the product.').' <span class="bold">'.$order_detail->product_name.'</span>';
								Hook::exec('actionProductCancel', array('order' => $order, 'id_order_detail' => (int)$id_order_detail));
							}
						if (!count($this->errors) && $customizationList)
							foreach ($customizationList as $id_customization => $id_order_detail)
							{
								$order_detail = new OrderDetail((int)($id_order_detail));
								$qtyCancelProduct = abs($customizationQtyList[$id_customization]);
								if (!$order->deleteCustomization($id_customization, $qtyCancelProduct, $order_detail))
									$this->errors[] = Tools::displayError('An error occurred during deletion of product customization.').' '.$id_customization;
							}
						// E-mail params
						if ((Tools::isSubmit('generateCreditSlip') || Tools::isSubmit('generateDiscount')) && !count($this->errors))
						{
							$customer = new Customer((int)($order->id_customer));
							$params['{lastname}'] = $customer->lastname;
							$params['{firstname}'] = $customer->firstname;
							$params['{id_order}'] = $order->id;
							$params['{order_name}'] = $order->getUniqReference();
						}

						// Generate credit slip
						if (Tools::isSubmit('generateCreditSlip') && !count($this->errors))
						{
							if (!OrderSlip::createOrderSlip($order, $full_product_list, $full_quantity_list, Tools::isSubmit('shippingBack')))
								$this->errors[] = Tools::displayError('Cannot generate credit slip');
							else
							{
								Hook::exec('actionOrderSlipAdd', array('order' => $order, 'productList' => $full_product_list, 'qtyList' => $full_quantity_list));
								@Mail::Send(
									(int)$order->id_lang,
									'credit_slip',
									Mail::l('New credit slip regarding your order', $order->id_lang),
									$params,
									$customer->email,
									$customer->firstname.' '.$customer->lastname,
									null,
									null,
									null,
									null,
									_PS_MAIL_DIR_,
									true,
									(int)$order->id_shop
								);
							}
						}

						// Generate voucher
						if (Tools::isSubmit('generateDiscount') && !count($this->errors))
						{
							$cartrule = new CartRule();
							$languages = Language::getLanguages($order);
							$cartrule->description = sprintf($this->l('Credit Slip for order #%d'), $order->id);
							foreach ($languages as $language)
							{
								// Define a temporary name
								$cartrule->name[$language['id_lang']] = 'V0C'.(int)($order->id_customer).'O'.(int)($order->id);
							}
							// Define a temporary code
							$cartrule->code = 'V0C'.(int)($order->id_customer).'O'.(int)($order->id);

							$cartrule->quantity = 1;
							$cartrule->quantity_per_user = 1;
							// Specific to the customer
							$cartrule->id_customer = $order->id_customer;
							$now = time();
							$cartrule->date_from = date('Y-m-d H:i:s', $now);
							$cartrule->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25)); /* 1 year */
							$cartrule->active = 1;

							$products = $order->getProducts(false, $full_product_list, $full_quantity_list);

							$total = 0;
							foreach ($products as $product)
								$total += $product['unit_price_tax_incl'] * $product['product_quantity'];

							if (Tools::isSubmit('shippingBack'))
								$total += $order->total_shipping;

							$cartrule->reduction_amount = $total;
							$cartrule->reduction_tax = true;
							$cartrule->minimum_amount_currency = $order->id_currency;
							$cartrule->reduction_currency = $order->id_currency;

							if (!$cartrule->add())
								$this->errors[] = Tools::displayError('Cannot generate voucher');
							else
							{
								// Update the voucher code and name
								foreach ($languages as $language)
									$cartrule->name[$language['id_lang']] = 'V'.(int)($cartrule->id).'C'.(int)($order->id_customer).'O'.$order->id;
								$cartrule->code = 'V'.(int)($cartrule->id).'C'.(int)($order->id_customer).'O'.$order->id;
								if (!$cartrule->update())
									$this->errors[] = Tools::displayError('Cannot generate voucher');
								else
								{
									$currency = $this->context->currency;
									$params['{voucher_amount}'] = Tools::displayPrice($cartrule->reduction_amount, $currency, false);
									$params['{voucher_num}'] = $cartrule->code;
									@Mail::Send((int)$order->id_lang, 'voucher', sprintf(Mail::l('New voucher regarding your order %s', (int)$order->id_lang), $order->reference),
									$params, $customer->email, $customer->firstname.' '.$customer->lastname, null, null, null,
									null, _PS_MAIL_DIR_, true, (int)$order->id_shop);
								}
							}
						}
					}
					else
						$this->errors[] = Tools::displayError('No product or quantity selected.');

					// Redirect if no errors
					if (!count($this->errors))
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=31&token='.$this->token);
				}
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('messageReaded'))
			Message::markAsReaded(Tools::getValue('messageReaded'), $this->context->employee->id);
		elseif (Tools::isSubmit('submitAddPayment') && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$amount = str_replace(',', '.', Tools::getValue('payment_amount'));
				$currency = new Currency(Tools::getValue('payment_currency'));
				$order_has_invoice = $order->hasInvoice();
				if ($order_has_invoice)
					$order_invoice = new OrderInvoice(Tools::getValue('payment_invoice'));
				else
					$order_invoice = null;

				if (!Validate::isLoadedObject($order))
					$this->errors[] = Tools::displayError('Order can\'t be found');
				elseif (!Validate::isNegativePrice($amount))
					$this->errors[] = Tools::displayError('Amount is invalid');
				elseif (!Validate::isString(Tools::getValue('payment_method')))
					$this->errors[] = Tools::displayError('Payment method is invalid');
				elseif (!Validate::isString(Tools::getValue('payment_transaction_id')))
					$this->errors[] = Tools::displayError('Transaction ID is invalid');
				elseif (!Validate::isLoadedObject($currency))
					$this->errors[] = Tools::displayError('Currency is invalid');
				elseif ($order_has_invoice && !Validate::isLoadedObject($order_invoice))
					$this->errors[] = Tools::displayError('Invoice is invalid');
				elseif (!Validate::isDate(Tools::getValue('payment_date')))
					$this->errors[] = Tools::displayError('Date is invalid');
				else
				{
					if (!$order->addOrderPayment($amount, Tools::getValue('payment_method'), Tools::getValue('payment_transaction_id'), $currency, Tools::getValue('payment_date'), $order_invoice))
						$this->errors[] = Tools::displayError('An error occurred on adding order payment');
					else
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
				}
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::isSubmit('submitEditNote'))
		{
			$note = Tools::getValue('note');
			$order_invoice = new OrderInvoice((int)Tools::getValue('id_order_invoice'));
			if (Validate::isLoadedObject($order_invoice) && Validate::isCleanHtml($note))
			{
				if ($this->tabAccess['edit'] === '1')
				{
					$order_invoice->note = $note;
					if ($order_invoice->save())
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order_invoice->id_order.'&vieworder&conf=4&token='.$this->token);
					else
						$this->errors[] = Tools::displayError('Unable to save invoice note.');
				}
				else
					$this->errors[] = Tools::displayError('You do not have permission to edit here.');
			}
			else
				$this->errors[] = Tools::displayError('Unable to load invoice for edit note.');
		}
		elseif (Tools::isSubmit('submitAddOrder') && ($id_cart = Tools::getValue('id_cart')) &&
			($module_name = Tools::getValue('payment_module_name')) &&
			($id_order_state = Tools::getValue('id_order_state')) && Validate::isModuleName($module_name))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$payment_module = Module::getInstanceByName($module_name);
				$cart = new Cart((int)$id_cart);
				Context::getContext()->currency = new Currency((int)$cart->id_currency);
				Context::getContext()->customer = new Customer((int)$cart->id_customer);
				$employee = new Employee((int)Context::getContext()->cookie->id_employee);
				$payment_module->validateOrder(
					(int)$cart->id, (int)$id_order_state,
					$cart->getOrderTotal(true, Cart::BOTH), $payment_module->displayName, $this->l('Manual order - Employee:').
					Tools::safeOutput(substr($employee->firstname, 0, 1).'. '.$employee->lastname), array(), null, false, $cart->secure_key
				);
				if ($payment_module->currentOrder)
					Tools::redirectAdmin(self::$currentIndex.'&id_order='.$payment_module->currentOrder.'&vieworder'.'&token='.$this->token);
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to add here.');
		}
		elseif ((Tools::isSubmit('submitAddressShipping') || Tools::isSubmit('submitAddressInvoice')) && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$address = new Address(Tools::getValue('id_address'));
				if (Validate::isLoadedObject($address))
				{
					// Update the address on order
					if (Tools::isSubmit('submitAddressShipping'))
						$order->id_address_delivery = $address->id;
					elseif (Tools::isSubmit('submitAddressInvoice'))
						$order->id_address_invoice = $address->id;
					$order->update();
					Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
				}
				else
					$this->errors[] = Tools::displayErrror('This address can\'t be loaded');
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::isSubmit('submitChangeCurrency') && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Tools::getValue('new_currency') != $order->id_currency && !$order->valid)
				{
					$old_currency = new Currency($order->id_currency);
					$currency = new Currency(Tools::getValue('new_currency'));
					if (!Validate::isLoadedObject($currency))
						throw new PrestaShopException('Can\'t load Currency object');

					// Update order detail amount
					foreach ($order->getOrderDetailList() as $row)
					{
						$order_detail = new OrderDetail($row['id_order_detail']);
						$fields = array(
							'ecotax',
							'product_price',
							'reduction_amount',
							'total_shipping_price_tax_excl',
							'total_shipping_price_tax_incl',
							'total_price_tax_incl',
							'total_price_tax_excl',
							'product_quantity_discount',
							'purchase_supplier_price',
							'reduction_amount',
							'reduction_amount_tax_incl',
							'reduction_amount_tax_excl',
							'unit_price_tax_incl',
							'unit_price_tax_excl',
							'original_product_price'
							
						);
						foreach ($fields as $field)
							$order_detail->{$field} = Tools::convertPriceFull($order_detail->{$field}, $old_currency, $currency);

						$order_detail->update();
						$order_detail->updateTaxAmount($order);
					}

					$id_order_carrier = Db::getInstance()->getValue('
						SELECT `id_order_carrier`
						FROM `'._DB_PREFIX_.'order_carrier`
						WHERE `id_order` = '.(int)$order->id);
					if ($id_order_carrier)
					{
						$order_carrier = new OrderCarrier($id_order_carrier);
						$order_carrier->shipping_cost_tax_excl = (float)Tools::convertPriceFull($order_carrier->shipping_cost_tax_excl, $old_currency, $currency);
						$order_carrier->shipping_cost_tax_incl = (float)Tools::convertPriceFull($order_carrier->shipping_cost_tax_incl, $old_currency, $currency);
						$order_carrier->update();
					}

					// Update order && order_invoice amount
					$fields = array(
						'total_discounts',
						'total_discounts_tax_incl',
						'total_discounts_tax_excl',
						'total_discount_tax_excl',
						'total_discount_tax_incl',
						'total_paid',
						'total_paid_tax_incl',
						'total_paid_tax_excl',
						'total_paid_real',
						'total_products',
						'total_products_wt',
						'total_shipping',
						'total_shipping_tax_incl',
						'total_shipping_tax_excl',
						'total_wrapping',
						'total_wrapping_tax_incl',
						'total_wrapping_tax_excl',
					);

					$invoices = $order->getInvoicesCollection();
					if ($invoices)
						foreach ($invoices as $invoice)
						{
							foreach ($fields as $field)
								if (isset($invoice->$field))
									$invoice->{$field} = Tools::convertPriceFull($invoice->{$field}, $old_currency, $currency);
							$invoice->save();
						}

					foreach ($fields as $field)
						if (isset($order->$field))
							$order->{$field} = Tools::convertPriceFull($order->{$field}, $old_currency, $currency);

					// Update currency in order
					$order->id_currency = $currency->id;
					// Update conversion rate
					$order->conversion_rate = (float)$currency->conversion_rate;
					$order->update();
				}
				else
					$this->errors[] = Tools::displayError('You cannot change the currency');
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::isSubmit('submitGenerateInvoice') && isset($order))
		{
			if (!Configuration::get('PS_INVOICE'))
				$this->errors[] = Tools::displayError('Invoice management has been disabled');
			elseif ($order->hasInvoice())
				$this->errors[] = Tools::displayError('This order already has an invoice');
			else
			{
				$order->setInvoice(true);
				Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
			}
		}
		elseif (Tools::isSubmit('submitDeleteVoucher') && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$order_cart_rule = new OrderCartRule(Tools::getValue('id_order_cart_rule'));
				if (Validate::isLoadedObject($order_cart_rule) && $order_cart_rule->id_order == $order->id)
				{
					if ($order_cart_rule->id_order_invoice)
					{
						$order_invoice = new OrderInvoice($order_cart_rule->id_order_invoice);
						if (!Validate::isLoadedObject($order_invoice))
							throw new PrestaShopException('Can\'t load Order Invoice object');

						// Update amounts of Order Invoice
						$order_invoice->total_discount_tax_excl -= $order_cart_rule->value_tax_excl;
						$order_invoice->total_discount_tax_incl -= $order_cart_rule->value;

						$order_invoice->total_paid_tax_excl += $order_cart_rule->value_tax_excl;
						$order_invoice->total_paid_tax_incl += $order_cart_rule->value;

						// Update Order Invoice
						$order_invoice->update();
					}

					// Update amounts of order
					$order->total_discounts -= $order_cart_rule->value;
					$order->total_discounts_tax_incl -= $order_cart_rule->value;
					$order->total_discounts_tax_excl -= $order_cart_rule->value_tax_excl;

					$order->total_paid += $order_cart_rule->value;
					$order->total_paid_tax_incl += $order_cart_rule->value;
					$order->total_paid_tax_excl += $order_cart_rule->value_tax_excl;

					// Delete Order Cart Rule and update Order
					$order_cart_rule->delete();
					$order->update();
					Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
				}
				else
					$this->errors[] = Tools::displayError('Cannot edit this Order Cart Rule');
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::getValue('submitNewVoucher') && isset($order))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!Tools::getValue('discount_name'))
					$this->errors[] = Tools::displayError('You must specify a name in order to create a new discount');
				else
				{
					if ($order->hasInvoice())
					{
						// If the discount is for only one invoice
						if (!Tools::isSubmit('discount_all_invoices'))
						{
							$order_invoice = new OrderInvoice(Tools::getValue('discount_invoice'));
							if (!Validate::isLoadedObject($order_invoice))
								throw new PrestaShopException('Can\'t load Order Invoice object');
						}
					}

					$cart_rules = array();
					switch (Tools::getValue('discount_type'))
					{
						// Percent type
						case 1:
							if (Tools::getValue('discount_value') < 100)
							{
								if (isset($order_invoice))
								{
									$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round($order_invoice->total_paid_tax_incl * Tools::getValue('discount_value') / 100, 2);
									$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round($order_invoice->total_paid_tax_excl * Tools::getValue('discount_value') / 100, 2);

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
								elseif ($order->hasInvoice())
								{
									$order_invoices_collection = $order->getInvoicesCollection();
									foreach ($order_invoices_collection as $order_invoice)
									{
										$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round($order_invoice->total_paid_tax_incl * Tools::getValue('discount_value') / 100, 2);
										$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round($order_invoice->total_paid_tax_excl * Tools::getValue('discount_value') / 100, 2);

										// Update OrderInvoice
										$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
									}
								}
								else
								{
									$cart_rules[0]['value_tax_incl'] = Tools::ps_round($order->total_paid_tax_incl * Tools::getValue('discount_value') / 100, 2);
									$cart_rules[0]['value_tax_excl'] = Tools::ps_round($order->total_paid_tax_excl * Tools::getValue('discount_value') / 100, 2);
								}
							}
							else
								$this->errors[] = Tools::displayError('Discount value is invalid');
							break;
						// Amount type
						case 2:
							if (isset($order_invoice))
							{
								if (Tools::getValue('discount_value') > $order_invoice->total_paid_tax_incl)
									$this->errors[] = Tools::displayError('Discount value is greater than the order invoice total');
								else
								{
									$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(Tools::getValue('discount_value'), 2);
									$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(Tools::getValue('discount_value') / (1 + ($order->getTaxesAverageUsed() / 100)), 2);

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
							}
							elseif ($order->hasInvoice())
							{
								$order_invoices_collection = $order->getInvoicesCollection();
								foreach ($order_invoices_collection as $order_invoice)
								{
									if (Tools::getValue('discount_value') > $order_invoice->total_paid_tax_incl)
										$this->errors[] = Tools::displayError('Discount value is greater than the order invoice total (Invoice:').$order_invoice->getInvoiceNumberFormatted(Context::getContext()->language->id).')';
									else
									{
										$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(Tools::getValue('discount_value'), 2);
										$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(Tools::getValue('discount_value') / (1 + ($order->getTaxesAverageUsed() / 100)), 2);

										// Update OrderInvoice
										$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
									}
								}
							}
							else
							{
								if (Tools::getValue('discount_value') > $order->total_paid_tax_incl)
									$this->errors[] = Tools::displayError('Discount value is greater than the order total');
								else
								{
									$cart_rules[0]['value_tax_incl'] = Tools::ps_round(Tools::getValue('discount_value'), 2);
									$cart_rules[0]['value_tax_excl'] = Tools::ps_round(Tools::getValue('discount_value') / (1 + ($order->getTaxesAverageUsed() / 100)), 2);
								}
							}
							break;
						// Free shipping type
						case 3:
							if (isset($order_invoice))
							{
								if ($order_invoice->total_shipping_tax_incl > 0)
								{
									$cart_rules[$order_invoice->id]['value_tax_incl'] = $order_invoice->total_shipping_tax_incl;
									$cart_rules[$order_invoice->id]['value_tax_excl'] = $order_invoice->total_shipping_tax_excl;

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
							}
							elseif ($order->hasInvoice())
							{
								$order_invoices_collection = $order->getInvoicesCollection();
								foreach ($order_invoices_collection as $order_invoice)
								{
									if ($order_invoice->total_shipping_tax_incl <= 0)
										continue;
									$cart_rules[$order_invoice->id]['value_tax_incl'] = $order_invoice->total_shipping_tax_incl;
									$cart_rules[$order_invoice->id]['value_tax_excl'] = $order_invoice->total_shipping_tax_excl;

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
							}
							else
							{
								$cart_rules[0]['value_tax_incl'] = $order->total_shipping_tax_incl;
								$cart_rules[0]['value_tax_excl'] = $order->total_shipping_tax_excl;
							}
							break;
						default:
							$this->errors[] = Tools::displayError('Discount type is invalid');
					}

					$res = true;
					foreach ($cart_rules as &$cart_rule)
					{
						$cartRuleObj = new CartRule();
						$cartRuleObj->date_from = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($order->date_add)));
						$cartRuleObj->date_to = date('Y-m-d H:i:s', strtotime('+1 hour'));
						$cartRuleObj->name[Configuration::get('PS_LANG_DEFAULT')] = Tools::getValue('discount_name');
						$cartRuleObj->quantity = 0;
						$cartRuleObj->quantity_per_user = 1;
						if (Tools::getValue('discount_type') == 1)
							$cartRuleObj->reduction_percent = Tools::getValue('discount_value');
						elseif (Tools::getValue('discount_type') == 2)
							$cartRuleObj->reduction_amount = $cart_rule['value_tax_excl'];
						elseif (Tools::getValue('discount_type') == 3)
							$cartRuleObj->free_shipping = 1;
						$cartRuleObj->active = 0;
						if ($res = $cartRuleObj->add())
							$cart_rule['id'] = $cartRuleObj->id;
						else
							break;
					}

					if ($res)
					{
						foreach ($cart_rules as $id_order_invoice => $cart_rule)
						{
							// Create OrderCartRule
							$order_cart_rule = new OrderCartRule();
							$order_cart_rule->id_order = $order->id;
							$order_cart_rule->id_cart_rule = $cart_rule['id'];
							$order_cart_rule->id_order_invoice = $id_order_invoice;
							$order_cart_rule->name = Tools::getValue('discount_name');
							$order_cart_rule->value = $cart_rule['value_tax_incl'];
							$order_cart_rule->value_tax_excl = $cart_rule['value_tax_excl'];
							$res &= $order_cart_rule->add();

							$order->total_discounts += $order_cart_rule->value;
							$order->total_discounts_tax_incl += $order_cart_rule->value;
							$order->total_discounts_tax_excl += $order_cart_rule->value_tax_excl;
							$order->total_paid -= $order_cart_rule->value;
							$order->total_paid_tax_incl -= $order_cart_rule->value;
							$order->total_paid_tax_excl -= $order_cart_rule->value_tax_excl;
						}

						// Update Order
						$res &= $order->update();
					}

					if ($res)
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
					else
						$this->errors[] = Tools::displayError('An error occurred on OrderCartRule creation');
				}
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
		}

		parent::postProcess();
	}
}

