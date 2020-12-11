<?php

declare(strict_types=1);

namespace Application\Modules\Board;
use Application\Core\Controller as Controller;
use Application\Models\UserModel;

class UserlistController extends Controller
{
	
	public function getList($request, $response, $arg)
	{
		$page = $arg['page'] ?? 1;
		$data = $request->getAttribute('cache');

		if(!isset($data))
		{
			$routeName = \Slim\Routing\RouteContext::fromRequest($request)->getRoutingResults()->getUri();
			$data = self::getUsers($page);
			$this->cache->store($routeName, $data, $this->settings['cache']['cache_time']);
		}
		
		
		$this->view->getEnvironment()->addGlobal('users',$data['users']);
		$this->view->getEnvironment()->addGlobal('paginator',$data['paginator']);
		$this->view->getEnvironment()->addGlobal('title', $this->translator->trans('lang.userlist'));
		
		return $this->view->render($response, 'userlist.twig');	;
	}
	
	
	protected function getUsers($currentPage)
	{
			
		$totalItems = UserModel::count();
		$itemsPerPage = $this->settings['pagination']['users'];		
		$urlPattern = self::base_url().'/userlist/(:num)';

		$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);	
		
		$users = UserModel::skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
				->take($paginator->getItemsPerPage())
				->get()->toArray();
				
		return ['users' => $users, 'paginator' => $paginator];
	}
}

