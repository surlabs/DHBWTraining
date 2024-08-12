<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use platform\DHBWTrainingConfig;

/**
 * Class ilDHBWTrainingConfigGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilDHBWTrainingConfigGUI: ilObjComponentSettingsGUI
 */
class ilDHBWTrainingConfigGUI extends ilPluginConfigGUI
{

    private DHBWTrainingConfig $config;

    public function performCommand(string $cmd): void
    {
        // TODO: Implement performCommand() method.
    }

}