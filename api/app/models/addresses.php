<?php
namespace models;
use core\model;

class addresses extends model
{
    private $table = 'addresses';

    public function get($id)
    {
	    return $this->db->getRowByKeys($this->table, ['id' => $id]);
    }

    public function add($data)
    {
    	return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        $this->db->delete($this->table, ['id' => $id]);
        return true;
    }
}