<?php

declare(strict_types=1);

namespace Application\Modules\Board;

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
        if ($request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest' || $this->auth->check() === 0) {
            return $resposne->withStatus(403);
        }
        $uid = $this->auth->check();
        if ($uid > 0) {
           $this->OnlineController->setOnline($uid); 
        }
        
        $module = $request->getParsedBody()['module'];
        
        switch ($module) {
            case 'chatbox':
                $resposne = self::chatbox($request, $resposne);
                break;
            case 'messenger':
                $resposne = self::messenger($request, $resposne);
                break;
            default:
                $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $resposne;    
    }
  
    protected function chatbox(Request &$request, Response &$response): Response
    {
        $route = $request->getParsedBody()['route'];
        switch ($route) {
            case 'get':
                $response = $this->ChatboxController->getChatMesasages($request, $response);
                break;
            case 'post':
                $response = $this->ChatboxController->postChatMessage($request, $response);
                break;
            case 'edit':
                $response = $this->ChatboxController->editMessage($request, $response);
                break;
            case 'load':
                $response = $this->ChatboxController->loadMoreMessages($request, $response);
                break;
            case 'check':
                $response = $this->ChatboxController->checkNewMessage($request, $response);
                break;
            default:
                $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $response;
    }
    
    protected function messenger(Request &$request, Response &$response): Response
    {
        $route = $request->getParsedBody()['route'];
        switch ($route) {
            case 'get':
                $response = $this->MessegnerController->getChatMesasages($request, $response);
                break;
            case 'post':
                $response = $this->MessegnerController->postChatMessage($request, $response);
                break;
            case 'edit':
                $response = $this->MessegnerController->editMessage($request, $response);
                break;
            case 'load':
                $response = $this->MessegnerController->loadMoreMessages($request, $response);
                break;
            case 'check':
                $response = $this->MessegnerController->checkNewMessage($request, $response);
                break;
            default:
                $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        return $response;
    }
  
}