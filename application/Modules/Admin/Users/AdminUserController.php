<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Users;

use Application\Core\Controller as Controller;
use Application\Models\UserModel;
use Application\Models\GroupsModel;

class AdminUserController extends Controller
{
    public function index($request, $response, $arg)
    {
        $page = $arg['page'] ?? 1;
        
        $data = self::getUsers($page);

        $this->adminView->getEnvironment()->addGlobal('users', $data['users']);
        $this->adminView->getEnvironment()->addGlobal('paginator', $data['paginator']);

        return $this->adminView->render($response, 'users_list.twig');
    }
    
    public function editUser($request, $response, $arg)
    {
        $userdata = UserModel::where('users.id', $arg['id'])
                            ->leftJoin('groups', 'groups.id', 'users.main_group')
                            ->leftJoin('images', 'images.id', 'users.avatar')
                            ->select('users.*', 'groups.grupe_name', 'images._150')->get()->toArray()[0];

        isset($userdata['_150'])? $userdata['avatar'] = self::base_url() . '/public/upload/avatars/' . $userdata['_150'] :  $userdata['avatar'] = self::base_url() . '/public/img/avatar.png';
                            
        $groups = GroupsModel::get();

        $this->adminView->getEnvironment()->addGlobal('user', $userdata);
        $this->adminView->getEnvironment()->addGlobal('groups', $groups);
        return $this->adminView->render($response, 'user_edit.twig');
    }
    
    public function saveUserData($request, $response)
    {
        $body = $request->getParsedBody();
        $user = UserModel::find($body['id']);
        
        foreach ($body as $k => $v) {
            if ($_SESSION['user'] == $body['id'] && $k == 'admin_lvl') {
                $this->flash->addMessage('warning', 'you cant edit admin level of yourself!');
                return $response->withHeader('Location', $this->router->urlFor('admin.user.edit', ['id' => $user->id]))
                ->withStatus(302);
            }
            if ($k != 'id') {
                if ($k == 'banned' && $_SESSION['user'] == $body['id']) {
                    $this->flash->addMessage('warning', 'you cant ban yourself!');
                } else {
                    $user->$k = $v;
                }
            }
        }
        $user->save();
        return $response->withHeader('Location', $this->router->urlFor('admin.user.edit', ['id' => $user->id]))
                ->withStatus(302);
    }
    
    public function deleteUser($request, $response)
    {
        $body = $request->getParsedBody();
        if ($body['id'] != $_SESSION['user']) {
            UserModel::find($body['id'])->delete();
            $message = ['info', 'user deleted!'];
        } else {
            $message = ['warning', 'you cant delete yourself!'];
        }
        
        $this->flash->addMessage($message[0], $message[1]);
        return $response->withHeader('Location', $this->router->urlFor('admin.users'))
                ->withStatus(302);
    }
    
    protected function getUsers($currentPage)
    {
        $totalItems = UserModel::count();
        $itemsPerPage = $this->settings['pagination']['users'];
        $urlPattern = self::base_url().'/acp/users/list/(:num)';

        $paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
        
        $users = UserModel::skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
                ->take($paginator->getItemsPerPage())
                ->get()->toArray();
        return ['users' => $users, 'paginator' => $paginator];
    }
};
