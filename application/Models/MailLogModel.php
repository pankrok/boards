<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class MailLogModel extends Model
{
    protected $table = 'mail_logs';
   
    protected $fillable =
        [
            'recipient',
            'is_send',
            'topic',
            'content_txt',
            'content_html',
            'log'
        ];
}
