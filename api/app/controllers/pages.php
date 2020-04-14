<?php
namespace controllers;
use models\pages as PageModel;
use core\validate;
use core\image;

class pages extends authorized
{
    const LIMIT = 16;

    public function get()
    {
        if (validate::int(isset($this->request->id)?$this->request->id:false, ['required' => true])) {
            $pages = new PageModel();
            return $pages->get($this->request->id);
        } elseif (validate::int(isset($this->request->meta_id)?$this->request->meta_id:false, ['required' => true])) {
            $pages = new PageModel();
            return $pages->getByMetaId($this->request->meta_id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $pages = new PageModel();
	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);
            return $pages->add($this->requestFiltered(
                ['name', 'description', 'genimg', 'alt', 'title', 'active', 'meta_id', 'img', 'sm_description', 'h1']
            ));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function update()
    {
        if (validate::int($this->request->id, ['required' => true])
            && validate::string($this->request->name, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $pages = new PageModel();
	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);

            return $pages->update($this->request->id,
                $this->requestFiltered(
	                ['name', 'description', 'genimg', 'alt', 'title', 'active', 'meta_id', 'img', 'sm_description', 'h1'])
            );
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function delete()
    {
        if (validate::int($this->request->id, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $pages = new PageModel();
            return $pages->delete($this->request->id, $this->request->meta_id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getList()
    {
        $pages = new PageModel();
        return $pages->getList($this->request->page?$this->request->page:1, self::LIMIT);
    }

    public function getListPages()
    {
        $pages = new PageModel();
        return $pages->getListPages(self::LIMIT);
    }

    public function getActive()
    {
        $pages = new PageModel();
        return $pages->getActive();
    }

    public function find()
    {
        $pages = new PageModel();
        return $pages->find($this->request->q);
    }

    public function upload()
    {
        $img_dir = '/pages';

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
    	error_reporting(E_ALL);
    	ini_set('display_errors', 1);

        $img_dir = '/redactor';

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