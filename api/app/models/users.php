<?php
namespace models;
use core\model;

class users extends model
{
    public $table = 'users';

    public function login($login, $password)
    {
        $user =
	        $this->db->getRowByKeys($this->table, [
                'login' => $login,
                'password' => md5($password),
                'active' => 1
        ]);

        if ($user) {
            $token = md5(json_encode($user).time());
            $this->redis->hSet('authorized', $token, $user['id']);

            $user['token'] = $token;

            $this->redis->hSet('users', $user['id'], json_encode($user));

            return $user;
        }

        return false;
    }

    public function getByToken($token)
    {
        return $this->redis->hGet('authorized', $token);
    }
}