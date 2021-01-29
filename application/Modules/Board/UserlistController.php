<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Core\Controller as Controller;
use Application\Models\UserModel;

class UserlistController extends Controller
{
    public function getList($request, $response, $arg)
    {
        $page = $arg['page'] ?? 1;
        $data = $request->getAttribute('cache');

        if (!isset($data)) {
            $routeName = \Slim\Routing\RouteContext::fromRequest($request)->getRoutingResults()->getUri();
            $data = self::getUsers($page);
            $this->cache->store($routeName, $data, $this->settings['cache']['cache_time']);
        }
        
        $this->view->getEnvironment()->addGlobal('users', $data['users']);
        $this->view->getEnvironment()->addGlobal('paginator', $data['paginator']);
        $this->view->getEnvironment()->addGlobal('title', $this->translator->get('lang.userlist'));
        
        return $this->view->render($response, 'userlist.twig');
        ;
    }
    
    
    protected function getUsers($currentPage)
    {
        $totalItems = UserModel::count();
        $itemsPerPage = $this->settings['pagination']['users'];
        $urlPattern = self::base_url().'/userlist/(:num)';

        $paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
        
        $users = UserModel::skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
                ->leftJoin('images', 'images.id', 'users.avatar')
                ->select('users.*', 'images._38')
                ->take($paginator->getItemsPerPage())
                ->get()->toArray();
                
        foreach ($users as $k => $v) {
            $v['avatar'] ? $users[$k]['avatar'] = self::base_url() . '/public/upload/avatars/' .$v['_38'] : $users[$k]['avatar'] =  self::base_url() . '/public/img/avatar.png';
            $username = $this->group->getGroupDate($v['main_group'], $v['username']);
            $users[$k]['username_html'] = $username['username'];
            $users[$k]['group_name'] = $username['group'];
            $users[$k]['url'] = $this->router->urlFor('user.profile', [
                        'username' => $this->urlMaker->toUrl($v['username']),
                        'uid' => $v['id']
                        ]);
        }
                
        return ['users' => $users, 'paginator' => $paginator];
    }
}
