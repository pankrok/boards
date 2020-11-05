<?php

declare(strict_types=1);

namespace Application\Core\Modules\Auth;

use Application\Models\UserModel;

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
    if(isset($_SESSION['user'])) return UserModel::find($_SESSION['user'])->makeHidden(['password'])->toArray();
    }
    
  /**
    * check is user login
    * @return boolen
    **/
  
    public function check()
    {
        return isset($_SESSION['user']) ?  $_SESSION['user'] : false;
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