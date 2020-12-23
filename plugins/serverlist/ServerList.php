<?php
  
namespace Plugins\ServerList;

use Application\Core\Modules\Plugins\PluginInterface;
use Application\Core\Modules\Plugins\PluginController;

use Plugins\ServerList\models\ServerListModel;

class ServerList implements PluginInterface
{

    public static function getSubscribedEvents()
    {
        return ['home.loaded' => 'showList',
				'plugin.contoller.ServerList' => 'panel'];

    }


	public static function info() : array
	{
		return [
			'name' => 'Server list',
			'version' => '1.2',
			'panel' => true,
			'boards_v' => '1.1.20',
			'author' => 'PanKrok',
			'website' => 's89.eu',
			'desc' => ''
		];
		
	} 
	
	public static function activation() : bool
	{
		PluginController::addToTpl('home.twig', '{% block content %}', '{{ plugin_serverlist | raw }}');
		return true;
	}
	
	public static function deactivation() : bool
	{
		PluginController::removeFromTpl('home.twig', '{{ plugin_serverlist | raw }}');
		return true;
	}
	
	public static function install() : bool
	{
		$q = "`id` INT(11) NOT NULL AUTO_INCREMENT, 
			  `name` varchar(255) NOT NULL,
			  `game` varchar(63) NOT NULL,
			  `ip` varchar(63) NOT NULL,
			  `port` varchar(5) NOT NULL,
			  `admin` varchar(255) NOT NULL,
			  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  add PRIMARY KEY (`id`)";
			  
		PluginController::createTable('serverlist', $q);
		
		return true;
	}
	
	public static function uninstall() : bool
	{
		PluginController::dropTable('serverlist');
		return true;
	}

	public function panel($data)
	{
		if(isset($_POST['add']))
		{
			ServerListModel::create([
				'name' => $_POST['name'],
				'game' => $_POST['game'],
				'ip' => $_POST['ip'],
				'port' => $_POST['port'],
				'admin' => $_POST['admin']
			]);	
		}
		if(isset($_POST['edit']))
		{
			$server = ServerListModel::find($_POST['id']);
			$server->name = $_POST['name'];
			$server->game = $_POST['game'];
			$server->ip = $_POST['ip'];
			$server->port = $_POST['port'];
			$server->admin = $_POST['admin'];
			$server->save();
		}
		
		if(isset($_POST['id']) && isset($_POST['delete']))
		{
			ServerListModel::find($_POST['id'])->delete();
		}
		if(isset($GLOBALS['params'][2]))
		{
			$server = ServerListModel::find($GLOBALS['params'][2])->toArray();
			$data->setAdminTwigData('server', $server);
		}
		
		$servers = ServerListModel::get()->toArray();
		$data->setAdminTwigData('servers', $servers);
	}

    public function showList($data)
    {
			$content = '<div class="card border-light mb-3">';
			$text = file_get_contents($data->getPluginsDir() . '/ServerList/data/server_head.txt');
			$content .= sprintf($text, 'nazwa', 'adres', 'mapa', 'graczy', 'max', 'online', 'opiekun');
			$servers = ServerListModel::get()->toArray();
			if(empty($servers))
				return false;
				
			$gq = new serverlist\GameQ3\GameQ3();
			
			foreach($servers as $k => $server)
			{
				try {
					$gq->addServer([
						'id' => $k,
						'type' => $server['game'],
						'connect_host' => $server['ip'].':'.$server['port'],
					]);
				}
				catch(Exception $e) {
					die($e->getMessage());
				}
			}
			
			$results = $gq->requestAllData();
			
			foreach($results as $k => $result)
			{
				if($result["info"]['online']){
				  $address 	= 	($result["info"]['connect_addr']) . ':' . ($result["info"]['connect_port']);
				  $online 	= 	'<span class="fc-green"><i class="fas fa-circle-notch fa-spin"></i> ONLINE</span>';
				  $name 	=	($result["general"]['hostname']);
				  $map 		=	($result["general"]['map']);
				  $players 	= 	($result["general"]["num_players"]);
				  $max 		=	($result["general"]['max_players']);
				  $img		=	'/plugins/ServerList/serverlist/ico/cs16.png';
				}
				else
				{
				  $address 	= 	($result["info"]['connect_addr']) . ':' . ($result["info"]['connect_port']);
				  $online 	= 	'<span class="fc-r"><i class="fas fa-circle-notch "></i> OFFLINE</span>';
				  $name 	=	$servers[$k]['name'];
				  $map 		=	'-';
				  $players 	= 	'-';
				  $max 		=	'-';
				  $img		=	'/plugins/ServerList/serverlist/ico/cs16.png';
				}
				$text = file_get_contents($data->getPluginsDir() . '/ServerList/data/server_row.txt');
				$content .= sprintf($text, $img, $name, $address, $map, $players, $max, $online, $servers[$k]['admin']);
				
			}
			
		$content .= '</div>';
		$data->setTwigData('serverlist', $content);		
		return true;
    }

}
