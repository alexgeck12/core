<?php
namespace controllers;

use core\validate;

class similar_products extends authorized
{
	private $model;

	public function __construct()
	{
		parent::__construct();
		$this->model = new \models\similar_products();
	}

	public function getSimilarProducts()
	{
		if (validate::int($this->request->id)) {
			return $this->model->getByProduct($this->request->id);
		}
	}

    public function getSimilarProductsActiveAndAvailable()
    {
        if (validate::int($this->request->id)) {
            return $this->model->getByProductActiveAndAvailable($this->request->id);
        }
    }

	public function getProducts()
	{
		if (validate::int($this->request->id)) {
			return $this->model->getBySimilarProducts($this->request->id);
		}
	}

	public function add()
	{
		$this->model->add($this->request->product_id, $this->request->similar_product_id);
	}

	public function remove()
	{
		$this->model->remove($this->request->product_id, $this->request->similar_product_id);
	}
	
	public function getCount()
	{
		if (validate::int($this->request->id)) {
			return $this->model->getCount($this->request->id);
		}
	}
}