<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class AdminLogModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    protected $table = 'admin_logs';
    
    protected $fillable =
        [
            'admin_id',
            'log'
        ];
}
