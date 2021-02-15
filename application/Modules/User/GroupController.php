<?php

declare(strict_types = 1);

namespace Application\Modules\User;

use Application\Models\GroupsModel;
use Application\Models\UserModel;

class GroupController
{
    public function getGroupDate($id, $username)
    {
        if ($id === null) {
            $id = self::getGroupeId($username);
        }
        if (!$group = GroupsModel::find($id)) {
            return [
                'username' => $username,
                'group' => 'none'
            ];
        }
        
        $usernameHtml = str_replace('{{username}}', $username, $group->username_html);
        
        return [
            'username' => $usernameHtml,
            'group' => $group->grupe_name
        ];
    }
    
    public function getGroupeId(string $username) : int
    {
        return UserModel::where('username', $username)->first()->main_group;
    }
}
