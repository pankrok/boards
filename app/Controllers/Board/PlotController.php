<?php
namespace App\Controllers\Board;

use App\Models\PostsModel;
use App\Models\PlotsModel;
use App\Models\PlotsReadModel;
use App\Models\BoardsModel;
use App\Models\LikeitModel;
use App\Models\ReportReasonsModel;
use App\Controllers\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

 /**
 * Plot controller
 * @package BOARDS Forum
 */

class PlotController extends Controller
{
    
	private function lastPost($plotId)
	{
		$this->cache->setCache('plot-'.$plotId);
		if(!$data = $this->cache->retrieve('-last-post'))
		{
			$data = PostsModel::orderBy('created_at', 'desc')->where('plot_id', $plotId)->first();
			$data = strtotime($data->created_at);
			$this->cache->store('-last-post', $data);
		}		
		return $data;
	}
	
	public function lastSeenPost($plotId)
	{
		if(isset($_SESSION['user'])){
			$this->cache->setCache('plot-'.$plotId);
			if(!$data = $this->cache->retrieve('-lastSeen-'.$_SESSION['user']))
			{
				$data = PlotsReadModel::orderBy('timeline', 'desc')->where([
					['plot_id', $plotId],
					['user_id', $_SESSION['user']]
				])->first();
				isset($data->timeline) ? $data = $data->timeline : $data = 0;
				$this->cache->store('-lastSeen-'.$_SESSION['user'], $data);
			}		
			return $data;
		}
	}
	
	private function csftToken()
	{
		$token = ($this->container->get('csrf')->generateToken());
		return [
			'csrf_name' => $token['csrf_name'],
			'csrf_value' => $token['csrf_value']
		];
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
	
	public function getPlot($request, $response, $arg)
	{
		$this->cache->setCache('plot-'.$arg['plot_id']);
		$currentPage = (isset($arg['page']) ? $arg['page'] : 1);
		
		if(!$paginator = $this->cache->retrieve('paginator'))
		{
			$totalItems = PostsModel::where('plot_id', $arg['plot_id'])->count();
			$itemsPerPage = 10;
			
			$urlPattern = self::base_url().'/plot/'.$arg['plot'].'/'.$arg['plot_id'].'/(:num)';
			$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
			$this->cache->store('paginator',$paginator);	
		}
		else
		{
			$paginator->setCurrentPage($currentPage);
		}
		$this->view->getEnvironment()->addGlobal('paginator', $paginator);
		
		if($this->cache->isCached('-page-'.$paginator->getCurrentPage()))
		{
			$data = $this->cache->retrieve('-page-'.$paginator->getCurrentPage());
		}
		else
		{
			$data = PostsModel::where('posts.plot_id', $arg['plot_id'] )
					->skip(($paginator->getCurrentPage()-1)*$paginator->getItemsPerPage())
					->take($paginator->getItemsPerPage())
					->get();
			$this->cache->store('-page-'.$paginator->getCurrentPage(), $data);
		}
		
		if($data) $this->view->getEnvironment()->addGlobal('plot', [
															'posts' => $data, 
															'plot_id' => $arg['plot_id'],
															]);
			
		//canonical
		$this->cache->setCache('plot-'.$arg['plot_id']);
		$this->cache->eraseExpired();
		if($this->cache->isCached('-canonical-'.$paginator->getCurrentPage()))
		{
			$canonical = $this->cache->retrieve('-canonical-'.$paginator->getCurrentPage());
			$title = $this->cache->retrieve('-title-'.$paginator->getCurrentPage());	
		}
		else
		{
			$plot = PlotsModel::select('id', 'plot_name')->find($arg['plot_id']);
			$canonical = self::base_url() .'/plot/'. $this->urlMaker->toUrl($plot->plot_name) .'/' . $plot->id . '/' . intval($paginator->getCurrentPage()*1);
			$paginator->getCurrentPage() > 1 ? $title =  $plot->plot_name . ' ' . $this->translator->trans('lang.page') . ' ' . $paginator->getCurrentPage() : $title =  $plot->plot_name;
			$this->cache->setCache('plot-'.$arg['plot_id']);
			$this->cache->store('-canonical-'.$paginator->getCurrentPage(), $canonical);
			$this->cache->store('-title-'.$paginator->getCurrentPage(), $title);
		}
		
		if($this->cache->isCached('locked'))
		{
			$locked = $this->cache->retrieve('locked');	
		}
		else
		{
			$plot = PlotsModel::select('locked')->find($arg['plot_id']);
			$locked = $plot->locked;
			$this->cache->store('locked', $locked);
		}
		
		$this->view->getEnvironment()->addGlobal('canonical', $canonical);
		//END canonical		
		$this->view->getEnvironment()->addGlobal('title', $title);
		$this->view->getEnvironment()->addGlobal('locked', $locked);
		
		if(	$this->auth->check() 
			&& $paginator->getCurrentPage() == ceil($paginator->getTotalItems()/$paginator->getItemsPerPage()) 
			&& (self::lastPost($arg['plot_id']) > self::lastSeenPost($arg['plot_id'])))
		{
			PlotsReadModel::create([
			'plot_id' => $arg['plot_id'],
			'user_id' => $_SESSION['user'],
			'timeline' => time()
			]);
			

			$this->cache->setCache('plot-'.$arg['plot_id']);
			if($this->cache->isCached('active')) $this->cache->erase('-lastSeen-'.$_SESSION['user']);

		}else{
			#	cookie for unlogged
		}
		
		if(!isset($_SESSION['view']) || $_SESSION['view'] !== $arg['plot_id'])
		{
			$_SESSION['view'] = $arg['plot_id'];
			$data = PlotsModel::find($arg['plot_id']);
			$data->increment('views');
			$data->save();
		}	
		
		/* reasons of reports */
		$this->cache->setCache('reports');
		if($this->cache->isCached('reasons'))
		{
			$reasons = $this->cache->retrieve('reasons');	
		}
		else
		{
			$reasons = ReportReasonsModel::get();
			$this->cache->store('reasons', $reasons);
		}
		$this->view->getEnvironment()->addGlobal('reasons', $reasons);
		
		return $this->view->render($response, 'plot.twig');
	}
	
	public function replyPost($request, $response)
	{
		
		if($this->cache->isCached('locked'))
		{
			$locked = $this->cache->retrieve('locked');	
		}
		else
		{
			$plot = PlotsModel::select('locked')->find($request->getParsedBody()['plot_id']);
			$locked = $plot->locked;
			$this->cache->store('locked', $locked);
		}
		
		
		$this->cache->setCache('plot-'.$request->getParsedBody()['plot_id']);
		$paginator = $this->cache->retrieve('paginator');
		if($this->cache->isCached('-page-'.ceil($paginator->getTotalItems()/$paginator->getItemsPerPage()))) $this->cache->erase('-page-'.ceil($paginator->getTotalItems()/$paginator->getItemsPerPage()));
		if($this->cache->isCached('paginator')) $this->cache->erase('paginator');
		
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
			
			$data['response'] = file_get_contents(MAIN_DIR.'/skins/bluehaze/ajax/newpost.twig');
			$replace = [$uurl, $user->username, $avatar,  $user->user_grupe, $user->user_groupe, $user->posts, $user->plots, $user->created_at, $user->reputation, date("Y-m-d H:i:s"), ($request->getParsedBody()['content'])];
			$find = ['{@uurl@}', '{@username@}', '{@avatar@}', '{@user_grupe@}', '{@posts@}', '{@plots@}', '{@join@}', '{@rep@}', '{@date@}', '{@created_at@}', '{@content@}'];
			
			$data['response'] = str_replace($find, $replace, $data['response']);
			
			$this->cache->setCache($user->id);
			if($this->cache->isCached('activity'))$this->cache->erase('activity');
			$user->posts = $user->posts+1;
			$user->save();
			
			$boardID = PlotsModel::select('board_id')->find('board_id' ,$request->getParsedBody()['plot_id']);
			$this->cache->setCache('Statistics');
			if($this->cache->isCached('boardStats'.$boardID)) $this->cache->erase('boardStats'.$boardID);
			
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
				$this->cache->setCache('board-'.$board->id);
				$paginator = $this->cache->retrieve('paginator');
				if($this->cache->isCached('board-'.$paginator->getNumPages())) $this->cache->erase('board-'.$paginator->getNumPages());
				if($this->cache->isCached('paginator')) $this->cache->erase('paginator');			
			
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
			
			++$post->reputation;
			$post->save();
			
			$this->userdata->addReputation($post->user_id);
			
			LikeitModel::create([
				'user_id' => intval($_SESSION['user']),
				'post_id' => intval($postID)
			]);
			
			$data['likeit'] = ' <div id="overlay" style="display: none;"><div id="text" class="alert alert-success fade show" role="alert">
								  <strong>'.$this->translator->trans('lang.reputation added').'</strong>
								</div></div>';
			$this->cache->eraseAll();					

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