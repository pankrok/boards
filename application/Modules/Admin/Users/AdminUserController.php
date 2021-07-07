<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Users;

use Application\Core\AdminController as Controller;
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
        $this->adminView->getEnvironment()->addGlobal('show_users', true);

        return $this->adminView->render($response, 'users_list.twig');
    }
    
    public function usersConfig($request, $response)
    {
        return $this->adminView->render($response, 'usersAndGroups.twig');
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
        $this->adminView->getEnvironment()->addGlobal('show_users', true);
        return $this->adminView->render($response, 'user_edit.twig');
    }
    
    public function saveUserData($request, $response)
    {
        $body = $request->getParsedBody();
        $user = UserModel::find($body['id']);
        
        foreach ($body as $k => $v) {
            if ($_SESSION['user'] === intval($body['id']) && intval($body['admin_lvl']) !== $user->admin_lvl) {
                $this->flash->addMessage('warning', 'you cant edit admin level of yourself!');
                return $response->withHeader('Location', $this->router->urlFor('admin.user.edit', ['id' => $user->id]))
                ->withStatus(302);
            }
            if ($k != 'id') {
                if ($body['banned'] === '1' && $_SESSION['user'] === intval($body['id'])) {
                    $this->flash->addMessage('warning', 'you cant ban yourself!');
                } else {
                    if ($k === 'password' && $v !== '') {
                        $user->$k = password_hash($v, PASSWORD_DEFAULT);
                    }
                    
                    if ($k !== 'password') {
                        $user->$k = $v;
                    }
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
    
    public function addUser($request, $response)
    {
        $this->adminView->getEnvironment()->addGlobal('default_group', $this->settings['board']['default_group']);
        $this->adminView->getEnvironment()->addGlobal('groups', GroupsModel::get());
        return $this->adminView->render($response, 'user_add.twig');
    }
    
    public function addUserPost($request, $response)
    {
        if (UserModel::where('username', $this->purifier->purify($request->getParsedBody()['username']))->first() !== null) {
            $this->flash->addMessage('danger', 'Username already exist!');
            return $response
            ->withHeader('Location', $this->router->urlFor('admin.user.add'))
            ->withStatus(302);
        }
        
        if (UserModel::where('email', $this->purifier->purify($request->getParsedBody()['email']))->first() !== null) {
            $this->flash->addMessage('danger', 'Email already exist!');
            return $response
            ->withHeader('Location', $this->router->urlFor('admin.user.add'))
            ->withStatus(302);
        }
        
        foreach ($request->getParsedBody() as $k => $v) {
            if ($v === '') {
                $this->flash->addMessage('warning', "$k cannot be empty!");
                return $response
                ->withHeader('Location', $this->router->urlFor('admin.user.add'))
                ->withStatus(302);
            }
        }
        
        $user = UserModel::create([
            'email' => $this->purifier->purify($request->getParsedBody()['email']),
            'username' => $this->purifier->purify($request->getParsedBody()['username']),
            'password' => password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT),
            'warn_level' => $request->getParsedBody()['warn_level'],
            'banned' => $request->getParsedBody()['banned'],
            'tfa' => $request->getParsedBody()['tfa'],
            'admin_lvl' => $request->getParsedBody()['admin_lvl'],
            'main_group' => $request->getParsedBody()['main_group'],
            'confirmed' => $request->getParsedBody()['confirmed'],
        ]);
        
        return $response->withHeader('Location', $this->router->urlFor('admin.user.edit', ['id' => $user->id]))
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
