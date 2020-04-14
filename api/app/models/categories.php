<?php
namespace models;
use core\model;

class categories extends model
{
    private $table = 'categories';

    public function get($id)
    {
	    $data = $this->db->query("SELECT t.*, m.url
           FROM ".$this->table." t
           LEFT JOIN meta m ON t.meta_id = m.id            
           WHERE t.id = $id
        ")->fetch_assoc();
        $data['img'] = json_decode($data['img'], true);

        return $data;
    }

    public function getByMetaId($id)
    {
	    $data = $this->db->query("SELECT t.*, m.url
           FROM ".$this->table." t
           LEFT JOIN meta m ON t.meta_id = m.id            
           WHERE t.meta_id = $id
        ")->fetch_assoc();
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

	public function getAll()
	{
		return $this->db->getFiltered($this->table, [], ['pos' => 'ASC']);
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


	public function getUrl($id)
	{
		return $this->db->query("
            SELECT m.url
            FROM ".$this->table." t
            LEFT JOIN meta m ON t.meta_id = m.id            
            WHERE t.id = $id            
        ")->fetch_assoc();
    }

	public function getChildren($id)
	{
		$categories = $this->db->query("SELECT * FROM categories WHERE active = 1")->fetch_all(MYSQLI_ASSOC);
		$children_ids = [];
		return $this->getChildrenByCategory($id, $categories, $children_ids);
	}

	private function getChildrenByCategory($category_id, $categories, &$children_ids)
	{
		foreach ($categories as $category) {
			if ($category_id == $category['pid']) {
				$children_ids[$category['id']] = $category['id'];
				$this->getChildrenByCategory($category['id'], $categories, $children_ids);
			}
		}

		return $children_ids;
	}

	public function getParents($id)
	{
		$category = [];
		$parents = [];

		$categories = $this->db->query("SELECT c.*, m.url FROM categories c LEFT JOIN meta m ON c.meta_id = m.id  WHERE c.active = 1")->fetch_all(MYSQLI_ASSOC);

		foreach ($categories as $item) {
			if ($item['id'] == $id) {
				$category = $item;
			}
		}

		$parents = $this->getParentsByCategory($category, $categories, $parents);

		usort($parents, function($a, $b){
			if ($a['pid'] == $b['pid']) {
				return 0;
			}
			return ($a['pid'] < $b['pid']) ? -1 : 1;
		});

		return $parents;
	}

	private function getParentsByCategory($category, $categories, &$parents)
	{
		foreach ($categories as $cat) {
			if ($category['pid'] == $cat['id']) {
				$parents[$cat['id']] = $cat;
				$this->getParentsByCategory($cat, $categories, $parents);
			}
		}

		return $parents;
	}
}