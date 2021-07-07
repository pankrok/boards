<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Core\Controller;
use Application\Models\PlotsModel;
use Application\Models\PostsModel;
use Application\Models\UserModel;
use Application\Models\PagesModel;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

 /**
 * Search controller
 * @package BOARDS Forum
 */

class SearchController extends Controller
{


    public function index(Request $request, Response $response, array $data): Response
    {
        if(isset($data['search_in']) && isset($data['find_results']) && isset($data['find']) && isset($data['query'])) {
            switch ($data['search_in']) {
                case 'plots':
                    $result = self::searchPlots($data);
                    break;
                case 'pages':
                    $result = self::searchPages($data);
                    break;
                case 'users':
                    $result = self::searchUsers($data);
                    break;
            }
        }
        
        $this->view->getEnvironment()->addGlobal('type', $data['search_in']);
        $this->view->getEnvironment()->addGlobal('results', $result);
        
        return $this->view->render($response, 'pages/search/index.twig');
    }
    
    public function searchRouter(Request $request, Response $response, array $data): Response
    {
        $body = $request->getParsedBody();
        
        return $response
                ->withHeader('Location', $this->router->urlFor('search', [
                                                        'search_in' => $body['search_in'],
                                                        'find_results' => $body['find_results'],
                                                        'find' => $body['find'],
                                                        'query' => $body['query']
                                                        ]))
                ->withStatus(303);
    }
    
    protected function searchPlots(array &$data) {
        $return = ['type' => 'plot'];
        $querys = explode(' ', $data['query']);
        if (count($querys) > 6) {
            $querys = array_slice($querys, 0, 6);
        }
        
        if ($data['find_results'] === 'topics') {
            $return = PlotsModel::where([['posts.hidden', 0],['plots.hidden', 0]])
                    ->leftJoin('posts', 'plots.id', '=', 'posts.plot_id')
                    ->leftJoin('users', 'plots.author_id', '=', 'users.id')
                    ->select('plots.id as plot_id', 'plots.plot_name', 'posts.*', 'users.username', 'users.id as uid')
                    ->take(50);
            if ($data['find'] === 'phrase') {  
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->where('plots.plot_name', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('plots.plot_name', 'like',  '%' . $querys[0] . '%');                  
                }
            }
            
            if ($data['find'] === 'any') { 
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->orWhere('plots.plot_name', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('plots.plot_name', 'like', '%' . $querys[0] . '%');                  
                }
            }
            
            $return = $return->get()->toArray();
        }
        
        if ($data['find_results'] === 'all') {
            $topic = PlotsModel::where([['posts.hidden', 0],['plots.hidden', 0]])
                    ->leftJoin('posts', 'plots.id', '=', 'posts.plot_id')
                    ->leftJoin('users', 'plots.author_id', '=', 'users.id')
                    ->select('plots.id as plot_id', 'plots.plot_name', 'posts.*', 'users.username', 'users.id as uid')
                    ->take(50);
            $posts = PostsModel::where([['posts.hidden', 0],['plots.hidden', 0]])
                    ->leftJoin('plots', 'plots.id', '=', 'posts.plot_id')
                    ->leftJoin('users', 'posts.user_id', '=', 'users.id')
                    ->select('plots.id as plot_id', 'plots.plot_name', 'posts.*', 'users.username', 'users.id as uid')
                    ->take(50);
            if ($data['find'] === 'phrase') {   
                if  (isset($querys[1])) {
                    $topic->where(function($topic) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $topic->where('plots.plot_name', 'LIKE',  '%' . $query . '%');
                        }
                    });
                    
                    $posts->where(function($posts) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $posts->where('content', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $topic->where('plots.plot_name', 'like', '%'.  $querys[0] . '%'); 
                    $posts->where('posts.content', 'like', '%' .  $querys[0] . '%');                     
                }
            }
            
            if ($data['find'] === 'any') {             
                if  (isset($querys[1])) {
                    $topic->where(function($topic) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $topic->orWhere('plots.plot_name', 'LIKE',  '%' . $query . '%');
                        }
                    });
                    
                    $posts->where(function($posts) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $posts->orWhere('posts.content', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $topic->where('plots.plot_name', 'like',  $querys[0] . '%');   
                    $posts->where('posts.content', 'like', '%' .  $querys[0] . '%');                          
                }
            }

            $return = array_merge($topic->get()->toArray(), $posts->get()->toArray());
        }
        $return['type'] = 'plot';
        return $return;
    }
    protected function searchPages(array $data) : array {
        
        
        $querys = explode(' ', $data['query']);
        if (count($querys) > 6) {
            $querys = array_slice($querys, 0, 6);
        }
        
        if ($data['find_results'] === 'topics') {
            $return = PagesModel::where('active', 1)
                    ->take(50);
            if ($data['find'] === 'phrase') {  
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->where('name', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('name', 'LIKE',  '%' . $querys[0] . '%');                  
                }
            }
            
            if ($data['find'] === 'any') { 
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->orWhere('name', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('name', 'LIKE', '%' . $querys[0] . '%');                  
                }
            }
            $return = $return->get()->toArray();
        }
        
        if ($data['find_results'] === 'all') {
           $return = PagesModel::where('active', 1)
                    ->take(50);
            if ($data['find'] === 'phrase') {  
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->where('name', 'LIKE',  '%' . $query . '%');
                            $return->orWhere('content', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('name', 'LIKE',  '%' . $querys[0] . '%');
                    $return->orWhere('content', 'LIKE',  '%' . $querys[0] . '%');                    
                }
            }
            
            if ($data['find'] === 'any') { 
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->orWhere('name', 'LIKE',  '%' . $query . '%');
                            $return->orWhere('content', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('plots.plot_name', 'LIKE', '%' . $querys[0] . '%');      
                    $return->orWhere('content', 'LIKE',  '%' . $querys[0] . '%');                     
                }
            }
           
            $return = $return->get()->toArray();
            
        }
        
        $return['type'] = 'page';
        return $return;
    }
    protected function searchUsers(array $data) : array {
        $querys = explode(' ', $data['query']);
        if (count($querys) > 6) {
            $querys = array_slice($querys, 0, 6);
        }
        
        if ($data['find_results'] === 'name') {
            $return = UserModel::leftJoin('groups', 'users.main_group', 'groups.id')
                    ->leftJoin('images', 'users.avatar', 'images.id')
                    ->select('users.*', 'groups.username_html', 'groups.grupe_name', 'images._150')
                    ->take(50);
            if ($data['find'] === 'phrase') {  
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->where('users.username', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('users.username', 'like',  '%' . $querys[0] . '%');                  
                }
            }
            
            if ($data['find'] === 'any') { 
                if  (isset($querys[1])) {
                    $return->where(function($return) use (&$querys) {
                        foreach ($querys as $query) {                             
                            $return->orWhere('users.username', 'LIKE',  '%' . $query . '%');
                        }
                    });
                } else {
                    $return->where('users.username', 'like', '%' . $querys[0] . '%');                  
                }
            }
            
            $return = $return->get()->toArray();
            foreach ($return as $k => $v) {
                if (isset($return[$k]['avatar'])) {
                    $return[$k]['avatar'] = self::base_url() . '/public/upload/avatars/'.$v['_150'];
                } else {
                    $return[$k]['avatar'] = self::base_url() . '/public/img/avatar.png'; 
                }   
                $username_html = $this->group->getGroupDate($v['main_group'], $v['username']); 
                $return[$k]['username_html'] = $username_html['username'];
                $return[$k]['group'] = $username_html['group'];
            }
        }
        $users = [
            'data' => $return,
            'type' => 'user'
        ]; 
        return $users;
    }
    
}