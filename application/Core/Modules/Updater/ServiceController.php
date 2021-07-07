<?php

declare(strict_types=1);

namespace Application\Core\Modules\Updater;

use Application\Core\Controller;

class ServiceController extends Controller
{
    protected $services;
    protected $url;
    
    public function Init()
    {
        if (BOARDS === null) {
            die();
        }
        $this->log->info('Init updater services.');
        $this->url = 'https://' . unserialize(file_get_contents(MAIN_DIR . '/environment/Config/updates.dat'))['host'] . '/updates/%s';
        $this->services = [
            'lock' 				=> MAIN_DIR . '/environment/Config/lock',
            'version' 			=> base64_decode($this->settings['core']['version'], true),
            'update_version' 	=> MAIN_DIR.'/environment/Update/tmp/version.txt',
            'status' => MAIN_DIR . '/environment/Update/status.json',
            'update_status' => MAIN_DIR . '/environment/Update/tmp/status.json',
            'tmp' => MAIN_DIR . '/environment/Update/tmp',
            'update_dir' => MAIN_DIR . '/environment/Update/',
            'files_dir' => MAIN_DIR . '/environment/Update/tmp/files',
            'files_ini' => MAIN_DIR . '/environment/Update/tmp/files/files.json',
            'lib_ini' => MAIN_DIR . '/environment/Update/tmp/files/lib.json',
            'skin_ini' => MAIN_DIR . '/environment/Update/tmp/skins/files.json',
            'db_file'   => MAIN_DIR . '/environment/Update/tmp/db/update.sql',
            'update_dat' => MAIN_DIR . '/environment/Update/update.dat',
            'update_list' => MAIN_DIR . '/environment/Update/update_list.json',
            'settings' => MAIN_DIR.'/environment/Config/settings.json',
            ];
    }
    
    public function get(string $key)
    {
        if (isset($this->services[$key])) {
            return $this->services[$key];
        }
    
        return null;
    }
    
    public function url(string $url)
    {
        return sprintf($this->url, $url);
    }
}
