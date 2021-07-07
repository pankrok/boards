<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class CostumBoxModel extends Model
{
    protected $table = 'costum_boxes';
    public $timestamps = false;
    
    protected $fillable =
        [
            'translate',
            'name_prefix',
            'name',
            'html',
        ];
}
