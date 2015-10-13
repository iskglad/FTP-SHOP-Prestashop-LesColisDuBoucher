<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminRecipeControllerCore extends AdminController
{
	protected $category;

	public $id_recipe_category;

	protected $position_identifier = 'id_recipe';

	public function __construct()
	{
		$this->table = 'recipe';
		$this->className = 'Recipe';
		$this->lang = true;
		$this->addRowAction('view');
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->fields_list = array(
			'id_recipe' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'title' => array('title' => $this->l('Title'), 'width' => '300', 'filter_key' => 'b!title'),
			'difficulty' => array('title' => $this->l('Difficulty'), 'width' => '50', 'filter_key' => 'b!difficulty'),
			'number_people' => array('title' => $this->l('Number of people'), 'width' => '50', 'filter_key' => 'b!number_people'),
			'duration' => array('title' => $this->l('Duration'), 'width' => '50', 'filter_key' => 'b!duration'),
			'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'position', 'align' => 'center', 'position' => 'position'),
			'active' => array('title' => $this->l('Displayed'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
			);

		// The controller can't be call directly
		// In this case, AdminCmsContentController::getCurrentCMSCategory() is null
		if (!AdminRecipeContentController::getCurrentCMSCategory())
		{
			$this->redirect_after = '?controller=AdminCmsContent&token='.Tools::getAdminTokenLite('AdminCmsContent');
			$this->redirect();
		}

		$this->_category = AdminRecipeContentController::getCurrentCMSCategory();
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'recipe_category` c ON (c.`id_recipe_category` = a.`id_recipe_category`)';
		$this->_select = 'a.position ';
		$this->_filter = 'AND c.id_recipe_category = '.(int)$this->_category->id;

		parent::__construct();
	}

	public function renderForm()
	{
		$this->display = 'edit';
		$this->toolbar_btn['save-and-preview'] = array(
			'href' => '#',
			'desc' => $this->l('Save and preview')
		);
		$this->initToolbar();
		if (!$this->loadObject(true))
			return;

		$categories = RecipeCategory::getCategories($this->context->language->id, false);
		$html_categories = RecipeCategory::recurseCMSCategory($categories, $categories[0][1], 1, $this->getFieldValue($this->object, 'id_recipe_category'), 1);
		
		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('CMS Page'),
				'image' => '../img/admin/tab-categories.gif'
			),
			'input' => array(
				// custom template
				array(
					'type' => 'select_category',
					'label' => $this->l('CMS Category'),
					'name' => 'id_recipe_category',
					'options' => array(
						'html' => $html_categories,
					),
				),
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
					'type' => 'text',
					'label' => $this->l('Type of meat:'),
					'name' => 'type_meat',
					'lang' => true,
					'size' => 50
				),
				array(
					'type' => 'text',
					'label' => $this->l('Type of cooking:'),
					'name' => 'type_cooking',
					'lang' => true,
					'size' => 5
				),
				array(
					'type' => 'text',
					'label' => $this->l('level:'),
					'name' => 'difficulty',
					'lang' => true,
					'size' => 5
				), 
			/*	array(
					'type' => 'select',
					'label' => $this->l('Level of difficulty:'),
					'name' => 'difficulty',
					'required' => true,
					'options' => array(
						'query' => $difficulties,
						'id' => 'id',
						'name' => 'name'
					)
				),  */
				array(
					'type' => 'text',
					'label' => $this->l('Number of people:'),
					'name' => 'number_people',
					'lang' => true,
					'size' => 10
				),
				array(
					'type' => 'text',
					'label' => $this->l('Duration:'),
					'name' => 'duration',
					'lang' => true,
					'size' => 10
				),
				array(
					'type' => 'text',
					'label' => $this->l('Cooking time:'),
					'name' => 'cooking_time',
					'lang' => true,
					'size' => 10
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Prior'),
					'name' => 'prior_content',
					'autoload_rte' => true,
					'lang' => true,
					'rows' => 5,
					'cols' => 40,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Ingredients'),
					'name' => 'ingredients_content',
					'autoload_rte' => true,
					'lang' => true,
					'rows' => 5,
					'cols' => 40,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Recipe'),
					'name' => 'recipe_content',
					'autoload_rte' => true,
					'lang' => true,
					'rows' => 10,
					'cols' => 40,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Tips'),
					'name' => 'tips_content',
					'autoload_rte' => true,
					'lang' => true,
					'rows' => 5,
					'cols' => 40,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta title:'),
					'name' => 'meta_title',
					'id' => 'name', // for copy2friendlyUrl compatibility
					'lang' => true,
					'required' => true,
					'class' => 'copy2friendlyUrl',
					'hint' => $this->l('Invalid characters:').' <>;=#{}',
					'size' => 50
				),
				array(
					'type' => 'text',
					'label' => $this->l('Friendly URL'),
					'name' => 'link_rewrite',
					'required' => true,
					'lang' => true,
					'hint' => $this->l('Only letters and the minus (-) character are allowed')
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
		$this->toolbar_title = $this->l('Pages in this category');
		$this->toolbar_btn['new'] = array(
			'href' => self::$currentIndex.'&amp;add'.$this->table.'&amp;id_recipe_category='.(int)$this->id_recipe_category.'&amp;token='.$this->token,
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
		if (Tools::isSubmit('viewrecipe') && ($id_recipe = (int)Tools::getValue('id_recipe')) && ($recipe = new Recipe($id_recipe, $this->context->language->id)) && Validate::isLoadedObject($recipe))
		{
			$redir = $this->context->link->getRecipeLink($recipe);
			if (!$recipe->active)
			{
				$admin_dir = dirname($_SERVER['PHP_SELF']);
				$admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);
				$redir .= '?adtoken='.Tools::getAdminTokenLite('AdminRecipeContent').'&ad='.$admin_dir.'&id_employee='.(int)$this->context->employee->id;
			}
			Tools::redirectAdmin($redir);
		}
		elseif (Tools::isSubmit('deleterecipe'))
		{
			if (Tools::getValue('id_recipe') == Configuration::get('PS_CONDITIONS_CMS_ID'))
			{
				Configuration::updateValue('PS_CONDITIONS', 0);
				Configuration::updateValue('PS_CONDITIONS_CMS_ID', 0);
			}
			$recipe = new Recipe((int)Tools::getValue('id_recipe'));
			$recipe->cleanPositions($recipe->id_recipe_category);
			if (!$recipe->delete())
				$this->errors[] = Tools::displayError('An error occurred while deleting object.')
					.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
			else
				Tools::redirectAdmin(self::$currentIndex.'&id_recipe_category='.$recipe->id_recipe_category.'&conf=1&token='.Tools::getAdminTokenLite('AdminRecipeContent'));
		}/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (Tools::isSubmit($this->table.'Box'))
				{
					$recipe = new Recipe();
					$result = true;
					$result = $recipe->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$recipe->cleanPositions((int)Tools::getValue('id_recipe_category'));
						$token = Tools::getAdminTokenLite('AdminRecipeContent');
						Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$token.'&id_category='.(int)Tools::getValue('id_recipe_category'));
					}
					$this->errors[] = Tools::displayError('An error occurred while deleting selection.');

				}
				else
					$this->errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddrecipe') || Tools::isSubmit('submitAddrecipeAndPreview'))
		{
			parent::validateRules();
			if (!count($this->errors))
			{
				if (!$id_recipe = (int)Tools::getValue('id_recipe'))
				{
					$recipe = new Recipe();
					$this->copyFromPost($recipe, 'recipe');
					if (!$recipe->add())
						$this->errors[] = Tools::displayError('An error occurred while creating object.')
							.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($recipe->id);
				}
				else
				{
					$recipe = new Recipe($id_recipe);
					$this->copyFromPost($recipe, 'recipe');
					if (!$recipe->update())
						$this->errors[] = Tools::displayError('An error occurred while updating object.')
							.' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($recipe->id);

				}
                if (Tools::isSubmit('submitAddrecipeAndPreview'))
                {
                    $alias = $this->getFieldValue($recipe, 'link_rewrite', $this->context->language->id);
                    $preview_url = $this->context->link->getRecipeLink($recipe, $alias, $this->context->language->id);

                    if (!$recipe->active)
                    {
                    	$admin_dir = dirname($_SERVER['PHP_SELF']);
                        $admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);
                    	
                    	$params = http_build_query(array(
                    		'adtoken' => Tools::getAdminTokenLite('AdminRecipeContent'),
                    		'ad' => $admin_dir,
                    		'id_employee' => (int)$this->context->employee->id)
                    		);
                    	if (Configuration::get('PS_REWRITING_SETTINGS'))
                    		$params = '?'.$params;
                    	else
                    		$params = '&'.$params;
                    	
                    	$preview_url .= $recipe->active ? '' : $params;
                    }
                    Tools::redirectAdmin($preview_url);
                }
                else
					Tools::redirectAdmin(self::$currentIndex.'&id_recipe_category='.$recipe->id_recipe_category.'&conf=4&token='.Tools::getAdminTokenLite('AdminRecipeContent'));
			}
		}
		elseif (Tools::getValue('position'))
		{
			if ($this->tabAccess['edit'] !== '1')
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
			elseif (!Validate::isLoadedObject($object = $this->loadObject()))
				$this->errors[] = Tools::displayError('An error occurred while updating status for object.')
					.' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			elseif (!$object->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position')))
				$this->errors[] = Tools::displayError('Failed to update the position.');
			else
				Tools::redirectAdmin(self::$currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=4'.(($id_category = (int)Tools::getValue('id_recipe_category')) ? ('&id_recipe_category='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminRecipeContent'));
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('statusrecipe') && Tools::isSubmit($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin(self::$currentIndex.'&conf=5'.((int)Tools::getValue('id_recipe_category') ? '&id_recipe_category='.(int)Tools::getValue('id_recipe_category') : '').'&token='.Tools::getValue('token'));
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


