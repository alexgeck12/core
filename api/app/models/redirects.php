<?php
namespace models;
use core\model;

class redirects extends model
{
    private $table = 'redirects';

    public function get($id)
    {
        return $this->db->getRowByKeys($this->table, ['id' => $id]);
    }

    public function getByURI($uri)
    {
        $data = $this->db->getRowByKeys($this->table, ['url' => $uri, 'active' => '1']);
        return isset($data)?$data:'';
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

    public function getList($page, $limit)
    {
        return $this->db->getFiltered($this->table, [], ["id" => "DESC"], [$limit*($page-1), $limit]);
    }

    public function getListPages($limit)
    {
        $count = count($this->db->getFiltered($this->table, [], ["id" => "DESC"]));
        return ceil($count / $limit);
    }

    public function getActive()
    {
        return $this->db->getFiltered($this->table, ['active' => '1'], ["id" => "DESC"]);
    }

    public function find($q)
    {
        $data = $this->db->query("
            SELECT r.*
            FROM ".$this->table." r
            WHERE r.url LIKE '%".$q."%' OR                  
                  r.redirect_url LIKE '%".$q."%' 
            ORDER BY r.id DESC
        ")->fetch_all(MYSQLI_ASSOC);

        return $data;
    }
}