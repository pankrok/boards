<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalFieldsModel extends Model
{
    protected $table = 'additional_fields';
    public $timestamps = false;

    protected $fillable =
        [
            'add_name',
            'add_type',
			'add_values',
            'description'		
        ];
}
