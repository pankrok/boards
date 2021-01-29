<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Middleware\OutputBufferingMiddleware;
use Respect\Validation\Validator as v;
use Middlewares\TrailingSlash;
use MatthiasMullie\Minify;

session_set_cookie_params(60*60*24, '/; samesite=Lax');
session_start();

require MAIN_DIR . '/libraries/autoload.php';

$container = new Container();
global $tplDir;

AppFactory::setContainer($container);
$app = AppFactory::create();
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

$container->set('getBasePath', function () use ($app) {
    return $app->getBasePath();
});


$container->set('settings', function ($container) {
    return json_decode(file_get_contents(MAIN_DIR . '/environment/Config/settings.json'), true);
});
$tplDir = $container->get('settings')['twig']['skin'];

$container->set('db_settings', function ($container) {
    return  require(MAIN_DIR . '/environment/Config/db_settings.php');
});

$routeParser = $app->getRouteCollector()->getRouteParser();
$container->set('router', function ($container) use ($routeParser) {
    return $routeParser;
});

$container->set('purifier', function () {
    return  new HTMLPurifier();
});

$container->set('mailer', function ($container) {
    return  new Application\Core\Modules\Mailer\MailCore($container->get('view'));
});

$container->set('cache', function ($container) {
    $cacheSettings = $container->get('settings')['cache'];
    return new Application\Core\Modules\Cache\CacheCore($cacheSettings);
});

$container->set('images', function ($container) {
    return new Application\Core\Modules\Images\Resizer($container->get('settings')['images']['path']);
});

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('db_settings'));
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container->set('db', function ($container) use ($capsule) {
    return $capsule;
});

$container->set('translator', function ($container) {
    $fallback = $container->get('settings')['translations']['fallback'];
    $loader = new \Illuminate\Translation\FileLoader(
        new \Illuminate\Filesystem\Filesystem(),
        MAIN_DIR . '/environment/Translations'
    );
    $translator = new \Illuminate\Translation\Translator($loader, $_SESSION['lang'] ?? $fallback);
    $translator->setFallback($fallback);
    return $translator;
});

$container->set('urlMaker', function () {
    return new \Application\Core\Modules\ToUrl\ToUrl();
});

$container->set('flash', function ($container) {
    return new \Slim\Flash\Messages;
});

$container->set('UnreadController', function ($container) {
    return new \Application\Modules\Board\UnreadController($container);
});

$container->set('tfa', function ($container) {
    $name = $container->get('settings')['board']['main_page_name'];
    $tfa = new \stdClass();
    $tfa->google = new RobThree\Auth\TwoFactorAuth( $name, 6, 30, 'sha1' );
    $tfa->mail   = new RobThree\Auth\TwoFactorAuth( $name, 6, 300, 'sha1' );
    return $tfa;
});

$container->set('view', function ($container) {
    $assets = $container->get('getBasePath') . '/public';
    
    $twigSettings = $container->get('settings')['twig'];
    $skin = $_SESSION['skin'] ?? $twigSettings['skin'];
        
    
    $view = new \Slim\Views\Twig(MAIN_DIR . '/skins/'. $skin . '/tpl', [
        'cache' => (bool)$twigSettings['cache'] ? MAIN_DIR . '/skins/'. $skin . '/cache/twig' : false,
        'debug' => (bool)$twigSettings['debug'],
    ]);
    
    $router = $container->get('router');
    if ($twigSettings['debug']) {
        $view->addExtension(new \Twig\Extension\DebugExtension());
    }
    $view->addExtension(new \Twig\Extension\StringLoaderExtension());
    $view->addExtension(new Application\Core\Modules\Views\Extensions\UrlExtension($router, $container->get('urlMaker')));
    $view->addExtension(new Application\Core\Modules\Views\Extensions\TranslationExtension($container->get('translator')));
    $view->addExtension(new Application\Core\Modules\Views\Extensions\OnlineExtension($container->get('OnlineController')));
    $view->addExtension(new Application\Core\Modules\Views\Extensions\UnreadExtension($container->get('UnreadController')));

    if (file_exists(MAIN_DIR . '/skins/' . $skin . '/cache_assets.json')) {
        $skinAssets = json_decode(file_get_contents(MAIN_DIR . '/skins/' . $skin . '/cache_assets.json'), true);
        
        $view->getEnvironment()->addGlobal('css', $skinAssets['css']);
        $view->getEnvironment()->addGlobal('js', $skinAssets['js']);
    }
    

    $view->getEnvironment()->addGlobal('unread', $container->get('MessageController')->countUnreadMessages());
    $view->getEnvironment()->addGlobal('auth', [
       'check' => $container->get('auth')->check(),
       'user' => $container->get('auth')->user(),
       'admin' => $container->get('auth')->checkAdmin()
    ]);
    
    $view->getEnvironment()->addGlobal('menus', Application\Modules\Board\MenuController::getMenu());
    
    if (file_exists(MAIN_DIR . '/environment/Config/lock')) {
        $lock = 1;
    } else {
        $lock = $container->get('settings')['board']['boards_off'];
    }
    
    $view->getEnvironment()->addGlobal('boards_off', $lock);
    $view->getEnvironment()->addGlobal('main_title', $container->get('settings')['board']['main_page_name']);
    
    $view->getEnvironment()->addGlobal('assets', $assets);
    $view->getEnvironment()->addGlobal('skin_assets', $skinAssets);
    
    $view->getEnvironment()->addGlobal('flash', $container->get('flash'));
    $view->getEnvironment()->addGlobal('setString', $container->get('urlMaker'));
    
    return $view;
});

$container->set('adminView', function ($container) {
    $twigSettings = $container->get('settings')['admin'];
    $view = new \Slim\Views\Twig(
        [
            MAIN_DIR . '/public/admin/'.$twigSettings['skin'].'/tpl',
            MAIN_DIR.'/plugins'
        ],
        [
            'cache' => false,
            'debug' => false,
        ]
    ); 
    
    $router = $container->get('router');
    $view->addExtension(new Application\Core\Modules\Views\Extensions\UrlExtension($router, $container->get('urlMaker')));
    $view->addExtension(new Application\Core\Modules\Views\Extensions\TranslationExtension($container->get('translator')));
      
    $filter = new \Twig\TwigFilter('base64_decode', function ($string) {
        return base64_decode($string);
    });

    $lock = file_exists(MAIN_DIR . '/environment/Config/lock');
    $view->getEnvironment()->addFilter($filter);
    $view->getEnvironment()->addGlobal('lock', $lock);
    $view->getEnvironment()->addGlobal('admin_url', $container->get('settings')['core']['admin']);
    $view->getEnvironment()->addGlobal('admin', $container->get('auth')->checkAdmin());
    $view->getEnvironment()->addGlobal('board_link', $_SERVER['HTTP_HOST']);
    $view->getEnvironment()->addGlobal('flash', $container->get('flash'));
    $view->getEnvironment()->addGlobal('setString', $container->get('urlMaker'));
    
    return $view;
});

$container->set('auth', function ($container) {
    return new Application\Core\Modules\Auth\Auth($container->get('tfa'));
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

$container->set('captcha', function ($container) use ($routeParser) {
    return new LordDashMe\SimpleCaptcha\Captcha();
});

$container->set('event', function ($container) {
    return new Application\Core\Modules\Plugins\PluginController($container);
});


$container->set('group', function () {
    return new Application\Modules\User\GroupController();
});

$middleware = require 'Middleware.php';
$middleware($app);

$modules = require 'Modules.php';
$modules($app);

v::with('Application\\Core\\Modules\\Validation\\Rules\\');

require 'Routes.php';
