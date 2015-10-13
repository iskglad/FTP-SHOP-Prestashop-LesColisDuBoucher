<?php

class OrderDetailController extends OrderDetailControllerCore
{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        if (!($id_order = (int)Tools::getValue('id_order')) || !Validate::isUnsignedId($id_order))
            $this->errors[] = Tools::displayError('Order ID required');
        else
        {
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order) && $order->id_customer == $this->context->customer->id)
            {
                $id_order_state = (int)($order->getCurrentState());
                $carrier = new Carrier((int)($order->id_carrier), (int)($order->id_lang));
                $addressInvoice = new Address((int)($order->id_address_invoice));
                $addressDelivery = new Address((int)($order->id_address_delivery));

                $inv_adr_fields = AddressFormat::getOrderedAddressFields($addressInvoice->id_country);
                $dlv_adr_fields = AddressFormat::getOrderedAddressFields($addressDelivery->id_country);

                $invoiceAddressFormatedValues = AddressFormat::getFormattedAddressFieldsValues($addressInvoice, $inv_adr_fields);
                $deliveryAddressFormatedValues = AddressFormat::getFormattedAddressFieldsValues($addressDelivery, $dlv_adr_fields);

                if ($order->total_discounts > 0)
                    $this->context->smarty->assign('total_old', (float)($order->total_paid - $order->total_discounts));
                $products = $order->getProducts();

                /* DEPRECATED: customizedDatas @since 1.5 */
                $customizedDatas = Product::getAllCustomizedDatas((int)($order->id_cart));
                Product::addCustomizationPrice($products, $customizedDatas);

                OrderReturn::addReturnedQuantity($products, $order->id);

                $customer = new Customer($order->id_customer);

                $order_state = new OrderState($id_order_state);
                $order_state_name = $order_state->name[$this->context->language->id];
                $this->context->smarty->assign(array(
                    'shop_name' => strval(Configuration::get('PS_SHOP_NAME')),
                    'order' => $order,
                    'return_allowed' => (int)($order->isReturnable()),
                    'currency' => new Currency($order->id_currency),
                    'order_state' => (int)($id_order_state),
                    'order_state_name' => $order_state_name,
                    'invoiceAllowed' => (int)(Configuration::get('PS_INVOICE')),
                    'invoice' => (OrderState::invoiceAvailable($id_order_state) && count($order->getInvoicesCollection())),
                    'order_history' => $order->getHistory($this->context->language->id, false, true),
                    'products' => $products,
                    'discounts' => $order->getCartRules(),
                    'carrier' => $carrier,
                    'address_invoice' => $addressInvoice,
                    'invoiceState' => (Validate::isLoadedObject($addressInvoice) && $addressInvoice->id_state) ? new State($addressInvoice->id_state) : false,
                    'address_delivery' => $addressDelivery,
                    'inv_adr_fields' => $inv_adr_fields,
                    'dlv_adr_fields' => $dlv_adr_fields,
                    'invoiceAddressFormatedValues' => $invoiceAddressFormatedValues,
                    'deliveryAddressFormatedValues' => $deliveryAddressFormatedValues,
                    'deliveryState' => (Validate::isLoadedObject($addressDelivery) && $addressDelivery->id_state) ? new State($addressDelivery->id_state) : false,
                    'is_guest' => false,
                    'messages' => CustomerMessage::getMessagesByOrderId((int)($order->id), false),
                    'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
                    'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
                    'isRecyclable' => Configuration::get('PS_RECYCLABLE_PACK'),
                    'use_tax' => Configuration::get('PS_TAX'),
                    'group_use_tax' => (Group::getPriceDisplayMethod($customer->id_default_group) == PS_TAX_INC),
                    /* DEPRECATED: customizedDatas @since 1.5 */
                    'customizedDatas' => $customizedDatas
                    /* DEPRECATED: customizedDatas @since 1.5 */
                ));

                if ($carrier->url && $order->shipping_number)
                    $this->context->smarty->assign('followup', str_replace('@', $order->shipping_number, $carrier->url));
                $this->context->smarty->assign('HOOK_ORDERDETAILDISPLAYED', Hook::exec('displayOrderDetail', array('order' => $order)));
                Hook::exec('actionOrderDetail', array('carrier' => $carrier, 'order' => $order));

                unset($carrier, $addressInvoice, $addressDelivery);
            }
            else
                $this->errors[] = Tools::displayError('Cannot find this order');
            unset($order);
        }

        $this->setTemplate(_PS_THEME_DIR_.'order-detail.tpl');
    }

}

