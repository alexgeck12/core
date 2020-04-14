<?php
namespace models;
use core\model;

class promocodes extends model
{
	public $table = 'promocodes';

	public function add($data)
	{
		return $this->db->insert($this->table, $data);
	}

	public function get($id)
	{
		return $this->db->getRowByKeys($this->table, ['id' => $id]);
	}

	public function getList($page, $limit)
	{
		$page = abs($page - 1);
		return $this->db->getGrid($this->table, $page, $limit);
	}

	public function getListPages($limit)
	{
		$count = count($this->db->getFiltered($this->table, [], ["id" => "ASC"]));
		return ceil($count / $limit);
	}

	public function update($id, $data)
	{
		return $this->db->update($this->table, $data, ['id' => $id]);
	}

	public function getGift()
	{
		return $this->db->getFiltered($this->table, ['gift' => '1'])[0];
	}

	public function delete($id)
	{
		return $this->db->delete($this->table, ['id' => $id]);
	}

	public function apply($code, $user)
	{
		$result = $this->db->getRowByKeys($this->table, ['code' => $code, 'active' => '1']);

		if (!$result) {
			return ['error' => 'Не верный промокод'];
		} else {
			$cart = json_decode($this->redis->hGet("cart", $user), true);

			switch ($result['type']) {
				case 'percent':
					$cart['code'] = $code;
					$cart['discount'] = (string) ($cart['total'] / 100 * $result['value']);
					$cart['by_discount'] = (string) ($cart['total'] - ($cart['total'] / 100 * $result['value']));
					break;
				case 'fix':
					$cart['code'] = $code;
					$cart['discount'] = (string) $result['value'];
					$cart['by_discount'] = (string) ($cart['total'] - $result['value']);
					break;
			}

			$this->redis->hSet("cart", $user, json_encode($cart, JSON_UNESCAPED_UNICODE));

			return $cart;
		}
	}
}