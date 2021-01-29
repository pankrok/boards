<?php
declare(strict_types=1);


namespace Application\Core\Modules\Plugins;

use Illuminate\Database\Capsule\Manager as DB;

/**
*
*
*
**/
class PluginController
{
    
    /**
    * The copy of Twig view	*
    * @var twig
    **/
    
    protected $twig;
    
    /**
    * The instance of Event Dispatcher
    * @var EventDispatcher
    **/
    
    protected $dispacher;
    
    /**
    * The instance of plugin loader
    * @var EventDispatcher
    **/
    
    protected $pluginLoader;

    /**
    *
    * @todo remove 45 line!
    *
    **/
    
    protected static $tplDir;
    
    public function __construct($container)
    {
        $this->dispacher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $this->globalEvent = new PluginGlobalEventController($container);
        $this->adminEvent = new PluginAdminEventController($container);
        $this->pluginLoader = new PluginLoaderController($container->get('cache'), $container->get('settings')['twig']['skin']);
        self::$tplDir = $container->get('settings')['twig']['skin'];
        
        if ($container->get('settings')['plugins']['active'] === 1) {   
            if (is_array($this->pluginLoader->getPluginsList())) {
                foreach ($this->pluginLoader->getPluginsList() as $val) {
                    if (($val['active'] || isset($GLOBALS['admin'])) && class_exists('Plugins\\'.$val['plugin_name'].'\\'.$val['plugin_name'])) {
                        $pluginName = 'Plugins\\'. (string)$val['plugin_name'] .'\\'.(string)$val['plugin_name'];
                        $this->dispacher->addSubscriber(new $pluginName());
                    }
                }
            }
        }
    }
    
    
    
    public function addGlobalEvent($eventName)
    {
        $this->dispacher->dispatch($eventName, $this->globalEvent);
    }
    
    public function addAdminEvent($eventName)
    {
        $this->dispacher->dispatch($eventName, $this->adminEvent);
    }
    
    public function getPluginLoader()
    {
        return $this->pluginLoader;
    }
    
    
    public static function addToTpl($template, $find, $html)
    {
        $tpl = MAIN_DIR . '/skins/' . $GLOBALS['tplDir'] . '/tpl/' . $template;
        $find = str_replace('/', '\/', $find);
        $filecontent = file_get_contents($tpl);
        $pattern = '/('. $find .')(.*?)/s';
        $result = preg_replace($pattern, $find."\n".$html."\n", $filecontent);

        return file_put_contents($tpl, $result);
    }
    
    public static function removeFromTpl($template, $find)
    {
        $tpl = MAIN_DIR . '/skins/' . $GLOBALS['tplDir'] . '/tpl/' . $template;
        $filecontent = file_get_contents($tpl);
        $find = str_replace("/", '\/', $find);
        $pattern = '/('. $find .')(.*?)/s';
        $result = str_replace("\n".$find."\n", '', $filecontent);
        $result = str_replace('\\/', '/', $result);
        return file_put_contents($tpl, $result);
    }
    
    public static function addModule(string $prefix, string $name, string $html, string $side, int $order, array $pages): bool
    {
        if ($side !== 'top' || $side !== 'bottom' || $side !== 'left' || $side !== 'right') {
            $side = 'top';
        }
        
        $skinId = \Application\Models\SkinsModel::where('active', 1)->first()->id;
        $costumBox = \Application\Models\CostumBoxModel::create([
            'name_prefix' => $prefix,
            'name' => $name,
            'html' => $html
        ]);
        
        if (isset($costumBox)) {
            $box = \Application\Models\BoxModel::create([
                'costum_id' => $costumBox->id,
                'costum' => 1
            ]);
        } else {
            return false;
        }
        
        if (isset($box)) {
            $skinBoxes = \Application\Models\SkinsBoxesModel::create([
                'skin_id' => $skinId,
                'box_id' => $box->id,
                'side' => $side,
                'box_order' => $order,
                'active' => json_encode($pages)
            ]);
        } else {
            return false;
        }
        
        if (isset($skinBoxes)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function removeModule(string $name)
    {
        $deleteBox = \Application\Models\CostumBoxModel::where('name', $name)->delete();
        return $deleteBox;
    }
    
    public static function createTable($table, $query)
    {
        $db = require(MAIN_DIR . '/environment/Config/db_settings.php');
        $txt = 'CREATE TABLE IF NOT EXISTS `'.$db['database'].'`.`'. $db['prefix']. $table .'` ( '.$query.') ENGINE = InnoDB;';

        $return = DB::statement($txt);
        return $return;
    }
    
    public static function dropTable($table)
    {
        $db = require(MAIN_DIR . '/environment/Config/db_settings.php');
        $txt = 'DROP TABLE `'.$db['database'].'`.`'. $db['prefix']. $table . '`';
        $return = DB::statement($txt);
        return $return;
    }
}
