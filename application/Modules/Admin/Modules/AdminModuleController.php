<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Modules;
use Application\Core\Controller as Controller;

class AdminModuleController extends Controller
{
	
	public function index($request, $response, $arg)
	{

		$modules = self::getAllModules($arg['id');
	
		$this->adminView->getEnviroment()->addGlobal('modules', $modules);
		
		return $this->adminView->render($response, 'module.twig');
	
	}
	
	
	protected function getAllModules($activeSkin)
	{
		
		$positions = ['top', 'left', 'right', 'bottom'];
	
		foreach($positions as $position)
		{
			$boxes[$position] = SkinsBoxesModel::where([['side', $position], ['skin_id', $activeSkin]])
							->leftJoin('boxes', 'skins_boxes.box_id', 'boxes.id')
							->leftJoin('costum_boxes', 'boxes.costum_id', 'costum_boxes.id')
							->select('skins_boxes.side', 'skins_boxes.box_order', 'skins_boxes.active', 'boxes.costum_id', 'boxes.engine', 'costum_boxes.translate', 'costum_boxes.name_prefix', 'costum_boxes.name', 'costum_boxes.html')
							->orderBy('skins_boxes.box_order', 'desc')->get()->toArray();
			
			
			foreach($boxes[$position] as $k => $v)
			{
				if($boxes[$position][$k]['active'] = json_decode($v['active'], true)[$name])
				{
					if($v['translate'])
					{
						$boxes[$position][$k]['name'] = $this->container->get('translator')->trans('lang.'.$v['name']);				
					}
				}
				else
				{
					unset($boxes[$position][$k]);
				}
			}
		}
		
		return $boxes;
	}
	
	
	
};

