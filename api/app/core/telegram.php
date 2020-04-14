<?php
namespace core;

class telegram extends model
{
    private $api_url = 'https://api.telegram.org/bot';
    private $token = '';

	public function __construct()
	{
		$conf = parse_ini_file(__DIR__.'/../config.ini', true);
		$conf = $conf['telegram'];
		$this->token = $conf['token'];
    }

    public function getUpdates()
    {
        $data = array();
        $update_id = $this->redis->get("telegram_update_id");
        if ($update_id) {
            $data['offset'] = $update_id+1;
        }
        $response = $this->apiCall('getUpdates', $data)->result;
        if (count($response)) {
            $this->redis->set("telegram_update_id", $response[count($response)-1]->update_id);
        }

        $response = $this->apiCall('getUpdates', [])->result;
        return $response;
    }

    private function apiCall($method, $data)
    {
        $ch = curl_init($this->api_url.$this->token.'/'.$method);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        curl_close($ch);
        return json_decode($resp);
    }

    public function __call($method, $params)
    {
        return $this->apiCall($method, $params[0]);
    }
}