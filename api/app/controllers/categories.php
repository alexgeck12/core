<?php
namespace controllers;
use core\validate;
use models\categories as CategoryModel;

class categories extends authorized
{

    public function getList()
    {
        $categories = new CategoryModel();
        return $categories->getList();
    }
}