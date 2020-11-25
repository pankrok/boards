<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Settings;
use Application\Core\Controller as Controller;


class AdminSettingsController extends Controller
{
	
	public function index($request, $response)
	{

		$this->adminView->getEnvironment()->addGlobal('settings', $this->settings);
		

		return $this->adminView->render($response, 'settings.twig');	
	
	}
	
	public function saveSettings($request, $response)
	{
		
		echo file_put_contents(MAIN_DIR. 'environment/Config/settings.json', json_encode($request->getParsedBody(), JSON_PRETTY_PRINT));
		
		return $response;
		
		
	}
	
}