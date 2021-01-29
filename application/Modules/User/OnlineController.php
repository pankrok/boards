<?php
declare(strict_types=1);
namespace Application\Modules\User;

use Application\Models\UserModel;
use Application\Core\Controller;

class OnlineController extends Controller
{
    public function setOnline($request, $response)
    {
        if (isset($_SESSION['user'])) {
            $online = UserModel::find($_SESSION['user']);
            $online->timestamps = false;
            $online->online_time = time();
            $online->save();
        }
        return $response;
    }
    
    public function getOnline(): array
    {
        $oldCacheName = $this->cache->getName();
        $this->cache->setName('online.users');
        $this->cache->deleteExpired();
        
        if (!$online = $this->cache->receive('id')) {
            $online = [];
            $data = UserModel::where('last_active', '>', date("Y-m-d H:i:s", time()-60*15))->select('id')->get()->toArray();
            foreach ($data as $v) {
                $online[$v['id']] = true;
            }
            
            $this->cache->store('id', $online, 60*15);
        }
        $this->cache->setName($oldCacheName);
        return $online;
    }
    
    public function getOnlineList()
    {
        $oldCacheName = $this->cache->getName();
        $this->cache->setName('online.users');
        $this->cache->deleteExpired();
        
        if (!$onlineList = $this->cache->receive('list')) {
            $online = [];
            $data = UserModel::where('last_active', '>', date("Y-m-d H:i:s", time()-60*15))->select('id', 'username', 'main_group')->get()->toArray();
            foreach ($data as $k => $v) {
                $urlName = $this->urlMaker->toUrl($v['username']);
                $onlineList[$k] = [
                    'username' => $this->group->getGroupDate($v['main_group'], $v['username'])['username'],
                    'url' => $this->router->urlFor('user.profile', ['uid' => $v['id'], 'username' => $urlName])
                    ];
            }
            
            $this->cache->store('list', $onlineList, 60*15);
        }
        $this->cache->setName($oldCacheName);
        return $onlineList;
    }
}
