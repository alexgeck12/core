<?php
namespace controllers;
use models\products as ProductModel;

class products extends authorized
{

    public function getList()
    {
        $products = new ProductModel();
        return $products->getList($this->requestFiltered(['category_id']));
    }
}