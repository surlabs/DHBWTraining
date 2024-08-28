<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use ILIAS\UI\Factory;
use objects\RecommenderCurl;
use objects\RecommenderResponse;
use objects\Training;
use platform\DHBWTrainingException;

/**
 * Class DHBWMainGUI
 *
 * @ilCtrl_Calls      DHBWMainGUI: ilAssQuestionPageGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWMainGUI
{
    private Factory $factory;
    private ilCtrl $ctrl;
    private ilDHBWTrainingPlugin $plugin;
    private Training $training;
    private RecommenderResponse $response;


    public function __construct(Training $training)
    {
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->ctrl = $DIC->ctrl();
        $this->plugin = ilDHBWTrainingPlugin::getInstance();

        $this->training = $training;
        $this->response = new RecommenderResponse();
    }

    public function performCommand(string $cmd): void
    {
        $this->{$cmd}();
    }

    public function executeCommand()
    {
        global $DIC;

        $this->performCommand($DIC->ctrl()->getCmd("index"));
    }

    /**
     * @throws ilCtrlException
     */
    private function index()
    {
        global $DIC;

        $start_button = $this->factory->button()->standard($this->plugin->txt("object_start_training"), $this->ctrl->getLinkTarget($this, "start"));

        $DIC->toolbar()->addStickyItem($start_button);
    }

    /**
     * @throws DHBWTrainingException
     */
    private function start()
    {
        ilSession::set(RecommenderCurl::KEY_RESPONSE_PROGRESS_METER, '');
        ilSession::set(RecommenderCurl::KEY_RESPONSE_PROGRESS_BAR, '');

        $recommender = new RecommenderCurl($this->training, $this->response);

        $recommender->start();

        $this->proceedWithReturnOfRecommender();
    }

    private function proceedWithReturnOfRecommender()
    {
        // TODO: Implement this method
    }
}