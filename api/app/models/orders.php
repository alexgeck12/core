<?php
namespace models;
use core\model;

class orders extends model
{
	private $table = 'orders';

	public function get($id)
	{
		$order = $this->db->getRowByKeys($this->table, ['id' => $id]);
		$order['products'] = $this->db->query(
			"select p.id, p.name, p.cost, op.quantity from products as p
				  left join order_products as op
				  on p.id = op.product_id
				  where op.order_id = $id"
		)->fetch_all(MYSQLI_ASSOC);
		return $order;
	}

	public function add($data)
	{
		return $this->db->insert($this->table, $data);
	}

	public function update($id, $data)
	{
		return $this->db->update($this->table, $data, ['id' => $id]);
	}

	public function addProducts($order_id, $products)
	{
		$data = [];
		foreach ($products as $product) {
			$data[$product['id']] = [
				'order_id' => $order_id,
				'product_id' => $product['id'],
				'quantity' => $product['quantity']
			];
		}
		return $this->db->multi_insert('order_products', $data);
	}

	public function getList($page, $limit)
	{
		return $this->db->getFiltered($this->table, [], ["id" => "DESC"], [$limit*($page-1), $limit]);
	}

	public function getListPages($limit)
	{
		$count = count($this->db->getFiltered($this->table, [], ["id" => "DESC"]));
		return ceil($count / $limit);
	}
}