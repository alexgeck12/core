<?php
namespace controllers;
use models\users;

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
				exit(json_encode(["error" => "auth"]));
			}
		} elseif ($signature) {
			ksort($data);

			if (md5(json_encode($data).'d836448ef8c015471071035b61bee27e0aff0b895d5f5ee5754242b9bde3b93c') != $signature) {
				exit(json_encode(["error" => "auth"]));
			}

			$this->user = $client;
		} else {
			exit(json_encode(["error" => "auth"]));
		}

	}

	public function requestFiltered($params, $data = false)
    {
    	if ($data) {
		    return array_intersect_key((array)$data, array_flip($params));
	    }
    	return array_intersect_key((array)$this->request, array_flip($params));
    }
}