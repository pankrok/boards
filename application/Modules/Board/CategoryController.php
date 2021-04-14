<?php

declare(strict_types=1);
namespace Application\Modules\Board;

use Application\Core\Controller;
use Application\Models\BoardsModel;
use Application\Models\PlotsModel;
use Application\Models\PostsModel;
use Application\Models\CategoryModel;

class CategoryController extends Controller
{
    public function getCategory($request, $response, $arg)
    {
        if (is_numeric($arg['category_id'])) {
            $cache = $request->getAttribute('cache');
            if (!isset($cache)) {
                $routeName = \Slim\Routing\RouteContext::fromRequest($request)->getRoutingResults()->getUri();
                
                $data = self::getCategoryData($arg['category_id']);
                $category = CategoryModel::find($arg['category_id'])->get()->toArray()[0];
                $this->cache->store($routeName, ['data' => $data, 'category' => $category], $this->settings['cache']['cache_time']);
            } else {
                $data = $cache['data'];
                $category =  $cache['category'];
            }
        }

        $this->view->getEnvironment()->addGlobal('boards', $data);
        $this->view->getEnvironment()->addGlobal('category', $category);
        $this->view->getEnvironment()->addGlobal('title', $category['name']);
        
        return $this->view->render($response, 'category.twig');
    }
    
    public function categoryCleanCache($id, $name = null)
    {
        $this->cache->setName('category.getCategory');
        $pages = ceil(BoardsModel::where([['category_id', $id], ['active', '=', '1']])->count() / $this->settings['pagination']['boards']);
        if (!isset($name)) {
            $name = $this->urlMaker->toUrl(CategoryModel::select('name')->find($id)->toArray()['name']);
        }
        
        for ($i = 1; $i <= $pages; $i++) {
            $this->cache->delete($this->router->urlFor('category.getCategory', [
                'category' => $name,
                'category_id' => $id,
                'page' => $i
            ]));
        }
        
        $this->cache->delete($this->router->urlFor('category.getCategory', [
                'category' => $name,
                'category_id' => $id,
                ]));
    }
    
    protected function getCategoryData($id)
    {
        $data = [];
        $boards = BoardsModel::where([['category_id', $id], ['active', '=', '1']])->get()->toArray();
        foreach ($boards as $k => $v) {
            if (isset($data[$v['id']]['childboard'])) {
                $data[$v['id']] += $v;
            } else {
                $data[$v['id']] = $v;
            }
            $data[$v['id']]['url'] =  $this->router->urlFor('board.getBoard', [
                'board' => $this->urlMaker->toUrl($v['board_name']),
                'board_id' => $v['id']
                ]);
            $lastpost = PlotsModel::orderBy('updated_at', 'DESC')
                                                ->where('board_id', '=', $v['id'])
                                                ->leftJoin('users', 'users.id', 'plots.author_id')
                                                ->select('plots.*', 'users.username')
                                                ->first();
            if (isset($lastpost)) {
                $lastpost->toArray();
                $lastPostId = PostsModel::orderBy('updated_at', 'DESC')
                                        ->where('plot_id', '=', $lastpost['id'])
                                        ->first()->id;
                
                $plots = PlotsModel::where('board_id', $v['id'])->get();
                $countPosts = 0;
                foreach ($plots as $plot) {
                    $countPosts += PostsModel::where([['plot_id', $plot->id], ['hidden', 0]])->count();
                }

                $count =  ceil(PlotsModel::where('board_id', '=', $v['id'])->count() / $this->settings['pagination']['plots']);
                $data[$v['id']]['last_post_url'] = $this->router->urlFor('board.getPlot', [
                    'plot' => $this->urlMaker->toUrl($lastpost['plot_name']),
                    'plot_id' => $lastpost['id'],
                    'page' => $count
                ]) . '#post-' . $lastPostId;
                
                $data[$v['id']]['posts_number'] = $countPosts;
                $data[$v['id']]['plots_number'] = $plots->count();
                $data[$v['id']]['plot_name'] = (strlen($lastpost['plot_name']) > 20) ? substr($lastpost['plot_name'], 0, 17).'...' : $lastpost['plot_name'];
                
                $data[$v['id']]['last_post_author_url'] =  $this->router->urlFor('user.profile', [
                    'user' => $this->urlMaker->toUrl($lastpost['username']),
                    'uid' => $lastpost['author_id']
                
                ]);
                $data[$v['id']]['last_post_author'] = $this->group->getGroupDate(null, $lastpost['username'])['username'];
            } else {
                $data[$v['id']]['posts_number'] = 0;
                $data[$v['id']]['plots_number'] = 0;
            }
            
            if ($data[$v['id']]['parent_id'] !== null) {
                $pid = $data[$v['id']]['parent_id'];
                $data[$pid]['childboard'][$k] = $data[$v['id']];
                unset($data[$v['id']]);
            }
        }
 
        return $data;
    }
}
