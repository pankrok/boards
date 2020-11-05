<?php
namespace Application\Modules\Board;

use Application\Models\PlotsModel;
use Application\Models\PostsModel;
use Application\Models\PlotsReadModel;
use Application\Models\BoardsModel;
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
		$data = PostsModel::orderBy('created_at', 'desc')->where('plot_id', $plotId)->first();
		$data = strtotime($data->created_at);
			
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
	
	protected function csftToken()
	{
		$token = ($this->container->get('csrf')->generateToken());
		return [
			'csrf_name' => $token['csrf_name'],
			'csrf_value' => $token['csrf_value']
		];
	}
	
	protected function base_url()
	{
		if(isset($_SERVER['HTTPS'])){
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
			$protocol = 'http';
		}
		return $protocol . "://" . $_SERVER['HTTP_HOST'] . PREFIX;
	}
	
	public function getPlot($request, $response, $arg)
	{
		
		$currentPage = $arg['page'];
		
		$totalItems = PostsModel::where('plot_id', $arg['plot_id'])->count();
		$itemsPerPage = $this->settings['pagination']['plots'];
		
		$urlPattern = self::base_url().'/plot/'.$arg['plot'].'/'.$arg['plot_id'].'/(:num)';
		$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
	
		$this->view->getEnvironment()->addGlobal('paginator', $paginator);

		$data = PostsModel::where('posts.plot_id', $arg['plot_id'] )
				->skip(($paginator->getCurrentPage()-1)*$paginator->getItemsPerPage())
				->take($paginator->getItemsPerPage())
				->join('users', 'users.id', '=', 'posts.user_id')
				->select('posts.*', 'users.avatar', 'users.username', 'users.user_group', 'users.posts', 'users.plots')
				->get();
			
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
	
		$plot = PlotsModel::select('locked')->find($request->getParsedBody()['plot_id']);
		$locked = $plot->locked;
		
		$data['csrf'] = self::csftToken();

		if($this->auth->check() && !$locked){
			$user = $this->auth->user();
			PostsModel::create([
				'user_id' => $user->id,
				'plot_id' => $request->getParsedBody()['plot_id'],
				'content' => $request->getParsedBody()['content'],				
			]);
			$avatar = $user->avatar ? self::base_url() .'/cache/img/'.$user->_85 : self::base_url() .'/public/img/avatar.png';
			$uurl = self::base_url() .'/user/'. $this->urlMaker->toUrl($user->username) .'/'. $user->user_id;
			
			$data['response'] = file_get_contents(MAIN_DIR.'/skins/'.$this->settings['twig']['skin'].'/tpl/ajax/newpost.twig');
			$replace = [$uurl, $user->username, $avatar,  $user->user_grupe, $user->user_groupe, $user->posts, $user->plots, $user->created_at, $user->reputation, date("Y-m-d H:i:s"), ($request->getParsedBody()['content'])];
			$find = ['{@uurl@}', '{@username@}', '{@avatar@}', '{@user_grupe@}', '{@posts@}', '{@plots@}', '{@join@}', '{@rep@}', '{@date@}', '{@created_at@}', '{@content@}'];
			
			$data['response'] = str_replace($find, $replace, $data['response']);
			
			
			$user->posts = $user->posts+1;
			$user->save();
			
			$boardID = PlotsModel::select('board_id')->find('board_id' ,$request->getParsedBody()['plot_id']);
						
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
}