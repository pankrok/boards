<?php

/************
*			*
*  BOARDS 	*
*			*
************/

declare(strict_types=1);
ini_set( 'display_errors', 'on');

$t = (microtime(true)); #remove from production!

DEFINE('MAIN_DIR', __DIR__);

require MAIN_DIR . '/application/Core/Init.php';
$app->run();


$mem = round((memory_get_usage()/1024/1024),2);#remove from production!
$dt = round((microtime(true) - $t) * 1000, 2);#remove from production!
//echo('<br /><font size="1" class="right">script execution time: ' . $dt .' | memory usage: '.$mem.' MB</font>');#remove from production!

