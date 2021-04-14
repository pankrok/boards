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
            'boards_v' => '=.0.1.*',
            'author' => 'PanKrok',
            'website' => 's89.eu',
            'desc' => 'This is an example plugin.'
        ];
    }
    
    public static function activation() : bool
    {
        $tpl = PluginController::addToTpl('home.twig', '<!-- /board -->', '{{ include(template_from_string(plugin_bar | raw)) }}');
        $tpl ? $ret = true : $ret = false;
        return $ret;
    }
    
    public static function deactivation() : bool
    {
        $tpl = PluginController::removeFromTpl('home.twig', '{{ include(template_from_string(plugin_bar | raw)) }}');
        $tpl ? $ret = true : $ret = false;
        return $ret;
    }
    
    public static function install() : bool
    {
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
    
    public static function uninstall() : bool
    {
        $module = PluginController::removeModule('Example');
        $module ? $ret = true : $ret = false;
        return $ret;
    }

    public function myFooPlugin($data)
    {
        $text = file_get_contents($data->getPluginsDir().'/ExamplePlugin/data/example.txt');
        $data->setTwigData('bar', $text);
    }
    
    public function myAdminFunc($data)
    {
        if (isset($_POST['plugin_data'])) {
            file_put_contents($data->getPluginsDir().'/ExamplePlugin/data/example.txt', $_POST['plugin_data']);
        }

        $content = file_get_contents($data->getPluginsDir().'/ExamplePlugin/data/example.txt');
        $data->setAdminTwigData('data', $content);
    }
}
