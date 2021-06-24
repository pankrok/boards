<?php
declare(strict_types=1);
namespace Application\Modules\User;

use Application\Models\UserModel;
use Application\Models\UserDataModel;
use Application\Models\PostsModel;
use Application\Models\SecretModel;
use Application\Models\ImagesModel;
use Application\Core\Controller;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as v;

 /**
 * User Panel controller
 * @package BOARDS Forum
 */

class UserPanelController extends Controller
{
    private function moveUploadedFile($directory, $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    private function getUserdata($uid)
    {
        $data = UserDataModel::where('user_id', '=', $uid)->first();
        if (isset($data)) {
            $data->toArray();
        }
        return $data;
    }
    
    public function findPage($created, $plotId)
    {
        $count = ceil(PostsModel::where([
                                    ['created_at', '<=' ,$created],
                                    ['plot_id', '=' ,$plotId]
                                    ])
                                ->count() / $this->settings['pagination']['plots']);
                                
        return $count;
    }
    
    public function getProfile($request, $response, $arg)
    {
        if (is_numeric($arg['uid'])) {
            if (isset($_SESSION['user']) && $arg['uid'] == $_SESSION['user']) {
                if (isset($_SESSION['set-tfa'])) {
                    $_SESSION['set-tfa'] = null;
                    unset($_SESSION['set-tfa']);
                    $this->view->getEnvironment()->addGlobal('card', 'tfa');
                }
                
                $canEdit = true;
            } else {
                $canEdit = false;
            }
            
            $data = UserModel::leftJoin('images', 'users.avatar', '=', 'images.id')
                            ->select('users.*', 'images._150')
                            ->find($arg['uid']);
            
            if (!isset($data)) {
                throw new \Slim\Exception\HttpNotFoundException($request);
            }
            
            $posts = PostsModel::orderBy('created_at', 'desc')
                                ->leftJoin('plots', 'posts.plot_id', '=', 'plots.id')
                                ->select('posts.*', 'plots.plot_name')
                                ->where('posts.user_id', $arg['uid'])
                                ->get()->toArray();
                                
                            
            foreach ($posts as $k => $v) {
                $page = self::findPage($v['created_at'], $v['plot_id']);
                $posts[$k]['url'] = self::base_url() . '/plot/' . $this->urlMaker->toUrl($v['plot_name']) . '/' . $v['plot_id'] . '/'. $page . '#post-'. $v['id'];
            }
            $html = $this->group->getGroupDate($data->main_group, $data->username);
            $data['name_html'] = $html['username'];
            $data['group_name'] = $html['group'];
            
            $title = $this->translator->get('lang.user') . ': ' .$data->username;
            $this->view->getEnvironment()->addGlobal('posts', $posts);
            $this->view->getEnvironment()->addGlobal('profile', $data);
            $this->view->getEnvironment()->addGlobal('can_edit', $canEdit);
            $this->view->getEnvironment()->addGlobal('title', $title);
            $this->view->getEnvironment()->addGlobal('id', $arg['uid']);
            
            $additionalData = self::getUserdata($arg['uid']);
    
            if ($data['tfa'] === 1) {
                $secret = SecretModel::where('user_id', $data['id'])->first()->secret;
                $qc = '<img src="' . $this->tfa->google->getQRCodeImageAsDataUri($data->username, $secret) . '">';
                $this->view->getEnvironment()->addGlobal('sec', [
                    'secret' => $secret,
                    'qc' => $qc
                    ]);
            }
            if (isset($additionalData)) {
                $this->view->getEnvironment()->addGlobal('additional', $additionalData);
            }
        }
        return $this->view->render($response, 'pages/users/index.twig');
    }
    
    public function postProfilePicture($request, $response)
    {
        $this->auth->checkBan();
        $uploadedFiles = $request->getUploadedFiles();
        $user = UserModel::find($this->auth->user()['id']);

        if ($user->avatar != null) {
            $delete = ImagesModel::find($user['avatar']);
            @unlink(MAIN_DIR.'/public/upload/avatars'.$delete->original);
            @unlink(MAIN_DIR.'/public/upload/avatars'.$delete->_38);
            @unlink(MAIN_DIR.'/public/upload/avatars'.$delete->_85);
            @unlink(MAIN_DIR.'/public/upload/avatars'.$delete->_150);
            $user->avatar = null;
            $user->save();
            $delete->delete();
        }
            
        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['avatar'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = self::moveUploadedFile(MAIN_DIR.'/public/upload/avatars/', $uploadedFile);
                
            $cache = $this->images->resize($filename);
                
            $image = ImagesModel::create([
                    'original' => $filename,
                    '_38' => $cache[0],
                    '_85' => $cache[1],
                    '_150' => $cache[2]
                ]);
                
            // cache here
                
            $user->avatar = $image->id;
            $user->save();
        }
        return $response;
    }
    
    public function setTwoFactor($request, $response)
    {
        $user = UserModel::where('id', $request->getParsedBody()['id'])->first();

        if (isset($request->getParsedBody()['tfaChecbox'])) {
            $user->tfa = true;
            SecretModel::create([
                'user_id' => $user->id,
                'secret' =>  $this->tfa->google->createSecret()
            ]);
        } else {
            $user->tfa = false;
            SecretModel::where('user_id', $user->id)->delete();
        }
        $user->save();
        $_SESSION['set-tfa'] = true;
        return $response
                ->withHeader('Location', $this->router->urlFor('user.profile', [
                    'username' => $this->urlMaker->toUrl($user->username),
                    'uid' => $user->id
                ]))
                ->withStatus(302);
    }
    
    public function postChangeData($request, $response)
    {
        $this->auth->checkBan();
        if ($this->auth->check()) {
            $user = UserDataModel::where('user_id', $_SESSION['user'])->get()->first();
        
            if (!$user) {
                $user = UserDataModel::create(['user_id' => $_SESSION['user']]);
            }
                
            if ($request->getParsedBody()['name']) {
                $user->name = $this->purifier->purify($request->getParsedBody()['name']);
            }
            if ($request->getParsedBody()['surname']) {
                $user->surname = $this->purifier->purify($request->getParsedBody()['surname']);
            }
            if ($request->getParsedBody()['sex']) {
                $user->sex = $request->getParsedBody()['sex'];
            }
            if ($request->getParsedBody()['website']) {
                $disallowed = ['http://', 'https://'];
                foreach ($disallowed as $d) {
                    if (strpos($request->getParsedBody()['website'], $d) === 0) {
                        $user->website =  str_replace($d, '', $url);
                    } else {
                        $user->website = $request->getParsedBody()['website'];
                    }
                }
            }
            if ($request->getParsedBody()['bday']) {
                $user->bday = $this->purifier->purify($request->getParsedBody()['bday']);
            }
            if ($request->getParsedBody()['location']) {
                $user->location = $this->purifier->purify($request->getParsedBody()['location']);
            }
            
            $validation = $this->validator->validate($request, [
                'password'      => v::noWhitespace()->notEmpty(),
                'vpassword'      => v::notEmpty()->equals($request->getParsedBody()['password'])
            ]);
        
            if (!$validation->faild()) {
                $password = UserModel::find($this->auth->user()['id']);
                $password->password = password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT);
                $password->save();
            }
        
            $validation = $this->validator->validate($request, [
                'email'   => v::noWhitespace()->notEmpty()->email()->EmailAvailble(),
            ]);
        
            if (!$validation->faild()) {
                $mail = UserModel::find($this->auth->user()['id']);
                $mail->password = password_hash($request->getParsedBody()['email'], PASSWORD_DEFAULT);
                $mail->save();
            }
            $user->save();
        }
        $user= $this->auth->user();
        
        $url = self::base_url() . '/user/' . $this->urlMaker->toUrl($user['username']) . '/' . $user['id'];
        
        return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
    }
}
