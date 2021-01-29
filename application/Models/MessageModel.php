<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class MessageModel extends Model
{
    protected $table = 'message';
    
    protected $fillable =
        [
            'sender_id',
            'recipient_id',
            'topic',
            'body'
        ];
}
