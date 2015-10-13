<?php

class AddressesController extends AddressesControllerCore
{
    public function initContent()
    {
        parent::initContent();
        $addresses_style = array(
            'company' => 'address_company',
            'vat_number' => 'address_company',
            'firstname' => 'address_name',
            'lastname' => 'address_name',
            'address1' => 'address_address1',
            'address2' => 'address_address2',
            'city' => 'address_city',
            'country' => 'address_country',
            'phone' => 'address_phone',
            'phone_mobile' => 'address_phone_mobile',
            'alias' => 'address_title',
            //custom
            'code' =>   'address_code',
            'floor' =>   'address_floor'
        );
        $this->context->smarty->assign(array(
            'addresses_style' => $addresses_style
        ));
    }
}

