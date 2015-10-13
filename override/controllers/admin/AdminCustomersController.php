<?php

class AdminCustomersController extends AdminCustomersControllerCore
{
	
	public function __construct()
	{
		parent::__construct();
		
		$this->required_database = true;
		$this->required_fields = array('newsletter','optin');
		$this->table = 'customer';
		$this->className = 'Customer';
		$this->lang = false;
		$this->deleted = true;
		$this->explicitSelect = true;

		$this->context = Context::getContext();

		$this->default_form_language = $this->context->language->id;

		$genders = array();
		$genders_icon = array('default' => 'unknown.gif');
		foreach (Gender::getGenders() as $gender)
		{
			$gender_file = 'genders/'.$gender->id.'.jpg';
			if (file_exists(_PS_IMG_DIR_.$gender_file))
				$genders_icon[$gender->id] = '../'.$gender_file;
			else
				$genders_icon[$gender->id] = $gender->name;
			$genders[$gender->id] = $gender->name;
		}
        // SELECT lcdb_address.phone as phone, lcdb_address.phone_mobile as phone_mobile, lcdb_address.postcode as postcode FROM lcdb_address INNER JOIN lcdb_customer ON lcdb_address.id_customer = lcdb_customer.id_customer,
		$this->_select = '
		a.date_add,
		IF (YEAR(`birthday`) = 0, "-", (YEAR(CURRENT_DATE)-YEAR(`birthday`)) - (RIGHT(CURRENT_DATE, 5) < RIGHT(birthday, 5))) AS `age`, (
			SELECT c.date_add FROM '._DB_PREFIX_.'guest g
			LEFT JOIN '._DB_PREFIX_.'connections c ON c.id_guest = g.id_guest
			WHERE g.id_customer = a.id_customer
			ORDER BY c.date_add DESC
			LIMIT 1
		) as connect,
		(SELECT GROUP_CONCAT(distinct gl.name SEPARATOR " | ") FROM `'._DB_PREFIX_.'group_lang` gl
		LEFT JOIN '._DB_PREFIX_.'customer_group cg ON gl.id_group = cg.id_group
		WHERE cg.id_customer = a.id_customer) as userGroup,
		(SELECT count(o.id_order) FROM `'._DB_PREFIX_.'orders` o
		WHERE o.id_customer = a.id_customer) as orderDone,
		a.note as memo';


		$this->fields_list = array(
			'id_customer' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 20
			),
			'id_gender' => array(
				'title' => $this->l('Titles'),
				'width' => 70,
				'align' => 'center',
				'icon' => $genders_icon,
				'orderby' => false,
				'type' => 'select',
				'list' => $genders,
				'filter_key' => 'a!id_gender',
			),
			'lastname' => array(
				'title' => $this->l('Last Name'),
				'width' => 100
			),
			'firstname' => array(
				'title' => $this->l('First name'),
				'width' => 100
			),
			'email' => array(
				'title' => $this->l('E-mail address'),
				'width' => 140,
			),
			'userGroup' => array(
				'title' => $this->l('Group'),
				'width' => "auto",
				'havingFilter' => true
			),
            /*
            'phone' => array(
                'title' => $this->l('Tel1'),
                'width' => "auto",
                'havingFilter' => true
            ),
            'phone_mobile' => array(
                'title' => $this->l('Tel2'),
                'width' => "auto",
                'havingFilter' => true
            ),
            'postcode' => array(
                'title' => $this->l('CP'),
                'width' => "auto",
                'havingFilter' => true
            ),
            */
			'orderDone' => array(
				'title' => $this->l('Commandes'),
				'width' => 60,
			),
			// 'orderNext' => array(
			// 	'title' => $this->l('Commandes à venir'),
			// 	'width' => 140,
			// ),
			//'note' => array(
			//	'title' => $this->l('Note'),
			//	'width' => 'auto',
            //    'filter_key' => 'a!note',
			//),
			'active' => array(
				'title' => $this->l('Enabled'),
				'width' => 70,
				'align' => 'center',
				'active' => 'status',
				'type' => 'bool',
				'orderby' => false,
				'filter_key' => 'a!active',
			),
			//'date_add' => array(
			//	'title' => $this->l('Registration'),
			//	'width' => 150,
			//	'type' => 'date',
			//	'align' => 'right'
			//),
			//'connect' => array(
			//	'title' => $this->l('Last visit'),
			//	'width' => 100,
			//	'type' => 'datetime',
			//	'search' => false,
			//	'havingFilter' => true
			//)
		);

	}


    public function renderForm()
    {
        if (!($obj = $this->loadObject(true)))
            return;

        $genders = Gender::getGenders();
        $list_genders = array();
        foreach ($genders as $key => $gender)
        {
            $list_genders[$key]['id'] = 'gender_'.$gender->id;
            $list_genders[$key]['value'] = $gender->id;
            $list_genders[$key]['label'] = $gender->name;
        }

        $years = Tools::dateYears();
        $months = Tools::dateMonths();
        $days = Tools::dateDays();

        $groups = Group::getGroups($this->default_form_language, true);
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Customer'),
                'image' => '../img/admin/tab-customers.gif'
            ),
            'input' => array(
                array(
                    'type' => 'radio',
                    'label' => $this->l('Titles:'),
                    'name' => 'id_gender',
                    'required' => false,
                    'class' => 't',
                    'values' => $list_genders
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('First name:'),
                    'name' => 'firstname',
                    'size' => 33,
                    'required' => true,
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Last name:'),
                    'name' => 'lastname',
                    'size' => 33,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('E-mail address:'),
                    'name' => 'email',
                    'size' => 33,
                    'required' => true
                ),
                array(
                    'type' => 'password',
                    'label' => $this->l('Password:'),
                    'name' => 'passwd',
                    'size' => 33,
                    'required' => ($obj->id ? false : true),
                    'desc' => ($obj->id ? $this->l('Leave blank if no change') : $this->l('5 characters min., only letters, numbers, or').' -_')
                ),
                array(
                    'type' => 'birthday',
                    'label' => $this->l('Birthday:'),
                    'name' => 'birthday',
                    'options' => array(
                        'days' => $days,
                        'months' => $months,
                        'years' => $years
                    )
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Allow or disallow this customer to log in')
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Newsletter:'),
                    'name' => 'newsletter',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'newsletter_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'newsletter_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Customer will receive your newsletter via e-mail')
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Opt-in:'),
                    'name' => 'optin',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'optin_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'optin_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Customer will receive your ads via e-mail')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Note / Memo:'),
                    'name' => 'note',
                    'size' => 50,
                    'required' => false,
                    'desc' => $this->l('Mémo pour la préparation des colis du client')
                ),
            )
        );

        // if we add a customer via fancybox (ajax), it's a customer and he doesn't need to be added to the visitor and guest groups
        if (Tools::isSubmit('addcustomer') && Tools::isSubmit('submitFormAjax'))
        {
            $visitor_group = Configuration::get('PS_UNIDENTIFIED_GROUP');
            $guest_group = Configuration::get('PS_GUEST_GROUP');
            foreach ($groups as $key => $g)
                if (in_array($g['id_group'], array($visitor_group, $guest_group)))
                    unset($groups[$key]);
        }

        $this->fields_form['input'] = array_merge($this->fields_form['input'],
            array(
                array(
                    'type' => 'group',
                    'label' => $this->l('Group access:'),
                    'name' => 'groupBox',
                    'values' => $groups,
                    'required' => true,
                    'desc' => $this->l('Select all customer groups you would like to apply to this customer')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Default customer group:'),
                    'name' => 'id_default_group',
                    'options' => array(
                        'query' => $groups,
                        'id' => 'id_group',
                        'name' => 'name'
                    ),
                    'hint' => $this->l('The group will be as applied by default.'),
                    'desc' => $this->l('Apply the discount\'s price of this group.')
                )
            )
        );

        // if customer is a guest customer, password hasn't to be there
        if ($obj->id && ($obj->is_guest && $obj->id_default_group == Configuration::get('PS_GUEST_GROUP')))
        {
            foreach ($this->fields_form['input'] as $k => $field)
                if ($field['type'] == 'password')
                    array_splice($this->fields_form['input'], $k, 1);
        }

        if (Configuration::get('PS_B2B_ENABLE'))
        {
            $risks = Risk::getRisks();

            $list_risks = array();
            foreach ($risks as $key => $risk)
            {
                $list_risks[$key]['id_risk'] = (int)$risk->id;
                $list_risks[$key]['name'] = $risk->name;
            }

            $this->fields_form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Company:'),
                'name' => 'company',
                'size' => 33
            );
            $this->fields_form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('SIRET:'),
                'name' => 'siret',
                'size' => 14
            );
            $this->fields_form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('APE:'),
                'name' => 'ape',
                'size' => 5
            );
            $this->fields_form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Website:'),
                'name' => 'website',
                'size' => 33
            );
            $this->fields_form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Outstanding allowed:'),
                'name' => 'outstanding_allow_amount',
                'size' => 10,
                'hint' => $this->l('Valid characters:').' 0-9',
                'suffix' => '¤'
            );
            $this->fields_form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Max payment days:'),
                'name' => 'max_payment_days',
                'size' => 10,
                'hint' => $this->l('Valid characters:').' 0-9'
            );
            $this->fields_form['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Risk:'),
                'name' => 'id_risk',
                'required' => false,
                'class' => 't',
                'options' => array(
                    'query' => $list_risks,
                    'id' => 'id_risk',
                    'name' => 'name'
                ),
            );
        }

        $this->fields_form['submit'] = array(
            'title' => $this->l('   Save   '),
            'class' => 'button'
        );

        $birthday = explode('-', $this->getFieldValue($obj, 'birthday'));

        $this->fields_value = array(
            'years' => $this->getFieldValue($obj, 'birthday') ? $birthday[0] : 0,
            'months' => $this->getFieldValue($obj, 'birthday') ? $birthday[1] : 0,
            'days' => $this->getFieldValue($obj, 'birthday') ? $birthday[2] : 0,
        );

        // Added values of object Group
        $customer_groups = $obj->getGroups();
        $customer_groups_ids = array();
        if (is_array($customer_groups))
            foreach ($customer_groups as $customer_group)
                $customer_groups_ids[] = $customer_group;

        // if empty $carrier_groups_ids : object creation : we set the default groups
        if (empty($customer_groups_ids))
        {
            $preselected = array(Configuration::get('PS_UNIDENTIFIED_GROUP'), Configuration::get('PS_GUEST_GROUP'), Configuration::get('PS_CUSTOMER_GROUP'));
            $customer_groups_ids = array_merge($customer_groups_ids, $preselected);
        }

        foreach ($groups as $group)
            $this->fields_value['groupBox_'.$group['id_group']] =
                Tools::getValue('groupBox_'.$group['id_group'], in_array($group['id_group'], $customer_groups_ids));

        //get the grandParent class
        //AdminCustomerControllerCore (parent of $this) reset the field, so we cant edit or customise the fields
        //we need to pass its renderForm and directly call the grand parent renderForm.
        $adminCustomerCoreClass = Tools::get_grandparent_class($this);
        return $adminCustomerCoreClass::renderForm();
    }
}

