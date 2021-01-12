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
    
	/**
    * Remove session and redirect to home page
    *
    * @param object $request
	* @param object $response
    * @return object
    **/
	
	public function getChatMesasages()
	{
			$base_url = self::base_url();
			$offset = ChatboxModel::all()->count()-10;
			$data = ChatboxModel::skip($offset)
								->take(10)
								->join('users', 'users.id', '=', 'chatbox.user_id')
								->leftJoin('images', 'users.avatar', '=', 'images.id')
								->select('chatbox.*', 'images._38', 'users.username', 'users.main_group', 'users.avatar')
								->get();
			foreach($data as $k => $v)
			{
				$group = $this->group->getGroupDate($v->main_group, $v->username);
				$v->avatar ? $v->avatar = $base_url. $this->settings['images']['path'] .$v->_38 : $v->avatar = $base_url.'/public/img/avatar.png';
				$v->uurl = $base_url.'/user/'.$this->urlMaker->toUrl($v->username).'/'.$v->user_id;
				$data[$k]->username_html = $group['username'];
			}
						
		if($data) $this->view->getEnvironment()->addGlobal('chatbox', $data);
	}
	
	public function postChatMessage($request, $response)
	{
		$this->auth->checkBan();
		$base_url = self::base_url();
		$user = UserModel::leftJoin('images', 'users.avatar', '=', 'images.id')
						->find($_SESSION['user']);		
		
		
		$token = ($this->container->get('csrf')->generateToken());
		$data['csrf'] = self::csftToken();
	
		if (isset($request->getParsedBody()['shout']) && $request->getParsedBody()['shout'] != '' && $user)
		{	
	
			$shout = ChatboxModel::create([
				'user_id' => $_SESSION['user'],
				'content' => $this->purifier->purify($request->getParsedBody()['shout'])
			]);
			
			$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($user->username).'/'.$_SESSION['user'];
			$avatar = $user->avatar ? $base_url. $this->settings['images']['path'] .$user->_38 : $base_url.'/public/img/avatar.png';
			$username_html = $this->group->getGroupDate($user->main_group, $user->username);
			

			$this->view->getEnvironment()->addGlobal('shout', [
				'id' =>		$shout->id, 
				'uurl' =>		$uurl, 
				'username_html' =>		$username_html['username'], 
				'avatar' =>		$avatar, 
				'updated_at' =>		$shout->created_at, 
				'content' =>		$shout->content]);
			
			$data['shout'] = $this->view->fetch('templates/partials/boxes/oneShout.twig');

		}
			
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);
	}
	
	public function loadMoreMessages($request, $response){
		
		$base_url = self::base_url();
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
			->leftJoin('images', 'users.avatar', '=', 'images.id')
			->select('chatbox.*', 'images._38', 'users.username', 'users.avatar', 'users.main_group')
			->get();
			
		$data['chatbox'][0] = '<span id="scrollHere"></span>';
		foreach($chatbox as $shout)
		{
			$avatar = $shout->avatar ? $base_url. $this->settings['images']['path'] .$shout->_38 : $base_url.'/public/img/avatar.png';
			$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($shout->username).'/'.$shout->user_id;

			$username_html = $this->group->getGroupDate($shout->main_group, $shout->username);

			
			$this->view->getEnvironment()->addGlobal('shout', [
				'id' =>		$shout->id, 
				'uurl' =>		$uurl, 
				'username_html' =>		$username_html['username'], 
				'avatar' =>		$avatar, 
				'updated_at' =>		$shout->created_at, 
				'content' =>		$shout->content]);
			
			
			$data['chatbox'][$i] = $this->view->fetch('templates/partials/boxes/oneShout.twig');
			$i++;
		}
		
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);		
	}
	
	public function checkNewMessage($request, $response){
		
		$base_url = self::base_url();
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
				$user = UserModel::leftJoin('images', 'users.avatar', '=', 'images.id')
					->select('users.*', 'images._38')
					->find($shout->user_id);
				$avatar = $user->avatar ? $base_url. $this->settings['images']['path'] .$user->_38 : $base_url.'/public/img/avatar.png';
				$uurl = $base_url.'/user/'.$this->urlMaker->toUrl($user->username).'/'.$shout->user_id;
				$user->username = $this->group->getGroupDate($user->main_group, $user->username)['username'];
				
				$this->view->getEnvironment()->addGlobal('shout', [
				'id' =>		$shout->id, 
				'uurl' =>		$uurl, 
				'username_html' =>	$user->username, 
				'avatar' =>		$avatar, 
				'updated_at' =>		$shout->created_at, 
				'content' =>		$shout->content]);
					
				$data['chatbox'][$i] = $this->view->fetch('templates/partials/boxes/oneShout.twig');
				$i++;
			}
		}

		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);	
	}
}