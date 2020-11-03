<?php

namespace Admin\Auth;

use App\Models\UserModel;

 /**
 * Authentication class
 * @package BOARDS Forum
 */

class AdminAuth
{
    /**
    * check is user exist in database
    * @return boolen
    **/
	
    public function user()
    {	
		if(isset($_SESSION['admin'])) return UserModel::leftJoin('images', 'users.avatar', '=', 'images.id')->select('images._38', 'users.*')->find($_SESSION['admin']);
    }
    
	/**
    * check is user login
    * @return boolen
    **/
	
    public function check()
    {
        return isset($_SESSION['admin']);
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
			$user = UserModel::where([
			['email', $login[1]],
			['admin_lvl', '>=', 10]			
			])->first();
        }
        if($login[0] == 'username')
		{
			$user = UserModel::where([
			['username', $login[1]],
			['admin_lvl', '>=', 10]			
			])->first();
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
            $_SESSION['admin'] = $user->id;
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
		
		unset($_SESSION['admin']);
		
	}
}