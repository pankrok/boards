<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Board;
use Application\Core\Controller as Controller;

use Application\Models\BoardsModel;
use Application\Models\CategoryModel;

class AdminBoardController extends Controller
{
	
	public function index($request, $response, $arg)
	{

		$this->adminView->getEnvironment()->addGlobal('categories', self::getCategories());
		$this->adminView->getEnvironment()->addGlobal('boards', self::getBoards());

		return $this->adminView->render($response, 'board.twig');	
	
	}
	
	public function orderPost($request, $response)
	{
		$body = $request->getParsedBody();

		foreach($body as $k => $v)
		{
			
			$h = explode('-', $k);
			$id = $h[1];

			switch ($h[0]) {
				
				case 'Category':
					$data = CategoryModel::find($id);
					$data->category_order = $v;
					$data->active = false;
					$data->save();
					
				break;
				
				case 'CategoryChecbox':
					$data = CategoryModel::find($id);
					$data->active = true;
					$data->save();
					
				break;
				
				case 'Boards':
					$data = BoardsModel::find($id);
					$data->board_order = $v;
					$data->active = false;
					$data->save();
				break;
				case 'BoardChecbox':
					$data = BoardsModel::find($id);
					$data->active = true;
					$data->save();
				break;
				
			}

			
		}

		return $response
		  ->withHeader('Location', $this->router->urlFor('admin.boards'))
		  ->withStatus(302);;
		
	}
	
	public function addCategory($request, $response)
	{
		$body = $request->getParsedBody();
		
		CategoryModel::create([
			'name' => $body['name'],
			'category_order' => $body['order']		
		]);

		return $response
		  ->withHeader('Location', $this->router->urlFor('admin.boards'))
		  ->withStatus(302);
	}
	
	public function addBoard($request, $response)
	{
		
		$body = $request->getParsedBody();
		
		BoardsModel::create([
			'board_name' => $body['name'],
			'board_description' => $body['desc'],
			'category_id' => $body['cat_id'],
			'board_order' => $body['order'],
			'visability' => 1
		]);
		
		return $response
		  ->withHeader('Location', $this->router->urlFor('admin.boards'))
		  ->withStatus(302);
	}
	
	public function deleteBoard($request, $response, $arg)
	{
		
		if(isset($request->getParsedBody()['confirm']) && $request->getParsedBody()['element'] === 'board')
		{
			BoardsModel::find($request->getParsedBody()['id'])->delete();
			$this->cache->clearCache();
			return $response
			  ->withHeader('Location', $this->router->urlFor('admin.boards'))
			  ->withStatus(302);
			
		}
		
		if(isset($request->getParsedBody()['confirm']) && $request->getParsedBody()['element'] === 'category')
		{
			CategoryModel::find($request->getParsedBody()['id'])->delete();
			return $response
			  ->withHeader('Location', $this->router->urlFor('admin.boards'))
			  ->withStatus(302);
			
		}
		
		$this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
		$this->adminView->getEnvironment()->addGlobal('element', $arg['element']);
		return $this->adminView->render($response, 'admin_delete.twig');
	}
	
	public function editBoard($request, $response, $arg)
	{
		$body = $request->getParsedBody();
		$board = BoardsModel::find($arg['id']);

		if($body)
		{
			$board->board_name = $body['name'];
			$board->board_description = $body['desc'];
			$board->category_id = $body['cat_id'];
			$board->board_order = $body['order'];
			$board->visability	= $body['visability'];
			$board->save();
		}
		
		$this->adminView->getEnvironment()->addGlobal('categories', self::getCategories());
		$this->adminView->getEnvironment()->addGlobal('data', $board->toArray());
		$this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
		
		return $this->adminView->render($response, 'board_edit.twig');
	}
	
	
	protected function getCategories()
	{
		$categories = \Application\Models\CategoryModel::orderBy('category_order', 'DESC')->get(); 
		return $categories;	
	}	
	
	protected function getBoards()
	{
		
		$handler = \Application\Models\BoardsModel::orderBy('category_id')->orderBy('board_order', 'DESC')->get()->toArray();
		foreach($handler as $k => $v)
		{
			$boards[$v['category_id']][$v['id']] = $v;	
		}
		return $boards;
	}
	
	
};

