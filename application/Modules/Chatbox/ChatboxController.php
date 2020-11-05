<?php

namespace Application\Modules\Chatbox;

use Application\Models\ChatboxModel;
use Application\Models\UserModel;

use Application\Core\Controller;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

 /**
 * Chatbox controller
 * @package BOARDS Forum
 */

class ChatboxController extends Controller
{
    
	protected function csftToken()
	{
		$token = ($this->container->get('csrf')->generateToken());
		return [
			'csrf_name' => $token['csrf_name'],
			'csrf_value' => $token['csrf_value']
		];
	}
	
	private function baserUrl()
	{
		if(isset($_SERVER['HTTPS'])){
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
			$protocol = 'http';
		}
		return $protocol . "://" . $_SERVER['HTTP_HOST'] . PREFIX;
	}
	/**
    * Remove session and redirect to home page
    *
    * @param object $request
	* @param object $response
    * @return object
    **/
	
	public function getChatMesasages()
	{
		
			$offset = ChatboxModel::all()->count()-10;
			$data = ChatboxModel::skip($offset)
								->take(10)
								->join('users', 'users.id', '=', 'chatbox.user_id')
								->join('images', 'users.avatar', '=', 'images.id')
								->select('chatbox.*', 'images._38', 'users.username', 'users.avatar')
								->get();
			// poprawić pobieranie avatorów
			
						
		if($data) $this->view->getEnvironment()->addGlobal('chatbox', $data);
	}
	
	public function postChatMessage($request, $response)
	{
		$base_url = self::baserUrl();
		$user = UserModel::find($_SESSION['user']);		
		if($user->avatar != NULL)
			$user->join('images', 'users.avatar', '=', 'images.id')->find($_SESSION['user']);
		
		$token = ($this->container->get('csrf')->generateToken());
		$data['csrf'] = self::csftToken();
	
		if (isset($request->getParsedBody()['shout']) && $request->getParsedBody()['shout'] != '' && $user)
		{	
	
			$id = ChatboxModel::create([
				'user_id' => $_SESSION['user'],
				'content' => htmlspecialchars($request->getParsedBody()['shout'])
			]);
			
			$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($user->username).'/'.$_SESSION['user'];
			$avatar = $user->avatar ? $base_url. $this->settings['images']['path'] .$user->_38 : $base_url.'/public/img/avatar.png';
			$shout = htmlspecialchars($request->getParsedBody()['shout']);
			
			$data['shout'] = file_get_contents(MAIN_DIR.'/skins/'.$this->settings['twig']['skin'].'//tpl/ajax/newshout.twig');
			$replace = [$id->id, $uurl, $user->username, $user->name_html, $avatar,  date("Y-m-d H:i:s"), $shout];
			$find = ['{@$id@}', '{@uurl@}', '{@username@}', '{@username_html@}', '{@avatar@}', '{@date@}', '{@shout@}'];
			
			$data['shout'] = str_replace($find, $replace, $data['shout']);

		}
			
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);
	}
	
	public function loadMoreMessages($request, $response){
		
		$base_url = self::baserUrl();
		$i = 1;
		
		$data['csrf'] = self::csftToken();
		
		$offset = ChatboxModel::all()->count()-($request->getParsedBody()['offset']*10)-3;
		
		if($offset < -10){
			
			$data['chatbox'] = 'no more shouts';
			$response->getBody()->write(json_encode($data));
			return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);		
		}
		
		$chatbox = ChatboxModel::offset($offset)
			->take(10)
			->orderBy('chatbox.id', 'asc')
			->join('users', 'users.id', '=', 'chatbox.user_id')
			->join('images', 'users.avatar', '=', 'images.id')
			->select('chatbox.*', 'images._38', 'users.username', 'users.avatar')
			->get();
			
		$data['chatbox'][0] = '<span id="scrollHere"></span>';
		foreach($chatbox as $shout)
		{
			$avatar = $shout->avatar ? $base_url. $this->settings['images']['path'] .$shout->_38 : $base_url.'/public/img/avatar.png';
			$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($shout->username).'/'.$shout->user_id;
			
			$data['chatbox'][$i] = file_get_contents(MAIN_DIR.'/skins/'.$this->settings['twig']['skin'].'/tpl/ajax/newshout.twig');
			
			$replace = [$shout->id, $uurl, $shout->username, $shout->username, $avatar, $shout->created_at, $shout->content];
			$find = ['{@$id@}', '{@uurl@}', '{@username@}', '{@username_html@}', '{@avatar@}', '{@date@}', '{@shout@}'];
			$data['chatbox'] = str_replace($find, $replace, $data['chatbox']);
			
			$i++;
		}
		
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);		
	}
	
	public function checkNewMessage($request, $response){
		
		$base_url = self::baserUrl();
		$i = 0;
		$last = null;
		
		$data['csrf'] = self::csftToken();
		
			$offset = ChatboxModel::all()->count()-10;
			$chatbox = ChatboxModel::skip($offset)
				->take(10)
				->get();
		
		$data['chatbox'][0] = 'no new shouts';
		
		foreach($chatbox as $shout){
			if($request->getParsedBody()['lastShout'] < $shout->id) 
			{	
				$user = UserModel::join('images', 'users.avatar', '=', 'images.id')->find($shout->user_id);
				$avatar = $user->avatar ? $base_url. $this->settings['images']['path'] .$user->_38 : $base_url.'/public/img/avatar.png';
				$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($user->username).'/'.$shout->user_id;
				
				$data['chatbox'][$i] = file_get_contents(MAIN_DIR.'/skins/'.$this->settings['twig']['skin'].'/tpl/ajax/newshout.twig');
				
				$replace = [$shout->id, $uurl, $user->username, $user->username, $avatar, $shout->created_at, $shout->content];
				$find = ['{@$id@}', '{@uurl@}', '{@username@}', '{@username_html@}', '{@avatar@}', '{@date@}', '{@shout@}'];
				$data['chatbox'] = str_replace($find, $replace, $data['chatbox']);
				
				$i++;
			}
		}

		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);	
	}
}