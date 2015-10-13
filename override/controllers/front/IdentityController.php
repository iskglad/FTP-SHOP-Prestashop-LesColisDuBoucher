<?php

class IdentityController extends IdentityControllerCore
{

	public function postProcess()
	{
		$origin_newsletter = (bool)$this->customer->newsletter;

		if (isset($_POST['years']) && isset($_POST['months']) && isset($_POST['days']))
			$this->customer->birthday = (int)($_POST['years']).'-'.(int)($_POST['months']).'-'.(int)($_POST['days']);

		if (Tools::isSubmit('submitIdentity'))
		{
			if (!@checkdate(Tools::getValue('months'), Tools::getValue('days'), Tools::getValue('years')) &&
				!(Tools::getValue('months') == '' && Tools::getValue('days') == '' && Tools::getValue('years') == ''))
				$this->errors[] = Tools::displayError('Invalid date of birth');
			else
			{
				$email = trim(Tools::getValue('email'));
				$this->customer->birthday = (empty($_POST['years']) ? '' : (int)$_POST['years'].'-'.(int)$_POST['months'].'-'.(int)$_POST['days']);
				$_POST['old_passwd'] = trim($_POST['old_passwd']);
				
				if (!Validate::isEmail($email))
					$this->errors[] = Tools::displayError('This e-mail address is not valid');
				elseif ($this->customer->email != $email && Customer::customerExists($email, true))
					$this->errors[] = Tools::displayError('An account is already registered with this e-mail.');
				elseif (empty($_POST['old_passwd']) || (Tools::encrypt($_POST['old_passwd']) != $this->context->cookie->passwd))
					$this->errors[] = Tools::displayError('Your password is incorrect.');
				elseif ($_POST['passwd'] != $_POST['confirmation'])
					$this->errors[] = Tools::displayError('Password and confirmation do not match');
				else
				{
					$prev_id_default_group = $this->customer->id_default_group;

					// Merge all errors of this file and of the Object Model
					$this->errors = array_merge($this->errors, $this->customer->validateController());
				}

				if (!count($this->errors))
				{
					$this->customer->id_default_group = (int)$prev_id_default_group;
					$this->customer->firstname = Tools::ucfirst(Tools::strtolower($this->customer->firstname));

					if (!isset($_POST['newsletter']) || ($_POST['newsletter'] == "0")){
						$this->customer->newsletter = 0;
					}elseif (!$origin_newsletter && isset($_POST['newsletter']) && ($_POST['newsletter'] == "1") ){
						if ($module_newsletter = Module::getInstanceByName('blocknewsletter')){
							if ($module_newsletter->active){
								$module_newsletter->confirmSubscription($this->customer->email);
							}
						}		
					}

					if (!isset($_POST['optin']))
						$this->customer->optin = 0;
					if (Tools::getValue('passwd'))
						$this->context->cookie->passwd = $this->customer->passwd;
					if ($this->customer->update())
					{
						$this->context->cookie->customer_lastname = $this->customer->lastname;
						$this->context->cookie->customer_firstname = $this->customer->firstname;
						$this->context->smarty->assign('confirmation', 1);
					}
					else
						$this->errors[] = Tools::displayError('Cannot update information');
				}
			}
		}
		else
			$_POST = array_map('stripslashes', $this->customer->getFields());

		return $this->customer;
	}

}

