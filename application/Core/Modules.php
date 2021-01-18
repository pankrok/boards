<?php

declare(strict_types = 1);

use Slim\App;

return function (App $app) {
    $container = $app->getContainer();
    
    $container->set('CronController', function ($container) {
        return new Application\Modules\CronJobs\CronController($container);
    });
    
    $container->set('HomeController', function ($container) {
        return new Application\Modules\Board\HomeController($container);
    });
    
    $container->set('PageController', function ($container) {
        return new Application\Modules\Board\PageController($container);
    });
    
    $container->set('CategoryController', function ($container) {
        return new Application\Modules\Board\CategoryController($container);
    });
    
    $container->set('BoardController', function ($container) {
        return new Application\Modules\Board\BoardController($container);
    });
    
    $container->set('PlotController', function ($container) {
        return new Application\Modules\Board\PlotController($container);
    });
    
    $container->set('UserPanelController', function ($container) {
        return new Application\Modules\User\UserPanelController($container);
    });
    
    $container->set('AuthController', function ($container) {
        return new Application\Modules\Auth\AuthController($container);
    });
    
    $container->set('ChatboxController', function ($container) {
        return new Application\Modules\Chatbox\ChatboxController($container);
    });
    
    $container->set('StatisticController', function ($container) {
        return new Application\Modules\Statistic\StatisticController($container);
    });
    
    $container->set('SkinController', function ($container) {
        return new Application\Modules\Skins\SkinController($container);
    });
    
    $container->set('UserlistController', function ($container) {
        return new Application\Modules\Board\UserlistController($container);
    });
    
    $container->set('ForgetPasswordController', function ($container) {
        return new Application\Modules\Auth\ForgetPasswordController($container);
    });
    
    $container->set('OnlineController', function ($container) {
        return new Application\Modules\User\OnlineController($container);
    });
    
    # Admin controllers section
    
    $container->set('AdminHomeController', function ($container) {
        return new Application\Modules\Admin\AdminHomeController($container);
    });
    
    $container->set('AdminPagesController', function ($container) {
        return new Application\Modules\Admin\Board\AdminPagesController($container);
    });
    
    $container->set('AdminBoardController', function ($container) {
        return new Application\Modules\Admin\Board\AdminBoardController($container);
    });
    
    $container->set('AdminSkinsController', function ($container) {
        return new Application\Modules\Admin\Skins\AdminSkinsController($container);
    });
    
    $container->set('AdminSkinsBoxesController', function ($container) {
        return new Application\Modules\Admin\Skins\AdminSkinsBoxesController($container);
    });
    
    $container->set('AdminSkinEditorController', function ($container) {
        return new Application\Modules\Admin\Skins\AdminSkinEditorController($container);
    });
    
    $container->set('AdminSettingsController', function ($container) {
        return new Application\Modules\Admin\Settings\AdminSettingsController($container);
    });
    
    $container->set('AdminMenuController', function ($container) {
        return new Application\Modules\Admin\Settings\AdminMenuController($container);
    });
    
    $container->set('AdminPluginController', function ($container) {
        return new Application\Modules\Admin\Plugins\AdminPluginController($container);
    });
    
    $container->set('AdminUserController', function ($container) {
        return new Application\Modules\Admin\Users\AdminUserController($container);
    });
    
    $container->set('AdminGroupController', function ($container) {
        return new Application\Modules\Admin\Users\AdminGroupController($container);
    });
    
    $container->set('AdminUpdateController', function ($container) {
        return new Application\Modules\Admin\Updater\AdminUpdateController($container);
    });
};
