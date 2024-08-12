<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

/**
 * Class ilObjDHBWTrainingListGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilObjDHBWTrainingListGUI extends ilObjectPluginListGUI
{
    public function getGuiClass(): string
    {
        // TODO: Implement getGuiClass() method.
    }

    public function initCommands(): array
    {
        // TODO: Implement initCommands() method.
    }

    public function initType()
    {
        $this->setType(ilDHBWTrainingPlugin::PLUGIN_ID);
    }


}