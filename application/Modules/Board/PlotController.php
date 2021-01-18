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
        if ($data['created_at']) {
            $data = strtotime($data['created_at']);
        } else {
            $data = 0;
        }
            
        return $data;
    }
    
    protected function lastSeenPost($plotId)
    {
        if (isset($_SESSION['user'])) {
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
        $cache = $request->getAttribute('cache');
        if (!isset($cache)) {
            $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
            $name = $routeContext->getRoute()->getName();
            $routeName = $routeContext->getRoutingResults()->getUri();
            $this->cache->setName($name);
            
            if (isset($arg['page']) && $arg['page'] > 0) {
                $currentPage = $arg['page'];
            } else {
                $currentPage = 1;
            }
            
            $totalItems = PostsModel::where('plot_id', $arg['plot_id'])->count();
            $itemsPerPage = $this->settings['pagination']['plots'];
            
            $urlPattern = self::base_url().'/plot/'.$arg['plot'].'/'.$arg['plot_id'].'/(:num)';
            $paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
        
            

            $data = PostsModel::where('posts.plot_id', $arg['plot_id'])
                    ->orderBy('posts.created_at', 'ASC')
                    ->skip(($paginator->getCurrentPage()-1)*$paginator->getItemsPerPage())
                    ->take($paginator->getItemsPerPage())
                    ->join('users', 'users.id', '=', 'posts.user_id')
                    ->leftJoin('images', 'users.avatar', '=', 'images.id')
                    ->select('posts.*', 'users.avatar', 'users.username', 'users.reputation', 'users.main_group', 'users.posts', 'users.plots', 'images._85', 'images._38')
                    ->get();
            
            foreach ($data as $k => $v) {
                $group = $this->group->getGroupDate($v->main_group, $v->username);
                $data[$k]->user_url = self::base_url() .'/user/'. $v->username .'/'. $v->user_id ;
                $data[$k]->avatar = $v->avatar ? self::base_url() . $this->settings['images']['path'] . $v->_85 : self::base_url() . '/public/img/avatar.png';
                $data[$k]->username_html = $group['username'];
                $data[$k]->group = $group['group'];
            }
            
            $plot = PlotsModel::find($arg['plot_id']);
            
            $plot_data['plot_data'] = $data;
            $plot_data['locked'] = $plot->locked;
            $plot_data['title'] = $plot->plot_name;
            $plot_data['hidden'] = $plot->hidden;
            
            $boardList = BoardsModel::select('id', 'board_name')->get()->toArray();
            
            $cache = [
                'paginator' => $paginator,
                'data' => $data,
                'locked' => $plot->locked,
                'plot_name' => $plot->plot_name,
                'hidden' => $plot->hidden,
                'plotList' => $boardList
            ];
            $this->cache->store($routeName, $cache, $this->settings['cache']['cache_time']);
        } else {
            $paginator = $cache['paginator'];
            $plot_data['plot_data'] = $cache['data'];
            $plot_data['locked'] = $cache['locked'];
            $plot_data['title'] = $cache['plot_name'];
            $plot_data['hidden'] = $cache['hidden'];
            $boardList = $cache['plotList'];
            $this->cache->deleteExpired();
        }
        
        if ($this->auth->check()
            && $paginator->getCurrentPage() == ceil($paginator->getTotalItems()/$paginator->getItemsPerPage())
            && (self::lastPost($arg['plot_id']) > self::lastSeenPost($arg['plot_id']))) {
            $plotRead = PlotsReadModel::firstOrCreate([
                'plot_id' => $arg['plot_id'],
                'user_id' => $_SESSION['user']
            ]);
            $plotRead->timeline = time();
            $plotRead->save();
        }
        
        if (!isset($_SESSION['view']) || $_SESSION['view'] !== $arg['plot_id']) {
            $_SESSION['view'] = $arg['plot_id'];
            $v = PlotsModel::find($arg['plot_id']);
            $v->increment('views');
            $v->save();
        }
        
        $this->view->getEnvironment()->addGlobal('paginator', $paginator);
        $this->view->getEnvironment()->addGlobal('board_list', $boardList);
        $this->view->getEnvironment()->addGlobal('title', $plot_data['title']);
        $this->view->getEnvironment()->addGlobal('locked', $plot_data['locked']);
        $this->view->getEnvironment()->addGlobal('hidden', $plot_data['hidden']);
        if ($plot_data) {
            $this->view->getEnvironment()->addGlobal('plot', [
                                                            'posts' => $plot_data['plot_data'],
                                                            'plot_id' => $arg['plot_id'],
                                                            ]);
        }
            
        return $this->view->render($response, 'plot.twig');
    }
    
    public function replyPost($request, $response)
    {
        $this->auth->checkBan();
        $plot = PlotsModel::select('locked', 'board_id', 'plot_name', 'id')->find($request->getParsedBody()['plot_id']);
        $locked = $plot->locked;
        
        $data['csrf'] = self::csftToken();

        if ($this->auth->check() && !$locked) {
            $user =UserModel::find($this->auth->user()['id']);
            $newPost = PostsModel::create([
                'user_id' => $user->id,
                'plot_id' => $plot->id,
                'content' => $this->purifier->purify($request->getParsedBody()['content']),
            ]);
            
            if ($user->avatar) {
                $avatar = ImagesModel::find($user->avatar);
            }
            
            $avatar = $user->avatar ? self::base_url() .'/public/upload/avatars/'.$avatar->_85 : self::base_url() .'/public/img/avatar.png';
            $uurl = self::base_url() .'/user/'. $this->urlMaker->toUrl($user->username) .'/'. $user->id;
            $group = $this->group->getGroupDate($user->main_group, $user->username);
            
            $var = [
                'id' => $newPost->id,
                'user_url' => $uurl,
                'username' => $user->username,
                'username_html' => $group['username'],
                'user_id' => $user->id,
                'avatar' => $avatar,
                'group' => $group['group'],
                'posts' => $user->posts,
                'plots' => $user->plots,
                'join' => $user->created_at,
                'reputation' => $user->reputation,
                'created_at' => date("Y-m-d H:i:s"),
                'content' => $this->purifier->purify($request->getParsedBody()['content'])
            ];
            
            $this->view->getEnvironment()->addGlobal('post', $var);
            $data['response'] = $this->view->fetch('templates/partials/boxes/onePost.twig');
            
            $user->posts++;
            $user->save();
            
            $board = BoardsModel::find($plot->board_id);
            $board->last_post_date = time();
            $board->last_post_author = $group['username'];
            $board ->posts_number++;
            $board->save();
            
            $plotRead = PlotsReadModel::firstOrCreate([
                'plot_id' => $plot->id,
                'user_id' => $_SESSION['user']
                ]);
            $plotRead->timeline = time();
            $plotRead->save();
            
            $totalItems = PostsModel::where('plot_id', $request->getParsedBody()['plot_id'])->count();
            $itemsPerPage = $this->settings['pagination']['plots'];
            $pages = ceil($totalItems / $itemsPerPage);
            $this->cache->setName('board.getPlot');
            $name = $this->getBasePath .'/plot/' . $this->urlMaker->toUrl($plot->plot_name) . '/'.$plot->id;
            
            if ($pages == 1) {
                $this->cache->delete($name);
            }
            $name .= '/'.$pages;
            $this->cache->delete($name);
            $this->BoardController->boardCleanCache($plot->board_id);
        } else {
            $data['response'] = $this->translator->trans('lang.you have to been logged in');
        }
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
    }
    
    public function newPlot($request, $response, $arg)
    {
        $this->auth->checkBan();
        $this->view->getEnvironment()->addGlobal('board_id', $arg['board_id']);
        $board = BoardsModel::find($arg['board_id']);
        
        if ($board->locked) {
            return $this->view->render($response, 'locked.twig');
        }
        
        return $this->view->render($response, 'newplot.twig');
    }
    
    public function newPlotPost($request, $response)
    {
        $data['csrf'] = self::csftToken();
        $this->auth->checkBan();
        if ($request->getParsedBody()['board_id']) {
            $board = BoardsModel::find($request->getParsedBody()['board_id']);
            
            if (!$board->locked && $request->getParsedBody()['content'] != '' &&  $request->getParsedBody()['topic'] != '') {
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
                    'content' => $this->purifier->purify($request->getParsedBody()['content']),
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
                $this->BoardController->boardCleanCache($board->id);
            } else {
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
        $this->auth->checkBan();
        
        $postID = $request->getParsedBody()['post_id'];
        $reasonID = $request->getParsedBody()['reason_id'];
            
        $report = ReportsModel::create([
            'user_id' => isset($_SESSION['user']) ? intval($_SESSION['user']) : null,
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
        $this->auth->checkBan();
        $data['csrf'] = self::csftToken();
        $postID = substr($request->getParsedBody()['post_id'], 5);
        $url =
        $data['likeit'] = LikeitModel::where([
            ['user_id', intval($_SESSION['user'])],
            ['post_id', intval($postID)]
        ])->first();
        if (!$data['likeit']) {
            $post = PostsModel::find($postID);
            
            if ($post->user_id == $_SESSION['user']) {
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
            
            $this->cache->setName('board.getPlot');
            $this->cache->delete($request->getParsedBody()['url']);
        } else {
            $data['likeit'] = ' <div id="overlay" style="display: none;""><div id="text" class=" alert alert-danger fade show" role="alert">
								  <strong>'.$this->translator->trans('lang.you add reputation to this post').'</strong>
								</div></div>';
        }
            
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
    }
    
    public function editPost($request, $response)
    {
        $this->auth->checkBan();
        $body = $request->getParsedBody();
        $data['token'] = self::csftToken();
        $post = PostsModel::find($body['post_id']);
                
        if ($this->auth->check() == $post->user_id || $this->auth->checkAdmin() > 1) {
            $body['content'] = $this->purifier->purify($body['content']);
            $post->hidden = ($body['hide'] ? 1 : 0);
            $post->content = $body['content'];
            $post->edit_by = $this->auth->user()['username'];
            $post->save() ? $data['response'] = 'success' : $data['response']  = 'danger';
            
            $page = $this->UserPanelController->findPage($post->created_at, $post->plot_id);
            $plot = $this->urlMaker->toUrl(PlotsModel::find($post->plot_id)->plot_name);
            
            $route = $this->router->urlFor('board.getPlot', [
                                                        'plot' => $plot,
                                                        'plot_id' => $post->plot_id,
                                                        'page' => $page
                                                        ]);
                                                        
            $this->cache->setName('board.getPlot');
            $this->cache->delete($route);
            $route .= '#post-'.$body['post_id'];
            
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                $response->getBody()->write(json_encode($data));
                return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(201);
            }
            
            $this->flash->addMessage($data['response'], 'Post edition:' . $data['response']);
            return $response
                ->withHeader('Location', $route)
                ->withStatus(302);
        } else {
            return $response->withStatus(403);
        }
    }
    
    public function lockPlot($request, $response)
    {
        $body = $request->getParsedBody();
        $plot = PlotsModel::find($body['id']);
        $plot->plot_name =  $this->purifier->purify($body['plot_name']);
        $plot->locked = $body['lock'] ? 1 : 0;
        $plot->hidden = $body['hidden'] ? 1 : 0;
        $plot->save();
        $body['lock'] ? $lock = 'locked' : $lock = 'ulocked';
        
        $this->flash->addMessage('success', $this->translator->trans('Plot '.$lock.' success!'));
        return $response
          ->withHeader('Location', $this->router->urlFor('board.getPlot', ['plot_id'=>$body['id'], 'plot' => $this->urlMaker->toUrl($plot->plot_name)]))
          ->withStatus(302);
    }
}
