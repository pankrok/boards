<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class ConversationsModel extends Model
{
    protected $table = 'conversations';
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('H:i d.m.y');
    }
    
    protected $fillable =
        [
            'topic',
            'admin',
            'users'
        ];
}