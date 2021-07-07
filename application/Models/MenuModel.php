<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class MenuModel extends Model
{
    protected $table = 'menu';
    
    protected $fillable =
                    [
                        'url',
                        'name',
                        'url_order'

                    ];
}
