<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Core\Controller as Controller;
use Application\Models\PlotsReadModel;
use Application\Models\PostsModel;
use Application\Models\PlotsModel;
use Application\Models\BoardsModel;
use Application\Models\CategoriesModel;

class UnreadController extends Controller
{
    public function isUnreadPlot(int $id) : bool
    {
        if ($this->auth->check()) {
            $this->cache->setName('unread-'.$_SESSION['user']);
            if (!$unread = $this->cache->receive('plot-'.$id)) {
                $plotRead = PlotsReadModel::where([['user_id', $_SESSION['user']],['plot_id', $id]])->first();
                if (!isset($plotRead)) {
                    return false;
                }
                $plot = PostsModel::orderBy('created_at', 'DESC')->select('id', 'created_at')->find($plotRead->plot_id)->toArray();
                if ($plot !== null) {
                    $plot['created_at'] = $plot['created_at'] ?? 'now';
                    if (!is_string($plot['created_at']) === true) {
                        return false;
                    }
                    ($plotRead['timeline'] >= strtotime($plot['created_at'])) ? $unread = true : $unread = false;
                } else {
                    $unread = false;
                }
                $this->cache->store('plot-'.$id, $unread, 300);
            }
            return $unread;
        }
        return false;
    }
    
    public function isUnreadBoard(int $id) : bool
    {
        if ($this->auth->check()) {
            $this->cache->setName('unread-'.$_SESSION['user']);
            if (!$unread = $this->cache->receive('board-'.$id)) {
                $list = PlotsModel::where('board_id', $id)->select('id')->get()->toArray();
                
                foreach ($list as $k => $v) {
                    $query[$k] = $v['id'];
                }
                if (!isset($query)) {
                    return false;
                }
                $plot = PostsModel::orderBy('created_at', 'DESC')->select('id', 'created_at')->whereIn('id', $query)->first();
                if ($plot === null) {
                    return false;
                }
                
                $plot->toArray();
                $plot['created_at'] = $plot['created_at'] ?? 'now';
                $plotRead = PlotsReadModel::where([['user_id', $_SESSION['user']],['plot_id', $plot['id']]])->first();
    
                if (!isset($plotRead)) {
                    return false;
                }
                var_Dump($plot['id']);
                //($plotRead->timeline >= strtotime($plot['created_at'])) ? $unread = true : $unread = false;
                
                $this->cache->store('board-'.$id, $unread, 300);
            }
            
            return $unread = true;
        }
        return false;
    }
}
