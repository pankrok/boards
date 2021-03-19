<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Core\Controller as Controller;

class HomeController extends Controller
{
    public function index($request, $response, $arg)
    {
        $cache = $request->getAttribute('cache');
        if (!isset($cache)) {
            $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
            $name = $routeContext->getRoute()->getName();
            $routeName = $routeContext->getRoutingResults()->getUri();
            $this->cache->setName($name);
            
            $categories = self::getCategories();
            $boards = self::getBoards();
            $this->cache->store($routeName, ['categories' => $categories, 'boards' => $boards], $this->settings['cache']['cache_time']);
        } else {
            $categories = $cache['categories'];
            $boards 	= $cache['boards'];
        }
        
        $this->ChatboxController->getChatMesasages();
        
        $this->view->getEnvironment()->addGlobal('categories', $categories);
        $this->view->getEnvironment()->addGlobal('boards', $boards);
        
        if ($this->auth->check()) {
            $username = $this->group->getGroupDate($this->auth->user()['main_group'], $this->auth->user()['username']);
            $user = $this->auth->user();
            
            if ($user['avatar']) {
                $user['avatar'] =  self::base_url() . '/public/upload/avatars/' . $user['_150'];
            } else {
                $user['avatar'] = self::base_url() . '/public/img/avatar.png';
            }
            
            $user['username'] = $username['username'];
            $user['group'] = $username['group'];
            
            $this->view->getEnvironment()->addGlobal('user', $user);
        }
        $this->view->getEnvironment()->addGlobal('online', $this->OnlineController->getOnlineList());
        $this->view->getEnvironment()->addGlobal('stats', $this->StatisticController->getStats());
        $this->event->addGlobalEvent('home.loaded');
        return $this->view->render($response, 'home.twig');
        ;
    }
    
    protected function getCategories()
    {
        $categories = \Application\Models\CategoryModel::where('active', 1)->orderBy('category_order', 'DESC')->get()->toArray();
        
        foreach ($categories as $k => $v) {
            $categories[$k]['url'] = $this->router->urlFor('category.getCategory', [
                'category' => $this->urlMaker->toUrl($v['name']),
                'category_id' =>  $v['id']
                ]);
        }
        
        return $categories;
    }
    
    protected function getBoards()
    {
        $boards = [];
        $handler = \Application\Models\BoardsModel::where('active', '1')->orderBy('category_id')->orderBy('board_order', 'DESC')->get()->toArray();
        
        foreach ($handler as $k => $v) {
            
            $lastpost = \Application\Models\PlotsModel::orderBy('updated_at', 'DESC')
                                                    ->where('board_id', '=', $v['id'])
                                                    ->leftJoin('users', 'users.id', 'plots.author_id')
                                                    ->leftJoin('images', 'images.id', 'users.avatar')
                                                    ->select('plots.*', 'users.username', 'images._38')
                                                    ->first();
            if (isset($lastpost)) {
                $lastpost->toArray();

                if ($lastpost['_38']) {
                    $lastpost['_38'] =  self::base_url() . '/public/upload/avatars/' . $lastpost['_38'];
                } else {
                    $lastpost['_38'] = self::base_url() . '/public/img/avatar.png';
                }
            }
            $plots = \Application\Models\PlotsModel::where([['board_id', $v['id']], ['hidden', 0]])->get();
            $postsNo = 0;
            foreach ($plots as $plot) {
                $postsNo += \Application\Models\PostsModel::where([['plot_id', $plot->id], ['hidden', 0]])->count();
            }
            
            if (isset($boards[$v['category_id']][$v['id']]['childboard'])) {
                $boards[$v['category_id']][$v['id']] += $v;
            } else {
                $boards[$v['category_id']][$v['id']] = $v;
            }
            
            $boards[$v['category_id']][$v['id']]['plots_number'] =  $plots->count();
            $boards[$v['category_id']][$v['id']]['posts_number'] =  $postsNo;
            $boards[$v['category_id']][$v['id']]['url'] = self::base_url() . '/board/' . $this->urlMaker->toUrl($v['board_name'])	. '/' . $v['id'];
            if (isset($lastpost)) {
                
                $boards[$v['category_id']][$v['id']]['last_post'] = true;
                $boards[$v['category_id']][$v['id']]['plot_name'] = (strlen($lastpost['plot_name']) > 10) ? substr($lastpost['plot_name'], 0, 7).'...' : $lastpost['plot_name'];
                $boards[$v['category_id']][$v['id']]['last_post_url'] = $this->router->urlFor('board.getPlot', [
                    'plot' => $this->urlMaker->toUrl($lastpost['plot_name']),
                    'plot_id' => $lastpost['id'],
                    'page' => \Application\Models\PlotsModel::where('board_id', '=', $v['id'])->count(),
                ]) .'#post-' . $lastpost['id'];
                
                $boards[$v['category_id']][$v['id']]['last_post_author_url'] = $this->router->urlFor('user.profile', [
                    'username' => $this->urlMaker->toUrl($lastpost['username']),
                    'uid' => $lastpost['author_id']
                ]);
                $boards[$v['category_id']][$v['id']]['last_post_avatar'] = $lastpost['_38'];
            } else {
                $boards[$v['category_id']][$v['id']]['last_post'] = false;          
            }
            
            if ($boards[$v['category_id']][$v['id']]['parent_id'] !== null) {
                $pid = $boards[$v['category_id']][$v['id']]['parent_id'];
                $boards[$v['category_id']][$pid]['childboard'][$v['id']] = $boards[$v['category_id']][$v['id']];
                unset($boards[$v['category_id']][$v['id']]);
            }
        }
        
        $boards['groups_legend'] = \Application\Models\GroupsModel::select('grupe_name')->get()->toArray();
        
        return $boards;
    }
    
};
