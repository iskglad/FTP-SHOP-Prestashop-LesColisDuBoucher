<?php

class AuthController extends AuthControllerCore
{

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $groups = Group::getGroups($this->context->language->id);
        $this->context->smarty->assign('groups', $groups);

    }

    /**
     * Update context after customer creation
     * @param Customer $customer Created customer
     */
    protected function updateContext(Customer $customer)
    {

        if (Tools::getValue('groupments'))
        {
            $groupment = Tools::getValue('groupments');
            $customer->addGroups(array($groupment));
        }

        $this->context->customer = $customer;
        $this->context->smarty->assign('confirmation', 1);
        $this->context->cookie->id_customer = (int)$customer->id;
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->logged = 1;
        // if register process is in two steps, we display a message to confirm account creation
        if (!Configuration::get('PS_REGISTRATION_PROCESS_TYPE'))
            $this->context->cookie->account_created = 1;
        $customer->logged = 1;
        $this->context->cookie->email = $customer->email;
        $this->context->cookie->is_guest = !Tools::getValue('is_new_customer', 1);
        // Update cart address
        $this->context->cart->secure_key = $customer->secure_key;
    }

    /**
     * Process login
     */
    protected function processSubmitLogin()
    {
        Hook::exec('actionBeforeAuthentication');
        $passwd = trim(Tools::getValue('passwd'));
        $email = trim(Tools::getValue('email'));
        if (empty($email))
            $this->errors[] = Tools::displayError('E-mail address required');
        elseif (!Validate::isEmail($email))
            $this->errors[] = Tools::displayError('Invalid e-mail address');
        elseif (empty($passwd))
            $this->errors[] = Tools::displayError('Password is required');
        elseif (!Validate::isPasswd($passwd))
            $this->errors[] = Tools::displayError('Invalid password');
        else
        {
            $customer = new Customer();
            $authentication = $customer->getByEmail(trim($email), trim($passwd));
            if (!$authentication || !$customer->id)
                $this->errors[] = Tools::displayError('Authentication failed');
            else
            {
                $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
                $this->context->cookie->id_customer = (int)($customer->id);
                $this->context->cookie->customer_lastname = $customer->lastname;
                $this->context->cookie->customer_firstname = $customer->firstname;
                $this->context->cookie->logged = 1;
                $customer->logged = 1;
                $this->context->cookie->is_guest = $customer->isGuest();
                $this->context->cookie->passwd = $customer->passwd;
                $this->context->cookie->email = $customer->email;

                // Add customer to the context
                $this->context->customer = $customer;

                if (Configuration::get('PS_CART_FOLLOWING') && (empty($this->context->cookie->id_cart) || Cart::getNbProducts($this->context->cookie->id_cart) == 0))
                    $this->context->cookie->id_cart = (int)Cart::lastNoneOrderedCart($this->context->customer->id);

                // Update cart address
                $this->context->cart->id = $this->context->cookie->id_cart;
                $this->context->cart->setDeliveryOption(null);
                $this->context->cart->id_address_delivery = Address::getFirstCustomerAddressId((int)($customer->id));

                $this->context->cart->id_address_invoice = Address::getFirstCustomerAddressId((int)($customer->id));
                $this->context->cart->secure_key = $customer->secure_key;
                $this->context->cart->update();
                $this->context->cart->autosetProductAddress();

                // Delete cart if the customer is Pro
                //      Because if he already added product in cart before to connect, these products were from "normal cart" not "product cart"
                if (Customer::isCurrentCustomerPro())
                    $this->context->cart->delete();

                Hook::exec('actionAuthentication');

                // Login information have changed, so we check if the cart rules still apply
                CartRule::autoRemoveFromCart($this->context);
                CartRule::autoAddToCart($this->context);

                if (!$this->ajax)
                {
                    if ($back = Tools::getValue('back'))
                        Tools::redirect(html_entity_decode($back));
                    Tools::redirect('index.php?controller=my-account');
                }
            }
        }
        if ($this->ajax)
        {
            $return = array(
                'hasError' => !empty($this->errors),
                'errors' => $this->errors,
                'token' => Tools::getToken(false)
            );
            die(Tools::jsonEncode($return));
        }
        else
            $this->context->smarty->assign('authentification_error', $this->errors);
    }

}

