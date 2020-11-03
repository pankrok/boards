<?php

namespace Admin\Controllers;

use App\Controllers\Controller;
use App\Models\CategoryModel;
use App\Models\BoardsModel;

 /**
 * Authentication controller
 * @package BOARDS Forum
 */

class AdminBoardsController extends Controller
{
	
	public function managment($request, $response)
    {
		$categories = CategoryModel::select('category_name', 'id', 'category_order')
								->where('category_active', '1')
								->orderby('category_order', 'asc')
								->get();
								
								
			foreach($categories as $key => $v)
			{
				$boards = BoardsModel::select('id', 'board_name', 'board_description')
									->where([
										['board_active', '=', '1'],
										['category_id', '=', $v->id]])
									->orderby('board_order', 'asc')
									->get();
									
				$data[$key] = [					
					'name' =>$v->category_name,
					'cat_id' => $v->id,	
					'boards' =>  $boards,	
					];
			}
			
		$this->view->getEnvironment()->addGlobal('categories', $data);
		
		return $this->view->render($response, 'managment.twig');
	}
	
	public function postManagment($request, $response)
    {
		return $this->view->render($response, 'managment.twig');
	}
	
	public function boardorder($request, $response)
	{
		$i = 0;
		$token = ($this->container->get('csrf')->generateToken());
		$data['csrf'] = [
			'csrf_name' => $token['csrf_name'],
			'csrf_value' => $token['csrf_value']
		];
		
		$boards = explode('&', $request->getParsedBody()['order']);

		foreach($boards as $v)
		{
			
			$id = substr($v, 7);
			if(substr($v, 0, 4) == 'item'){
				$handler = BoardsModel::where('id', $id)->first();
				$handler->board_order = $i;
			}
			if(substr($v, 0, 4) == 'cate'){
				$handler = CategoryModel::where('id', $id)->first();
				$handler->category_order = $i;
			}
			$handler->save();	
			$i++;
		}
		
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);
	}
	
}