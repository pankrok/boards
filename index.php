<?php

/************
*			*
*  BOARDS 	*
*			*
************/

declare(strict_types=1);
ini_set( 'display_errors', 'on');
DEFINE('MAIN_DIR', __DIR__);

if(file_exists(__DIR__ . '/environment/Config/db_settings.php'))
{
	require MAIN_DIR . '/application/Core/Init.php';
	$app->run();
}
else
{
	header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
         $url = "https://";   
    else  
         $url = "http://";    
    $url.= $_SERVER['HTTP_HOST'];     
    $url.= $_SERVER['REQUEST_URI'] . '/install';   
	header("Location: $url");
}