<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Skins;

use Application\Core\Controller as Controller;
use Application\Models\SkinsModel;
use MatthiasMullie\Minify;

class AdminSkinEditorController extends Controller
{
    public function twigEditor($request, $response, $arg)
    {
        $skinDir = SkinsModel::where('id', $arg['skin_id'])->first()->dirname;
        $this->adminView->getEnvironment()->addGlobal('skin_id', $arg['skin_id']);

        $files = self::getDirContents(MAIN_DIR . '/skins/'.$skinDir.'/tpl');
        $arg['id'] = $arg['id'] ?? 0;
        $code = file_get_contents($files[$arg['id']]);

        $count = strlen(MAIN_DIR);
        foreach ($files as $k => $v) {
            $v = explode('/', $v);
            $list[$k] = end($v);
        }
        
        $this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
        $this->adminView->getEnvironment()->addGlobal('code', $code);
        $this->adminView->getEnvironment()->addGlobal('list', $list);
        $this->adminView->getEnvironment()->addGlobal('type', 'twig');
        return $this->adminView->render($response, 'twig_editor.twig');
    }
    
    public function twigSave($request, $response)
    {
        $body = $request->getParsedBody();
        $skinDir = SkinsModel::where('id', $body['skin_id'])->first()->dirname;

        $files = self::getDirContents(MAIN_DIR . '/skins/'.$skinDir.'/tpl');
            
        
        file_put_contents($files[$body['twig_id']], $body['code']);
        $this->cache->cleanAllSkinsCache();
        $this->flash->addMessage('success', 'Skin save success!');
        
        return $response
          ->withHeader('Location', $this->router->urlFor('admin.skin.twig.edit', ['skin_id'=>$body['skin_id'], 'id' => $body['twig_id']]))
          ->withStatus(302);
    }
    
    public function cssEditor($request, $response, $arg)
    {
        $skinDir = SkinsModel::where('id', $arg['skin_id'])->first()->dirname;
        $this->adminView->getEnvironment()->addGlobal('skin_id', $arg['skin_id']);
        
        $files = self::getDirContents(MAIN_DIR . '/skins/'.$skinDir.'/assets/css');
        if (empty($files)) {
            $this->adminView->render($response, 'twig_editor.twig');
            return $response;
        }
            
        $arg['id'] = $arg['id'] ?? 0;
        
        $code = file_get_contents($files[$arg['id']]);


        foreach ($files as $k => $v) {
            $v = explode('/', $v);
            $list[$k] = end($v);
        }

        $this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
        $this->adminView->getEnvironment()->addGlobal('code', $code);
        $this->adminView->getEnvironment()->addGlobal('list', $list);
        $this->adminView->getEnvironment()->addGlobal('type', 'css');
        return $this->adminView->render($response, 'twig_editor.twig');
    }
    
    public function jsEditor($request, $response, $arg)
    {
        $skinDir = SkinsModel::where('id', $arg['skin_id'])->first()->dirname;
        $this->adminView->getEnvironment()->addGlobal('skin_id', $arg['skin_id']);

        $files = self::getDirContents(MAIN_DIR . '/skins/'.$skinDir.'/assets/js');
        if (empty($files)) {
            $this->adminView->render($response, 'twig_editor.twig');
            return $response;
        }
        
        $arg['id'] = $arg['id'] ?? 0;
        $code = file_get_contents($files[$arg['id']]);

        foreach ($files as $k => $v) {
            $v = explode('/', $v);
            $list[$k] = end($v);
        }
        
        
        $this->adminView->getEnvironment()->addGlobal('id', $arg['id']);
        $this->adminView->getEnvironment()->addGlobal('code', $code);
        $this->adminView->getEnvironment()->addGlobal('list', $list);
        $this->adminView->getEnvironment()->addGlobal('type', 'js');
        return $this->adminView->render($response, 'twig_editor.twig');
    }
    
    
    public function addFile($request, $response, $arg)
    {
        if (!isset($arg['skin_id'])) {
            return $response
          ->withHeader('Location', $this->router->urlFor('admin.skinlist'))
          ->withStatus(200);
        }
        
        $skin = SkinsModel::find($arg['skin_id']);
        $dir = MAIN_DIR . '/skins/'.$skin->dirname ;
        
        $dirlist = self::getDirContents($dir.'/tpl', true, false);
        $dirlist[] = $dir.'/tpl';
        $count = strlen(MAIN_DIR);
        foreach ($dirlist as $k => $v) {
            $dirlist[$k] = substr($v, $count);
        }
        
        $this->cache->cleanAllSkinsCache();
        $this->adminView->getEnvironment()->addGlobal('skin_id', $arg['skin_id']);
        $this->adminView->getEnvironment()->addGlobal('main_skin_dir', substr($dir, $count));
        $this->adminView->getEnvironment()->addGlobal('dir_list', array_reverse($dirlist));
        
        return $this->adminView->render($response, 'add_file.twig');
    }
    
    public function addFilePost($request, $response)
    {
        $body = $request->getParsedBody();
        $body['file'] = explode('.', $body['file'])[0];
            
        $newfile = MAIN_DIR . $body['dir'] . '/'. $body['file'] . $body['extension'];
        if ($body['extension'] === '.twig' || $body['extension'] === '.css' || $body['extension'] === '.js') {
            if (file_exists($newfile)) {
                $this->flash->addMessage('danger', "file already exist!");
                return $response
                      ->withHeader('Location', $this->router->urlFor('admin.skin.addfile', ['skin_id' => $body['skin_id']]))
                      ->withStatus(302);
            }
            
            $fh = fopen($newfile, 'w') or ($message = "ERR Can't create file");
            fclose($fh);
            if ($body['extension'] === '.css' || $body['extension'] === '.js') {
                $skinDir = SkinsModel::where('id', $body['skin_id'])->first()->dirname;
                $handler = json_decode(file_get_contents(MAIN_DIR . '/skins/'. $skinDir . '/skin.json'), true);
                if ($body['extension'] === '.css') {
                    array_push($handler['assets']['css'], $body['file'] . $body['extension']);
                    file_put_contents(MAIN_DIR . '/skins/'. $skinDir . '/skin.json', json_encode($handler, JSON_PRETTY_PRINT));
                }
                if ($body['extension'] === '.js') {
                    array_push($handler['assets']['css'], $body['file'] . $body['extension']);
                    file_put_contents(MAIN_DIR . '/skins/'. $skinDir . '/skin.json', json_encode($handler, JSON_PRETTY_PRINT));
                }
            }
            
            $this->cache->cleanAllSkinsCache();
            $this->flash->addMessage('info', "file created");
        } else {
            $this->flash->addMessage('danger', "Can't create file");
        }
        return $response
          ->withHeader('Location', $this->router->urlFor('admin.skin.addfile', ['skin_id' => $body['skin_id']]))
          ->withStatus(302);
    }
    
    protected function getDirContents($dir, $dirs = false, $file = true, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                if ($file) {
                    $results[] = $path;
                }
            } elseif ($value != "." && $value != "..") {
                self::getDirContents($path, $dirs, $file, $results);
                if ($dirs) {
                    $results[] = $path;
                }
            }
        }

        return $results;
    }
    
    public function copyTemplate($request, $response)
    {
        $body = $request->getParsedBody();
        $skinDir = SkinsModel::where('id', $body['skin_id'])->first();
        $dir = MAIN_DIR . '/skins/'.$skinDir->dirname;
        $files = array_reverse(self::getDirContents($dir, true));
        $newSkinDir = md5(microtime().$dir);
        $dirNew = MAIN_DIR . '/skins/' . $newSkinDir;
        
        if (!is_dir($dirNew)) {
            mkdir($dirNew);
        }
        
        $c = strlen($dir);
        
        foreach ($files as $file) {
            $fd = substr($file, $c);
            $nd = $dirNew . $fd;
         
            if (is_dir($dir.$fd)) {
                mkdir($nd);
            } else {
                copy($dir.$fd, $nd);
            }
        }
        
        @rename($dirNew.'/'.$skinDir->name.'.png', $dirNew.'/'.$newSkinDir.'.png');
        @rename($dirNew.'/'.$skinDir->name.'.jpg', $dirNew.'/'.$newSkinDir.'.jpg');
        
        
        SkinsModel::create([
            'name' => $skinDir->name . ' copy',
            'dirname' => $newSkinDir,
            'author' => $skinDir->author,
            'version' => $skinDir->version,
            'active' => 0
        ]);
        $this->cache->cleanAllSkinsCache();
        
        return $response
          ->withHeader('Location', $this->router->urlFor('admin.skinlist'))
          ->withStatus(200);
        ;
    }
}
