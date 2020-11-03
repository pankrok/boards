<?php
namespace App\Controllers\Board;

use App\Models\PostsModel;
use App\Models\PlotsModel;
use App\Models\BoardsModel;
use App\Models\UserModel;
use App\Controllers\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

 /**
 * Plot controller
 * @package BOARDS Forum
 */

class StatisticController extends Controller
{

		public function posts()
		{
			if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');
			$this->cache->eraseExpired();
			if($data = $this->cache->retrieve('posts'))
			{
				return $data;
			}
			else
			{
				$data = PostsModel::count();
				$this->cache->store('posts', $data, 3600);
				return $data;
			}
		}
		
		public function plots()
		{
			if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');
			$this->cache->eraseExpired();
			if($data = $this->cache->retrieve('plots'))
			{
				return $data;
			}
			else
			{
				$data = PlotsModel::count();
				$this->cache->store('plots', $data, 3600);
				return $data;
			}
		}
		
		public function users()
		{
			if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');
			$this->cache->eraseExpired();
			if($data = $this->cache->retrieve('users'))
			{
				return $data;
			}
			else
			{
				$data = UserModel::count();
				$this->cache->store('users', $data, 3600);
				return $data;
			}
		}
		
		public function online()
		{
			if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');
			$this->cache->eraseExpired();
			if($data = $this->cache->retrieve('online'))
			{
				return $data;
			}
			else
			{
				$data = UserModel::where('last_active', '>', (time()-300))->select('id', 'username')->get();
				foreach($data as $k => $v)
				{
					$data[$k]->username_html = $this->userdata->getNameHtml($v->id);
				}
				$this->cache->store('online', $data, 300);
				return $data;
			}
		}
		
		public function lastUser()
		{
			if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');
			if($data = $this->cache->retrieve('lastUser'))
			{
				return $data;
	
			}
			else
			{
				$us = UserModel::orderBy('created_at', 'desc')->select('id')->first();
				$data = $this->userdata->getData($us->id);
				$this->cache->store('lastUser', $data);
				return $data;
			}
		}
		
		public function boardStats($id)
		{
			if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');			
			if($data = $this->cache->retrieve('boardStats'.$id))
			{
				return $data;
			}
			else
			{
				$data['posts'] = null;
				$data['plots'] = null;
				$data['last'] = null;
				$data['pages'] = null;
				$data['read'] = 'read';
				
				$childboards = BoardsModel::where('parent_id', $id)->get(); 
				$i = 1;
				$cb[0] = $id;
				foreach($childboards as $v)
				{
					$cb[$i] = $v->id;
					$i++;
				}
				$data['plots'] = PlotsModel::whereIn('board_id', $cb)->select('id')->get();
				
				foreach($data['plots'] as $k => $val)
				{
					$data['posts'] =  $data['posts'] + PostsModel::where('plot_id', $val->id)->count();
					$posts = PostsModel::leftJoin('plots', 'posts.plot_id', 'plots.id')
						->orderBy('posts.created_at', 'desc')
						->where('posts.plot_id', $val->id)
						->select('posts.id', 'plots.plot_name', 'posts.user_id', 'posts.plot_id', 'posts.created_at')
						->get();

					$last[$k]['model'] = $posts->first();
					$last[$k]['count'] = ceil($posts->count() / 10);
					
					if($k == 0) {
						(strtotime($last[$k]['model']->created_at) > $this->PlotController->lastSeenPost($val->id)) ? $data['read'] = 'unread' : $data['read'] = 'read';
						$data['last'] = $last[$k]['model'];
						$data['pages'] = $last[$k]['count'];
					}	
					if($k > 0 && strtotime($data['last']->created_at) < strtotime($last[$k]['model']->created_at)) 
					{
						(strtotime($last[$k]['model']->created_at) > $this->PlotController->lastSeenPost($val->id)) ? $data['read'] = 'unread' : $data['read'] = 'read';
						$data['last'] = $last[$k]['model'];
						$data['pages'] = $last[$k]['count'];
					}
					
					
				}
				
				if(!$data['posts']) $data['posts'] = '0';
				$data['plots'] = $data['plots']->count();
				if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');	
				$this->cache->store('boardStats'.$id, $data);
				
				return $data;
			}
		}
		
		
		
		public function plotStats($id)
		{
			if($this->cache->getCache() != 'Statistics') $this->cache->setCache('Statistics');			
			if($data = $this->cache->retrieve('plotStats'.$id))
			{
				return $data;
			}
			else
			{
				$data['last'] = null;
				$data['count'] = null;
				
				$posts = PostsModel::where('plot_id', $id)
					->orderBy('posts.created_at', 'desc')
					->select('posts.id', 'posts.user_id', 'posts.created_at')
					->get();
					
				$user = $this->userdata->getData($posts->first()->user_id);
				
				$data['lastPost'] = $posts->first();
				$data['lastUser'] = $user;
				$data['replies'] = $posts->count();

				return $data;
			}
		}
	
}