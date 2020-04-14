<?php
namespace models;
use core\model;

class banners extends model
{
    private $table = 'banners';

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
    	return $this->db->getFiltered($this->table, [], ['pos' => 'ASC']);
    }

    public function getActive()
    {
	    return $this->db->getFiltered($this->table, ['active' => '1'], ['pos' => 'ASC']);
    }
}