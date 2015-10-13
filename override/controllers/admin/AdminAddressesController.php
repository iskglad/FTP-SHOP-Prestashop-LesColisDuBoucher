<?php

class AdminAddressesController extends AdminAddressesControllerCore
{
    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Addresses'),
                'image' => '../img/admin/contact.gif'
            ),
            'input' => array(
                array(
                    'type' => 'text_customer',
                    'label' => $this->l('Customer'),
                    'name' => 'id_customer',
                    'size' => 33,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Identification Number'),
                    'name' => 'dni',
                    'size' => 30,
                    'required' => false,
                    'desc' => $this->l('DNI / NIF / NIE')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Address alias'),
                    'name' => 'alias',
                    'size' => 33,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Home phone'),
                    'name' => 'phone',
                    'size' => 33,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Mobile phone'),
                    'name' => 'phone_mobile',
                    'size' => 33,
                    'required' => false,
                    'desc' => sprintf($this->l('You must register at least one phone number %s'), '<sup>*</sup>')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Code'),
                    'name' => 'code',
                    'size' => 33,
                    'required' => false,
                    'desc'=> $this->l('Code d\'accès.')
                ), array(
                    'type' => 'text',
                    'label' => $this->l('Étage'),
                    'name' => 'floor',
                    'size' => 33,
                    'required' => false
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Other'),
                    'name' => 'other',
                    'cols' => 36,
                    'rows' => 4,
                    'required' => false,
                    'hint' => $this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span>'
                ),
            ),
            'submit' => array(
                'title' => $this->l('   Save   '),
                'class' => 'button'
            )
        );
        $id_customer = (int)Tools::getValue('id_customer');
        if (!$id_customer && Validate::isLoadedObject($this->object))
            $id_customer = $this->object->id_customer;
        if ($id_customer)
        {
            $customer = new Customer((int)$id_customer);
            $token_customer = Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)$this->context->employee->id);
        }

        // @todo in 1.4, this include was done before the class declaration
        // We should use a hook now
        if (Configuration::get('VATNUMBER_MANAGEMENT') && file_exists(_PS_MODULE_DIR_.'vatnumber/vatnumber.php'))
            include_once(_PS_MODULE_DIR_.'vatnumber/vatnumber.php');
        if (Configuration::get('VATNUMBER_MANAGEMENT'))
            if (file_exists(_PS_MODULE_DIR_.'vatnumber/vatnumber.php') && VatNumber::isApplicable(Configuration::get('PS_COUNTRY_DEFAULT')))
                $vat = 'is_applicable';
            else
                $vat = 'management';

        $this->tpl_form_vars = array(
            'vat' => isset($vat) ? $vat : null,
            'customer' => isset($customer) ? $customer : null,
            'tokenCustomer' => isset ($token_customer) ? $token_customer : null
        );

        // Order address fields depending on country format
        $addresses_fields = $this->processAddressFormat();
        // we use  delivery address
        $addresses_fields = $addresses_fields['dlv_all_fields'];

        $temp_fields = array();

        foreach ($addresses_fields as $addr_field_item)
        {
            if ($addr_field_item == 'company')
            {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('Company'),
                    'name' => 'company',
                    'size' => 33,
                    'required' => false,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span>'
                );
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('VAT number'),
                    'name' => 'vat_number',
                    'size' => 33,
                );
            }
            else if ($addr_field_item == 'lastname')
            {
                if (isset($customer) &&
                    !Tools::isSubmit('submit'.strtoupper($this->table)) &&
                    Validate::isLoadedObject($customer) &&
                    !Validate::isLoadedObject($this->object))
                    $default_value = $customer->lastname;
                else
                    $default_value = '';

                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('Last name'),
                    'name' => 'lastname',
                    'size' => 33,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span>',
                    'default_value' => $default_value,
                );
            }
            else if ($addr_field_item == 'firstname')
            {
                if (isset($customer) &&
                    !Tools::isSubmit('submit'.strtoupper($this->table)) &&
                    Validate::isLoadedObject($customer) &&
                    !Validate::isLoadedObject($this->object))
                    $default_value = $customer->firstname;
                else
                    $default_value = '';

                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('First name'),
                    'name' => 'firstname',
                    'size' => 33,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span>',
                    'default_value' => $default_value,
                );
            }
            else if ($addr_field_item == 'address1')
            {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('Address'),
                    'name' => 'address1',
                    'size' => 33,
                    'required' => true,
                );
            }
            else if ($addr_field_item == 'address2')
            {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('Address').' (2)',
                    'name' => 'address2',
                    'size' => 33,
                    'required' => false,
                );
            }
            elseif ($addr_field_item == 'postcode')
            {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('Postal Code/Zip Code'),
                    'name' => 'postcode',
                    'size' => 33,
                    'required' => true,
                );
            }
            else if ($addr_field_item == 'city')
            {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('City'),
                    'name' => 'city',
                    'size' => 33,
                    'required' => true,
                );
            }
            else if ($addr_field_item == 'country' || $addr_field_item == 'Country:name')
            {
                $temp_fields[] = array(
                    'type' => 'select',
                    'label' => $this->l('Country:'),
                    'name' => 'id_country',
                    'required' => false,
                    'default_value' => (int)$this->context->country->id,
                    'options' => array(
                        'query' => Country::getCountries($this->context->language->id),
                        'id' => 'id_country',
                        'name' => 'name',
                    )
                );
                $temp_fields[] = array(
                    'type' => 'select',
                    'label' => $this->l('State'),
                    'name' => 'id_state',
                    'required' => false,
                    'options' => array(
                        'query' => array(),
                        'id' => 'id_state',
                        'name' => 'name',
                    )
                );
            }
        }

        // merge address format with the rest of the form
        array_splice($this->fields_form['input'], 3, 0, $temp_fields);

        //get the grandParent class
        //AdminAddressesControllerCore (parent of $this) reset the fields, so we cant edit or customise them
        //we need to directly call the grand parent renderForm.
        $adminCoreClass = Tools::get_grandparent_class($this);
        return $adminCoreClass::renderForm();
    }
}

