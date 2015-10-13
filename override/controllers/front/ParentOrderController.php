<?php

class ParentOrderController extends ParentOrderControllerCore
{
	public function setMedia()
	{
		FrontController::setMedia();
		if ($this->context->getMobileDevice() == false)
		{
			// Adding CSS style sheet
			$this->addCSS(_THEME_CSS_DIR_.'addresses.css');
			$this->addCSS(_THEME_CSS_DIR_.'checkout.css');
			// Adding JS files
			$this->addJS('http://maps.googleapis.com/maps/api/js?key=AIzaSyDvWSB_8JhCl-0moGJVn2iMPt8-9xlP2r8&amp;sensor=true');
			$this->addJS(_THEME_JS_DIR_.'plugins/infobox_packed.js');
			$this->addJS(_THEME_JS_DIR_.'tools.js');
			$this->addJS(_THEME_JS_DIR_.'relay.js');
			if ((Configuration::get('PS_ORDER_PROCESS_TYPE') == 0 && Tools::getValue('step') == 1) || Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
				$this->addJS(_THEME_JS_DIR_.'order-address.js');
			$this->addJqueryPlugin('fancybox');
			if ((int)(Configuration::get('PS_BLOCK_CART_AJAX')) || Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
			{
				$this->addJS(_THEME_JS_DIR_.'cart-summary.js');
				$this->addJqueryPlugin('typewatch');
			}
		}
	}

	protected function _assignSummaryInformations()
	{
        $summary = $this->context->cart->getSummaryDetails();
		$customizedDatas = Product::getAllCustomizedDatas($this->context->cart->id);

		// override customization tax rate with real tax (tax rules)
		if ($customizedDatas)
		{
			foreach ($summary['products'] as &$productUpdate)
			{
				$productId = (int)(isset($productUpdate['id_product']) ? $productUpdate['id_product'] : $productUpdate['product_id']);
				$productAttributeId = (int)(isset($productUpdate['id_product_attribute']) ? $productUpdate['id_product_attribute'] : $productUpdate['product_attribute_id']);

				if (isset($customizedDatas[$productId][$productAttributeId]))
					$productUpdate['tax_rate'] = Tax::getProductTaxRate($productId, $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
			}

			Product::addCustomizationPrice($summary['products'], $customizedDatas);
		}

		$cart_product_context = Context::getContext()->cloneContext();
		foreach ($summary['products'] as $key => &$product)
		{
           //	customcode
			$currProd = new Product($product['id_product'], true, $this->context->language->id, $this->context->shop->id);
            $currProdFeatures = $currProd->getFrontFeatures($this->context->language->id);
			foreach ($currProdFeatures as $key => $features) {
				if ($features['id_feature'] == 3) { // if feature = label (3 = id feature label)
					$product['label'] = $features['value'];
				}
			}

            //Tools::testVar($product['id_product_attribute']);
            //get current combination infos
            $found = false;
            foreach($currProd->combinations as $comb){
                //find current combination
                if ($comb['id_product_attribute'] == $product['id_product_attribute']){
                    $found = true;
                    //Setting isPromo
                    $product['isPro'] = $comb['isPro'];
                    //Setting isPro
                    $product['isPromo'] = $comb['isPromo'];
                    //Setting label
                    $product['label_name'] = $comb['label_name'];
                    //Setting colis name (used for colis)
                    $product['colis_name'] = $comb['colis_name'];
                    //Setting available quantity
                    $product['available_quantity'] = StockAvailable::getQuantityAvailableByProduct((int)$product['id_product'], $comb['id_product_attribute']);
                    if ($product['available_quantity'] < 0)
                        $product['available_quantity'] = 10000;//valeur arbritraire pour le front end. quantity < 0 => infinite
                }
            }
            //set default display value, so that the customer will now that the product is not from his cart version
            if ($found == false){
                $product['isPro'] = false;
                $product['isPromo'] = false;
                $product['label_name'] = "selection";
                $product['colis_name'] = "";
                $product['available_quantity'] = 10000;
                $product['description_short'] = "Attention ce produit ne fait partie de votre version de carte. Veuillez nous contactez.";
            }


			//	/customcode

			$product['quantity'] = $product['cart_quantity'];// for compatibility with 1.2 themes

			if ($cart_product_context->shop->id != $product['id_shop'])
				$cart_product_context->shop = new Shop((int)$product['id_shop']);
			$product['price_without_specific_price'] = Product::getPriceStatic(
				$product['id_product'], 
				!Product::getTaxCalculationMethod(), 
				$product['id_product_attribute'], 
				2, 
				null, 
				false, 
				false,
				1,
				false,
				null,
				null,
				null,
				$null,
				true,
				true,
				$cart_product_context);

			if (Product::getTaxCalculationMethod())
				$product['is_discounted'] = $product['price_without_specific_price'] != $product['price'];
			else
				$product['is_discounted'] = $product['price_without_specific_price'] != $product['price_wt'];
           //Product::testVar($currProd);

		}

        // Get available cart rules and unset the cart rules already in the cart
		$available_cart_rules = CartRule::getCustomerCartRules($this->context->language->id, (isset($this->context->customer->id) ? $this->context->customer->id : 0), true, true, true, $this->context->cart);
		$cart_cart_rules = $this->context->cart->getCartRules();
		foreach ($available_cart_rules as $key => $available_cart_rule)
		{
			if (strpos($available_cart_rule['code'], 'BO_ORDER_') === 0)
			{
				unset($available_cart_rules[$key]);
				continue;
			}
			foreach ($cart_cart_rules as $cart_cart_rule)
				if ($available_cart_rule['id_cart_rule'] == $cart_cart_rule['id_cart_rule'])
				{
					unset($available_cart_rules[$key]);
					continue 2;
				}
		}

		$show_option_allow_separate_package = (!$this->context->cart->isAllProductsInStock(true) && Configuration::get('PS_SHIP_WHEN_AVAILABLE'));

        $this->context->smarty->assign($summary);

		$this->context->smarty->assign(array(
			'token_cart' => Tools::getToken(false),
			'isLogged' => $this->isLogged,
			'isVirtualCart' => $this->context->cart->isVirtualCart(),
			'productNumber' => $this->context->cart->nbProducts(),
			'voucherAllowed' => CartRule::isFeatureActive(),
			'shippingCost' => $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING),
			'shippingCostTaxExc' => $this->context->cart->getOrderTotal(false, Cart::ONLY_SHIPPING),
			'customizedDatas' => $customizedDatas,
			'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
			'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
			'lastProductAdded' => $this->context->cart->getLastProduct(),
			'displayVouchers' => $available_cart_rules,
			'currencySign' => $this->context->currency->sign,
			'currencyRate' => $this->context->currency->conversion_rate,
			'currencyFormat' => $this->context->currency->format,
			'currencyBlank' => $this->context->currency->blank,
			'show_option_allow_separate_package' => $show_option_allow_separate_package,
				
		));

        $this->context->smarty->assign(array(
			'HOOK_SHOPPING_CART' => Hook::exec('displayShoppingCartFooter', $summary),
			'HOOK_SHOPPING_CART_EXTRA' => Hook::exec('displayShoppingCart', $summary)
		));
	}

	protected function _assignCarrier()
	{
		$address = new Address($this->context->cart->id_address_delivery);
		$id_zone = Address::getZoneById($address->id);
		$minimumOrder = Zone::getMinimumOrderById($id_zone);
		$carriers = $this->context->cart->simulateCarriersOutput();
		$checked = $this->context->cart->simulateCarrierSelectedOutput();
		$delivery_option_list = $this->context->cart->getDeliveryOptionList();
        $this->setDefaultCarrierSelection($this->context->cart->getDeliveryOptionList());

        //Reload page if error in carrier shipping price calculus
        /*foreach ($delivery_option_list as $id_address => &$option_list){
            foreach ($option_list as $key => &$option){
                if ($option["unique_carrier"]){
                    if ($option["total_price_with_tax"] == 0){
                        foreach ($option['carrier_list'] as $id_carrier => $carrier_infos){
                            if ($carrier_infos['instance']->is_free != 1){
                                //header('Location: '.$_SERVER['REQUEST_URI']);
                            }
                        }
                    }

                }
            }
        }*/
		$this->context->smarty->assign(array(
			'address_collection' => $this->context->cart->getAddressCollection(),
			'delivery_option_list' => $delivery_option_list,
			'carriers' => $carriers,
			'checked' => $checked,
			'minimum_order' => $minimumOrder,
			'delivery_option' => $this->context->cart->getDeliveryOption(null, false)
		));

		$vars = array(
			'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array(
				'carriers' => $carriers,
				'checked' => $checked,
				'delivery_option_list' => $delivery_option_list,
				'delivery_option' => $this->context->cart->getDeliveryOption(null, false)
			))
		);
		
		Cart::addExtraCarriers($vars);
		
		$this->context->smarty->assign($vars);
	}
}

