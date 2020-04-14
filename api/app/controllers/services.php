<?php
namespace controllers;
use models\services as ServiceModel;
use core\validate;
use core\image;

class services extends authorized
{
    const LIMIT = 16;

    public function get()
    {
        if (validate::int($this->request->id, ['required' => true])) {
            $services = new ServiceModel();
            return $services->get($this->request->id);
        } elseif (validate::int($this->request->meta_id, ['required' => true])) {
            $services = new ServiceModel();
            return $services->getByMetaId($this->request->meta_id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $services = new ServiceModel();

            return $services->add($this->requestFiltered([
                'name' => $this->request->name,
                'description' => $this->request->description,
                'genimg' => $this->request->genimg,
                'alt' => $this->request->alt,
                'title' => $this->request->title,
                'active' => $this->request->active,
                'meta_id' => $this->request->meta_id,
                'img' => json_encode($this->request->img, JSON_UNESCAPED_UNICODE),
                'sm_description' => $this->request->sm_description,
                'h1' => $this->request->h1
            ]));
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
            $id = $this->request->id;
            $services = new ServiceModel();
            return $services->update(
                $this->request->id,
                $this->requestFiltered(
                    [
                        'name' => $this->request->name,
                        'description' => $this->request->description,
                        'genimg' => $this->request->genimg,
                        'alt' => $this->request->alt,
                        'title' => $this->request->title,
                        'active' => $this->request->active,
                        'meta_id' => $this->request->meta_id,
                        'img' => json_encode($this->request->img, JSON_UNESCAPED_UNICODE),
                        'sm_description' => $this->request->sm_description,
                        'h1' => $this->request->h1
                    ])
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
            $services = new ServiceModel();
            return $services->delete($this->request->id, $this->request->meta_id);
        }else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getList()
    {
        $services = new ServiceModel();
        return $services->getList($this->request->page?$this->request->page:1, self::LIMIT);
    }

    public function getListPages()
    {
        $services = new ServiceModel();
        return $services->getListPages(self::LIMIT);
    }

    public function getActive()
    {
        $services = new ServiceModel();
        return $services->getActive();
    }

    public function find()
    {
        $services = new ServiceModel();
        return $services->find($this->request->q);
    }

    public function upload()
    {
        $img_dir = '/services';

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
        $img_dir = '/redactor/services';

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