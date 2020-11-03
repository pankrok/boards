<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'users';
    
    protected $fillable = 
        [
            'username',
            'email',
            'password',
			'salt',
			'user_group',
			'additional_groups',
			'main_group',
			'posts',
			'plots',
			'avatar',
			'reg_date',
			'last_active',
			'last_post',
			'online_time',
			'recommended_by',
			'pm',
			'recive_pm',
			'brithday',
			'brithday_visibility',
			'mail_visibility',
			'active_visibility',
			'timezone',
			'friends',
			'ignore_users',
			'style',
			'away',
			'away_start',
			'away_end',
			'lang',
			'warn_level',
			'priv_notes',
			'lostpw',
			'admin_lvl'
        ];
    
}