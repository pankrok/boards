<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class UserAdditionalFieldsModel extends Model
{
    protected $table = 'user_additional_fields';
    public $timestamps = false;

    protected $fillable =
        [
            'user_id',
            'field_id',
			'add_value'	
        ];
}
