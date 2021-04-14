<?php

declare(strict_types=1);

namespace Application\Core\Modules\Updater;

use Application\Core\Controller;

class UpdateController extends Controller
{
    public function manager($request, $response, $arg)
    {
        if (BOARDS === null) {
            die();
        }

        $status = json_decode(file_get_contents($this->ServiceProvider->get('status')), true);
        if ($status['status'] == 'Update Error!') {
            echo json_encode($status['message']);
            return $response;
        }

        if (file_exists($this->ServiceProvider->get('lock'))) {
            $return = self::status();
        } else {
            if (isset($arg['start'])) {
                if ($status['status'] == 'boards is updated') {
                    $return = self::checkUpdate();
                } else {
                    $return = self::startUpdate();
                }
                echo $return;
                return $response;
            }
            $return = self::checkUpdate();
        }
        $this->log->debug(file_get_contents($this->ServiceProvider->get('update_list')));
        echo $return;
        return $response;
    }
    
    public function checkUpdateAjax($request, $response)
    {
        echo self::checkUpdate();
        return $response;
    }
    
    public function unlock($request, $response, $arg)
    {
        if (isset($arg['lock']) && file_exists($this->ServiceProvider->get('lock'))) {
            unlink($this->ServiceProvider->get('lock'));
            echo 1;
        } else {
            echo 0;
        }
        return $response;
    }
    
    private function checkUpdate()
    {
        $this->log->debug('checking for updates.');
        
        $query = [
            'version' => $this->ServiceProvider->get('version'),
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ServiceProvider->url('checkUpdate'));
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);

        $this->log->debug('Boards version is: '. $this->ServiceProvider->get('version'));
        $this->log->debug('Update version is: '. $result);

        file_put_contents($this->ServiceProvider->get('update_list'), $result);
        $result = json_decode($result, true);
        $version = explode('.', $result[0]['version']);
        $boardVersion = explode('.', $this->ServiceProvider->get('version'));
        
        if (intval($version[0]) !== intval($boardVersion[0])
            || intval($version[1]) !== intval($boardVersion[1])
            || intval($version[2]) !== intval($boardVersion[2])
        ) {
            $status = json_encode(['status' => 'boards can be updated']);
            file_put_contents(
                $this->ServiceProvider->get('status'),
                $status
            );
        } else {
            $status = json_encode(['status' => 'boards is updated']);
            file_put_contents(
                $this->ServiceProvider->get('status'),
                $status
            );
        }
        return $status;
    }
    
    
    private function status()
    {
        $return = file_get_contents($this->ServiceProvider->get('status'));
        if (is_dir($this->ServiceProvider->get('tmp'))) {
            $status = json_decode(file_get_contents($this->ServiceProvider->get('update_status')), true);

            foreach ($status as $k => $v) {
                if (isset($v['start']) && isset($v['updated']) && isset($v['lock'])) {
                    if ($v['start'] === 0 && $v['updated'] === 0 && $v['lock'] === 0) {
                        $controller = $k.'Controller';
                        $this->$controller->start();
                        break;
                    }
                    if ($v['start'] === 1 && $v['updated'] === 0 && $v['lock'] === 0) {
                        $controller = $k.'Controller';
                        $this->$controller->start();
                        break;
                    }
                    if ($v['start'] === 1 && $v['updated'] === 0 && $v['lock'] === 1) {
                        break;
                    }
                }
            }
            if (end($status)['updated'] === 1 || empty(end($status))) {
                $status = self::endUpdate();
            }
        }
        return json_encode($status);
    }
    
    private function startUpdate()
    {
        $this->log->info('Update start');
        $fh = fopen($this->ServiceProvider->get('lock'), 'w') or die("Can't create update lock!");
        fclose($fh);
        self::getPackage();
        
        return json_encode(['status' => 'update start']);
    }
    
    
    private function getPackage()
    {
        $this->log->debug('downloading update package');
        $version = json_decode(file_get_contents($this->ServiceProvider->get('update_list')), true)[0]['version'];
        
        $output_filename = $this->ServiceProvider->get('update_dir')."$version.tar.gz";
        $this->log->debug($output_filename);
        $host = $this->ServiceProvider->url("packages/update/$version.tar.gz");
        $this->log->debug($host);
        if (!file_exists($output_filename)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $host);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            $fp = fopen($output_filename, 'w');
            fwrite($fp, $result);
            fclose($fp);
        }
        if (file_exists($output_filename) && !file_exists(substr($output_filename, 0, -3))) {
            $p = new \PharData($output_filename);
            $p->decompress();
            $this->log->debug("Boards $version file ungz");
        }
        $output_filename = substr($output_filename, 0, -3);
        
        if (file_exists($output_filename) && !is_dir(MAIN_DIR . '/environment/Update/tmp')) {
            $phar = new \PharData($output_filename);
            
            if (!is_dir(MAIN_DIR . '/environment/Update/tmp')) {
                mkdir(MAIN_DIR . '/environment/Update/tmp', 0777, true);
            }
            $phar->extractTo(MAIN_DIR . '/environment/Update/tmp');
            $this->log->debug("Boards $version file untar");
            unlink($output_filename);
        }
    }
    
    private function clean()
    {
        $this->log->info('clean files');
        $files = json_decode(file_get_contents($this->ServiceProvider->get('files_ini')), true);
        $version = json_decode(file_get_contents($this->ServiceProvider->get('update_list')), true)[0]['version'];
        if (!empty($files)) {
            foreach ($files as $k => $v) {
                $this->log->debug('delete files backup file: '. $k . '.back');
                if (file_exists(MAIN_DIR  . $k . '.back')) {
                    unlink(MAIN_DIR  . $k . '.back');
                }
            }
        }
        
        $files = json_decode(file_get_contents($this->ServiceProvider->get('lib_ini')), true);
        if (!empty($files)) {
            foreach ($files as $k => $v) {
                $this->log->debug('delete libs backup file: '. $k . '.back');
                if (file_exists(MAIN_DIR  . $k . '.back')) {
                    unlink(MAIN_DIR  . $k . '.back');
                }
            }
        }
        
        $files = json_decode(file_get_contents($this->ServiceProvider->get('skin_ini')), true);
        if (!empty($files)) {
            foreach ($files as $k => $v) {
                $this->log->debug('delete skins backup file: '. $k . '.back');
                if (file_exists(MAIN_DIR  . $k . '.back')) {
                    unlink(MAIN_DIR  . $k . '.back');
                }
            }
        }
        if (file_exists($this->ServiceProvider->get('update_dir') .'/'.$version.'.tar.gz')) {
            unlink($this->ServiceProvider->get('update_dir') .'/'.$version.'.tar.gz');
        }
    }
    
    private function endUpdate()
    {
        self::clean();
        
        $settings = $this->settings;
        $version = json_decode(file_get_contents($this->ServiceProvider->get('update_list')), true)[0]['version'];
        $settings['core']['version'] = base64_encode($version);
        
        $data = json_encode($settings, JSON_PRETTY_PRINT);
        file_put_contents($this->ServiceProvider->get('settings'), $data);
        
        $this->log->info('cleaning tmp directory');
        try {
            self::deleteDir($this->ServiceProvider->get('tmp'));
        } catch (\Exception $e) {
            $this->log->error('tmp directory error: ' . $e->getMessage());
        }
        $this->log->info('cleaning cache');
        $this->cache->clearCache();
        unlink($this->ServiceProvider->get('lock'));
        $this->log->info('Boards update success');
        
        $status = json_encode(['status' => 'boards is updated']);
        file_put_contents(
            $this->ServiceProvider->get('status'),
            $status
        );
        return ['status' => 'finish'];
    }
    
    public function statusUpdate($update): bool
    {
        if (file_put_contents($this->ServiceProvider->get('status'), json_encode($update))) {
            return true;
        } else {
            return false;
        }
    }
    
    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        
        $files = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir("$dirPath/$file")) {
                self::deleteDir("$dirPath/$file");
            } else {
                unlink("$dirPath/$file");
            }
        }
        
        rmdir($dirPath);
    }
}
