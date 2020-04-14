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
            $token = md5(implode($user).time());
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

    public function getProfile($id)
    {
        $user = json_decode($this->redis->hGet('users', $id), 1);

        if (!$user) {
            $user = $this->db->getRowByKeys($this->table, ['id' => $id ]);
            $this->redis->hSet('users', $user['id'], json_encode($user));
        }

        return $user;
    }

	public function get($ids)
	{
		return $this->db->getFiltered($this->table, ['id' => explode(',', $ids)], [], []);
    }

    public function updateProfile($id, $data)
    {
        $res = $this->db->update($this->table, $data, ['id' => $id]);

        if ($res) {
            $this->redis->hSet(
                'users',
                $id,
                json_encode(
                    $user = $this->db->getRowByKeys($this->table, ['id' => $id ])
                )
            );
        }
        return $res;
    }

    public function changePassword($id, $password, $newPassword)
    {
        $hash = $this->db->query("SELECT password FROM $this->table WHERE id = $id")->fetch_assoc()['password'];

        if ($hash === md5($password)) {
            return $this->db->update($this->table, ['password' => md5($newPassword)], ['id' => $id]);
        } else {
            return ['error' => 'Введен не верный пароль'];
        }
    }

    public function getAll()
    {
        return $this->db->getFiltered($this->table);
    }

    public function getList($page, $limit)
    {
	    return $this->db->getFiltered($this->table, [], ["id" => "DESC"], [$limit*($page-1), $limit]);
    }

    public function getListPages($limit)
    {
        $count = count($this->db->getFiltered($this->table, [], ["id" => "DESC"], []));
        return ceil($count / $limit);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, array('id' => $id));
    }

    public function getUserById($id)
    {
        return $user = $this->db->getRowByKeys($this->table, array('id' => $id));
    }

    public function create($data)
    {
    	unset($data->id);
        return $this->db->insert($this->table, $data);
    }

    public function find($q)
    {
        return $this->db->query("
            SELECT p.*
            FROM ".$this->table." p
            WHERE p.name LIKE '%".$q."%' OR
                  p.login LIKE '%".$q."%' OR
                  p.phone LIKE '%".$q."%'
            ORDER BY p.id DESC
        ")->fetch_all(MYSQLI_ASSOC);
    }
}