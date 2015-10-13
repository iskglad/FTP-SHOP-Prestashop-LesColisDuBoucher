<?php

class IndexController extends IndexControllerCore
{
	
	
	public function initContent()
	{
		
		$messages = Guestbook::getGuestbookPages($this->context->language->id);
		
		$this->context->smarty->assign(array(
			'messages' => $messages
		));
		
		parent::initContent();
	}
}

