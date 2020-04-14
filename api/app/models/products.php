<?php
namespace models;
use core\model;

class products extends model
{
    private $table = 'products';

	public function get($ids)
	{
		$data = $this->db->getFiltered($this->table, ['id' => explode(',', $ids)], [], []);

		foreach ($data as &$item) {
			$item['img'] = json_decode($item['img'], true);
		}

		return $data;
	}

    public function getByMetaId($ids)
    {
	    $data = $this->db->getFiltered($this->table, ['meta_id' => explode(',', $ids)], [], []);

	    foreach ($data as &$item) {
		    $item['img'] = json_decode($item['img'], true);
	    }

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

	public function getAllForSimilar($id)
	{
		if ($id == 'new') {
			return $this->db->getFiltered($this->table, [], ['name' => 'ASC']);
		} else {
			return $this->db->getFiltered($this->table, ['id' => '!='.$id], ['name' => 'ASC']);
		}
	}

    public function find($q)
    {
	    return $this->db->query("
            SELECT p.*
            FROM ".$this->table." p
            WHERE p.name LIKE '%".$q."%' OR
                  p.description LIKE '%".$q."%' OR
                  p.sm_description LIKE '%".$q."%' OR
                  p.h1 LIKE '%".$q."%' 
            ORDER BY p.id ASC
        ")->fetch_all(MYSQLI_ASSOC);
    }

	public function getGridByCategoryId($ids, $page, $limit)
	{
		$page = abs($page - 1);
		$offset = $page*$limit;

		$products = $this->db->query("
            SELECT 
                products.*,
				meta.url
			FROM products		            
			LEFT JOIN meta
			ON products.meta_id = meta.id			
			LEFT JOIN product_category pc
			ON products.id = pc.product_id
			WHERE pc.category_id IN (".$ids .") AND products.active = 1
			GROUP BY products.id
			ORDER BY products.cost ASC
			LIMIT ".$limit." OFFSET ".$offset)->fetch_all(MYSQLI_ASSOC);

		$count = $this->db->query("
			SELECT p.id  
			FROM product_category pc		
			LEFT JOIN products p
			ON p.id = pc.product_id		  
			WHERE pc.category_id IN (".$ids .") AND p.active = 1		  
			GROUP BY p.id")->fetch_all(MYSQLI_ASSOC);

		return ['products' => $products, 'pages' => ceil(count($count)/$limit), 'count' => count($count)];
	}

	public function getGridByManufacturerId($manufacturer_id, $page, $limit)
	{
		$page = abs($page - 1);
		$offset = $page*$limit;

		$products = $this->db->query("
            SELECT 
                products.*,
				meta.url
			FROM products	            
			LEFT JOIN meta
			ON products.meta_id = meta.id			
			WHERE products.manufacturer_id = ".$manufacturer_id ." AND products.active = 1 AND products.stock = 1
			ORDER BY products.cost ASC
			LIMIT ".$limit." OFFSET ".$offset)->fetch_all(MYSQLI_ASSOC);

		$count = $this->db->getFiltered($this->table, ['manufacturer_id' => $manufacturer_id, 'active' => '1'], [], [], 'count(*) as count')[0]['count'];

		return ['products' => $products, 'pages' => ceil($count/$limit), 'count' => $count];
	}

	public function getBestseller($limit)
	{
		return $this->db->query("
			SELECT 
                products.*,
				meta.url
			FROM products
			LEFT JOIN meta
			ON products.meta_id = meta.id
			LEFT JOIN order_products pc
			ON products.id = pc.product_id
			GROUP BY products.id
			ORDER BY pc.quantity DESC
			LIMIT ".$limit
		)->fetch_all(MYSQLI_ASSOC);
	}
}