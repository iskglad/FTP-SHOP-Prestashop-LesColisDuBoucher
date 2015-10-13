<?php

class AdminPostContentControllerCore extends AdminController
{
	/** @var object adminPostCategories() instance */
	protected $admin_post_categories;

	/** @var object adminPost() instance */
	protected $admin_post;

	/** @var object Category() instance for navigation*/
	protected static $category = null;

	public function __construct()
	{
		/* Get current category */
		$id_post_category = (int)Tools::getValue('id_post_category', Tools::getValue('id_post_category_parent', 1));
		$this->table = 'post';
		$this->className = 'Post';
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
		$this->admin_post = new AdminPostController();
		$this->fieldImageSettings = array('name' => 'image', 'dir' => 'po');
		parent::__construct();
	}

	/**
	 * Return current category
	 *
	 * @return object
	 */
/*	public static function getCurrentPostCategory()
	{
		return self::$category;
	} */

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
	//	$this->admin_post_categories->tabAccess = $this->tabAccess;
		$this->admin_post->tabAccess = $this->tabAccess;
		return $result;
	}

	public function initContent()
	{
	//	$this->admin_post_categories->token = $this->token;
		$this->admin_post->token = $this->token;

	/*	if ($this->display == 'edit_category')
			$this->content .= $this->admin_post_categories->renderForm();
		else */ if ($this->display == 'edit_page')
			$this->content .= $this->admin_post->renderForm();
		else if ($this->display == 'view_page')
			$fixme = 'fixme';// @FIXME
		else
		{
			$id_post_category = (int)Tools::getValue('id_post_category');
			if (!$id_post_category)
				$id_post_category = 1;

			// Post categories breadcrumb
			$post_tabs = array('post_category', 'post');
			// Cleaning links
			$cat_bar_index = self::$currentIndex;
			foreach ($post_tabs as $tab)
				if (Tools::getValue($tab.'Orderby') && Tools::getValue($tab.'Orderway'))
					$cat_bar_index = preg_replace('/&'.$tab.'Orderby=([a-z _]*)&'.$tab.'Orderway=([a-z]*)/i', '', self::$currentIndex);

		//	$this->content .= $this->admin_post_categories->renderList();
		//	$this->admin_post->id_post_category = $id_post_category;
			$this->content .= $this->admin_post->renderList();
		/*	$this->context->smarty->assign(array(
				'post_breadcrumb' => getPath($cat_bar_index, $id_post_category, '', '', 'post'),
			)); */
		}

		$this->context->smarty->assign(array(
			'content' => $this->content
		));
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitDelpost')
			|| Tools::isSubmit('previewSubmitAddpostAndPreview')
			|| Tools::isSubmit('submitAddpost')
			|| Tools::isSubmit('deletepost')
			|| Tools::isSubmit('viewpost')
			|| (Tools::isSubmit('statuspost') && Tools::isSubmit('id_post'))
			|| (Tools::isSubmit('way') && Tools::isSubmit('id_post')) && (Tools::isSubmit('position')))
			$this->admin_post->postProcess();
	/*	elseif (Tools::isSubmit('submitDelpost_category')
			|| Tools::isSubmit('submitAddpost_categoryAndBackToParent')
			|| Tools::isSubmit('submitBulkdeletepost_category')
			|| Tools::isSubmit('submitAddpost_category')
			|| Tools::isSubmit('deletepost_category')
			|| (Tools::isSubmit('statuspost_category') && Tools::isSubmit('id_post_category'))
			|| (Tools::isSubmit('position') && Tools::isSubmit('id_post_category_to_move')))
				$this->admin_post_categories->postProcess();

		if (((Tools::isSubmit('submitAddpost_category') || Tools::isSubmit('submitAddpost_categoryAndStay')) // &&  count($this->admin_post_categories->errors))
			|| Tools::isSubmit('updatepost_category')
			|| Tools::isSubmit('addpost_category'))
			$this->display = 'edit_category';
		else */ if (((Tools::isSubmit('submitAddpost') || Tools::isSubmit('submitAddpostAndStay')) && count($this->admin_post->errors))
			|| Tools::isSubmit('updatepost')
			|| Tools::isSubmit('addpost'))
			$this->display = 'edit_page';
		else
		{
			$this->display = 'list';
			$this->id_post_category = (int)Tools::getValue('id_post_category');
		}

		if (isset($this->admin_post->errors))
			$this->errors = array_merge($this->errors, $this->admin_post->errors);

/*		if (isset($this->admin_post_categories->errors))
			$this->errors = array_merge($this->errors, $this->admin_post_categories->errors);
*/
		parent::postProcess();
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryUi('ui.widget');
		$this->addJqueryPlugin('tagify');
	}

	public function ajaxProcessUpdatePostPositions()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			$id_post = (int)Tools::getValue('id_post');
			$id_category = (int)Tools::getValue('id_post_category');
			$way = (int)Tools::getValue('way');
			$positions = Tools::getValue('post');
			if (is_array($positions))
				foreach ($positions as $key => $value)
				{
					$pos = explode('_', $value);
					if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_category && $pos[2] == $id_post))
					{
						$position = $key;
						break;
					}
				}
			$post = new Post($id_post);
			if (Validate::isLoadedObject($post))
			{
				if (isset($position) && $post->updatePosition($way, $position))
					die(true);
				else
					die('{"hasError" : true, "errors" : "Can not update post position"}');
			}
			else
				die('{"hasError" : true, "errors" : "This post can not be loaded"}');
		}
	}

	public function ajaxProcessUpdatePostCategoriesPositions()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			$id_post_category_to_move = (int)Tools::getValue('id_post_category_to_move');
			$id_post_category_parent = (int)Tools::getValue('id_post_category_parent');
			$way = (int)Tools::getValue('way');
			$positions = Tools::getValue('post_category');
			if (is_array($positions))
				foreach ($positions as $key => $value)
				{
					$pos = explode('_', $value);
					if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_post_category_parent && $pos[2] == $id_post_category_to_move))
					{
						$position = $key;
						break;
					}
				}
			$post_category = new PostCategory($id_post_category_to_move);
			if (Validate::isLoadedObject($post_category))
			{
				if (isset($position) && $post_category->updatePosition($way, $position))
					die(true);
				else
					die('{"hasError" : true, "errors" : "Can not update post categories position"}');
			}
			else
				die('{"hasError" : true, "errors" : "This post category can not be loaded"}');
		}
	}

	public function ajaxProcessPublishPost()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			if ($id_post = (int)Tools::getValue('id_post'))
			{
				$bo_post_url = dirname($_SERVER['PHP_SELF']).'/index.php?tab=AdminPostContent&id_post='.(int)$id_post.'&updatepost&token='.$this->token;

				if (Tools::getValue('redirect'))
					die($bo_post_url);

				$post = new Post((int)(Tools::getValue('id_post')));
				if (!Validate::isLoadedObject($post))
					die('error: invalid id');

				$post->active = 1;
				if ($post->save())
					die($bo_post_url);
				else
					die('error: saving');
			}
			else
				die ('error: parameters');
		}
	}

}
