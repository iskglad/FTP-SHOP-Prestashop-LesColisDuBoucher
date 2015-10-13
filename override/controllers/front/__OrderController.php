<?php

class OrderController extends OrderControllerCore
{
	
	public function init()
	{
		global $orderTotal;

		ParentOrderController::init();

		$this->step = (int)(Tools::getValue('step'));
		if (!$this->nbProducts)
			$this->step = -1;

		// If some products have disappear
		if (!$this->context->cart->checkQuantities())
		{
			$this->step = 0;
			$this->errors[] = Tools::displayError('An item in your cart is no longer available in this quantity, you cannot proceed with your order.');
		}

		// Check minimal amount
		$currency = Currency::getCurrency((int)$this->context->cart->id_currency);

		$orderTotal = $this->context->cart->getOrderTotal();
		$minimal_purchase = Tools::convertPrice((float)Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
		if ($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimal_purchase && $this->step != -1)
		{
			$this->step = 0;
			$this->errors[] = sprintf(
				Tools::displayError('A minimum purchase total of %s is required in order to validate your order.'),
				Tools::displayPrice($minimal_purchase, $currency)
			);
		}
		if (!$this->context->customer->isLogged(true) && in_array($this->step, array(1, 2, 3)))
		{
			$back_url = $this->context->link->getPageLink('order', true, (int)$this->context->language->id, array('step' => $this->step, 'multi-shipping' => (int)Tools::getValue('multi-shipping')));
			$params = array('multi-shipping' => (int)Tools::getValue('multi-shipping'), 'display_guest_checkout' => (int)Configuration::get('PS_GUEST_CHECKOUT_ENABLED'), 'back' => $back_url);
			Tools::redirect($this->context->link->getPageLink('authentication', true, (int)$this->context->language->id, $params));
		}

		if (Tools::getValue('multi-shipping') == 1)
			$this->context->smarty->assign('multi_shipping', true);
		else
			$this->context->smarty->assign('multi_shipping', false);

		if ($this->context->customer->id)
			$this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
		else
			$this->context->smarty->assign('address_list', array());


		if ($this->context->cart->nbProducts())
		{
			if (Tools::getValue('ajax'))
			{
				if (Tools::getValue('method'))
				{
					switch (Tools::getValue('method'))
					{
						case 'updateMessage':
							if (Tools::isSubmit('message'))
							{
								$txtMessage = urldecode(Tools::getValue('message'));
								$this->_updateMessage($txtMessage);
								if (count($this->errors))
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								die(true);
							}
							break;


						case 'getCarrierList':
							die(Tools::jsonEncode($this->_getCarrierList()));
							break;

						case 'updateExtraCarrier':
							// Change virtualy the currents delivery options
							$delivery_option = $this->context->cart->getDeliveryOption();
							$delivery_option[(int)Tools::getValue('id_address')] = Tools::getValue('id_delivery_option');
							$this->context->cart->setDeliveryOption($delivery_option);
							$this->context->cart->save();
							$return = array(
								'content' => Hook::exec(
									'displayCarrierList',
									array(
										'address' => new Address((int)Tools::getValue('id_address'))
									)
								)
							);
							die(Tools::jsonEncode($return));
							break;

						case 'updateAddressesSelected':
							if ($this->context->customer->isLogged(true))
							{
								$address_delivery = new Address((int)(Tools::getValue('id_address_delivery')));
								$this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());
								$address_invoice = ((int)(Tools::getValue('id_address_delivery')) == (int)(Tools::getValue('id_address_invoice')) ? $address_delivery : new Address((int)(Tools::getValue('id_address_invoice'))));
								if ($address_delivery->id_customer != $this->context->customer->id || $address_invoice->id_customer != $this->context->customer->id)
									$this->errors[] = Tools::displayError('This address is not yours.');
								elseif (!Address::isCountryActiveById((int)(Tools::getValue('id_address_delivery'))))
									$this->errors[] = Tools::displayError('This address is not in a valid area.');
								elseif (!Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice) || $address_invoice->deleted || $address_delivery->deleted)
									$this->errors[] = Tools::displayError('This address is invalid.');
								else
								{
									$this->context->cart->id_address_delivery = (int)(Tools::getValue('id_address_delivery'));
									$this->context->cart->id_address_invoice = Tools::isSubmit('same') ? $this->context->cart->id_address_delivery : (int)(Tools::getValue('id_address_invoice'));
									if (!$this->context->cart->update())
										$this->errors[] = Tools::displayError('An error occurred while updating your cart.');

									// Address has changed, so we check if the cart rules still apply
									CartRule::autoRemoveFromCart($this->context);
									CartRule::autoAddToCart($this->context);
		
									if (!$this->context->cart->isMultiAddressDelivery())
										$this->context->cart->setNoMultishipping(); // As the cart is no multishipping, set each delivery address lines with the main delivery address

									if (!count($this->errors))
									{
										$result = $this->_getCarrierList($address_delivery);
										// Wrapping fees
										$wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
										$wrapping_fees_tax_inc = $wrapping_fees = $this->context->cart->getGiftWrappingPrice();
										$result = array_merge($result, array(
											'gift_price' => Tools::displayPrice(Tools::convertPrice(Product::getTaxCalculationMethod() == 1 ? $wrapping_fees : $wrapping_fees_tax_inc, new Currency((int)($this->context->cookie->id_currency)))),
											'carrier_data' => $this->_getCarrierList($address_delivery))
										);

										die(Tools::jsonEncode($result));
									}
								}
								if (count($this->errors))
									die(Tools::jsonEncode(array(
										'hasError' => true,
										'errors' => $this->errors
									)));
							}
							die(Tools::displayError());
							break;

						case 'multishipping':
							$this->_assignSummaryInformations();
							$this->context->smarty->assign('product_list', $this->context->cart->getProducts());
							
							if ($this->context->customer->id)
								$this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
							else
								$this->context->smarty->assign('address_list', array());
							$this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping-products.tpl');
							$this->display();
							die();
							break;

						case 'cartReload':
							$this->_assignSummaryInformations();
							if ($this->context->customer->id)
								$this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
							else
								$this->context->smarty->assign('address_list', array());
							$this->context->smarty->assign('opc', true);
							$this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
							$this->display();
							die();
							break;

						case 'noMultiAddressDelivery':
							$this->context->cart->setNoMultishipping();
							die();
							break;

						default:
							throw new PrestaShopException('Unknown method "'.Tools::getValue('method').'"');
					}
				}
				else
					throw new PrestaShopException('Method is not defined');
			}
		}
		// elseif (Tools::isSubmit('ajax'))
			// throw new PrestaShopException('Method is not defined');
	}

	public function postProcess()
	{
		// Update carrier selected on preProccess in order to fix a bug of
		// block cart when it's hooked on leftcolumn
		if ($this->step == 3 && Tools::isSubmit('processCarrier'))
			$this->processCarrier();
	}
	
	public function initContent()
	{
		global $isVirtualCart;

		ParentOrderController::initContent();

		if (Tools::isSubmit('ajax') && Tools::getValue('method') == 'updateExtraCarrier')
		{
			// Change virtualy the currents delivery options
			$delivery_option = $this->context->cart->getDeliveryOption();
			$delivery_option[(int)Tools::getValue('id_address')] = Tools::getValue('id_delivery_option');
			$this->context->cart->setDeliveryOption($delivery_option);
			$this->context->cart->save();
			$return = array(
				'content' => Hook::exec(
					'displayCarrierList',
					array(
						'address' => new Address((int)Tools::getValue('id_address'))
					)
				)
			);
			die(Tools::jsonEncode($return));
		}

		if ($this->nbProducts)
			$this->context->smarty->assign('virtual_cart', $isVirtualCart);

		// 4 steps to the order
		switch ((int)$this->step)
		{
			case -1;
				$this->context->smarty->assign('empty', 1);
				$left_col = Category::getSubCategoriesByDepth(2, 4, $this->context->language->id);
				$this->context->smarty->assign('left_col', $left_col);
				$this->addCSS(_THEME_CSS_DIR_.'cart.css');
				$this->addCSS(_THEME_CSS_DIR_.'delivery.css');
				$this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
			break;

			case 1:
				$this->_assignAddress();
				$this->_assignCarrier();
				$this->_assignRelays();

				$this->processAddressFormat();
				if (Tools::getValue('multi-shipping') == 1)
				{
					$this->_assignSummaryInformations();
					$this->context->smarty->assign('product_list', $this->context->cart->getProducts());
					$this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping.tpl');
				}
				else
					$this->setTemplate(_PS_THEME_DIR_.'order-carrier.tpl');
					$this->addJS(_THEME_JS_DIR_.'checkout.js');
			break;

			case 2:
				$date_only = false;
				$limitedDays = false;
				if (Tools::isSubmit('processAddress'))
					$this->processAddress();
				if (Tools::getValue('custom_relay'))
				{
					$this->context->cart->custom_relay = (int) Tools::getValue('custom_relay');
					$this->context->cart->save();
					$date_only = true;
				}
				if (Address::getZoneById($this->context->cart->id_address_delivery) == ID_ZONE_PROVINCE) {
					$date_only = true;
					$limitedDays = true;
				}
                                $id_address_delivery = $this->context->cart->id_address_delivery;
		$id_zone = Address::getZoneById($id_address_delivery);
                               $zone = new Zone($id_zone);
                               $conge =   Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT DISTINCT holiday
			FROM `'._DB_PREFIX_.'calendar_publicholiday`
			ORDER BY `holiday` ASC
		');
                                
                                $conge_tab = array();
                                foreach ($conge as $value) {
                                $conge_tab[] = $value['holiday'];
                                }
                                
                                $conge = $conge_tab;
//                                '2014, 06, 03#2014, 06, 05#2014, 06, 11#2014, 06, 26#2014, 07, 02#2014, 08, 21#2014, 08, 27#2014, 08, 28';
//                                $conge = explode('#',$conge);
                                if(strtolower($zone->name) == 'paris' && date('h')<13)
                                    $dateFrom = date('Y, m, d', strtotime('+2 day')); 
                                else
                                    $dateFrom = date('Y, m, d', strtotime('+3 day')); 
                                $dateTo = date('Y, m, d', strtotime('+1 year')); 
                                $dateinterval = '';
                                $dateCongeFrom ='';
                                $date_From = substr_replace($dateFrom,(int)(Tools::substr($dateFrom, 6, 2))-1,6,2);
                                
                                $i = 0;
                                for ($i=0;$i<sizeof($conge); $i++) {
                                if($dateFrom < $conge){
                                    
                                    $dateConge = substr_replace($conge[$i],sprintf("%02d",(int)(Tools::substr($conge[$i], 5, 2))-1),5,2);
                                    
                                    
                                    $dateCongeTo = substr_replace($dateConge,sprintf("%02d",(int)(Tools::substr($dateConge, 8, 2))-1),8,2);
                                    $dateCongeFrom = substr_replace($dateConge,sprintf("%02d",(int)(Tools::substr($dateConge, 8, 2))+1),8,2);
                                    if($i == 0 && sizeof($conge)==1){
                                        $dateinterval .= "{ from: new Date(".$date_From."),to: new Date(".$dateCongeTo.") },";
                                        $dateinterval .= "{ from: new Date(".$dateCongeFrom."),to: new Date(".$dateTo.") },";
                                    }
                                    elseif($i == 0)
                                        $dateinterval .= "{ from: new Date(".$date_From."),to: new Date(".$dateCongeTo.") },";
                                    elseif($i == sizeof($conge)-1){
                                        $dateConge_prev = substr_replace($conge[$i-1],(int)(Tools::substr($conge[$i-1], 5, 2))-1,5,2);
                                        $dateCongeTo_b1 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))-1,8,2);
                                    $dateCongeTo_b2 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))+1,8,2);                                    
                                    $dateCongeTo_a1 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))-1,8,2);
                                    $dateCongeTo_a2 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))+1,8,2);
                                        $dateinterval .= "{ from: new Date(".$dateCongeTo_a2."),to: new Date(".$dateCongeTo_b1.") },";
                                        $dateinterval .= "{ from: new Date(".$dateCongeTo_b2."),to: new Date(".$dateTo.") },";
                                        
                                    }
                                    else{
                                        
                                        $dateConge_prev = substr_replace($conge[$i-1],(int)(Tools::substr($conge[$i-1], 5, 2))-1,5,2);
                                        $dateCongeTo_b1 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))-1,8,2);
                                    $dateCongeTo_b2 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))+1,8,2);                                    
                                    $dateCongeTo_a1 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))-1,8,2);
                                    $dateCongeTo_a2 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))+1,8,2);
                                         $dateinterval .= "{ from: new Date(".$dateCongeTo_a2."),to: new Date(".$dateCongeTo_b1.") },";

                                         
                                    }
                                    
                                }
                                }


                                $selectableDateRange = "[".$dateinterval."]";
                                
				$this->context->smarty->assign(array(
					'date_only' => $date_only,
					'limitedDays' => $limitedDays,
                                        'selectableDateRange' => $selectableDateRange
				));

				$this->autoStep();
				$this->_assignCarrier();
				$this->processCarrier();
				$this->_assignZone();
				$this->setTemplate(_PS_THEME_DIR_.'order-date-delivery.tpl');
			break;

			case 3:
				if (Tools::getValue('date_delivery'))
				{
					$this->context->cart->date_delivery = date('Y-m-d',strtotime(Tools::getValue('date_delivery')));
					$this->context->cart->hour_delivery = Tools::getValue('hour_delivery');
					$this->context->cart->save();
				}

				if ($this->context->cart->custom_relay) {
					$customRelay = new Carrier($this->context->cart->custom_relay);
					$this->context->smarty->assign(array(
						'customRelay' => $customRelay,
					));
				}
				// if (!$this->context->cart->isVirtualCart())
				// 				{
				// 					if (!Tools::getValue('delivery_option') && !Tools::getValue('id_carrier') && !$this->context->cart->delivery_option && !$this->context->cart->id_carrier)
				// 						Tools::redirect('index.php?controller=order&step=2');
				// 					elseif (!Tools::getValue('id_carrier') && !$this->context->cart->id_carrier)
				// 					{
				// 						foreach (Tools::getValue('delivery_option') as $delivery_option)
				// 							if (empty($delivery_option))
				// 								Tools::redirect('index.php?controller=order&step=2');
				// 					}
				// 				}

				$this->autoStep();

				// Bypass payment step if total is 0
				if (($id_order = $this->_checkFreeOrder()) && $id_order)
				{
					if ($this->context->customer->is_guest)
					{
						$order = new Order((int)$id_order);
						$email = $this->context->customer->email;
						$this->context->customer->mylogout(); // If guest we clear the cookie for security reason
						Tools::redirect('index.php?controller=guest-tracking&id_order='.urlencode($order->reference).'&email='.urlencode($email));
					}
					else
						Tools::redirect('index.php?controller=history');
				}
				$this->_assignPayment();
				// assign some informations to display cart
				$this->_assignSummaryInformations();
				$this->setTemplate(_PS_THEME_DIR_.'order-payment.tpl');
			break;

			default:
				$this->_assignSummaryInformations();
				$left_col = Category::getSubCategoriesByDepth(2, 4, $this->context->language->id);
				$this->context->smarty->assign('left_col', $left_col);
				$this->addCSS(_THEME_CSS_DIR_.'cart.css');
				$this->addCSS(_THEME_CSS_DIR_.'delivery.css');
				$this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
			break;
		}

		$this->context->smarty->assign(array(
			'currencySign' => $this->context->currency->sign,
			'currencyRate' => $this->context->currency->conversion_rate,
			'currencyFormat' => $this->context->currency->format,
			'currencyBlank' => $this->context->currency->blank,
		));
	}

	protected function _assignRelays()
	{
		$relays = Order::getRelays();
		// $vars[0]['name'] = str_replace(' ', '_', strtolower($vars[0]['name']));
		$this->context->smarty->assign(
			array(
				'relays' => json_encode($relays),
				'ID_RELAY_CARRIER' => ID_RELAY_CARRIER
			)
		);
	}

	protected function _assignZone()
	{
		$id_address_delivery = $this->context->cart->id_address_delivery;
		$id_zone = Address::getZoneById($id_address_delivery);
		$vars = Zone::getZoneCustomInfos($id_zone);
		$vars[0]['name'] = str_replace(' ', '_', strtolower($vars[0]['name']));
//                $s  = json_decode('{"2013":{"10":{"27":"2","28":"2","29":"2","30":"2","31":"2"}},"2014":{"01":{"21":"2","22":"2"},"02":{"7":"2","8":"2","9":"2","18":"2","21":"2","26":"2"},"04":{"3":"2"},"05":{"27":null,"28":"2","29":null,"30":null,"31":null}}}');
//                print_r($s);
                
		$this->context->smarty->assign($vars[0]);
	}

	protected function _getCarrierList($address_delivery)
	{
		$cms = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);
		$link_conditions = $this->context->link->getCMSLink($cms, $cms->link_rewrite, true);
		if (!strpos($link_conditions, '?'))
			$link_conditions .= '?content_only=1';
		else
			$link_conditions .= '&content_only=1';
		
		// If a rule offer free-shipping, force hidding shipping prices
		$free_shipping = false;
		foreach ($this->context->cart->getCartRules() as $rule)
			if ($rule['free_shipping'])
			{
				$free_shipping = true;
				break;
			}
		$carriers = $this->context->cart->simulateCarriersOutput();
		$delivery_option = $this->context->cart->getDeliveryOption(null, false, false);

		$wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
		$wrapping_fees_tax_inc = $wrapping_fees = $this->context->cart->getGiftWrappingPrice();

		$id_zone = Address::getZoneByZipCode($address_delivery->postcode);
		$minimumOrder = Zone::getMinimumOrderById($id_zone);

		$vars = array(
			'free_shipping' => $free_shipping,
			'checkedTOS' => (int)($this->context->cookie->checkedTOS),
			'recyclablePackAllowed' => (int)(Configuration::get('PS_RECYCLABLE_PACK')),
			'giftAllowed' => (int)(Configuration::get('PS_GIFT_WRAPPING')),
			'cms_id' => (int)(Configuration::get('PS_CONDITIONS_CMS_ID')),
			'conditions' => (int)(Configuration::get('PS_CONDITIONS')),
			'link_conditions' => $link_conditions,
			'recyclable' => (int)($this->context->cart->recyclable),
			'gift_wrapping_price' => (float)$wrapping_fees,
			'total_wrapping_cost' => Tools::convertPrice($wrapping_fees_tax_inc, $this->context->currency),
			'total_wrapping_tax_exc_cost' => Tools::convertPrice($wrapping_fees, $this->context->currency),
			'delivery_option_list' => $this->context->cart->getDeliveryOptionList(),
			'carriers' => $carriers,
			'checked' => $this->context->cart->simulateCarrierSelectedOutput(),
			'delivery_option' => $delivery_option,
			'address_collection' => $this->context->cart->getAddressCollection(),
			'minimum_order' => $minimumOrder,
			'postcode' => $address_delivery->postcode,
			'ID_RELAY_CARRIER' => ID_RELAY_CARRIER
		);
		
		Cart::addExtraCarriers($vars);
		
		$this->context->smarty->assign($vars);

		if (!Address::isCountryActiveById((int)($this->context->cart->id_address_delivery)) && $this->context->cart->id_address_delivery != 0)
			$this->errors[] = Tools::displayError('This address is not in a valid area.');
		elseif ((!Validate::isLoadedObject($address_delivery) || $address_delivery->deleted) && $this->context->cart->id_address_delivery != 0)
			$this->errors[] = Tools::displayError('This address is invalid.');
		else
		{
			$result = array(
				'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array(
					'carriers' => $carriers,
					'delivery_option_list' => $this->context->cart->getDeliveryOptionList(),
					'delivery_option' => $this->context->cart->getDeliveryOption(null, true)
				)),
				'carrier_block' => $this->context->smarty->fetch(_PS_THEME_DIR_.'order-carrier-ajax.tpl')
			);
			
			Cart::addExtraCarriers($result);
			return $result;
		}
		if (count($this->errors))
			return array(
				'hasError' => true,
				'errors' => $this->errors,
				'carrier_block' => $this->context->smarty->fetch(_PS_THEME_DIR_.'order-carrier-ajax.tpl')
			);
	}
	
}