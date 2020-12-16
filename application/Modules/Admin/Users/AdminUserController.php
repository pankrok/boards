<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Users;
use Application\Core\Controller as Controller;
use Application\Models\UserModel;

class AdminUserController extends Controller
{
	
	public function index($request, $response, $arg)
	{
		
		$page = $arg['page'] ?? 1;
		
		$data = self::getUsers($page);

		$this->adminView->getEnvironment()->addGlobal('users',$data['users']);
		$this->adminView->getEnvironment()->addGlobal('paginator',$data['paginator']);

		return $this->adminView->render($response, 'users_list.twig');
	
	}
	
	public function editUser($request, $response, $arg)
	{
		return $this->adminView->render($response, 'user_edit.twig');
	}
	
	public function saveUserData($request, $response)
	{
		
	}
	
	protected function getUsers($currentPage)
	{
			
		$totalItems = UserModel::count();
		$itemsPerPage = $this->settings['pagination']['users'];		
		$urlPattern = self::base_url().'/acp/users/list/(:num)';

		$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);	
		
		$users = UserModel::skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
				->take($paginator->getItemsPerPage())
				->get()->toArray();
		return ['users' => $users, 'paginator' => $paginator];
	}
	
	
	
};

