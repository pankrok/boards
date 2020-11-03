<?php

namespace App\Models;

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