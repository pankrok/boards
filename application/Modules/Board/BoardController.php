<?php

declare(strict_types=1);
namespace Application\Modules\Board;

use Application\Models\PostsModel;
use Application\Models\PlotsModel;
use Application\Models\BoardsModel;
use Application\Core\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

 /**
 * Plot controller
 * @package BOARDS Forum
 */

class BoardController extends Controller
{
	
	public function getBoard($request, $response, $arg)
	{
		
		if(isset($arg['board_id']) && is_numeric($arg['board_id']))
		{			
			$data = $request->getAttribute('cache');

			if(!$data)
			{
				$routeName = \Slim\Routing\RouteContext::fromRequest($request)->getRoutingResults()->getUri();
				$data = self::getBoardData($arg);
				$this->cache->store($routeName, $data, $this->settings['cache']['cache_time']);
			}
			
		}
	
		$this->view->getEnvironment()->addGlobal('paginator', $data[1]);
		$this->view->getEnvironment()->addGlobal('board_name', $data[2]);
		$this->view->getEnvironment()->addGlobal('board', [
															'plots' => $data[0], 
															'board_id' => $arg['board_id'],
															'board_url' => $arg['board'],
															]);
			
			
				
		return $this->view->render($response, 'board.twig');
	}
	
	protected function getBoardData($arg)
	{
		$currentPage = (isset($arg['page']) ? $arg['page'] : 1);
			
		$totalItems = PlotsModel::where('board_id', $arg['board_id'])->count();
		$itemsPerPage = $this->settings['pagination']['boards'];		
		$urlPattern = self::base_url().'/board/'.$arg['board'].'/'.$arg['board_id'].'/(:num)';

		$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);	
			
		
		$boardName = BoardsModel::select('board_name')->find($arg['board_id'])->toArray()['board_name'];	
		$data = PlotsModel::where([
					['plot_active', '=', '1'],
					['board_id', '=', $arg['board_id']]])
				->join('users', 'users.id', '=', 'plots.author_id')
				->select('plots.*', 'users.username')
				->skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
				->take($paginator->getItemsPerPage())
				->get()->toArray();
		
		foreach($data as $k => $v)
		{
			$posts = PostsModel::where('plot_id', $v['id'])->count();
			$lastPostData = PostsModel::orderBy('created_at', 'desc')
										->join('users', 'users.id', '=', 'posts.user_id')
										->select('users.username', 'posts.created_at')
										->first()
										->toArray();
			
			$data[$k]['all_posts'] = $posts;
			$data[$k]['last_post_date'] = $lastPostData['created_at'];
			$data[$k]['last_post_autor'] = $lastPostData['username'];
		}

		
		return [$data, $paginator, $boardName];
	}
}