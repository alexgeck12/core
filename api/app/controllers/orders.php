<?php
namespace controllers;
use models\orders as OrderModel;

class orders extends authorized
{
    public function add()
    {
        $orders = new OrderModel();
        return $orders->add($this->requestFiltered(['product_id']));
    }
}