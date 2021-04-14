<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class SecretModel extends Model
{
    protected $table = 'secret';
    public $timestamps = false;

    protected $fillable =
        [
            'user_id',
            'secret'
        ];
}
