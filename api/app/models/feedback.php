<?php
namespace models;
use core\model;

class feedback extends model
{
	private $table = 'feedback';

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

	public function getAll($type = 'message')
	{
		return $this->db->getFiltered($this->table, ['type' => $type], ['created' => 'DESC']);
	}

	public function getList($page, $limit, $type = 'message')
	{
		return $this->db->getFiltered($this->table, ['type' => $type], ['created' => 'DESC'], [$limit*($page-1), $limit] );
	}

	public function getListPages($limit, $type = 'message')
	{
		$count = count($this->db->getFiltered($this->table, ['type' => $type], ["id" => "DESC"], [], 'id'));
		return ceil($count / $limit);
	}

	public function getUnviewed($type = 'message')
	{
		return $this->db->getFiltered($this->table, ['viewed' => 0, 'type' => $type], ['created' => 'DESC']);
	}

	public function find($q, $type)
	{
		$data = $this->db->query("
            SELECT f.*
            FROM ".$this->table." f
            WHERE (f.type = '".$type."') AND
                  (f.name LIKE '%".$q."%' OR
                   f.phone LIKE '%".$q."%' OR
                   f.email LIKE '%".$q."%' OR
                   f.message LIKE '%".$q."%')                   
            ORDER BY f.created DESC 
        ")->fetch_all(MYSQLI_ASSOC);

		return $data;
	}

	public function getToNotify($type)
	{
		return $this->db->getFiltered($this->table, ['type' => $type, 'notified' => 0]);
	}

	public function setNotified($id)
	{
		return $this->db->update($this->table, ['notified' => 1], ['id' => $id]);
	}
}