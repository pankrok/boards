<?php
namespace App\Controllers\Board;

use App\Models\PlotsModel;
use App\Models\BoardsModel;
use App\Controllers\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

 /**
 * Plot controller
 * @package BOARDS Forum
 */

class BoardController extends Controller
{
    
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
	
	public function getBoard($request, $response, $arg)
	{
		
		if(isset($arg['board_id']) && is_numeric($arg['board_id']))
		{
			$this->cache->setCache('board-'.$arg['board_id']);
			$currentPage = (isset($arg['page']) ? $arg['page'] : 1);
			if(!$paginator = $this->cache->retrieve('paginator')){
				$totalItems = PlotsModel::where('board_id', $arg['board_id'])->count();
				$itemsPerPage = 10;		
				$urlPattern = self::base_url().'/board/'.$arg['board'].'/'.$arg['board_id'].'/(:num)';
		
				$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
				$this->cache->store('paginator', $paginator);	
			}
			else
			{
				$paginator->setCurrentPage($currentPage);
			}
			$this->view->getEnvironment()->addGlobal('paginator', $paginator);
				
			if($this->cache->isCached('-'.$paginator->getCurrentPage()))
			{
				$data = $this->cache->retrieve('-'.$paginator->getCurrentPage());	
			}
			else
			{
				$data = PlotsModel::where([
							['plot_active', '=', '1'],
							['board_id', '=', $arg['board_id']]])
						->skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
						->take($paginator->getItemsPerPage())
						->get();
				
				
				foreach($data as $k => $v)
				{
					$posts = $this->stats->plotStats($v->id);
					
					$data[$k]['replies'] = $posts['replies'];
					$data[$k]['last_url'] = self::base_url() . '/user/' .$posts['lastUser']->username . '/' . $posts['lastPost']->user_id;
					$data[$k]['last_user_html'] = $posts['lastUser']->name_html;
					$data[$k]['last_uid'] = $posts['lastUser']->id;
					$data[$k]['last_ca'] = $posts['lastPost']->created_at;
					$data[$k]['lastPage'] = ceil($posts['replies']/10);
					$data[$k]['created_by'] = $this->userdata->getNameHtml($v->author_id);
				}
				$this->cache->setCache('board-'.$arg['board_id']);		
				$this->cache->store('-'.$paginator->getCurrentPage(), $data);
				
			}
			if($data) $this->view->getEnvironment()->addGlobal('board', [
																'plots' => $data, 
																'board_id' => $arg['board_id'],
																'board_url' => $arg['board'],
																]);
			
			#canonical
			if($this->cache->isCached('-canonical-'.$paginator->getCurrentPage()))
			{
				$this->cache->setCache('board-'.$arg['board_id']);	
				$canonical = $this->cache->retrieve('-canonical-'.$arg['board_id']);
				$title = $this->cache->retrieve('-title-'.$paginator->getCurrentPage());			
			}
			else
			{	
				$board = BoardsModel::find($arg['board_id']);
				$canonical = self::base_url() .'/board/'. $this->urlMaker->toUrl($board->board_name) . '/' . $board->id . '/' . intval($paginator->getCurrentPage());
				$paginator->getCurrentPage() > 1 ? $title =  $board->board_name . ' - ' . $this->translator->trans('lang.page') . ' ' . $paginator->getCurrentPage() : $title =  $board->board_name;
				$this->cache->setCache('board-'.$arg['board_id']);	
				$this->cache->store('-canonical-'.$paginator->getCurrentPage(), $canonical, 604800);
				$this->cache->store('-title-'.$paginator->getCurrentPage(), $title);
			}
			
			$childbords = BoardsModel::where('parent_id', $arg['board_id'])->get();
			$isChild = $childbords->count();
			$this->view->getEnvironment()->addGlobal('childbords', $childbords);
			$this->view->getEnvironment()->addGlobal('checkcb', $isChild);
			
			$this->view->getEnvironment()->addGlobal('canonical', $canonical);
			$this->view->getEnvironment()->addGlobal('title', $title);
		}			
		return $this->view->render($response, 'board.twig');
	}
	
}