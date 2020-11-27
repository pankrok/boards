<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class BoardsModel extends Model
{
    protected $table = 'boards';
    
    protected $fillable = 
        [
			'board_name',
			'board_description',
			'category_id',
			'parent_id',
			'board_order',
			'plots_number',
			'posts_number',
			'last_post_date',
			'last_post_author',
			'active'			
        ];

}