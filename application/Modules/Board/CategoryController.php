<?php

declare(strict_types=1);
namespace Application\Modules\Board;

use Application\Core\Controller;
use Application\Models\BoardsModel;
use Application\Models\PlotsModel;
use Application\Models\PostsModel;
use Application\Models\CategoryModel;

class CategoryController extends Controller 
{
	
	public function getCategory($request, $response, $arg)
	{
		if(is_numeric($arg['category_id']))
		{
			$data = BoardsModel::where('category_id', $arg['category_id'])->get()->toArray();
			$category = CategoryModel::find($arg['category_id'])->get()->toArray()[0];
		
			foreach($data as $k => $v)
			{
				
				$data[$k]['url'] =  self::base_url() . '/board/' . $this->urlMaker->toUrl($v['board_name']) . '/' . $v['id'];
				$lastpost = PlotsModel::orderBy('updated_at', 'DESC')
													->where('board_id', '=', $v['id'])
													->leftJoin('users', 'users.id', 'plots.author_id')
													->select('plots.*', 'users.username')
													->first();
				
				
				if(isset($lastpost))
				{
					$lastpost->toArray();
					$lastPostId = PostsModel::orderBy('updated_at', 'DESC')
											->where('plot_id', '=', $lastpost['id'])
											->first()->id;
				
					$count =  ceil(PlotsModel::where('board_id', '=', $v['id'])->count() / $this->settings['pagination']['plots']);						
					$data[$k]['last_post_url'] = self::base_url() 
												. '/plot/' 
												. $this->urlMaker->toUrl($lastpost['plot_name'])	
												. '/' 
												. $lastpost['id'] 
												. '/' 
												. $count
												. '#post-' . $lastPostId;
 
					$data[$k]['plot_name'] = $lastpost['plot_name'];	
					$data[$k]['last_post_author_url'] = self::base_url() . '/user/' . $this->urlMaker->toUrl($lastpost['username'])	. '/' . $lastpost['author_id'];
				}	
					
			}
		}
		
		$this->view->getEnvironment()->addGlobal('boards', $data);
		$this->view->getEnvironment()->addGlobal('category', $category);
		
		return $this->view->render($response, 'category.twig');
	}
	
	
}