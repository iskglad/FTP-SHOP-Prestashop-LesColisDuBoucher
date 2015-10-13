<?php

class AdminCartsController extends AdminCartsControllerCore
{
    public function setMedia()
    {
        $this->addJqueryUI('ui.datepicker');
        $this->addCSS(_PS_CSS_DIR_.'admin.css', 'all');
        $admin_webpath = str_ireplace(_PS_ROOT_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', '', $admin_webpath);
        $this->addCSS(__PS_BASE_URI__.$admin_webpath.'/themes/'.$this->bo_theme.'/css/admin.css', 'all');
        if ($this->context->language->is_rtl)
            $this->addCSS(_THEME_CSS_DIR_.'rtl.css');

        $this->addJquery();
        $this->addjQueryPlugin(array('cluetip', 'hoverIntent', 'scrollTo', 'alerts', 'chosen'));

        $this->addJS(array(
            _PS_JS_DIR_.'admin.js',
            _PS_JS_DIR_.'toggle.js',
            _PS_JS_DIR_.'tools.js',
            _PS_JS_DIR_.'ajax.js',
            _PS_JS_DIR_.'toolbar.js'
        ));


        if (!Tools::getValue('submitFormAjax'))
        {
            $this->addJs(_PS_JS_DIR_.'notifications.js');
            if (Configuration::get('PS_HELPBOX'))
                $this->addJS(_PS_JS_DIR_.'helpAccess.js');
        }

        // Execute Hook AdminController SetMedia
        Hook::exec('actionAdminControllerSetMedia', array());
    }

    public function ajaxReturnVars()
    {
        $id_cart = (int)$this->context->cart->id;
        $message_content = '';
        if ($message = Message::getMessageByCartId((int)$this->context->cart->id))
            $message_content = $message['message'];
        $cart_rules = $this->context->cart->getCartRules(CartRule::FILTER_ACTION_SHIPPING);

        $free_shipping = false;
        if (count($cart_rules))
            foreach ($cart_rules as $cart_rule)
                if ($cart_rule['id_cart_rule'] == CartRule::getIdByCode('BO_ORDER_'.(int)$this->context->cart->id))
                {
                    $free_shipping = true;
                    break;
                }
        return array('summary' => $this->getCartSummary(),
            'delivery_option_list' => $this->getDeliveryOptionList(),
            'cart' => $this->context->cart,
            'addresses' => $this->context->customer->getAddresses((int)$this->context->cart->id_lang, true),
            'id_cart' => $id_cart,
            'order_message' => $message_content,
            'link_order' => $this->context->link->getPageLink(
                    'order', false,
                    (int)$this->context->cart->id_lang,
                    'step=3&recover_cart='.$id_cart.'&token_cart='.md5(_COOKIE_KEY_.'recover_cart_'.$id_cart)),
            'free_shipping' => (int)$free_shipping
        );
    }
    public function ajaxProcessUpdateDeliveryOption()
    {
        if ($this->tabAccess['edit'] === '1')
        {
            $delivery_option = Tools::getValue('delivery_option');
            if ($delivery_option !== false)
                $this->context->cart->setDeliveryOption(array($this->context->cart->id_address_delivery => $delivery_option));
            if (Validate::isBool(($recyclable = (int)Tools::getValue('recyclable'))))
                $this->context->cart->recyclable = $recyclable;
            if (Validate::isBool(($gift = (int)Tools::getValue('gift'))))
                $this->context->cart->gift = $gift;
            if (Validate::isMessage(($gift_message = pSQL(Tools::getValue('gift_message')))))
                $this->context->cart->gift_message = $gift_message;

            //update date
            if (Validate::isDate(($date_delivery = pSQL(Tools::getValue('date_delivery')))))
                $this->context->cart->date_delivery = $date_delivery;

            //update hours
            if (Validate::isMessage(($hour_delivery_from = pSQL(Tools::getValue('hour_delivery_from')))) &&
                Validate::isMessage(($hour_delivery_to = pSQL(Tools::getValue('hour_delivery_to'))))){
                $this->context->cart->hour_delivery = "Entre ".$hour_delivery_from." et ".$hour_delivery_to;
            }

            $this->context->cart->save();
            echo Tools::jsonEncode($this->ajaxReturnVars());
        }
    }
}

