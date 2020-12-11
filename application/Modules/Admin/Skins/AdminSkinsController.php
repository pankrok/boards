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
		return $this->adminView->render($response, 'add_skin.twig');
	}
	
	public function addSkinPost($request, $response)
	{
		$skinDir = $request->getParsedBody()['skin_name'];
		if(file_exists(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'))
		{
			$paths = [];
			$skinData = json_decode(file_get_contents(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'), true);

			$skin =	SkinsModel::Create([
				'name' => $skinData['name'],
				'dirname' => $skinDir,
				'author' => $skinData['author'],
				'version' => $skinData['version']			
				]);
			
			foreach($skinData['assets']['css'] as $v)
			{
				$minifier = new Minify\CSS();
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/css/'.$v);
				$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/css/'. md5($v).'.min.css');
				$vname = explode('.', $v)[0];
				$paths['css'][$vname] =  '<link rel="stylesheet" href="'. self::base_url() . '/skins/' . $skinDir . '/cache/css/'. md5($v).'.min.css">';
			}
				
			foreach($skinData['assets']['js'] as $v)
			{
				$minifier = new Minify\JS();
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/js/'.$v);
				$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/js/'. md5($v).'.min.js');
				$vname = explode('.', $v)[0];
				$paths['js'][$vname] = '<script type="text/javascript" src="' .self::base_url() . '/skins/' . $skinDir . '/cache/js/'. md5($v).'.min.js"></script>';
			}
			if(!isset($paths['css'])) $paths['css'] = null;
			if(!isset($paths['js'])) $paths['js'] = null;
			
			
			file_put_contents(MAIN_DIR . '/skins/' . $skinDir . '/cache_assets.json', json_encode($paths));
			$this->flash->addMessage('success', 'skin added');
		}
		
		return $response;
	}
	
	public function editSkin($request, $response)
	{
		return $response;
	}
	
	public function setSkinDefault($request, $response)
	{
		if(isset($request->getParsedBody()['skin_dir']))
		{
			$settings = $this->settings;
			
			$skin = SkinsModel::where('dirname', $settings['twig']['skin'])->first();
			$skin->active = 0;
			$skin->save();
			unset($skin);
			
			$settings['twig']['skin'] = $request->getParsedBody()['skin_dir'];
			
			$skin = SkinsModel::where('dirname', $settings['twig']['skin'])->first();
			$skin->active = 1;
			$skin->save();
			unset($skin);
			
			$settings = json_encode($settings, JSON_PRETTY_PRINT);			
			
			$oldCacheName = $this->cache->getName();
			$this->cache->setName('box-controller');
			$this->cache->delete('boxes');
			$this->cache->setName($oldCacheName);
			
			file_put_contents(MAIN_DIR . '/environment/Config/settings.json', $settings);
			$this->flash->addMessage('success', 'skin set as default');
		}
		return $response
		  ->withHeader('Location', $this->router->urlFor('admin.skinlist'))
		  ->withStatus(302);
	}
	
	public function reloadCssJs($request, $response)
	{
		$skinDir = $request->getParsedBody()['skin_dir'];
		if(file_exists(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'))
		{
			$paths = [];
			$skinData = json_decode(file_get_contents(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'), true);		
			
						foreach($skinData['assets']['css'] as $v)
			{
				$minifier = new Minify\CSS();
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/css/'.$v);
				$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/css/'. md5($v).'.min.css');
				$vname = explode('.', $v)[0];
				$paths['css'][$vname] =  '<link rel="stylesheet" href="'. self::base_url() . '/skins/' . $skinDir . '/cache/css/'. md5($v).'.min.css">';
			}
				
			foreach($skinData['assets']['js'] as $v)
			{
				$minifier = new Minify\JS();
				$minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/js/'.$v);
				$minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/js/'. md5($v).'.min.js');
				$vname = explode('.', $v)[0];
				$paths['js'][$vname] = '<script type="text/javascript" src="' .self::base_url() . '/skins/' . $skinDir . '/cache/js/'. md5($v).'.min.js"></script>';
			}
			
			if(!isset($paths['css'])) $paths['css'] = null;
			if(!isset($paths['js'])) $paths['js'] = null;
			
			
			file_put_contents(MAIN_DIR . '/skins/' . $skinDir . '/cache_assets.json', json_encode($paths));
			$this->flash->addMessage('success', 'assets reloaded');
		}
		
		return $response 
				->withHeader('Location', $this->router->urlFor('admin.skinlist'))
				->withStatus(302);
	}
	
	public function removeSkin($request, $response)
	{
		if(isset($request->getParsedBody()['confirm_delete']))
		{
			$skinId = $request->getParsedBody()['skin_id']; 
			$skin = SkinsModel::find($skinId);
			if($skin->active)
			{
				$this->flash->addMessage('danger', 'you cannot delete active skin');	
			}
			else
			{
				
				$dirPath = MAIN_DIR.'/skins/'.$skin->dirname;
				//self::deleteDir($dirPath);
				
				$skin->delete();
				echo 'removed';
			}
			
			$this->flash->addMessage('success', 'skin deleted');
		}
		else
		{
			$this->flash->addMessage('warning', 'select checbox to delete skin');
		}
		return $response 
				->withHeader('Location', $this->router->urlFor('admin.skinlist'))
				->withStatus(302);;
	}
	
	public function renameSkin($request, $response)
	{
		$body = $request->getParsedBody();
		if(isset($body['skin_id']) && isset($body['skin_name']))
		{
			$skin = SkinsModel::find($body['skin_id']);
			$skin->name = $body['skin_name'];
			$skin->save();
			
			$this->flash->addMessage('success', 'skin name changed');
		}
		else
		{
			$this->flash->addMessage('danger', 'skin name cannot be empty');
		}
		return $response 
				->withHeader('Location', $this->router->urlFor('admin.skinlist'))
				->withStatus(302);
		
	}
	
	protected function deleteDir($dirPath)
	{
				if (! is_dir($dirPath)) {
					throw new InvalidArgumentException("$dirPath must be a directory");
				}
				if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
					$dirPath .= '/';
				}
				$files = glob($dirPath . '*', GLOB_MARK);
				foreach ($files as $file) {
					if (is_dir($file)) {
						self::deleteDir($file);
					} else {
						unlink($file);
					}
				}
				rmdir($dirPath);

	}
}