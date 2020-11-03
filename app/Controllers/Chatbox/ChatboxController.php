<?php

namespace App\Controllers\Chatbox;

use App\Models\ChatboxModel;
use App\Controllers\Controller;
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
		$this->event->addGlobalEvent('chat.get.messages');
		$this->cache->setCache('Chatbox');
		if($this->cache->isCached('Chatbox'))
		{
			$data = $this->cache->retrieve('Chatbox');
			
		}
		else
		{
			$offset = ChatboxModel::all()->count()-20;
			$data = ChatboxModel::skip($offset)
								->take(20)
								->get();
			
			$this->cache->store('Chatbox', $data, 300); 
		}
		
		if($data) $this->view->getEnvironment()->addGlobal('chatbox', $data);
		
	}
	
	public function postChatMessage($request, $response)
	{
		$base_url = self::baserUrl();
		$this->event->addGlobalEvent('chat.post.messages');
		$user = $this->userdata->getData($_SESSION['user']);		
		
		$token = ($this->container->get('csrf')->generateToken());
		$data['csrf'] = self::csftToken();
	
		if (isset($request->getParsedBody()['shout']) && $request->getParsedBody()['shout'] != '' && $user)
		{	
	
			$id = ChatboxModel::create([
				'user_id' => $_SESSION['user'],
				'content' => htmlspecialchars($request->getParsedBody()['shout'])
			]);
			
			$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($user->username).'/'.$_SESSION['user'];
			$avatar = $user->avatar ? $base_url.'/cache/img/'.$user->_38 : $base_url.'/public/img/avatar.png';
			$shout = htmlspecialchars($request->getParsedBody()['shout']);
			
			$data['shout'] = file_get_contents(MAIN_DIR.'/skins/bluehaze/ajax/newshout.twig');
			$replace = [$id->id, $uurl, $user->username, $user->name_html, $avatar,  date("Y-m-d H:i:s"), $shout];
			$find = ['{@$id@}', '{@uurl@}', '{@username@}', '{@username_html@}', '{@avatar@}', '{@date@}', '{@shout@}'];
			
			$data['shout'] = str_replace($find, $replace, $data['shout']);

		}
		
		$this->cache->setCache('Chatbox');
		if($this->cache->isCached('Chatbox')) $this->cache->erase('Chatbox');
		
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);
	}
	
	public function loadMoreMessages($request, $response){
		
		$base_url = self::baserUrl();
		$i = 1;
		
		$data['csrf'] = self::csftToken();
		
		$offset = ChatboxModel::all()->count()-($request->getParsedBody()['offset']*10)-20;
		
		if($offset < -10){
			
			$data['chatbox'] = 'no more shouts';
			$response->getBody()->write(json_encode($data));
			return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);		
		}
		
		$chatbox = ChatboxModel::offset($offset)
			->take(10)
			->orderBy('chatbox.id', 'asc')
			->select( 'chatbox.id', 'chatbox.user_id', 'chatbox.content', 'chatbox.created_at')
			->get();
		$data['chatbox'][0] = '<span id="scrollHere"></span>';
		foreach($chatbox as $shout)
		{
			$user = $this->userdata->getData($shout->user_id);
			$avatar = $user->avatar ? $base_url.'/cache/img/'.$user->_38 : $base_url.'/public/img/avatar.png';
			$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($user->username).'/'.$shout->user_id;
			
			$data['chatbox'][$i] = file_get_contents(MAIN_DIR.'/skins/'.$this->settings['skin'].'/ajax/newshout.twig');
			
			$replace = [$shout->id, $uurl, $user->username, $user->name_html, $avatar, $shout->created_at, $shout->content];
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
		
		$this->cache->setCache('Chatbox');
		$data['csrf'] = self::csftToken();
		
		if($this->cache->isCached('Chatbox'))
		{
			$chatbox = $this->cache->retrieve('Chatbox');
		}
		else
		{
			$offset = ChatboxModel::all()->count()-10;
			$chatbox = ChatboxModel::skip($offset)
				->take(10)
				->get();
			$this->cache->store('Chatbox', $chatbox);
		}
		$data['chatbox'][0] = 'no new shouts';
		
		foreach($chatbox as $shout){
			if($request->getParsedBody()['lastShout'] < $shout->id) 
			{	
				$user = $this->userdata->getData($shout->user_id);
				$avatar = $user->avatar ? $base_url.'/cache/img/'.$user->_38 : $base_url.'/public/img/avatar.png';
				$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($user->username).'/'.$shout->user_id;
				
				$data['chatbox'][$i] = file_get_contents(MAIN_DIR.'/skins/view/ajax/newshout.twig');
				
				$replace = [$shout->id, $uurl, $user->username, $user->name_html, $avatar, $shout->created_at, $shout->content];
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