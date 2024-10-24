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
            [
                "permission" => "write",
                "cmd" => "participants",
                "txt" => $this->txt("object_participants")
            ],
            [
                "permission" => "write",
                "cmd" => "settings",
                "txt" => $this->txt("object_settings")
            ]
        ];
    }

    public function initType()
    {
        $this->setType(ilDHBWTrainingPlugin::PLUGIN_ID);
    }

    public function getCustomProperties($a_prop): array
    {
        if (!isset($this->obj_id)) {
            return [];
        }

        $props = parent::getCustomProperties($a_prop);

        if (ilObjDHBWTrainingAccess::_isOffline($this->obj_id)) {
            $props[] = array(
                'alert' => true,
                'newline' => true,
                'property' => 'Status',
                'value' => 'Offline'
            );
        }

        return $props;
    }

    public function getAlertProperties(): array
    {
        if (!isset($this->obj_id)) {
            return [];
        }
        $props = parent::getAlertProperties();
        if (ilObjDHBWTrainingAccess::_isOffline($this->obj_id)) {
            $props[] = array(
                'alert' => true,
                'newline' => true,
                'property' => 'Status',
                'value' => 'Offline'
            );
        }
        return $props;
    }
}