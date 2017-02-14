<?php

namespace VideoRecruit\Phalcon\Links;

/**
 * Class Helpers
 *
 * @package VideoRecruit\Phalcon\Links
 */
class Helpers
{

	/**
	 * This method is part of Nette Framework http://nette.org
	 *
	 * Is IP address in CIDR block?
	 */
	public static function ipMatch($ip, $mask)
	{
		list($mask, $size) = explode('/', $mask . '/');
		$tmp = function ($n) {
			return sprintf('%032b', $n);
		};
		$ip = implode('', array_map($tmp, unpack('N*', inet_pton($ip))));
		$mask = implode('', array_map($tmp, unpack('N*', inet_pton($mask))));
		$max = strlen($ip);

		if (!$max || $max !== strlen($mask) || (int) $size < 0 || (int) $size > $max) {
			return FALSE;
		}

		return strncmp($ip, $mask, $size === '' ? $max : (int) $size) === 0;
	}
}
