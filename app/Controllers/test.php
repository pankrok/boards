<?php

namespace App\Controllers;

class test extends Controller
{
	

	public function index($request, $response)
    {
		$token = ($this->container->get('csrf')->generateToken());

		$this->captcha->code();
		$this->captcha->image();
		$_SESSION['captcha'] = md5($this->captcha->getCode());
		echo '<img src="'.$this->captcha->getImage().'">';
		echo '
			<form action="/captcha" method="POST">

			<input type="text" name="user_captcha_code" value="">

			<input type="submit" value="Login">
			<input id="csrf_name" type="hidden" name="csrf_name" value="'.$token['csrf_name'].'">
			<input id="csrf_value" type="hidden" name="csrf_value" value="'.$token['csrf_value'].'">

			</form>
	
		';
		
		return $response;
	}
	
	public function captcha($request, $response)
    {
		$data =  $_SESSION['captcha'];
		unset($_SESSION['captcha']);
		var_dump($data);
		
		if (md5($request->getParsedBody()['user_captcha_code']) === $data) {
			echo 'Code is valid!';
		} else {
			echo 'Code is invalid!';
		}
		
		return $response;
	}
}
