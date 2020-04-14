<?php
namespace models;
use core\model;

class deliveries extends model
{
    private $table = 'deliveries';

    public function get($id)
    {
        $data = $this->db->getRowByKeys($this->table, ['id' => $id]);
        $data['img'] = json_decode($data['img'], true);

        return $data;
    }

    public function getByMetaId($id)
    {
        $data = $this->db->getRowByKeys($this->table, ['meta_id' => $id]);
        $data['img'] = json_decode($data['img'], true);

        return $data;
    }

    public function add($data)
    {
	    return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id, $meta_id)
    {
        $this->db->delete('meta', ['id' => $meta_id]);
        $this->db->delete($this->table, ['id' => $id]);
        return true;
    }

    public function getList($page, $limit)
    {
        $data = $this->db->query("
            SELECT t.*
            FROM ".$this->table." t
            ORDER BY t.id ASC
            LIMIT ".$limit*($page-1).", ".$limit."
        ")->fetch_all(MYSQLI_ASSOC);

        return $data;
    }

    public function getListPages($limit)
    {
        $count = count($this->db->getFiltered($this->table, [], ["id" => "ASC"]));
        return ceil($count / $limit);
    }

    public function getActive()
    {
	    return $this->db->getFiltered($this->table, ['active' => '1']);
    }

    public function find($q)
    {
        $data = $this->db->query("
            SELECT p.*
            FROM ".$this->table." p
            WHERE p.name LIKE '%".$q."%' OR
                  p.description LIKE '%".$q."%' OR
                  p.sm_description LIKE '%".$q."%' OR
                  p.h1 LIKE '%".$q."%' 
            ORDER BY p.id ASC
        ")->fetch_all(MYSQLI_ASSOC);

        return $data;
    }
}