<?php
namespace models;
use core\model;

class comments extends model
{
	private $table = 'comments';

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

	public function getList($page, $limit)
	{
		return $this->db->getFiltered($this->table, [], ["id" => "DESC"], [$limit*($page-1), $limit]);
	}

	public function getListPages($limit)
	{
		$count = count($this->db->getFiltered($this->table, [], []));
		return ceil($count / $limit);
	}

	public function getByItem($item_id, $type, $page, $limit)
	{
		return $this->db->getFiltered($this->table, ['item_id' => $item_id, 'type' => $type, 'active' => 1], ['created' => 'DESC'], [$limit*($page-1), $limit]);
	}

	public function getRating($item_id, $type)
	{
		return $this->db->query("SELECT ROUND(SUM(rating)/COUNT(id), 0) as rating, COUNT(id) as count FROM comments WHERE item_id = $item_id AND type = '".$type."' AND active = 1")->fetch_assoc();
	}

	public function getUnviewed($type = 'product')
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
                   f.email LIKE '%".$q."%' OR
                   f.comment LIKE '%".$q."%')                   
            ORDER BY f.created DESC ")->fetch_all(MYSQLI_ASSOC);

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