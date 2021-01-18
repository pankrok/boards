<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class LikeitModel extends Model
{
    protected $table = 'likeit';
    
    
    protected $fillable =
        [
            'user_id',
            'post_id',
        ];
}
