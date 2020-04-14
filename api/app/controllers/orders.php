<?php
namespace controllers;
use models\orders as OrdersModel;
use core\validate;
use core\telegram;

class orders extends authorized
{
	const LIMIT = 16;

	public function get()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$orders = new OrdersModel();
			return $orders->get($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function add()
	{
		if (validate::int($this->request->customer_id, ['required' => true]) &&
			validate::int($this->request->address_id, ['required' => true]) &&
			validate::int($this->request->delivery_id, ['required' => true])
		) {
			$orders = new OrdersModel();
			$order_id = $orders->add(
				$this->requestFiltered([
					'customer_id',
					'delivery_id',
					'promocode_id',
					'address_id',
					'payment_type',
					'name',
					'phone',
					'email',
					'middlename',
					'lastname',
					'comment',
					'discount',
					'sub_total',
					'shipping',
					'total'
				])
			);

			// добавляем продукты к заказу
			$orders->addProducts($order_id, $this->request->products);

			return $order_id;
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function update()
	{

	}

	public function getList()
	{
		$orders = new OrdersModel();
		return $orders->getList($this->request->page?$this->request->page:1, self::LIMIT);
	}

	public function getListPages()
	{
		$orders = new OrdersModel();
		return $orders->getListPages(self::LIMIT);
	}
}