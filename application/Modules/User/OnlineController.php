<?php
declare(strict_types=1);
namespace Application\Modules\User;

use Application\Models\UserModel;
use Application\Core\Controller;

class OnlineController extends Controller
{
    public function setOnline(int &$uid)
    {
        if (isset($uid)) {
            $online = UserModel::find($uid);
            $online->touch();
        }
        return null;
    }
    
    public function getOnline(): array
    {
        $oldCacheName = $this->cache->getPath();
        $this->cache->setPath('online.users');
        
        if (!$online = $this->cache->get('id')) {
            $online = [];
            $data = UserModel::where('updated_at', '>', date("Y-m-d H:i:s", time()-60*15))->select('id')->get()->toArray();
            foreach ($data as $v) {
                $online[$v['id']] = true;
            }
            
            $this->cache->set('id', $online, 60);
        }
        $this->cache->setPath($oldCacheName);
        return $online;
    }
    
    public function getOnlineList()
    {
        $oldCacheName = $this->cache->getPath();
        $this->cache->setPath('online.users');
        
        if (!$onlineList = $this->cache->get('list')) {
            $online = [];
            $data = UserModel::where('updated_at', '>', date("Y-m-d H:i:s", time()-60*15))->select('id', 'username', 'main_group', 'updated_at')->get()->toArray();
            foreach ($data as $k => $v) {
                $urlName = $this->urlMaker->toUrl($v['username']);
                $onlineList[$k] = [
                    'username' => $this->group->getGroupDate($v['main_group'], $v['username'])['username'],
                    'url' => $this->router->urlFor('user.profile', ['uid' => $v['id'], 'username' => $urlName]),
                    'updated_at' => $v['updated_at']
                    ];
            }
            
            $this->cache->set('list', $onlineList, 60);
        }
        $this->cache->setPath($oldCacheName);
        return $onlineList;
    }
}
