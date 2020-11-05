<?php

declare(strict_types=1);
namespace Application\Modules\Board;

use Application\Core\Controller;
use Application\Models\BoardsModel;
use Application\Models\CategoryModel;

class CategoryController extends Controller 
{
	
	public function getCategory($request, $response, $arg)
	{
		if(is_numeric($arg['category_id']))
		{
			$data = BoardsModel::where('category_id', $arg['category_id'])->get()->toArray();
			$category = CategoryModel::find($arg['category_id'])->get()->toArray()[0];
		}
		$this->view->getEnvironment()->addGlobal('boards', $data);
		$this->view->getEnvironment()->addGlobal('category', $category);
		
		return $this->view->render($response, 'category.twig');
	}
	
	
}