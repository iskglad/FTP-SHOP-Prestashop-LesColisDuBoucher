<?php

class Validate extends ValidateCore
{
	public static function isRelayName($name)
	{
		return empty($name) || preg_match(Tools::cleanNonUnicodeSupport('/^[^<>;=#{}]*$/u'), $name);
	}
}

