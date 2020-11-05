<?php

declare(strict_types=1);

namespace Application\Modules\CronJobs
;
use Application\Core\Controller as Controller;

class CronController extends Controller
{
	
	public function main($request, $response)
	{
		
		try
		{
			self::cacheCron();
		}
		catch (Exception $e)
		{
			echo 'ERR CODE: '.$e->getCode().', MESSAGE:<br />
			'.$e->getMessage();
		}
		
		return $response;
	}
	
	protected function cacheCron()
	{
		
		return $this->cache->deleteExpired();
	
	}
};

