<?php
namespace controllers;
use core\validate;
use models\menu as MenuModel;

class menu extends authorized
{
    public function get()
    {
        if (validate::int(isset($this->request->id)?$this->request->id:'', ['required' => true])) {
            $menu = new MenuModel();
            return $menu->get($this->request->id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])
            && validate::string($this->request->url, ['required' => true])) {
            $menu = new MenuModel();

            if ($this->request->url){
                $this->request->url = $this->relativeURL($this->request->url);
            }

            return $menu->add($this->requestFiltered(['name', 'h1', 'url', 'active', 'priority', 'type']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function update()
    {
        if (validate::int($this->request->id, ['required' => true])) {
            $menu = new MenuModel();

            if ($this->request->url){
                $this->request->url = $this->relativeURL($this->request->url);
            }

            return $menu->update($this->request->id, $this->requestFiltered(['name', 'h1', 'url', 'active', 'priority', 'type']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function delete()
    {
        if (validate::int(isset($this->request->id)?$this->request->id:'', ['required' => true])) {
            $menu = new MenuModel();
            return $menu->delete($this->request->id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getAll()
    {
        $menu = new MenuModel();
        $tree = array();

        $result = $menu->getAll($this->request->type);

        foreach ($result as $item){
            $tree[$item['pid']][$item['id']] = $item;
        }

        $menu = array();
        $this->getTree($menu, 0, $tree);
        return (array)$menu;
    }

    public function getActive()
    {
        $menu = new MenuModel();
        $tree = array();

        $result = $menu->getActive($this->request->type);

        foreach ($result as $item){
            $tree[$item['pid']][$item['id']] = $item;
        }

        $menu = array();
        $this->getTree($menu, 0, $tree);
        return (array)$menu;
    }

    private function getTree(&$menu, $pid, $data)
    {
        foreach ($data[$pid] as $id => &$item) {
            if (isset($data[$id])) {
                $item['children'] = array();
                $this->getTree($item['children'], $id, $data);
            }
            $menu[] = $item;
        }
    }

    public function savePosition()
    {
        $menu = $this->request->menu;
        $this->saveTree($menu, 0, 0);
        return true;
    }

    public function saveTree(&$branch, $pid, $pos) {

        foreach ($branch as $tree) {
            $pos++;
            if (isset($tree['children'])) {
                $this->saveTree($tree['children'], $tree['id'], $pos);
            }

            $menu = new MenuModel();
            $menuItem = $menu->get($tree['id']);
            $menuItem['pos'] = $pos;
            $menuItem['pid'] = $pid;
            $menu->update($tree['id'], $menuItem);
        }
    }
}