<?php
namespace controllers;
use core\api;
use core\controller;

class notFound extends controller
{
	public function index()
	{
     	$this->render('404', []);
	}
}