<?php

declare(strict_types=1);

namespace Application\Modules\Statistic;

use Application\Core\Controller as Controller;
use Application\Models\UserModel;
use Application\Models\PlotsModel;
use Application\Models\PostsModel;

class StatisticController extends Controller
{

	public function getStats()
	{
	
		$data['users_count'] 	= UserModel::count();
		$data['plots_count'] 	= PlotsModel::count();
		$data['posts_count'] 	= PostsModel::count();
		$user = UserModel::orderBy('updated_at', 'DESC')
											->first();
		
		if(!$user) return false;
		
		$user->name_html = $this->group->getGroupDate($user->main_group, $user->username)['username'];
		$user = '<a href="' . self::base_url() . '/user/' . $this->urlMaker->toUrl($user->username) . '/' . $user->id .'">' . $user->name_html . '</a>';
		
		$data['last_user'] = $user;
		
		return $data;
		
	}
	
}