<?php

declare(strict_types=1);

namespace Application\Core\Modules\Explorer;

class Explorer
{
    private $dirArray;
    
    public function __construct($container)
    {
        $settings = $container->get('settings');
        $tmp = [
            'skins'     => MAIN_DIR . '/skins/%s',
            'skinTwigCache' => MAIN_DIR . '/skins/%s/cache/twig',
            'skinCssCache' => MAIN_DIR . '/skins/%s/cache/css',
            'skinJsCache' => MAIN_DIR . '/skins/%s/cache/js',
            'skinCss'   => MAIN_DIR . '/skins/%s/assets/css',
            'skinJs'    => MAIN_DIR . '/skins/%s/assets/js',
            'adminTwig' => MAIN_DIR . '/public/admin/%s/tpl',
            'skinTpl'   => MAIN_DIR . '/skins/%s/tpl',
            'config'    => MAIN_DIR . '/environemt/Config/%s',
            'logs'      => MAIN_DIR . '/environemt/Logs/%s',
            'translations' => MAIN_DIR . '/environemt/Translations/%s',
            'logs'      => MAIN_DIR . '/environemt/Logs/%s',
            'update'    => MAIN_DIR . '/environemt/Update/%s',
            'cache'     => MAIN_DIR . $settings['cache']['cache_dir'],
            'skinCache' => MAIN_DIR . '/skins/'. $settings['twig']['skin'] . '/cache/%s',
            'plugins'    => MAIN_DIR . '/plugins/%s',
            'public'    => MAIN_DIR . '/public/%s',
            'mailsTpl'  => MAIN_DIR . '/public/mails/%s',
            'avators'   => MAIN_DIR . '/public/upload/avators/%s',
        ];
        
        $this->dirArray = $tmp;
        unset($tmp);
    }
    
    public function get(string $dir, string $file = '') : string
    {
        return sprintf($this->dirArray[$dir], $file);
    }
    
    public function getFileContent(string $dir, string $file = '') : string
    {
        return file_get_contents(sprintf($this->dirArray[$dir], $file));
    }
    
    public function getJsonArray(string $dir, string $file = '') : array
    {
        return json_decode(file_get_contents(sprintf($this->dirArray[$dir], $file)), true);
    }
    
    public function saveToJson(string $dir, string $file, $data)
    {
        return file_put_contents(
            sprintf($this->dirArray[$dir], $file),
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
    
    public function showError(string $message, int $code = 200, $html = '') : string
    {
        http_response_code($code);
        $data = self::getFileContent('public', 'pages/error.html');
        $style = self::getFileContent('public', 'pages/style.css');
        $message = sprintf($data, $style, $message, $code, $html);
        return $message;
    }
}
