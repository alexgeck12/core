<?php
namespace core;

abstract class model
{
	/**
	 * @property db $db
	 */

	/**
	 * @property redis $redis
	 */

	public function __get($var)
	{
		switch ($var) {
			case 'db':
				$this->db = db::getInstance();
				return $this->db;
				break;
			case 'redis':
				$this->redis = redis::getInstance();
				return $this->redis;
				break;
		}
		
	}

	protected function getCache($name)
	{
		return json_decode($this->redis->get('cache:'.get_class($this).":$name"), 1);
	}

	protected function setCache($name, $data)
	{
		return $this->redis->set('cache:'.get_class($this).":$name", json_encode($data));
	}

	protected function delCache($name)
	{
		if ($name == '*') {
			$keys = $this->redis->keys('cache:'.addslashes(get_class($this)).":*");
			foreach ($keys as $key) {
				$this->redis->del($key);
			}


			return count($keys);
		} else {
			return $this->redis->del('cache:'.get_class($this).":$name");
		}

	}
}