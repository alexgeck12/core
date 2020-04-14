<?php
namespace models;
use core\model;

class menu extends model
{
    private $table = 'menu';

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
        return $this->db->delete($this->table, ['id' => $id]);
    }

    public function getAll($type = 'top')
    {
        return $this->db->getFiltered($this->table, ['type' => $type], ['pos' => 'ASC']);
    }

    public function getActive($type = 'top')
    {
        return $this->db->getFiltered($this->table, ['type' => $type, 'active' => '1'], ['pos' => 'ASC']);
    }
}