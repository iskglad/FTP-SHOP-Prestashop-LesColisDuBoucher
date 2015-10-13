<?php
/**
 * Created by PhpStorm.
 * User: gladisk
 * Date: 11/12/14
 * Time: 11:41 AM
 */

class AdminCarriersRelayAddressesController extends AdminControllerCore{
    /** @var array countries list */
    protected $countries_array = array();

    public function __construct()
    {
        $this->required_database = true;
        $this->required_fields = array('company','address2', 'postcode', 'other', 'phone', 'vat_number', 'dni');
        $this->table = 'address';
        $this->className = 'AddressCarrierRelay';
        $this->lang = false;
        $this->addressType = 'customer';
        $this->explicitSelect = true;
        $this->context = Context::getContext();

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

        if (!Tools::getValue('realedit'))
            $this->deleted = true;

        $countries = Country::getCountries($this->context->language->id);
        foreach ($countries as $country)
            $this->countries_array[$country['id_country']] = $country['name'];

        $this->fields_list = array(
            'id_address' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
            'lastname' => array('title' => $this->l('Name'), 'width' => 140),
            'address1' => array('title' => $this->l('Address')),
            'postcode' => array('title' => $this->l('Postal Code/Zip Code'), 'align' => 'right', 'width' => 80),
            'city' => array('title' => $this->l('City'), 'width' => 150),
            'country' => array('title' => $this->l('Country'), 'width' => 100, 'type' => 'select', 'list' => $this->countries_array, 'filter_key' => 'cl!id_country'));

        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->toolbar_btn['import'] = array(
            'href' => $this->context->link->getAdminLink('AdminImport', true).'&import_type='.$this->table,
            'desc' => $this->l('Import')
        );
    }

    public function renderList()
    {
       $this->_select = 'cl.`name` as country';
        $this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (cl.`id_country` = a.`id_country` AND cl.`id_lang` = '.(int)$this->context->language->id.')
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON a.id_carrier_relay = c.id_carrier
		';
        $this->_where = 'AND a.id_carrier_relay != 0 '.
            //Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c').
            ' AND c.type_carrier = 2'.
            ' AND c.deleted = 0'.
            ' AND c.active = 1';
        return parent::renderList();
    }

    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Addresses'),
                'image' => '../img/admin/contact.gif'
            ),
            'input' => array(
                array(
                    'type' => 'text_carrier_relay',
                    'label' => $this->l('Carrier Relay ID'),
                    'name' => 'carrier',
                    'size' => 33,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Address name'),
                    'name' => 'lastname',
                    'size' => 33,
                    'required' => true,
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
                    'label' => $this->l('Phone'),
                    'name' => 'phone',
                    'size' => 33,
                    'required' => false,
                    'desc' => sprintf($this->l('You must register at least one phone number %s'), '<sup>*</sup>')
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
        $id_carrier_relay = (int)Tools::getValue('id_carrier_relay');
        if (!$id_carrier_relay && Validate::isLoadedObject($this->object))
            $id_carrier_relay = $this->object->id_carrier_relay;
        if ($id_carrier_relay)
        {
            $carrier_relay = new Carrier((int)$id_carrier_relay);
            $token_carrier_relay = Tools::getAdminToken('AdminCarriersRelays'.(int)(Tab::getIdFromClassName('AdminCarriersRelays')).(int)$this->context->employee->id);
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
            'carrier_relay' => isset($carrier_relay) ? $carrier_relay : null,
            'token_carrier_relay' => isset ($token_carrier_relay) ? $token_carrier_relay : null
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
            else if ($addr_field_item == 'code')
            {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l("Code d\'accès"),
                    'name' => 'code',
                    'size' => 33,
                    'required' => false,
                );
            }
            else if ($addr_field_item == 'floor')
            {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->l('Étage'),
                    'name' => 'floor',
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

        return parent::renderForm();
    }

    public function processSave()
    {
        // Transform name in id_c for parent processing
        /*if (Validate::isEmail(Tools::getValue('email')))
        {
            $customer = new Customer();
            $customer->getByEmail(Tools::getValue('email'), null, false);
            if (Validate::isLoadedObject($customer))
                $_POST['id_customer'] = $customer->id;
            else
                $this->errors[] = Tools::displayError('This e-mail address is not registered.');
        }
        else */
        if ($id_carrier_relay = Tools::getValue('id_carrier_relay'))
        {
            $carrier_relay = new Carrier((int)$id_carrier_relay);
            if (Validate::isLoadedObject($carrier_relay))
                $_POST['id_carrier_relay'] = $carrier_relay->id;
            else
                $this->errors[] = Tools::displayError('Unknown carrier relay');
        }
        else
            $this->errors[] = Tools::displayError('Unknown carrier relay');

        if (Country::isNeedDniByCountryId(Tools::getValue('id_country')) && !Tools::getValue('dni'))
            $this->errors[] = Tools::displayError('Identification number is incorrect or has already been used.');

        /* If the selected country does not contain states */
        $id_state = (int)Tools::getValue('id_state');
        $id_country = (int)Tools::getValue('id_country');
        $country = new Country((int)$id_country);
        if ($country && !(int)$country->contains_states && $id_state)
            $this->errors[] = Tools::displayError('You have selected a state for a country that does not contain states.');

        /* If the selected country contains states, then a state have to be selected */
        if ((int)$country->contains_states && !$id_state)
            $this->errors[] = Tools::displayError('An address located in a country containing states must have a state selected.');

        /* Check zip code */
        if ($country->need_zip_code)
        {
            $zip_code_format = $country->zip_code_format;
            if (($postcode = Tools::getValue('postcode')) && $zip_code_format)
            {
                $zip_regexp = '/^'.$zip_code_format.'$/ui';
                $zip_regexp = str_replace(' ', '( |)', $zip_regexp);
                $zip_regexp = str_replace('-', '(-|)', $zip_regexp);
                $zip_regexp = str_replace('N', '[0-9]', $zip_regexp);
                $zip_regexp = str_replace('L', '[a-zA-Z]', $zip_regexp);
                $zip_regexp = str_replace('C', $country->iso_code, $zip_regexp);
                if (!preg_match($zip_regexp, $postcode))
                    $this->errors[] = Tools::displayError('Your Postal Code/Zip Code is incorrect.').'<br />'.
                        Tools::displayError('Must be typed as follows:').' '.
                        str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $zip_code_format)));
            }
            else if ($zip_code_format)
                $this->errors[] = Tools::displayError('Postal Code/Zip Code required.');
            else if ($postcode && !preg_match('/^[0-9a-zA-Z -]{4,9}$/ui', $postcode))
                $this->errors[] = Tools::displayError('Your Postal Code/Zip Code is incorrect.');
        }

        /* If this address come from order's edition and is the same as the other one (invoice or delivery one)
        ** we delete its id_address to force the creation of a new one */
        if ((int)Tools::getValue('id_order'))
        {
            $this->_redirect = false;
            if (isset($_POST['address_type']))
                $_POST['id_address'] = '';
        }

        // Check the requires fields which are settings in the BO
        $address = new Address();
        $this->errors = array_merge($this->errors, $address->validateFieldsRequiredDatabase());

        if (empty($this->errors))
            return parent::processSave();
        else
            // if we have errors, we stay on the form instead of going back to the list
            $this->display = 'edit';

        /* Reassignation of the order's new (invoice or delivery) address */
        $address_type = ((int)Tools::getValue('address_type') == 2 ? 'invoice' : ((int)Tools::getValue('address_type') == 1 ? 'delivery' : ''));
        if ($this->action == 'save' && ($id_order = (int)Tools::getValue('id_order')) && !count($this->errors) && !empty($address_type))
        {
            if (!Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'orders SET `id_address_'.$address_type.'` = '.Db::getInstance()->Insert_ID().' WHERE `id_order` = '.$id_order))
                $this->errors[] = Tools::displayError('An error occurred while linking this address to its order.');
            else
                Tools::redirectAdmin(Tools::getValue('back').'&conf=4');
        }
    }

    public function processAdd()
    {
        if (Tools::getValue('submitFormAjax'))
            $this->redirect_after = false;

        return parent::processAdd();
    }

    /**
     * Get Address formats used by the country where the address id retrieved from POST/GET is.
     *
     * @return array address formats
     */
    protected function processAddressFormat()
    {
        $tmp_addr = new Address((int)Tools::getValue('id_address'));

        $selected_country = ($tmp_addr && $tmp_addr->id_country) ? $tmp_addr->id_country : (int)Configuration::get('PS_COUNTRY_DEFAULT');

        $inv_adr_fields = AddressFormat::getOrderedAddressFields($selected_country, false, true);
        $dlv_adr_fields = AddressFormat::getOrderedAddressFields($selected_country, false, true);

        $inv_all_fields = array();
        $dlv_all_fields = array();

        $out = array();

        foreach (array('inv','dlv') as $adr_type)
        {
            foreach (${$adr_type.'_adr_fields'} as $fields_line)
                foreach (explode(' ', $fields_line) as $field_item)
                    ${$adr_type.'_all_fields'}[] = trim($field_item);

            $out[$adr_type.'_adr_fields'] = ${$adr_type.'_adr_fields'};
            $out[$adr_type.'_all_fields'] = ${$adr_type.'_all_fields'};
        }

        return $out;
    }

    /**
     * Method called when an ajax request is made
     * @see AdminController::postProcess()
     */
    public function ajaxProcess()
    {
        if (Tools::isSubmit('id_carrier_relay'))
        {
            $id_carrier_relay = pSQL(Tools::getValue('id_carrier_relay'));
            $carrier_relay = new Carrier($id_carrier_relay);
            if (!empty($carrier_relay))
            {
                echo Tools::jsonEncode(array('carrier_name' => $carrier_relay->name));
            }
        }
        die;
    }
}
