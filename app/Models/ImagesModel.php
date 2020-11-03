<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagesModel extends Model
{
    protected $table = 'images';
	public $timestamps = false;
    
    protected $fillable = 
        [
			'original',
			'_38',
			'_85',
			'_150',		
        ];
    
}