<?php
namespace models;
use core\model;

class statuses extends model
{
    private $table = 'statuses';

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

    public function getAll()
    {
        return $this->db->getGrid($this->table);
    }

    public function getActive()
    {
        return $this->db->getFiltered($this->table, ['active' => '1']);
    }

    public function find($q)
    {
        return $this->db->query("
            SELECT p.*
            FROM ".$this->table." p
            WHERE p.name LIKE '%".$q."%' 
            ORDER BY p.id ASC
        ")->fetch_all(MYSQLI_ASSOC);
    }
}