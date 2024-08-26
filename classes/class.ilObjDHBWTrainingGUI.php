<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

/**
 * Class ilObjDHBWTrainingGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjDHBWTrainingGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjDHBWTrainingGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 */
class ilObjDHBWTrainingGUI extends ilObjectPluginGUI
{
    public function getAfterCreationCmd(): string
    {
        return $this->getStandardCmd();
    }

    public function getStandardCmd(): string
    {
        return "index";
    }

    public function performCommand(string $cmd): void
    {
        // TODO: Implement performCommand() method.
    }

    public function getType(): string
    {
        return ilDHBWTrainingPlugin::PLUGIN_ID;
    }
}