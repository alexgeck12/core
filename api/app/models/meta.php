<?php
namespace models;

use core\model;

class meta extends model
{
    private $table = 'meta';

    public function getById($id)
    {
        return $this->db->getRowByKeys($this->table, ['id' => $id]);
    }

    public function getByUri($uri)
    {
        $result = $this->db->getRowByKeys($this->table, ['uri' => $uri]);

        if (!$result) {
            return ['error' => 'Page not found'];
        }

        return $result;
    }

    public function uri($uri)
    {
        return $this->db->getRowByKeys($this->table, ['url' => $uri]);
    }

    public function add($data)
    {
    	return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function getSitemap()
    {
        $data = $this->db->getFiltered($this->table, [], ['id' => 'DESC'], [], 'id, url, type, title');
        foreach ($data as &$item){
            switch ($item['type']){
                case 'article':
                    $item['active'] = $this->db->getRowByKeys('articles', ['meta_id' => $item['id']])['active'];
                    break;
                case 'category':
                    $item['active'] = $this->db->getRowByKeys('categories', ['meta_id' => $item['id']])['active'];
                    break;
                default:
                    $item['active'] = $this->db->getRowByKeys('pages', ['meta_id' => $item['id']])['active'];
            }
        }

        return $data;
    }
}