<?php

namespace App\Auth;

use App\Models\UserModel;

 /**
 * Authentication class
 *
 * @package BOARDS Forum
 * @since   0.1
 */

class Auth
{
    /**
    * check is user exist in database
    * @return boolen
    **/
	
    public function user()
    {	
		if(isset($_SESSION['user'])) return UserModel::leftJoin('images', 'users.avatar', '=', 'images.id')->select('images._38','images._85','images._150','users.*')->find($_SESSION['user'])->makeHidden(['password']);
    }
    
	/**
    * check is user login
    * @return boolen
    **/
	
    public function check()
    {
        return isset($_SESSION['user']) ?  $_SESSION['user'] : false;
    }
	
	public function admin()
    {
        return isset($_SESSION['admin']) ? $_SESSION['admin'] : false;
    }
	
	/**
    * find is user username or email is in database and set in session user ID if exists
	*
	* @param $login username or email
	* @param $password password 
    * @return boolen
    **/
	
    public function attempt($login, $password)
    {
		if($login[0] == 'email')
		{
			$user = UserModel::where('email', $login[1])->first();
        }
        if($login[0] == 'username')
		{
			$user = UserModel::where('username', $login[1])->first();
        }
        if(!$user)
        {
            return false;
        }
        
		if($user->banned)
		{
			return 'banned';
		}
		
        if(password_verify($password, $user->password))
        {
            $_SESSION['user'] = $user->id;
			if($user->admin_lvl >= 10) $_SESSION['admin'] = $user->admin_lvl;
            return true;
        }
        
        return false;
    }
	
	/**
    * logout - remove user session
    * @return boolen
    **/
	
	public function logout()
	{
		
		unset($_SESSION['user']);
		if(isset(($_SESSION['admin']))) unset($_SESSION['admin']);
		
	}
}