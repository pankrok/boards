<?php
use DI\Container;
use Slim\Factory\AppFactory;
use Respect\Validation\Validator as v;

ini_set('session.cookie_httponly', true);

session_start();
require MAIN_DIR . '/libs/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$admin = AppFactory::create();
$container = $admin->getContainer();

$routeParser = $admin->getRouteCollector()->getRouteParser();
$errorMiddleware = $admin->addErrorMiddleware(true, true, true);
$admin->add($errorMiddleware);


$container->set('settings', function ($container) {
    return json_decode(file_get_contents( MAIN_DIR . '/bootstrap/config.json' ), true);
});

$container->set('logger', function ($container) {
    return new App\Controllers\InfologController();
});

$container->set('cache', function($container) {
	$cache = new App\CacheManager\CacheManager($container->get('settings')['cache'], $container->get('logger'));
	return $cache->cache();
});

$container->set('router', function($container) use ($routeParser) {
	return $routeParser;
});

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('settings')['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container->set('translator', function ($container) {
    $fallback = $container->get('settings')['translations']['fallback'];
	$loader = new \Illuminate\Translation\FileLoader(
		new \Illuminate\Filesystem\Filesystem(), MAIN_DIR.'/admin/Lang'
	);
	$translator = new \Illuminate\Translation\Translator($loader, $_SESSION['lang'] ?? $fallback);
	$translator->setFallback($fallback);
	return $translator;
});

$container->set('db', function ($container) use ($capsule) {
    return $capsule;  
});

$container->set('auth', function ($container) {
    return new \Admin\Auth\AdminAuth;
});

$container->set('flash', function($container) {
	return new \Slim\Flash\Messages;
});
$container->set('mailer', function($container){
	$mail = new App\Controllers\Mail\MailController($container);
	$mail->initMailer([
			'type' => 'MAIL',
			'email' => 'pankrok@gmail.com',
			'name' => 'JR'	
			]);
	return $mail->mailer;		
});	

$container->set('urlMaker', function(){
	return new \App\Controllers\UrlCreatorController();
});

$container->set('view', function($container){
    $view = new \Slim\Views\Twig(MAIN_DIR . '/admin/Skins/admin',[
        'cache' => false,
        'debug' => true,
    ]);
    
    $router = $container->get('router');

	$view->addExtension(new App\Views\Extensions\BaseUrlExtension($router, $container->get('urlMaker')));
	$view->addExtension(new App\Views\Extensions\TranslationExtension($container->get('translator')));
	
    $view->getEnvironment()->addGlobal('auth', [ 
        'check' => $container->get('auth')->check(),
        'user' => $container->get('auth')->user(),
    ]);
	
	$view->getEnvironment()->addGlobal('checkOnline', time());
    $view->getEnvironment()->addGlobal('flash', $container->get('flash'));
	$view->getEnvironment()->addGlobal('setString', $container->get('urlMaker'));
	
    return $view;
});

$container->set('resizer', function (){
    return new App\CacheManager\ImageResizer();;  
});

$container->set('validator', function () {
  return new App\Validation\Validator;  
});

$container->set('event', function($container) {
	return new App\Controllers\Plugins\PluginController($container);
});


$container->set('AdminHomeController', function($container)
{
    return new Admin\Controllers\AdminHomeController($container);
});

$container->set('AdminAuthController', function($container)
{
    return new Admin\Controllers\Auth\AdminAuthController($container);
});

$container->set('AdminBoardsController', function($container)
{
    return new Admin\Controllers\AdminBoardsController($container);
});

$container->set('AdminConfigurationController', function($container)
{
    return new Admin\Controllers\AdminConfigurationController($container);
});


$container->set('csrf', function ($container) {
    $guard = new App\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $handler) {
        $request = $request->withAttribute("csrf_status", false);
        return $handler->handle($request);
    });
    return $guard;
});

$admin->add(new \App\Middleware\EventMiddleware($container));
$admin->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$admin->add(new \App\Middleware\OldInputMiddleware($container));
$admin->add(new \App\Middleware\CsrfViewMiddleware($container));

$admin->add($container->get('csrf'));


v::with('App\\Validation\\Rules\\');

require MAIN_DIR . '/admin/routes.php';