<?php

declare(strict_types=1);

namespace Application\Core\Modules\Configuration;

class ConfigurationCore
{
    public static function saveConfig($cfg = [])
    {
        if (!empty($cfg)) {
            $configuration = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/settings.json'), true);
            !empty($cfg['main_page_name']) 	? 	$configuration['board']['main_page_name'] = $cfg['main_page_name'] : null;
            !empty($cfg['main_mail'])	? 	$configuration['board']['main_mail'] = $cfg['main_mail'] : null;
            (!empty($cfg['page_off']))	? 	$configuration['board']['page_off'] = $cfg['page_off'] : null;
            !empty($cfg['fallback']) 	?	$configuration['translations']['fallback'] = $cfg['fallback'] : null;
            !empty($cfg['boards'])		? 	$configuration['pagination']['boards'] = $cfg['boards'] : null;
            !empty($cfg['plots'])		? 	$configuration['pagination']['plots'] = $cfg['plots'] : null;
            !empty($cfg['users'])		? 	$configuration['pagination']['users'] = $cfg['users'] : null;
            !empty($cfg['default_group'])? 	$configuration['board']['default_group'] = intval($cfg['default_group']) : null;
            $cfg['plugins'] == '1'	    ? 	$configuration['plugins']['active'] = 1 : $configuration['plugins']['active'] = 0;
            $cfg['boards_off'] == '1'	? 	$configuration['board']['boards_off'] = 1 : $configuration['board']['boards_off'] = 0;
            $cfg['confirm_reg'] == '1'	? 	$configuration['board']['confirm_reg'] = 1 : $configuration['board']['confirm_reg'] = 0;
                 
            return file_put_contents(MAIN_DIR . '/environment/Config/settings.json', json_encode($configuration, JSON_PRETTY_PRINT));
        }
        return false;
    }
}
