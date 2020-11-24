<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class SkinsModel extends Model
{
    protected $table = 'skins';
    
    protected $fillable = 
        [
            'name',
			'dirname',
            'author',
			'version'
        ];
    
}