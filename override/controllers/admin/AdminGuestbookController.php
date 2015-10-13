<?php

class AdminGuestbookControllerCore extends AdminController
{
	protected $category;

	//	public $id_guestbook_category;

	protected $position_identifier = 'id_guestbook';

	public function __construct()
	{
		$this->table = 'guestbook';
		$this->className = 'Guestbook';
		$this->lang = true;
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->fields_list = array(
			'id_guestbook' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'firstname' => array('title' => $this->l('Firstname'), 'width' => '100', 'filter_key' => 'b!title'),
			'lastname' => array('title' => $this->l('Lastname'), 'width' => '100', 'filter_key' => 'b!title'),
			'message' => array('title' => $this->l('Message'), 'width' => '300', 'filter_key' => 'b!title'),
			'active' => array('title' => $this->l('Displayed'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
			);

		parent::__construct();

	}

	public function renderForm()
	{
		$this->display = 'edit';
		$this->initToolbar();
		if (!$this->loadObject(true))
			return;

		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
			'title' => $this->l('Guestbook Page'),
			'image' => '../img/admin/tab-categories.gif'
			),
		'input' => array(
			// custom template
		array(
			'type' => 'text',
			'label' => $this->l('Firstname:'),
			'name' => 'firstname',
			'required' => true,
			'size' => 50
			),
		array(
			'type' => 'text',
			'label' => $this->l('Lastname:'),
			'name' => 'lastname',
			'required' => true,
			'size' => 50
			),
		array(
			'type' => 'text',
			'label' => $this->l('Email:'),
			'name' => 'email',
			'required' => true,
			'size' => 50
			),
		array(
			'type' => 'text',
			'label' => $this->l('City:'),
			'name' => 'city',
			'lang' => true,
			'required' => true,
			'size' => 50
			),
		array(
			'type' => 'textarea',
			'label' => $this->l('Message'),
			'name' => 'message',
			'autoload_rte' => true,
			'lang' => true,
			'rows' => 5,
			'cols' => 40,
			'hint' => $this->l('Invalid characters:').' <>;=#{}'
		),
		array(
			'type' => 'radio',
			'label' => $this->l('Displayed:'),
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
			),
			),
		'submit' => array(
			'title' => $this->l('   Save   '),
			'class' => 'button'
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

		$this->tpl_form_vars = array(
			'active' => $this->object->active,
			'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL')
			);

		return parent::renderForm();
	}

	public function renderList()
	{
		$this->toolbar_title = $this->l('Guestbooks');
		$this->toolbar_btn['new'] = array(
			'href' => self::$currentIndex.'&amp;add'.$this->table.'&amp;token='.$this->token,
		'desc' => $this->l('Add new')
			);

		return parent::renderList();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('viewguestbook') && ($id_guestbook = (int)Tools::getValue('id_guestbook')) && ($guestbook = new Guestbook($id_guestbook, $this->context->language->id)) && Validate::isLoadedObject($guestbook))
		{
			$redir = $this->context->link->getGuestbookLink($guestbook);
			if (!$guestbook->active)
			{
				$admin_dir = dirname($_SERVER['PHP_SELF']);
				$admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);
				$redir .= '?adtoken='.Tools::getAdminTokenLite('AdminGuestbookContent').'&ad='.$admin_dir.'&id_employee='.(int)$this->context->employee->id;
			}
			Tools::redirectAdmin($redir);
		}
		elseif (Tools::isSubmit('deleteguestbook'))
		{
			if (Tools::getValue('id_guestbook') == Configuration::get('PS_CONDITIONS_POST_ID'))
			{
				Configuration::updateValue('PS_CONDITIONS', 0);
				Configuration::updateValue('PS_CONDITIONS_POST_ID', 0);
			}
			$guestbook = new Guestbook((int)Tools::getValue('id_guestbook'));
			if (!$guestbook->delete())
				$this->errors[] = Tools::displayError('An error occurred while deleting object.')
				.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
			else
				Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.Tools::getAdminTokenLite('AdminGuestbook'));
		}/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (Tools::isSubmit($this->table.'Box'))
				{
					$guestbook = new Guestbook();
					$result = true;
					$result = $guestbook->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$token = Tools::getAdminTokenLite('AdminGuestbook');
						Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$token);
					}
					$this->errors[] = Tools::displayError('An error occurred while deleting selection.');

				}
				else
					$this->errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddguestbook') || Tools::isSubmit('submitAddguestbookAndPreview'))
		{
			parent::validateRules();
			if (!count($this->errors))
			{
				if (!$id_guestbook = (int)Tools::getValue('id_guestbook'))
				{
					$guestbook = new Guestbook();
					$this->copyFromPost($guestbook, 'guestbook');
					if (!$guestbook->add())
						$this->errors[] = Tools::displayError('An error occurred while creating object.')
						.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($guestbook->id);
				}
				else
				{
					$guestbook = new Guestbook($id_guestbook);
					$this->copyFromPost($guestbook, 'guestbook');
					if (!$guestbook->update())
						$this->errors[] = Tools::displayError('An error occurred while updating object.')
						.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($guestbook->id);

				}
				if (Tools::isSubmit('submitAddguestbookAndPreview'))
				{
					$alias = $this->getFieldValue($guestbook, 'link_rewrite', $this->context->language->id);
					$preview_url = $this->context->link->getGuestbookLink($guestbook, $alias, $this->context->language->id);

					if (!$guestbook->active)
					{
						$admin_dir = dirname($_SERVER['PHP_SELF']);
						$admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);

						$params = http_build_query(array(
							'adtoken' => Tools::getAdminTokenLite('AdminGuestbookContent'),
							'ad' => $admin_dir,
							'id_employee' => (int)$this->context->employee->id)
							);
						if (Configuration::get('PS_REWRITING_SETTINGS'))
							$params = '?'.$params;
						else
							$params = '&'.$params;

						$preview_url .= $guestbook->active ? '' : $params;
					}
					Tools::redirectAdmin($preview_url);
				}
				else
					Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.Tools::getAdminTokenLite('AdminGuestbook'));
			}
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('statusguestbook') && Tools::isSubmit($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getValue('token'));
					else
						$this->errors[] = Tools::displayError('An error occurred while updating status.');
				}
				else
					$this->errors[] = Tools::displayError('An error occurred while updating status for object.')
					.' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');

		}
		else
			parent::postProcess(true);
	}
}

