<?php
namespace models;
use core\model;

class products extends model
{
    private $table = 'products';

    public function getList($category_id)
    {
        return $this->db->getFiltered($this->table, ['category_id' => $category_id]);
    }
}