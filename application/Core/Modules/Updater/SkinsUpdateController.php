<?php

declare(strict_types=1);

namespace Application\Core\Modules\Updater;

use Application\Core\Controller;

class SkinsUpdateController extends Controller
{
    public function start()
    {
        $t = time();

        if (BOARDS === null) {
            die();
        }
        $this->log->info('Update skins');
        $files = json_decode(file_get_contents($this->ServiceProvider->get('skin_ini')), true);
                
        $status = json_decode(file_get_contents($this->ServiceProvider->get('update_status')), true);
        $status['SkinsUpdate']['start'] = 1;
        $status['SkinsUpdate']['lock'] = 1;
        $status['SkinsUpdate']['updated'] = 0;
        file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));

        foreach ($files as $k => $v) {
            if (time() - $t > 20) {
                file_put_contents($this->ServiceProvider->get('skin_ini'), json_encode($files, JSON_PRETTY_PRINT));
                $status['SkinsUpdate']['start'] = 1;
                $status['SkinsUpdate']['lock'] = 0;
                $status['SkinsUpdate']['updated'] = 0;
                file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));
                $this->log->info('Update timeout');
                die();
            }
            
            
            if ($v['md5'] == 'new' && $v['updated'] == 0) {
                $pathHandler = explode('/', substr($v['path'], 1));
                $pathCheck = MAIN_DIR.'/';         
                foreach ($pathHandler as $newPath) {    
                    $pathCheck .= $newPath;
                    
                    if (!is_dir($pathCheck)) {
                        $this->log->debug("Creating dir: $pathCheck");
                        mkdir($pathCheck);
                    }
                    $pathCheck .= '/';
                }
                $this->log->debug('Creating skin file: ' . $k);
                $h = file_get_contents($this->ServiceProvider->get('tmp') . $k);
                file_put_contents(MAIN_DIR . $k, $h);
                $files[$k]['updated'] = 1;
            } elseif ($v['md5'] != 'new' && $v['updated'] == 0) {
                $backup = file_get_contents(MAIN_DIR . $k);
                $md5 = md5($backup);
                if ($v['md5'] == $md5 && $v['updated'] == 0) {
                    $this->log->debug('Update application file: ' . $k);
                    $h = file_get_contents($this->ServiceProvider->get('tmp') . $k);
                    file_put_contents(MAIN_DIR . $k, $h);
                    file_put_contents(MAIN_DIR . $k . '.back', $backup);
                    $files[$k]['updated'] = 1;
                } else {
                    $this->log->warning($v['path'].$k. ' file md5 checksums not match');
                    $this->log->warning('oryginal: ' . $v['md5'] .' || current: ' . $md5);
                }
            }
        }
        $status['SkinsUpdate']['updated'] = 1;
        file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));
        $this->log->info('Update skins end');
    }
    
    private function revert()
    {
        $this->log->warning('revert files');
        $files = json_decode(file_get_contents($this->ServiceProvider->get('skin_ini')), true);
        foreach ($files as $k => $v) {
            if (file_exists(MAIN_DIR . $k . '.back')) {
                $this->log->warning('revert: '. $k);
                $content = file_get_contents(MAIN_DIR . $k . '.back');
                file_put_contents(MAIN_DIR . $k);
                $this->log->warning('remove backup file: '. $k . '.back');
                unlink(MAIN_DIR . $k . '.back');
            }
        }
    }
}
