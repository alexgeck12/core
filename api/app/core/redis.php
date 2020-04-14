<?php
namespace core;

class redis
{
	protected static $instance;
	private function __clone(){}
	private function __wakeup(){}
	private function __construct(){}

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new \Redis();
			$conf = parse_ini_file(__DIR__.'/../config.ini', true);
			$conf = $conf['redis'];
			self::$instance->pconnect($conf['host'], $conf['port']);
			self::$instance->select(isset($conf['db'])?$conf['db']:0);
		}
		return self::$instance;
	}
}