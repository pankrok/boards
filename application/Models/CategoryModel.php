<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    
    protected $fillable = 
        [
			'name',
			'category_order',
			'active'
        ];
    
}