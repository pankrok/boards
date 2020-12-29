<?php

declare(strict_types=1);

ini_set( 'display_errors', 'on');
DEFINE('MAIN_DIR', __DIR__ . '/../..');

use DI\Container;
use Slim\Factory\AppFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

session_set_cookie_params(60*60*24, '/; samesite=Lax');
session_start();

require MAIN_DIR . '/libraries/autoload.php';

$container = new Container();
global $tplDir;

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true,true);
$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'];
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }
    return '';
})());

$container = $app->getContainer();
$container->set('getBasePath', function() use($app)
{
	return $app->getBasePath();
});


$container->set('settings', function ($container) {
    return json_decode(file_get_contents(MAIN_DIR . '/environment/Config/settings.json'), true);
});
$container->set('db_settings', function ($container) {
    return  require(MAIN_DIR . '/environment/Config/db_settings.php');
});

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('db_settings'));
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container->set('db', function ($container) use ($capsule) {
    return $capsule;  
});

$container->set('log', function($container) {
	$log = new Logger('update');
	$log->pushHandler(new StreamHandler(MAIN_DIR . '/environment/Logs/update-' . base64_decode($container->get('settings')['core']['version'])  .'.log.txt', Logger::DEBUG));
	
	return $log;
});

$container->set('UpdateController', function($container)
{
	return new Application\Core\Modules\Updater\UpdateController($container);
});	
		
$app->get('[/]', 'UpdateController:index');

$app->run();