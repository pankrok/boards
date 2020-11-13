<?php
  
namespace Plugins;

use App\Models\UserModel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Controllers\Plugins\PluginEventController;

class ServerList implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return ['home.loaded' => 'ShowList'];

    }

    public function ShowList($data)
    {
		
			$servers =[ 
					[
						'id' => 'DD2',
						'type' => 'cs',
						'connect_host' => '37.233.101.83:27015',
					]
				];

			$gq = new serverlist\GameQ3\GameQ3();
			
			foreach($servers as $server)
			{
				try {
					$gq->addServer($server);
				}
				catch(Exception $e) {
					die($e->getMessage());
				}
			}
			$results = $gq->requestAllData();
			if($results["DD2"]["info"]['online']){
			  $address 	= 	($results["DD2"]["info"]['connect_addr']) . ':' . ($results["DD2"]["info"]['connect_port']);
			  $online 	= 	'<span class="fc-green"><i class="fas fa-circle-notch fa-spin"></i> ONLINE</span>';
			  $name 	=	($results["DD2"]["general"]['hostname']);
			  $map 		=	($results["DD2"]["general"]['map']);
			  $players 	= 	($results["DD2"]["general"]["num_players"]);
			  $max 		=	($results["DD2"]["general"]['max_players']);
			  $img		=	'/plugins/serverlist/ico/cs16.png';
			}
			else
			{
			  $address 	= 	($results["DD2"]["info"]['connect_addr']) . ':' . ($results["DD2"]["info"]['connect_port']);
			  $online 	= 	'<span class="fc-r"><i class="fas fa-circle-notch "></i> OFFLINE</span>';
			  $name 	=	'[PL] S89.EU ☆ DD2 Only';
			  $map 		=	'-';
			  $players 	= 	0;
			  $max 		=	32;
			  $img		=	'/plugins/serverlist/ico/cs16.png';
			}
			$content = '
			<div class="row content-bg p-2 mx-4 my-1 text-center">
				<div class="col-12 col-md-4 m-auto"> 
					<img class="rounded-circle" src="'.$img.'" /> '.$name.'
				</div>
				<div class="col-9 col-md-3 m-auto"> 
					'.$address.' &nbsp; '.$map.'
				</div>
				<div class="col-3 col-md-1 m-auto"> 
					'.$players.'/'.$max.'
				</div>
				<div class="col-5 col-md-1">
					'.$online.'
				</div>
				<div class="col-7 col-md-3">H@: <span style="text-shadow: 0px 0px 3px #ff0000; color: #ff0000; background: url(/public/img/skill/15.gif)"><i class="fas fa-sync fa-spin"></i>&nbsp;jestem fejmem XD </span></div>
			</div>';
			
			
		
		$data->setTwigData('serverlist', $content);		
    }

}
