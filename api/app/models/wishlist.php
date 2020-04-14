<?php
namespace models;
use core\model;

class wishlist extends model
{
    public function get($user)
    {
        return json_decode($this->redis->hGet("wishlist", $user),1);
    }

    public function add($data)
    {
        $wishlist = $this->get($data->user);
        $user = $data->user;
        unset($data->user);

        if (!$wishlist) {
            $wishlist = [];
        }

        $wishlist[$data->product_id] = $data;
        return $this->redis->hSet("wishlist", $user, json_encode($wishlist, JSON_UNESCAPED_UNICODE));
    }

    public function remove($data)
    {
        $wishlist = $this->get($data->user);
        $user = $data->user;
        unset($data->user);
        unset($wishlist[$data->product_id]);

        if (!$wishlist) {
            $wishlist = [];
        }

        return $this->redis->hSet("wishlist", $user, json_encode($wishlist, JSON_UNESCAPED_UNICODE));
    }
}