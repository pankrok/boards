<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class PostsModel extends Model
{
    protected $table = 'posts';
    
    protected $fillable = 
        [
			'user_id',
			'plot_id',
			'content',
			'post_reputation',
			'hidden',
        ];
    
}