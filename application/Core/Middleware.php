<?php

declare(strict_types = 1);

use Slim\App;
use Application\Core\Modules\ErrorController\ErrorController;

return function(App $app)
{
	$container = $app->getContainer();
	$debug = (bool)$container->get('settings')['core']['debug'];
	
	$app->addBodyParsingMiddleware();
	$errorMiddleware = $app->addErrorMiddleware($debug , $debug , $debug);
	$errHandler = new ErrorController($app->getCallableResolver(), $app->getResponseFactory());
	$errHandler->setLevel($container->get('settings')['core']['log_level']);
	$errorMiddleware->setDefaultErrorHandler($errHandler);

	$app->add($errorMiddleware);

	$app->add(new Application\Middleware\TrailingShashMiddleware(true));
	$app->add(new Application\Middleware\OldInputMiddleware($container));
	$app->add(new Application\Middleware\ModulesMiddleware($container));
	$app->add(new Application\Middleware\CacheMiddleware($container));
	$app->add(new Application\Middleware\EventMiddleware($container));
	$app->add(new Application\Middleware\MessageMiddleware($container));


	$app->addRoutingMiddleware();
};