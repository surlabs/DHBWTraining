<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use platform\DHBWTrainingException;
use ui\DHBWExportsTable;
use ui\DHBWParticipantsTable;

/**
 * Class ilObjDHBWTrainingGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjDHBWTrainingGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjDHBWTrainingGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 */
class ilObjDHBWTrainingGUI extends ilObjectPluginGUI
{
    private Factory $factory;
    private Renderer $renderer;

    public function __construct(int $a_ref_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();

        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
    }

    public function getAfterCreationCmd(): string
    {
        return $this->getStandardCmd();
    }

    public function getStandardCmd(): string
    {
        return "start";
    }

    public function performCommand(string $cmd): void
    {
        $this->{$cmd}();
    }

    public function getType(): string
    {
        return ilDHBWTrainingPlugin::PLUGIN_ID;
    }

    protected function setTabs(): void
    {
        $this->tabs->addTab("start", $this->plugin->txt("object_start"), $this->ctrl->getLinkTarget($this, "start"));

        if ($this->checkPermissionBool("write")) {
            $this->tabs->addTab("participants", $this->plugin->txt("object_participants"), $this->ctrl->getLinkTarget($this, "participants"));
            $this->tabs->addTab("settings", $this->plugin->txt("object_settings"), $this->ctrl->getLinkTarget($this, "settings"));
            $this->tabs->addTab("export", $this->plugin->txt("object_export"), $this->ctrl->getLinkTarget($this, "export"));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs->addTab("perm_settings", $this->lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass(array(
                get_class($this),
                "ilPermissionGUI",
            ), "perm"));
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function start(): void
    {
        global $DIC;

        $this->tabs->activateTab("start");

        $start_button = $this->factory->button()->standard($this->plugin->txt("object_start_training"), $this->ctrl->getLinkTarget($this, "startTraining"));

        $DIC->toolbar()->addStickyItem($start_button);
    }

    /**
     * @throws ilException
     * @throws DHBWTrainingException
     */
    private function participants()
    {
        $this->tabs->activateTab("participants");

        $data_factory = new \ILIAS\Data\Factory();

        $table = $this->factory->table()->data(
            '',
            [
                'name' => $this->factory->table()->column()->text($this->plugin->txt("participants_table_name"))->withIsSortable(true),
                'username' => $this->factory->table()->column()->text($this->plugin->txt("participants_table_usr_name"))->withIsSortable(true),
                'learning_progress' => $this->factory->table()->column()->text($this->plugin->txt("participants_table_learning_progress"))->withIsSortable(true),
                'first_access' => $this->factory->table()->column()->date($this->plugin->txt("participants_table_first_access"), $data_factory->dateFormat()->standard())->withIsSortable(true),
                'last_access' => $this->factory->table()->column()->date($this->plugin->txt("participants_table_last_access"), $data_factory->dateFormat()->standard())->withIsSortable(true),
            ],
            new DHBWParticipantsTable()
        );

        $this->tpl->addCss($this->plugin->getDirectory() . "/templates/css/fix_table_width.css");

        $this->tpl->setContent($this->renderer->render($table->withRequest($this->request)));
    }

    /**
     * @throws ilCtrlException
     */
    private function settings(): void
    {
        $this->tabs->activateTab("settings");

        $form_action = $this->ctrl->getLinkTarget($this, "settings");
        $this->tpl->setContent($this->renderSettingsForm($form_action, $this->buildSettingsForm()));
    }

    private function renderSettingsForm(string $form_action, array $sections): string
    {
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        $saving_info = "";

        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            if ($result) {
                $saving_info = $this->saveSettings();
            }
        }

        return $saving_info . $this->renderer->render($form);
    }

    private function buildSettingsForm(): array
    {
        $inputs_basic = array();
        $recommender_system = array();

        $inputs_basic[] = $this->factory->input()->field()->text(
            $this->plugin->txt('object_settings_title')
        )->withValue($this->object->getTitle())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->setTitle($v);
            }
        ))->withRequired(true);

        $inputs_basic[] = $this->factory->input()->field()->textarea(
            $this->plugin->txt('object_settings_description')
        )->withValue($this->object->getDescription())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->setDescription($v);
            }
        ));

        $inputs_basic[] = $this->factory->input()->field()->checkbox(
            $this->plugin->txt('object_settings_online'),
            $this->plugin->txt('object_settings_online_info')
        )->withValue($this->object->getTraining()->isOnline())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setOnline($v);
            }
        ));

        $inputs_basic[] = $this->factory->input()->field()->checkbox(
            $this->plugin->txt('object_settings_learning_progress'),
            $this->plugin->txt('object_settings_learning_progress_info')
        )->withValue($this->object->getTraining()->isLearningProgress())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setLearningProgress($v);
            }
        ));

        $recommender_system[] = $this->factory->input()->field()->text(
            $this->plugin->txt('object_settings_installation_key')
        )->withValue($this->object->getTraining()->getInstallationKey())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setInstallationKey($v);
            }
        ))->withRequired(true);

        $recommender_system[] = $this->factory->input()->field()->text(
            $this->plugin->txt('object_settings_secret')
        )->withValue($this->object->getTraining()->getSecret())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setSecret($v);
            }
        ))->withRequired(true);

        $external_server_group = $this->factory->input()->field()->group(array(
            $this->factory->input()->field()->text(
                $this->plugin->txt('object_settings_url')
            )->withValue($this->object->getTraining()->getUrl())->withAdditionalTransformation($this->refinery->custom()->transformation(
                function ($v) {
                    $this->object->getTraining()->setUrl($v);
                }
            ))->withRequired(true)
        ), $this->plugin->txt('object_settings_external_server'));

        $built_in_server_group = $this->factory->input()->field()->group(array(

        ), $this->plugin->txt('object_settings_built_in_server'), $this->plugin->txt('object_settings_built_in_server_info'));

        $recomender_system_server = $this->factory->input()->field()->switchableGroup(array(
            "external_server" => $external_server_group,
            "built_in_server" => $built_in_server_group
        ), $this->plugin->txt('object_settings_server'));

        if ($this->object->getTraining()->isRecommenderSystemServer()) {
            $recomender_system_server = $recomender_system_server->withValue("built_in_server");
        } else {
            $recomender_system_server = $recomender_system_server->withValue("external_server");
        }

        $recomender_system_server = $recomender_system_server->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setRecommenderSystemServer($v[0] == "built_in_server");
            }
        ));

        $recommender_system[] = $recomender_system_server;

        $recommender_system[] = $this->factory->input()->field()->checkbox(
            $this->plugin->txt('object_settings_log')
        )->withValue($this->object->getTraining()->isLog())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setLog($v);
            }
        ));

        return array(
            $this->factory->input()->field()->section($inputs_basic, $this->plugin->txt("object_settings_basic"), ""),
            $this->factory->input()->field()->section($recommender_system, $this->plugin->txt("object_settings_recommender_system"), ""),
        );
    }

    private function saveSettings(): string
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();

        $this->object->update();

        return $renderer->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('object_settings_msg_success')));
    }

    private function export()
    {
        global $DIC;
        $this->tabs->activateTab("export");

        $generate_export = $this->factory->button()->standard($this->plugin->txt("object_export_generate"), $this->ctrl->getLinkTarget($this, "generateExport"));

        $DIC->toolbar()->addStickyItem($generate_export);

        $data_factory = new \ILIAS\Data\Factory();

        $table = $this->factory->table()->data(
            '',
            [
                'file' => $this->factory->table()->column()->text($this->plugin->txt("exports_table_file"))->withIsSortable(true),
                'size' => $this->factory->table()->column()->text($this->plugin->txt("exports_table_size"))->withIsSortable(true),
                'date' => $this->factory->table()->column()->date($this->plugin->txt("exports_table_date"), $data_factory->dateFormat()->standard())->withIsSortable(true),
                'actions' => $this->factory->table()->column()->link($this->plugin->txt("exports_table_actions"))->withIsSortable(false)
            ],
            new DHBWExportsTable()
        );

        $this->tpl->addCss($this->plugin->getDirectory() . "/templates/css/fix_table_width.css");

        $this->tpl->setContent($this->renderer->render($table->withRequest($this->request)));
    }

    private function generateExport()
    {
        dump("Generate export"); exit();
    }

    private function downloadExport()
    {
        $file = $this->request->getQueryParams()["file"];

        dump("Download: $file"); exit();
    }
}