<?php
namespace controllers;
use models\products as ProductModel;
use models\properties as PropertiesModel;
use models\categories as CategoryModel;
use models\similar_products as SimilarProductsModel;
use core\validate;
use core\image;

class products extends authorized
{
    const LIMIT = 6;

    public function get()
    {
	    $products = new ProductModel();
	    $properties = new PropertiesModel();

        if (validate::int_list(isset($this->request->id)?$this->request->id:false, ['required' => true])) {
            $products = $products->get($this->request->id);
            foreach ($products as &$product) {
            	$product['properties'] = $properties->getByProductId($product['id']);
            }
	        return $products;
        } elseif (validate::int_list(isset($this->request->meta_id)?$this->request->meta_id:false, ['required' => true])) {
            $products = $products->getByMetaId($this->request->meta_id);
	        foreach ($products as &$product) {
		        $product['properties'] = $properties->getByProductId($product['id']);
	        }

	        return $products;
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function add()
    {
        if (validate::string($this->request->name, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $products = new ProductModel();
	        $properties = new PropertiesModel();
	        $similarProducts = new SimilarProductsModel();

	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);

	        $id =
	            $products->add($this->requestFiltered([
                    'name',
                    'description',
                    'genimg',
                    'alt',
                    'title',
                    'active',
                    'meta_id',
                    'img',
                    'sm_description',
                    'h1',
		            'cost',
	                'old_cost',
		            'on_main',
		            'stock',
	                'manufacturer_id'
                ]));

	        if ($this->request->properties) {
	        	$_properties = [];

	        	foreach ($this->request->properties as &$property) {
			        $property['product_id'] = $id;
			        if (empty($property['dictionary_id'])) {
				        $property['dictionary_id'] = '0';
			        }
			        if (empty($property['value'])) {
				        $property['value'] = '';
			        }
			        $_properties[] = $property;
		        }

		        $properties->addProductProperties($_properties, $id);
	        }

	        if ($this->request->similar_products) {
		        foreach ($this->request->similar_products as $similar_product) {
			        $similarProducts->add($id, $similar_product);
		        }
	        }

	        return $id;
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
            $products = new ProductModel();
	        $properties = new PropertiesModel();
	        $similarProducts = new SimilarProductsModel();

	        if ($this->request->type !== 'list') {
		        $properties->removeProductProperties($this->request->id);
		        $similarProducts->removeSimilar($this->request->id);
		        //$product_tags->removeTags($this->request->id);
	        }

	        $this->request->img = json_encode($this->request->img, JSON_UNESCAPED_UNICODE);

	        $product =
		        $products->update(
	                $this->request->id,
	                $this->requestFiltered([
		                'name',
		                'description',
		                'genimg',
		                'alt',
		                'title',
		                'active',
		                'meta_id',
		                'img',
		                'sm_description',
		                'h1',
		                'cost',
		                'old_cost',
		                'on_main',
		                'stock',
		                'manufacturer_id'
	                ])
            );

	        if ($this->request->properties) {
		        $_properties = [];

		        foreach ($this->request->properties as &$property) {
			        $property['product_id'] = $this->request->id;
			        if (empty($property['dictionary_id'])) {
				        $property['dictionary_id'] = '0';
			        }
			        if (empty($property['value'])) {
				        $property['value'] = '';
			        }
			        $_properties[] = $property;
		        }
		        $properties->addProductProperties($_properties, $this->request->id);
	        }

	        if (!empty($this->request->similar_products)) {
		        foreach ($this->request->similar_products as $similar_product) {
			        $similarProducts->add($this->request->id, $similar_product);
		        }
	        }

	        return $product;
        } else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function delete()
    {
        if (validate::int($this->request->id, ['required' => true])
            && validate::int($this->request->meta_id, ['required' => true])
        ) {
            $products = new ProductModel();
            return $products->delete($this->request->id, $this->request->meta_id);
        }else {
            return ['error' => 'Ошибка валидации'];
        }
    }

    public function getList()
    {
        $products = new ProductModel();
        return $products->getList($this->request->page?$this->request->page:1, self::LIMIT);
    }

    public function getListPages()
    {
        $products = new ProductModel();
        return $products->getListPages(self::LIMIT);
    }

    public function getActive()
    {
        $products = new ProductModel();
        return $products->getActive();
    }

	public function getAllForSimilar()
	{
		$products = new ProductModel();
		return $products->getAllForSimilar($this->request->id);
	}

	public function find()
    {
        $products = new ProductModel();
        return $products->find($this->request->q);
    }

	public function getGridByCategoryId()
	{
		$products = new ProductModel();
		$categories = new CategoryModel();
		$category_ids = $categories->getChildren($this->request->category_id);

		if ($category_ids) {
			$category_ids = implode(',', $category_ids);
			$category_ids .= ','.$this->request->category_id;
		} else {
			$category_ids = $this->request->category_id;
		}

		return $products->getGridByCategoryId(
			$category_ids,
			isset($this->request->page)?$this->request->page:1,
			self::LIMIT
		);
	}

	public function getGridByManufacturerId()
	{
		$products = new ProductModel();
		return $products->getGridByManufacturerId(
			$this->request->manufacturer_id,
			isset($this->request->page)?$this->request->page:1,
			self::LIMIT
		);
	}

	public function getBestseller()
	{
		$products = new ProductModel();
		return $products->getBestseller(self::LIMIT);
	}

    public function upload()
    {
        $img_dir = '/products';

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