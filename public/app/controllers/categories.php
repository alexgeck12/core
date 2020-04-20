<?php
namespace controllers;
use core\api;
use core\controller;

class categories extends controller
{
	public function get()
	{
        $categories = api::get('categories/getList');
	    $products = api::get('products/getList', ['category_id' => $_GET['category_id']]);

	    $this->render('category', [
            'categories' => $categories,
            'products' => $products
        ]);
	}
}
