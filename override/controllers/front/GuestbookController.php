<?php

class GuestbookControllerCore extends FrontController
{
	public $php_self = 'guestbook';

	public function setMedia()
	{
		parent::setMedia();

		if (isset($this->assignCase) && $this->assignCase == 1)
			$this->addJS(_THEME_JS_DIR_.'guestbook.js');

		$this->addCSS(_THEME_CSS_DIR_.'guestbook.css');
	}

	/**
	 * Assign template vars related to page content
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		
		$result = Guestbook::getGuestbookPages(true, $this->context->language->id);
		$nbMessages = count($result);
		$this->pagination($nbMessages);
		$messages = Guestbook::getGuestbookPages($this->context->language->id);
		
		$this->context->smarty->assign(array(
			'guestbook' => $messages,
			'nbProducts' => $nbMessages
		));
		
		$this->setTemplate(_PS_THEME_DIR_.'guestbook.tpl');
	}
	
	public function postProcess()
	{
		if (Tools::isSubmit('submitMessage'))
		{
			$firstname = Tools::getValue('firstname');
			$lastname = Tools::getValue('lastname');
			$from = trim(Tools::getValue('email'));
			$city = Tools::getValue('city');
			$message = Tools::getValue('message');
			
			if (!Validate::isEmail($from))
				$this->errors[] = Tools::displayError('Invalid e-mail address');
			else if (!$message)
				$this->errors[] = Tools::displayError('Message cannot be blank');
			else if (!$firstname)
				$this->errors[] = Tools::displayError('Firstname cannot be blank');
			else if (!$lastname)
				$this->errors[] = Tools::displayError('Lastname cannot be blank');
			else if (!$city)
				$this->errors[] = Tools::displayError('City cannot be blank');
			else if (!Validate::isCleanHtml($message))
				$this->errors[] = Tools::displayError('Invalid message');
			else
			{
			
				$gb = new Guestbook();
				$gb->firstname = $firstname;
				$gb->lastname = $lastname;
				$gb->email = $from;
				$gb->city = $city;
				$gb->message = $message;
				$gb->add();
				
				$this->context->smarty->assign('confirmation', "true");
				
			}
			
			if (count($this->errors) > 1)
				array_unique($this->errors);
		}
	}
}