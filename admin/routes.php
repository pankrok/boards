<?php

/* ADMIN PANEL */

$admin->get(PREFIX.ADMIN, 'AdminHomeController:index')->setName('admin.home');
$admin->get(PREFIX.ADMIN.'/configuration', 'AdminConfigurationController:settings')->setName('admin.settings');
$admin->get(PREFIX.ADMIN.'/configuration/settings', 'AdminConfigurationController:settings')->setName('admin.settings');

$admin->get(PREFIX.ADMIN.'/boards/managment', 'AdminBoardsController:managment')->setName('admin.managment');
$admin->post(PREFIX.ADMIN.'/boards/managment/boardorder', 'AdminBoardsController:boardorder')->setName('admin.ajax.border');

$admin->get(PREFIX.ADMIN.'/auth/signin', 'AdminAuthController:getSignIn')->setName('admin.auth.signin');
$admin->post(PREFIX.ADMIN.'/auth/signin', 'AdminAuthController:postSignIn');
$admin->get(PREFIX.ADMIN.'/auth/signout', 'AdminAuthController:getSignOut')->setName('admin.auth.signout');