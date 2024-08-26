<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

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
            $this->plugin->txt('object_settings_learning_progress'),
            $this->plugin->txt('object_settings_learning_progress_info')
        )->withValue($this->object->getTraining()->isLearningProgress())->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                $this->object->getTraining()->setLearningProgress($v);
            }
        ));

        return array(
            $this->factory->input()->field()->section($inputs_basic, $this->plugin->txt("object_settings_basic"), ""),
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