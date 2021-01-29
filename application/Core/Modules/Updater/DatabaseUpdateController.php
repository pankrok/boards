<?php

declare(strict_types=1);

namespace Application\Core\Modules\Updater;

use Application\Core\Controller;
use Illuminate\Database\Capsule\Manager as DB;

class DatabaseUpdateController extends Controller
{ 
    public function start()
    {
        $this->log->info('Update database');
        
        $status = json_decode(file_get_contents($this->ServiceProvider->get('update_status')), true);
        $status['DatabaseUpdate']['start'] = 1;
        $status['DatabaseUpdate']['lock'] = 1;
        $status['DatabaseUpdate']['updated'] = 0;
        file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));
        $dbSettings = require(MAIN_DIR . '/environment/Config/db_settings.php');
        
        
        $handle = fopen($this->ServiceProvider->get('db_file'), "r");
        $find = ['db_name', 'brd_'];
        $replace = [$dbSettings['database'], $dbSettings['prefix']];
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                
                if($line !== '' && substr($line , 0, 2) !== '--') {
                    $sql = str_replace($find, $replace, $line);
                    try {              
                        DB::statement($sql); 
                        usleep(10000);
                    } catch (\Exception $e) {
                        $this->log->error('DB UPDATE ERROR:'.$e->getMessage());
                    }
                }
            }

            fclose($handle);
        } else {
            $this->log->error('db update file read error!');
        }

        $status['DatabaseUpdate']['updated'] = 1;
        file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));
    }    
}