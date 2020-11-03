<?php
#translate
$app->get(PREFIX.'/translate/{lang}', TranslationController::class .':switch')->setName('lang.switch');
#homepage
$app->get(PREFIX.'/', 'HomeController:index')->setName('home');
$app->get(PREFIX.'/userlist[{page}]', 'HomeController:userlist')->setName('userlist');
#auth pages
$app->get(PREFIX.'/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
$app->post(PREFIX.'/auth/signin', 'AuthController:postSignIn');
$app->get(PREFIX.'/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
$app->post(PREFIX.'/auth/signup', 'AuthController:postSignUp');
$app->post(PREFIX.'/auth/captcha', 'AuthController:reloadCaptcha')->setName('auth.captcha');
$app->get(PREFIX.'/auth/forgot-password', 'AuthController:getForgotPassword')->setName('auth.forgotPassword');
$app->post(PREFIX.'/auth/forgot-password', 'AuthController:postForgotPassword');
$app->post(PREFIX.'/auth/hint', 'AuthController:postHintUsers')->setName('auth.hint');
$app->get(PREFIX.'/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');
#userpanel
$app->get(PREFIX.'/user[/{username}/{uid}]', 'UserPanelController:getProfile')->setName('user.profile');
$app->post(PREFIX.'/user[/{username}/{uid}]', 'UserPanelController:postProfilePicture')->setName('user.postPicture');
$app->post(PREFIX.'/changedata', 'UserPanelController:postChangeData')->setName('user.postChangeData');

#chatbox
$app->post(PREFIX.'/chatbox/postmessage', 'ChatboxController:postChatMessage')->setName('postChatbox');
$app->post(PREFIX.'/chatbox/loadmore', 'ChatboxController:loadMoreMessages')->setName('loadChatbox');
$app->post(PREFIX.'/chatbox/checknew', 'ChatboxController:checkNewMessage')->setName('checkNewMessage');

#category
$app->get(PREFIX.'/category/{category}/{category_id}[/{page}]', 'BoardController:getBoard')->setName('board.getBoard');
#board
$app->get(PREFIX.'/board/{board}/{board_id}[/{page}]', 'BoardController:getBoard')->setName('board.getBoard');
#topic
$app->get(PREFIX.'/plot/{plot}/{plot_id}[/{page}]', 'PlotController:getPlot')->setName('board.getPlot');
$app->post(PREFIX.'/replyPost', 'PlotController:replyPost')->setName('board.replyPost');
$app->post(PREFIX.'/likePost', 'PlotController:likeit')->setName('board.likeit');
$app->get(PREFIX.'/newplot/{board_id}', 'PlotController:newPlot')->setName('board.newPlot');
$app->post(PREFIX.'/newplot/post', 'PlotController:newPlotPost')->setName('board.newPlotPost');
$app->post(PREFIX.'/report', 'PlotController:reportPost')->setName('board.raport.post');
#admin edit
$app->post(PREFIX.'/edit/admin/plot', 'AdminEditPost:editPost')->setName('admin.editpost');

#####debug#####
$app->get(PREFIX.'/console/debug/phpinfo', 'HomeController:info');
$app->get(PREFIX.'/test', 'HomeController:test')->setName('test');
$app->post(PREFIX.'/ctest', 'HomeController:captcha')->setName('ctest');


$app->get(PREFIX.'/captcha', 'test:index')->setName('ctest');
$app->post(PREFIX.'/captcha', 'test:captcha')->setName('ctest');
