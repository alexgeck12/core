<?php
namespace controllers;
use core\validate;
use models\filters as filtersModel;

class filters extends authorized
{
	const LIMIT = 16;

	public function get()
	{
		if (validate::int(isset($this->request->id)?$this->request->id:'', ['required' => true])) {
			$filters = new filtersModel();
			return $filters->get($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function add()
	{
		if (validate::string($this->request->name, ['required' => true])) {
			$filters = new filtersModel();
			return $filters->add($this->requestFiltered(['name', 'property_id', 'prefix', 'type', 'pos', 'categories']));
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function update()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$filters = new filtersModel();
			return $filters->update($this->request->id, $this->requestFiltered(['name', 'property_id', 'prefix', 'type', 'pos', 'categories']));
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function delete()
	{
		if (validate::int(isset($this->request->id)?$this->request->id:'', ['required' => true])) {
			$filters = new filtersModel();
			return $filters->delete($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function getAll()
	{
		$filters = new filtersModel();
		return $filters->getAll();
	}

	public function savePosition()
	{
		$filters = $this->request->filters;
		return $this->saveTree($filters, 0);
	}

	public function saveTree(&$branch, $pos) {

		foreach ($branch as $tree) {
			$pos++;
			if (isset($tree['children'])) {
				$this->saveTree($tree['children'], $tree['id']);
			}

			$filtersModel = new filtersModel();
			$filterItem = $filtersModel->get($tree['id']);
			$filterItem['pos'] = $pos;
			$filtersModel->update($tree['id'], $filterItem);
		}

		return $branch;
	}

	public function filterPreview()
	{
		$filters = new filtersModel();
		return $filters->filterPreview((array)$this->request);
	}

	public function selection()
	{
		$filters = new filtersModel();
		return $filters->selection((array)$this->request, self::LIMIT);
	}
}