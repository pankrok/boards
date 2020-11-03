<?php

namespace Admin\Curl;

 /**
 * Authentication controller
 * @package BOARDS Forum
 */

class Updates 
{
	private function getToken()
	{
		return file_get_contents(MAIN_DIR.'/bootstrap/info.dat')
	}	
	
	
	private function info($data)
	{
		return unserialize(base64_decode($data));
	}
	
	private function curl($point)
	{
		$token = self::getToken();
		$data = self::info($token);
		$host = $data['host'];
		$process = curl_init();
		$headers = array();
		$headers[] = "Content-Type: application/json";
		$headers[] = "Authorization: Bearer ".$token;

		curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($process, CURLOPT_URL, "https://$host/$point");
		curl_setopt($process, CURLOPT_HEADER, 0);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($process);
		curl_close($process);

		return json_decode($return);
	}
	
	public function updates()
    {
		return self::curl('update');
	}
	
	public function news()
	{
		return self::curl('news');
	}
	
}