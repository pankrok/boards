<?php

namespace App\Controllers\User;

use App\Models\UserModel;
use App\Models\UserDataModel;
use App\Models\PostsModel;
use App\Controllers\Controller;


 /**
 * User Data controller
 * @package BOARDS Forum
 */

class UserDataController extends Controller
{
	
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
	
	public function getData($id)
	{
		if($this->cache->getCache()!= 'userdata')$this->cache->setCache('userdata');
		if(!$data = $this->cache->retrieve($id)){
			$data = UserModel::leftJoin('images', 'users.avatar', '=', 'images.id')
				->leftJoin('groups', 'groups.id', '=', 'users.user_group')
				->select('images._38','images._85','images._150', 'groups.name_html', 'groups.group_html', 'groups.group_name', 'users.*')
				->find($id);
			
			
			$data->name_html = str_replace('{@login@}', $data->username, $data->name_html);
			$data->group_name = str_replace('{@group@}', $data->group_name, $data->group_html);
			
			$this->cache->store($id, $data);
		}
		return $data;
	}
	
	public function getNameHtml($id)
	{
		$data = self::getData($id);
		return $data->name_html;
	}
	
	public function getAdditionalData($id)
	{
		if($this->cache->getCache()!= 'additional_userdata') $this->cache->setCache('additional_userdata');
		if(!$data = $this->cache->retrieve($id)){
			$data = UserDataModel::where('user_id', $id)->first();
			$this->cache->store($id, $data);
		}
		
		return $data;		
		
	}
	
	public function getPosts($id)
	{
		
		$data = PostsModel::leftJoin('plots', 'plots.id', 'posts.plot_id')
							->select('plots.plot_name', 'posts.*')
							->where('posts.user_id', $id)
							->orderBy('posts.id', 'desc')
							->take(20)
							->get();
		foreach($data as $k => $v)
		{
			$data[$k]['page'] = ceil (PostsModel::where([
			['created_at', '<=', $v->created_at],
			['plot_id', $v->plot_id]])->count()/10);
		}
		
		return $data;		
	}
	
	public function listUsers($page = 1)
	{
		
		$this->cache->setCache('userlist');
		if(!$paginator = $this->cache->retrieve('paginator'))
		{
			$totalItems = UserModel::count();
			$itemsPerPage = 20;
			$urlPattern = self::base_url().'/userlist/(:num)';
			$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $page, $urlPattern);
			$this->cache->store('paginator',$paginator);
		}
		else
		{
			$paginator->setCurrentPage($page);
		}
		$this->view->getEnvironment()->addGlobal('paginator', $paginator);
		
		if($this->cache->isCached('-page-'.$paginator->getCurrentPage()))
		{
			$data = $this->cache->retrieve('-page-'.$paginator->getCurrentPage());
		}
		else
		{
			$data = UserModel::select('id')
					->skip(
					($paginator->getCurrentPage()-1)*
						$paginator->getItemsPerPage())
					->take($paginator->getItemsPerPage())
					->orderBy('id', 'asc')
					->get();
			foreach($data as $k => $v)
			{
				$data[$k] = self::getData($v->id);
			}
			
			$this->cache->store('-page-'.$paginator->getCurrentPage(), $data);
		}
		
		return $data;
		
	}
	
	public function addReputation($id)
	{
		$data = UserModel::find($id);
		++$data->reputation;
		$data->save();
		if($this->cache->getCache()!= 'userdata')$this->cache->setCache('userdata');
		if($this->cache->isCached($id)) $this->cache->erase($id);
	}
}