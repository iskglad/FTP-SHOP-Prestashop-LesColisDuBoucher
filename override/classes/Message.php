<?php

class Message extends MessageCore
{
	public static function getMessagesByOrderId($id_order, $private = false, Context $context = null)
	{
		if (!Validate::isBool($private))
			die(Tools::displayError());
		if (!$context)
			$context = Context::getContext();
		$m = Db::getInstance()->executeS('
			SELECT m.*, c.`firstname` AS cfirstname, c.`lastname` AS clastname, e.`firstname` AS efirstname, e.`lastname` AS elastname,
			(COUNT(mr.id_message) = 0 AND m.id_customer != 0) AS is_new_for_me
			FROM `'._DB_PREFIX_.'message` m
			LEFT JOIN `'._DB_PREFIX_.'customer` c ON m.`id_customer` = c.`id_customer`
			LEFT JOIN `'._DB_PREFIX_.'message_readed` mr
			ON mr.`id_message` = m.`id_message`
			AND mr.`id_employee` = '.(isset($context->employee) ? (int)$context->employee->id : '\'\'').'
			LEFT OUTER JOIN `'._DB_PREFIX_.'employee` e ON e.`id_employee` = m.`id_employee`
			WHERE m.`id_order` = '.(int)$id_order.'
			'.(!$private ? ' AND m.`private` = 0' : '').'
			GROUP BY m.id_message
			ORDER BY m.date_add DESC
			');

		$o = Db::getInstance()->executeS('
			SELECT ct.*, m.*, e.`firstname` AS efirstname, e.`lastname` AS elastname
			FROM `'._DB_PREFIX_.'customer_thread` ct
			LEFT JOIN `'._DB_PREFIX_.'customer_message` m ON m.`id_customer_thread` = ct.`id_customer_thread`
			LEFT OUTER JOIN `'._DB_PREFIX_.'employee` e ON e.`id_employee` = m.`id_employee`
			WHERE ct.`id_order` = '.(int)$id_order.'
			ORDER BY ct.`date_add` DESC'
			);

		return array_merge($o,$m);

	}
}

