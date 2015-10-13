<?php

class Link extends LinkCore
{
	/**
	 * Create a link to a Recipe category
	 *
	 * @param mixed $category RecipeCategory object (can be an ID category, but deprecated)
	 * @param string $alias
	 * @param int $id_lang
	 * @return string
	 */
	public function getRecipeCategoryLink($category, $alias = null, $id_lang = null)
	{
		if (!$id_lang)
			$id_lang = Context::getContext()->language->id;
		$url = _PS_BASE_URL_.__PS_BASE_URI__.$this->getLangLink($id_lang);

		if (!is_object($category))
			$category = new RecipeCategory($category, $id_lang);

		// Set available keywords
		$params = array();
		$params['id'] = $category->id;
		$params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
		$params['meta_keywords'] =	Tools::str2url($category->meta_keywords);
		$params['meta_title'] = Tools::str2url($category->meta_title);

		return $url.Dispatcher::getInstance()->createUrl('recipe_category_rule', $id_lang, $params, $this->allow);
	}
	
	/**
	 * Create a link to a Recipe page
	 *
	 * @param mixed $recipe Recipe object (can be an ID Recipe, but deprecated)
	 * @param string $alias
	 * @param bool $ssl
	 * @param int $id_lang
	 * @return string
	 */
	public function getRecipeLink($recipe, $alias = null, $ssl = false, $id_lang = null)
	{
		$base = (($ssl && $this->ssl_enable) ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_);
		
		if (!$id_lang)
			$id_lang = Context::getContext()->language->id;
		$url = $base.__PS_BASE_URI__.$this->getLangLink($id_lang);

		if (!is_object($recipe))
			$recipe = new Recipe($recipe, $id_lang);

		// Set available keywords
		$params = array();
		$params['id'] = $recipe->id;
		$params['rewrite'] = (!$alias) ? (is_array($recipe->link_rewrite) ? $recipe->link_rewrite[(int)$id_lang] : $recipe->link_rewrite) : $alias;

		if (isset($recipe->meta_title) && !empty($recipe->meta_title))
			$params['meta_title'] = is_array($recipe->meta_title) ? Tools::str2url($recipe->meta_title[(int)$id_lang]) : Tools::str2url($recipe->meta_title);
		else
			$params['meta_title'] = '';
		return $url.Dispatcher::getInstance()->createUrl('recipe_rule', $id_lang, $params, $this->allow);
	}
	
	public function getGuestbookLink($guestbook, $alias = null, $ssl = false, $id_lang = null)
	{
		$base = (($ssl && $this->ssl_enable) ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_);
		
		if (!$id_lang)
			$id_lang = Context::getContext()->language->id;
		$url = $base.__PS_BASE_URI__.$this->getLangLink($id_lang);

		if (!is_object($guestbook))
			$guestbook = new Guestbook($guestbook, $id_lang);

		// Set available keywords
		$params = array();
		$params['id'] = $guestbook->id;
		$params['rewrite'] = (!$alias) ? (is_array($guestbook->link_rewrite) ? $guestbook->link_rewrite[(int)$id_lang] : $guestbook->link_rewrite) : $alias;

		if (isset($guestbook->meta_title) && !empty($guestbook->meta_title))
			$params['meta_title'] = is_array($guestbook->meta_title) ? Tools::str2url($guestbook->meta_title[(int)$id_lang]) : Tools::str2url($guestbook->meta_title);
		else
			$params['meta_title'] = '';
		return $url.Dispatcher::getInstance()->createUrl('guestbook_rule', $id_lang, $params, $this->allow);
	}

    public function getBaseLink($id_shop = null, $ssl = null)
	{
		static $force_ssl = null;
		if ($ssl === null)
		{
			if ($force_ssl === null)
				$force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
			$ssl = $force_ssl;
		}
		if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null)
			$shop = new Shop($id_shop);
		else
			$shop = Context::getContext()->shop;
		$base = (($ssl && $this->ssl_enable) ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);
		return $base.$shop->getBaseURI();
	}
}

