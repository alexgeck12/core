<?php
namespace models;
use core\model;

class orders extends model
{
    private $table = 'orders';

    public function add($data)
    {
        return $this->db->insert($this->table, $data);
    }
}