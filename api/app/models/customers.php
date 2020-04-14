<?php
namespace models;
use core\model;
use core\helper;

class customers extends model
{
	private $table = 'customers';

	public function get($id)
	{
		return $this->db->getRowByKeys($this->table, ['id' => $id]);
	}

	public function getByPhone($phone)
	{
		return $this->db->getRowByKeys($this->table, ['phone' => $phone]);
	}

	public function getByEmail($email)
	{
		return $this->db->getRowByKeys($this->table, ['email' => $email]);
	}

	public function add($data)
	{
		if ($result = $this->getByPhone($data['phone'])) {
			return $result['id'];
		}
		if ($result = $this->getByEmail($data['email'])) {
			return $result['id'];
		}

		$data['password'] = helper::generatePassword();
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
		return ceil(count($this->db->getFiltered($this->table, [], [])) / $limit);
	}

	public function find($q)
	{
		return $this->db->query("
            SELECT f.*
            FROM ".$this->table." f
            WHERE (f.name LIKE '%".$q."%' OR
                   f.email LIKE '%".$q."%' OR
                   f.phone LIKE '%".$q."%')                   
            ORDER BY f.created DESC ")->fetch_all(MYSQLI_ASSOC);
	}
}