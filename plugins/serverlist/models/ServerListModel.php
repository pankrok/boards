<?php

declare(strict_types=1);

namespace Plugins\ServerList\models;

use Illuminate\Database\Eloquent\Model;

class ServerListModel extends Model
{
    protected $table = 'serverlist';
    
    protected $fillable =
        [
            'name',
            'game',
            'ip',
            'port',
            'admin'
            
        ];
}
