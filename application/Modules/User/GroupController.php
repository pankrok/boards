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
            if ($username === null) {
                $username = '';
            }
            $id = self::getGroupeId($username);
        }
        if (!$group = GroupsModel::find($id)) {
            return [
                'username' => 'deleted user',
                'group' => 'deleted user'
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
        $data = UserModel::where('username', $username)->first();
        if( $data->main_group === null || $username === '') {
            return 0;
        }
        
        return $data->main_group;
    }
}
