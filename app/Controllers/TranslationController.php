<?php

namespace App\Controllers;

use App\Models\User;
use Slim\Routing\RouteContext;
use Slim\Views\Twig as View;

/**
* Translation Controller
*
* @package BOARDS Forum
* @since   0.1
*
* @todo redirect to last URL
*
**/

class TranslationController extends Controller
{
    
	/**
    * Switch language in session by getting argument from URL /translate/{lang}
    *
    * @param object $request
	* @param object $response 
	* @param array $args
    * @return object 
    **/
	
	public function switch($request, $response, $args)
    {
		if(isset($args['lang'])){
			$_SESSION['lang'] = $args['lang'];
		}
			
		return $response
			->withHeader('Location', $this->router->urlFor('home'))
			->withStatus(302);
	}
}