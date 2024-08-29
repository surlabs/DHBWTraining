<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use ILIAS\UI\Factory;
use objects\Participant;
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
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    private function start()
    {
        ilSession::set(RecommenderCurl::KEY_RESPONSE_PROGRESS_METER, '');
        ilSession::set(RecommenderCurl::KEY_RESPONSE_PROGRESS_BAR, '');

        $recommender = new RecommenderCurl($this->training, $this->response);

        $recommender->start();

        $this->proceedWithReturnOfRecommender();
    }

    /**
     * @throws DHBWTrainingException
     * @throws ilCtrlException
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     */
    private function proceedWithReturnOfRecommender()
    {
        global $DIC, $ilUser;
        $output = "";

        if ($this->training->isLearningProgress() && $this->response->getLearningProgressStatus() !== null) {
            $participant = Participant::findOrCreateParticipantByUsrAndTrainingObjectId($ilUser->getId(), $this->training->getId());

            $participant->setStatus($this->response->getLearningProgressStatus());
            $participant->setLastAccess(new DateTime());
            $participant->save();
        }

        if (!empty($this->response->getCompetences())) {
            global $ilDB;
            foreach ($this->response->getCompetences() as $competence_id => $level_id) {
                $sql = "select id from skl_level as sl join qpl_qst_skl_assigns as ql on ql.skill_base_fi = sl.skill_id where ql.skill_tref_fi = $competence_id and sl.nr = $level_id limit 1";
                $set = $ilDB->query($sql);
                $row = $ilDB->fetchAssoc($set);

                ilPersonalSkill::addPersonalSkill($ilUser->getId(), $competence_id);
                ilBasicSkill::writeUserSkillLevelStatus(
                    $row['id'],
                    $ilUser->getId(),
                    $this->training->getId(),
                    $competence_id,
                    ilBasicSkill::ACHIEVED,
                    true
                );
            }
        }

        switch ($this->response->getStatus()) {
            case RecommenderResponse::STATUS_SUCCESS:
                if ($this->response->getAnswerResponse() != "") {
                    $formatter = new ilAssSelfAssessmentQuestionFormatter();

                    $this->response->addSendMessage($formatter->format($this->response->getFeedback()), $this->response->getFeedbackType());
                }

                if ($this->response->getMessage()) {
                    $this->response->addSendMessage($this->response->getMessage(), $this->response->getMessageType());
                }

                if ($this->response->getAnswerResponse() != "") {
                    $question = RecommenderResponse::getQuestionByRecomander($_POST['recomander_id']);
                    $output = $this->initAnsweredQuestionForm($question);

                    break;
                }

                switch ($this->response->getResponseType()) {
                    case RecommenderResponse::RESPONSE_TYPE_NEXT_QUESTION:
                        $question = RecommenderResponse::getQuestionByRecomander($this->response->getRecomander());
                        $output = $this->initQuestionForm($question);
                        break;
                    case RecommenderResponse::RESPONSE_TYPE_TEST_IS_FINISHED:
                        $this->response->sendMessages();
                        $DIC->ctrl()->redirect($this, "index");
                        return;
                    default:
                        $output = $this->initSeparatorForm();
                        break;
                }
                break;

            case RecommenderResponse::STATUS_ERROR:
                if ($this->training->isLog()) {
                    $this->response->addSendFailure(vsprintf($this->plugin->txt("error_recommender_system"), [$this->response->getMessage()]));
                }
                break;

            default:
                break;
        }

        $this->response->sendMessages();

        $DIC->ui()->mainTemplate()->setRightContent($this->response->getProgressMetersHtml());

        $DIC->ui()->mainTemplate()->setContent($this->response->renderProgressBar() . $output);
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     */
    protected function initAnsweredQuestionForm($question) : string
    {
        global $DIC, $ilUser;

        $tpl = new ilTemplate($this->plugin->getDirectory() . '/templates/default/tpl.questions_answered_form.html', true, true);
        $tpl->setVariable("ACTION", $DIC->ctrl()->getLinkTarget($this, "sendRating"));
        $q_gui = assQuestionGUI::_getQuestionGUI("", (int) $question['question_id']);

        if (!is_object($q_gui)) {
            $this->response->addSendFailure(vsprintf($this->plugin->txt("error_no_question_id"), [$question['question_id']]));

            return $tpl->get();
        }
        $previewSession = new ilAssQuestionPreviewSession($ilUser->getId(), $question['question_id']);
        $q_gui->setPreviewSession($previewSession);

//        /**
//         * shuffle like before
//         */
//        $shuffler = new ilArrayElementShuffler();
//        $shuffler->setSeed($q_gui->object->getId() + $ilUser->getId());
//        $q_gui->object->setShuffle(1);
//        $q_gui->object->setShuffler($shuffler);

        $tpl->setCurrentBlock('question');
        $tpl->setVariable('TITLE', $q_gui->object->getTitle());
        $tpl->setVariable('QUESTION', $q_gui->getPreview());

        $tpl->parseCurrentBlock();

        $tpl->setVariable('DIFFICULTY', $this->plugin->txt('difficulty'));
        $tpl->setVariable('CANCEL_BTN_VALUE', 'cancel');
        $tpl->setVariable('CANCEL_BTN_TEXT', $this->plugin->txt('interrupt'));
        $tpl->setVariable('BTN_QST_LEVEL1_VALUE', '1');
        $tpl->setVariable('BTN_QST_LEVEL1_TEXT', $this->plugin->txt('level1'));
        $tpl->setVariable('BTN_QST_LEVEL2_VALUE', '2');
        $tpl->setVariable('BTN_QST_LEVEL2_TEXT', $this->plugin->txt('level2'));
        $tpl->setVariable('BTN_QST_LEVEL3_VALUE', '3');
        $tpl->setVariable('BTN_QST_LEVEL3_TEXT', $this->plugin->txt('level3'));
        $tpl->setVariable('BTN_QST_LEVEL4_VALUE', '4');
        $tpl->setVariable('BTN_QST_LEVEL4_TEXT', $this->plugin->txt('level4'));

        $tpl->setVariable('QUESTION_ID', $question['question_id']);
        $tpl->setVariable('RECOMANDER_ID', $question['recomander_id']);
        $previewSession->setParticipantsSolution(null);
        return $tpl->get();
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     */
    protected function initQuestionForm($question) : string
    {
        global $DIC, $ilUser;

        $tpl = new ilTemplate($this->plugin->getDirectory() . '/templates/default/tpl.questions_form.html', true, true);
        $tpl->setVariable("ACTION", $DIC->ctrl()->getLinkTarget($this, "answer"));

        $q_gui = assQuestionGUI::_getQuestionGUI("", (int) $question['question_id']);

        if (!is_object($q_gui)) {
            $this->response->addSendFailure(vsprintf($this->plugin->txt("error_no_question_id"), [$question['question_id']]));

            return $tpl->get();
        }

        $previewSession = new ilAssQuestionPreviewSession($ilUser->getId(), $question['question_id']);

        $previewSession->init();
        $q_gui->setPreviewSession($previewSession);

//        /**
//         * Shuffle!
//         */
//        $shuffler = new ilArrayElementShuffler();
//        $shuffler->setSeed($q_gui->object->getId() + $ilUser->getId());
//        $q_gui->object->setShuffle(1);
//        $q_gui->object->setShuffler($shuffler);

        $q_gui->setPreviousSolutionPrefilled(true);
        $tpl->setCurrentBlock('question');
        $tpl->setVariable('TITLE', $q_gui->object->getTitle());
        $tpl->setVariable('QUESTION', $q_gui->getPreview());
        $tpl->parseCurrentBlock();
        $tpl->setVariable('CANCEL_BTN_VALUE', 'cancel');
        $tpl->setVariable('CANCEL_BTN_TEXT', $this->plugin->txt('interrupt'));
        $tpl->setVariable('NEXT_BTN_VALUE', 'next');
        $tpl->setVariable('PROCEED_BTN_TEXT', $this->plugin->txt('submit_answer'));
        $tpl->setVariable('QUESTION_ID', $question['question_id']);
        $tpl->setVariable('RECOMANDER_ID', $question['recomander_id']);

        return $tpl->get();
    }


    /**
     * @throws ilCtrlException
     */
    protected function initSeparatorForm() : string
    {
        global $DIC;

        $tpl = new ilTemplate('tpl.questions_form.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/DhbwTraining');
        $tpl->setVariable("ACTION", $DIC->ctrl()->getLinkTarget($this, "proceed"));
        $tpl->setVariable('CANCEL_BTN_VALUE', 'cancel');
        $tpl->setVariable('CANCEL_BTN_TEXT', $this->plugin->txt('interrupt'));
        $tpl->setVariable('NEXT_BTN_VALUE', 'next');
        $tpl->setVariable('PROCEED_BTN_TEXT', $this->plugin->txt('submit_answer'));
        $tpl->setVariable('RECOMANDER_ID', $this->response->getRecomander());

        return $tpl->get();
    }
}