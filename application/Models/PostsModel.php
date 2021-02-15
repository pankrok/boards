<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class PostsModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    protected $table = 'posts';
    
    protected $fillable =
        [
            'user_id',
            'plot_id',
            'content',
            'post_reputation',
            'hidden',
            'edit_by'
        ];
}
