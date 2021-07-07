<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class UserModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    protected $table = 'users';
    
    protected $fillable =
        [
            'username',
            'email',
            'password',
            'user_group',
            'additional_groups',
            'main_group',
            'admin_lvl',
            'confirmed',
            'posts',
            'plots',
            'avatar',
            'reputation',
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
            'banned',
            'priv_notes',
            'lostpw'
        ];
        
    protected $hidden = ['password'];
}
