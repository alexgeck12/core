<?php
namespace controllers;
use core\validate;
use models\redirects as RedirectsModel;

class redirects extends authorized
{
    const LIMIT = 16;

    public function get()
    {
        if (validate::int(isset($this->request->id)?$this->request->id:false, ['required' => true])) {
            $redirects = new RedirectsModel();
            return $redirects->get($this->request->id);
        } elseif (validate::string(isset($this->request->uri)?$this->request->uri:false, ['required' => true])) {
            $redirects = new RedirectsModel();
            return $redirects->getByURI($this->request->uri);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->url, ['required' => true])) {
            $redirects = new RedirectsModel();
            return $redirects->add($this->requestFiltered(['url', 'redirect_url', 'active']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function update()
    {
        if (validate::int($this->request->id, ['required' => true])
            && validate::string($this->request->url, ['required' => true])) {
            $redirects = new RedirectsModel();
            return $redirects->update($this->request->id, $this->requestFiltered(['url', 'redirect_url', 'active']));
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function delete()
    {
        if (validate::int($this->request->id, ['required' => true])) {
            $redirects = new RedirectsModel();
            return $redirects->delete($this->request->id);
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getList()
    {
        $redirects = new RedirectsModel();
        return $redirects->getList($this->request->page?$this->request->page:1, self::LIMIT);
    }

    public function getListPages()
    {
        $redirects = new RedirectsModel();
        return $redirects->getListPages(self::LIMIT);
    }

    public function getActive()
    {
        $redirects = new RedirectsModel();
        return $redirects->getActive();
    }

    public function find()
    {
        $redirects = new RedirectsModel();
        return $redirects->find($this->request->q);
    }
}