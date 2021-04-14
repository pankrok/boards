<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class RatesModel extends Model
{  
    protected $table = 'rates';
    public $timestamps = false;
    
    protected $fillable =
        [
            'user_id',
            'plot_id',
            'rate',
        ];
}
