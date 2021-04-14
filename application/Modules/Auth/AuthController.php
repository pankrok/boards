<?php

declare(strict_types=1);

namespace Application\Modules\Auth;

use Application\Models\UserModel;
use Application\Models\AdditionalFieldsModel;
use Application\Models\UserAdditionalFieldsModel;
use Application\Models\SecretModel;
use Application\Core\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as v;

 /**
 * Authentication controller
 *
 * @package BOARDS Forum
 * @since   0.1
 */

class AuthController extends Controller
{
    
  /**
    * Remove session and redirect to home page
    *
    * @param object $request
  * @param object $response
    * @return object
    **/
  
    public function getSignOut($request, $response)
    {
        $this->auth->logout();
        return $response
          ->withHeader('Location', $this->router->urlFor('home'))
          ->withStatus(302);
    }
  
    /**
      * Render Sign In page
      *
      * @param object $request
    * @param object $response
      * @return object
      **/
  
    public function getSignIn($request, $response)
    {
        return $this->view->render($response, 'auth/signin.twig');
    }
    
    
    /**
    * Validate data from sign in form, set session for success login
    *
    * @param object $request
    * @param object $response
    * @return object
    **/
  
    public function postSignIn($request, $response)
    {
        $validation = $this->validator->validate($request, [
        'login' =>   v::notEmpty()->email()
        ]);
         
        if ($validation->faild() && isset($request->getParsedBody()['tfa'])) {
            $auth = $this->auth->attempt(
                ['username', $request->getParsedBody()['login']],
                null,
                $request->getParsedBody()['tfa']
            );
        } elseif ($validation->faild() && !isset($request->getParsedBody()['tfa'])) {
            $auth = $this->auth->attempt(
                ['username', $request->getParsedBody()['login']],
                $request->getParsedBody()['password']
            );
        } else {
            $auth = $this->auth->attempt(
                ['email', $request->getParsedBody()['login']],
                $request->getParsedBody()['password']
            );
        }

        if (!$auth) {
            $this->flash->addMessage('danger', 'Invalid email or password.');
        
            if (isset($request->getParsedBody()['admin'])) {
                return $response
        ->withHeader('Location', $this->router->urlFor('admin.home'))
        ->withStatus(302);
            }

            return $response
        ->withHeader('Location', $this->router->urlFor('auth.signin'))
        ->withStatus(302);
        }

        if ($auth === 'banned') {
            $this->flash->addMessage('danger', 'You are ' . $auth);

            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signin'))
            ->withStatus(302);
        }
        
        if ($auth === 'not confirmed account') {
            
            $auth .= '<br /><a href="'.$this->router->urlFor('auth.resend').'">resend code</a>';
            $this->flash->addMessage('danger', 'You are ' . $auth);

            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signin'))
            ->withStatus(302);
        }
        
        if (isset($request->getParsedBody()['admin']) && $auth === 'tfa') {
            return $response
        ->withHeader('Location', $this->router->urlFor('admin.home.tfa'))
        ->withStatus(302);
        }
        
        if ($auth === 'tfa') {
            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signin.tfa'))
            ->withStatus(302);
        }
        
        if (isset($request->getParsedBody()['admin'])) {
            return $response
        ->withHeader('Location', $this->router->urlFor('admin.home'))
        ->withStatus(302);
        }
    
        
        return $response
        ->withHeader('Location', $this->router->urlFor('home'))
        ->withStatus(302);
    }
    
    /**
    * Render Sign in 2fa page
    *
    * @param object $request
    * @param object $response
    * @return object
    **/
    
    public function twoFactorAuth($request, $response, $arg)
    {
        if (isset($_SESSION['tfa'])) {
            $secret = SecretModel::where('user_id', $_SESSION['tfa'])->first()->secret;
            $user = UserModel::find($_SESSION['tfa']);
        }
        if (!isset($arg['mail'])) {
            $this->tfa->google->getCode($secret);
        } else {
            $code = $this->tfa->mail->getCode($secret);
            $this->mailer->send(
                $user['email'],
                $user['username'],
                $this->translator->get('lang.Two factor code from') . $this->settings['board']['main_page_name'],
                '2fa',
                ['code' => $code]
            );
        }
        $this->view->getEnvironment()->addGlobal('username', $user['username']);
        return $this->view->render($response, 'auth/2fa.twig');
    }
    
    /**
    * Render Sign up page
    *
    * @param object $request
    * @param object $response
    * @return object
    **/

    public function getSignUp($request, $response)
    {
        $this->captcha->code();
        $this->captcha->image();
        
        $additionalFields = AdditionalFieldsModel::get()->toArray();

        $_SESSION['captcha'] = md5($this->captcha->getCode());
        $captcha = [
            'input' => '<input id="captchaCode" type="text"  class="form-control" name="board_captcha" value="" placeholder="Captcha code">',
            'image' => '<img class="img-fluid rounded mx-auto d-block" id="captchaImg" src="'.$this->captcha->getImage().'">'
        ];
        $this->view->getEnvironment()->addGlobal('captcha', $captcha);
        $this->view->getEnvironment()->addGlobal('additionalFields', $additionalFields);
        
        return $this->view->render($response, 'auth/signup.twig');
    }

    /**
    * Validate data from sign up form, register in database new user
    *
    * @param object $request
    * @param object $response
    * @return object
    **/

    public function postSignUp($request, $response)
    {
        $data = $_SESSION["captcha"];
        $_SESSION["captcha"] = null;
        $captcha = (md5($request->getParsedBody()['board_captcha']) !== $data);
        $passlenght = strlen($request->getParsedBody()['password']);
        $usernamelenght = strlen($request->getParsedBody()['username']);
        if (32 < $passlenght || $passlenght < 8) {
            $this->flash->addMessage('danger', 'Password must be bettwen 8 and and 32 characters');
            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signup'))
            ->withStatus(302);
        }
        
        if (32 < $usernamelenght || $usernamelenght < 4) {
            $this->flash->addMessage('danger', 'Username must be bettwen 4 and and 32 characters');
            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signup'))
            ->withStatus(302);
        }
        
        $validation = $this->validator->validate($request, [
            'username'         => v::notEmpty(),
            'email'         => v::noWhitespace()->notEmpty()->email()->EmailAvailble(),
            'password'      => v::noWhitespace()->notEmpty(),
            'vemail'         => v::notEmpty()->equals($request->getParsedBody()['email']),
            'vpassword'      => v::notEmpty()->equals($request->getParsedBody()['password'])
        ]);

        if ($validation->faild() || $captcha) {
            if ($captcha) {
                $this->flash->addMessage('danger', 'Invalid captcha code');
            }
            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signup'))
            ->withStatus(302);
        }
        
        $additionalFields = AdditionalFieldsModel::get()->toArray();
        if ($additionalFields !== null) {
            foreach ($additionalFields as $k => $field) {
                $fieldName = $this->urlMaker->toUrl($field['add_name']);
                if ($field['add_require'] === 1 &&  $request->getParsedBody()[$fieldName] === null) {
                    $this->flash->addMessage('danger', 'fill require fields');
                    return $response
                    ->withHeader('Location', $this->router->urlFor('auth.signup'))
                    ->withStatus(302);
                }
                $userAdditionalFields[$field['id']] = $request->getParsedBody()[$fieldName];
            }
        }
        
        if (UserModel::where('username', $this->purifier->purify($request->getParsedBody()['username']))->first() !== null) {
            $this->flash->addMessage('danger', 'Username already exist!');
            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signup'))
            ->withStatus(302);
        }
        
        $user = UserModel::create([
            'email' => $this->purifier->purify($request->getParsedBody()['email']),
            'username' => $this->purifier->purify($request->getParsedBody()['username']),
            'password' => password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT),
            'recommended_by' => $request->getParsedBody()['recommended'],
            'main_group' => $this->settings['board']['default_group']
        ]);
        if ($additionalFields !== null) {
            foreach ($userAdditionalFields as $k => $v) {
                UserAdditionalFieldsModel::create([
                    'user_id' => $user->id,
                    'field_id' => $k,
                    'add_value' => $v
                ]);
            }
        }
        
        if (intval($this->settings['board']['confirm_reg']) !== 1) {
            $this->flash->addMessage('info', 'Account created.');
            $auth = $this->auth->attempt(
                ['username', $request->getParsedBody()['username']],
                $request->getParsedBody()['password']
            );
        } else {
            $user->lostpw = bin2hex(random_bytes(32));
            $url = self::base_url(true) . $this->router->urlFor('auth.confirm', ['code' => $user->lostpw]);
            $urlCode = self::base_url(true) . $this->router->urlFor('auth.confirm');
            $user->save();
            $this->mailer->send(
                $user->email,
                $user->username,
                $this->translator->get('lang.confirm account'),
                'reg_confirm',
                ['url' => $url, 'code' => $user->lostpw, 'url_code' => $urlCode, 'username' => $user->username]
            );
            $this->flash->addMessage('info', 'activation email has been send');
            return $response
            ->withHeader('Location', $this->router->urlFor('home'))
            ->withStatus(302);
        }
        
        if (!$auth) {
            $this->flash->addMessage('danger', 'Invalid email or password');
            return $response
            ->withHeader('Location', $this->router->urlFor('auth.signin'))
            ->withStatus(302);
        }

        return $response
        ->withHeader('Location', $this->router->urlFor('home'))
        ->withStatus(302);
    }

    public function postHintUsers($request, $response)
    {
        $token = ($this->csrf->generateToken());
        $data['csrf'] = [
            'csrf_name' => $token['csrf_name'],
            'csrf_value' => $token['csrf_value']
        ];
        if (isset($request->getParsedBody()['recommended'])) {
            $recommended = $request->getParsedBody()['recommended'];
            $users = UserModel::select('users.username')->where('username', 'like', '%'.$recommended.'%')->get();
        
            foreach ($users as $k => $v) {
                $data['username'][$k] = $v->username;
            }
        }
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
    }

    public function refreshCaptcha($request, $response)
    {
        $token = ($this->csrf->generateToken());
        $data['csrf'] = [
            'csrf_name' => $token['csrf_name'],
            'csrf_value' => $token['csrf_value']
        ];
        
        $this->captcha->code();
        $this->captcha->image();
        $_SESSION['captcha'] = md5($this->captcha->getCode());
        $data['captcha'] = '<img class="img-fluid rounded mx-auto d-block" id="captchaImg" src="'.$this->captcha->getImage().'">';
        
        $response->getBody()->write(json_encode($data));
        
        
        return $response;
    }
    
    public function confirmAccount($request, $response, $arg)
    {
        if ($arg['code'] !== null || $request->getQueryParams()['code'] !== null) {
            $code = $arg['code'] ?? $request->getQueryParams()['code'];
            if (($user = UserModel::where('lostpw', $code)->first()) !== null) {
                $user->confirmed = 1;
                $user->lostpw = '';
                $user->save();
                
                $this->flash->addMessage('success', 'account is activtion success, you can log in now');
                
                return $response
                ->withHeader('Location', $this->router->urlFor('home'))
                ->withStatus(302);
            }
        }
        return $this->view->render($response, 'auth/confirm_account.twig');
    }
    
    public function resendActiveMail($request, $response)
    {
        $user = UserModel::find($_SESSION['ncuid']);
        unset($_SESSION['ncuid']);
        $user->lostpw = bin2hex(random_bytes(32));
        $url = self::base_url(true) . $this->router->urlFor('auth.confirm', ['code' => $user->lostpw]);
        $urlCode = self::base_url(true) . $this->router->urlFor('auth.confirm');
        $user->save();
        $this->mailer->send(
            $user->email,
            $user->username,
            $this->translator->get('lang.confirm account'),
            'reg_confirm',
            ['url' => $url, 'code' => $user->lostpw, 'url_code' => $urlCode, 'username' => $user->username]
        );
        $this->flash->addMessage('info', 'activation email has been send');
        return $response
        ->withHeader('Location', $this->router->urlFor('home'))
        ->withStatus(302);
    }   
        
}
