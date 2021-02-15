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
        if (isset($arg['board_id']) && is_numeric($arg['board_id'])) {
            $data = $request->getAttribute('cache');

            if (!isset($data)) {
                $routeName = \Slim\Routing\RouteContext::fromRequest($request)->getRoutingResults()->getUri();
                $data = self::getBoardData($arg);
                $this->cache->store($routeName, $data, $this->settings['cache']['cache_time']);
            }
        }
    
        $this->view->getEnvironment()->addGlobal('paginator', $data[1]);
        $this->view->getEnvironment()->addGlobal('board_name', $data[2]);
        $this->view->getEnvironment()->addGlobal('title', $data[2]);
        $this->view->getEnvironment()->addGlobal('new_plot', $data[3]);
        $this->view->getEnvironment()->addGlobal('board', [
                                                            'plots' => $data[0],
                                                            'board_id' => $arg['board_id'],
                                                            'board_url' => $arg['board'],
                                                            ]);
            
            
        $this->cache->deleteExpired();
        return $this->view->render($response, 'board.twig');
    }
    
    public function getBoardData($arg)
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
                ->select('plots.*', 'users.username', 'users.main_group')
                ->orderBy('updated_at', 'DESC')
                ->skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
                ->take($paginator->getItemsPerPage())
                ->get()->toArray();
        
        foreach ($data as $k => $v) {
            $postsCount = PostsModel::where([['plot_id', $v['id']], ['hidden', 0]])->count();
            $lastPostData = PostsModel::where('plot_id', $v['id'])
                                        ->orderBy('created_at', 'desc')
                                        ->join('users', 'users.id', '=', 'posts.user_id')
                                        ->select('users.username', 'users.main_group', 'posts.created_at', 'posts.id', 'posts.user_id')
                                        ->first()
                                        ->toArray();
            
            $count =  ceil($postsCount / $this->settings['pagination']['plots']);
            
            $data[$k]['username_html'] = $this->group->getGroupDate($v['main_group'], $v['username'])['username'];
            $data[$k]['all_posts'] = $postsCount;
            $data[$k]['url'] = $this->router->urlFor('board.getPlot', [
                'plot' => $this->urlMaker->toUrl($v['plot_name']),
                'plot_id' => $v['id'],
                'page' => $count,
                ]) .'#post-' . $lastPostData['id'];
            $data[$k]['user_url'] = $this->router->urlFor('user.profile', [
                'username' => $this->urlMaker->toUrl($v['username']),
                'uid' => $v['author_id']
            ]);
            $data[$k]['last_post_date'] = $lastPostData['created_at'];
            $data[$k]['last_post_autor'] = $this->group->getGroupDate($lastPostData['main_group'], $lastPostData['username'])['username'];
            $data[$k]['last_post_autor_url'] = $this->router->urlFor('user.profile', [
                'username' => $this->urlMaker->toUrl($lastPostData['username']),
                'uid' => $lastPostData['user_id']
            ]);
        }
        $newPlot = $this->router->urlFor('board.newPlot', ['board_id' => $arg['board_id']]);
        
        
        return [$data, $paginator, $boardName, $newPlot];
    }
    
    public function boardCleanCache($id, $name = null)
    {
        $this->cache->setName('board.getBoard');
        $pages = ceil(PlotsModel::where('board_id', $id)->count() / $this->settings['pagination']['boards']);
        if (!isset($name)) {
            $name = $this->urlMaker->toUrl(BoardsModel::select('board_name')->find($id)->toArray()['board_name']);
        }
        
        for ($i = 1; $i <= $pages; $i++) {
            $this->cache->delete('/board/'.$name.'/'.$id.'/'.$i);
        }
        
        $this->cache->delete('/board/'.$name.'/'.$id);
        $this->CategoryController->categoryCleanCache(BoardsModel::find($id)->category_id); 
        $this->cache->setName('home');
        $this->cache->delete($this->router->urlFor('home'));
    }
}
