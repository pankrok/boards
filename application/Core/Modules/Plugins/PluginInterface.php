<?php
declare(strict_types=1);

namespace Application\Core\Modules\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface PluginInterface extends EventSubscriberInterface
{
    public static function info() : array;
    public static function activation() : bool;
    public static function deactivation() : bool;
    public static function install() : bool;
    public static function uninstall() : bool;
}
