<?php
namespace core;

class xmlReader {

    protected $reader;
    protected $result = [];
    // события
    protected $_eventStack = [];

    /*
        Конструктор класса.
    */

    public function __construct($xml, $type = 'string') {

        $this->reader = new \XMLReader();

        switch ($type) {
            case "string":
                $this->reader->xml($xml);
                break;
            case "file":
                if (is_file($xml)) {
                    $this->reader->open($xml);
                } else {
                    throw new \Exception('XML file {'.$xml.'} not exists!');
                }
                break;
        }
    }

    /*
        Потоково парсит xml и вызывает методы для определенных элементов
        напр.

        при обнаружении элемента <Rubric> попытается вызвать метод parseRubric
        все методы парсинга должны быть public или protected.
    */
    public function parse() {

        $this->reader->read();

        while ($this->reader->read()) {

            if ($this->reader->nodeType == \XMLReader::ELEMENT) {

                $fnName = 'parse' . $this->reader->localName;

                if (method_exists($this, $fnName)) {

                    $lcn = $this->reader->name;

                    // стреляем по началу парсинга блока
                    $this->fireEvent('beforeParseContainer', ['name' => $lcn]);
                    // пробежка по детям
                    if ($this->reader->name == $lcn && $this->reader->nodeType != \XMLReader::END_ELEMENT) {
                        // стреляем событие до парсинга элемента
                        $this->fireEvent('beforeParseElement', ['name' => $lcn]);
                        // вызываем функцию парсинга
                        $this->{$fnName}();
                        // стреляем событием по названию элемента
                        $this->fireEvent($fnName);
                        // стреляем событием по окончанию парсинга элемента
                        $this->fireEvent('afterParseElement', ['name' => $lcn]);
                    }
                }

            } elseif ($this->reader->nodeType == \XMLReader::END_ELEMENT) {

                $fnName = 'end' . $this->reader->localName;

                if (method_exists($this, $fnName)) {
                    $lcn = $this->reader->name;

                    $this->{$fnName}();

                    // стреляем по окончанию парсинга блока
                    $this->fireEvent('afterParseContainer', ['name' => $lcn]);
                }
            }
        }
    }

    /*
        Вызывается при каждом распознавании
    */
    public function onEvent($event, $callback) {

        if (!isset($this->_eventStack[$event])) {
            $this->_eventStack[$event] = [];
        }
        $this->_eventStack[$event][] = $callback;
        return $this;
    }

    /*
        Выстреливает событие
    */
    public function fireEvent($event, $params = null, $once = false) {

        if ($params == null) {
            $params = [];
        }

        $params['context'] = $this;

        if (!isset($this->_eventStack[$event])) {
            return false;
        }
        
        $count = count($this->_eventStack[$event]);

        if (isset($this->_eventStack[$event]) && $count > 0) {
            for ($i = 0; $i < $count; $i++) {
                call_user_func_array($this->_eventStack[$event][$i], $params);

                if ($once == true) {
                    array_splice($this->_eventStack[$event], $i, 1);
                }
            }
        }
    }

    /*
        Получить результаты парсинга
    */
    public function getResult() {
        return $this->result;
    }

    /*
        Очистить результаты парсинга
    */
    public function clearResult() {
        $this->result = [];
    }

}