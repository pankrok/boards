<?php

declare(strict_types = 1);

use Slim\App;

return function(App $app)
{
	$container = $app->getContainer();
	
	$container->set('CronController', function($container)
	{
		return new Application\Modules\CronJobs\CronController($container);
	});
	
	$container->set('HomeController', function($container)
	{
		return new Application\Modules\Home\HomeController($container);
	});	
	
	$container->set('BoardController', function($container)
	{
		return new Application\Modules\Board\BoardController($container);
	});	
	
	$container->set('UserPanelController', function($container)
	{
		return new Application\Modules\User\UserPanelController($container);
	});		
	
	$container->set('AuthController', function($container)
	{
		return new Application\Modules\Auth\AuthController($container);
	});	
	
	$container->set('ChatboxController', function ($container) {
	  return new Application\Modules\Chatbox\ChatboxController($container);  
	});
	
	$container->set('PlotController', function($container)
	{
		return new Application\Modules\Board\PlotController($container);
	});
	
	$container->set('AdminHomeController', function($container)
	{
		return new Application\Modules\Admin\AdminHomeController($container);
	});
	
		$container->set('AdminBoardController', function($container)
	{
		return new Application\Modules\Admin\Board\AdminBoardController($container);
	});
	
};