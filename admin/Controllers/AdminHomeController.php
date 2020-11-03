<?php

namespace Admin\Controllers;

use App\Controllers\Controller;

 /**
 * Authentication controller
 * @package BOARDS Forum
 */

class AdminHomeController extends Controller
{
	private function version()
	{
		return unserialize(base64_decode(file_get_contents(MAIN_DIR.'/bootstrap/info.dat')));
	}
	
	private function getNews()
	{
		
	}
	
	public function index($request, $response)
    {
		
		$forumInfo = self::version();
		
		$this->view->getEnvironment()->addGlobal('forumInfo', $forumInfo);
		return $this->view->render($response, 'home.twig');
	}
	
}