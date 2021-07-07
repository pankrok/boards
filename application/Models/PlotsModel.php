<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class PlotsModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

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
            'stars',
        ];
}
