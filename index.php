<?php

/************
*			*
*  BOARDS 	*
*			*
************/

	ini_set( 'display_errors', 'on');
	
    DEFINE('MAIN_DIR', __DIR__);
	DEFINE('PREFIX', '');
	DEFINE('ADMIN', '/panel');
	
    if(substr($_SERVER['REQUEST_URI'], strlen(PREFIX), strlen(ADMIN)) != ADMIN)
	{
		require MAIN_DIR . '/bootstrap/app.php';
		$app->run();
	}
	else
	{
		require MAIN_DIR . '/bootstrap/admin.php';
		$admin->run();
	}
