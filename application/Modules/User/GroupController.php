<?php

declare(strict_types = 1);

namespace Application\Modules\User;

use Application\Models\GroupsModel;

class GroupController
{
    public function getGroupDate($id, $username)
    {
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
}
