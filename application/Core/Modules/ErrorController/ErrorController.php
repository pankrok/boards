<?php

declare(strict_types = 1);

namespace Application\Core\Modules\ErrorController;

use \Slim\Handlers\ErrorHandler;
use Monolog\Logger;

class ErrorController extends ErrorHandler
{
    protected $_logger;
	
	protected function logError(string $error): void
    {    
		$stream = $this->_logger;
		$formatter = new \Monolog\Formatter\LineFormatter(null, null, true);
		$stream->setFormatter($formatter);
		$logger = new Logger('front-error');
		$logger->pushHandler($stream);
		$logger->error("\n$error\n");
    }
	
	public function setLevel($level)
	{
		switch ($level) {
			case '0':
				$this->_logger = new \Monolog\Handler\StreamHandler(MAIN_DIR.'/environment/Logs/error.log.txt', Logger::ERROR);
				break;
			case '1':
				$this->_logger = new \Monolog\Handler\StreamHandler(MAIN_DIR.'/environment/Logs/notice.log.txt', Logger::NOTICE);
				break;
			case '2':
				$this->_logger = new \Monolog\Handler\StreamHandler(MAIN_DIR.'/environment/Logs/info.log.txt', Logger::INFO);
				break;
			case '3':
				$this->_logger = new \Monolog\Handler\StreamHandler(MAIN_DIR.'/environment/Logs/debug.log.txt', Logger::DEBUG);
				break;
			default:
				$this->_logger = new \Monolog\Handler\StreamHandler(MAIN_DIR.'/environment/Logs/critical.log.txt', Logger::CRITICAL);
		}
	}
	
}