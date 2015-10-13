<?php

class AdminPostControllerCore extends AdminController
{
	protected $category;

//	public $id_post_category;

	protected $position_identifier = 'id_post';

	public function __construct()
	{
		$this->table = 'post';
		$this->className = 'Post';
		$this->lang = true;
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->fields_list = array(
			'id_post' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'title' => array('title' => $this->l('Title'), 'width' => '300', 'filter_key' => 'b!title'),
			'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'position', 'align' => 'center', 'position' => 'position'),
			'active' => array('title' => $this->l('Displayed'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
			);

		$this->_select = 'a.position ';
		$this->fieldImageSettings = array('name' => 'image', 'dir' => 'po');

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
				'title' => $this->l('Post Page'),
				'image' => '../img/admin/tab-categories.gif'
			),
			'input' => array(
				// custom template
				array(
					'type' => 'text',
					'label' => $this->l('Title:'),
					'name' => 'title',
					'lang' => true,
					'required' => true,
					'class' => 'copy2friendlyUrl',
					'size' => 50
				),
				array(
					'type' => 'file',
					'label' => $this->l('Image:'),
					'name' => 'image',
					'desc' => array(
						$this->l("Upload article's cover")
					)
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Article'),
					'name' => 'content',
					'autoload_rte' => true,
					'lang' => true,
					'rows' => 5,
					'cols' => 40,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Link:'),
					'name' => 'link',
					'lang' => true,
					'size' => 50
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
		
		$image = '../img/'.$this->fieldImageSettings['dir'].'/'.(int)$obj->id.'.jpg';

		$this->tpl_form_vars = array(
			'active' => $this->object->active,
			'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL')
		);
		return parent::renderForm();
	}

	public function renderList()
	{
		$this->toolbar_title = $this->l('Posts');
		$this->toolbar_btn['new'] = array(
			'href' => self::$currentIndex.'&amp;add'.$this->table.'&amp;token='.$this->token,
			'desc' => $this->l('Add new')
		);

		return parent::renderList();
	}

	public function displayList($token = null)
	{
		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader($token);
		if (!count($this->_list))
			echo '<tr><td class="center" colspan="'.(count($this->fields_list) + 2).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent($token);

		/* Close list table and submit button */
		$this->displayListFooter($token);
	}

	/**
	 * Modifying initial getList method to display position feature (drag and drop)
	 */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		if ($order_by && $this->context->cookie->__get($this->table.'Orderby'))
			$order_by = $this->context->cookie->__get($this->table.'Orderby');
		else
			$order_by = 'position';

		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
	}

	public function postProcess()
	{
		if (Tools::isSubmit('viewpost') && ($id_post = (int)Tools::getValue('id_post')) && ($post = new Post($id_post, $this->context->language->id)) && Validate::isLoadedObject($post))
		{
			$redir = $this->context->link->getPostLink($post);
			if (!$post->active)
			{
				$admin_dir = dirname($_SERVER['PHP_SELF']);
				$admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);
				$redir .= '?adtoken='.Tools::getAdminTokenLite('AdminPostContent').'&ad='.$admin_dir.'&id_employee='.(int)$this->context->employee->id;
			}
			Tools::redirectAdmin($redir);
		}
		elseif (Tools::isSubmit('deletepost'))
		{
			if (Tools::getValue('id_post') == Configuration::get('PS_CONDITIONS_POST_ID'))
			{
				Configuration::updateValue('PS_CONDITIONS', 0);
				Configuration::updateValue('PS_CONDITIONS_POST_ID', 0);
			}
			$post = new Post((int)Tools::getValue('id_post'));
			if (!$post->delete())
				$this->errors[] = Tools::displayError('An error occurred while deleting object.')
					.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
			else
				Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.Tools::getAdminTokenLite('AdminPostContent'));
		}/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (Tools::isSubmit($this->table.'Box'))
				{
					$post = new Post();
					$result = true;
					$result = $post->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$token = Tools::getAdminTokenLite('AdminPostContent');
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
		elseif (Tools::isSubmit('submitAddpost'))
		{
			parent::validateRules();
			if (!count($this->errors))
			{
				if (!$id_post = (int)Tools::getValue('id_post'))
				{
					$post = new Post();
					$this->copyFromPost($post, 'post');
					
					if (!$post->add())
						$this->errors[] = Tools::displayError('An error occurred while creating object.')
							.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($post->id);
						
					if (isset($_FILES['image']) && !$_FILES['image']['error'])
					{
						if ($_FILES['image']['error'] == UPLOAD_ERR_OK)
							$this->copyNoPictureImage($post->id);
						// class AdminTab deal with every $_FILES content, don't do that for no-picture
						unset($_FILES['image']);
						parent::postProcess();
					}
				}
				else
				{
					$post = new Post($id_post);
					$this->copyFromPost($post, 'post');
					if (!$post->update())
						$this->errors[] = Tools::displayError('An error occurred while updating object.')
						.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($post->id);
						
						
					if (!Validate::isLoadedObject($object = $this->loadObject()))
						$this->errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
					if ((int)$object->id == (int)Configuration::get('PS_LANG_DEFAULT') && (int)$_POST['active'] != (int)$object->active)
						$this->errors[] = Tools::displayError('You cannot change the status of the default language.');
					else{
						if (!empty($_FILES['image']['tmp_name']))
						{
							if ($_FILES['image']['error'] == UPLOAD_ERR_OK)
								$this->copyNoPictureImage($post->id);
							// class AdminTab deal with every $_FILES content, don't do that for no-picture
							unset($_FILES['image']);
							parent::postProcess();
						}
						else
						{
							$this->validateRules();
							$this->errors[] = Tools::displayError('Image fields are required.');
						}
					}

					$this->validateRules();
					

				}
				Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.Tools::getAdminTokenLite('AdminPost'));
					
			}
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('statuspost') && Tools::isSubmit($this->identifier))
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
	
	public function copyNoPictureImage($title)
	{
		if (isset($_FILES['image']) && $_FILES['image']['error'] === 0)
			if ($error = ImageManager::validateUpload($_FILES['image'], Tools::getMaxUploadSize()))
				$this->errors[] = $error;
			else
			{
				if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($_FILES['image']['tmp_name'], $tmp_name))
					return false;
				if (!ImageManager::resize($tmp_name, _PS_IMG_DIR_.'po/'.$title.'.jpg'))
					$this->errors[] = Tools::displayError('An error occurred while copying image to your product folder.');
				else
				{
					$images_types = ImageType::getImagesTypes('products');
					foreach ($images_types as $k => $image_type)
					{
						if (!ImageManager::resize($tmp_name, _PS_IMG_DIR_.'po/'.$title.'-default-'.stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']))
							$this->errors[] = Tools::displayError('An error occurred while resizing image to your product directory.');
					}
				}
				unlink($tmp_name);
			}
	}
}


