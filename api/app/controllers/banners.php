<?php
namespace controllers;
use models\banners as BannersModel;
use core\validate;
use core\image;

class banners extends authorized
{
    const LIMIT = 16;

    public function get()
    {
        if (validate::int(isset($this->request->id)?$this->request->id:false, ['required' => true])) {
            $banners = new BannersModel();
            return $banners->get($this->request->id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])
        ) {
            $banners = new BannersModel();
            return $banners->add($this->requestFiltered(['name', 'genimg', 'alt', 'title', 'active', 'sm_description', 'h1']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function update()
    {
        if (validate::int($this->request->id, ['required' => true])
            && validate::string($this->request->name, ['required' => true])
        ) {
            $banners = new BannersModel();
            return $banners->update($this->request->id, $this->requestFiltered(['name', 'genimg', 'alt', 'title', 'active', 'sm_description', 'h1']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function delete()
    {
        if (validate::int($this->request->id, ['required' => true])
        ) {
            $banners = new BannersModel();
            return $banners->delete($this->request->id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getAll()
    {
        $banners = new BannersModel();
        $tree = array();

        $result = $banners->getAll();

        foreach ($result as $item){
            $tree[$item['pid']][$item['id']] = $item;
        }

        $banners = array();
        $this->getTree($banners, 0, $tree);
        return (array)$banners;
    }

    public function getActive()
    {
        $banners = new BannersModel();
        $tree = array();

        $result = $banners->getActive();

        foreach ($result as $item){
            $tree[$item['pid']][$item['id']] = $item;
        }

        $banners = array();
        $this->getTree($banners, 0, $tree);
        return (array)$banners;
    }

    private function getTree(&$banners, $pid, $data)
    {
        foreach ($data[$pid] as $id => &$item) {
            if (isset($data[$id])) {
                $item['children'] = array();
                $this->getTree($item['children'], $id, $data);
            }
            $banners[] = $item;
        }
    }

    public function savePosition()
    {
        $banners = $this->request->banners;
        $this->saveTree($banners, 0, 0);
        return true;
    }

    public function saveTree(&$branch, $pid, $pos) {
        foreach ($branch as $tree) {
            $pos++;
            if (isset($tree['children'])) {
                $this->saveTree($tree['children'], $tree['id'], $pos);
            }

            $banners = new BannersModel();
            $bannersItem = $banners->get($tree['id'])[0];
            unset($bannersItem['genimg']);
            unset($bannersItem['img']);
            unset($bannersItem['url']);
            $bannersItem['pos'] = $pos;
            $bannersItem['pid'] = $pid;
            $banners->update($tree['id'], $bannersItem);
        }
    }

    public function upload()
    {
    	$img_dir = '/banners';

        if (!is_dir(ROOT.'/public/media'.$img_dir)) {
            mkdir(ROOT.'/public/media'.$img_dir, 0775, true);
        }

        if ($this->files->genimg['name']) {

            $extension = strtolower(strrchr($this->files->genimg['name'], '.'));
            $this->files->genimg['name'] = md5_file($this->files->genimg['tmp_name']).$extension;

            if (move_uploaded_file($this->files->genimg['tmp_name'], ROOT.'/public/media'.$img_dir.'/'.$this->files->genimg['name'])) {

                $path = ROOT.'/public/media'.$img_dir.'/'.$this->files->genimg['name'];
                $image = new image($path);
                $image->resizeIfBigger(1920, 1080);
                $image->save($path, "85");
                unset($image);

                return '/media'.$img_dir.'/'.$this->files->genimg['name'];
            }

        }
    }

    public function redactorUpload()
    {
        $img_dir = '/redactor/banners';

        if (!is_dir(ROOT.'/public/media'.$img_dir)) {
            mkdir(ROOT.'/public/media'.$img_dir, 0775, true);
        }

        if ($this->files->file['name']) {

            $extension = strtolower(strrchr($this->files->file['name'], '.'));
            $this->files->file['name'] = md5_file($this->files->file['tmp_name']).$extension;

            if (move_uploaded_file($this->files->file['tmp_name'], ROOT.'/public/media'.$img_dir.'/'.$this->files->file['name'])) {

                $path = ROOT.'/public/media'.$img_dir.'/'.$this->files->file['name'];
                $image = new image($path);
                $image->resizeIfBigger(1920, 1080);
                $image->save($path, "85");
                unset($image);

                return [
                    'filelink' => '/media'.$img_dir.'/'.$this->files->file['name']
                ];
            }

        }
    }
}