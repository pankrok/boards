<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportsModel extends Model
{
    protected $table = 'reports';
    
    protected $fillable = 
        [
			'post_id',
			'reason_id',
			'user_id',
			'comment',
			'closed',
        ];
    
}