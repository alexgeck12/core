<?php
namespace controllers;
use models\wishlist as wishlistModel;

class wishlist extends authorized
{
    public function get()
    {
        $wishlistModel = new wishlistModel();
        return $wishlistModel->get($this->request->user);
    }

    public function add()
    {
        $wishlistModel = new wishlistModel();
        return $wishlistModel->add($this->request);
    }

    public function remove()
    {
        $wishlistModel = new wishlistModel();
        return $wishlistModel->remove($this->request);
    }
}