<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Models\PlotsModel;
use Application\Models\PostsModel;
use Application\Models\PlotsReadModel;
use Application\Models\BoardsModel;
use Application\Models\UserModel;
use Application\Models\LikeitModel;
use Application\Models\ImagesModel;
use Application\Core\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

 /**
 * Plot controller
 * @package BOARDS Forum
 */

class PlotController extends Controller
{
    
	protected function lastPost($plotId)
	{
		$data = PostsModel::orderBy('created_at', 'desc')->where('plot_id', $plotId)->first()->toArray();
		if($data['created_at'])
			$data = strtotime($data['created_at']);
		else
			$data = 0;
			
		return $data;
	}
	
	protected function lastSeenPost($plotId)
	{
		if(isset($_SESSION['user'])){
			
			$data = PlotsReadModel::orderBy('timeline', 'desc')->where([
				['plot_id', $plotId],
				['user_id', $_SESSION['user']]
			])->first();
			isset($data->timeline) ? $data = $data->timeline : $data = 0;
				
			return $data;
		}
	}
	
	public function getPlot($request, $response, $arg)
	{
		if(isset($arg['page']) && $arg['page'] > 0)
        {
			$currentPage = $arg['page'];
        }
    	else
        {
        	$currentPage = 1;
        }
        
		$totalItems = PostsModel::where('plot_id', $arg['plot_id'])->count();
		$itemsPerPage = $this->settings['pagination']['plots'];
		
		$urlPattern = self::base_url().'/plot/'.$arg['plot'].'/'.$arg['plot_id'].'/(:num)';
		$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
	
		$this->view->getEnvironment()->addGlobal('paginator', $paginator);

		$data = PostsModel::where('posts.plot_id', $arg['plot_id'] )
				->skip(($paginator->getCurrentPage()-1)*$paginator->getItemsPerPage())
				->take($paginator->getItemsPerPage())
				->join('users', 'users.id', '=', 'posts.user_id')
				->leftJoin('images', 'users.avatar', '=', 'images.id')
				->select('posts.*', 'users.avatar', 'users.username', 'users.reputation', 'users.main_group', 'users.posts', 'users.plots', 'images._85')
				->get();	
		
		foreach($data as $k => $v)
		{
			$group = $this->group->getGroupDate($v->main_group, $v->username);
			$data[$k]->username_html = $group['username'];
			$data[$k]->group = $group['group'];
		}
		
		$plot = PlotsModel::find($arg['plot_id']);

		
		if(	$this->auth->check() 
			&& $paginator->getCurrentPage() == ceil($paginator->getTotalItems()/$paginator->getItemsPerPage()) 
			&& (self::lastPost($arg['plot_id']) > self::lastSeenPost($arg['plot_id'])))
		{
			PlotsReadModel::create([
			'plot_id' => $arg['plot_id'],
			'user_id' => $_SESSION['user'],
			'timeline' => time()
			]);
			
		}else{
			#	cookie for unlogged
		}
		
		if(!isset($_SESSION['view']) || $_SESSION['view'] !== $arg['plot_id'])
		{
			$_SESSION['view'] = $arg['plot_id'];
			$v = PlotsModel::find($arg['plot_id']);
			$v->increment('views');
			$v->save();
		}	
		$plot_data['plot_data'] = $data;	
		$plot_data['locked'] = $plot->locked;	
		$plot_data['title'] = $plot->plot_name;
		
		$this->view->getEnvironment()->addGlobal('title', $plot_data['title']);
		$this->view->getEnvironment()->addGlobal('locked', $plot_data['locked']);
		if($data) $this->view->getEnvironment()->addGlobal('plot', [
															'posts' => $plot_data['plot_data'], 
															'plot_id' => $arg['plot_id'],
															]);
			
		return $this->view->render($response, 'plot.twig');
	}
	
	public function replyPost($request, $response)
	{
	
		$plot = PlotsModel::select('locked', 'board_id')->find($request->getParsedBody()['plot_id']);
		$locked = $plot->locked;
		
		$data['csrf'] = self::csftToken();

		if($this->auth->check() && !$locked){
			
			$user =UserModel::find($this->auth->user()['id']);
			 ;
			PostsModel::create([
				'user_id' => $user->id,
				'plot_id' => $request->getParsedBody()['plot_id'],
				'content' => $request->getParsedBody()['content'],				
			]);
			
			if($user->avatar)			
				$avatar = ImagesModel::find($user->avatar);
			
			$avatar = $user->avatar ? self::base_url() .'/public/upload/avatars/'.$avatar->_85 : self::base_url() .'/public/img/avatar.png';	
			$uurl = self::base_url() .'/user/'. $this->urlMaker->toUrl($user->username) .'/'. $user->id;
			
			$data['response'] = file_get_contents(MAIN_DIR.'/skins/'.$this->settings['twig']['skin'].'/tpl/ajax/newpost.twig');
			$replace = [$uurl, $user->username, $avatar,  $user->user_grupe, $user->user_groupe, $user->posts, $user->plots, $user->created_at, $user->reputation, date("Y-m-d H:i:s"), ($request->getParsedBody()['content'])];
			$find = ['{{uurl}}', '{{username}}', '{{avatar}}', '{{user_grupe}}', '{{posts}}', '{{plots}}', '{{join}}', '{{rep}}', '{{date}}', '{{created_at}}', '{{content}}'];
			
			$data['response'] = str_replace($find, $replace, $data['response']);
			
			
			$user->posts++;
			$user->save();
			
			$user_html = $this->group->getGroupDate($user->main_group, $user->username)['username'];
			
			$board = BoardsModel::find($plot->board_id);
			$board->last_post_date = time();
			$board->last_post_author = $user_html;
			$board ->posts_number++;	
			$board->save();
			
			PlotsReadModel::create([
				'plot_id' => $request->getParsedBody()['plot_id'],
				'user_id' => $_SESSION['user'],
				'timeline' => time()
			]);			
		}
		else
		{
			$data['response'] = $this->translator->trans('lang.you have to been logged in');
		}
		
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);
	}
	
	public function newPlot($request, $response, $arg)
	{
		$this->view->getEnvironment()->addGlobal('board_id', $arg['board_id']);
		$board = BoardsModel::find($arg['board_id']);
		
		if($board->locked) return $this->view->render($response, 'locked.twig');
		
		return $this->view->render($response, 'newplot.twig');
	}
	
	public function newPlotPost($request, $response)
	{
		$data['csrf'] = self::csftToken(); 
					
		if($request->getParsedBody()['board_id'])
		{
			
			
			$board = BoardsModel::find($request->getParsedBody()['board_id']);
			
			if(!$board->locked && $request->getParsedBody()['content'] != '' &&  $request->getParsedBody()['topic'] != '') 
			{
				$newPlot = PlotsModel::create([
					'author_id' => $_SESSION['user'],
					'plot_name' => $request->getParsedBody()['topic'],
					'board_id' => $board->id,
					'plot_active' => 1,
					'pinned' => 0,
					'locked' => 0,
				]);
		
				$newPost = PostsModel::create([
					'user_id' => $_SESSION['user'],
					'plot_id' => $newPlot->id,
					'content' => $request->getParsedBody()['content'],
					'hidden' => 0
				]);
				
				$user =UserModel::find($this->auth->user()['id']);
				$user->posts++;
				$user->plots++;
				$user->save();
				
				$user_html = $this->group->getGroupDate($user->main_group, $user->username)['username'];
				
				$board->plots_number++;
				$board->posts_number++;
				$board->last_post_date = time();
				$board->last_post_author = $user_html;
				$board->save();
				
				$data['redirect'] = self::base_url() . '/plot/' . $this->urlMaker->toUrl($newPlot->plot_name) . '/' . $newPlot->id;					
			
			}
			else
			{
				$data['warn'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
								  <strong>'.$this->translator->trans('lang.post or topic cannot be empty').'</strong>
								  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								  </button>
								</div>';
				
				$this->translator->trans('lang.post or topic is empty');
			}
			
			$user = null;
			
		}
		$response->getBody()->write(json_encode($data));		
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);
		}
	
	
	public function reportPost($request, $response)
	{
		$data['csrf'] = self::csftToken();
		
		$postID = $request->getParsedBody()['post_id'];
		$reasonID = $request->getParsedBody()['reason_id'];
			
		$report = ReportsModel::create([
			'user_id' => isset($_SESSION['user']) ? intval($_SESSION['user']) : NULL,
			'post_id' => intval($postID),
			'reson_id' => intval($reasonID),
		]);
		$report ? $report = $this->translator->trans('lang.post has been reported to administration') : $this->translator->trans('lang.something went wrong');
		$data['report'] = ' <div id="overlay" style="display: none;""><div id="text" class=" alert alert-info fade show" role="alert">
								  <strong>'.$report.'</strong>
								</div></div>';
		
		$response->getBody()->write(json_encode($data));		
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);	
		
	}
	
	
	public function likeit($request, $response)
	{
		
		$data['csrf'] = self::csftToken();
		$postID = substr($request->getParsedBody()['post_id'], 5);
		$data['likeit'] = LikeitModel::where([
			['user_id', intval($_SESSION['user'])],
			['post_id', intval($postID)]
		])->first();
		if(!$data['likeit'])
		{
			
			
			$post = PostsModel::find($postID);
			
			if($post->user_id == $_SESSION['user']) 
			{
				$data['likeit'] = ' <div id="overlay" style="display: none;"><div id="text" class="alert alert-danger fade show" role="alert">
								  <strong>'.$this->translator->trans('lang.you cant like your own post').'</strong>
								</div></div>';	
				$response->getBody()->write(json_encode($data));		
				return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);	
			}
			
			$post->post_reputation++;
			$post->timestamps = false;
			$post->save();
			
			$user = UserModel::find($post->user_id);
			$user->reputation++;
			$user->save();

			
			LikeitModel::create([
				'user_id' => intval($_SESSION['user']),
				'post_id' => intval($postID)
			]);
			
			$data['likeit'] = ' <div id="overlay" style="display: none;"><div id="text" class="alert alert-success fade show" role="alert">
								  <strong>'.$this->translator->trans('lang.reputation added').'</strong>
								</div></div>';
			

		}
		else
		{
			$data['likeit'] = ' <div id="overlay" style="display: none;""><div id="text" class=" alert alert-danger fade show" role="alert">
								  <strong>'.$this->translator->trans('lang.you add reputation to this post').'</strong>
								</div></div>';
		}
			
		$response->getBody()->write(json_encode($data));		
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);	
	}
	
	
}