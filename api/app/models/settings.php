<?php
namespace models;
use core\model;

class settings extends model
{
    private $table = 'settings';

    public function update($data)
    {
        foreach($data as $key => $value) {
            $this->db->insert('settings', array('key' => $key, 'value' => $value), true);
        }
    }

    public function getAll()
    {
        $settings = array();
        $allSettings = $this->db->getFiltered($this->table);

        foreach ($allSettings as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }
}