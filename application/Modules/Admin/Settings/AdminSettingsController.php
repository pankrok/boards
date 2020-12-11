<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Settings;
use Application\Core\Controller as Controller;
use Application\Core\Modules\Configuration\ConfigurationCore;

class AdminSettingsController extends Controller
{
	
	public function index($request, $response)
	{
		$this->adminView->getEnvironment()->addGlobal('settings', $this->settings);
		return $this->adminView->render($response, 'settings.twig');
	}
	
	public function saveSettings($request, $response)
	{
		if(ConfigurationCore::saveConfig($request->getParsedBody()))
		{
			$this->flash->addMessage('success', 'configuration updated.');
		}
		else
		{
			$this->flash->addMessage('danger', 'configuration update error.');
		}
		return $response
				->withHeader('Location', $this->router->urlFor('admin.get.settings'))
				->withStatus(302);
		
		
	}
	
}