<?php
declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;

#home
$app->get('[/]', 'HomeController:index')->setName('home');
$app->get('/cron[/{key}]', 'CronController:main')->setName('cron');
$app->post('/online', 'OnlineController:setOnline')->setName('online');

#page
$app->get('/i[/{id}]', 'PageController:page')->setName('page');

#category
$app->get('/category/{category}/{category_id}[/{page}]', 'CategoryController:getCategory')->setName('category.getCategory');

#board
$app->get('/board/{board}/{board_id}[/{page}]', 'BoardController:getBoard')->setName('board.getBoard');

#plot
$app->group('/plot', function (RouteCollectorProxy $plot) {
    $plot->get('/{plot}/{plot_id:[0-9]+}[/[{page:[0-9]+}]]', 'PlotController:getPlot')->setName('board.getPlot');
    $plot->post('/editpost', 'PlotController:editPost')->setName('board.edit');
    $plot->get('/new/create/{board_id:[0-9]+}', 'PlotController:newPlot')->setName('board.newPlot');
    $plot->post('/new/send/post', 'PlotController:newPlotPost')->setName('board.newPlotPost');
    $plot->post('/replyPost', 'PlotController:replyPost')->setName('board.replyPost');
    $plot->post('/likePost', 'PlotController:likeit')->setName('board.likeit');
    $plot->post('/lockplot', 'PlotController:lockPlot')->setName('board.lock.plot');
    $plot->post('/rate-plot', 'PlotController:ratePlot')->setName('board.rate.plot');
});
#sign
$app->group('/auth', function (RouteCollectorProxy $auth) {
    $auth->get('/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $auth->get('/signin/tfa[/{mail}]', 'AuthController:twoFactorAuth')->setName('auth.signin.tfa');
    $auth->post('/signin', 'AuthController:postSignIn')->setName('auth.post.signin');
    $auth->get('/signup', 'AuthController:getSignUp')->setName('auth.signup');
    $auth->get('/resend', 'AuthController:resendActiveMail')->setName('auth.resend');
    $auth->get('/confirm-account[/{code}]', 'AuthController:confirmAccount')->setName('auth.confirm');
    $auth->post('/signup', 'AuthController:postSignUp')->setName('auth.post.signup');
    $auth->get('/logout', 'AuthController:getSignOut')->setName('auth.signout');
    $auth->post('/hint', 'AuthController:postHintUsers')->setName('auth.hint');
    $auth->post('/ref/captcha', 'AuthController:refreshCaptcha')->setName('auth.ref.captcha');
    $auth->get('/forgetpassword', 'ForgetPasswordController:index')->setName('auth.forget.pass');
    $auth->post('/forgetpassword/post', 'ForgetPasswordController:sendMail')->setName('auth.forget.pass.post');
    $auth->get('/forgetpassword/change/{id}/{hash}', 'ForgetPasswordController:chengePassword')->setName('auth.change.pass');
});
#chatbox
$app->group('/chatbox', function (RouteCollectorProxy $chatbox) {
    $chatbox->post('/postmessage', 'ChatboxController:postChatMessage')->setName('postChatbox');
    $chatbox->post('/loadmore', 'ChatboxController:loadMoreMessages')->setName('loadChatbox');
    $chatbox->post('/checknew', 'ChatboxController:checkNewMessage')->setName('checkNewMessage');
    $chatbox->post('/edit', 'ChatboxController:editMessage')->setName('editMessage');
});
#user
$app->group('/user', function (RouteCollectorProxy $user) {
    $user->post('/manage/tfa', 'UserPanelController:setTwoFactor')->setName('user.tfa');
    $user->get('[/{username}/{uid}]', 'UserPanelController:getProfile')->setName('user.profile');
    $user->post('[/{username}/{uid}]', 'UserPanelController:postProfilePicture')->setName('user.postPicture');
    $user->post('/changedata', 'UserPanelController:postChangeData')->setName('user.postChangeData');
});
#userlist
$app->get('/userlist[/{page}]', 'UserlistController:getList')->setName('userlist');

#mailbox
$app->group('/mailbox', function (RouteCollectorProxy $mailbox) {
    $mailbox->map(['GET', 'POST'], '/{folder}', 'MessageController:index')->setname('mailbox');
    $mailbox->get('/{folder}/message/{id}', 'MessageController:getMessage')->setname('mailbox.message');
    $mailbox->post('/send/message', 'MessageController:sendMessage')->setname('mailbox.send');
    $mailbox->post('/delete/message', 'MessageController:deleteMessage')->setname('mailbox.delete');
    $mailbox->post('/move/moveMessage', 'MessageController:moveMessage')->setname('mailbox.move');
});

#skin chang
$app->get('/setskin/{skin}', 'SkinController:change')->setName('changeskin');


#################
# ADMIN SECTION #
#################

$adm = $container->get('settings')['core']['admin'];
$app->group('/' .$adm, function (RouteCollectorProxy $admin) {
    $admin->get('[/]', 'AdminHomeController:index')->setName('admin.home');
    $admin->get('/configuration', 'AdminHomeController:config')->setName('admin.config');
    $admin->get('/auth/tfa[/{mail}]', 'AdminHomeController:tfa')->setName('admin.home.tfa');
    $admin->get('/logs[/{page}[/{items}]]', 'AdminLogController:index')->setName('admin.logs');
    $admin->post('/logs/set-item', 'AdminLogController:setItem')->setName('admin.logs.set.item');

    $admin->get('/reload/plugins', 'AdminHomeController:pluginReload')->setName('admin.reload.plugins'); // reload plugins
    $admin->get('/active', 'AdminHomeController:plugin');
  
    $admin->group('/board', function (RouteCollectorProxy $adminBoard) {
        $adminBoard->get('', 'AdminBoardController:index')->setName('admin.boards');
        $adminBoard->get('/edit[/{id}]', 'AdminBoardController:editBoard')->setName('admin.edit.board');
        $adminBoard->post('/edit[/{id}]', 'AdminBoardController:editBoard')->setName('admin.edit.board.post');
        $adminBoard->get('/delete/{element}/{id}', 'AdminBoardController:deleteBoard')->setName('admin.delete.board');

        $adminBoard->post('/order/post', 'AdminBoardController:orderPost')->setName('admin.board.order.post');
        $adminBoard->post('/addCategory/post', 'AdminBoardController:addCategory')->setName('admin.add.category');
        $adminBoard->map(['GET', 'POST'], '/category/edit/{id}', 'AdminBoardController:editCategory')->setName('admin.edit.category');
        $adminBoard->post('/addBoard/post', 'AdminBoardController:addBoard')->setName('admin.add.board');
        $adminBoard->post('/delete/confirm', 'AdminBoardController:deleteBoard')->setName('admin.delete.board.post');
    });

    $admin->group('/skin', function (RouteCollectorProxy $adminSkin) {
        $adminSkin->get('/skinslist[/[{page}]]', 'AdminSkinsController:skinsList')    ->setName('admin.skinlist');
        $adminSkin->get('/addskin', 'AdminSkinsController:addSkin')      ->setName('admin.add.skin');
        $adminSkin->post('/addskin', 'AdminSkinsController:addSkinPost')    ->setName('admin.add.skin.post');
        $adminSkin->post('/set/as/default', 'AdminSkinsController:setSkinDefault')  ->setName('admin.default.skin.post');
        $adminSkin->post('/delete/skin', 'AdminSkinsController:removeSkin')    ->setName('admin.delete.skin.post');
        $adminSkin->post('/reload/css/js', 'AdminSkinsController:reloadCssJs')    ->setName('admin.add.skin.reload');
        $adminSkin->post('/rename', 'AdminSkinsController:renameSkin')    ->setName('admin.skin.rename');
        $adminSkin->post('/copy', 'AdminSkinEditorController:copyTemplate')->setName('admin.skin.copy');
        $adminSkin->get('/add/file/{skin_id}', 'AdminSkinEditorController:addFile')  ->setName('admin.skin.addfile');
        $adminSkin->post('/add/file/post', 'AdminSkinEditorController:addFilePost')->setName('admin.skin.addfile.post');
    
        $adminSkin->group('/module', function (RouteCollectorProxy $adminSkinModule) {
            $adminSkinModule->get('/list/{route}/{id}', 'AdminSkinsBoxesController:index')->setName('admin.modules.skin.get');
            $adminSkinModule->get('/edit/{skin_id}/{id}', 'AdminSkinsBoxesController:editModule')->setName('admin.modules.skin.edit');
            $adminSkinModule->post('/remove', 'AdminSkinsBoxesController:fastRemove')->setName('admin.modules.skin.fastRemove');
            $adminSkinModule->post('/delete', 'AdminSkinsBoxesController:deleteModule')->setName('admin.modules.skin.delete');
            $adminSkinModule->get('/new/{skin_id}', 'AdminSkinsBoxesController:editModule')->setName('admin.modules.skin.new');
            $adminSkinModule->post('/save', 'AdminSkinsBoxesController:saveModule')->setName('admin.modules.skin.save');
        });
      
        $adminSkin->get('/edit/twig/{skin_id}[/{id}]', 'AdminSkinEditorController:twigEditor')->setName('admin.skin.twig.edit');
        $adminSkin->get('/edit/css/{skin_id}[/{id}]', 'AdminSkinEditorController:cssEditor')->setName('admin.skin.css.edit');
        $adminSkin->get('/edit/js/{skin_id}[/{id}]', 'AdminSkinEditorController:jsEditor')->setName('admin.skin.js.edit');
        $adminSkin->post('/twig/save', 'AdminSkinEditorController:twigSave')->setName('admin.skin.twig.save');
        $adminSkin->post('/delete/file', 'AdminSkinEditorController:deleteFile')->setName('admin.skin.file.delete');
    });
  
    $admin->group('/plugins', function (RouteCollectorProxy $adminPlug) {
        $adminPlug->get('[/]', 'AdminPluginController:pluginList')->setName('admin.plugins.get');
        $adminPlug->map(['GET', 'POST'], '/show/{pluginName}[/{param1}[/{param2}]]', 'AdminPluginController:pluginControl')->setName('admin.plugins.ext');
    
        $adminPlug->post('/install', 'AdminPluginController:pluginInstall')->setName('admin.plugins.install');
        $adminPlug->post('/uninstall', 'AdminPluginController:pluginUninstall')->setName('admin.plugins.uninstall');
        $adminPlug->post('/active', 'AdminPluginController:pluginActive')->setName('admin.plugins.active');
        $adminPlug->post('/deactive', 'AdminPluginController:pluginDective')->setName('admin.plugins.deactive');
    });
  
    
    $admin->group('/settings', function (RouteCollectorProxy $adminSettings) {
        $adminSettings->get('/config', 'AdminSettingsController:index')->setName('admin.get.settings');
        $adminSettings->post('/config/save', 'AdminSettingsController:saveSettings')->setName('admin.post.settings');
        $adminSettings->map(['GET', 'POST'], '/mail/config', 'AdminSettingsController:mailer')->setName('admin.mail.settings');
        $adminSettings->map(['GET', 'POST'], '/cleancache', 'AdminSettingsController:cleanCache')->setName('admin.cache');
      
        $adminSettings->group('/menu', function (RouteCollectorProxy $adminMenu) {
            $adminMenu->get('/list', 'AdminMenuController:index')->setName('admin.menu');
            $adminMenu->map(['GET', 'POST'], '/manage[/{id}]', 'AdminMenuController:manageItem')->setName('admin.menu.manage');
            $adminMenu->post('/delete', 'AdminMenuController:deleteItem')->setName('admin.menu.delete');
        });
      
        $adminSettings->group('/pages', function (RouteCollectorProxy $adminPages) {
            $adminPages->get('/list', 'AdminPagesController:index')->setName('admin.pages');
            $adminPages->map(['GET', 'POST'], '/manage[/{id}]', 'AdminPagesController:managePage')->setName('admin.page.edit');
            $adminPages->post('/delete', 'AdminPagesController:deletePage')->setName('admin.page.delete');
        });
        
        $adminSettings->group('/mail-templates', function (RouteCollectorProxy $adminMailTemplates) {
            $adminMailTemplates->get('/list', 'AdminMailTemplateController:index')->setName('admin.mail.template.list');
            $adminMailTemplates->get('/edit[/{twig}]', 'AdminMailTemplateController:editMailTemplate')->setName('admin.mail.template.edit');
            $adminMailTemplates->post('/save', 'AdminMailTemplateController:saveMailTemplate')->setName('admin.mail.template.save');
        });
        
        $adminSettings->get('/update', 'AdminUpdateController:index')->setName('admin.update');
    });
    
    $admin->group('/users', function (RouteCollectorProxy $adminUser) {
        $adminUser->get('/config', 'AdminUserController:usersConfig')->setName('admin.users.and.groups');
        $adminUser->get('/list[/{page}]', 'AdminUserController:index')->setName('admin.users');
        $adminUser->get('/edit/{id}', 'AdminUserController:editUser')->setName('admin.user.edit');
        $adminUser->post('/save', 'AdminUserController:saveUserData')->setName('admin.user.save');
        $adminUser->get('/add-user', 'AdminUserController:addUser')->setName('admin.user.add');
        $adminUser->post('/post/add-user', 'AdminUserController:addUserPost')->setName('admin.user.add.save');
        $adminUser->post('/delete', 'AdminUserController:deleteUser')->setName('admin.user.delete');
        $adminUser->get('/groups[/{page}]', 'AdminGroupController:index')->setName('admin.groups');
        $adminUser->get('/group/add', 'AdminGroupController:addGroup')->setName('admin.groups.add');
        $adminUser->get('/group/edit/{id}', 'AdminGroupController:editGroup')->setName('admin.groups.edit');
        $adminUser->post('/group/manage', 'AdminGroupController:groupPost')->setName('admin.groups.post');
        $adminUser->post('/group/delete', 'AdminGroupController:deleteGroup')->setName('admin.groups.delete');
    });
});

if ($container->get('settings')['cache']['active']) {
    if (!is_dir(MAIN_DIR.$container->get('settings')['cache']['cache_dir'].md5('slimRoute'))) {
        mkdir(MAIN_DIR.$container->get('settings')['cache']['cache_dir'].md5('slimRoute'));
    }
        
    $routeCollector = $app->getRouteCollector();
    $routeCollector->setCacheFile(MAIN_DIR.
    $container->get('settings')['cache']['cache_dir'].
    md5('slimRoute').
    '/'.
    md5('route').
    $container->get('settings')['cache']['cache_ext']);
}
