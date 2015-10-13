<?php

class AdminGroupsController extends AdminGroupsControllerCore
{
	public function __construct()
	{
		
		parent::__construct();
			
		$this->table = 'group';
		$this->className = 'Group';
		$this->lang = true;
		
		$groups_to_keep = array(
			Configuration::get('PS_UNIDENTIFIED_GROUP'),
			Configuration::get('PS_GUEST_GROUP'),
			Configuration::get('PS_CUSTOMER_GROUP')
		);
		
		$this->_select = '
		(SELECT COUNT(jcg.`id_customer`)
		FROM `'._DB_PREFIX_.'customer_group` jcg
		LEFT JOIN `'._DB_PREFIX_.'customer` jc ON (jc.`id_customer` = jcg.`id_customer`)
		WHERE jc.`deleted` != 1
		AND jcg.`id_group` = a.`id_group`) AS nb, a.is_group as isGroup' ;

		$this->fields_list = array(
			'id_group' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'filter_key' => 'b!name'
			),
			'reduction' => array(
				'title' => $this->l('Discount (%)'),
				'width' => 100,
				'align' => 'right',
				'type' => 'percent'
			),
			'nb' => array(
				'title' => $this->l('Members'),
				'width' => 25,
				'align' => 'center',
				'havingFilter' => true,
			),
			'isGroup' => array(
				'title' => $this->l('Groupment'),
				'width' => 25,
				'align' => 'center',
				'type' => 'bool',
				'callback' => 'printGroupmentIcon',
				'havingFilter' => true
			),
			'show_prices' => array(
				'title' => $this->l('Show prices'),
				'width' => 120,
				'align' => 'center',
				'type' => 'bool',
				'callback' => 'printShowPricesIcon',
				'orderby' => false
			),
			'date_add' => array(
				'title' => $this->l('Creation date'),
				'width' => 150,
				'type' => 'date',
				'align' => 'right'
			)
		);

		$this->addRowActionSkipList('delete', $groups_to_keep);

	}

	public function renderForm()
	{
		if (!($group = $this->loadObject(true)))
			return;

		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Customer group'),
				'image' => '../img/admin/tab-groups.gif'
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				'class' => 'button'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 33,
					'required' => true,
					'lang' => true,
					'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Discount (%):'),
					'name' => 'reduction',
					'size' => 33,
					'desc' => $this->l('Will automatically apply this value as a discount on all products for members of this customer group.')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Groupment:'),
					'name' => 'is_group',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'is_group_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'is_group_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Price display method:'),
					'name' => 'price_display_method',
					'desc' => $this->l('How prices are displayed in the order summary for this customer group.'),
					'options' => array(
						'query' => array(
							array(
								'id_method' => PS_TAX_EXC,
								'name' => $this->l('Tax excluded')
							),
							array(
								'id_method' => PS_TAX_INC,
								'name' => $this->l('Tax included')
							)
						),
						'id' => 'id_method',
						'name' => 'name'
					)
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Show prices:'),
					'name' => 'show_prices',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'show_prices_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'show_prices_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Customers in this group can view price')
				),
				array(
					'type' => 'group_discount_category',
					'label' => $this->l('Category discount:'),
					'name' => 'reduction',
					'size' => 33,
					'values' => ($group->id ? $this->formatCategoryDiscountList((int)$group->id) : array())
				),
				array(
					'type' => 'modules',
					'label' => array('auth_modules' => $this->l('Authorized modules:'), 'unauth_modules' => $this->l('Unauthorized modules:')),
					'name' => 'auth_modules',
					'values' => $this->formatModuleListAuth($group->id)
				)
			)
		);

		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'shop',
				'label' => $this->l('Shop association:'),
				'name' => 'checkBoxShopAsso',
			);
		}

		$this->fields_value['reduction'] = isset($group->reduction) ? $group->reduction : 0;

		$helper = new Helper();
		$this->tpl_form_vars['categoryTreeView'] = $helper->renderCategoryTree(null, array(), 'id_category', true, false, array(), true, true);

		return AdminController::renderForm();
	}
	
	public static function printGroupmentIcon($id_group, $tr)
	{
		$group = new Group($tr['id_group']);
		if (!Validate::isLoadedObject($group))
			return;
		return ($group->is_group ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />');
	}
}

