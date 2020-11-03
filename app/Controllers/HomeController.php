<?php

namespace App\Controllers;

use App\Models\BoardsModel;
use App\Models\PostsModel;
use App\Models\PlotsModel;
use App\Models\UsersModel;
use App\Models\GroupsModel;
use App\Models\CategoryModel;
use App\Models\ImagesModel;
use Slim\Views\Twig as View;

/**
* HomeController for main page
**/

class HomeController extends Controller
{
    /**
	* Index Page Controller 
	* 
	* @param object $request 
	* @param object $response 
	* @return twig
	**/
		
	public function index($request, $response)
    {
		
		$this->cache->setCache('categories');
		
		if($this->cache->isCached('categories'))
		{
			$data = $this->cache->retrieve('categories');
		}
		else
		{
			$categories = CategoryModel::select('category_name', 'id', 'category_order')
								->where('category_active', '1')
								->orderby('category_order', 'asc')
								->get();
								
								
			foreach($categories as $key => $v)
			{
				$boards = BoardsModel::select('id', 'board_name', 'board_order', 'board_description', 'parent_id')
									->where([
										['board_active', '=', '1'],
										['category_id', '=', $v->id]])
									->orderby('board_order', 'asc')
									->get();
								
				foreach($boards as $k => $board)
				{	
					
					$stats = $this->stats->boardStats($board->id); 
					$boards[$k]['posts_no'] = $stats['posts'];
					$boards[$k]['plots_no'] = $stats['plots'];	
					$boards[$k]['last'] 	= $stats['last'];
					$boards[$k]['endpage']	= $stats['pages'];
					$boards[$k]['childboard'] = BoardsModel::select('id', 'board_name', 'board_order', 'board_description', 'parent_id')
									->where([
										['board_active', '=', '1'],
										['parent_id', '=', $board->id]])
										->orderby('board_order', 'asc')
										->get();
										
					$this->auth->check() ? $boards[$k]['read'] = $stats['read'] : $boards[$k]['read'] = '';
				}
			
				$data[$key] = [					
					'name' =>$v->category_name,
					'cat_id' => $v->id,	
					'boards' =>  $boards,	
					];
			}
			$this->cache->store('categories', $data);			
		}
		if(!$groups = $this->cache->retrieve('groups-legend'))
		{
			$groups = GroupsModel::get();
			foreach($groups as $k => $v)
			{
				$groups[$k]->group_name = str_replace('{@group@}', $v->group_name, $v->group_html);
			}
			$this->cache->store('groups-legend', $groups);
		}
		
		
		$stats = [
			'posts' => $this->stats->posts(),
			'plots' => $this->stats->plots(),
			'users' => $this->stats->users(),
			'online' => $this->stats->online(),
			'lastUser' => $this->stats->lastUser()
		]; 
		$this->view->getEnvironment()->addGlobal('title', 'Sieć serwerów S89.EU');
		$this->view->getEnvironment()->addGlobal('stats', $stats);
		$this->view->getEnvironment()->addGlobal('categories', $data);
		$this->view->getEnvironment()->addGlobal('canonical', self:: base_url());
		$this->view->getEnvironment()->addGlobal('groups_legend', $groups);
		
		$this->event->addGlobalEvent('home.loaded');
		
		$this->ChatboxController->getChatMesasages();
		return $this->view->render($response, 'home.twig');
    }
	
	public function userlist($request, $response, $arg)
	{
		$page = (isset($arg['page']) ? $arg['page'] : 1);
		$users = $this->userdata->listUsers($page);
		$this->view->getEnvironment()->addGlobal('title', 'Użytkownicy sieci serwerów S89.EU');
		$this->view->getEnvironment()->addGlobal('users', $users);
		return $this->view->render($response, 'userlist.twig');	
	}
	
	public function test ($request, $response)
    {
		$this->cache->eraseAll();
		$this->event->addGlobalEvent('home.loaded.test');

		return $response;
	}
	
	public function captcha($request, $response)
	{
		$data =  $_SESSION['captcha'];
		unset($_SESSION['captcha']);
		var_dump($data);
		
		if ($_POST['user_captcha_code'] === $data) {
			echo 'Code is valid!';
		} else {
			echo 'Code is invalid!';
		}
		
		return $response;
	}	
	
	public function info($request, $response){
		phpinfo();
		return $response;
	}
	
	private function base_url()
	{
		if(isset($_SERVER['HTTPS'])){
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
			$protocol = 'http';
		}
		return $protocol . "://" . $_SERVER['HTTP_HOST'] . PREFIX;
	}
}