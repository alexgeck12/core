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

    // Авторизация с капчой

/*
    public function auth()
    {
        if (!empty($this->request->{'g-recaptcha-response'})) {

            $params = [
                'secret' => '6Lc6FpkUAAAAAD8Rl43LC6KZPalMfIXw5QyK94Ts',
                'response' => $this->request->{'g-recaptcha-response'},
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $resp = curl_exec($ch);
            curl_close($ch);

            $resp = json_decode($resp);

            if ($resp->success) {
                unset($this->request->{'g-recaptcha-response'});
                if ($this->request->login && $this->request->password) {
                    $user = new users();
                    return $user->login($this->request->login, $this->request->password)
                        ?: array('error' => 'Неправильный логин и/или пароль');
                } else {
                    return array('error' => 'Пароль и/или логин не может быть пустым');
                }
            } else {
                return array('error' => 'Капча не подтверждена');
            }


        } else {
            return array('error' => 'Не указана капча');
        }
    }
*/
}