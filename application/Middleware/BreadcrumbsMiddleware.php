<?php

/**
*
*	Breadcrumbs Middleware
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use Application\Models\PlotsModel;
use Application\Models\BoardsModel;
use Application\Models\CategoryModel;

class BreadcrumbsMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $breadcrumbs = null;
        $cache = $this->container->get('cache');
        
        $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
    
        $route = $routeContext->getRoute();
        $name = $route->getName();
        $routeName = $routeContext->getRoutingResults()->getUri();
        
        if (!$breadcrumbs = $cache->get($routeName.'-breadcrumbs')) {
            if ($name === 'page' || $name === 'category.getCategory' || $name === 'board.getBoard' || $name === 'board.getPlot' ||  $name === 'home') {
                $atr['plot'] = $route->getArgument('plot_id') ?? null;
                $atr['board'] = $route->getArgument('board_id') ?? null;
                $atr['cat'] = $route->getArgument('category_id') ?? null;
                $atr['id'] = $route->getArgument('id') ?? null;
                if (isset($atr['plot'])) {
                    $data = PlotsModel::where('plots.id', $atr['plot'])
                                    ->leftJoin('boards', 'boards.id', 'plots.board_id')
                                    ->leftJoin('categories', 'categories.id', 'boards.category_id')
                                    ->select('plots.plot_name', 'boards.board_name', 'categories.name', 'plots.id', 'plots.board_id', 'boards.category_id', 'boards.parent_id')
                                    ->get()->toArray()[0];
                                    
                    $breadcrumbs = [
                        0 => [	'last' => 0,
                                'path' => $this->container->get('router')->urlFor(
                                    'category.getCategory',
                                    ['category_id' => $data['category_id'],
                                    'category' => $this->container->get('urlMaker')->toUrl($data['name'])]
                                ),
                                'name' => $data['name']
                            ]
                        ];
                              
                                    
                    if (isset($data['parent_id'])) {
                        $parentBoards = BoardsModel::find($data['parent_id']);
                        array_push($breadcrumbs, ['last' => 1,
                                'path' => $this->container->get('router')->urlFor(
                                    'board.getBoard',
                                    ['board_id' => $parentBoards['id'],
                                    'board' => $this->container->get('urlMaker')->toUrl($parentBoards['board_name'])]
                                ),
                                'name' => $parentBoards['board_name']
                            ]);
                    }
                    
                    array_push(
                        $breadcrumbs,
                        ['last' => 0,
                                'path' => $this->container->get('router')->urlFor(
                                    'board.getBoard',
                                    ['board_id' => $data['board_id'],
                                    'board' => $this->container->get('urlMaker')->toUrl($data['board_name'])]
                                ),
                                'name' => $data['board_name']
                        ]
                    );
                    array_push(
                        $breadcrumbs,
                        ['last' => 1,
                                'path' => $this->container->get('router')->urlFor(
                                    'board.getPlot',
                                    ['plot_id' => $data['id'],
                                    'plot' => $this->container->get('urlMaker')->toUrl($data['plot_name'])]
                                ),
                                'name' => $data['plot_name']
                            ]
                    );
                }
                if (isset($atr['board'])) {
                    $data = BoardsModel::where('boards.id', $atr['board'])
                                    ->leftJoin('categories', 'categories.id', 'boards.category_id')
                                    ->select('boards.board_name', 'categories.name', 'boards.id', 'boards.category_id')
                                    ->first();
                            
                    $breadcrumbs = [
                        0 => [	'last' => 0,
                                'path' => $this->container->get('router')->urlFor(
                                    'category.getCategory',
                                    ['category_id' => $data['category_id'],
                                    'category' => $this->container->get('urlMaker')->toUrl($data['name'])]
                                ),
                                'name' => $data['name']
                            ]
                        ];
                        
                    if (isset($data['parent_id'])) {
                        $parentBoards = BoardsModel::find($data['parent_id']);
                        array_push($breadcrumbs, ['last' => 1,
                                'path' => $this->container->get('router')->urlFor(
                                    'board.getBoard',
                                    ['board_id' => $parentBoards['id'],
                                    'board' => $this->container->get('urlMaker')->toUrl($parentBoards['board_name'])]
                                ),
                                'name' => $parentBoards['board_name']
                            ]);
                    }
                    
                    array_push(
                        $breadcrumbs,
                        ['last' => 0,
                                'path' => $this->container->get('router')->urlFor(
                                    'board.getBoard',
                                    ['board_id' => $data['board_id'],
                                    'board' => $this->container->get('urlMaker')->toUrl($data['board_name'])]
                                ),
                                'name' => $data['board_name']
                        ]
                    );
                }
                if (isset($atr['cat'])) {
                    $data = CategoryModel::where('categories.id', $atr['cat'])
                                    ->select('categories.name', 'categories.id')
                                    ->get()->toArray()[0];
                            
                    $breadcrumbs = [
                        0 => [	'last' => 1,
                                'path' => $this->container->get('router')->urlFor(
                                    'category.getCategory',
                                    ['category_id' => $data['id'],
                                    'category' => $this->container->get('urlMaker')->toUrl($data['name'])]
                                ),
                                'name' => $data['name']
                            ]
                        ];
                }
            } elseif (explode('.', $name)[0] === 'admin') {
                $i = 0;
                if (strpos($routeName, 'settings')) {
                    $breadcrumbs[$i] = ['last' => 0,
                                'path' => $this->container->get('router')->urlFor('admin.config'),
                                'name' => $this->container->get('translator')->get('admin.configuration')
                                ];
                    $i++;
                }
                
                if (strpos($routeName, 'users') && !strpos($routeName, 'config')) {
                    $breadcrumbs[$i] = ['last' => 0,
                                'path' => $this->container->get('router')->urlFor('admin.users.and.groups'),
                                'name' => $this->container->get('translator')->get('admin.users and groups')
                                ];
                    $i++;
                }
                
                if (strpos($routeName, 'skin') && !strpos($routeName, 'skinlist') && !strpos($routeName, 'addskin')) {
                    $breadcrumbs[$i] = ['last' => 0,
                                'path' => $this->container->get('router')->urlFor('admin.skinlist'),
                                'name' => $this->container->get('translator')->get('admin.skinlist')
                                ];
                    $i++;
                }
                
                if ($name !== 'admin.home') {
                    $breadcrumbs[$i] = ['last' => 1,
                                    'path' => $this->container->get('router')->urlFor($name, $route->getArguments()),
                                    'name' => $this->container->get('translator')->get($name)
                                    ];
                }
            } else {
                $breadcrumbs[0] = ['last' => 1,
                                'path' => $this->container->get('router')->urlFor($name, $route->getArguments()),
                                'name' => $this->container->get('translator')->get($name)
                                ];
            }
            $cache->set($routeName.'-breadcrumbs', $breadcrumbs, 0);
        }
        
        $this->container->get('view')->getEnvironment()->addGlobal('breadcrumbs', $breadcrumbs);
        if (explode('.', $name)[0] !== 'admin') {
            $this->container->get('view')->getEnvironment()->addGlobal('breadcrumbs', $breadcrumbs);
        } else {
            $this->container->get('adminView')->getEnvironment()->addGlobal('breadcrumbs', $breadcrumbs);
        }
        return $handler->handle($request);
    }
}
