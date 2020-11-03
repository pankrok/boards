<?php
namespace App\Controllers;

use \Slim\Handlers\ErrorHandler;
use Monolog\Logger;

class ErrorController extends ErrorHandler
{
    protected function logError(string $error): void
    {    
		$stream = (new  \Monolog\Handler\StreamHandler(MAIN_DIR.'/admin/logs/front-error.log', Logger::DEBUG));
		$formatter = new \Monolog\Formatter\LineFormatter(null, null, true);
		$stream->setFormatter($formatter);
		$logger = new Logger('front-error');
		$logger->pushHandler($stream);
		$logger->error("\n$error\n");
    }
	
}