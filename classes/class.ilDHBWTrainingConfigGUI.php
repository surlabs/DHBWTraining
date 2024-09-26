<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use platform\DHBWTrainingConfig;
use platform\DHBWTrainingException;

/**
 * Class ilDHBWTrainingConfigGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilDHBWTrainingConfigGUI: ilObjComponentSettingsGUI
 */
class ilDHBWTrainingConfigGUI extends ilPluginConfigGUI
{
    protected Factory $factory;
    protected Renderer $renderer;
    protected \ILIAS\Refinery\Factory $refinery;
    protected ilCtrl $control;
    protected ilGlobalTemplateInterface $tpl;
    protected $request;

    /**
     * @throws DHBWTrainingException
     * @throws ilException
     */
    public function performCommand($cmd): void
    {
        global $DIC;
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->control = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();

        switch ($cmd) {
            case "configure":
                DHBWTrainingConfig::load();
                $this->control->setParameterByClass('ilDHBWTrainingConfigGUI', 'cmd', 'configure');
                $form_action = $this->control->getLinkTargetByClass("ilDHBWTrainingConfigGUI", "configure");
                $rendered = $this->renderForm($form_action, $this->buildForm());
                break;
            default:
                throw new ilException("command not defined");

        }

        $this->tpl->setContent($rendered);
    }

    /**
     * @throws DHBWTrainingException
     */
    private function buildForm(): array
    {
        $learning_progress = $this->factory->input()->field()->checkbox(
            $this->plugin_object->txt('config_learning_progress'),
            $this->plugin_object->txt('config_learning_progress_info')
        )->withValue((bool) DHBWTrainingConfig::get("learning_progress"))->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                DHBWTrainingConfig::set('learning_progress', $v);
            }
        ));

        $salt = $this->factory->input()->field()->text(
            $this->plugin_object->txt('config_salt'),
            $this->plugin_object->txt('config_salt_info')
        )->withValue((string) DHBWTrainingConfig::get("salt"))->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                DHBWTrainingConfig::set('salt', $v);
            }
        ))->withRequired(true);

        return array(
            $learning_progress,
            $salt
        );
    }

    /**
     * @throws DHBWTrainingException
     */
    private function renderForm(string $form_action, array $sections): string
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
                $saving_info = $this->save();

                $form = $this->factory->input()->container()->form()->standard(
                    $form_action,
                    $this->buildForm()
                );
            }
        }

        return $saving_info . $this->renderer->render($form);
    }

    public function save(): string
    {
        DHBWTrainingConfig::save();
        return $this->renderer->render($this->factory->messageBox()->success($this->plugin_object->txt('config_msg_success')));
    }
}
