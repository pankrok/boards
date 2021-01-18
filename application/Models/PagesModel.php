<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class PagesModel extends Model
{
    protected $table = 'pages';
    
    protected $fillable =
                    [
                        'content',
                        'name',
                        'active'
                    ];
}
