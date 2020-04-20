<?php
namespace controllers;
use core\api;
use core\controller;

class order extends controller
{
	public function add()
	{
	    echo json_decode(api::get('orders/add', ['product_id' => $_POST['product_id']]));
	}
}
