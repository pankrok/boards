<?php

namespace Admin\Controllers\Auth;

use App\Models\UserModel;
use App\Controllers\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as v;

 /**
 * Authentication controller
 *
 * @package BOARDS Forum
 * @since   0.1
 */

class AdminAuthController extends Controller
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
			->withHeader('Location', $this->router->urlFor('admin.home'))
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
		
		return $this->view->render($response, 'templates/auth/signin.twig');
    }
	
	public function postSignIn($request, $response)
    {
		$validation = $this->validator->validate($request, [
			'login' =>	 v::notEmpty()->email()
		 ]);
		if($validation->faild())
		{
			$auth = $this->auth->attempt(
				['username', $request->getParsedBody()['login']],
				$request->getParsedBody()['password']
			);
		}
		else
		{
			$auth = $this->auth->attempt(
				['email', $request->getParsedBody()['login']],
				$request->getParsedBody()['password']
        );
		}
		
        if(!$auth)
		{

			$this->flash->addMessage('danger', 'Invalid email or password.');
		
			return $response
					->withHeader('Location', $this->router->urlFor('admin.home'))
					->withStatus(302);
        }
		
		$this->cache->setCache('admin'.$_SESSION['admin']);
		$this->cache->erase('active');
        return $response
			->withHeader('Location', $this->router->urlFor('admin.home'))
			->withStatus(302);
    }

	
}