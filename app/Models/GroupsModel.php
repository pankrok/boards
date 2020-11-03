<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupsModel extends Model
{
    protected $table = 'groups';
    
    protected $fillable = 
        [
            'name_html',
            'grupe_html',
            'grupe_name',
			'grupe_level',
        ];
    
}