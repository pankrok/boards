<?php

declare(strict_types=1);

namespace Application\Core\Modules\Updater;

use Application\Core\Controller;

class LibrariesUpdateController extends Controller
{
    public function start()
    {
        $t = time();

        if (BOARDS === null) {
            die();
        }
        $this->log->info('Update libs');
        $files = json_decode(file_get_contents($this->ServiceProvider->get('lib_ini')), true);
                
        $status = json_decode(file_get_contents($this->ServiceProvider->get('update_status')), true);
        $status['LibrariesUpdate']['start'] = 1;
        $status['LibrariesUpdate']['lock'] = 1;
        $status['LibrariesUpdate']['updated'] = 0;
        file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));

        foreach ($files as $k => $v) {
            if (time() - $t > 20) {
                file_put_contents($this->ServiceProvider->get('lib_ini'), json_encode($files, JSON_PRETTY_PRINT));
                $status['LibrariesUpdate']['start'] = 1;
                $status['LibrariesUpdate']['lock'] = 0;
                $status['LibrariesUpdate']['updated'] = 0;
                file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));
                $this->log->info('Update timeout');
                die();
            }
            
            if ($v['md5'] === 'new' && $v['updated'] === 0) {
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
                
                $this->log->debug('Creating libraries file: ' . $k);
                $h = file_get_contents($this->ServiceProvider->get('files_dir') .  $k);
                file_put_contents(MAIN_DIR . $k, $h);
                $files[$k]['updated'] = 1;
            } elseif ($v['md5'] !== 'new' && $v['md5'] === 'del' && $v['updated'] === 0) {
                $this->log->debug('remove libraries file: ' . $k);
                $backup = file_get_contents(MAIN_DIR . $k);
                file_put_contents(MAIN_DIR .  $k . '.back', $backup);
                unlink(MAIN_DIR . $k);
            } elseif ($v['md5'] !== 'new' && $v['md5'] !== 'del' && $v['updated'] === 0) {
                $backup = file_get_contents(MAIN_DIR .  $k);
                $md5 = md5($backup);
                if ($v['md5'] == $md5 && $v['updated'] == 0) {
                    $this->log->debug('Update libraries file: ' . $k);
                    $h = file_get_contents($this->ServiceProvider->get('files_dir') . $k);
                    file_put_contents(MAIN_DIR . $k, $h);
                    file_put_contents(MAIN_DIR . $k . '.back', $backup);
                    $files[$k]['updated'] = 1;
                } else {
                    $this->log->error($v['path'].$k. ' file md5 checksums not match');
                    $this->log->error('oryginal: ' . $v['md5'] .' || current: ' . $md5);
                    $this->UpdateController->statusUpdate(['status' => 'Update Error!', 'message' => ['type' => 'alert-danger', 'data' => 'Update Error, check logs for details.']]);
                    self::revert();
                    $this->UpdateController->deleteDir($this->ServiceProvider->get('tmp'));
                    unlink($this->ServiceProvider->get('lock'));
                    return null;
                }
            }
        }
        $status['LibrariesUpdate']['updated'] = 1;
        file_put_contents($this->ServiceProvider->get('update_status'), json_encode($status, JSON_PRETTY_PRINT));
        $this->log->info('Update libs end');
    }
    
    private function revert()
    {
        $this->log->warning('revert files');
        $files = json_decode(file_get_contents($this->ServiceProvider->get('files_ini')), true);
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
