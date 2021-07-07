<?php


function bDir() {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return mb_substr($_SERVER['SCRIPT_NAME'],0,-8);
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return mb_substr($scriptDir,0,-8);
    }
    return '';
};

function steps()
{
	$return = $_GET['step'] ?? 0;
	return $return;
}

function paginator()
{
	$page = @intval($_GET['step']) ?? 0;
	
	$next 	= '?step=' . strval($page+1);
	$pre 	= '?step=' . strval($page-1);
	
	if($page == 6)
		$next = '#';
	
	if($page == 0)
		$pre = '#';

	return ['pre' => $pre, 'next' => $next];
}

function createDBcfgFile()
{	if($_POST)	{
		$data = $_POST;
		
		$host 		= $data['host'];
		$database	= $data['database'];
		$username 	= $data['username'];
		$password 	= $data['password'];
		$prefix 	= $data['prefix'];
		
		$file = MAIN_DIR.'/install/assets/boards.sql';
		$data = file_get_contents($file);
		$data = str_replace('brd_', $prefix, $data);
		file_put_contents(MAIN_DIR.'/install/assets/install_boards.sql', $data);
		
		$data = "<?php
		return [
			'driver' => 'mysql',
			'host' => '$host',
			'database' => '$database',
			'username' => '$username',
			'password' => '$password',
			'charset' => 'utf8',
			'collation' => 'utf8_general_ci',
			'prefix' => '$prefix'
		];";
		
		file_put_contents(MAIN_DIR.'/environment/Config/db_settings.php', $data);		}
}

function checkConnection()
{
	$status = false;
	$sql = require MAIN_DIR.'/environment/Config/db_settings.php';
	if($sql['host'] && $sql['database'] && $sql['username']	)
	{
		$myPDO = new PDO('mysql:host='.$sql['host'].';dbname='.$sql['database'], $sql['username'], $sql['password']);
		$status = $myPDO->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}
	return $status;
}

function createDB()
{
	$status = false;	$sql = require MAIN_DIR.'/environment/Config/db_settings.php';
	if($sql['host'] && $sql['database'] && $sql['username'] )
	{
		$pdo = new PDO('mysql:host='.$sql['host'].';dbname='.$sql['database'], $sql['username'], $sql['password']);
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$query = file_get_contents(MAIN_DIR.'/install/assets/install_boards.sql');
		$pdo->exec($query);
		$status = true;
		
		$url = substr($_SERVER['REQUEST_URI'], 0, -15);    
		$data = [	
			'url' => $url.'userlist',
			'name' => '<i class="fas fa-users"></i> Members',
			'url_order' => 1,
		];
		
		$query = "INSERT INTO ".$sql['prefix']."menu (url, name, url_order) VALUES (:url, :name, :url_order)";
		$stmt= $pdo->prepare($query);
		$stmt->execute($data);
		
		$data = [	
			'url' => $url.'i/2',
			'name' => '<i class="fas fa-gavel"></i> Regulations',
			'url_order' => 0,
		];
		
		$stmt= $pdo->prepare($query);
		$stmt->execute($data);
		
	}
	return $status;
}

function createAdmin()
{
	$data = $_POST;
	$sql = require MAIN_DIR.'/environment/Config/db_settings.php';
	$pdo = new PDO('mysql:host='.$sql['host'].';dbname='.$sql['database'], $sql['username'], $sql['password']);
	
	if($data['password'] !== $data['passwordv'])
		return 'passwords not match';
		
	$data = [	
			'email' => $data['email'],
			'username' => $data['username'],
			'password' => password_hash($data['password'], PASSWORD_DEFAULT),
			'main_group' => 1,			'admin_lvl' => 10,
	];
	
	$query = "INSERT INTO ".$sql['prefix']."users (email, username, password, main_group, admin_lvl) VALUES (:email, :username, :password, :main_group, :admin_lvl)";
	$stmt= $pdo->prepare($query);
	$stmt->execute($data);
	
}


function createCFG()
{
	
	$cfg = json_decode(file_get_contents(MAIN_DIR.'/environment/Config/settings.json'), true);
	$_POST['admin'] ? $cfg['core']['admin'] = $_POST['admin'] : $cfg['core']['admin'] = 'acp';
	$_POST['main_page_name'] ? $cfg['board']['main_page_name'] = $_POST['main_page_name'] : $cfg['board']['main_page_name'] = 'BOARDS';
	
    reloadAssets();
	return file_put_contents(MAIN_DIR.'/environment/Config/settings.json', json_encode($cfg, JSON_PRETTY_PRINT));
	
}

function reloadAssets()
{
    require MAIN_DIR . '/libraries/autoload.php';
    
    $home = bDir();
    
    $cfg = json_decode(file_get_contents(MAIN_DIR.'/environment/Config/settings.json'), true);
    $skinDir = $cfg["twig"]["skin"];
    if (file_exists(MAIN_DIR . '/skins/' . $skinDir . '/skin.json')) {
        $paths = [];
        $skinData = json_decode(file_get_contents(MAIN_DIR . '/skins/' . $skinDir . '/skin.json'), true);
        
        foreach ($skinData['assets']['css'] as $v) {
            $minifier = new MatthiasMullie\Minify\CSS();
            $minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/css/'.$v);
            $minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/css/'. md5($v).'.min.css');
            $vname = explode('.', $v)[0];
            $paths['css'][$vname] =  '<link rel="stylesheet" href="'. $home . '/skins/' . $skinDir . '/cache/css/'. md5($v).'.min.css">';
        }
            
        foreach ($skinData['assets']['js'] as $v) {
            $minifier = new MatthiasMullie\Minify\JS();
            $minifier->add(MAIN_DIR . '/skins/' . $skinDir . '/assets/js/'.$v);
            $minifier->minify(MAIN_DIR . '/skins/' . $skinDir . '/cache/js/'. md5($v).'.min.js');
            $vname = explode('.', $v)[0];
            $paths['js'][$vname] = '<script async type="text/javascript" src="' . $home . '/skins/' . $skinDir . '/cache/js/'. md5($v).'.min.js"></script>';
        }
        
        if (!isset($paths['css'])) {
            $paths['css'] = null;
        }
        if (!isset($paths['js'])) {
            $paths['js'] = null;
        }
        
        
        file_put_contents(MAIN_DIR . '/skins/' . $skinDir . '/cache_assets.json', json_encode($paths));

        return true;
    } else {
        return false;
    }
}











