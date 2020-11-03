<?php
use DI\Container;
use Slim\Factory\AppFactory;
use Respect\Validation\Validator as v;

ini_set('session.cookie_httponly', true);

session_start();
require MAIN_DIR . '/libs/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$container = $app->getContainer();

$container->set('settings', function ($container) {
    return json_decode(file_get_contents( MAIN_DIR . '/bootstrap/config.json' ), true);
});

$routeParser = $app->getRouteCollector()->getRouteParser();
$app->addRoutingMiddleware();

$errHandler = new App\Controllers\ErrorController($app->getCallableResolver(), $app->getResponseFactory());
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errHandler);

$app->add($errorMiddleware);

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

$container->set('captcha', function($container) use ($routeParser) {
	return new LordDashMe\SimpleCaptcha\Captcha();
});

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('settings')['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container->set('translator', function ($container) {
    $fallback = $container->get('settings')['translations']['fallback'];
	$loader = new \Illuminate\Translation\FileLoader(
		new \Illuminate\Filesystem\Filesystem(), $container->get('settings')['translations']['path']
	);
	$translator = new \Illuminate\Translation\Translator($loader, $_SESSION['lang'] ?? $fallback);
	$translator->setFallback($fallback);
	return $translator;
});

$container->set('db', function ($container) use ($capsule) {
    return $capsule;  
});

$container->set('auth', function ($container) {
    return new \App\Auth\Auth;
});

$container->set('userdata', function ($container) {
    return new \App\Controllers\User\UserDataController($container);
});

$container->set('flash', function($container) {
	return new \Slim\Flash\Messages;
});
$container->set('mailer', function($container){
	$mail = new App\Controllers\Mail\MailController($container);
	$mail->initMailer($container->get('settings')['mail']);
	return $mail->mailer;		
});	

$container->set('urlMaker', function(){
	
	return new \App\Controllers\UrlCreatorController();
	
});

$container->set('view', function($container){
    $view = new \Slim\Views\Twig(MAIN_DIR . '/skins/'. $container->get('settings')['skin'],[
        'cache' => false,
        'debug' => true,
    ]);
    
    $router = $container->get('router');

	$view->addExtension(new App\Views\Extensions\BaseUrlExtension($router, $container->get('urlMaker')));
	$view->addExtension(new App\Views\Extensions\TranslationExtension($container->get('translator')));
    $view->addExtension(new App\Views\Extensions\UserExtension($container->get('cache'), $container->get('userdata')));
	
    $view->getEnvironment()->addGlobal('auth', [ 
        'check' => $container->get('auth')->check(),
		'admin' => $container->get('auth')->admin(),
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

$container->set('stats', function($container)
{
    return new \App\Controllers\Board\StatisticController($container);
});

$container->set('HomeController', function($container)
{
    return new \App\Controllers\HomeController($container);
});

$container->set('ChatboxController', function ($container) {
  return new App\Controllers\Chatbox\ChatboxController($container);  
});

$container->set('TranslationController', function($container)
{
    return new \App\Controllers\TranslationController($container);
});

$container->set('UserPanelController', function($container)
{
    return new \App\Controllers\User\UserPanelController($container);
});
$container->set('AuthController', function($container)
{
    return new \App\Controllers\Auth\AuthController($container);
});
$container->set('PlotController', function($container)
{
    return new \App\Controllers\Board\PlotController($container);
});
$container->set('BoardController', function($container)
{
    return new \App\Controllers\Board\BoardController($container);
});

$container->set('AdminHomeController', function($container)
{
    return new \App\Controllers\Admin\AdminHomeController($container);
});

$container->set('AdminEditPost', function($container)
{
    return new \Admin\Controllers\Front\AdminFrontBoardController($container);
});


$container->set('csrf', function ($container) {
    $guard = new App\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $handler) {
        $request = $request->withAttribute("csrf_status", false);
        return $handler->handle($request);
    });
    return $guard;
});

$app->add(new \App\Middleware\UserActiveMiddleware($container));
$app->add(new \App\Middleware\EventMiddleware($container));
$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$app->add($container->get('csrf'));

v::with('App\\Validation\\Rules\\');


require MAIN_DIR . '/app/routes.php';