<?php
namespace controllers;
use models\customers as CustomersModel;
use core\validate;

class customers extends authorized
{
	const LIMIT = 16;

	public function get()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$customers = new CustomersModel();
			return $customers->get($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function add()
	{
		if (validate::phone($this->request->phone, ['required' => true]) &&
			validate::email($this->request->email, ['required' => true]) &&
			validate::string($this->request->name, ['required' => true])
		) {
			$customers = new CustomersModel();
			return $customers->add($this->requestFiltered(['name', 'phone', 'email']));
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function update()
	{

	}

	public function getList()
	{
		$customers = new CustomersModel();
		return $customers->getList($this->request->page?$this->request->page:1, self::LIMIT);
	}

	public function getListPages()
	{
		$customers = new CustomersModel();
		return $customers->getListPages(self::LIMIT);
	}
}