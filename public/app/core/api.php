<?php
namespace core;

class api
{
	public static function get($uri, $params = [])
	{
		return self::request('GET', $uri, $params);
	}

	public static function post($uri, $params = [])
	{
		return self::request('POST', $uri, $params);
	}

	private static function request($method, $uri, $params = [])
	{
		array_walk_recursive($params, function (&$value, $key)  {
			if (is_int($value)) {
				$value = (string) $value;
			}
		});

		ksort($params);

		$sig = md5(json_encode($params).'d836448ef8c015471071035b61bee27e0aff0b895d5f5ee5754242b9bde3b93c');
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/api/'.$uri;

		$ch = curl_init();
		switch ($method) {
			case 'GET':
				curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($params));
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
				break;
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Signature: '.$sig]);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		$resp = curl_exec($ch);

		curl_close($ch);

		return json_decode($resp);
	}
}