<?php
namespace controllers;
use models\categories as CategoryModel;
use core\validate;
use core\image;

class categories extends authorized
{
    const LIMIT = 16;

    public function get()
    {
        if (validate::int(isset($this->request->id)?$this->request->id:false, ['required' => true])) {
            $categories = new CategoryModel();
            return $categories->get($this->request->id);
        } elseif (validate::int(isset($this->request->meta_id)?$this->request->meta_id:false, ['required' => true])) {
            $categories = new CategoryModel();
            return $categories->getByMetaId($this->request->meta_id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $categories = new CategoryModel();
	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);

            return $categories->add($this->requestFiltered(['name', 'description', 'genimg', 'alt', 'title', 'active', 'meta_id', 'img', 'sm_description', 'h1']));
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
            $categories = new CategoryModel();
	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);

            return $categories->update(
            	$this->request->id,
	            $this->requestFiltered(['name', 'description', 'genimg', 'alt', 'title', 'active', 'meta_id', 'img', 'sm_description', 'h1'])
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
            $categories = new CategoryModel();
            return $categories->delete($this->request->id, $this->request->meta_id);
        }else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getList()
    {
        $categories = new CategoryModel();
        return $categories->getList($this->request->page?$this->request->page:1, self::LIMIT);
    }

    public function getListPages()
    {
        $categories = new CategoryModel();
        return $categories->getListPages(self::LIMIT);
    }

	public function getAll()
	{
		$categories = new CategoryModel();
		$tree = array();

		$result = $categories->getAll();

		foreach ($result as $item){
			$tree[$item['pid']][$item['id']] = $item;
		}

		$categories = array();
		$this->getTree($categories, 0, $tree);
		return (array)$categories;
	}

	public function getActive()
	{
		$categories = new CategoryModel();
		$tree = array();

		$result = $categories->getActive();

		foreach ($result as $item){
			$tree[$item['pid']][$item['id']] = $item;
		}

		$categories = array();
		$this->getTree($categories, 0, $tree);
		return (array)$categories;
	}

	private function getTree(&$categories, $pid, $data)
	{
		foreach ($data[$pid] as $id => &$item) {
			if (isset($data[$id])) {
				$item['children'] = array();
				$this->getTree($item['children'], $id, $data);
			}
			$categories[] = $item;
		}
	}

	public function savePosition()
	{
		$categories = $this->request->categories;
		$this->saveTree($categories, 0, 0);
		return true;
	}

	public function saveTree(&$branch, $pid, $pos) {

		foreach ($branch as $tree) {
			$pos++;
			if (isset($tree['children'])) {
				$this->saveTree($tree['children'], $tree['id'], $pos);
			}

			$categories = new CategoryModel();
			$categoryItem = $categories->get($tree['id']);
			$categoryItem['pos'] = $pos;
			$categoryItem['pid'] = $pid;
			$categories->update($tree['id'], $this->requestFiltered(['pos', 'pid'], $categoryItem));
		}
	}

    public function find()
    {
        $categories = new CategoryModel();
        return $categories->find($this->request->q);
    }

    public function upload()
    {
        $img_dir = '/categories';

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

    public function getUrl()
    {
	    $categories = new CategoryModel();
	    return $categories->getUrl($this->request->id);
    }

	public function getChildren()
	{
		$categories = new CategoryModel();
		return $categories->getChildren($this->request->id);
    }

	public function getParents()
	{
		$categories = new CategoryModel();
		return $categories->getParents($this->request->id);
	}

}