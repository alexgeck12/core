<?php
namespace models;
use core\model;

class categories extends model
{
    private $table = 'categories';

    public function getList()
    {
        return $this->db->getFiltered($this->table);
    }
}