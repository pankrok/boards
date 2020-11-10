<?php

declare(strict_types=1);

namespace Application\Modules\Home;
use Application\Core\Controller as Controller;

class HomeController extends Controller
{
	
	public function index($request, $response, $arg)
	{
		$cache = $request->getAttribute('cache');
		
		if(!$cache)
		{
			$routeName = \Slim\Routing\RouteContext::fromRequest($request)->getRoutingResults()->getUri();
			$categories = self::getCategories();
			$boards = self::getBoards();
			$this->cache->store($routeName, ['categories' => $categories, 'boards' => $boards], $this->settings['cache']['cache_time']);
		}
		else
		{
			$categories = $cache['categories'];
			$boards 	= $cache['boards'];
		}
		
		$this->ChatboxController->getChatMesasages();
		
		$this->view->getEnvironment()->addGlobal('categories', $categories);
		$this->view->getEnvironment()->addGlobal('boards', $boards);
		
		if($this->auth->check())
		{
			$username = $this->group->getGroupDate($this->auth->user()['main_group'], $this->auth->user()['username']);
			$user = $this->auth->user();
			
			$user['username'] = $username['username'];
			$user['group'] = $username['group'];
			
			$this->view->getEnvironment()->addGlobal('user', $user);
		}
		
		
		$this->event->addGlobalEvent('home.loaded');	
		return $this->view->render($response, 'home.twig');	;
	
	}
	
	protected function getCategories()
	{
		
		$categories = \Application\Models\CategoryModel::orderBy('category_order', 'DESC')->get()->toArray(); 
		return $categories;
		
	}	
	
	protected function getBoards()
	{
		$boards = [];
		$handler = \Application\Models\BoardsModel::orderBy('category_id')->orderBy('board_order', 'DESC')->get()->toArray();
		
		foreach($handler as $k => $v)
		{
		
			$boards[$v['category_id']][$v['id']] = $v;
		
		}
		return $boards;
	}
	
	public function session($request, $response)
	{
	
		var_dump($_SESSION);
		return $response;
	
	}
};

