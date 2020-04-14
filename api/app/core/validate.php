<?php
namespace core;

class validate
{
	public static function string($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}
		error_log($required);

		if ($required) {
			if (isset($params['min']) && strlen($data) < $params['min']) {
				return false;
			}

			if (isset($params['max']) && strlen($data) > $params['max']) {
				return false;
			}
		}

		return true;
	}

	public static function int($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			if (!is_numeric($data)) {
				return false;
			}

			if (isset($params['min']) && $data < $params['min']) {
				return false;
			}

			if (isset($params['max']) && $data > $params['max']) {
				return false;
			}
		}

		return true;
	}

	public static function lon($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			if (!is_float($data)) {
				return false;
			}

			if ($data < -180 || $data > 180) {
				return false;
			}
		}

		return true;
	}

	public static function lat($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			if (!is_float($data)) {
				return false;
			}

			if ($data < -90 || $data > 90) {
				return false;
			}
		}

		return true;
	}

	public static function email($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			if (!preg_match('/.+@.+\..+/i', $data)) {
				return false;
			}

			if (!checkdnsrr(array_pop(explode("@", $data)), "MX")) {
				return false;
			}
		}

		return true;
	}

	public static function phone($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {

		    $chrs = array("+", "(", ")", "-", " ");
		    $digits = str_replace($chrs,'', $data);

			if (!is_numeric($digits)) {
				return false;
			}

			if (strlen($data) < 7 || strlen($data) > 18) {
				return false;
			}
		}

		return true;
	}

	public static function credit_card($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			$data = (string)$data;

			if (!is_numeric($data)) {
				return false;
			}

			$length = strlen($data);
			$checksum = 0;

			for ($i = $length - 1; $i >= 0; $i -= 2) {
				$checksum += (int)substr($data, $i, 1);
			}

			for ($i = $length - 2; $i >= 0; $i -= 2) {
				$double = substr($data, $i, 1) * 2;
				$checksum += (int)(($double >= 10) ? ($double - 9) : $double);
			}

			return ($checksum % 10 === 0);
		}

		return true;
	}

	public static function date($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			if (!isset($params['format'])) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = $params['format'];
			}

			$d = \DateTime::createFromFormat($format, $data);
			return $d && $d->format($format) == $data;
		}

		return true;
	}

	public function ip($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			if (!filter_var($data, FILTER_VALIDATE_IP)) {
				return false;
			}
		}

		return true;
	}

	public static function int_list($data, $params = array())
	{
		$required = self::required($data, $params);

		if ($required == -1) {
			return false;
		}

		if ($required) {
			return preg_match('/^(?:|(?:\d+\,*)*\d+)$/', $data);
		}

		return true;
	}

	private static function required($data, $params)
	{
		if (isset($params['required']) && $params['required'] && empty($data)) {
			return -1;
		} elseif (empty($data)) {
			return 0;
		}

		return 1;
	}
}