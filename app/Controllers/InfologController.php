<?php
namespace App\Controllers;

use Monolog\Logger;

class InfologController
{

	protected $logger;	

	
	public function __construct()
	{
		$stream = (new  \Monolog\Handler\StreamHandler(MAIN_DIR.'/admin/logs/front-info.log.txt', Logger::ERROR));
		$formatter = new \Monolog\Formatter\LineFormatter(null, null, true);
		$stream->setFormatter($formatter);
		$logger = new Logger('front-info');
		$logger->pushHandler($stream);
		$this->logger = $logger;
	}
	
	public function info($info)
	{
		$this->logger->info("$info");		
	}

	public function warn($info)
	{
		$this->logger->warning("$info");		
	} 
	
	public function error($info)
	{
		$this->logger->warning("$info");		
	} 
	
}