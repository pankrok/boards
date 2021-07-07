<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class BoxModel extends Model
{
    protected $table = 'boxes';
    public $timestamps = false;
    
    protected $fillable =
                    [
                        'costum_id',
                        'costum',
                        'engine'
                    ];
}
