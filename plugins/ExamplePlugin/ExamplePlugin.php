<?php
declare(strict_types=1);
namespace Plugins\ExamplePlugin;

use Application\Core\Modules\Plugins\PluginInterface;
use Application\Core\Modules\Plugins\PluginController;

class ExamplePlugin implements PluginInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'home.loaded' => 'myFooPlugin',
            'plugin.contoller.ExamplePlugin' => 'myAdminFunc'
            ];
    }

    public static function info() : array
    {
        return [
            'name' => 'Example Plugin',
            'version' => '1.1',
            'panel' => true,
            'boards_v' => '1.1.20',
            'author' => 'PanKrok',
            'website' => 's89.eu',
            'desc' => 'This is an example plugin.'
        ];
    }
    
    public static function activation() : bool
    {
        PluginController::addToTpl('home.twig', '<!-- /board -->', '{{ plugin_bar | raw }}');
        
        $pagesArr = [
                'home' => 1,
                'category.getCategory' => 0,
                'board.getBoard' => 0,
                'board.getPlot' => 0,
                'board.newPlot' => 0,
                'auth.signin' => 0,
                'auth.signup' => 0,
                'user.profile' => 0,
                'userlist' => 0
            ];
            
        $module = PluginController::addModule('', 'Example', 'Example plugin module', 'right', 0, $pagesArr);
        $module ? $ret = true : $ret = false;
        
        return $ret;
    }
    
    public static function deactivation() : bool
    {
        PluginController::removeModule('Example');
        PluginController::removeFromTpl('home.twig', '{{ plugin_bar | raw }}');
        return true;
    }
    
    public static function install() : bool
    {
        return true;
    }
    
    public static function uninstall() : bool
    {
        return true;
    }

    public function myFooPlugin($data)
    {
        $text = file_get_contents($data->getPluginsDir().'/ExamplePlugin/data/example.txt');
        $content = sprintf($text, $data->translate('Holy guacamole!'), $data->translate('This is a plugin example!'));
                    
        $data->setTwigData('bar', $content);
    }
    
    public function myAdminFunc($data)
    {
        if (isset($_POST['plugin_data'])) {
            file_put_contents($data->getPluginsDir().'/ExamplePlugin/data/example.txt', $_POST['plugin_data']);
        }
        
        $content = file_get_contents($data->getPluginsDir().'/ExamplePlugin/data/example.txt');
        $data->setAdminTwigData('data', $content);
    }
    
    public function translations($data)
    {
        $trans =
        [
            1=>['lang' => 'pl_PL',
                'name' => 'plugin',
                'translations' => [
                    'Holy guacamole!' => 'Jasny gwint!',
                    'This is a plugin example!' => 'To przykładowy plugin!'
                ]
            ],
            2=>['lang' => 'en_US',
                'name' => 'plugin',
                'translations' => [
                    'Holy guacamole!' => 'Holy guacamole!',
                    'This is a plugin example!' => 'This is a plugin example!'
                ]
            ]
        ];
    }
}
