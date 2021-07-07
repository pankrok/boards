<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class GroupsModel extends Model
{
    protected $table = 'groups';
    
    protected $fillable =
        [
            'username_html',
            'grupe_name',
            'grupe_level',
        ];
}
