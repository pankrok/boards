<?php

declare(strict_types=1);

namespace Application\Modules\Board\Data;

use Application\Interfaces\Board\PlotDataInterface;
use Application\Core\Controller;
use Application\Models\PlotsModel;
use Application\Models\PostsModel;
use Application\Models\PlotsReadModel;
use Application\Models\BoardsModel;
use Application\Models\UserModel;
use Application\Models\LikeitModel;
use Application\Models\ImagesModel;
use Application\Models\RatesModel;
use JasonGrimes\Paginator;

/**
 * Plot data controller
 * @package BOARDS Forum
 */

class PlotDataController extends Controller implements PlotDataInterface
{
    public function getPlotData(array $arg) : array
    {
        $plot_data = [];
        $currentPage = intval($arg['page'] ?? 1);

        $totalItems = PostsModel::where([
                        ['plot_id', $arg['plot_id']],
                        ['hidden', 0]
                    ])->count();
                    
        $itemsPerPage = $this->settings['pagination']['plots'];
        
        $urlPattern = self::base_url().'/plot/'.$this->urlMaker->toUrl($arg['plot']).'/'.$arg['plot_id'].'/(:num)';
        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
    
        $data = PostsModel::where('posts.plot_id', $arg['plot_id'])
                ->orderBy('posts.created_at', 'ASC')
                ->skip(($paginator->getCurrentPage()-1)*$paginator->getItemsPerPage())
                ->take($paginator->getItemsPerPage())
                ->leftJoin('users', 'users.id', '=', 'posts.user_id')
                ->leftJoin('images', 'users.avatar', '=', 'images.id')
                ->select('posts.*', 'users.avatar', 'users.username', 'users.reputation', 'users.main_group', 'users.posts', 'users.plots', 'users.created_at as user_join', 'images._85', 'images._38')
                ->get();
        
        foreach ($data as $k => $v) {
            $group = $this->group->getGroupDate($v->main_group, $v->username);
            $data[$k]->user_url = $this->router->urlFor('user.profile', ['username' => $v->username, 'uid' => $v->user_id]);
            $data[$k]->avatar = $v->avatar ? self::base_url() . $this->settings['images']['path'] . $v->_85 : self::base_url() . '/public/img/avatar.png';
            $data[$k]->username_html = $group['username'];
            $data[$k]->group = $group['group'];
        }
        
        $plot = PlotsModel::leftJoin('users', 'users.id', 'plots.author_id')
            ->leftJoin('images', 'images.id', 'users.avatar')
            ->select('plots.*', 'images._38', 'users.username', 'users.main_group', 'users.id as uid')
            ->find($arg['plot_id']);
       
        $plot_data['plot'] = [
            'board_id' => $plot->board_id,
            'username' => $plot->username,
            'username_html' => $this->group->getGroupDate($plot->main_group, $plot->username)['username'],
            'user_url' => $this->router->urlFor('user.profile', ['username' => $this->urlMaker->toUrl($plot->username), 'uid' => $plot->uid]),
            '_38' => $plot->_38,
            'plot_id' => $arg['plot_id'],
            'posts' => $data->toArray()
        ];
        $plot_data['locked'] = $plot->locked;
        $plot_data['title'] = $plot->plot_name;
        $plot_data['board_id'] = $plot->board_id;
        $plot_data['hidden'] = $plot->hidden;
        $plot_data['stars'] = $plot->stars;
        $plot_data['paginator'] = $paginator;
        $plot_data['board_list'] = BoardsModel::select('id', 'board_name')->get()->toArray();
        
        unset($data);
        unset($plot);
        unset($paginator);
        
        return $plot_data;
    }
    
    public function getPlotLastPost(int $id): array
    {
        $data =  PostsModel::orderBy('created_at', 'desc')
            ->leftJoin('users', 'users.id', '=', 'posts.user_id')
            ->leftJoin('images', 'users.avatar', '=', 'images.id')
            ->select('posts.created_at', 'users.avatar', 'users.username', 'users.main_group', 'images._85')
            ->where('plot_id', $id)->first();
        $data->avatar = $data->avatar ? self::base_url() . $this->settings['images']['path'] . $data->_85 : self::base_url() . '/public/img/avatar.png';
            
        return $data->toArray();
    }
    
    public function setUserSeePost(int &$plot_id, Paginator &$paginator): bool
    {
        if ($this->auth->check()
        && $paginator->getCurrentPage() === $paginator->getNumPages()) {
            $lastPostData = PostsModel::orderBy('created_at', 'desc')->where('plot_id', $plot_id)->first()->toArray();
            if ($lastPostData['created_at'] !== null) {
                $lastPostData = strtotime($lastPostData['created_at']);
            } else {
                $lastPostData = 0;
            }
            
            $lastSeenPostData = PlotsReadModel::orderBy('timeline', 'desc')->where([
                ['plot_id', $plot_id],
                ['user_id', $_SESSION['user']]
            ])->first();
            isset($lastSeenPostData->timeline) ? $lastSeenPostData = $lastSeenPostData->timeline : $lastSeenPostData = 0;
            
            if ($lastPostData > $lastSeenPostData) {
                $plotRead = PlotsReadModel::firstOrCreate([
                    'plot_id' => $plot_id,
                    'user_id' => $_SESSION['user']
                ]);
                $plotRead->timeline = time();
                $plotRead->save();
                unset($lastPostData);
                unset($lastSeenPostData);
                
                return true;
            }
        }
        
        return false;
    }
    
    public function setNewPost(array &$body) : string
    {
        $this->auth->checkBan();
        $plot = PlotsModel::select('locked', 'board_id', 'plot_name', 'id')->find($body['plot_id']);

        $data['csrf'] = self::csftToken();

        if ($this->auth->check() && !$plot->locked) {
            $user =UserModel::find($this->auth->user()['id']);
            $newPost = PostsModel::create([
                'user_id' => $user->id,
                'plot_id' => $plot->id,
                'content' => $this->purifier->purify($body['content']),
            ]);
            
            if ($user->avatar) {
                $avatar = ImagesModel::find($user->avatar);
            }
            
            $avatar = $user->avatar ? self::base_url() .'/public/upload/avatars/'.$avatar->_85 : self::base_url() .'/public/img/avatar.png';
            $uurl = self::base_url() .'/user/'. $this->urlMaker->toUrl($user->username) .'/'. $user->id;
            $group = $this->group->getGroupDate($user->main_group, $user->username);
            $data['pid'] = $newPost->id;
            $var = [
                'id' => $newPost->id,
                'user_url' => $uurl,
                'username' => $user->username,
                'username_html' => $group['username'],
                'user_id' => $user->id,
                'avatar' => $avatar,
                'group' => $group['group'],
                'posts' => $user->posts,
                'plots' => $user->plots,
                'join' => $user->created_at,
                'reputation' => $user->reputation,
                'created_at' => date("Y-m-d H:i:s"),
                'content' => $this->purifier->purify($body['content']),
                'ajax' => true
            ];
            
            $this->view->getEnvironment()->addGlobal('plot', ['posts'=> [$var]]);
            $data['response'] = $this->view->fetch('boxes/plot/post.twig');
            
            $user->posts++;
            $user->save();
            
            $board = BoardsModel::find($plot->board_id);
            $board->last_post_date = time();
            $board->last_post_author = $group['username'];
            $board ->posts_number++;
            $board->save();
            
            $plotRead = PlotsReadModel::firstOrCreate([
                'plot_id' => $plot->id,
                'user_id' => $_SESSION['user']
                ]);
            $plotRead->timeline = time();
            $plotRead->save();
            
            $totalItems = PostsModel::where('plot_id', $body['plot_id'])->count();
            $itemsPerPage = $this->settings['pagination']['plots'];
            $pages = ceil($totalItems / $itemsPerPage);
            $this->cache->setPath('board.getPlot');
            $name = $this->getBasePath .'/plot/' . $this->urlMaker->toUrl($plot->plot_name) . '/'.$plot->id;
            
            if ($pages == 1) {
                $this->cache->delete($name);
            }
            $name .= '/'.$pages;
            $this->cache->delete($name);
            $this->BoardController->boardCleanCache($plot->board_id);
        } else {
            $data['response'] = $this->translator->get('you have to been logged in');
        }
        
        unset($plot);
        return json_encode($data);
    }
    
    public function setNewPlot(array &$body) : array
    {
        $data['csrf'] = self::csftToken();
        $this->auth->checkBan();
        if ($body['board_id']) {
            $board = BoardsModel::find($body['board_id']);
            
            if (!$board->locked && $body['content'] != '' &&  $body['topic'] != '') {
                $newPlot = PlotsModel::create([
                    'author_id' => $_SESSION['user'],
                    'plot_name' => $body['topic'],
                    'board_id' => $board->id,
                    'plot_active' => 1,
                    'pinned' => 0,
                    'locked' => 0,
                ]);
        
                $newPost = PostsModel::create([
                    'user_id' => $_SESSION['user'],
                    'plot_id' => $newPlot->id,
                    'content' => $this->purifier->purify($body['content']),
                    'hidden' => 0
                ]);
                
                $user =UserModel::find($this->auth->user()['id']);
                $user->posts++;
                $user->plots++;
                $user->save();
                
                $user_html = $this->group->getGroupDate($user->main_group, $user->username)['username'];
                
                $board->plots_number++;
                $board->posts_number++;
                $board->last_post_date = time();
                $board->last_post_author = $user_html;
                $board->save();
                
                $data['redirect'] = self::base_url() . '/plot/' . $this->urlMaker->toUrl($newPlot->plot_name) . '/' . $newPlot->id;
                $data['board_id'] = $body['board_id'];
                $this->BoardController->boardCleanCache($board->id);
            } else {
                $data['warn'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
								  <strong>'.$this->translator->get('post or topic cannot be empty').'</strong>
								  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								  </button>
								</div>';
            }
            
            $user = null;
        }
        
        return $data;
    }
    
    public function setPlotRate(array &$body) : array
    {
        $data['csrf'] = self::csftToken();
        $data['refresh'] = false;
        $type = 'info';
        if ($this->auth->check() !== 0) {
            if (RatesModel::where([
                ['user_id', $_SESSION['user']],
                ['plot_id', $body['plot_id']]
            ])->count() > 0) {
                $message = 'You already rate this topic';
            } else {
                $avr = RatesModel::where('plot_id', $body['plot_id'])->avg('rate');
                $plot = PlotsModel::find($body['plot_id']);
                if ($plot->author_id === $this->auth->check())
                {
                    $type = 'warning';
                    $message = 'You cant rate own plot!';
                    $data['refresh'] = true;
                } else {
                    $plot->stars = $avr;
                    $plot->timestamps = false;
                    $plot->save();
                    RatesModel::create([
                        'user_id' => $_SESSION['user'],
                        'plot_id' => $body['plot_id'],
                        'rate' => $body['rate'],
                    ]);
                    $type = 'success';
                    $message = 'You rate topics!';
                    $data['refresh'] = true;
                }
            }
        } else {
            $type = 'danger';
            $message = 'You have to log in for rate topics!';
        }        
        
        $this->view->getEnvironment()->addGlobal('name', $this->translator->get('rate'));
        $this->view->getEnvironment()->addGlobal('alert',[
            'type' => $type,
            'close' => false,
            'body' => $this->translator->get($message)
        ]);
        $data['message'] = $this->view->fetch('boxes/modals/default.twig');
        
        return $data;
    }
    
    public function setPlotData(array &$body) : bool {}
    public function setPostData(array &$body) : bool {}
    public function setPostRate(array &$body) : bool {}
}
