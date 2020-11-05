<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class PluginsModel extends Model
{
    protected $table = 'plugins';
    
    protected $fillable = 
        [
			'plugin_name',
			'active',
			'install',
			'version'		
        ];

}