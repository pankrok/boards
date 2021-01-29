<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxModel extends Model
{
    protected $table = 'mailbox';
    public $timestamps = false;

    protected $fillable =
        [
            'user_id',
            'mailbox',
			'message_id',
			'unread'			
        ];
}
