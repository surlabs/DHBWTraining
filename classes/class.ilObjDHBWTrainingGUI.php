<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use objects\DHBWParticipant;
use platform\DHBWTrainingException;
use ui\DHBWParticipantsTable;

/**
 * Class ilObjDHBWTrainingGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjDHBWTrainingGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjDHBWTrainingGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjDHBWTrainingGUI: DHBWMainGUI
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

    /**
     * @throws ilCtrlException
     */

    public function performCommand(string $cmd): void
    {
        global $DIC;

        $next_class = $DIC->ctrl()->getNextClass($this);

        if (strtolower($next_class) == strtolower(DHBWMainGUI::class)) {
            $this->tabs->activateTab("start");

            $this->ctrl->forwardCommand(new DHBWMainGUI($this->object->getTraining()));

            return;
        }

        $this->setTitleAndDescription();
        $this->{$cmd}();
    }

    public function getType(): string
    {
        return ilDHBWTrainingPlugin::PLUGIN_ID;
    }

    protected function setTabs(): void
    {
        $this->tabs->addTab("start", $this->plugin->txt("object_start"), $this->ctrl->getLinkTargetByClass(DHBWMainGUI::class, "index"));

        if ($this->checkPermissionBool("write")) {
            $this->tabs->addTab("participants", $this->plugin->txt("object_participants"), $this->ctrl->getLinkTarget($this, "participants"));
            $this->tabs->addTab("settings", $this->plugin->txt("object_settings"), $this->ctrl->getLinkTarget($this, "settings"));
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
        $this->ctrl->redirectByClass(DHBWMainGUI::class);
    }

    /**
     * @throws DHBWTrainingException
     */
    private function participants()
    {
        $this->tabs->activateTab("participants");

        $data_factory = new \ILIAS\Data\Factory();

        $participants_data = new DHBWParticipantsTable();

        $participants_data->setRecords(DHBWParticipant::loadParticipantsArrayByTrainingId($this->object->getId()));

        $date_format = $data_factory->dateFormat()->custom()->weekday()->comma()->space()->day()->dot()->month()->dot()->year()->space()->hours24()->colon()->minutes()->colon()->seconds()->get();

        $table = $this->factory->table()->data(
            '',
            [
                'name' => $this->factory->table()->column()->text($this->plugin->txt("participants_table_name"))->withIsSortable(true),
                'username' => $this->factory->table()->column()->text($this->plugin->txt("participants_table_usr_name"))->withIsSortable(true),
                'learning_progress' => $this->factory->table()->column()->text($this->plugin->txt("participants_table_learning_progress"))->withIsSortable(true),
                'first_access' => $this->factory->table()->column()->date($this->plugin->txt("participants_table_first_access"), $date_format)->withIsSortable(true),
                'last_access' => $this->factory->table()->column()->date($this->plugin->txt("participants_table_last_access"), $date_format)->withIsSortable(true),
            ],
            $participants_data
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

        if ($this->object->getTraining()->getRecommenderSystemServer() == 1) {
            $recomender_system_server = $recomender_system_server->withValue("external_server");
        } else {
            $recomender_system_server = $recomender_system_server->withValue("built_in_server");
        }

        $recomender_system_server = $recomender_system_server->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setRecommenderSystemServer($v[0] == "external_server" ? 1 : 2);
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
}