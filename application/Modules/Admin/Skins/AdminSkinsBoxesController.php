<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Skins;

use Application\Core\Controller as Controller;
use Application\Models\SkinsBoxesModel;
use Application\Models\BoxModel;
use Application\Models\CostumBoxModel;

class AdminSkinsBoxesController extends Controller
{
    public function index($request, $response, $arg)
    {
        $skinModules = self::getSkinModules($arg['id'], $arg['route']);
        $allModules = self::getAllModules();
        $pages = self::createPagesPreview($arg['route'], $arg['id']);
        
        $this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
        $this->adminView->getEnvironment()->addGlobal('route', $arg['route']);
        $this->adminView->getEnvironment()->addGlobal('skin_modules', $skinModules);
        $this->adminView->getEnvironment()->addGlobal('modules', $allModules);
        $this->adminView->getEnvironment()->addGlobal('pages', $pages);
        return $this->adminView->render($response, 'module.twig');
    }
    
    public function editModule($request, $response, $arg)
    {
        if (isset($arg['id'])) {
            $box = BoxModel::find($arg['id']);
            if ($box->costum) {
                $costumBox = CostumBoxModel::find($box->costum_id);
            } else {
                $costumBox	= null;
            }
            $skinBoxes = SkinsBoxesModel::where([['skin_id', $arg['skin_id']], ['box_id', $arg['id']]])->first();
            
            if ($skinBoxes) {
                $skinBoxes->toArray();
                $skinBoxes['active'] = json_decode($skinBoxes['active'], true);
            } else {
                $skinBoxes['active'] = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/box_cache.json'), true);
                foreach ($skinBoxes['active'] as $k => $v) {
                    unset($skinBoxes['active'][$k]);
                    $skinBoxes['active'][$v] = false;
                }
            }
            
            $this->adminView->getEnvironment()->addGlobal('module', [
                                                                'box' => $box,
                                                                'skin_boxes' => $skinBoxes,
                                                                'costum_box' => $costumBox
                                                                ]);
        } else {
            $skinBoxes['active'] = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/box_cache.json'), true);
            foreach ($skinBoxes['active'] as $k => $v) {
                unset($skinBoxes['active'][$k]);
                $skinBoxes['active'][$v] = false;
            }
            
            $box = ['costum' => 1];
            $this->adminView->getEnvironment()->addGlobal('module', ['box' => $box,'skin_boxes' => $skinBoxes]);
        }
        $this->adminView->getEnvironment()->addGlobal('skin_id', $arg['skin_id']);
        
            
        $this->cache->cleanAllSkinsCache();
        return $this->adminView->render($response, 'edit_module.twig');
    }
    
    protected function getAllModules()
    {
        $boxes = BoxModel::leftJoin('costum_boxes', 'boxes.costum_id', 'costum_boxes.id')
                            ->select('boxes.*', 'costum_boxes.name', 'costum_boxes.translate')
                            ->get()->toArray();
        
        foreach ($boxes as $k => $v) {
            if ($v['translate']) {
                $boxes[$k]['name'] = $this->container->get('translator')->get('module.'.$v['name']);
            }
        }
        
        return $boxes;
    }
    
    protected function getSkinModules($activeSkin, $name)
    {
        $positions = ['top', 'left', 'right', 'bottom'];
    
        foreach ($positions as $position) {
            $boxes[$position] = SkinsBoxesModel::where([['side', $position], ['skin_id', $activeSkin]])
                            ->leftJoin('boxes', 'skins_boxes.box_id', 'boxes.id')
                            ->leftJoin('costum_boxes', 'boxes.costum_id', 'costum_boxes.id')
                            ->select('skins_boxes.id', 'skins_boxes.box_id', 'skins_boxes.side', 'skins_boxes.box_order', 'skins_boxes.active', 'boxes.costum_id', 'boxes.engine', 'costum_boxes.translate', 'costum_boxes.name_prefix', 'costum_boxes.name', 'costum_boxes.html')
                            ->orderBy('skins_boxes.box_order', 'desc')->get()->toArray();
            
            
            foreach ($boxes[$position] as $k => $v) {
                if (isset(json_decode($v['active'], true)[$name])) {
                    $active = json_decode($v['active'], true)[$name];
                    if ($active) {
                        if ($v['translate']) {
                            $boxes[$position][$k]['name'] = $this->container->get('translator')->get('module.'.$v['name']);
                        }
                    } else {
                        unset($boxes[$position][$k]);
                    }
                } else {
                    unset($boxes[$position][$k]);
                }
            }
        }
        
        return $boxes;
    }
    
    protected function createPagesPreview($current, $id)
    {
        $pages = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/box_cache.json'), true);
        
        foreach ($pages as $k => $v) {
            $v == $current ? $active = 'active' : $active = '';
            $url = $this->router->urlFor('admin.modules.skin.get', ['route' => $v, 'id' => $id]);
            $name = $this->translator->get('admin.'.$v);
            $pages[$k] = '<li class="nav-item">
			<a class="nav-link '. $active .'" href="'. $url .'">' . $name . '</a>
		  </li>';
        }
        return $pages;
    }
    

    public function saveModule($request, $response)
    {
        $body = $request->getParsedBody();

        $body['costum_box_id'] = $body['costum_box_id'] ?? 0;
        $body['skins_boxes_id'] = $body['skins_boxes_id'] ?? 0;
        $body['box_id'] = $body['box_id'] ?? 0;

        if ($body['costum_box_id'] != 'system') {
            if (CostumBoxModel::where('name', $body['module_name'])->first() && $body['costum_box_id'] === 0) {
                $this->flash->addMessage('danger', 'module name shoud be unique!');
                return $response
                ->withHeader('Location', $this->router->urlFor('admin.modules.skin.get', ['route' => 'home', 'id' => $body['skin_id']]))
                ->withStatus(302);
            }
            $costum_box = CostumBoxModel::updateOrCreate(['id' => $body['costum_box_id']], [
                'name_prefix' => $body['module_name_prefix'],
                'name' => $body['module_name'],
                'html' => $body['module_html'],
            ]);
            
            $active = [	'home' => (int)$body['home'],
                                'category.getCategory' => (int) $body['category_getCategory'],
                                'board.getBoard' =>	(int) $body['board_getBoard'],
                                'board.getPlot' =>	(int) $body['board_getPlot'],
                                'board.newPlot' =>	(int) $body['board_newPlot'],
                                'auth.signin' =>	(int) $body['auth_signin'],
                                'auth.signup' =>	(int) $body['auth_signup'],
                                'user.profile' =>	(int) $body['user_profile'],
                                'userlist' =>	(int) $body['userlist']
                                ];
            $box = BoxModel::updateOrCreate(['id' => $body['box_id']], [
            'costum_id' => $costum_box->id
            ]);
        } else {
            $active = [	'home' => (int)$body['home'] ];
            $box = BoxModel::find($body['box_id']);
            $costum_box = true;
        }
        
        
        
        $skins_boxes = SkinsBoxesModel::updateOrCreate(['id' => $body['skins_boxes_id']], [
                            'skin_id' => $body['skin_id'],
                            'box_id' => $box->id,
                            'side' => $body['position'],
                            'box_order' => (int)$body['box_order'],
                            'active' => json_encode($active)
            ]);
        
        if ($skins_boxes && $box && $costum_box) {
            $this->flash->addMessage('success', 'module added');
            $this->cache->cleanAllSkinsCache();
        } else {
            $this->flash->addMessage('danger', 'module addeding error');
        }
        
        return $response
                ->withHeader('Location', $this->router->urlFor('admin.modules.skin.get', ['route' => 'home', 'id' => $body['skin_id']]))
                ->withStatus(302);
    }
    
    public function fastRemove($request, $response)
    {
        $body = $request->getParsedBody();
        $skins_boxes = SkinsBoxesModel::find($body['id']);
        $handler = json_decode($skins_boxes->active, true);
        $handler[$body['route']] = 0;
        $skins_boxes->active = json_encode($handler);
        $skins_boxes->save();
        
        $this->cache->cleanAllSkinsCache();
        return $response
                ->withHeader('Location', $this->router->urlFor('admin.modules.skin.get', ['route' => $body['route'], 'id' => $body['skin_id']]))
                ->withStatus(302);
    }
    
    public function deleteModule($request, $response)
    {
        $body = $request->getParsedBody();
        
        $costum_box = CostumBoxModel::find($body['costum_box_id']);
        $box = BoxModel::find($body['box_id']);
        
        if ($costum_box->delete()) {
            $box->delete();
        }
        $this->cache->cleanAllSkinsCache();
        
        return $response
                ->withHeader('Location', $this->router->urlFor('admin.modules.skin.get', ['route' => 'home', 'id' => $body['skin_id']]))
                ->withStatus(302);
    }
};
