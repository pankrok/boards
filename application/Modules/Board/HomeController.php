<?php

declare(strict_types=1);

namespace Application\Modules\Board;
use Application\Core\Controller as Controller;

class HomeController extends Controller
{
	
	public function index($request, $response, $arg)
	{
		$cache = $request->getAttribute('cache');
		if(!isset($cache))
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
			
			if($user['avatar'])
			{
				$user['avatar'] =  self::base_url() . '/public/upload/avatars/' . $user['_150'];
			}
			else
			{
				$user['avatar'] = self::base_url() . '/public/img/avatar.png';
			}
			
			$user['username'] = $username['username'];
			$user['group'] = $username['group'];
			
			$this->view->getEnvironment()->addGlobal('user', $user);
		}
		$this->view->getEnvironment()->addGlobal('sidebar_active', true);
		$this->view->getEnvironment()->addGlobal('sidebars', self::getBoxes());
		$this->view->getEnvironment()->addGlobal('stats', $this->StatisticController->getStats());
		$this->event->addGlobalEvent('home.loaded');	
		return $this->view->render($response, 'home.twig');	;
	
	}
	
	protected function getCategories()
	{
		
		$categories = \Application\Models\CategoryModel::orderBy('category_order', 'DESC')->get()->toArray(); 
		
		foreach($categories as $k => $v)
		{
			$categories[$k]['url'] = self::base_url() . '/category/' . $this->urlMaker->toUrl($v['name'])	. '/' . $v['id'];
		}
		
		return $categories;
		
	}	
	
	protected function getBoards()
	{
		$boards = [];
		$handler = \Application\Models\BoardsModel::orderBy('category_id')->orderBy('board_order', 'DESC')->get()->toArray();
		
		foreach($handler as $k => $v)
		{
			$lastpost = \Application\Models\PlotsModel::orderBy('updated_at', 'DESC')
													->where('board_id', '=', $v['id'])
													->leftJoin('users', 'users.id', 'plots.author_id')
													->leftJoin('images', 'images.id', 'users.avatar')
													->select('plots.*', 'users.username', 'images._38')
													->first();
			if(isset($lastpost))
			{				
				$lastpost->toArray();									
				if($lastpost['_38'])
				{
						$lastpost['_38'] =  self::base_url() . '/public/upload/avatars/' . $lastpost['_38'];
				}
				else
				{
					$lastpost['_38'] = self::base_url() . '/public/img/avatar.png';
				}

			}
			$boards[$v['category_id']][$v['id']] = $v;			
			$boards[$v['category_id']][$v['id']]['url'] = self::base_url() . '/board/' . $this->urlMaker->toUrl($v['board_name'])	. '/' . $v['id'];
			if(isset($lastpost))
			{
				$boards[$v['category_id']][$v['id']]['last_post'] = true;
				$boards[$v['category_id']][$v['id']]['last_post_url'] = self::base_url() . '/plot/' . $this->urlMaker->toUrl($lastpost['plot_name'])	. '/' . $lastpost['id'];
				$boards[$v['category_id']][$v['id']]['last_post_author_url'] = self::base_url() . '/user/' . $this->urlMaker->toUrl($lastpost['username'])	. '/' . $lastpost['author_id'];
				$boards[$v['category_id']][$v['id']]['last_post_avatar'] = $lastpost['_38'];
			}	
		
		}
		
		$boards['groups_legend'] = \Application\Models\GroupsModel::select('grupe_name')->get()->toArray();
		
		return $boards;
	}
	
	protected function getBoxes()
	{
		$boxes = \Application\Models\BoxModel::orderBy('box_order', 'desc')->get()->toArray();
		foreach($boxes as $k => $v)
		{
			if($v['translate'])
			{
				$boxes[$k]['name'] = $this->translator->trans('lang.'.$v['name']);				
			}
		}	
		 
		return $boxes;
	}
	
	public function session($request, $response)
	{
	
		//var_dump($_SESSION);
		return $response;
	
	}
};

