<?php

declare(strict_types=1);

namespace Application\Core\Modules\Auth;

use Application\Models\UserModel;
use Application\Models\SecretModel;

 /**
 * Authentication class
 *
 * @package BOARDS Forum
 * @since   0.1
 */

class Auth
{
    
    protected $tfa;
    protected $confirmReg;
    
    public function __construct($tfa, $confirmReg)
    {
        $this->tfa = $tfa;
        $this->confirmReg = $confirmReg;
    }
    
    /**
    * check is user exist in database
    * @return boolen
    **/
  
    public function user()
    {
        if (isset($_SESSION['user'])) {
            $a = UserModel::leftJoin('images', 'images.id', '=', 'users.avatar')->select('users.*', 'images._38', 'images._85', 'images._150')->find($_SESSION['user'])->makeHidden(['password']);
            if ($a) {
                $a->toArray();
            }
            return $a;
        }
    }
    
    /**
      * check is user login
      * @return boolen
      **/
  
    public function check()
    {
        return isset($_SESSION['user']) ?  $_SESSION['user'] : false;
    }
  
    public function checkAdmin()
    {
        return isset($_SESSION['user']) ? UserModel::select('admin_lvl')->find($_SESSION['user'])->toArray()['admin_lvl'] : false;
    }
  
    public function checkBan()
    {
        $ban = UserModel::select('banned')->find($_SESSION['user']);
        if ($ban->banned) {
            $_SESSION['user'] = null;
            unset($_SESSION['user']);
            if (isset(($_SESSION['admin']))) {
                unset($_SESSION['admin']);
            }
            die('YOU ARE BANNED!');
        }
        
        return null;
    }
  
    /**
      * find is user username or email is in database and set in session user ID if exists
    *
    * @param $login username or email
    * @param $password password
      * @return boolen
      **/
  
    public function attempt($login, $password, $tfa = null)
    {
        if ($login[0] == 'email') {
            $user = UserModel::where('email', $login[1])->first();
        }
        if ($login[0] == 'username') {
            $user = UserModel::where('username', $login[1])->first();
        }
        if (!$user) {
            return false;
        }
        
        if(!$user->confirmed && $this->settings['board']['confirm_reg'] === 1) {
            return 'not confirmed account';
        }
        
        if ($user->banned) {
            return 'banned';
        }

        if (isset($tfa)) {
            $secret = SecretModel::where('user_id', $user->id)->first();
            if($this->tfa->google->verifyCode($secret->secret, $tfa) 
            || $this->tfa->mail->verifyCode($secret->secret, $tfa)){
        
                    $_SESSION['user'] = $user->id;
                    $_SESSION['tfa'] = null;
                    unset($_SESSION['tfa']);
                    return true;
            } else {
                return false;
            }
        }

        if (password_verify($password, $user->password) && !$user->tfa) {
            $_SESSION['user'] = $user->id;
            return true;
        }
        
        if (password_verify($password, $user->password) && $user->tfa) {
            $_SESSION['tfa'] = $user->id;
            return 'tfa';
        }
        
        return false;
    }
  
    /**
      * logout - remove user session
      * @return boolen
      **/
  
    public function logout()
    {
        $_SESSION['user'] = null;
        unset($_SESSION['user']);
        if (isset(($_SESSION['admin']))) {
            unset($_SESSION['admin']);
        }
    }
}
