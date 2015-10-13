<?php

class CmsController extends CmsControllerCore
{
	
	public function initContent()
	{
		parent::initContent();

		$parent_cat = new CMSCategory(1, $this->context->language->id);
		$this->context->smarty->assign('id_current_lang', $this->context->language->id);
		$this->context->smarty->assign('home_title', $parent_cat->name);
		$this->context->smarty->assign('cgv_id', Configuration::get('PS_CONDITIONS_CMS_ID'));
		if (isset($this->cms->id_cms_category) && $this->cms->id_cms_category) {
			$path = Tools::getFullPath($this->cms->id_cms_category, $this->cms->meta_title, 'CMS');
		} else if (isset($this->cms_category->meta_title)) {
			if ($this->cms_category->id == 4) {
				$this->addJS(_THEME_JS_DIR_.'faq.js');
			}
			$path = Tools::getFullPath(1, $this->cms_category->meta_title, 'CMS');
		}
		if ($this->assignCase == 1)
		{
			$this->context->smarty->assign(array(
				'cms' => $this->cms,
				'content_only' => (int)(Tools::getValue('content_only')),
				'path' => $path
			));
		}
		else if ($this->assignCase == 2)
		{
			$this->context->smarty->assign(array(
				'cms_category' => $this->cms_category,
				'sub_category' => $this->cms_category->getFullSubCategories($this->context->language->id),
				'cms_pages' => CMS::getCMSPages($this->context->language->id, (int)($this->cms_category->id) ),
				'path' => ($this->cms_category->id !== 1) ? Tools::getPath($this->cms_category->id, $this->cms_category->name, false, 'CMS') : '',
			));
		}

		$this->setTemplate(_PS_THEME_DIR_.'cms.tpl');
	}
}

