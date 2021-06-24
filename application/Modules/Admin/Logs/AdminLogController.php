<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Logs;

use Application\Core\AdminController as Controller;
use Application\Models\AdminLogModel;

class AdminLogController extends Controller
{
    public function index($request, $response, $arg)
    {
        if (!isset($arg['page'])) {
            $arg['page'] = 0;
        }
        
        if (!isset($arg['items'])) {
            $arg['items'] = 20;
        }

        $data = self::getLogs($arg['page'], $arg['items']);
        $this->adminView->getEnvironment()->addGlobal('logs', $data['logs']);
        $this->adminView->getEnvironment()->addGlobal('paginator', $data['paginator']);
        
        return $this->adminView->render($response, 'admin_logs.twig');
    }
    
    public function setItem($request, $response)
    {
        $items = $request->getParsedBody()['items'];
        return $response->withHeader('Location', $this->router->urlFor('admin.logs', ['page' => 1, 'items' => $items]))
                ->withStatus(302);
    }
    
    protected function getLogs($page, $items)
    {
        if ($items === null) {
            $items = 20;
        }
        
        if ($page === null) {
            $page = 1;
        }
        
        if ($items > 100) {
            $items = 100;
        }
        
        $totalItems = AdminLogModel::count();
        $itemsPerPage = $items;
        $urlPattern = $this->router->urlFor('admin.logs', [
            'page' => '(:num)',
            'items' => $items
            ]);

        $paginator = new \JasonGrimes\Paginator($totalItems, $itemsPerPage, $page, $urlPattern);
        $logs = AdminLogModel::skip(($paginator->getCurrentPage() - 1)*$paginator->getItemsPerPage())
                ->orderBy('id', 'DESC')
                ->take($paginator->getItemsPerPage())
                ->get()->toArray();
        $this->adminView->getEnvironment()->addGlobal('items', $items);
          
        return ['logs' => $logs, 'paginator' => $paginator];
    }
}
