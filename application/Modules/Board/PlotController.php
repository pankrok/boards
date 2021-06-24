<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Interfaces\Board\PlotInterface;
use Application\Core\Controller;
use Application\Models\PlotsModel;
use Application\Models\PostsModel;
use Application\Models\PlotsReadModel;
use Application\Models\BoardsModel;
use Application\Models\UserModel;
use Application\Models\LikeitModel;
use Application\Models\ImagesModel;
use Application\Models\RatesModel;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

 /**
 * Plot controller
 * @package BOARDS Forum
 */

class PlotController extends Controller implements PlotInterface
{
    
    public function getPlot(Request $request, Response $response, array $arg): Response
    {
        $arg['plot_id'] = intval($arg['plot_id']);
        $arg['page'] = intval($arg['page'] ?? 1);
        if (!isset($arg['page'])) {
            $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
            $routeName = $routeContext->getRoutingResults()->getUri();
            return  $response
                    ->withHeader('Location', "$routeName/1")
                    ->withStatus(303);
        }
        
        $plot_data = $request->getAttribute('cache');
        if ($plot_data === null) {
            $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
            $name = $routeContext->getRoute()->getName();
            $routeName = $routeContext->getRoutingResults()->getUri();
            $this->cache->setPath($name);
            $plot_data = $this->PlotDataController->getPlotData($arg);
            $this->cache->set($routeName, $plot_data, $this->settings['cache']['cache_time']);
        }

        if (($plot_data['hidden'] == 1 && $this->auth->checkAdmin() < 1)
            || ($plot_data['paginator']->getNumPages() < $arg['page'])) {
            throw new \Slim\Exception\HttpNotFoundException($request);
        }
        
        $this->PlotDataController->setUserSeePost($arg['plot_id'], $plot_data['paginator']);
        
        if (!isset($_SESSION['view']) || $_SESSION['view'] !== $arg['plot_id']) {
            $_SESSION['view'] = $arg['plot_id'];
            $v = PlotsModel::find($arg['plot_id']);
            $v->timestamps = false;
            $v->increment('views');
            $v->save();
        }

        foreach ($plot_data as $k => $v) {
            $this->view->getEnvironment()->addGlobal($k, $v);
        }
 
        return $this->view->render($response, 'pages/plot/list.twig');
    }
    
    public function replyPost(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $response->getBody()->write($this->PlotDataController->setNewPost($data));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
    }
    
    public function newPlot(Request $request, Response $response, array  $arg) : Response
    {
        $this->auth->checkBan();
        $this->view->getEnvironment()->addGlobal('board_id', $arg['board_id']);
        $board = BoardsModel::find($arg['board_id']);
        
        if ($board->locked) {
            return $this->view->render($response, 'locked.twig');
        }
        
        return $this->view->render($response, 'pages/plot/new.twig');
    }
    
    public function newPlotPost(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $return = $this->PlotDataController->setNewPlot($body);
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                $response->getBody()->write(json_encode($return));
                return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(201);
        } else {
            if (isset($return['warn'])) {
                $this->flash->addMessage('danger', 'post or topic cannot be empty');
                $return['redirect'] = $this->router->urlFor('board.newPlot', ['board_id' => $return['board_id']]);
            }
            return $response
            ->withHeader('Location', $return['redirect'])
            ->withStatus(303);    
        }          
    }
    
    public function ratePlot(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $data = $this->PlotDataController->setPlotRate($body);
        if ($data['refresh'] === true) {
                self::cleanAllPlotPages($body['plot_id']);
        }
        
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
        } else {
            $this->flash->addMessage('danger', 'post or topic cannot be empty');  
            return $response
            ->withHeader(
                'Location',
                \Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getName()
                ) 
            ->withStatus(303);            
        }
        
    }
    
    public function reportPost(Request $request, Response $response): Response
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
        $report ? $report = $this->translator->get('lang.post has been reported to administration') : $this->translator->get('lang.something went wrong');
        $data['report'] = ' <div id="overlay" style="display: none;""><div id="text" class=" alert alert-info fade show" role="alert">
								  <strong>'.$report.'</strong>
								</div></div>';
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
    }
    
    
    public function likeit(Request $request, Response $response): Response
    {
        $this->auth->checkBan();
        $data['csrf'] = self::csftToken();
        $postID = $request->getParsedBody()['id'];
        $this->view->getEnvironment()->addGlobal('name', 'like-modal');
        $data['likeit'] = LikeitModel::where([
            ['user_id', intval($_SESSION['user'])],
            ['post_id', intval($postID)]
        ])->first();
        if (!$data['likeit']) {
            $post = PostsModel::find($postID);
            
            if ($post->user_id == $_SESSION['user']) {                               
                $this->view->getEnvironment()->addGlobal('alert', [
                    'type' => 'danger',
                    'body' => $this->translator->get('you cant like your own post')
                ]);
            
                $data['likeit'] = $this->view->fetch('boxes/modals/default.twig');                  
                                
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
            
            $this->view->getEnvironment()->addGlobal('alert', [
                    'type' => 'success',
                    'body' => $this->translator->get('reputation added')
                ]); 

          
            
            $this->cache->setPath('board.getPlot');
            $this->cache->delete($request->getParsedBody()['url']);
        } else {
            $this->view->getEnvironment()->addGlobal('alert', [
                    'type' => 'warning',
                    'body' => $this->translator->get('you already add reputation to this post')
                ]); 
        }
        
        $data['likeit'] = $this->view->fetch('boxes/modals/default.twig');   
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
    }
    
    public function editPost(Request $request, Response $response): Response
    {
        $this->auth->checkBan();
        $body = $request->getParsedBody();
        $data['token'] = self::csftToken();
        $post = PostsModel::find($body['post_id']);
                
        if ($this->auth->check() == $post->user_id || $this->auth->checkAdmin() > 1) {
            $body['content'] = $this->purifier->purify($body['content']);
            $post->hidden = (isset($body['hide']) ? 1 : 0);
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
                                                        
            $this->cache->setPath('board.getPlot');
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
                ->withStatus(303);
        } else {
            return $response->withStatus(403);
        }
    }
    
    public function moderatePlot(Request $request, Response $response): Response
    {
        if ($this->auth->checkAdmin() < 1) {
            echo $this->explorer->showError(
                'Unauthorized',
                401,
                'Access to this resource is denied your client has not supplied the correct authentication.'
            );
            die();
        }
        
        
        $body = $request->getParsedBody();
        $hidden = ($body['hidden'] ? 1 : 0);
        $plot = PlotsModel::find($body['id']);
 
        if (intval($body['board_select']) !== $plot->board_id) {
            $this->BoardController->boardCleanCache($plot->board_id);
        }
        
        $plot->plot_name =  $this->purifier->purify($body['plot_name']);
        $plot->board_id = $body['board_select'];
        $plot->locked = ($body['lock'] ? 1 : 0);
        $plot->hidden = $hidden;
        $plot->timestamps = false;
        $plot->save();
        $route = $this->router->urlFor('board.getPlot', ['plot_id'=>$body['id'], 'plot' => $this->urlMaker->toUrl($plot->plot_name)]);
        if (isset($body['lock'])) {
            $lock = 'locked';
        } else {
            $lock = 'ulocked';
        }
        
        self::cleanAllPlotPages($body['id']);
        $this->BoardController->boardCleanCache($plot->board_id);
        $this->flash->addMessage('success', $this->translator->get('Plot '.$lock.' success!'));
        return $response
          ->withHeader('Location', $route)
          ->withStatus(303);
    }
    
    protected function cleanAllPlotPages($plotId) : bool
    {
        $this->cache->setPath('board.getPlot');
        $plot = PlotsModel::find($plotId);
        $pages = ceil(PostsModel::where('plot_id', $plotId)->count() / $this->settings['pagination']['plots']);
        $plotUrl = $this->urlMaker->toUrl(PlotsModel::find($plotId)->plot_name);
        for ($i = 1; $i <= $pages; $i++) {
            $route = $this->router->urlFor('board.getPlot', [
                                                        'plot' => $plotUrl,
                                                        'plot_id' => $plotId,
                                                        'page' => $i
                                                        ]);
            if ($this->cache->delete($route) === false) {
                return false;
            }
        }
        
        return true;
    }
}
