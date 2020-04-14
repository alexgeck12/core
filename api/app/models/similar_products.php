<?php
namespace models;
use core\model;

class similar_products extends model
{
	public $table = 'similar_products';

	public function getByProduct($id)
	{
		return $this->db->query("
			SELECT 
				p.*,
				meta.url
			FROM $this->table sp
			LEFT JOIN products p
				ON sp.similar_product_id = p.id
			LEFT JOIN meta
				ON p.meta_id = meta.id
			WHERE sp.product_id = $id				
				AND p.active = 1
		")->fetch_all(MYSQLI_ASSOC);
	}

    public function getByProductActiveAndAvailable($id)
    {
        return $this->db->query("
			SELECT 
				p.*,
				meta.url
			FROM $this->table sp
			LEFT JOIN products p
				ON sp.similar_product_id = p.id
			LEFT JOIN meta
				ON p.meta_id = meta.id
			WHERE sp.product_id = $id				
				AND p.active = 1 AND p.available = 1
		")->fetch_all(MYSQLI_ASSOC);
    }

	public function getBySimilarProducts($id)
	{
		return $this->db->query("
			SELECT c.id, c.name
			FROM $this->table oc
			LEFT JOIN products c
				ON oc.product_id = c.id
			WHERE oc.similar_product_id = $id
		")->fetch_all(MYSQLI_ASSOC);
	}

	public function add($product_id, $similar_product_id)
	{
		return $this->db->insert($this->table, ['product_id' => $product_id, 'similar_product_id' => $similar_product_id]);
	}

	public function remove($product_id, $similar_product_id)
	{
		return $this->db->delete($this->table, ['product_id' => $product_id, 'similar_product_id' => $similar_product_id]);
	}

	public function removeSimilar($product_id)
	{
		return $this->db->delete($this->table, ['product_id' => $product_id]);
	}

	public function getCount($id)
	{
		return $this->db->query("SELECT COUNT(*) FROM similar_products WHERE product_id = $id")->fetch_row()[0];
	}

    public function getCountActiveAndAvailable($id)
    {
        return $this->db->query("              
                SELECT COUNT(id) as count 
                FROM similar_products sp 
                INNER JOIN products p 
                ON p.id = sp.similar_product_id
                WHERE p.active AND p.available AND product_id = $id
                UNION
                SELECT COUNT(product_id) FROM similar_products 
                WHERE product_id = $id
            ")->fetch_all();
    }
}