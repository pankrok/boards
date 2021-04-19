<?php

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
    
    if ($page == 6) {
        $next = '#';
    }
    
    if ($page == 0) {
        $pre = '#';
    }

    return ['pre' => $pre, 'next' => $next];
}

function createDBcfgFile()
{
    if ($_POST) {
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
        
        file_put_contents(MAIN_DIR.'/environment/Config/db_settings.php', $data);
    }
}

function checkConnection()
{
    $status = false;
    $sql = require MAIN_DIR.'/environment/Config/db_settings.php';
    if ($sql['host'] && $sql['database'] && $sql['username']) {
        $myPDO = new PDO('mysql:host='.$sql['host'].';dbname='.$sql['database'], $sql['username'], $sql['password']);
        $status = $myPDO->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }
    return $status;
}

function createDB()
{
    $status = false;
    $sql = require MAIN_DIR.'/environment/Config/db_settings.php';
    if ($sql['host'] && $sql['database'] && $sql['username']) {
        $pdo = new PDO('mysql:host='.$sql['host'].';dbname='.$sql['database'], $sql['username'], $sql['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    
    if ($data['password'] !== $data['passwordv']) {
        return 'passwords not match';
    }
        
    $data = [
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'main_group' => 1,
            'admin_lvl' => 10,
            'confirmed' => 1,
    ];
    
    $query = "INSERT INTO ".$sql['prefix']."users (email, username, password, main_group, admin_lvl, confirmed) VALUES (:email, :username, :password, :main_group, :admin_lvl, :confirmed)";
    $stmt= $pdo->prepare($query);
    $stmt->execute($data);
}


function createCFG()
{
    $cfg = json_decode(file_get_contents(MAIN_DIR.'/environment/Config/settings.json'), true);
    $_POST['admin'] ? $cfg['core']['admin'] = $_POST['admin'] : $cfg['core']['admin'] = 'acp';
    $_POST['main_page_name'] ? $cfg['board']['main_page_name'] = $_POST['main_page_name'] : $cfg['board']['main_page_name'] = 'BOARDS';
    
    return file_put_contents(MAIN_DIR.'/environment/Config/settings.json', json_encode($cfg, JSON_PRETTY_PRINT));
}

function checkReq()
{
    extension_loaded('gd') ? $gd = true : $gd = false;
    $phpv = phpversion();
    $php = explode('.', $phpv);
    $php_check = false;
    if (intval($php[0]) === 8) {
       $php_check = true;
    }    
    if (intval($php[0]) === 7 && intval($php[1]) === 4) {
       $php_check = true ;
    }
    
    $curl =  function_exists('curl_version');
    $check = [
        'php' => $phpv,
        'php_check' => $php_check,
        'gd' => $gd,
        'curl' => $curl,
    ];
    
    
    
    return $check;
}