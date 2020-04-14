<?php
namespace models;
use core\model;

class services extends model
{
    private $table = 'services';

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
            SELECT t.*, m.url
            FROM ".$this->table." t
            LEFT JOIN meta m ON t.meta_id = m.id            
            ORDER BY t.id ASC
            LIMIT ".$limit*($page-1).", ".$limit."
        ")->fetch_all(MYSQLI_ASSOC);
        foreach ($data as &$item) {
            $item['img'] = json_decode($item['img'], true);
        }
        return $data;
    }

    public function getListPages($limit)
    {
        $count = count($this->db->getFiltered($this->table, [], ["id" => "ASC"]));
        return ceil($count / $limit);
    }

    public function getActive()
    {
        $data = $this->db->query("
            SELECT t.*, m.url
            FROM ".$this->table." t
            LEFT JOIN meta m ON t.meta_id = m.id            
            WHERE t.active = 1
            ORDER BY t.id ASC            
        ")->fetch_all(MYSQLI_ASSOC);
        foreach ($data as &$item) {
            $item['img'] = json_decode($item['img'], true);
        }
        return $data;
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