<?php

declare(strict_types=1);

namespace Application\Modules\Ajax;

use Application\Core\Controller;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

 /**
 * Chatbox controller
 * @package BOARDS Forum
 */

class AjaxController extends Controller
{
  
    public function ajax(Request $request, Response $resposne) : Response
    {
        if ($request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest' || $this->auth->check() === 0) {     
            return $resposne->withStatus(403);
        }

        $uid = $this->auth->check();
        if ($uid > 0) {
           $this->OnlineController->setOnline($uid); 
        }
        
        $module = $request->getParsedBody()['module'];
        switch ($module) {
            case 'chatbox':
                $resposne = self::chatbox($request);
                break;
            case 'messenger':
                $resposne = self::messenger($request);
                break;
            case 'plot':
                $resposne = self::plot($request);
                break;
        }

        return $resposne;    
    }
  
    protected function chatbox(Request &$request): Response
    {

        $route = $request->getParsedBody()['route'];
        switch ($route) {
            case 'get':
                $response = $this->ChatboxController->getChatMesasages($request, new \Slim\Psr7\Response());
                break;
            case 'post':
                $response = $this->ChatboxController->postChatMessage($request, new \Slim\Psr7\Response());
                break;
            case 'edit':
                $response = $this->ChatboxController->editMessage($request, new \Slim\Psr7\Response(), []);
                break;
            case 'load':
                $response = $this->ChatboxController->loadMoreMessages($request, new \Slim\Psr7\Response());
                break;
            case 'check':
                $response = $this->ChatboxController->checkNewMessage($request, new \Slim\Psr7\Response());
                break;
            default:
                $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $response;
    }
    
    protected function messenger(Request &$request): Response
    {
        $route = $request->getParsedBody()['route'];
        switch ($route) {
            case 'get':
                $response = $this->MessengerController->get($request, new \Slim\Psr7\Response(), []);
                break;
            case 'getNew':
                $response = $this->MessengerController->getNew($request, new \Slim\Psr7\Response(), []);
                break;
            case 'post':
                $response = $this->MessengerController->post($request, new \Slim\Psr7\Response());
                break;
            case 'list':
                $response = $this->MessengerController->list($request, new \Slim\Psr7\Response(), []);
                break;
            case 'find':
                $response = $this->MessengerController->find($request, new \Slim\Psr7\Response());
                break;
            case 'start':
                $response = $this->MessengerController->start($request, new \Slim\Psr7\Response());
                break;
            case 'check':
                $response = $this->MessengerController->check($request, new \Slim\Psr7\Response());
                break;
            default:
                $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $response;
    }
    
    protected function plot(Request &$request): Response
    {
        $route = $request->getParsedBody()['route'];
        switch ($route) {
            case 'set':
                $response = $this->PlotController->newPlotPost($request, new \Slim\Psr7\Response());
                break;
            case 'post':
                $response = $this->PlotController->replyPost($request, new \Slim\Psr7\Response());
                break;
            case 'moderate':
                $response = $this->PlotController->moderatePlot($request, new \Slim\Psr7\Response());
                break;
            case 'edit':
                $response = $this->PlotController->editPost($request, new \Slim\Psr7\Response());
                break;
            case 'like':
                $response = $this->PlotController->likeit($request, new \Slim\Psr7\Response());
                break;
            case 'rate':
                $response = $this->PlotController->ratePlot($request, new \Slim\Psr7\Response());
                break;
            default:
                $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $response;
    }
  
}