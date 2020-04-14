<?php
namespace controllers;

use core\validate;
use models\meta as MetaModel;

class meta extends authorized
{
    public function get()
    {
        $meta = new MetaModel();
        if (validate::int($this->request->id, ['required' => true])) {
            return $meta->getById($this->request->id);
        }

        if (validate::string($this->request->uri, ['required' => true])) {
            return $meta->getByUri($this->request->uri);
        }

        return [];
    }

    public function uri()
    {
        if ($this->request->uri) {
            $metaModel = new MetaModel();
            return $metaModel->uri($this->request->uri);
        }
    }

    public function add()
    {
        if (validate::string($this->request->url, ['required' => true])) {
            $meta = new MetaModel();
            if ($this->request->url){
                $this->request->url = $this->relativeURL($this->request->url);
            }
            return $meta->add($this->requestFiltered(
            	['url', 'title', 'description', 'keywords', 'canonical', 'breadcrumbs', 'type', 'noindex', 'nofollow']
            ));
        } else {
            return ['error' => 'Ошибка валидации. Поле url обязательно'];
        }
    }

    public function update()
    {
        if (validate::int($this->request->id, ['required' => true])
            && validate::string($this->request->url, ['required' => true])) {
            $meta = new MetaModel();
            if (empty($this->request->noindex)) $this->request->noindex = '0';
            if (empty($this->request->nofollow)) $this->request->nofollow = '0';
            if ($this->request->url){
                $this->request->url = $this->relativeURL($this->request->url);
            }
            return $meta->update(
            	$this->request->id,
	            $this->requestFiltered(
	            	['url', 'title', 'description', 'keywords', 'canonical', 'breadcrumbs', 'type', 'noindex', 'nofollow'])
            );
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getSitemap()
    {
        $metaModel = new MetaModel();
        return $metaModel->getSitemap();
    }
}