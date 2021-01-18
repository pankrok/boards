<?php

declare(strict_types=1);

namespace Application\Modules\Auth;

use Application\Models\UserModel;
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
        
        if ($validation->faild()) {
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
            $this->flash->addMessage('danger', 'You are banned!');

            return $response
        ->withHeader('Location', $this->router->urlFor('auth.signin'))
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
        
        $_SESSION['captcha'] = md5($this->captcha->getCode());
        $captcha = [
            'input' => '<input id="captchaCode" type="text"  class="form-control" name="board_captcha" value="" placeholder="Captcha code">',
            'image' => '<img class="img-fluid rounded mx-auto d-block" id="captchaImg" src="'.$this->captcha->getImage().'">'
        ];
        $this->view->getEnvironment()->addGlobal('captcha', $captcha);
        
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
        UserModel::create([
            'email' => $this->purifier->purify($request->getParsedBody()['email']),
            'username' => $this->purifier->purify($request->getParsedBody()['username']),
            'password' => password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT),
            'recommended_by' => $request->getParsedBody()['recommended'],
            'main_group' => $this->settings['board']['default_group']
        ]);
        $this->flash->addMessage('info', 'Account created.');

        $auth = $this->auth->attempt(
            [

        'username', $request->getParsedBody()['username']],
            $request->getParsedBody()['password']
        );
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
}
