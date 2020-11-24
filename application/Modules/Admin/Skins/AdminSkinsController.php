<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Skins;
use Application\Core\Controller as Controller;
use Application\Models\SkinsModel;
use MatthiasMullie\Minify;

class AdminSkinsController extends Controller
{

	public function skinsList($request, $response, $arg)
	{
		file_put_contents(MAIN_DIR . '/cfg.json', json_encode( $this->settings, JSON_PRETTY_PRINT));
		if(isset($arg['page']) && $arg['page'] > 0)
        {
			$currentPage = $arg['page'];
        }
    	else
        {
        	$currentPage = 1;
        }
		
		$totalItems = SkinsModel::count();
		$itemsPerPage = $this->settings['pagination']['plots'];
		
		$urlPattern = self::base_url().  $this->settings['core']['admin'] . '/skinslist/(:num)';
		$paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
		
		$data = SkinsModel::skip(($paginator->getCurrentPage()-1)*$paginator->getItemsPerPage())
				->take($paginator->getItemsPerPage())->get()->toArray();
		
		$this->adminView->getEnvironment()->addGlobal('skins', $data);

		return $this->adminView->render($response, 'skinslist.twig');	
		
	}
	
	public function addSkin($request, $response)
	{
		$dir    = MAIN_DIR . '/skins';
		$files = scandir($dir);
		
		$diff = SkinsModel::select('dirname')->get()->toArray();
		foreach($diff as $k => $v)
		{
			$diff[$k] = $v['dirname'];
		}
		
		$files  = array_diff($files, $diff);
		$files  = array_diff($files, ['.', '..']);

		$this->adminView->getEnvironment()->addGlobal('skins', $files);
		return $this->adminView->render($response, 'addSkin.twig');
	}
	
	public function addSkinPost($request, $response)
	{
		$skinDir = $request->getParsedBody()['skin_name'];
		if(file_exists(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'))
		{
		
			$skinData = json_decode(file_get_contents(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'), true);

			$skin =	SkinsModel::Create([
				'name' => $skinData['name'],
				'dirname' => $skinDir,
				'author' => $skinData['author'],
				'version' => $skinData['version']			
				]);
			
			$minifier = new Minify\CSS();
			foreach($skinData['assets']['css'] as $v)
			{
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/css/'.$v);
			}
			$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/css/'. md5($skinData['name']).'.min.css');
			
			$minifier = new Minify\JS();
			foreach($skinData['assets']['js'] as $v)
			{
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/js/'.$v);
			}
			$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/js/'. md5($skinData['name']).'.min.js');
			
			$paths = [
				'css' => '<link rel="stylesheet" href="'. self::base_url() . '/skins/' . $skinDir . '/cache/css/'. md5($skinData['name']).'.min.css">',			
				'js' => '<script type="text/javascript" src="' .self::base_url() . '/skins/' . $skinDir . '/cache/js/'. md5($skinData['name']).'.min.js"></script>'
			];
			
			file_put_contents(MAIN_DIR . '/skins/' . $skinDir . '/cache_assets.json', json_encode($paths));
		}
		
		return $response;
	}
	
	public function editSkin($request, $response)
	{
		
		
		return $response;
	}
	
	public function reloadCssJs($request, $response)
	{
		$skinDir = $request->getParsedBody()['skin_dir'];
		if(file_exists(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'))
		{
		
			$skinData = json_decode(file_get_contents(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'), true);		
			$minifier = new Minify\CSS();
			foreach($skinData['assets']['css'] as $v)
			{
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/css/'.$v);
			}
			$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/css/'. md5($skinData['name']).'.min.css');
			
			$minifier = new Minify\JS();
			foreach($skinData['assets']['js'] as $v)
			{
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/js/'.$v);
			}
			$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/js/'. md5($skinData['name']).'.min.js');
			
			$paths = [
				'css' => '<link rel="stylesheet" href="'. self::base_url() . '/skins/' . $skinDir . '/cache/css/'. md5($skinData['name']).'.min.css">',			
				'js' => '<script type="text/javascript" src="' .self::base_url() . '/skins/' . $skinDir . '/cache/js/'. md5($skinData['name']).'.min.js"></script>'
			];
			
			file_put_contents(MAIN_DIR . '/skins/' . $skinDir . '/cache_assets.json', json_encode($paths));
		}
		
		return $response;
	}
	
	public function removeSkin($request, $response)
	{
		if($request->getParsedBody()['confirm_delete'])
		{
			$skinId = $request->getParsedBody()['skin_id']; 
			$skin = SkinsModel::find($skinId);
			var_dump($skin->delete());
			return $response;
		}
		return $response;
	}
	

	
}