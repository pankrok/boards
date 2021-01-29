<?php

declare(strict_types=1);

namespace Application\Modules\Admin;

use Application\Core\Controller as Controller;
use Application\Models\SecretModel;
use Application\Models\UserModel;

class AdminHomeController extends Controller
{
    public function index($request, $response, $arg)
    {
        return $this->adminView->render($response, 'home.twig');
    }
    
    public function tfa($request, $response, $arg)
    {
        
        if(isset($_SESSION['tfa'])){
            $secret = SecretModel::where('user_id', $_SESSION['tfa'])->first()->secret;
            $user = UserModel::find($_SESSION['tfa'])->toArray()    ;
        }
        
        if(!isset($arg['mail'])){
            $this->tfa->google->getCode($secret);
            $this->adminView->getEnvironment()->addGlobal('mail', true);
        }else{
            $code = $this->tfa->mail->getCode($secret);
        
         //   send(string $addres, string $username, string $subject,  string $template, array $variables) : string
            $this->mailer->send(
                $user['email'],
                $user['username'],
                $this->translator->get('lang.Two factor code from') . $this->settings['board']['main_page_name'],
                '2fa',
                ['code' => $code]
            );
        }
        $this->adminView->getEnvironment()->addGlobal('admin2fa' , true);
        $this->adminView->getEnvironment()->addGlobal('username', $user['username']);
        return $this->adminView->render($response, 'home.twig');
    }
};
