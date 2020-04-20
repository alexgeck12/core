<?php
include 'common.php';

$common = new common();

class reader extends core\xmlReader {

    public $products;
    public $categories;
    public $params;
    public $values;

    protected $pointer;

    public function __construct($xml)
    {
        parent::__construct($xml);
    }

    protected function parseCategory ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Category') {
            if ($this->pointer !== 'product') {
                $this->pointer = 'category';
                $this->result['category'] = [
                    'id'   => $this->reader->getAttribute('id'),
                    'pid'  => $this->reader->getAttribute('parentId')?$this->reader->getAttribute('parentId'):'0',
                    'name' => '',
                ];
            } else {
                $this->result[$this->pointer]['category_id'] = $this->reader->getAttribute('id');
            }
        }
    }

    protected function endCategory ()
    {
        if ($this->pointer !== 'product') {
            $this->categories[$this->result['category']['id']] = $this->result['category'];
            $this->pointer = false;
        }
    }

    protected function parseName ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'name' || $this->reader->localName == 'Name') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['name'] = $this->reader->value;
            }
        }
    }

    protected function parseParam ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Param') {
            $this->pointer = 'param';
            $this->result['param'] = [
                'id' => $this->reader->getAttribute('id'),
                'name' => '',
            ];
        }
    }

    protected function endParam ()
    {
        $this->params[$this->result['param']['id']] = $this->result['param'];
        $this->pointer = false;
    }

    protected function parseValue ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'value') {

            $this->result['value'] = [
                'id' => $this->reader->getAttribute('id'),
                'param_id' => $this->result['param']['id'],
                'name' => '',
                'unit' => $this->reader->getAttribute('unit')?$this->reader->getAttribute('unit'):''
            ];

            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result['value']['name'] = $this->reader->value;
                $this->values[$this->result['value']['id']] = $this->result['value'];
            }

        }
    }

    protected function parseProduct()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Product') {
            $this->pointer = 'product';
            $this->result['product'] = [
                'id'   => '',
                'name'  => '',
                'vendor' => '',
                'model' => '',
                'vendorCode' => '',
                'typePrefix' => '',
                'groupId' => '',
                'dealerID' => '',
                'inStock' => '',
                'available' => '',
                'downloadable' => '',
                'price' => '',
                'itemType' => '',
                'category_id' => '',
                'picture' => '',
                'annotation' => '',
                'termsConditions' => '',
                'activationRules' => '',
                'termsOfUse' => '',
            ];
        }
    }

    protected function endProduct ()
    {
        $this->products[$this->result['product']['id']] = $this->result['product'];
        $this->pointer = false;
    }

    protected function parseId ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Id') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['id'] = $this->reader->value;
            }
        }
    }

    protected function parseVendor ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Vendor') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['vendor'] = $this->reader->value;
            }
        }
    }

    protected function parseModel ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Model') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['model'] = $this->reader->value;
            }
        }
    }

    protected function parseVendorCode ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'VendorCode') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['vendorCode'] = $this->reader->value;
            }
        }
    }

    protected function parseTypePrefix ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'TypePrefix') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['typePrefix'] = $this->reader->value;
            }
        }
    }

    protected function parseGroupId ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'GroupId') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['groupId'] = $this->reader->value;
            }
        }
    }

    protected function parseDealerID ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'DealerID') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['dealerID'] = $this->reader->value;
            }
        }
    }

    protected function parseInStock ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'InStock') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['inStock'] = $this->reader->value;
            }
        }
    }

    protected function parseAvailable ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Available') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['available'] = $this->reader->value;
            }
        }
    }

    protected function parseDownloadable ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Downloadable') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['downloadable'] = $this->reader->value;
            }
        }
    }

    protected function parsePrice ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Price') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['price'] = $this->reader->value;
            }
        }
    }

    protected function parseItemType ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'ItemType') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['itemType'] = $this->reader->value;
            }
        }
    }

    protected function parsePicture ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Picture') {
            $this->reader->read();
            if ($this->reader->nodeType == XMLReader::TEXT) {
                $this->result[$this->pointer]['picture'] = $this->reader->value;
            }
        }
    }

    protected function parseAnnotation ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'Annotation') {
            $this->reader->read();
            $this->result[$this->pointer]['annotation']
                = htmlspecialchars(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $this->reader->value));
        }
    }

    protected function parseTermsConditions ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'TermsConditions') {
            $this->reader->read();
            $this->result[$this->pointer]['termsConditions']
                = htmlspecialchars(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $this->reader->value));
        }
    }

    protected function parseActivationRules ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'ActivationRules') {
            $this->reader->read();
            $this->result[$this->pointer]['activationRules']
                = htmlspecialchars(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $this->reader->value));

        }
    }

    protected function parseTermsOfUse ()
    {
        if ($this->reader->nodeType == XMLReader::ELEMENT && $this->reader->localName == 'TermsOfUse') {
            $this->reader->read();
            $this->result[$this->pointer]['termsOfUse']
                = htmlspecialchars(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $this->reader->value));
        }
    }
}


$xml = $common->request('GetCategories');
$reader = new reader($xml);
$reader->parse();

$common->db->multi_insert('categories', $reader->categories, true);
$common->db->multi_insert('params', $reader->params, true);
$common->db->multi_insert('values', $reader->values, true);

$xml = $common->request('GetProduct');
$reader = new reader($xml);
$reader->parse();

$products = array_chunk($reader->products, 200);
foreach ($products as $chunk) {
    $common->db->multi_insert('products', $chunk, true);
}