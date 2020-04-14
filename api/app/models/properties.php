<?php
namespace models;
use core\model;

class properties extends model
{
	public $table = 'properties';

	public function get($id)
	{
		$info = $this->db->getRowByKeys($this->table, ['id' => $id]);

		if ($info['type'] == 'select') {
			$dictionary = $this->db->getFiltered('dictionary', ['property_id' => $info['id']]);
			foreach ($dictionary as $variant) {
				$info['dictionaries'][$variant['id']] = $variant;
			}
		}

		$info['value'] = false;

		return $info;
	}

	public function add($data)
	{
		if ($data['type'] == 'select') {
			$dictionaries = $data['dictionaries'];
			$_dictionaries = [];

			unset($data['dictionaries']);
			unset($data['value']);

			$property_id = $this->db->insert($this->table, $data);

			foreach ($dictionaries as $dictionary) {
				$_dictionaries[] = ['property_id' => $property_id, 'value' => $dictionary['value']];
			}

			$this->db->multi_insert('dictionary', $_dictionaries);

			return $property_id;
		} else {
			return $this->db->insert($this->table, $data);
		}
	}

	public function update($id, $data)
	{
		if ($data['type'] == 'select') {
			$dictionaries = $data['dictionaries'];
			unset($data['dictionaries']);
			unset($data['value']);

			foreach ($dictionaries as &$dictionary) {
				if (!empty($dictionary['id'])) {
					$this->db->update('dictionary', $dictionary, ['id' => $dictionary['id'], 'property_id' => $id]);
				} else {
					$dictionary['id'] = $this->db->insert('dictionary', ['property_id' => $id, 'value' => $dictionary['value']]);
				}
			}
			$this->redis->del('product_properties');
			return $this->db->update($this->table, $data, ['id' => $id]);
		} else {
			$this->redis->del('product_properties');
			return $this->db->update($this->table, $data, ['id' => $id]);
		}
	}

	public function delete($id)
	{
		$this->db->delete($this->table, ['id' => $id]);
		$this->db->delete('dictionary', ['property_id' => $id]);
		$this->db->delete('product_properties', ['property_id' => $id]);
		return true;
	}

	public function deleteDictionary($dictionary_id, $property_id)
	{
		return $this->db->delete('dictionary', ['id' => $dictionary_id, 'property_id' => $property_id]);
	}

	public function getByCategoryId($category_id)
	{
		$properties = $this->db->getFiltered($this->table, ['category_id' => $category_id]);
		foreach ($properties as &$property) {
			if ($property['type'] == 'select') {
				$dictionary = $this->db->getFiltered('dictionary', ['property_id' => $property['id']]);
				foreach ($dictionary as $variant) {
					$property['dictionaries'][$variant['id']] = $variant;
				}
			}
			$property['value'] = false;
		}

		return $properties;
	}

	public function getAll()
	{
		return $this->db->getGrid($this->table, []);
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

	public function addProductProperties($data, $product_id)
	{
		$this->db->multi_insert('product_properties', $data);
		$this->redis->hDel('product_properties', $product_id);
	}

	public function removeProductProperties($product_id)
	{
		$this->redis->hDel('product_properties', $product_id);
		return $this->db->delete('product_properties', ['product_id' => $product_id]);
	}

	public function getByProductId($id)
	{
		$data = json_decode($this->redis->hGet('product_properties', $id), true);

		if (!$data) {
			$data = $this->db->query("
				SELECT
					p.*,
					d.id as dictionary_id,
					c.name as category,
					(case when (p.type = 'select') then d.value else pp.value end) as value
				FROM product_properties pp
				LEFT JOIN properties p
					ON pp.property_id = p.id
				LEFT JOIN dictionary d
					ON pp.dictionary_id = d.id
				LEFT JOIN categories c
					ON p.category_id = c.id
				WHERE pp.product_id = $id
				ORDER BY p.pos
			")->fetch_all(MYSQLI_ASSOC);

			foreach ($data as &$row) {
				if ($row['type'] == 'select') {
					$dictionary = $this->db->query("SELECT id, value FROM dictionary WHERE property_id = $row[id] ORDER BY value+0")->fetch_all(MYSQLI_ASSOC);
					$i = 0;
					foreach ($dictionary as $variant) {
						$row['dictionary'][$i] = $variant; $i++;
					}
				}
			}

			$this->redis->hSet('product_properties', $id, json_encode($data));
		}

		return $data;
	}
}