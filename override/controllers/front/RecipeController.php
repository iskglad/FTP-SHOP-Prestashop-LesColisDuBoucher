<?php

class RecipeControllerCore extends FrontController
{
	public $php_self = 'recipe';
	public $assignCase;
	public $recipe;
	public $recipe_category;

	public function canonicalRedirection($canonicalURL = '')
	{
		if (Validate::isLoadedObject($this->recipe) && ($canonicalURL = $this->context->link->getRecipeLink($this->recipe)))
			parent::canonicalRedirection($canonicalURL);
		else if (Validate::isLoadedObject($this->recipe_category) && ($canonicalURL = $this->context->link->getRecipeCategoryLink($this->recipe_category)))
			parent::canonicalRedirection($canonicalURL);
	}

	/**
	 * Initialize recipe controller
	 * @see FrontController::init()
	 */
	public function init()
	{
		parent::init();

		if ($id_recipe = (int)Tools::getValue('id_recipe'))
			$this->recipe = new Recipe($id_recipe, $this->context->language->id);
		else if ($id_recipe_category = (int)Tools::getValue('id_recipe_category'))
			$this->recipe_category = new RecipeCategory($id_recipe_category, $this->context->language->id);

		$this->canonicalRedirection();

		// assignCase (1 = Recipe page, 2 = Recipe category)
		if (Validate::isLoadedObject($this->recipe))
		{
			$adtoken = Tools::getAdminToken('AdminRecipeContent'.(int)Tab::getIdFromClassName('AdminRecipeContent').(int)Tools::getValue('id_employee'));
			if (!$this->recipe->active && Tools::getValue('adtoken') != $adtoken)
			{
				header('HTTP/1.1 404 Not Found');
				header('Status: 404 Not Found');
			}
			else
				$this->assignCase = 1;
		}
		else if (Validate::isLoadedObject($this->recipe_category))
			$this->assignCase = 2;
		else
		{
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
		}
	}

	public function setMedia()
	{
		parent::setMedia();

		if ($this->assignCase == 1)
			$this->addJS(_THEME_JS_DIR_.'recipe.js');

		$this->addCSS(_THEME_CSS_DIR_.'recipe.css');
	}

	/**
	 * Assign template vars related to page content
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		$parent_cat = new RecipeCategory(1, $this->context->language->id);
		$this->context->smarty->assign('id_current_lang', $this->context->language->id);
		$this->context->smarty->assign('home_title', $parent_cat->name);
		
		if (isset($this->recipe->id_recipe_category) && $this->recipe->id_recipe_category)
			$path = Tools::getFullPath($this->recipe->id_recipe_category, $this->recipe->meta_title, 'Recipe');
		else if (isset($this->recipe_category->meta_title))
			$path = Tools::getFullPath(1, $this->recipe_category->meta_title, 'Recipe');
		if ($this->assignCase == 1)
		{
			$this->context->smarty->assign(array(
				'recipe_category' => new RecipeCategory($this->recipe->id_recipe_category, $this->context->language->id),
				'recipe' => $this->recipe,
				'content_only' => (int)(Tools::getValue('content_only')),
				'path' => $path
			));
		}
		else if ($this->assignCase == 2)
		{
			$this->context->smarty->assign(array(
				'parent_recipe_category' => new RecipeCategory($this->recipe_category->id_parent, $this->context->language->id),
				'recipe_category' => $this->recipe_category,
				'sub_category' => $this->recipe_category->getSubCategories($this->context->language->id),
				'recipe_pages' => Recipe::getRecipePages($this->context->language->id, (int)($this->recipe_category->id) ),
				'path' => ($this->recipe_category->id !== 1) ? Tools::getPath($this->recipe_category->id, $this->recipe_category->name, false, 'Recipe') : '',
			));
		}
		
		// get left col of category page
		$parent = new RecipeCategory(1, $this->context->language->id);
		$left_col = $parent->getSubCategoriesByDepth(1, 3, $this->context->language->id);
		$this->context->smarty->assign('left_col', $left_col);
		
		$this->setTemplate(_PS_THEME_DIR_.'recipe.tpl');
	}
}
