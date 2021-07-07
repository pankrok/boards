<?php

declare(strict_types=1);

namespace Application\Modules\Auth;

use Application\Models\UserModel;
use Application\Core\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as v;

class ForgetPasswordController extends Controller
{
    public function index($request, $response)
    {
        return $this->view->render($response, 'auth/forgot_password.twig');
    }
    
    public function sendMail($request, $response)
    {
        $body = $request->getParsedBody();
        $user = UserModel::where('email', $body['email'])->first();
        
        $hash = hash('sha512', microtime() . json_encode($user->username) . microtime(true));
        $user->lostpw = $hash;
        $user->save();
        $path = self::base_url(true) . $this->router->urlFor('auth.change.pass', ['id' => $user->id, 'hash' => $hash]);
        $subject = $this->translator->get('Forget Password') . ' ' . $this->settings["board"]["main_page_name"];
        $variables = [
            'path' =>  $path,
            'username' =>  $user->username,
            'main_name' => $this->settings['board']['main_page_name'],
        ];
        
        
        if ($this->mailer->send($user->email, $user->username, $subject, 'lostpw', $variables)) {
            $this->flash->addMessage('success', 'Mail send to your address!');
        }

        return $response
            ->withHeader('Location', $this->router->urlFor('home'))
            ->withStatus(303);
    }
    
    
    public function chengePassword($request, $response, $arg)
    {
        $user = UserModel::find($arg['id']);
        if ($user->lostpw === $arg['hash']) {
            $this->view->getEnvironment()->addGlobal('id', $arg['id']);
            $this->view->getEnvironment()->addGlobal('hash', $arg['hash']);
            return $this->view->render($response, 'auth/change_lost_pw.twig');
        }
        return $response->withHeader('Location', $this->router->urlFor('home'))
            ->withStatus(303);
    }
    
    public function chengePasswordPost($request, $response)
    {
        $body = $request->getParsedBody();
        $validation = $this->validator->validate($request, [
            'vpassword'      => v::notEmpty()->equals($body['password'])
        ]);
        
        $passlenght = strlen($body['password']);
        if (32 < $passlenght || $passlenght < 8) {
            $this->flash->addMessage('danger', 'Password must be bettwen 8 and and 32 characters');
            return $response->withHeader('Location', $this->router->urlFor('auth.change.pass', ['id' => $body['id'], 'hash' => $body['hash']]))
            ->withStatus(303);
        }

        if ($validation->faild()) {
            $this->flash->addMessage('danger', 'Something went wrong, try again!');
            return $response->withHeader('Location', $this->router->urlFor('auth.change.pass', ['id' => $body['id'], 'hash' => $body['hash']]))
            ->withStatus(303);
        } else {
            $user = UserModel::find($body['id']);
            if($user->lostpw === null) {
                $this->flash->addMessage('danger', 'Something went wrong, try again!');
                return $response->withHeader('Location', $this->router->urlFor('home'))
                ->withStatus(302);
            }
            $user->password = password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT);
            $user->lostpw = null;
            $user->save();
            $this->flash->addMessage('success', 'Password changed.');
        }
        
        return $response->withHeader('Location', $this->router->urlFor('home'))
            ->withStatus(303);
    }
}
