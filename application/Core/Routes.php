<?php
declare(strict_types=1);

#home
$app->get('[/]', 'HomeController:index')->setName('home');
$app->get('/home[/]', 'HomeController:index')->setName('home');
$app->get('/cron[/{key}]', 'CronController:main')->setName('cron');
$app->get('/s', 'HomeController:session');

#category
$app->get('/category/{category}/{category_id}[/{page}]', 'CategoryController:getCategory')->setName('category.getCategory');

#board
$app->get('/board/{board}/{board_id}[/{page}]', 'BoardController:getBoard')->setName('board.getBoard');

#plot
$app->get('/plot/{plot}/{plot_id}[/[{page}]]', 'PlotController:getPlot')->setName('board.getPlot');
$app->get('/newplot/{board_id}', 'PlotController:newPlot')->setName('board.newPlot');
$app->post('/newplot/post', 'PlotController:newPlotPost')->setName('board.newPlotPost');
$app->post('/replyPost', 'PlotController:replyPost')->setName('board.replyPost');
$app->post('/likePost', 'PlotController:likeit')->setName('board.likeit');

#sign
$app->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
$app->post('/auth/signin', 'AuthController:postSignIn');
$app->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
$app->post('/auth/signup', 'AuthController:postSignUp');
$app->get('/auth/logout', 'AuthController:getSignOut')->setName('auth.signout');
$app->post('/auth/hint', 'AuthController:postHintUsers')->setName('auth.hint');

#chatbox
$app->post('/chatbox/postmessage', 'ChatboxController:postChatMessage')->setName('postChatbox');
$app->post('/chatbox/loadmore', 'ChatboxController:loadMoreMessages')->setName('loadChatbox');
$app->post('/chatbox/checknew', 'ChatboxController:checkNewMessage')->setName('checkNewMessage');

#user
$app->get('/user[/{username}/{uid}]', 'UserPanelController:getProfile')->setName('user.profile');
$app->post('/user[/{username}/{uid}]', 'UserPanelController:postProfilePicture')->setName('user.postPicture');
$app->post('/changedata', 'UserPanelController:postChangeData')->setName('user.postChangeData');

#userlist
$app->get('/userlist[/{page}]', 'UserlistController:getList')->setName('userlist');

#skin chang
#userlist
$app->get('/setskin/{skin}', 'SkinController:change');


#################
# ADMIN SECTION #
#################

$adm = $container->get('settings')['core']['admin'];

$app->get('/' .$adm , 'AdminHomeController:index')->setName('admin.home');

$app->get('/' . $adm . '/board', 'AdminBoardController:index')->setName('admin.boards');
$app->get('/' . $adm . '/board/edit[/{id}]', 'AdminBoardController:editBoard')->setName('admin.edit.board');
$app->post('/' . $adm . '/board/edit[/{id}]', 'AdminBoardController:editBoard');

$app->post('/' . $adm . '/board/order/post', 'AdminBoardController:orderPost')->setName('admin.board.order.post');
$app->post('/' . $adm . '/board/addCategory/post', 'AdminBoardController:addCategory')->setName('admin.add.category');
$app->post('/' . $adm . '/board/addBoard/post', 'AdminBoardController:addBoard')->setName('admin.add.board');

$app->get('/' . $adm .'/active', 'AdminHomeController:plugin');

$app->get('/' . $adm .'/skinslist[/[{page}]]', 'AdminSkinsController:skinsList')->setName('admin.skinlist');
$app->get('/' . $adm .'/addskin', 'AdminSkinsController:addSkin')->setName('admin.add.skin');
$app->post('/' . $adm .'/addskin', 'AdminSkinsController:addSkinPost')->setName('admin.add.skin.post');
$app->post('/' . $adm .'/delete/skin', 'AdminSkinsController:removeSkin')->setName('admin.delete.skin.post');
$app->post('/' . $adm .'/skin/reload/css/js', 'AdminSkinsController:reloadCssJs')->setName('admin.add.skin.reload');
$app->get('/' . $adm .'/settings', 'AdminSettingsController:index')->setName('admin.get.settings');
