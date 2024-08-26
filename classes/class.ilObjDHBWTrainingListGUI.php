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
        return ilObjDHBWTrainingGUI::class;
    }

    public function initCommands(): array
    {
        return [
            [
                "permission" => "read",
                "cmd" => "start",
                "default" => true,
            ],
        ];
    }

    public function initType()
    {
        $this->setType(ilDHBWTrainingPlugin::PLUGIN_ID);
    }


}