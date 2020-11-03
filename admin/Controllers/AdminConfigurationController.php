<?php

namespace Admin\Controllers;

use App\Controllers\Controller;

 /**
 * Authentication controller
 * @package BOARDS Forum
 */

class AdminConfigurationController extends Controller
{
	
	public function saveSettings($request, $response)
	{
		$data = file_get_contents(MAIN_DIR.'/bootstrap/config.php');
		foreach($request->getParsedBody() as $key => $value)
		{
			$data = str_replace($key, $value, $data);
		}
	}
	
	public function settings($request, $response)
    {
		$this->view->getEnvironment()->addGlobal('settings', $this->settings);
		return $this->view->render($response, 'settings.twig');
	}
	
}