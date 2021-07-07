<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;


class MessageModel extends Model
{
    protected $table = 'message';
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    protected $fillable =
        [
            'sender_id',
            'conversation_id',
            'topic',
            'body'
        ];
}
