<?php

declare(strict_types=1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class SkinsBoxesModel extends Model
{
    protected $table = 'skins_boxes';
	public $timestamps = false;
	
	 protected $fillable = 
					[
						'skin_id',
						'box_id',
						'box_order',
						'side',
						'active'
					];
}