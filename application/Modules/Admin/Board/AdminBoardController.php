<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Board;

use Application\Core\AdminController as Controller;

use Application\Models\BoardsModel;
use Application\Models\CategoryModel;

class AdminBoardController extends Controller
{
    public function index($request, $response, $arg)
    {
        $this->adminView->getEnvironment()->addGlobal('categories', self::getCategories());
        $this->adminView->getEnvironment()->addGlobal('boards', self::getBoards());
        $this->adminView->getEnvironment()->addGlobal('parents', self::getParentBoards());

        return $this->adminView->render($response, 'board.twig');
    }
    
    public function orderPost($request, $response)
    {
        $body = $request->getParsedBody();

        foreach ($body as $k => $v) {
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
          ->withStatus(302);
        ;
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
        isset($body['visability']) ? $visability = 1 : $visability = 0;
        if ($body['parent_id'] !== '') {
            $body['cat_id'] = BoardsModel::find($body['parent_id'])['category_id'];
        } else {
            $body['parent_id'] = null;
        }
        
        BoardsModel::create([
            'board_name' => $body['name'],
            'board_description' => $body['desc'],
            'category_id' => $body['cat_id'],
            'board_order' => $body['order'],
            'parent_id' => $body['parent_id'],
            'active' => $visability
        ]);
        
        return $response
          ->withHeader('Location', $this->router->urlFor('admin.boards'))
          ->withStatus(302);
    }
    
    public function deleteBoard($request, $response, $arg)
    {
        if (isset($request->getParsedBody()['confirm']) && $request->getParsedBody()['element'] === 'board') {
            BoardsModel::find($request->getParsedBody()['id'])->delete();
            $this->cache->clearCache();
            return $response
              ->withHeader('Location', $this->router->urlFor('admin.boards'))
              ->withStatus(302);
        }
        
        if (isset($request->getParsedBody()['confirm']) && $request->getParsedBody()['element'] === 'category') {
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
        
        if ($body) {
            isset($body['visability']) ? $visability = 1 : $visability = 0;
            $board->board_name = $body['name'];
            $board->board_description = $body['desc'];
            if ($body['parent_id'] !== '') {
                $isParent = BoardsModel::where('parent_id', $arg['id'])->count();
                if ($isParent === 0) {
                    $body['cat_id'] = BoardsModel::find($body['parent_id'])['category_id'];
                } else {
                    unset($body['parent_id']);
                    $this->flash->addMessage('danger', 'parent cannot be childboard!');
                }
            } else {
                $body['parent_id'] = null;
            }
            
            $board->parent_id = $body['parent_id'];
            $board->category_id = $body['cat_id'];
            $board->board_order = $body['order'];
            $board->active	= $visability;
            $board->save();
            
            return $response
              ->withHeader('Location', $this->router->urlFor('admin.edit.board', ['id' => $arg['id']]))
              ->withStatus(302);
        }
        $this->adminView->getEnvironment()->addGlobal('categories', self::getCategories());
        $this->adminView->getEnvironment()->addGlobal('parents', self::getParentBoards());
        $this->adminView->getEnvironment()->addGlobal('data', $board->toArray());
        $this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
        
        return $this->adminView->render($response, 'board_edit.twig');
    }
    
    public function editCategory($request, $response, $arg)
    {
        $body = $request->getParsedBody();
        $category = CategoryModel::find($arg['id']);
        
        if (!empty($body)) {
            isset($body['visability']) ? $visability = 1 : $visability = 0;
            $category->name = $body['name'];
            $category->category_order = $body['order'];
            $category->active	= $visability;
            $category->save();
        }
        
        $this->adminView->getEnvironment()->addGlobal('category', $category->toArray());
        $this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
        
        return $this->adminView->render($response, 'category_edit.twig');
    }
    
    protected function getCategories()
    {
        $categories = \Application\Models\CategoryModel::orderBy('category_order', 'DESC')->get();
        return $categories;
    }
    
    protected function getBoards()
    {
        $boards = null;
        $handler = \Application\Models\BoardsModel::orderBy('category_id')->orderBy('board_order', 'DESC')->get()->toArray();
        foreach ($handler as $k => $v) {
            if (isset($v['parent_id'])) {
                $boards[$v['category_id']][$v['parent_id']]['childboards'][$v['id']] = $v;
                unset($boards[$v['category_id']][$v['id']]);
            } elseif (isset($boards[$v['category_id']][$v['id']]['childboards'])) {
                $boards[$v['category_id']][$v['id']] += $v;
            } else {
                $boards[$v['category_id']][$v['id']] = $v;
            }
        }
        return $boards;
    }
    
    protected function getParentBoards()
    {
        $boards = null;
        $handler = \Application\Models\BoardsModel::whereNull('parent_id')->orderBy('category_id')->orderBy('board_order', 'DESC')->get()->toArray();
        foreach ($handler as $k => $v) {
            $boards[$v['id']] = $v;
        }
        return $boards;
    }
};
