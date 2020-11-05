<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Respect\Validation\Validator as v;

session_start();
require MAIN_DIR . '/libraries/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$container = $app->getContainer();

$container->set('settings', function ($container) {
    return parse_ini_file(MAIN_DIR . '/environment/Config/settings.ini', true);
});

$container->set('db_settings', function ($container) {
    return  require(MAIN_DIR . '/environment/Config/db_settings.php');
});

$routeParser = $app->getRouteCollector()->getRouteParser();
$container->set('router', function($container) use ($routeParser) {
	return $routeParser;
});

$container->set('cache', function($container) {
	$cacheSettings = $container->get('settings')['cache'];
	return new Application\Core\Modules\Cache\CacheCore($cacheSettings);
});

$container->set('images', function($container) {
	return new Application\Core\Modules\Images\Resizer($container->get('settings')['images']['path']);
});

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('db_settings'));
//$capsule->setAsGlobal();
$capsule->bootEloquent();

$container->set('db', function ($container) use ($capsule) {
    return $capsule;  
});

$container->set('translator', function ($container) {
	$fallback = $container->get('settings')['translations']['fallback'];
	$loader = new \Illuminate\Translation\FileLoader(
		new \Illuminate\Filesystem\Filesystem(), MAIN_DIR . $container->get('settings')['translations']['path']
	);
	$translator = new \Illuminate\Translation\Translator($loader, $_SESSION['lang'] ?? $fallback);
	$translator->setFallback($fallback);
	return $translator;
});

$container->set('urlMaker', function(){	
	return new \Application\Core\Modules\ToUrl\ToUrl();	
});

$container->set('flash', function($container) {
	return new \Slim\Flash\Messages;
});

$container->set('view', function($container){
	
	$twigSettings = $container->get('settings')['twig'];

    $view = new \Slim\Views\Twig(MAIN_DIR . '/skins/'. $twigSettings['skin'] . '/tpl',[
        'cache' => (bool)$twigSettings['cache'] ? MAIN_DIR . '/skins/'. $twigSettings['skin'] . '/cache' : false,
        'debug' => (bool)$twigSettings['debug'],
    ]);
    
    $router = $container->get('router');

	$view->addExtension(new Application\Core\Modules\Views\Extensions\UrlExtension($router, $container->get('urlMaker')));
	$view->addExtension(new Application\Core\Modules\Views\Extensions\TranslationExtension($container->get('translator')));
    //$view->addExtension(new App\Views\Extensions\UserExtension($container->get('cache'), $container->get('userdata')));
	
    $view->getEnvironment()->addGlobal('auth', [ 
       'check' => $container->get('auth')->check(),
//		'admin' => $container->get('auth')->admin(),
        'user' => $container->get('auth')->user(),
    ]);
	
    $view->getEnvironment()->addGlobal('flash', $container->get('flash'));
	$view->getEnvironment()->addGlobal('setString', $container->get('urlMaker'));
	
    return $view;
});

$container->set('adminView', function($container){
	
	$twigSettings = $container->get('settings')['admin'];
    $view = new \Slim\Views\Twig(MAIN_DIR . '/public/admin/'.$twigSettings['skin'].'/tpl',[
        'cache' => (bool)$twigSettings['cache'],
        'debug' => (bool)$twigSettings['debug'],
    ]);
    
    $router = $container->get('router');

	$view->addExtension(new Application\Core\Modules\Views\Extensions\UrlExtension($router, $container->get('urlMaker')));
	$view->addExtension(new Application\Core\Modules\Views\Extensions\TranslationExtension($container->get('translator')));
    //$view->addExtension(new App\Views\Extensions\UserExtension($container->get('cache'), $container->get('userdata')));
	
	$view->getEnvironment()->addGlobal('admin_url', $container->get('settings')['core']['admin']);
    $view->getEnvironment()->addGlobal('auth', [ 
       'check' => $container->get('auth')->check(),
//		'admin' => $container->get('auth')->admin(),
        'user' => $container->get('auth')->user(),
    ]);
	
    $view->getEnvironment()->addGlobal('flash', $container->get('flash'));
	$view->getEnvironment()->addGlobal('setString', $container->get('urlMaker'));
	
    return $view;
});

$container->set('auth', function ($container) {
    return new Application\Core\Modules\Auth\Auth;
});

$container->set('validator', function () {
  return new Application\Core\Modules\Validation\Validator;  
});

$container->set('csrf', function ($container) {
    $guard = new Application\Core\Modules\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $handler) {
        $request = $request->withAttribute("csrf_status", false);
        return $handler->handle($request);
    });
    return $guard;
});

$container->set('captcha', function($container) use ($routeParser) {
	return new LordDashMe\SimpleCaptcha\Captcha();
});

$container->set('event', function($container) {
	return new Application\Core\Modules\Plugins\PluginController($container);
});

$middleware = require 'Middleware.php';
$middleware($app);

$modules = require 'Modules.php';
$modules($app);

v::with('Application\\Core\\Modules\\Validation\\Rules\\');

require 'Routes.php';



