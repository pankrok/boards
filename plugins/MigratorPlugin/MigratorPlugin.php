<?php
declare(strict_types=1);
namespace Plugins\MigratorPlugin;

use Application\Core\Modules\Plugins\PluginInterface;
use Application\Core\Modules\Plugins\PluginController;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Database\Capsule\Manager as DB;

class MigratorPlugin implements PluginInterface
{
    protected $log;

    public static function getSubscribedEvents()
    {
        return [
            'plugin.contoller.MigratorPlugin' => 'adminIndex'
            ];
    }

    public static function info() : array
    {
        return [
            'name' => 'MYBB migrator',
            'version' => '1.0',
            'panel' => true,
            'boards_v' => '0.1.6',
            'author' => 'PanKrok',
            'website' => 's89.eu',
            'desc' => "{{ trans('Migrate your data from MyBB to BOARDS') }}"
        ];
    }
    
    public static function activation() : bool
    {
        return true;
    }
    
    public static function deactivation() : bool
    {
        return true;
    }
    
    public static function install() : bool
    {
        return true;
    }
    
    public static function uninstall() : bool
    {
        return true;
    }
    
    public function adminIndex($data)
    {
        if (isset($_POST['migrate'])) {
            self::migrate($data);
            die();
        }
        
        if (isset($_POST['host'])) {
            $db = [
                'driver' => 'mysql',
                'host' => $_POST['host'],
                'database' => $_POST['base'],
                'username' => $_POST['user'],
                'password' => $_POST['pass'],
                'charset' => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix' => $_POST['pref']
            ];
            file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/db.json', json_encode($db, JSON_PRETTY_PRINT));
        }
        $force = $_POST['force'] ?? false;
        $data->setAdminTwigData('connection', self::checkConnection($data, $force));
        $db = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/db.json'));
        $data->setAdminTwigData('db', $db);
    }
    
    protected function checkConnection($data, bool $force = false) : bool
    {
        $db = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/db.json'), true);
        
        if (file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/connection.txt') !== '1' || $force === true) {
            try {
                $capsule = new \Illuminate\Database\Capsule\Manager;
                $capsule->addConnection($db);
                $capsule->bootEloquent();
                if ($capsule->getConnection()->getPdo()) {
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/connection.txt', '1');
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                file_put_contents($data->getPluginsDir().'/MigratorPlugin/migration.log.txt', json_encode($e, JSON_PRETTY_PRINT), FILE_APPEND);
                return false;
            }
        }
        
        return true;
    }
    
    protected function migrate($data)
    {
        $this->log = new Logger('name');
        $this->log->pushHandler(new StreamHandler($data->getPluginsDir().'/MigratorPlugin/data/migration.log.txt', Logger::DEBUG));
        $mybb_db = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/db.json'), true);
        $boards_db = require(MAIN_DIR . '/environment/Config/db_settings.php');
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($boards_db);
        $capsule->addConnection($mybb_db, 'migration');
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        $migration = self::init($data);
        self::moveData($data, $migration);
    }
    
    protected function init($data) : array
    {
        if (!file_exists($data->getPluginsDir().'/MigratorPlugin/data/migration_handler.json')) {
            $migration = [
                'groups' => DB::connection('migration')->table('usergroups')->count(),
                'users' => DB::connection('migration')->table('users')->count(),
                'categories' => DB::connection('migration')->table('forums')->where('type', 'c')->count(),
                'forums' => DB::connection('migration')->table('forums')->where('type', 'f')->count(),
                'plots' => DB::connection('migration')->table('threads')->count(),
                'posts' => DB::connection('migration')->table('posts')->count()
            ];
            file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_handler.json', json_encode($migration, JSON_PRETTY_PRINT));
        } else {
            $migration = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_handler.json'), true);
        }
        
        return $migration;
    }
    
    protected function moveData($data, $migration)
    {
        try {
            $maxElements = 200;
            $moved = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json'), true);
            self::showProgress($data);
            if (isset($moved['groups']['done']) && $moved['groups']['done'] !== 'yes') {
                ('migrating groups');
                $skip = $moved['groups']['done'] ?? 0;
                $skip *= $maxElements;
                if (isset($_SESSION['skip'])) {
                    $skip += $_SESSION['skip'];
                    $_SESSION['skip'] = 0;
                    unset($_SESSION['skip']);
                }
                $groups = DB::connection('migration')->table('usergroups')->skip($skip)->take($maxElements)->get();
                if (file_exists($data->getPluginsDir().'/MigratorPlugin/data/migration_groupes.json')) {
                    $groupe_helper = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_groupes.json'), true);
                }
                $i = 1;
                foreach ($groups as $g) {
                    $h = \Application\Models\GroupsModel::create([
                        'username_html' => str_replace(['{', '}'], ['{{', '}}'], $g->namestyle),
                        'grupe_name' => str_replace('{username}', $g->title, $g->namestyle),
                        'grupe_level' => 0,
                    ]);
                    $groupe_helper[$g->gid] = $h->id;
                    $i++;
                    $this->log->info('MYBB group gid: ' . $g->gid . ' is Boards group: ' . $h->id);
                }
                if ($i < 200) {
                    $moved['groups']['done'] = 'yes';
                    $moved['groups']['count'] += $i;
                    $this->log->info('Updating plugin database for groups');
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_groupes.json', json_encode($groupe_helper, JSON_PRETTY_PRINT));
                } else {
                    $moved['groups']['done'] = ($skip+1);
                    $moved['groups']['count'] += $i;
                    $this->log->info('Updating plugin database for groups');
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_groupes.json', json_encode($groupe_helper, JSON_PRETTY_PRINT));
                }
                $this->log->info('Updating plugin database for migrated data');
                file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json', json_encode($moved, JSON_PRETTY_PRINT));
                die();
            }
            
            if ($moved['users']['done'] !== 'yes') {
                $this->log->info('Migrate users');
                $skip = $moved['users']['done'] ?? 0;
                $users = DB::connection('migration')->table('users')->skip($skip*$maxElements)->take($maxElements)
                        ->leftJoin('usergroups', 'users.usergroup', '=', 'usergroups.gid')
                        ->get();
                $groupe_helper = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_groupes.json'), true);
                $i = 1;
                foreach ($users as $u) {
                    $h = \Application\Models\UserModel::create([
                        'username' => $u->username,
                        'email' => $u->email,
                        'password' => $u->password,
                        'main_group' => $groupe_helper[$u->usergroup],
                        'admin_lvl' => 0,
                        'last_post' => $u->lastpost,
                        'last_active' => $u->lastactive,
                        'confirmed' => 1,
                    ]);
                    $this->log->info('Create new user: '.$u->username);
                    if ($u->avatartype === 'upload') {
                        $url = DB::connection('migration')->table('settings')->where('name', 'bburl')->first()->value;
                        if (substr($url, -1) === '/') {
                            $url = substr($url, 0, -1);
                        }
                        $url .= explode('?', substr($u->avatar, 1))[0];
                        $img_path = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/settings.json'), true)['images']['path'];
                        $tmp = explode('/', $url);
                        $fileName = end($tmp);
                        $saveto = $img_path . 'tmp-' . $fileName;
         
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                        $uploadedFile = curl_exec($ch);
                        curl_close($ch);
                            
                        if (file_exists(MAIN_DIR . $saveto)) {
                            unlink(MAIN_DIR .$saveto);
                        }
                        
                        file_put_contents(MAIN_DIR . $saveto, $uploadedFile);
                        
                        $info = getimagesize(MAIN_DIR . $saveto);
                        $mime = $info['mime'] ?? 'none';
                        
                        if ($mime === 'image/jpeg' || $mime === 'image/png' || $mime === 'image/gif') {
                            self::croppImg($saveto, $mime);
                            $tmp = explode('/', $url);
                            $filename = self::moveUploadedFile(MAIN_DIR . $img_path, 'tmp-' . end($tmp));
                         
                            $resizer = new \Application\Core\Modules\Images\Resizer($img_path);
                            $cache = $resizer->resize($filename);
                                
                            $image = \Application\Models\ImagesModel::create([
                                    'original' => $filename,
                                    '_38' => $cache[0],
                                    '_85' => $cache[1],
                                    '_150' => $cache[2]
                                ]);

                            $h->avatar = $image->id;
                            $this->log->info('adding avator for user: '.$u->username);
                        }
                        if (file_exists(MAIN_DIR . $saveto)) {
                            unlink(MAIN_DIR .$saveto);
                        }
                    }
                    
                    $h->mergeFillable(['created_at', 'updated_at']);
                    $h->created_at = date("Y-m-d H:i:s", $u->regdate);
                    $h->updated_at = date("Y-m-d H:i:s", $u->regdate);
                    $h->save();
                    $i++;
                }
                if ($i < 200) {
                    $moved['users']['done'] = 'yes';
                    $moved['users']['count'] += $i;
                } else {
                    $moved['users']['done'] = ($skip+1);
                    $moved['users']['count'] += $i;
                }
                file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json', json_encode($moved, JSON_PRETTY_PRINT));
                die();
            }
            
            if ($moved['categories']['done'] !== 'yes') {
                $skip = $moved['categories']['done'] ?? 0;
                $categories = DB::connection('migration')->table('forums')->where('type', 'c')->skip($skip*$maxElements)->take($maxElements)->get();
                if (file_exists($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json')) {
                    $category_helper = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json'), true);
                }
                
                $i = 1;
                foreach ($categories as $c) {
                    $h = \Application\Models\CategoryModel::create([
                        'name' => $c->name,
                        'category_order' => ($migration['categories'] - $c->disporder),
                        'active' => $c->active
                    ]);
                    $this->log->info('migrating category: '.$c->name);
                    $category_helper[$c->fid] = $h->id;
                    $i++;
                }
                if ($i < 200) {
                    $moved['categories']['done'] = 'yes';
                    $moved['categories']['count'] += $i;
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json', json_encode($category_helper, JSON_PRETTY_PRINT));
                } else {
                    $moved['categories']['done'] = ($skip+1);
                    $moved['categories']['count'] += $i;
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json', json_encode($category_helper, JSON_PRETTY_PRINT));
                }
                file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json', json_encode($moved, JSON_PRETTY_PRINT));
                die();
            }
            
            if ($moved['forums']['done'] !== 'yes') {
                $skip = $moved['forums']['done'] ?? 0;
                $forums = DB::connection('migration')->table('forums')->where('type', 'f')->skip($skip*$maxElements)->take($maxElements)->orderBy('parentlist', 'asc')->get();
                if (file_exists($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json')) {
                    $category_helper = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json'), true);
                }
                
                $i = 1;
                foreach ($forums as $f) {
                    $parents = explode(',', $f->parentlist);
                    if ($parents[1] != $f->fid) {
                        $parent = $category_helper[$parents[1]];
                    } else {
                        $parent = null;
                    }
                    
                    $h = \Application\Models\BoardsModel::create([
                                'board_name' => $f->name,
                                'board_description' => $f->description,
                                'category_id' => $category_helper[$parents[0]],
                                'parent_id' => $parent,
                                'board_order' => ($migration['forums'] - $f->disporder),
                                'active' => $f->active
                    ]);
                    $this->log->info('migrating board: '.$f->name);
                    $category_helper[$f->fid] = $h->id;
                    $i++;
                }
                if ($i < 200) {
                    $moved['forums']['done'] = 'yes';
                    $moved['forums']['count'] += $i;
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json', json_encode($category_helper, JSON_PRETTY_PRINT));
                } else {
                    $moved['forums']['done'] = ($skip+1);
                    $moved['forums']['count'] += $i;
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json', json_encode($category_helper, JSON_PRETTY_PRINT));
                }
                file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json', json_encode($moved, JSON_PRETTY_PRINT));
                die();
            }
            
            if ($moved['plots']['done'] !== 'yes') {
                $skip = $moved['plots']['done'] ?? 0;
                $plots = DB::connection('migration')->table('threads')->skip($skip*$maxElements)->take($maxElements)->get();
                if (file_exists($data->getPluginsDir().'/MigratorPlugin/data/migration_plots.json')) {
                    $plots_helper = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_plots.json'), true);
                }
                if (file_exists($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json')) {
                    $category_helper = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_categories.json'), true);
                }
                
                $i = 1;
                foreach ($plots as $p) {
                    if ($p->subject !== '' && $category_helper[$p->fid] !== null) {
                        if ($p->visible === 1) {
                            $visable = 0;
                        } else {
                            $visable = 1;
                        }
                        
                        $h = \Application\Models\PlotsModel::create([
                                    'plot_name' => $p->subject,
                                    'board_id' => $category_helper[$p->fid],
                                    'author_id' => \Application\Models\UserModel::where('username', $p->username)->first()->id,
                                    'plot_active' => 1,
                                    'pinned' => $p->sticky,
                                    'locked' => $p->closed,
                                    'hidden' => $visable,
                                    'views' => $p->views,
                        ]);
                        $h->mergeFillable(['created_at', 'updated_at']);
                        $h->created_at = date("Y-m-d H:i:s", $p->dateline);
                        $h->updated_at = date("Y-m-d H:i:s", $p->dateline);
                        $h->save();
                        $this->log->info('migrating plot: '.$p->subject);
                        $plots_helper[$p->tid] = $h->id;
                    }
                    $i++;
                }
                if ($i < 200) {
                    $moved['plots']['done'] = 'yes';
                    $moved['plots']['count'] += $i;
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_plots.json', json_encode($plots_helper, JSON_PRETTY_PRINT));
                } else {
                    $moved['plots']['done'] = ($skip+1);
                    $moved['plots']['count'] += $i;
                    file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_plots.json', json_encode($plots_helper, JSON_PRETTY_PRINT));
                }
                file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json', json_encode($moved, JSON_PRETTY_PRINT));
                die();
            }
            
            if ($moved['posts']['done'] !== 'yes') {
                if (file_exists($data->getPluginsDir().'/MigratorPlugin/data/migration_plots.json')) {
                    $plots_helper = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_plots.json'), true);
                } else {
                    die(json_encode('file plots read error'));
                }
                $skip = $moved['posts']['done'] ?? 0;
                $posts = DB::connection('migration')->table('posts')->orderBy('dateline', 'ASC')->skip($skip*$maxElements)->take($maxElements)->get();
                           
                $i = 1;
                foreach ($posts as $p) {
                    $username = \Application\Models\UserModel::where('username', $p->username)->first();
                    if ($p->subject !== '' && isset($plots_helper[$p->tid]) && isset($username['id'])) {
                        if ($p->visible === 1) {
                            $visable = 0;
                        } else {
                            $visable = 1;
                        }
                        
                        $h = \Application\Models\PostsModel::create([
                            'user_id' => $username['id'],
                            'plot_id' => $plots_helper[$p->tid],
                            'content' => self::toHtml($p->message),
                            'post_reputation' => 0,
                            'hidden' => $visable
                        ]);
                        $h->mergeFillable(['created_at', 'updated_at']);
                        $h->created_at = date("Y-m-d H:i:s", $p->dateline);
                        $h->updated_at = date("Y-m-d H:i:s", $p->dateline);
                        $h->save();
                        $this->log->info('migrating post: '.$p->pid);
                    }
                    $i++;
                }
                if ($i < 200) {
                    $moved['posts']['done'] = 'yes';
                    $moved['posts']['count'] += $i;
                } else {
                    $moved['posts']['done'] = ($skip+1);
                    $moved['posts']['count'] += $i;
                }
                file_put_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json', json_encode($moved, JSON_PRETTY_PRINT));
                die();
            }
        } catch (\Exception $e) {
            $_SESSION['skip'] = $i;
            $this->log->error($e->getCode());
            $this->log->error($e->getMessage());
        }
    }
    
    private function toHtml($text, $advanced=true)
    {
        $text  = htmlspecialchars($text);
        $basic_bbcode = [
            '[b]', '[/b]',
            '[i]', '[/i]',
            '[u]', '[/u]',
            '[s]','[/s]',
            '[ul]','[/ul]',
            '[li]', '[/li]',
            '[ol]', '[/ol]',
            '[center]', '[/center]',
            '[left]', '[/left]',
            '[right]', '[/right]',
        ];

        $basic_html = [
            '<b>', '</b>',
            '<i>', '</i>',
            '<u>', '</u>',
            '<s>', '</s>',
            '<ul>','</ul>',
            '<li>','</li>',
            '<ol>','</ol>',
            '<div style="text-align: center;">', '</div>',
            '<div style="text-align: left;">',   '</div>',
            '<div style="text-align: right;">',  '</div>',
        ];

        $text = str_replace($basic_bbcode, $basic_html, $text);

        if ($advanced) {
            $advanced_bbcode = [
             '#\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.+)\[/color\]#Usi',
             '#\[size=([0-9][0-9]?)](.+)\[/size\]#Usi',
             '#\[size=([a-zA-Z\-]*?)](.+)\[/size\]#Usi',
             '#\[font=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.+)\[/font\]#Usi',
             '#\[align=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.+)\[/align\]#Usi',
             '#\[quote](\r\n)?(.+?)\[/quote]#si',
             '#\[quote=(.*?)](\r\n)?(.+?)\[/quote]#si',
             '#\[url](.+)\[/url]#Usi',
             '#\[url=(.+)](.+)\[/url\]#Usi',
             '#\[email]([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})\[/email]#Usi',
             '#\[email=([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})](.+)\[/email]#Usi',
             '#\[img](.+)\[/img]#Usi',
             '#\[img=(.+)](.+)\[/img]#Usi',
             '#\[code](\r\n)?(.+?)(\r\n)?\[/code]#si',
             '#\[youtube]http://[a-z]{0,3}.youtube.com/watch\?v=([0-9a-zA-Z]{1,11})\[/youtube]#Usi',
             '#\[youtube]([0-9a-zA-Z]{1,11})\[/youtube]#Usi',
             '/\[hr\]/',
            ];

            /**
             * This array contains the advanced static bbcode's html
             * @var array $advanced_html
             */
            $advanced_html = [
                 '<span style="color: $1">$2</span>',
                 '<span style="font-size: $1px">$2</span>',
                 '<span style="font-size: $1">$2</span>',
                 '<span style="font-family: $1">$2</span>',
                 '<span style="align: $1">$2</span>',
                 "<div class=\"quote\"><span class=\"quoteby\">Disse:</span>\r\n$2</div>",
                 "<div class=\"quote\"><span class=\"quoteby\">Disse <b>$1</b>:</span>\r\n$3</div>",
                 '<a rel="nofollow" target="_blank" href="$1">$1</a>',
                 '<a rel="nofollow" target="_blank" href="$1">$2</a>',
                 '<a href="mailto: $1">$1</a>',
                 '<a href="mailto: $1">$2</a>',
                 '<img src="$1" alt="$1" />',
                 '<img src="$1" alt="$2" />',
                 '<div class="code">$2</div>',
                 '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>',
                 '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>',
                 '<hr>',
            ];

            $text = preg_replace($advanced_bbcode, $advanced_html, $text);
        }

        //before return convert line breaks to HTML
        $this->log->info('parsing post bbcode');
        return nl2br($text);
    }
    
    private function moveUploadedFile($directory, $uploadedFile)
    {
        $extension = pathinfo($uploadedFile, PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        rename($directory  . $uploadedFile, $directory  . $filename);

        return $filename;
    }
    
    protected function showProgress($data)
    {
        $m = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_done.json'), true);
        $h = json_decode(file_get_contents($data->getPluginsDir().'/MigratorPlugin/data/migration_handler.json'), true);
        $all = 0;
        $current = 0;
        
        if ($h !== null) {
            foreach ($h as $v) {
                $all += $v;
            }
        } else {
            $all = 100;
        }
        
        if ($m !== null) {
            foreach ($m as $v) {
                $current += $v['count'];
            }
        } else {
            $current = 0;
        }
        $response = ceil($current/$all*100);
        if ($response >= 100) {
            $response = 'done';
        }
        echo json_encode($response);
    }
    
    protected function croppImg($url, $mime)
    {
        $imgSrc = MAIN_DIR . $url;
        list($width, $height) = getimagesize($imgSrc);
        if ($width === $height) {
            return null;
        }
        
        switch ($mime) {
            case 'image/jpeg':
                $myImage = imagecreatefromjpeg($imgSrc);
                break;
                
            case 'image/png':
                $myImage = imagecreatefrompng($imgSrc);
                break;
                
            case 'image/gif':
                $myImage = imagecreatefromgif($imgSrc);
                break;

        }

        if ($width > $height) {
            $y = 0;
            $x = ($width - $height) / 2;
            $smallestSide = $height;
        } else {
            $x = 0;
            $y = ($height - $width) / 2;
            $smallestSide = $width;
        }
        
        $smallestSide = ceil($smallestSide);
        $thumbSize = $smallestSide;

        $thumb = imagecreatetruecolor(intval($thumbSize), intval($thumbSize));
        $x = ceil($x);
        $y = ceil($y);
        
        imagecopyresampled($thumb, $myImage, 0, 0, intval($x), intval($y), intval($thumbSize), intval($thumbSize), intval($smallestSide), intval($smallestSide));
        
         
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($thumb, MAIN_DIR.$url, 100);
                break;
                
            case 'image/png':
                imagepng($thumb, MAIN_DIR.$url, 9);
                break;
                
            case 'image/gif':
                imagegif($thumb, MAIN_DIR.$url); ;
                break;

        }
    }
}
