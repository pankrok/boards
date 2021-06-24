<?php

declare(strict_types=1);

namespace Application\Modules\Messages;

use Application\Interfaces\Messages\MessengerInterface;
use Application\Core\Controller as Controller;
use Application\Models\MailboxModel;
use Application\Models\MessageModel;
use Application\Models\ConversationsModel;
use Application\Models\UserModel;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessengerController extends Controller implements MessengerInterface
{
 
    public function get(Request $request, Response $response, array $arg) : Response
    {
        $body = $request->getParsedBody(); 
        $body['cid'] = $body['cid'] ?? $arg['cid']; 
        if(null === (MessageModel::where('conversation_id', $body['cid'])->first())) { 
            $response->getBody()->write(json_encode([]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        }
        $conversation = ConversationsModel::where('conversations.id', $body['cid'])
                          ->where('conversations.users', 'like', '%\"'. $this->auth->check() . '\"%')
                          ->leftJoin('message', 'message.conversation_id', 'conversations.id')
                          ->leftJoin('users', 'message.sender_id', 'users.id')
                          ->leftJoin('images', 'images.id', 'users.avatar')
                          ->select('message.*', 'conversations.admin', 'users.username', 'images._38')
                          ->orderBy('message.created_at', 'desc')
                          ->take(20)
                          ->get()->toArray();
                          
        $conversation = array_reverse($conversation); 
        $end = end($conversation);
        MailboxModel::where('user_id', $this->auth->check())
                    ->where('message_id', '<=', $end['id'])
                    ->delete();
  
        $this->view->getEnvironment()->addGlobal('conversation', $conversation);    

        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $this->view->getEnvironment()->addGlobal('ajax', true);
            $ajax['html'] = $this->view->fetch('boxes/messenger/message.twig');
            $ajax['img'] = end($conversation)['_38'] ?? null;
            usleep(50000);
            $response->getBody()->write(json_encode($ajax));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        }
        
        return $response; // create convrsation twig for non JS users
    }
    
    public function post(Request $request, Response $response) : Response
    {
        $body = $request->getParsedBody();
        $auth = $this->auth->check();
        $message = MessageModel::create([
            'sender_id' => $auth,
            'conversation_id' => intval($body['cid']),
            'body' => $body['body']
        ]);
        $id= $message->id;
        
        $c = ConversationsModel::find(intval($body['cid'])); 
        $c->touch();
        
        $users = trim($c->users, '"');
        $users = explode('"', $users);
        foreach ($users as $user) {
            if (intval($user) !== intval($auth)) {
                MailboxModel::create([
                    'user_id' => $user,
                    'message_id' => $id,
                    'conversation_id' => $c->id,
                    'unread' => 1
                ]);
            }
        }
        
        $message = MessageModel::where('message.id', $id)
                ->leftJoin('users', 'message.sender_id', 'users.id')
                ->leftJoin('images', 'users.avatar', 'images.id')
                ->select('message.*', 'users.username', 'images._38')
                ->get()->toArray();       
        $this->view->getEnvironment()->addGlobal('ajax', true);
        $this->view->getEnvironment()->addGlobal('conversation', $message);
        $ajax = json_encode([
            'id' => $id,
            'html' => $this->view->fetch('boxes/messenger/message.twig')
        ]);
        usleep(50000);
        $response->getBody()->write($ajax);
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        
    }
    
    public function list(Request $request, Response $response, array $arg) : Response
    {
        $converstions = ConversationsModel::where('users', 'like', '%\"'. $this->auth->check() . '\"%')->orderBy('updated_at', 'desc')->get()->toArray();
        if ( !empty($converstions) ){
            foreach ($converstions as $k => $v) {
                $users = trim($v['users'], '"');
                $users = explode('"', $users);
                foreach ($users as $key => $user) { 
                    $users[$key] = UserModel::select('users.username', 'images._38')
                    ->leftJoin('images', 'images.id', 'users.avatar')
                    ->find(intval($user))->toArray();
                } 
                $converstions[$k]['unread'] = MailboxModel::where('user_id', $this->auth->check())->where('conversation_id', $v['id'])->count();
                $converstions[$k]['users'] = $users;
                $converstions[$k]['message'] = MessageModel::where('conversation_id', $converstions[$k]['id'])->orderBy('created_at', 'desc')->select('body')->first(); 
                if(isset($converstions[$k]['message'])) {
                    $converstions[$k]['message'] = $converstions[$k]['message']->toArray();
                }
            }
        }
        
        $this->view->getEnvironment()->addGlobal('conversations', $converstions);
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $ajax = json_encode($this->view->fetch('boxes/messenger/list.twig'));
            usleep(50000);
            $response->getBody()->write($ajax);
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        }
    
        return $this->view->render($response, 'messanger.twig');
    }
    
    public function find(Request $request, Response $response) : Response
    {
        $body = $request->getParsedBody();
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {

            $users = UserModel::where('users.id', '!=', $this->auth->check())
                        ->where('users.banned', 0)
                        ->leftJoin('images', 'users.avatar', 'images.id')
                        ->select('users.id', 'users.username', 'images._38')
                        ->get()->toArray();

            $this->view->getEnvironment()->addGlobal('msg_users', $users);
            $users = $this->view->fetch('boxes/messenger/userlist.twig'); 
            $users = json_encode($users);           
            usleep(50000);       
            $response->getBody()->write($users);
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
            
        }       
        
        return $response->withStatus(403);
    }
    
    public function start(Request $request, Response $response) : Response
    {
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $body = json_decode($request->getParsedBody()['users'], true); 
            foreach ($body as $k => $v) {
                $body[$k] = $v;
            }
            
            array_push($body,$this->auth->check());
            asort($body);
            $users = '"'. implode('"', $body) . '"';
            
            $check = ConversationsModel::where('users', $users)->first();
            if($check === null) {
                $conversation = ConversationsModel::create([
                    'admin' => $this->auth->check(),
                    'users' => $users
                ]);
                $return = json_encode(['cid' =>  $conversation->id]);
            } else {
                $return = json_encode(['cid' => $check->id]);
            }
            usleep(30000);       
            $response->getBody()->write($return);
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        }
        
        return $response->withStatus(403);
    }
    
    public function getNew(Request $request, Response $response) : Response
    {
        $body = $request->getParsedBody(); 
        $uid = $this->auth->check();
        $body['cid'] = $body['cid'] ?? $arg['cid']; 
        $body['mid'] = $body['mid'] ?? $arg['mid']; 
        if(null !== (MessageModel::where('conversation_id', $body['cid'])->first())) { 
            $conversation = ConversationsModel::where([
                                ['conversations.id', $body['cid']],
                                ['message.id', '>', $body['mid']],
                                ['message.sender_id', '!=', $uid],
                                ['conversations.users', 'like', '%\"'. $uid . '\"%']
                            ])
                              ->leftJoin('message', 'message.conversation_id', 'conversations.id')
                              ->leftJoin('users', 'message.sender_id', 'users.id')
                              ->leftJoin('images', 'images.id', 'users.avatar')
                              ->select('message.*', 'conversations.admin', 'users.username', 'images._38', )
                              ->orderBy('message.created_at', 'desc')
                              ->take(20)
                              ->get()->toArray();
        } else {  
               $response->getBody()->write(json_encode([]));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        }
        if(empty($conversation)) {  
               $response->getBody()->write(json_encode([]));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        }
        
        $conversation = array_reverse($conversation); 
        $end = end($conversation);
        MailboxModel::where('user_id', $this->auth->check())
                    ->where('message_id', '<=', $end['id'])
                    ->delete();
  
        $this->view->getEnvironment()->addGlobal('conversation', $conversation);    

        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $this->view->getEnvironment()->addGlobal('ajax', true);
            $ajax['html'] = $this->view->fetch('boxes/messenger/message.twig');
            $ajax['img'] = end($conversation)['_38'] ?? null;
            $ajax['id'] = end($conversation)['id'] ?? null;
            usleep(50000);
            $response->getBody()->write(json_encode($ajax));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        }
        
        return $response; // create convrsation twig for non JS users
        
    }
    
    public function check(Request $request, Response $response) : Response
    {
        if (($id = $this->auth->check()) > 0) {
            $return = MailboxModel::where('user_id', $this->auth->check())->get()->toArray();           
        } else {
            $return = '';
        }
        $return = json_encode($return);                
        $response->getBody()->write($return);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
 
}