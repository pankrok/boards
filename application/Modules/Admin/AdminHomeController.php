<?php

declare(strict_types=1);

namespace Application\Modules\Admin;

use Application\Core\Controller as Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Application\Models\SecretModel;
use Application\Models\UserModel;
use Application\Models\PlotsModel;
use Application\Models\PostsModel;

class AdminHomeController extends Controller
{
    public function index($request, $response, $arg)
    {
        $this->adminView->getEnvironment()->addGlobal('info', self::boardStats());
        return $this->adminView->render($response, 'home.twig');
    }
    
    public function config($request, $response, $arg)
    {
        return $this->adminView->render($response, 'config.twig');
    }
    
    public function tfa($request, $response, $arg)
    {
        if (isset($_SESSION['tfa'])) {
            $secret = SecretModel::where('user_id', $_SESSION['tfa'])->first()->secret;
            $user = UserModel::find($_SESSION['tfa'])->toArray()    ;
        }
        
        if (!isset($arg['mail'])) {
            $this->tfa->google->getCode($secret);
            $this->adminView->getEnvironment()->addGlobal('mail', true);
        } else {
            $code = $this->tfa->mail->getCode($secret);
            $this->mailer->send(
                $user['email'],
                $user['username'],
                $this->translator->get('lang.Two factor code from') . $this->settings['board']['main_page_name'],
                '2fa',
                ['code' => $code]
            );
        }
        $this->adminView->getEnvironment()->addGlobal('admin2fa', true);
        $this->adminView->getEnvironment()->addGlobal('username', $user['username']);
        return $this->adminView->render($response, 'home.twig');
    }
    
    private function boardStats() : array
    {
        $sec = 60;
        $min = 60;
        $hour = 24;
        $day = 1;
        $days = 29;
        $results = DB::select(DB::raw("select version()"));
        $mysql_version =  $results[0]->{'version()'};
        $mariadb_version = '';
        if (strpos($mysql_version, 'Maria') !== false) {
            $mariadb_version = $mysql_version;
            $mysql_version = '';
        }
        
        if (!$stats = $this->cache->receive('board-stats')) {
            for ($i = 0; $i < 30; $i++) {
                $date = date("Y-m-d", time() - (($days-$i) * $sec * $min * $hour * $day)).' 23:59:59';
                $perDate = date("Y-m-d", time() - (($days-$i) * $sec * $min * $hour * $day));
                $stats['plots'][$i] = PlotsModel::where('created_at', '<', $date)->count();
                $stats['posts'][$i]  = PostsModel::where('created_at', '<', $date)->count();
                $stats['users'][$i]  = UserModel::where('created_at', '<', $date)->count();
                $stats['plots_per_day'][$i] = PlotsModel::where([
                    ['created_at', '<', $perDate.' 23:59:59'],
                    ['created_at', '>', $perDate.' 00:00:00']
                ])->count();
                $stats['posts_per_day'][$i]  = PostsModel::where([
                    ['created_at', '<', $perDate.' 23:59:59'],
                    ['created_at', '>', $perDate.' 00:00:00']
                ])->count();
                $stats['users_per_day'][$i]  = UserModel::where([
                    ['created_at', '<', $perDate.' 23:59:59'],
                    ['created_at', '>', $perDate.' 00:00:00']
                ])->count();
            }
            
            $stats['plots'] = json_encode($stats['plots']);
            $stats['posts'] = json_encode($stats['posts']);
            $stats['users'] = json_encode($stats['users']);
            $stats['plots_per_day'] = json_encode($stats['plots_per_day']);
            $stats['posts_per_day'] = json_encode($stats['posts_per_day']);
            $stats['users_per_day'] = json_encode($stats['users_per_day']);
        }

        $stats['version'] = base64_decode($this->settings['core']['version']);
        $stats['php_version'] = phpversion();
        $stats['mysql_version'] = $mysql_version;
        
        $this->cache->store('board-stats', $stats, 3600);
        return $stats;
    }
};
