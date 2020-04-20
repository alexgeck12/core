<?php
namespace controllers;
use core\api;
use core\controller;

class main extends controller
{
	public function index()
	{
	    $categories = api::get('categories/getList');
        $this->render('main', ['categories' => $categories]);
	}
}
