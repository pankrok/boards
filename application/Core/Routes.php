<?php
declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;

#home
$app->get('[/]', 'HomeController:index')->setName('home');
$app->get('/cron[/{key}]', 'CronController:main')->setName('cron');

#category
$app->get('/category/{category}/{category_id}[/{page}]', 'CategoryController:getCategory')->setName('category.getCategory');

#board
$app->get('/board/{board}/{board_id}[/{page}]', 'BoardController:getBoard')->setName('board.getBoard');

#plot
$app->group('/plot', function (RouteCollectorProxy $plot) {
	$plot->get('/{plot}/{plot_id:[0-9]+}[/[{page:[0-9]+}]]', 'PlotController:getPlot')->setName('board.getPlot');
	$plot->get('/new/create/{board_id}', 'PlotController:newPlot')->setName('board.newPlot');
	$plot->post('/new/send/post', 'PlotController:newPlotPost')->setName('board.newPlotPost');
	$plot->post('/replyPost', 'PlotController:replyPost')->setName('board.replyPost');
	$plot->post('/likePost', 'PlotController:likeit')->setName('board.likeit');
});
#sign
$app->group('/auth', function (RouteCollectorProxy $auth) {
	$auth->get('/signin', 'AuthController:getSignIn')->setName('auth.signin');
	$auth->post('/signin', 'AuthController:postSignIn')->setName('auth.post.signin');
	$auth->get('/signup', 'AuthController:getSignUp')->setName('auth.signup');
	$auth->post('/signup', 'AuthController:postSignUp')->setName('auth.post.signup');
	$auth->get('/autlogout', 'AuthController:getSignOut')->setName('auth.signout');
	$auth->post('/hint', 'AuthController:postHintUsers')->setName('auth.hint');
	$auth->post('/ref/captcha', 'AuthController:refreshCaptcha')->setName('auth.ref.captcha');
});
#chatbox
$app->group('/chatbox', function (RouteCollectorProxy $chatbox) {
	$chatbox->post('/postmessage', 'ChatboxController:postChatMessage')->setName('postChatbox');
	$chatbox->post('/loadmore', 'ChatboxController:loadMoreMessages')->setName('loadChatbox');
	$chatbox->post('/checknew', 'ChatboxController:checkNewMessage')->setName('checkNewMessage');
});
#user
$app->group('/user', function (RouteCollectorProxy $user) {
	$user->get('[/{username}/{uid}]', 'UserPanelController:getProfile')->setName('user.profile');
	$user->post('[/{username}/{uid}]', 'UserPanelController:postProfilePicture')->setName('user.postPicture');
	$user->post('/changedata', 'UserPanelController:postChangeData')->setName('user.postChangeData');
});
#userlist
$app->get('/userlist[/{page}]', 'UserlistController:getList')->setName('userlist');

#skin chang
#userlist
$app->get('/setskin/{skin}', 'SkinController:change')->setName('changeskin');


#################
# ADMIN SECTION #
#################

$adm = $container->get('settings')['core']['admin'];
$app->group('/' .$adm, function (RouteCollectorProxy $admin) {
	$admin->get('', 'AdminHomeController:index')->setName('admin.home');

	$admin->get('/board', 'AdminBoardController:index')->setName('admin.boards');
	$admin->get('/board/edit[/{id}]', 'AdminBoardController:editBoard')->setName('admin.edit.board');
	$admin->post('/board/edit[/{id}]', 'AdminBoardController:editBoard');
	$admin->get('/board/delete/{element}/{id}', 'AdminBoardController:deleteBoard')->setName('admin.delete.board');

	$admin->post('/board/order/post', 'AdminBoardController:orderPost')->setName('admin.board.order.post');
	$admin->post('/board/addCategory/post', 'AdminBoardController:addCategory')->setName('admin.add.category');
	$admin->post('/board/addBoard/post', 'AdminBoardController:addBoard')->setName('admin.add.board');
	$admin->post('/board/delete/confirm', 'AdminBoardController:deleteBoard')->setName('admin.delete.board.post');

	$admin->get('/active', 'AdminHomeController:plugin');

	$admin->get('/skinslist[/[{page}]]', 'AdminSkinsController:skinsList')->setName('admin.skinlist');
	$admin->get('/addskin', 'AdminSkinsController:addSkin')->setName('admin.add.skin');
	$admin->post('/addskin', 'AdminSkinsController:addSkinPost')->setName('admin.add.skin.post');
	$admin->post('/set/as/default', 'AdminSkinsController:setSkinDefault')->setName('admin.default.skin.post');
	$admin->post('/delete/skin', 'AdminSkinsController:removeSkin')->setName('admin.delete.skin.post');
	$admin->post('/skin/reload/css/js', 'AdminSkinsController:reloadCssJs')->setName('admin.add.skin.reload');
	$admin->get('/settings', 'AdminSettingsController:index')->setName('admin.get.settings');
});