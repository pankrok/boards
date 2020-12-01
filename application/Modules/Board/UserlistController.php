<?php

declare(strict_types=1);

namespace Application\Modules\Board;
use Application\Core\Controller as Controller;
use Application\Models\UserModel;

class UserlistController extends Controller
{
	
	public function getList($request, $response, $arg)
	{
		$users = UserModel::get()->makeHidden(['password'])->toArray();
		
		$this->view->getEnvironment()->addGlobal('users',$users);
		return $this->view->render($response, 'userlist.twig');	;
	}
	
}