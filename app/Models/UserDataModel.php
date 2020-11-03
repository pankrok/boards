<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDataModel extends Model
{
    protected $table = 'userdata';
	public $timestamps = false;
    
    protected $fillable = 
        [
            'user_id',
            'name',
            'surname',
			'rank',
			'sex',
			'location',
			'website',
			'bday'
		];
		
}