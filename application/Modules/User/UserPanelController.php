<?php

namespace Application\Modules\User;

use Application\Models\UserModel;
use Application\Models\UserDataModel;
use Application\Models\ImagesModel;
use Application\Core\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as v;

 /**
 * User Panel controller
 * @package BOARDS Forum
 */

class UserPanelController extends Controller
{
	
	private function moveUploadedFile($directory, $uploadedFile)
	{
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
		$filename = sprintf('%s.%0.8s', $basename, $extension);

		$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

		return $filename;
	}

	
	public function getProfile($request, $response, $arg)
	{
		if(is_numeric($arg['uid']))
		{
			if(isset($_SESSION['user']) && $arg['uid'] == $_SESSION['user'])
			{
				$canEdit = true;
			}
			else
			{
				$canEdit = false;
			}
			
			$data = UserModel::find($arg['uid']);
			if($data->avatar != null)
				$data->join('images', 'users.avatar', '=', 'images.id')->get();
			
			$this->view->getEnvironment()->addGlobal('profile', $data);
			$this->view->getEnvironment()->addGlobal('can_edit', $canEdit);
			$this->view->getEnvironment()->addGlobal('title', $data->username);
			$this->view->getEnvironment()->addGlobal('id', $arg['uid']);
			
			/*$data = $this->userdata->getData($arg['uid']);
			
			
			$this->view->getEnvironment()->addGlobal('id', $arg['uid']);
			$this->view->getEnvironment()->addGlobal('profile', $data);
			$this->view->getEnvironment()->addGlobal('additional', $this->userdata->getAdditionalData($arg['uid']));
			$this->view->getEnvironment()->addGlobal('posts', $this->userdata->getPosts($arg['uid']));
			$this->view->getEnvironment()->addGlobal('title', $data->username);
			*/
			
		}
		return $this->view->render($response, 'user/profile.twig');
	}
	
	public function postProfilePicture($request, $response)
	{
	
			$uploadedFiles = $request->getUploadedFiles();
			$user = UserModel::find($this->auth->user()['id']);

			if($user->avatar != NULL)
			{
				$delete = ImagesModel::find($user['avatar']);
				@unlink(MAIN_DIR.'/public/upload/avatars'.$delete->original);
				@unlink(MAIN_DIR.'/public/upload/avatars'.$delete->_38);
				@unlink(MAIN_DIR.'/public/upload/avatars'.$delete->_85);
				@unlink(MAIN_DIR.'/public/upload/avatars'.$delete->_150);
				$user->avatar = null;
				$user->save();
				$delete->delete();
				$this->cache->eraseAll();	
			}
			
			// handle single input with single file upload
			$uploadedFile = $uploadedFiles['avatar'];
			if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				$filename = self::moveUploadedFile(MAIN_DIR.'/public/upload/avatars/', $uploadedFile); 	
				
				$cache = $this->images->resize($filename);		
				
				$image = ImagesModel::create([
					'original' => $filename,
					'_38' => $cache[0],
					'_85' => $cache[1],
					'_150' => $cache[2]
				]);
				
				// cache here
				
				$user->avatar = $image->id;
				$user->save();
			}
			return $response;
		
	}
	
	public function postChangeData($request, $response)
	{
		if($this->auth->check())
		{
			
			$user = UserDataModel::where('user_id', $_SESSION['user'])->get()->first();		
		
			if(!$user) $user = UserDataModel::create(['user_id' => $_SESSION['user']]);
				
			if($request->getParsedBody()['name']) $user->name = $request->getParsedBody()['name'];
			if($request->getParsedBody()['surname']) $user->surname = $request->getParsedBody()['surname'];
			if($request->getParsedBody()['sex']) $user->sex = $request->getParsedBody()['sex'];
			if($request->getParsedBody()['website'])
			{
				 $disallowed = array('http://', 'https://');
				   foreach($disallowed as $d) {
					  if(strpos($request->getParsedBody()['website'], $d) === 0) {
						 $user->website =  str_replace($d, '', $url);
					  }
					  else
					  {
						$user->website = $request->getParsedBody()['website'];  
					  }
				   }			
			}
			if($request->getParsedBody()['bday']) $user->bday = $request->getParsedBody()['bday'];
			if($request->getParsedBody()['location']) $user->location = $request->getParsedBody()['location'];
			
			$validation = $this->validator->validate($request, [
				'password'      => v::noWhitespace()->notEmpty(),
				'vpassword'      => v::notEmpty()->equals($request->getParsedBody()['password'])
            ]);
        
			if (!$validation->faild())
			{
				$password = $this->auth->user();
				$password->password = password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT);
				$password->save();
			}
		
			$validation = $this->validator->validate($request, [
				'email'   => v::noWhitespace()->notEmpty()->email()->EmailAvailble(), 
            ]);
        
			if (!$validation->faild())
			{
				$mail = $this->auth->user();
				$mail->password = password_hash($request->getParsedBody()['email'], PASSWORD_DEFAULT);
				$mail->save();
			}
		$user->save();
		}
		$user	= $this->auth->user();
		
		return $response
				->withHeader('Location', $this->router->urlFor('home'))
				->withStatus(302);
	}
	
}
