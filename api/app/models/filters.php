<?php
namespace models;
use core\model;

class filters extends model
{
	public $table = 'filters';

	public function get($id)
	{
		$filter = $this->db->getRowByKeys($this->table, ['id' => $id]);
		$filter['categories'] =
			$this->db->query("
				SELECT filter_categories.*, categories.name 
				FROM filter_categories 
				LEFT JOIN categories ON categories.id = filter_categories.category_id
				WHERE filter_categories.filter_id = ".$filter['id'])->fetch_all(MYSQLI_ASSOC);

		return $filter;
	}

	public function add($data)
	{
		if ($data['categories']) {
			$categories = $data['categories'];
			unset($data['categories']);
			$filter_id = $this->db->insert($this->table, $data);

			$filter_categories = [];
			foreach ($categories as $category) {
				$filter_categories[] = ['filter_id' => $filter_id, 'category_id' => $category['category_id']];
			}

			$this->db->multi_insert('filter_categories', $filter_categories);

			return $filter_id;
		} else {
			return $this->db->insert($this->table, $data);
		}
	}

	public function update($id, $data)
	{
		if ($data['categories']) {
			$categories = $data['categories'];
			unset($data['categories']);

			$this->db->update($this->table, $data, ['id' => $id]);
			$this->db->delete('filter_categories', ['filter_id' => $id]);

			$filter_categories = [];
			foreach ($categories as $category) {
				$filter_categories[] = ['filter_id' => $id, 'category_id' => $category['category_id']];
			}

			$this->db->multi_insert('filter_categories', $filter_categories);
			return $id;
		} else {
			return $this->db->update($this->table, $data, ['id' => $id]);
		}
	}

	public function delete($id)
	{
		$this->db->delete($this->table, ['id' => $id]);
		$this->db->delete('filter_categories',['filter_id' => $id]);
		return true;
	}

	public function getAll()
	{
		return $this->db->query("
            SELECT f.*, c.name as category 
            FROM filters f
            LEFT JOIN properties p
            ON p.id = f.property_id
            
            LEFT JOIN categories c
            ON c.id = p.category_id
            
            ORDER BY c.name ASC, pos ASC
            ")->fetch_all(MYSQLI_ASSOC);
	}

	public function filterPreview($data)
	{
		$category_id = (int)$data['category_id'];
		$cost = [];

		if (isset($data['cost'])) {
			list($cost['selected_min'],$cost['selected_max']) = $data['cost'];
		}

		unset($data['category_id']);
		unset($data['cost']);
		unset($data['page']);

		foreach ($data as $filter => $value) {
			$prefixes[] = $filter;
		}

		$_filters = $this->db->query("
			SELECT * FROM filter_categories fc
			LEFT JOIN filters f ON f.id = fc.filter_id
			WHERE fc.category_id = $category_id
			ORDER BY f.pos")->fetch_all(MYSQLI_ASSOC);

		if (empty($_filters)) {
			return ['filters' => [], 'cost' => $cost];
		}

		foreach ($_filters as $filter) {
			$filters[$filter['prefix']] = $filter;
		}

		$filter_sql = [];
		foreach ($data as $filter => $value) {
			if (!empty($value)) {
				$filter_sql[$filter] = "SELECT product_id 
					  FROM product_properties pp 
					  RIGHT JOIN products p ON p.id = pp.product_id   
					  WHERE p.active = 1 AND pp.property_id = " . $filters[$filter]['property_id'] . " 
						AND pp.dictionary_id IN (" . implode(",", $value) . ")";
			}
		}

		foreach ($filters as $prefix => &$filter) {
			$temp = $filter_sql;
			unset($temp[$prefix]);
			if (!empty($temp)) {
				$filter['dictionary'] = $this->db->query("
					SELECT d.id, d.value, COUNT(pp.dictionary_id) as count, show_hidden FROM dictionary d 
					LEFT JOIN product_properties pp
						ON d.property_id = pp.property_id 
							AND d.id = pp.dictionary_id
							AND pp.product_id IN (".implode(") AND pp.product_id IN (", $temp).") 
					WHERE d.property_id = $filter[property_id] group by d.id ORDER BY d.pos, d.id ASC
				")->fetch_all(MYSQLI_ASSOC);
			} else {
				/*
				$filter['dictionary'] = $this->db->query("
					SELECT d.id, d.value, COUNT(pp.dictionary_id) as count, show_hidden FROM dictionary d
					LEFT JOIN product_properties pp
						ON d.property_id = pp.property_id
							AND d.id = pp.dictionary_id
					WHERE d.property_id = $filter[property_id] group by d.id ORDER BY d.pos ASC
				")->fetch_all(MYSQLI_ASSOC);
				*/
				$filter['dictionary'] = $this->db->query("
					SELECT d.id, d.value, COUNT(pp.dictionary_id) as count, show_hidden FROM dictionary d 
					LEFT JOIN product_properties pp
						ON d.property_id = pp.property_id 
							AND d.id = pp.dictionary_id
					LEFT JOIN products p ON p.id = pp.product_id		 
					WHERE p.active = 1 AND d.property_id = $filter[property_id] group by d.id ORDER BY d.pos, d.id ASC
				")->fetch_all(MYSQLI_ASSOC);
			}

		}

		$cost += $this->db->query("
			SELECT
				MIN(p.cost) as min,
				MAX(p.cost) as max
			FROM products p
			LEFT JOIN product_category pc ON pc.product_id = p.id
			WHERE pc.category_id = $category_id AND p.active = 1
		")->fetch_assoc();

		if (!isset($cost['selected_min'])) {
			$cost['selected_min'] = $cost['min'];
		}

		if (!isset($cost['selected_max'])) {
			$cost['selected_max'] = $cost['max'];
		}

		return ['filters' => $filters, 'cost' => $cost];
	}

	public function selection($data, $limit)
	{
		$prev = '';
		$title = '';

		if (isset($data['page'])) {
			$page = abs(($data['page'][0]?$data['page'][0]:1) - 1);
		} else {
			$page = 0;
		}

		$offset = $page*$limit;

		if (isset($data['cost'])) {
			$cost = $data['cost'];
			unset($data['cost']);
		}

		$category_id = (int)$data['category_id'];

		unset($data['category_id']);
		unset($data['page']);

		foreach ($data as $k => $val) {
			$prefix[] = "'".$this->db->real_escape_string($k)."'";
			foreach ($val as $id) {
				$dictionary_ids[] = (int)$id;
			}
		}

		// если есть не только цена

		if (!empty($prefix) && !empty($dictionary_ids) && !empty($category_id)) {
			$where[] = "f.prefix IN (".implode(', ', $prefix).")";
			$where[] = "d.id IN (".implode(', ', $dictionary_ids).")";
			$where[] = "f.id IN (SELECT filter_id FROM filter_categories WHERE category_id = $category_id)";

			$properties = $this->db->query("
				SELECT d.id, f.property_id, f.prefix, f.name as prefix_cyr, d.value 
				FROM filters f
				LEFT JOIN dictionary d ON d.property_id = f.property_id
				WHERE ".implode(' AND ', $where)
			)->fetch_all(MYSQLI_ASSOC);

			$where = [];

			foreach ($properties as $property) {
				$_properties[$property['property_id']][] = $property['id'];
				$cyr_name = mb_convert_case($property['prefix_cyr'],MB_CASE_LOWER, 'UTF-8');
				if ($prev != $cyr_name) {
					$title .= "' " . $cyr_name . " " . $property['value'] . ",'";
				} else {
					$title = rtrim($title,";'");
					if ($title[strlen($title)-1] != ','){
						$title .= ',';
					}
					$title .= "' " . $property['value'] . ";'";
				}
				$prev = $cyr_name;
			}

			$title = rtrim($title,";'");
			$title = rtrim($title,",'");

			foreach ($_properties as $property_id => $value) {
				if (!empty($value)) {
					$where[] = "SELECT product_id FROM product_properties
					WHERE (property_id = ".(int)$property_id." AND dictionary_id IN (".implode(",", $value)."))";
				}
			}

			if (!empty($where)) {
				$where = "p.id IN (
				".implode(') AND p.id IN (', $where).")";
			}
		}

		// если есть только цена

		if (isset($cost)) {
			list($min_cost, $max_cost) = $cost;
			$min_cost = (int)$min_cost;
			$max_cost = (int)$max_cost;

			if (!empty($where)) {
				$where .= " AND ";
			} else {
				$where = '';
			}

			$where .= "cost >= $min_cost AND cost <= $max_cost";
		}

		$products = $this->db->query("
			SELECT SQL_CALC_FOUND_ROWS p.*, m.url
			FROM products p force index (products_index)
			LEFT JOIN product_properties pp ON pp.product_id = p.id
			LEFT JOIN product_category pc ON pp.product_id = p.id
			LEFT JOIN meta m ON p.meta_id = m.id
			WHERE p.active = 1 AND pc.category_id = $category_id AND $where
			GROUP BY p.id
			ORDER BY p.stock = 0, p.cost = 0.0 ASC, p.cost ASC	
			LIMIT ".$limit." OFFSET ".$offset)->fetch_all(MYSQLI_ASSOC);

		$count = $this->db->query("SELECT FOUND_ROWS()")->fetch_row()[0];

		return [
			'products' => $products,
			'pages' => ceil($count/$limit),
			'title' => preg_replace('/\'/', '', $title),
		];
	}
}