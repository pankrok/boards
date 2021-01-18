<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class PlotsModel extends Model
{
    protected $table = 'plots';
    
    protected $fillable =
        [
            'plot_name',
            'plot_tags',
            'board_id',
            'author_id',
            'plot_active',
            'pinned',
            'pinned_order',
            'locked',
            'hidden',
            'views',
    
        ];
}
