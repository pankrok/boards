<?php

declare(strict_types = 1);

use Slim\App;
use Application\Core\Modules\ErrorController\ErrorController;

return function(App $app)
{
	$container = $app->getContainer();
	$debug = (bool)$container->get('settings')['core']['debug'];
	
	
	$errorMiddleware = $app->addErrorMiddleware($debug , $debug , $debug);
	$errHandler = new ErrorController($app->getCallableResolver(), $app->getResponseFactory());
	$errHandler->setLevel($container->get('settings')['core']['log_level']);
	$errorMiddleware->setDefaultErrorHandler($errHandler);

	$app->add($errorMiddleware);

	$app->add(new Application\Middleware\TrailingShashMiddleware(true));
	$app->add(new Application\Middleware\CacheMiddleware($container));
	//$app->add(new Application\Middleware\Before($container)); THIS IS AN EXAMPLE, WILL BE REMOVE
	//$app->add(new Application\Middleware\After($container)); THIS IS AN EXAMPLE, WILL BE REMOVE

	$app->addRoutingMiddleware();
};