<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class ChatboxModel extends Model
{
    protected $table = 'chatbox';
    
    protected $fillable =
        [
            'user_id',
            'content'
        ];
}
