<?php

declare(strict_types=1);

namespace Application\Modules\Messages;

use Application\Core\Controller as Controller;
use Application\Models\MailboxModel;
use Application\Models\MessageModel;
use Application\Models\UserModel;

class MessageController extends Controller
{
    public function test($request, $response, $arg) 
    {
        $message = MessageModel::where('topic', $arg['topic']);
        $message->where('mailbox', 'inbox');
        $message->where(function($message){
                        $message->where('recipient_id', $_SESSION['user']);
                        $message->orWhere('sender_id', $_SESSION['user']);
                    });

        $message->rightJoin('mailbox', 'message.id', 'mailbox.message_id');
        $message->leftJoin('users', 'users.id', 'message.sender_id');
        
        $message->leftJoin('images', 'users.avatar', 'images.id');
        $message = $message->get()->toArray();
        foreach ($message as $k => $v) {
             if (isset($message[$k]['avatar'])) {
                $message[$k]['avatar'] = self::base_url() . '/public/upload/avatars/'.$v['_38'];
            } else {
                $message[$k]['avatar'] = self::base_url() . '/public/img/avatar.png'; 
            }   
            $message[$k]['username_html'] = $this->group->getGroupDate($v['main_group'], $v['username'])['username'];
        }

        $this->view->getEnvironment()->addGlobal('messages', $message);
        return $this->view->render($response, 'messanger.twig');
    }
    
    public function index($request, $response, $arg)
    {
        if (isset($_SESSION['user'])) {
            $this->view->getEnvironment()->addGlobal('unread', self::countUnreadMessages());
            $this->view->getEnvironment()->addGlobal('folder', $arg['folder']);
            $this->view->getEnvironment()->addGlobal('messages', self::getMessages($arg['folder']));
            if ($request->getParsedBody() !== null) {
                $body = $request->getParsedBody();
                $body['body'] = str_replace('<p>', '<p>| ', $body['body']);
                $this->view->getEnvironment()->addGlobal('reply', $body);
            }
        }

        return $this->view->render($response, 'mailbox.twig');
    }

    public function sendMessage($request, $response)
    {
        $body = $request->getParsedBody();
        if (isset($body['topic']) && isset($body['body'])) {
            $recipient = UserModel::where('username', $body['recipient'])->first();
            if ($recipient === null) {
                $this->flash->addMessage('danger', 'recipient does not exists');
                return  $response
                ->withHeader('Location', $this->router->urlFor('mailbox', ['folder' => 'outbox']))
                ->withStatus(302);
            }
            
            $message = MessageModel::create([
                'recipient_id' => $recipient->id,
                'sender_id' => $_SESSION['user'],
                'topic' => $this->purifier->purify($body['topic']),
                'body' => $this->purifier->purify($body['body'])
            ]);
        } else {
            $this->flash->addMessage('danger', 'You cant send massage without topic or content!');
        }

        if (isset($message)) {
            MailboxModel::create([
                'message_id' => $message->id,
                'mailbox' => 'inbox',
                'user_id' => $recipient->id,
                'unread' => 1
            ]);
            
            MailboxModel::create([
                'message_id' => $message->id,
                'mailbox' => 'outbox',
                'user_id' => $_SESSION['user'],
                'unread' => 0
            ]);
            
            $this->flash->addMessage('success', 'Message send!');
        } else {
            $message->delete();
            $this->flash->addMessage('danger', 'Something went wrong!');
        }
        
        return  $response
            ->withHeader('Location', $this->router->urlFor('mailbox', ['folder' => 'outbox']))
            ->withStatus(302);
    }
    
    public function getMessage($request, $response, $arg)
    {
        $message = MessageModel::where('id', $arg['id'])->first();
        if($message === null) {
            throw new \Slim\Exception\HttpNotFoundException($request);
        }        
        
        if ($message->sender_id === $_SESSION['user'] || $message->recipient_id === $_SESSION['user']) {
            if ($message->recipient_id === $_SESSION['user']) {
                $unread = MailboxModel::where([
                    ['user_id', $_SESSION['user']],
                    ['message_id', $arg['id']]
                ])->first();
                $this->cache->setPath('unread-messages');
                $this->cache->delete('user-'.$_SESSION['user']);
                $unread->unread = 0;
                $unread->save();
            }
            $users = [
                'from' => UserModel::select('username')->find($message->sender_id)['username'],
                'to' =>  UserModel::select('username')->find($message->recipient_id)['username']
            ];
            $this->view->getEnvironment()->addGlobal('users', $users);
            $this->view->getEnvironment()->addGlobal('message', $message);
        }
        $this->view->getEnvironment()->addGlobal('box', $arg['folder']);
        $this->view->getEnvironment()->addGlobal('folder', 'message');
        return $this->view->render($response, 'mailbox.twig');
    }
    
    public function deleteMessage($request, $response)
    {
        $body = $request->getParsedBody();
        MailboxModel::where([['user_id', $_SESSION['user']], ['message_id', $body['id']]])->delete();
        
        return $response
            ->withHeader('Location', $this->router->urlFor('mailbox', ['folder' => $body['folder']]))
            ->withStatus(302);
    }
    
    public function moveMessage($request, $response)
    {
        $body = $request->getParsedBody();
        $message = MailboxModel::where([
            ['user_id', $_SESSION['user']],
            ['message_id', $body['id']]
        ])->first();
        $message->mailbox = $body['newFolder'];
        $message->save();
        
        return $response
            ->withHeader('Location', $this->router->urlFor('mailbox', ['folder' => $body['folder']]))
            ->withStatus(302);
    }
    
    public function countUnreadMessages(): int
    {
        $this->cache->setPath('unread.messages');
        if (isset($_SESSION['user'])) {
            if (!$unread = $this->cache->get('user-'.$_SESSION['user'])) {
                $unread =  MailboxModel::where([
                        ['user_id', $_SESSION['user']],
                        ['unread', 1]
                    ])->count();
                $this->cache->set('user-'.$_SESSION['user'], $unread, $this->settings['cache']['cache_time']);
            }
        } else {
            $unread = 0;
        }
        
        return $unread;
    }
    
    public function getMessages(string $folder): array
    {
        $messages = [];
        if (isset($_SESSION['user'])) {
            $messages = MailboxModel::where([
                ['user_id', $_SESSION['user']],
                ['mailbox', $folder]
            ])->orderBy('message.created_at', 'DESC')
            ->leftJoin('message', 'mailbox.message_id', 'message.id')
            ->select('message.id', 'message.topic', 'message.created_at', 'mailbox.unread')
            ->get()->toArray();
        }
        
        return $messages;
    }
};
