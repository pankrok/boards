<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlotsReadModel extends Model
{
    protected $table = 'plotread';
	public $timestamps = false;
    
    protected $fillable = 
        [
			'plot_id',
			'user_id',
			'timeline'	
        ];
    
}