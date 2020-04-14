<?php
namespace controllers;
use models\properties as PropertiesModel;
use core\validate;

class properties extends authorized
{
	const LIMIT = 16;

	public function get()
	{
		$properties = new PropertiesModel();

		if (validate::int($this->request->id, ['required' => true])) {
			return $properties->get($this->request->id);
		}

		if (validate::string($this->request->category_id, ['required' => true])) {
			return $properties->getByCategoryId($this->request->category_id);
		}

		return ['error' => 'Ошибка валидации'];
	}

	public function add()
	{
		if (validate::string($this->request->name, ['required' => true])
			&& validate::string($this->request->type, ['required' => true])
		) {
			$properties = new PropertiesModel();

			return $properties->add($this->requestFiltered(
				['name', 'type', 'pos', 'title', 'show_in_product', 'category_id', 'dictionaries']
			));
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function update()
	{
		if (validate::int($this->request->id, ['required' => true])
			&& validate::string($this->request->name, ['required' => true])
			&& validate::string($this->request->type, ['required' => true])
		) {
			$properties = new PropertiesModel();
			return $properties->update($this->request->id,
				$this->requestFiltered(
					['name', 'type', 'pos', 'title', 'show_in_product', 'category_id', 'dictionaries'])
			);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function delete()
	{
		$properties = new PropertiesModel();

		if (validate::int($this->request->id, ['required' => true])
		) {
			return $properties->delete($this->request->id);
		}

		if (validate::int($this->request->dictionary_id, ['required' => true])
			&& validate::int($this->request->property_id, ['required' => true])
		) {
			return $properties->deleteDictionary($this->request->dictionary_id, $this->request->property_id);
		}

		return ['error' => 'Ошибка валидации'];
	}

	public function getAll()
	{
		$properties = new PropertiesModel();
		return $properties->getAll();
	}

	public function getList()
	{
		$properties = new PropertiesModel();
		return $properties->getList($this->request->page?$this->request->page:1, self::LIMIT);
	}

	public function getListPages()
	{
		$properties = new PropertiesModel();
		return $properties->getListPages(self::LIMIT);
	}
}