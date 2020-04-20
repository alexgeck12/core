<?php
//ini_set("display_errors", "1");
//error_reporting(E_ALL);

setlocale(LC_ALL, 'ru_RU.UTF-8');
define("ROOT", rtrim(realpath(__DIR__.'/../../../'), '/'));

function __autoload($class)
{
	$file = ROOT . '/api/app/' . str_replace('\\', '/', $class) . '.php';
	if (file_exists($file)) {

		include_once $file;
	} else {
		return false;
	}
}

class common extends core\model
{
    protected $settings;
    protected $TransactionID;

    public function __construct()
    {
        $this->settings = parse_ini_file(ROOT . '/api/app/config.ini', true)["mygiftcard"];
        $this->TransactionID = $this->redis->hGet('transaction', 'id');
        if (!$this->TransactionID) {
            $this->TransactionID = $this->redis->hIncrBy('transaction', 'id', 10);
        }
    }

    public function request($method, $params = [])
    {
        $this->TransactionID = $this->redis->hIncrBy('transaction', 'id', 1);

        $request = [
            'Authentication' => [
                'Login' => $this->settings['user'],
                'TransactionID' => $this->TransactionID,
                'MethodName' => $method,
                'Hash' => md5($this->TransactionID.$method.$this->settings['user'].$this->settings['password'])
            ],
            'Parameters' => $params
        ];

        $xml = new SimpleXMLElement('<Request/>');
        $this->array_to_xml($request, $xml);
        $request = $xml->asXML();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml"]);
        curl_setopt($ch, CURLOPT_URL, $this->settings['host'].$method.'/');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $resp = curl_exec($ch);

        if (curl_errno($ch)) {
            $info = curl_getinfo($ch);
            var_dump($info);
            var_dump(curl_error($ch));
        }

        curl_close($ch);

        return $resp;
    }

    protected function array_to_xml($data, &$xml_data) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if(is_numeric($key)) {
                    $key = 'item'.$key;
                }
                $sub_node = $xml_data->addChild($key);
                $this->array_to_xml($value, $sub_node);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}