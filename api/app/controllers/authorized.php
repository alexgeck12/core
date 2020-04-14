<?php
namespace controllers;
use models\users;

/**
 * Class authorized
 *
 * Все контроллеры, которые должны проходить авторизацию и проверку подписи
 * наследуются от этого класса
 *
 * @package controllers
 */

abstract class authorized
{
	public $request;
	public function __construct()
	{
		if(empty($_SERVER['Token'])) {
			$token = isset($_GET['token'])?$_GET['token']:'';
		} else {
			$token = $_SERVER['Token'];
		}
		$signature = $_SERVER['Signature'];
		$client = $_SERVER['Client'];

		$data = $_REQUEST;

		$users = new users();
		
		if ($token) {
			$this->user = $users->getByToken($token);
			if (!$this->user) {
				exit(json_encode(array("error" => "auth")));
			}
		} elseif ($signature) {
			ksort($data);

			if (md5(json_encode($data).'d836448ef8c015471071035b61bee27e0aff0b895d5f5ee5754242b9bde3b93c') != $signature) {
				exit(json_encode(array("error" => "auth")));
			}

			$this->user = $client;
		} else {
			exit(json_encode(array("error" => "auth")));
		}

	}

	public function requestFiltered($params, $data = false)
    {
    	if ($data) {
		    return array_intersect_key((array)$data, array_flip($params));
	    }
    	return array_intersect_key((array)$this->request, array_flip($params));
    }

    public function relativeURL($url)
    {
        $new = $url;
        if (mb_substr($url, 0, 1, 'utf-8') != '/'){
            $new = str_replace('https://www.'.$_SERVER['HTTP_HOST'], '', $new);
            $new = str_replace('http://www.'.$_SERVER['HTTP_HOST'], '', $new);
            $new = str_replace('https://'.$_SERVER['HTTP_HOST'], '', $new);
            $new = str_replace('http://'.$_SERVER['HTTP_HOST'], '', $new);
            $new = str_replace('www.'.$_SERVER['HTTP_HOST'], '', $new);
        } else {
            $new = str_replace('//'.$_SERVER['HTTP_HOST'], '', $new);
        }

        return $new;
    }
}