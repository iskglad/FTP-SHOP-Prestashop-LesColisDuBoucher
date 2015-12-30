<?php
setlocale(LC_TIME, "fr_FR.utf-8");

class OrderController extends OrderControllerCore
{
    public $display_debug;

    public function init()
    {
        global $orderTotal;

        ParentOrderController::init();

        //Displaying debug messages as error in front end (or not)
        //$this->display_debug = true;
        $this->display_debug = false;


        //Remove products wich havent enought quantity Or are ordered on a date where are not available
        if (Cart::getNbProducts($this->context->cart->id))
            $this->removeNoneAvailableProducts();

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
                            $this->_assignOrderAdjustmentInfos();
                            $this->_assignFreeShipping();

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

                        //Get all dates saved for a delivery of current cutsomer
                        //@Called lcdb_theme/js/glDatePicker.js line 133
                        case 'getCustomerDeliveryDates':
                            $next_orders = $this->getCustomerDeliveryDates();
                            echo json_encode($next_orders);
                            die();
                            break;

                        case 'getOutOfDeliveryDateProducts':
                            $passedDateProducts = $this->getOutOfDeliveryDateProducts();
                            echo json_encode($passedDateProducts);
                            die();
                            break;

                        //Delete cart rule from cart (used for dates that are out of rule shipping date)
                        //@Called from order_delivery_date/delete_cart_rule.js
                        case 'deleteCartRuleFromCart':
                            $res = $this->deleteCartRuleFromCart();
                            echo json_encode($res);
                            die();
                            break;

                        //Save order message from order payment summary page (page where customer chose payment method)
                        //@Called from order_payment/order_message.js
                        case 'updateOrderMessage':
                            if (Tools::getValue("message")){
                                $this->context->cart->message = Tools::getValue("message");
                                $this->context->cart->update();
                                echo "ok";
                            }
                            else
                                echo "ko";
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
                //============================
                //Cart Summary (error case)
                //============================
                $this->context->smarty->assign('empty', 1);
                $left_col = Category::getSubCategoriesByDepth(2, 4, $this->context->language->id);
                $this->context->smarty->assign('left_col', $left_col);
                $this->addCSS(_THEME_CSS_DIR_.'cart.css');
                $this->addCSS(_THEME_CSS_DIR_.'delivery.css');
                $this->_assignRelays();
                $this->_assignOrderAdjustmentInfos();
                $this->_assignFreeShipping();
                $this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
                break;

            case 1:
                //=============================
                //Order Carrier
                //=============================

                if ($this->context->cart->custom_relay){
                    $this->context->cart->custom_relay = 0;

                    $customer = new Customer($this->context->cart->id_customer);
                    $addresses = $customer->getAddresses($this->context->language->id);
                    if (count($addresses)){
                        $this->context->cart->id_address_delivery = $addresses[0]['id_address'];
                        $this->context->cart->id_address_invoice = $this->context->cart->id_address_delivery;
                    }

                    $this->context->cart->update();

                    if ($this->context->customer->isLogged(true))
                    {

                        $address_delivery = new Address($this->context->cart->id_address_delivery);
                        $this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());
                        $address_invoice = $address_delivery;
                        if ($address_delivery->id_customer != $this->context->customer->id || $address_invoice->id_customer != $this->context->customer->id)
                            $this->errors[] = Tools::displayError('This address is not yours.');
                        elseif (!Address::isCountryActiveById($this->context->cart->id_address_delivery))
                            $this->errors[] = Tools::displayError('This address is not in a valid area.');
                        elseif (!Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice) || $address_invoice->deleted || $address_delivery->deleted)
                            $this->errors[] = Tools::displayError('This address is invalid.');
                        else
                        {
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
                            }
                        }
                        header('Location: '.$_SERVER['REQUEST_URI']);
                    }
                }

                $this->_assignAddress();
                $this->_assignOrderAdjustmentInfos();
                $this->_assignFreeShipping();
                $this->_assignCarrier();
                $this->_assignRelays();

                $this->processAddressFormat();
                //get delivery address
                $addressDelivery = new Address((int)$this->context->cart->id_address_delivery);

                //get idZone (jet, ecolo, or UPS)
                $post_code = $addressDelivery->postcode;
                $id_zone = Address::getZoneByZipCode($post_code);

                //get minimum orders
                $minimum_order_zone_proche = Zone::getMinimumOrderById(ID_ZONE_JET);
                $minimum_order_current_zone = Zone::getMinimumOrderById($id_zone);

                //set variables
                $this->context->smarty->assign('id_zone', $id_zone);
                $this->context->smarty->assign('cp', $post_code);
                $this->context->smarty->assign('minimum_order_zone_proche', $minimum_order_zone_proche);
                $this->context->smarty->assign('minimum_order_current_zone', $minimum_order_current_zone);

                //get and set total product prices
                $current_cart = $this->context->smarty->getTemplateVars('cart');
                $total_with_tax = $current_cart->getTotalProductsPriceWithTax();
                $total_with_tax_with_gift = $current_cart->getTotalProductsPriceWithTax(true); //true = count gift
                $total_with_tax_and_discounts = $current_cart->getOrderTotalPriceWithTaxAndDiscount();
                $this->context->smarty->assign('total_products_price_with_tax', $total_with_tax);
                $this->context->smarty->assign('total_products_price_with_tax_with_gift', $total_with_tax_with_gift);
                $this->context->smarty->assign('total_order_price_with_tax_and_discounts', $total_with_tax_and_discounts);


                //add js files
                $this->addJS(_THEME_JS_DIR_.'order_minimum_error.js');
                $this->addJS(_THEME_JS_DIR_.'order_price_update.js');
                $this->addJS(_THEME_JS_DIR_.'checkout.js');
                $this->addJS(_THEME_JS_DIR_.'check_valid_carrier_relay.js');

                //gere multi-shipping (not used)
                if (Tools::getValue('multi-shipping') == 1)
                {
                    $this->_assignSummaryInformations();
                    $this->context->smarty->assign('product_list', $this->context->cart->getProducts());
                    $this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping.tpl');
                }
                //display order-carrier.tpl
                else{
                    $this->setTemplate(_PS_THEME_DIR_.'order-carrier.tpl');
                }
                break;

            case 2:
                //===================================
                //Order Delivery DATE
                //===================================
                $date_only = false;
                $limitedDays = false;
                $customRelay = null;
                $carrier_name = null;
                $carrier_description = null;

                if (Tools::isSubmit('processAddress'))
                    $this->processAddress();

                //save point relay (0 if not choosen)
                $this->context->cart->custom_relay = (int) Tools::getValue('custom_relay');
                if (Tools::getValue('custom_relay'))
                {
                    $this->context->cart->id_address_delivery = AddressCarrierRelay::getAddressIdByCarrierRelayId(Tools::getValue('custom_relay'));
                    $customRelay = new Carrier(Tools::getValue('custom_relay'));
                    $date_only = true;

                    //set relay description
                    $carrier_name = $customRelay->name;
                    $carrier_description = $customRelay->description[1];
                }
                $this->context->cart->save();

                $id_address_delivery = $this->context->cart->id_address_delivery;
                $delivery_address = new Address($id_address_delivery);
                $id_zone = Address::getZoneByZipCode($delivery_address->postcode);
                $zone = new Zone($id_zone);

                //limited dates for province
                if ( $id_zone == ID_ZONE_PROVINCE) {
                    $date_only = true;
                    $limitedDays = true;
                    $carrier = new Carrier($this->context->cart->id_carrier);
                    $carrier_name = $carrier->name;
                    $carrier_description = $carrier->description[1];
                }

                //get all holidays
                $conge =   Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			                    SELECT DISTINCT holiday
			                    FROM `'._DB_PREFIX_.'calendar_publicholiday`
			                    ORDER BY `holiday` ASC
		                    ');
                //keep in-coming holidays
                $conge_tab = array();
                foreach ($conge as $value) {
                    if(new DateTime(str_replace(',', '-', $value['holiday'])) >= new DateTime())
                        $conge_tab[] = $value['holiday'];
                }
                $conge = $conge_tab;

                //get order beginning date
                $dateFrom = Order::getFirstAvailableOrderDate($zone->id);

                //convert format
                $dateFrom = date('Y, m, d', $dateFrom);

                //allow command on 1month
                $dateTo = date('Y, m, d', strtotime('+2 month'));
                $dateinterval = '';
                $dateCongeFrom ='';

                $date_From = substr_replace($dateFrom,(int)(Tools::substr($dateFrom, 6, 2))-1,6,2);
                //var_dump('date_From:'.$date_From.'<br/>');
                $i = 0;
                for ($i=0;$i<sizeof($conge); $i++) {
                    if($dateFrom < $conge[$i]){
                        $dateConge = substr_replace($conge[$i],sprintf("%02d",(int)(Tools::substr($conge[$i], 5, 2))),5,2);
                        //var_dump('dateConge:'.$dateConge.'<br/>');
                        $dateCongeTo = substr_replace($dateConge,sprintf("%02d",(int)(Tools::substr($dateConge, 8, 2))-1),8,2);
                        //var_dump('dateCongeTo:'.$dateCongeTo.'<br/>');
                        $dateCongeFrom = substr_replace($dateConge,sprintf("%02d",(int)(Tools::substr($dateConge, 8, 2))+1),8,2);
                        //var_dump('dateCongeFrom:'.$dateCongeFrom.'<br/>');
                        if($i == 0 && sizeof($conge)==1){
                            $dateinterval .= "{ from: new Date(".$date_From."),to: new Date(".$dateCongeTo.") },";
                            $dateinterval .= "{ from: new Date(".$dateCongeFrom."),to: new Date(".$dateTo.") },";
                            //var_dump('dateInterval:'.$dateinterval.'<br/>');
                        }
                        elseif($i == 0){
                            $dateinterval .= "{ from: new Date(".$date_From."),to: new Date(".$dateCongeTo.") },";
                            //var_dump('dateIntervalFirst:'.$dateinterval.'<br/>');
                        }
                        elseif($i == sizeof($conge)-1){
                            $dateConge_prev = substr_replace($conge[$i-1],(int)(Tools::substr($conge[$i-1], 5, 2))-1,5,2);
                            $dateCongeTo_b1 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))-1,8,2);
                            $dateCongeTo_b2 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))+1,8,2);
                            $dateCongeTo_a1 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))-1,8,2);
                            $dateCongeTo_a2 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))+1,8,2);
                            $dateinterval .= "{ from: new Date(".$dateCongeTo_a2."),to: new Date(".$dateCongeTo_b1.") },";
                            $dateinterval .= "{ from: new Date(".$dateCongeTo_b2."),to: new Date(".$dateTo.") },";
                            //var_dump('dateIntervalLast:'.$dateinterval.'<br/>');

                        }
                        else{

                            $dateConge_prev = substr_replace($conge[$i-1],(int)(Tools::substr($conge[$i-1], 5, 2))-1,5,2);
                            $dateCongeTo_b1 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))-1,8,2);
                            $dateCongeTo_b2 = substr_replace($dateConge,(int)(Tools::substr($dateConge, 8, 2))+1,8,2);
                            $dateCongeTo_a1 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))-1,8,2);
                            $dateCongeTo_a2 = substr_replace($dateConge_prev,(int)(Tools::substr($dateConge_prev, 8, 2))+1,8,2);
                            $dateinterval .= "{ from: new Date(".$dateCongeTo_a2."),to: new Date(".$dateCongeTo_b1.") },";
                            //var_dump('dateIntervalInBetween:'.$dateinterval.'<br/>');
                        }

                    }
                }

                //defautl date interval (no conge)
                if ($dateinterval == ''){
                    $dateinterval = "{ from: new Date(".$date_From."),to: new Date(".$dateFrom.") }";
                }

                $selectableDateRange = "[".$dateinterval."]";

                //Get cart rules in Json (for shipping date limit check)
                $cart_rules_obj = array();
                $cart_rules = $this->context->cart->getCartRules();
                foreach ($cart_rules as $rule) {
                    $action = '';
                    //get rule action
                    if ($rule['reduction_amount'] > 0)
                        $action = "-".$rule['reduction_amount']." euros";
                    else if ($rule['reduction_percent'] > 0)
                        $action = "-".$rule['reduction_percent']."%";
                    else if ($rule['gift_product']){
                        $gift_product_name = Product::getProductName($rule['gift_product']);
                        $action = "\"".$gift_product_name."\" offert";
                    }

                    //Make object
                    $cart_rules_obj[] = array(
                        'id'                => $rule['id_cart_rule'],
                        'name'              => ucfirst($rule['name']),
                        'action'            => ucfirst($action),
                        'from'              => $rule['date_shipping_from'],
                        'to'                => $rule['date_shipping_to'],
                        'from_fr_string'    => strftime("%e %B", strtotime($rule['date_shipping_from'])),
                        'to_fr_string'      => strftime("%e %B", strtotime($rule['date_shipping_to'])),
                    );
                }

                $this->context->smarty->assign(array(
                    'date_only'             => $date_only,
                    'limitedDays'           => $limitedDays,
                    'selectableDateRange'   => $selectableDateRange,
                    'carrier_name'          => $carrier_name,
                    'carrier_description'   => $carrier_description,
                    'cart_rules_json'       => json_encode($cart_rules_obj)
                ));

                $this->autoStep();
                $this->_assignCarrier();
                $this->processCarrier();
                $this->_assignZone();

                //Set medias
                $this->addJS(_THEME_JS_DIR_.'config/constant.js');
                $this->addJS(_THEME_JS_DIR_.'order_delivery_date/reserved_delivery_date_click_event.js');
                $this->addJS(_THEME_JS_DIR_.'order_delivery_date/get_reserved_delivery_date_sync.js');
                $this->addJS(_THEME_JS_DIR_.'order_delivery_date/get_out_of_delivery_date_products.js');
                $this->addJS(_THEME_JS_DIR_.'order_delivery_date/display_out_of_date_products_error.js');
                $this->addJS(_THEME_JS_DIR_.'order_delivery_date/display_out_of_cart_rules_date_error.js');
                $this->addJS(_THEME_JS_DIR_.'order_delivery_date/delete_cart_rule.js');

                $this->setTemplate(_PS_THEME_DIR_.'order-date-delivery.tpl');
                break;

            case 3:
                //=================================
                //Order Summary
                //=================================
                if (Tools::getValue('date_delivery'))
                {
                    $this->context->cart->date_delivery = date('Y-m-d',strtotime(Tools::getValue('date_delivery')));
                    $this->context->cart->hour_delivery = Tools::getValue('hour_delivery');

                    //if hour_delivery contain "undefined" value -> clear
                    if (strpos($this->context->cart->hour_delivery, "undefined"))
                        $this->context->cart->hour_delivery = "";

                    $this->context->cart->save();
                }

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
                $this->_assignFreeShipping();
                $this->_assignSummaryInformations();
                $this->_assignOrderAdjustmentInfos();

                //Check cart rules shipping limit


                if ($this->context->cart->id_order_to_adjust > 0)
                    $this->_assignAdjustmentShippingDiscount();

                $this->addCSS(_THEME_CSS_DIR_.'order_payment/order_message.css');
                $this->addJS(_THEME_JS_DIR_.'order_payment/order_message.js');
                $this->setTemplate(_PS_THEME_DIR_.'order-payment.tpl');

                break;

            default:
                //===============================
                //Cart Summary
                //===============================

                $this->_assignFreeShipping();
                $this->_assignSummaryInformations();
                $this->_assignOrderAdjustmentInfos();
                $this->_assignRelays();

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

    /*Add FreeShipping Rule if price reached*/
    protected  function _assignFreeShipping(){
        //get delivery postcode
        $delivery_address = new Address($this->context->cart->id_address_delivery);
            //if no postcode setted
        if (!$delivery_address->postcode)
            $delivery_address->postcode = 75000; //set temporary postcode

        $id_zone = Address::getZoneByZipCode($delivery_address->postcode);
        $zone = new Zone($id_zone);

        if ($this->display_debug) $this->errors[] = "Cart id #".$this->context->cart->id; //Debug message
        //get total with taxes
        $total_wt = $this->context->cart->getTotalProductsPriceWithTax();

        //set freeShipping roof
        $free_shipping = $zone->free_shipping;

        //@TODO Manage free shipping according to Carrier free_shipping instead of Zone free_shipping
        //========================================================
        //Temporary solution (hard coding)
        //
        //Hard Code to manage "livraison le soir" and "livraison express" special cases:
            //"livraison le soir" is a jet carrier, but should be considered as Ecolo for prices
            //"livraison express" is a UPS carrier, but it has its own free shipping price
            //(different than Saver which use the normal free shipping for UPS zone
        //==========

        //get Ecolos free_shipping
        $ecolo_zone = new Zone(ID_ZONE_ECOLOTRANS);

        //set special free shipping case
            ////Used to show the good price for "livraison le soir" or "Livraison Express"  (see order-carrier.tpl line 418)
        $special_case_free_shipping = false;
        if (($id_zone == ID_ZONE_JET && $total_wt >= $ecolo_zone->free_shipping) ||
            ($id_zone == ID_ZONE_UPS && $total_wt >= DELIVERY_FREE_SHIPPING_UPS_EXPRESS))
            $special_case_free_shipping = true;

        $this->context->smarty->assign(array(
            'special_case_free_shipping' => $special_case_free_shipping //Used to show the good price for "livraison le soir" or "Livraison Express"  (see order-carrier.tpl line 418)
        ));


        //for free shipping calculus reconsidere as zone Ecolo if "livraison le soir" selected
        $carrier = new Carrier($this->context->cart->id_carrier);

        if ($carrier->name == 'Livraison le soir'){
            if ($this->display_debug)
                $this->errors[] = "Zone Jet, reconsidered as Ecolo for free shipping price: Livraison le soir";
            $zone = $ecolo_zone;
            $free_shipping = $zone->free_shipping;
        }

        ////for free shipping calculus reconsidere as zone Ecolo if "livraison le soir" selected
        if ($carrier->name == 'Livraison Express'){
            if ($this->display_debug)
                $this->errors[] = "Zone Ups, using  constant var (in settings.inc) for free shipping price: Livraison Express";
            $free_shipping = DELIVERY_FREE_SHIPPING_UPS_EXPRESS; //see config/settings.inc.php line 65
        }
        //=========================================================

        if ($total_wt >= $free_shipping || $this->context->cart->custom_relay > 0 ||
            $this->context->smarty->getTemplateVars('is_adjustment') == true){
            if ($this->display_debug) $this->errors[] = "Free shipping"; //Debug message
            //total product price > free shipping price OR ajustment cart

            //create and add a new FreeShipping rule
            $this->context->cart->addCustomFreeShippingRule();
            $this->context->smarty->assign(array(
                'free_shipping' => true,
            ));
        }
        else{
            if ($this->display_debug) $this->errors[] = " NOT Free shipping, delete free shipping if exist"; //Debug message
            //Delete FreeShipping rule if found
            $this->context->cart->deleteCustomFreeShippingRule();
            $this->context->smarty->assign(array(
                'free_shipping' => false,
            ));
        }
    }

    /*Assign Order adjustement infos for shopping-cart page (cart details)*/
    protected  function _assignOrderAdjustmentInfos(){
        //init
        $order_to_adjust = 0;
        $this->context->smarty->assign(array(
            'is_adjustment' => false,
        ));

        if ($this->display_debug) $this->errors[] = "CART #".$this->context->cart->id; //Debug message
        //if disableAjust
        if (Tools::getValue("disable_adjust") == 1){
            if ($this->display_debug) $this->errors[] = "Adjustment DISABLE"; //Debug message
            //remove order_adjust ID from cart
            $this->context->cart->id_order_to_adjust = 0;
            $this->context->cookie->__set("id_order_to_adjust", 0);
            $res = $this->context->cart->save();
            return 1;
        }

        //get ID order from GET[adjust]
        $id_order_to_adjust = Tools::getValue("adjust");
        if ($id_order_to_adjust > 0){
            //save in session
            $this->context->cookie->__set("id_order_to_adjust", $id_order_to_adjust);
        }
        else {
            //or from session if unfound
            $id_order_to_adjust = $this->context->cookie->id_order_to_adjust;
        }

        if ($id_order_to_adjust > 0){
            //set in card
            $this->context->cart->id_order_to_adjust = $id_order_to_adjust;

            $adjustments_total_wt = 0;
            //Get order to adjust (PS: loop to find the order if adjustment of adjustment)
            $order_to_adjust = null;
            while ($order_to_adjust == null){
                //get order
                $order_to_adjust = new Order($id_order_to_adjust);
                //if order is adjustment
                if ($order_to_adjust->id_order_to_adjust > 0){
                    //add total_wt
                    $adjustments_total_wt += $order_to_adjust->total_products_wt;
                    //make the loop continu
                    $id_order_to_adjust = $order_to_adjust->id_order_to_adjust;
                    $order_to_adjust = null;
                }
            }

            //if order exist
            if ($order_to_adjust->id){
                //if same user
                if ($order_to_adjust->id_customer == $this->context->cart->id_customer){
                    //get delivery Address
                    $delivery_address = new Address($order_to_adjust->id_address_delivery);

                    //get zone
                    $id_zone = Address::getZoneByZipCode($delivery_address->postcode);
                    $zone = new Zone($id_zone);

                    //save order_adjust ID in cart
                    $this->context->cart->id_oder_to_adjust = $order_to_adjust->id;
                    $this->context->cart->id_address_delivery = $order_to_adjust->id_address_delivery;
                    $this->context->cart->id_carrier = $order_to_adjust->id_carrier;
                    $this->context->cart->hour_delivery = $order_to_adjust->hour_delivery;
                    $this->context->cart->date_delivery = $order_to_adjust->date_delivery;

                    /*Total price to get to have shipping for free*/
                    $this->context->cart->adjustment_min_price_to_get_free_shipping_discount = 0;
                    $adjust_min_fs = $zone->free_shipping - ($adjustments_total_wt + $order_to_adjust->total_products_wt);
                    if ($adjust_min_fs > 0)
                        $this->context->cart->adjustment_min_price_to_get_free_shipping_discount = $adjust_min_fs;

                    $this->context->cart->save();


                    //Debug message
                    if ($this->display_debug)
                        $this->errors[] = "Adjustment:"."Order#:".$order_to_adjust->id."<br/>TTC:".$order_to_adjust->total_products_wt."<br/>Adjustments Total:".$adjustments_total_wt."<br/>ZonePrice:".$zone->free_shipping."<br/>Min to free shipping:".$this->context->cart->adjustment_min_price_to_get_free_shipping_discount;
                    //set vars
                    $this->context->smarty->assign(array(
                        'is_adjustment' => true,
                        'order_to_adjust' => $order_to_adjust,
                        'zone_free_shipping_price' => $zone->free_shipping,
                        /*Total price to get to have shipping for free*/
                        'adjust_price_to_free_shipping' => $adjust_min_fs
                    ));

                    //if Summary and payment page (step 3)
                    if ($this->step === 3) {
                        //Check cart rule shipping limit to avoid adjustment with disallowed cart rules
                        $rules = $this->context->cart->getCartRules();
                        foreach ($rules as $rule){
                            //if limit date setted setted
                            if (strtotime($rule['date_shipping_from']) > 0 && strtotime($rule['date_shipping_to']) > 0) {
                                //if date out of rule shipping date limits (comparing date only)
                                if (strtotime($order_to_adjust->date_delivery) < strtotime(substr($rule['date_shipping_from'], 0, 10)) ||
                                    strtotime($order_to_adjust->date_delivery) > strtotime(substr($rule['date_shipping_to'], 0, 10))
                                ) {
                                    //redirect to "basket" page (panier) with error query
                                    Tools::redirect('index.php?controller=order&error_cart_rule_limit=1');
                                    return 0;
                                }
                            }
                        }
                    }

                    //if "basket" page (panier) AND error_cart_rule_limit setted in url query
                    if ($this->step === 0 && Tools::getValue('error_cart_rule_limit')){
                        //Display error
                        $this->errors[] = Tools::displayError("La date de la commande est hors de la limite de date de vos codes de réductions");
                        return 0;
                    }

                    return 1;
                }
                //Error order owned by a different user
                else{
                    $this->errors[] = Tools::displayError('Adjustment not Allowed: Order is not from current User.');
                    return 0;
                }
            }
            //Error not found
            else{
                $this->errors[] = Tools::displayError('Ajustment not Allowed: This order is invalid.');
                return 0;
            }
        }
        //Debug message
        if ($this->display_debug)
            $this->errors[] = "Not Adjustment";
        return 1;
    }


    protected function _assignAdjustmentShippingDiscount(){
        //get vars
        $min_to_fs =  $this->context->cart->adjustment_min_price_to_get_free_shipping_discount;
        $total_wt = $this->context->cart->getTotalProductsPriceWithTax();

        //Debug message
        if ($this->display_debug){
            $this->errors[] = "Min to get FreeShipping:".$min_to_fs;
            $this->errors[] = "Total Product WT:".$total_wt;
        }

        //if order free shipping price havent been previously reached AND is now reached
        if ($min_to_fs > 0 && $total_wt > $min_to_fs){
            $order_to_adjust = $this->context->cart->getOrderToAdjust();
            //add a discount to pay it shipping price back
            if ($this->display_debug){
                $this->errors[] = "Add refund : ".$order_to_adjust->total_shipping;
            }
            $this->context->cart->addCustomShippingRefund($order_to_adjust->total_shipping);
        }

        //if order free shipping price have previously been reached OR isnt reached yet
        else if ($min_to_fs == 0 || ($min_to_fs > 0 && $total_wt > $min_to_fs)){
            //remove discount that pay the shipping price back (if exist)
            if ($this->display_debug){
                $this->errors[] = "No refund/remove if exist";
            }
            $this->context->cart->deleteCustomShippingRefund();
        }

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

        $this->context->smarty->assign($vars[0]);

        //Set Hours for "Livraison soir"
        if ($this->context->cart->id_carrier){
            $carrier = new Carrier($this->context->cart->id_carrier);

            if ($carrier->id_reference == ID_CARRIER_REFERENCE_LIVRAISON_LE_SOIR){
                $zone_ecolo = new Zone(ID_ZONE_ECOLOTRANS);
                $this->context->smarty->assign(array(
                    'h_start' => $zone_ecolo->h_start,
                    'h_end' => $zone_ecolo->h_end
                ));
            }
        }
    }

    //==============================================================================
    //Remove None Available Products
    //==============================================================================
    //None available -> No more quantity

    public function removeNoneAvailableProducts(){
        $products = $this->context->cart->getProducts(true);

        foreach ($products as $product){

            //if quantity != infinite
            if ($product['quantity_available'] > -1){
                //if more in card than available quantity
                if ($product['cart_quantity'] > $product['quantity_available']){
                    //set quantity to available quantity
                    $qty_to_reduce = $product['cart_quantity'] - $product['quantity_available'];

                    if ($qty_to_reduce == $product['cart_quantity']){
                        //remove product
                        $this->context->cart->deleteProduct($product['id_product'], $product['id_product_attribute']);

                        //Message
                        $this->errors[] = 'Le produit "'.$product['name'].'" à été retiré car plus disponible.';

                    }
                    else{
                        //update quantity
                        //@param $quantity, $id_product, $id_product_attribute = null, $id_customization = false, 'down'
                        $this->context->cart->updateQty($qty_to_reduce, $product['id_product'], $product['id_product_attribute'], $id_customization = false, 'down');

                        //Message
                        $this->errors[] = 'Plus que '.$product['quantity_available'].' "'.$product['name'].'" disponibles. La quantité à été réduite à '.$product['quantity_available'].'.';
                    }
                }
            }
        }
    }


    //==============================================================================


    protected function _getCarrierList($address_delivery)
    {
        $cms = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);
        $link_conditions = $this->context->link->getCMSLink($cms, $cms->link_rewrite, true);
        if (!strpos($link_conditions, '?'))
            $link_conditions .= '?content_only=1';
        else
            $link_conditions .= '&content_only=1';

        $this->_assignOrderAdjustmentInfos();
        $this->_assignFreeShipping();

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

        if ($free_shipping == true){
            $this->context->smarty->assign(array("free_shipping" => true));
        }
        $vars = array(
            /*'free_shipping' => $free_shipping,*/
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
            //get delivery address
            $addressDelivery = new Address((int)$this->context->cart->id_address_delivery);

            //get idZone (jet, ecolo, or UPS)
            $post_code = $addressDelivery->postcode;
            $id_zone = Address::getZoneByZipCode($post_code);
            $result = array(
                'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array(
                        'carriers' => $carriers,
                        'delivery_option_list' => $this->context->cart->getDeliveryOptionList(),
                        'delivery_option' => $this->context->cart->getDeliveryOption(null, true),
                        'id_zone' => $id_zone
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
    //======================================================================
    //AJAX FUNCS
    //======================================================================
    public function getOutOfDeliveryDateProducts(){
        if (!Tools::isSubmit('date_delivery'))
            return false;
        $passedDateProducts = array();

        //get date
        $date_delivery = strtotime(Tools::getValue('date_delivery'));
        //get product in cart
        $products = $this->context->cart->getProducts(true);

        foreach ($products as $product){

            //get product begin and end date
            $comb = new Combination($product['id_product_attribute']);
            $product_end_date = strtotime($comb->available_date);
            $product_begin_date = strtotime($comb->begin_date);

            //if ended date is before ordered date
            //OR begin date is after ordered date
            if (($product_end_date < $date_delivery && $product_end_date > 0) ||
                ($product_begin_date > $date_delivery && $product_begin_date > 0)
            ){
                array_push($passedDateProducts, $product);
            }
        }
        return $passedDateProducts;
    }

    public function getCustomerDeliveryDates(){
        $next_orders = array();

        //get id_customer
        $orders = Order::getCustomerOrders($this->context->cart->id_customer);
        if (!count($orders)){
            return array(); //empty array if no orders
        }
        //Take out passed orders
        foreach ($orders as $key => &$order){
            if (strtotime($order['date_delivery']) < time()){
                //unset($orders[$key]);
                $order['date_delivery_timestamp'] = -1;
            }
            else
                $order['date_delivery_timestamp'] = strtotime($order['date_delivery']); //keep timestamp
        }

        //Set others order infos
        foreach ($orders as &$order){
            //Post code
            $address = new Address($order['id_address_delivery']);
            $order['delivery_postcode'] = $address->postcode;

            //Carrier name
            $carrier = new Carrier($order['id_carrier']);
            $order['carrier_name'] = $carrier->name;
        }
        return $orders;
    }

    public function deleteCartRuleFromCart(){
        //init vars
        $res = array(
            'success'                       => 0,
            "new_order_total_with_shipping"     => 0,
            "new_order_total_without_shipping"  => 0
        );
        $cart = $this->context->cart; //get cart
        $id_cart_rule = Tools::getValue('id_cart_rule'); //get url query
        //Check error
        if (!$id_cart_rule){
            $res['success'] = 0;
            return $res;
        }

        //delete rule from cart
        $success = $cart->removeCartRule($id_cart_rule);
        if ($success){
            //update res
            $res['success'] = 1;
            //update order price
            $res['new_order_total_without_shipping'] = $cart->getOrderTotalPriceWithTaxAndDiscount();
            $res['new_order_total_with_shipping'] = $cart->getOrderTotal(true, Cart::BOTH, null, null, false);
        }

        return $res;
    }
}