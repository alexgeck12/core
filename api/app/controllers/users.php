<?php
namespace controllers;
use models\users as UsersModel;
use core\image;
use core\validate;

class users extends authorized
{
    const LIMIT = 16;

    public function profile()
    {
        $users = new UsersModel();
        $user = $users->getProfile(isset($this->request->id)?$this->request->id:$this->user);
        if (!$user) {
            return array("error" => "Такого пользователя не существует");
        }
        return $user;
    }

	public function get()
	{
		if (validate::int_list($this->request->id, ['required' => true])){
			$users = new UsersModel();
			return $users->get($this->request->id);
		}
		return ['error' => 'Ошибка валидации'];
    }

    public function save()
    {
        $users = new UsersModel();
        $user = $users->getUserById($this->request->id);

        foreach($this->request as $field => $val) {
            $user[$field] = $val;
        }

        return $users->updateProfile($this->request->id, $user);
    }

    public function update()
    {
        $users = new UsersModel();

        $current_user = $users->getUserById($this->user);

        if ($current_user['role'] === 'admin') {

            return $users->updateProfile($this->request->id,
	            $this->requestFiltered(['name', 'email', 'phone', 'genimg', 'role', 'login']));

        } else {

            if ($this->user == $this->request->id ) {
                return $users->updateProfile($this->request->id,
                    $this->requestFiltered(['name', 'email', 'phone', 'genimg']));
            } else {
                return ['error' => 'Access denied'];
            }
        }
    }

    public function upload()
    {
    	error_reporting(E_ALL);
    	ini_set('display_errors', 1);

        $img_dir = '/profiles';

        if (!is_dir(ROOT.'/public/media'.$img_dir)) {
            mkdir(ROOT.'/public/media'.$img_dir, 0775, true);
        }

        if ($this->files->genimg['name']) {

            $extension = strtolower(strrchr($this->files->genimg['name'], '.'));
            $this->files->genimg['name'] = md5_file($this->files->genimg['tmp_name']).$extension;

            if (move_uploaded_file($this->files->genimg['tmp_name'], ROOT.'/public/media'.$img_dir.'/'.$this->files->genimg['name'])) {

                $path = ROOT.'/public/media'.$img_dir.'/'.$this->files->genimg['name'];
                $image = new image($path);
                $image->resizeIfBigger(320, 240);
                $image->save($path, "85");
                unset($image);

                return '/media'.$img_dir.'/'.$this->files->genimg['name'];
            }

        }
    }


    public function changePassword()
    {
        $users = new UsersModel();
        $current_user = $users->getUserById($this->user);

        if ($current_user['role'] === 'admin') {
            if (validate::int($this->request->user_id, ['required' => true])
                && validate::string($this->request->password, ['required' => true])
                && validate::string($this->request->newPassword, ['required' => true])
                && validate::string($this->request->repeatPassword, ['required' => true])) {

                if ($this->request->newPassword !== $this->request->repeatPassword) {
                    return ['error' => 'Пароли не совпадают'];
                }


                return $users->changePassword($this->request->user_id, $this->request->password, $this->request->newPassword);
            } else {
                return ['error' => 'Не все поля заполнены'];
            }
        } else {

            if ($this->user == $this->request->user_id ) {

                if (validate::int($this->request->user_id, ['required' => true])
                    && validate::string($this->request->password, ['required' => true])
                    && validate::string($this->request->newPassword, ['required' => true])
                    && validate::string($this->request->repeatPassword, ['required' => true])) {

                    if ($this->request->newPassword !== $this->request->repeatPassword) {
                        return ['error' => 'Пароли не совпадают'];
                    }


                    return $users->changePassword($this->request->user_id, $this->request->password, $this->request->newPassword);
                } else {
                    return ['error' => 'Не все поля заполнены'];
                }

            }else {
                return ['error' => 'Access denied'];
            }
        }
    }

    public function create()
    {
        $users = new UsersModel();

        $current_user = $users->getUserById($this->user);

        if ($current_user['role'] === 'admin') {

            $this->request->password = md5($this->request->password);

            $user = $users->create($this->requestFiltered(['name', 'login', 'email', 'phone', 'role', 'password', 'genimg']));

            if (!$user) {
                return ["error" => "Пользователь не создан"];
            }

            return $user;

        } else {
            return ['error' => 'Access denied'];
        }

    }

    public function delete()
    {
        $users = new UsersModel();

        $current_user = $users->getUserById($this->user);

        if ($current_user['role'] === 'admin') {
            if (validate::int($this->request->id, ['required' => true])) {
                return $users->delete($this->request->id);
            } else {
                return ['error' => 'Ошибка валидации'];
            }
        } else {
            return ['error' => 'Access denied'];
        }
    }

    public function getAll()
    {
        $users = new UsersModel();
        return $users->getAll();
    }

    public function getList()
    {
        $users = new UsersModel();
        return $users->getList($this->request->page?$this->request->page:1, self::LIMIT);
    }

    public function getListPages()
    {
        $users = new UsersModel();
        return $users->getListPages(self::LIMIT);
    }

    public function find()
    {
        $users = new UsersModel();
        return $users->find($this->request->q);
    }
}