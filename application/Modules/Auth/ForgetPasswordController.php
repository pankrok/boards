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
        $path = $this->router->urlFor('auth.change.pass', ['id' => $user->id, 'hash' => $hash]);
        $subject = 'Forget Password ' . $this->settings["board"]["main_page_name"];
        $variables = [
            'path' =>  $path,
            'username' =>  $user->username,
        ];
        
        
        if ($this->mailer->send($user->email, $user->username, $subject, 'lostpw', $variables)) {
            $this->flash->addMessage('success', 'Mail send to your address!');
        }

        return $response
            ->withHeader('Location', $this->router->urlFor('home'))
            ->withStatus(302);
    }
    
    
    public function chengePassword($request, $response, $arg)
    {
        $user = UserModel::find($arg['id']);
        if ($user->lostpw === $arg['hash']) {
            $this->view->getEnvironment()->addGlobal('id', $arg['id']);
            $this->view->render($response, 'auth/change_lost_pw.twig');
        }
        return $response->withHeader('Location', $this->router->urlFor('home'))
            ->withStatus(302);
    }
    
    public function chengePasswordPost($request, $response)
    {
        $body = $request->getParsedBody();
        $validation = $this->validator->validate($request, [
            'vpassword'      => v::notEmpty()->equals($body['password'])
        ]);

        if ($validation->faild()) {
            $this->flash->addMessage('danger', 'Something went wrong!');
        }else{
            $user = UserModel::find($body['id']);
            $user->password = password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT);
            $user->lost_pw = '';
            $user->save();
            $this->flash->addMessage('success', 'Password changed.');
        }
        
        return $response->withHeader('Location', $this->router->urlFor('home'))
            ->withStatus(302);
    }
}
