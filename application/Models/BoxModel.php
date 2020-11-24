<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class BoxModel extends Model
{
    protected $table = 'boxes';
    
    protected $fillable = 
        [
            'box_order',
            'name',
            'html',
        ];
    
}