<?php
namespace controllers;
use models\statuses as StatusesModel;
use core\validate;

class statuses extends authorized
{
    public function get()
    {
        if (validate::int($this->request->id, ['required' => true])) {
            $statuses = new StatusesModel();
            return $statuses->get($this->request->id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])) {
            $statuses = new StatusesModel();

            return $statuses->add($this->requestFiltered(['name', 'active']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function update()
    {
        if (validate::int($this->request->id, ['required' => true])
	        && validate::string($this->request->name, ['required' => true])
        ) {
            $statuses = new StatusesModel();
            return $statuses->update($this->request->id, $this->requestFiltered(['name', 'active']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function delete()
    {
        if (validate::int($this->request->id, ['required' => true])) {
            $statuses = new StatusesModel();
            return $statuses->delete($this->request->id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getAll()
    {
        $statuses = new StatusesModel();
        return $statuses->getAll();
    }

    public function getActive()
    {
        $statuses = new StatusesModel();
        return $statuses->getActive();
    }

    public function find()
    {
        $statuses = new StatusesModel();
        return $statuses->find($this->request->q);
    }
}