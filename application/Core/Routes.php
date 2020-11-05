<?php
declare(strict_types=1);

#home
$app->get(PREFIX.'/', 'HomeController:index')->setName('home');
$app->get(PREFIX.'/cron[/{key}]', 'CronController:main')->setName('cron');
$app->get(PREFIX.'/s', 'HomeController:session');

#category
$app->get(PREFIX.'/category/{category}/{category_id}[/{page}]', 'CategoryController:getCategory')->setName('category.getCategory');

#board
$app->get(PREFIX.'/board/{board}/{board_id}[/{page}]', 'BoardController:getBoard')->setName('board.getBoard');

#plot
$app->get(PREFIX.'/plot/{plot}/{plot_id}[/[{page}]]', 'PlotController:getPlot')->setName('board.getPlot');
$app->get(PREFIX.'/newplot/{board_id}', 'PlotController:newPlot')->setName('board.newPlot');
$app->post(PREFIX.'/newplot/post', 'PlotController:newPlotPost')->setName('board.newPlotPost');
$app->post(PREFIX.'/replyPost', 'PlotController:replyPost')->setName('board.replyPost');

#sign
$app->get(PREFIX.'/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
$app->post(PREFIX.'/auth/signin', 'AuthController:postSignIn');
$app->get(PREFIX.'/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
$app->post(PREFIX.'/auth/signup', 'AuthController:postSignUp');
$app->get(PREFIX.'/auth/logout', 'AuthController:getSignOut')->setName('auth.signout');

#chatbox
$app->post(PREFIX.'/chatbox/postmessage', 'ChatboxController:postChatMessage')->setName('postChatbox');
$app->post(PREFIX.'/chatbox/loadmore', 'ChatboxController:loadMoreMessages')->setName('loadChatbox');
$app->post(PREFIX.'/chatbox/checknew', 'ChatboxController:checkNewMessage')->setName('checkNewMessage');

#user
$app->get(PREFIX.'/user[/{username}/{uid}]', 'UserPanelController:getProfile')->setName('user.profile');
$app->post(PREFIX.'/user[/{username}/{uid}]', 'UserPanelController:postProfilePicture')->setName('user.postPicture');
$app->post(PREFIX.'/changedata', 'UserPanelController:postChangeData')->setName('user.postChangeData');




#################
# ADMIN SECTION #
#################

$app->get(PREFIX.'/' . $container->get('settings')['core']['admin'], 'AdminHomeController:index');

$app->get(PREFIX.'/' . $container->get('settings')['core']['admin'] . '/board', 'AdminBoardController:index')->setName('admin.boards');
$app->get(PREFIX.'/' . $container->get('settings')['core']['admin'] . '/board/edit[/{id}]', 'AdminBoardController:editBoard')->setName('admin.edit.board');
$app->post(PREFIX.'/' . $container->get('settings')['core']['admin'] . '/board/edit[/{id}]', 'AdminBoardController:editBoard');

$app->post(PREFIX.'/' . $container->get('settings')['core']['admin'] . '/board/order/post', 'AdminBoardController:orderPost')->setName('admin.board.order.post');
$app->post(PREFIX.'/' . $container->get('settings')['core']['admin'] . '/board/addCategory/post', 'AdminBoardController:addCategory')->setName('admin.add.category');
$app->post(PREFIX.'/' . $container->get('settings')['core']['admin'] . '/board/addBoard/post', 'AdminBoardController:addBoard')->setName('admin.add.board');
