<?php

namespace App\Views\Extensions;

use App\Models\UserModel;
use App\Models\PlotsModel;
use Twig_Extension;
use Twig_SimpleFunction;

class UserExtension extends Twig_Extension
{
	
	protected $cache;
	protected $userdata;
	
	public function __construct($cache, $userdata)
	{
		$this->cache = $cache;
		$this->userdata = $userdata;
	}
	
	public function getFunctions()
	{
		return 	[
			new Twig_SimpleFunction('userActive', [$this, 'userActive']),
			new Twig_SimpleFunction('userActivity', [$this, 'userActivity']),
			new Twig_SimpleFunction('userData', [$this, 'userData']),
		];
		
	}
	
	public function userActive($uid)
	{
		$this->cache->setCache($uid);
		$this->cache->eraseExpired();
		
		if($this->cache->isCached('active')){
			$user = $this->cache->retrieve('active');
		}
		else
		{
			$user = UserModel::select('last_active')->find($uid);
			$this->cache->store('active', $user, 60);
		}
		if(isset($user) && $user->last_active > time()-300)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function userActivity($uid)
	{
		
		$this->cache->setCache($uid);
		$this->cache->eraseExpired();
		
		if($this->cache->isCached('activity')){
			$user = $this->cache->retrieve('activity');
		}
		else
		{
			$user = UserModel::select('username','user_group' ,'posts', 'plots', 'reputation', 'created_at')->find($uid);
			$user->plots = PlotsModel::where('author_id', $uid)->count();
			$this->cache->store('activity', $user, 300);
		}
		if(isset($user))
		{	
			return [
				'username' => $user->username,
				'group' => $user->user_group,
				'posts' => $user->posts,
				'plots' => $user->plots,
				'join'	=> date("d.m.Y", strtotime($user->created_at)),
				'reputation' => $user->reputation
			];
		}
	}
	
	public function userData($uid)
	{
		return $this->userdata->getData($uid);
	}
		
	
}