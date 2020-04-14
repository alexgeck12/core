<?php
namespace models;

use core\model;

class cart extends model
{
	private $cart;

	public function get($user)
    {
        return json_decode($this->redis->hGet("cart", $user), true);
    }

    public function add($user, $product_id, $quantity = 1, $personal = false, $code = false)
    {
		$this->cart = $this->get($user);
        $this->cart['personal'] = isset($personal)?$personal:$this->cart['personal'];
	    $this->cart['code'] = isset($code)?$code:$this->cart['code'];
	    $this->cart['sub_total'] = 0;

	    if (!isset($this->cart)) {
		    $this->cart = [];
	    }

	    $this->cart['products'][$product_id] = [
	    	'id' => $product_id,
		    'quantity' => $quantity
	    ];

	    $this->calculateProducts($this->cart['products']);

	    if (isset($this->cart['code'])) {
	    	$this->calculateDiscount();
	    }

	    if (isset($this->cart['personal']['delivery_id'])) {
		    $this->calculateDelivery();
	    }

	    $this->calculateTotal();

	    return $this->redis->hSet("cart", $user, json_encode($this->cart, JSON_UNESCAPED_UNICODE));
    }

    public function remove($user, $product_id)
    {
	    $this->cart = $this->get($user);
	    $this->cart['personal'] = isset($personal)?$personal:$this->cart['personal'];
	    $this->cart['code'] = isset($code)?$code:$this->cart['code'];
	    $this->cart['sub_total'] = 0;
	    unset($this->cart['products'][$product_id]);

	    if (count($this->cart['products']) == 0) {
		    $this->cart = [];
	    } else {
		    $this->calculateProducts($this->cart['products']);

		    if (!empty($this->cart['code'])) {
			    $this->calculateDiscount();
		    }

		    if (isset($this->cart['personal']['delivery_id'])) {
			    $this->calculateDelivery();
		    }

		    $this->calculateTotal();
	    }

	    return $this->redis->hSet("cart", $user, json_encode($this->cart, JSON_UNESCAPED_UNICODE));
    }

	public function clear($user)
	{
		return $this->redis->hSet("cart", $user, json_encode([], JSON_UNESCAPED_UNICODE));
    }

    private function calculateProducts($products)
    {
    	$ids = [];

	    foreach ($products as $id => $item) {
		    $ids[] = $id;
	    }

	    $products = $this->db->query("
			SELECT p.id, p.cost, p.h1, p.genimg, m.url 
			FROM products p 
			LEFT JOIN meta m 
			ON p.meta_id = m.id
			WHERE p.id IN(".implode(',' ,$ids).")")->fetch_all(MYSQLI_ASSOC);

	    foreach ($products as $product) {
		    foreach ($this->cart['products'] as $id => $item) {
			    if ($product['id'] == $id) {
				    $item['cost'] = $product['cost'];
				    $item['name'] = $product['h1'];
				    $item['url'] = $product['url'];
				    $item['genimg'] = $product['genimg'];
				    $item['total'] = (string) ($product['cost'] * $item['quantity']);

				    $this->cart['sub_total'] += $item['total'];
				    $this->cart['products'][$id] = $item;

			    }
		    }
	    }

	    $this->cart['count'] = count($this->cart['products']);
    }

	private function calculateDiscount()
	{
		if ($this->cart['code'] == '') {
			unset($this->cart['notice']);
			unset($this->cart['code']);
			unset($this->cart['discount']);
		} else {
			$result = $this->db->getRowByKeys('promocodes', [
				'code' => $this->cart['code'],
				'active' => '1',
				'date_start' => '<= '.date('Y-m-d'),
				'date_end' => '>= '.date('Y-m-d'),
			]);

			if ($result) {
				switch ($result['type']) {
					case 'percent':
						$this->cart['discount'] = $this->cart['sub_total'] / 100 * $result['value'];
						break;
					case 'fix':
						$this->cart['discount'] = $result['value'];
						break;
				}
				$this->cart['promocode_id'] = $result['id'];
				unset($this->cart['notice']);
			} else {
				$this->cart['notice'] = 'Купон либо недействителен, либо истек срок его действия, либо достигнут предел его использования!';
				unset($this->cart['code']);
				unset($this->cart['discount']);
				unset($this->cart['promocode_id']);
			}
		}
	}

	private function calculateDelivery()
	{
		$result = $this->db->getRowByKeys('deliveries', ['id' => $this->cart['personal']['delivery_id'], 'active' => '1']);
		$this->cart['delivery'] = $result['cost'];
	}

	private function calculateTotal()
	{
		$this->cart['total'] = $this->cart['sub_total'] - $this->cart['discount'] + $this->cart['delivery'];
	}
}