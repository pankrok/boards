<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;


class MailboxModel extends Model
{
    protected $table = 'mailbox';
    public $timestamps = false;
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    protected $fillable =
        [
            'user_id',
            'mailbox',
            'message_id',
            'conversation_id',
            'unread'
        ];
}
