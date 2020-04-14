<?php
namespace controllers;
use models\promocodes as PromocodesModel;
use core\validate;

class promocodes extends authorized
{
	const LIMIT = 16;

	public function get()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$promocodesModel = new PromocodesModel();
			return $promocodesModel->get($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function add()
	{
		if (validate::string($this->request->code, ['required' => true])
			&& validate::string($this->request->type, ['required' => true])
			&& validate::string($this->request->value, ['required' => true])
		) {
			$promocodesModel = new PromocodesModel();
			return $promocodesModel->add($this->requestFiltered(
				['code', 'type', 'value', 'active', 'gift', 'date_start', 'date_end', 'uses_total'])
			);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function update()
	{
		if (validate::int($this->request->id, ['required' => true])
			&& validate::string($this->request->code, ['required' => true])
			&& validate::string($this->request->type, ['required' => true])
			&& validate::string($this->request->value, ['required' => true])
		) {
			$promocodesModel = new PromocodesModel();
			return $promocodesModel->update($this->request->id,
				$this->requestFiltered(['code', 'type', 'value', 'active', 'gift', 'date_start', 'date_end', 'uses_total'])
			);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function delete()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$promocodesModel = new PromocodesModel();
			return $promocodesModel->delete($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function getList()
	{
		$promocodesModel = new PromocodesModel();
		return $promocodesModel->getList($this->request->page?$this->request->page:1, self::LIMIT);
	}

	public function getListPages()
	{
		$promocodesModel = new PromocodesModel();
		return $promocodesModel->getListPages(self::LIMIT);
	}

	public function apply()
	{
		if (validate::string($this->request->code, ['required' => true])
			&& validate::string($this->request->user, ['required' => true])
		) {
			$promocodesModel = new PromocodesModel();
			return $promocodesModel->apply($this->request->code, $this->request->user);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}
}