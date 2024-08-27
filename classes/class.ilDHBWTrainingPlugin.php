<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

/**
 * Class ilDHBWTrainingPlugin
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilDHBWTrainingPlugin extends ilRepositoryObjectPlugin
{

    const PLUGIN_ID = 'xdht';

    const PLUGIN_NAME = 'DHBWTraining';

    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            global $DIC;

            $component_repository = $DIC["component.repository"];

            $info = null;
            $plugin_name = self::PLUGIN_NAME;
            $info = $component_repository->getPluginByName($plugin_name);

            $component_factory = $DIC["component.factory"];

            $plugin_obj = $component_factory->getPlugin($info->getId());

            self::$instance = $plugin_obj;
        }

        return self::$instance;
    }

    protected function uninstallCustom(): void
    {
        // TODO: Implement uninstallCustom() method.
    }

}
