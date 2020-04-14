<?php
namespace controllers;
use models\articles as ArticlesModel;
use core\validate;
use core\image;

class articles extends authorized
{
    const LIMIT = 16;

    public function get()
    {
        if (validate::int($this->request->id, ['required' => true])) {
            $articles = new ArticlesModel();
            return $articles->get($this->request->id);
        } elseif (validate::int($this->request->meta_id, ['required' => true])) {
            $articles = new ArticlesModel();
            return $articles->getByMetaId($this->request->meta_id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $articles = new ArticlesModel();
	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);

	        return $articles->add($this->requestFiltered(
            	['name', 'description', 'genimg', 'alt', 'title', 'active', 'meta_id', 'img', 'sm_description', 'h1'])
	        );
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
            $articles = new ArticlesModel();
	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);

            return $articles->update($this->request->id, $this->requestFiltered(
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
            $articles = new ArticlesModel();
            return $articles->delete($this->request->id, $this->request->meta_id);
        }else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getList()
    {
        $articles = new ArticlesModel();
        return $articles->getList($this->request->page?$this->request->page:1, self::LIMIT);
    }

    public function getListPages()
    {
        $articles = new ArticlesModel();
        return $articles->getListPages(self::LIMIT);
    }

    public function getListActive()
    {
        $articles = new ArticlesModel();
        return $articles->getListActive($this->request->page?$this->request->page:1, $this->request->limit?$this->request->limit:self::LIMIT);
    }

	public function getActive()
	{
		$articles = new ArticlesModel();
		return $articles->getActive();
	}

    public function find()
    {
        $articles = new ArticlesModel();
        return $articles->find($this->request->q);
    }

    public function upload()
    {
        $img_dir = '/articles';

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
        $img_dir = '/redactor/articles';

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