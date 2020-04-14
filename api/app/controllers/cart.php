<?php
namespace controllers;
use models\cart as cartModel;

class cart extends authorized
{
    public function get()
    {
        $cartModel = new cartModel();
        return $cartModel->get($this->request->user);
    }

    public function add()
    {
        $cartModel = new cartModel();
        return $cartModel->add(
        	$this->request->user,
	        $this->request->id,
	        $this->request->quantity,
	        $this->request->personal,
	        $this->request->code
        );
    }

    public function remove()
    {
        $cartModel = new cartModel();
        return $cartModel->remove(
	        $this->request->user,
	        $this->request->id
        );
    }

	public function clear()
	{
		$cartModel = new cartModel();
		return $cartModel->clear($this->request->user);
    }
}