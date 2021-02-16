<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Users;

use Application\Core\AdminController as Controller;
use Application\Models\GroupsModel;
use Application\Models\UserModel;

class AdminGroupController extends Controller
{
    public function index($request, $response, $arg)
    {
        $groups = GroupsModel::get()->toArray();
        
        $this->adminView->getEnvironment()->addGlobal('groups', $groups);
        $this->adminView->getEnvironment()->addGlobal('show_users', true);
        return $this->adminView->render($response, 'groups/group_list.twig');
    }
    
    public function addGroup($request, $response)
    {
        $this->adminView->getEnvironment()->addGlobal('show_users', true);
        return $this->adminView->render($response, 'groups/group_manage.twig');
    }
    
    public function editGroup($request, $response, $arg)
    {
        $group = GroupsModel::find($arg['id']);
        $this->adminView->getEnvironment()->addGlobal('group', $group);
        $this->adminView->getEnvironment()->addGlobal('show_users', true);
        return $this->adminView->render($response, 'groups/group_manage.twig');
    }
    
    public function groupPost($request, $response)
    {
        $body = $request->getParsedBOdy();
        
        if (isset($body['id'])) {
            $group = GroupsModel::find($body['id']);
            $group->grupe_name = $body['name'];
            $group->username_html = $body['html'];
            $group->grupe_level = $body['grupe_level'];
            $group->save() ? $return = ['success', 'update'] : $return = ['danger', 'update error'];
        } else {
            $group = GroupsModel::create([
                'grupe_name' => $body['name'],
                'username_html' => $body['html'],
                'grupe_level' => $body['grupe_level']
            ]);
            $group ? $return = ['success', 'groupe created'] : $return = ['danger', 'groupe creation error'];
        }

        $this->flash->addMessage($return[0], $return[1]);
        return $response->withHeader('Location', $this->router->urlFor('admin.groups.edit', ['id' => $group->id]))
                ->withStatus(302);
    }
    
    public function deleteGroup($request, $response)
    {
        $body = $request->getParsedBOdy();
        if (UserModel::where('main_group', $body['id'])->count() === 0) {
            GroupsModel::find($body['id'])->delete() ? $return = ['success', 'group deleted'] : $return  = ['danger', 'delete error'];
        } else {
            $return = ['warning	', 'you cant delete groupe with users!'];
        }
        $this->flash->addMessage($return[0], $return[1]);
        return $response->withHeader('Location', $this->router->urlFor('admin.groups'))
                ->withStatus(302);
    }
}
