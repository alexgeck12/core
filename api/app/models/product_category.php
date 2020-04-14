<?php
namespace models;
use core\model;

class product_category extends model
{
	public $table = 'product_category';

	public function getByCategories($id)
	{
		return $this->db->query("
			SELECT p.id, p.name, p.img, pc.main
			FROM $this->table pc
			LEFT JOIN product p
				ON pc.product_id = p.id
			WHERE pc.category_id = $id
		")->fetch_all(MYSQLI_ASSOC);
	}

	public function getByProduct($id)
	{
		return $this->db->query("
			SELECT c.id, c.name, c.img, pc.main
			FROM $this->table pc
			LEFT JOIN categories c
				ON pc.category_id = c.id
			WHERE pc.product_id = $id
		")->fetch_all(MYSQLI_ASSOC);
	}

	public function add($data)
	{
		return $this->db->multi_insert($this->table, $data);
	}

	public function remove($product_id)
	{
		return $this->db->delete($this->table, array('product_id' => $product_id));
	}
}