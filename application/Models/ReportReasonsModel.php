<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReasonsModel extends Model
{
    protected $table = 'report_reasons';
    
    protected $fillable = 
        [
			'reason'
        ];
    
}