<?php
namespace controllers;
use models\users;

/**
 * Class main
 *
 * Метод авторизации
 * @package controllers
 */

class main
{
	public function auth()
	{
		if ($this->request->login && $this->request->password) {
			$user = new users();
			return $user->login($this->request->login, $this->request->password)
				?:['error'=>'Неправильный логин и/или пароль'];
		}
		return ['error' => 'Пароль и/или логин не может быть пустым'];
	}
}