<?php
namespace controllers;
use models\addresses as AddressesModel;
use core\validate;

class addresses extends authorized
{
	const LIMIT = 16;

	public function get()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$addresses = new AddressesModel();
			return $addresses->get($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function add()
	{
		if (validate::int($this->request->customer_id, ['required' => true]) &&
			validate::string($this->request->address, ['required' => true]) &&
			validate::int($this->request->kladr_id, ['required' => true])
		) {
			$addresses = new AddressesModel();
			return $addresses->add($this->requestFiltered(['customer_id', 'address', 'kladr_id', 'main']));
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function update()
	{

	}

	public function getList()
	{
		$addresses = new AddressesModel();
		return $addresses->getList($this->request->page?$this->request->page:1, self::LIMIT);
	}

	public function getListPages()
	{
		$addresses = new AddressesModel();
		return $addresses->getListPages(self::LIMIT);
	}
}