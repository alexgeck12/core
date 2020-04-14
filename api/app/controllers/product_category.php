<?php
namespace controllers;

class product_category extends authorized
{
	private $model;

	public function __construct()
	{
		$this->model = new \models\product_category();
	}

	public function getCategories()
	{
		if ($this->request->id) {
			return $this->model->getByProduct($this->request->id);
		}
	}

	public function getProducts()
	{
		if ($this->request->id) {
			return $this->model->getByCategories($this->request->id);
		}
	}

	public function add()
	{
		$this->model->add((array)$this->request->data);
	}

	public function remove()
	{
		$this->model->remove($this->request->product_id);
	}
}