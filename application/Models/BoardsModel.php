<?php

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class BoardsModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    protected $table = 'boards';
    
    protected $fillable =
        [
            'board_name',
            'board_description',
            'category_id',
            'parent_id',
            'board_order',
            'last_post_date',
            'last_post_author',
            'active'
        ];
}
