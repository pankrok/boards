<?php

namespace Admin\Controllers\Front;

use App\Controllers\Controller;
use App\Models\PostsModel;

class AdminFrontBoardController extends Controller
{
	
	protected function csftToken()
	{
		$token = ($this->container->get('csrf')->generateToken());
		return [
			'csrf_name' => $token['csrf_name'],
			'csrf_value' => $token['csrf_value']
		];
	}
	
	public function editPost($request, $response)
	{
		$data['token'] = self::csftToken();
		$id = ($request->getParsedBody()['post_id']);
		
		$post = PostsModel::where('id', $id)->first();
		
		$this->cache->setCache('plot-'.$post->plot_id);
		$lastpage = ceil(PostsModel::where('plot_id', $post->plot_id)->count() / 10);
		if($this->cache->isCached('-page-'.$lastpage)) $this->cache->erase('-page-'.$lastpage);
		
		$post->content = $request->getParsedBody()['content'];
		$post->save();
		$data['last'] = $lastpage;
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')
						->withStatus(201);
		
	}
	
}