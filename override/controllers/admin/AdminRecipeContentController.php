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

class AdminRecipeContentControllerCore extends AdminController
{
	/** @var object adminCMSCategories() instance */
	protected $admin_recipe_categories;

	/** @var object adminCMS() instance */
	protected $admin_recipe;

	/** @var object Category() instance for navigation*/
	protected static $category = null;

	public function __construct()
	{
		/* Get current category */
		$id_recipe_category = (int)Tools::getValue('id_recipe_category', Tools::getValue('id_recipe_category_parent', 1));
		self::$category = new RecipeCategory($id_recipe_category);
		if (!Validate::isLoadedObject(self::$category))
			die('Category cannot be loaded');
			
		$this->table = 'recipe';
		$this->className = 'Recipe';
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
		$this->admin_recipe_categories = new AdminRecipeCategoriesController();
		$this->admin_recipe = new AdminRecipeController();

		parent::__construct();
	}

	/**
	 * Return current category
	 *
	 * @return object
	 */
	public static function getCurrentCMSCategory()
	{
		return self::$category;
	}

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
		$this->admin_recipe_categories->tabAccess = $this->tabAccess;
		$this->admin_recipe->tabAccess = $this->tabAccess;
		return $result;
	}

	public function initContent()
	{
		$this->admin_recipe_categories->token = $this->token;
		$this->admin_recipe->token = $this->token;

		if ($this->display == 'edit_category')
			$this->content .= $this->admin_recipe_categories->renderForm();
		else if ($this->display == 'edit_page')
			$this->content .= $this->admin_recipe->renderForm();
		else if ($this->display == 'view_page')
			$fixme = 'fixme';// @FIXME
		else
		{
			$id_recipe_category = (int)Tools::getValue('id_recipe_category');
			if (!$id_recipe_category)
				$id_recipe_category = 1;

			// CMS categories breadcrumb
			$recipe_tabs = array('recipe_category', 'recipe');
			// Cleaning links
			$cat_bar_index = self::$currentIndex;
			foreach ($recipe_tabs as $tab)
				if (Tools::getValue($tab.'Orderby') && Tools::getValue($tab.'Orderway'))
					$cat_bar_index = preg_replace('/&'.$tab.'Orderby=([a-z _]*)&'.$tab.'Orderway=([a-z]*)/i', '', self::$currentIndex);

			$this->content .= $this->admin_recipe_categories->renderList();
			$this->admin_recipe->id_recipe_category = $id_recipe_category;
			$this->content .= $this->admin_recipe->renderList();
			$this->context->smarty->assign(array(
				'cms_breadcrumb' => getPath($cat_bar_index, $id_recipe_category, '', '', 'recipe'),
			));
		}

		$this->context->smarty->assign(array(
			'content' => $this->content
		));
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitDelrecipe')
			|| Tools::isSubmit('previewSubmitAddrecipeAndPreview')
			|| Tools::isSubmit('submitAddrecipe')
			|| Tools::isSubmit('deleterecipe')
			|| Tools::isSubmit('viewrecipe')
			|| (Tools::isSubmit('statusrecipe') && Tools::isSubmit('id_recipe'))
			|| (Tools::isSubmit('way') && Tools::isSubmit('id_recipe')) && (Tools::isSubmit('position')))
			$this->admin_recipe->postProcess();
		elseif (Tools::isSubmit('submitDelrecipe_category')
			|| Tools::isSubmit('submitAddrecipe_categoryAndBackToParent')
			|| Tools::isSubmit('submitBulkdeleterecipe_category')
			|| Tools::isSubmit('submitAddrecipe_category')
			|| Tools::isSubmit('deleterecipe_category')
			|| (Tools::isSubmit('statusrecipe_category') && Tools::isSubmit('id_recipe_category'))
			|| (Tools::isSubmit('position') && Tools::isSubmit('id_recipe_category_to_move')))
				$this->admin_recipe_categories->postProcess();

		if (((Tools::isSubmit('submitAddrecipe_category') || Tools::isSubmit('submitAddrecipe_categoryAndStay')) && count($this->admin_recipe_categories->errors))
			|| Tools::isSubmit('updaterecipe_category')
			|| Tools::isSubmit('addrecipe_category'))
			$this->display = 'edit_category';
		else if (((Tools::isSubmit('submitAddrecipe') || Tools::isSubmit('submitAddrecipeAndStay')) && count($this->admin_recipe->errors))
			|| Tools::isSubmit('updaterecipe')
			|| Tools::isSubmit('addrecipe'))
			$this->display = 'edit_page';
		else
		{
			$this->display = 'list';
			$this->id_recipe_category = (int)Tools::getValue('id_recipe_category');
		}

		if (isset($this->admin_recipe->errors))
			$this->errors = array_merge($this->errors, $this->admin_recipe->errors);

		if (isset($this->admin_recipe_categories->errors))
			$this->errors = array_merge($this->errors, $this->admin_recipe_categories->errors);

		parent::postProcess();
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryUi('ui.widget');
		$this->addJqueryPlugin('tagify');
	}

	public function ajaxProcessUpdateCmsPositions()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			$id_recipe = (int)Tools::getValue('id_recipe');
			$id_category = (int)Tools::getValue('id_recipe_category');
			$way = (int)Tools::getValue('way');
			$positions = Tools::getValue('recipe');
			if (is_array($positions))
				foreach ($positions as $key => $value)
				{
					$pos = explode('_', $value);
					if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_category && $pos[2] == $id_recipe))
					{
						$position = $key;
						break;
					}
				}
			$recipe = new Recipe($id_recipe);
			if (Validate::isLoadedObject($recipe))
			{
				if (isset($position) && $recipe->updatePosition($way, $position))
					die(true);
				else
					die('{"hasError" : true, "errors" : "Can not update recipe position"}');
			}
			else
				die('{"hasError" : true, "errors" : "This recipe can not be loaded"}');
		}
	}

	public function ajaxProcessUpdateCmsCategoriesPositions()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			$id_recipe_category_to_move = (int)Tools::getValue('id_recipe_category_to_move');
			$id_recipe_category_parent = (int)Tools::getValue('id_recipe_category_parent');
			$way = (int)Tools::getValue('way');
			$positions = Tools::getValue('recipe_category');
			if (is_array($positions))
				foreach ($positions as $key => $value)
				{
					$pos = explode('_', $value);
					if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_recipe_category_parent && $pos[2] == $id_recipe_category_to_move))
					{
						$position = $key;
						break;
					}
				}
			$recipe_category = new RecipeCategory($id_recipe_category_to_move);
			if (Validate::isLoadedObject($recipe_category))
			{
				if (isset($position) && $recipe_category->updatePosition($way, $position))
					die(true);
				else
					die('{"hasError" : true, "errors" : "Can not update recipe categories position"}');
			}
			else
				die('{"hasError" : true, "errors" : "This recipe category can not be loaded"}');
		}
	}

	public function ajaxProcessPublishCMS()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			if ($id_recipe = (int)Tools::getValue('id_recipe'))
			{
				$bo_recipe_url = dirname($_SERVER['PHP_SELF']).'/index.php?tab=AdminRecipeContent&id_recipe='.(int)$id_recipe.'&updaterecipe&token='.$this->token;

				if (Tools::getValue('redirect'))
					die($bo_recipe_url);

				$recipe = new Recipe((int)(Tools::getValue('id_recipe')));
				if (!Validate::isLoadedObject($recipe))
					die('error: invalid id');

				$recipe->active = 1;
				if ($recipe->save())
					die($bo_recipe_url);
				else
					die('error: saving');
			}
			else
				die ('error: parameters');
		}
	}

}
