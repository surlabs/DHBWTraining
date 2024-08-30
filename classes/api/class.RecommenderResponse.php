<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace api;

use ilGlobalTemplateInterface;
use ilProgressBar;
use objects\DHBWProgressMeter;

/**
 * Class RecommenderResponse
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class RecommenderResponse
{
    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";
    const RESPONSE_TYPE_PAGE = 1;
    const RESPONSE_TYPE_IN_PROGRESS = 2;
    const RESPONSE_TYPE_NEXT_QUESTION = 3;
    const RESPONSE_TYPE_TEST_IS_FINISHED = 4;
    const MESSAGE_TYPE_SUCCESS = ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS;
    const MESSAGE_TYPE_INFO = ilGlobalTemplateInterface::MESSAGE_TYPE_INFO;
    const MESSAGE_TYPE_QUESTION = ilGlobalTemplateInterface::MESSAGE_TYPE_QUESTION;
    const MESSAGE_TYPE_FAILURE = ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE;

    private string $status = "";
    private int $response_type = 0;
    private string $recomander_id = "";
    private string $message = "";
    private string $message_type = self::MESSAGE_TYPE_INFO;
    private string $answer_response = "";
    private string $answer_response_type = self::MESSAGE_TYPE_INFO;
    private bool $progress_display = false;
    private ?float $progress = null;
    private string $progress_type = self::MESSAGE_TYPE_INFO;
    private ?int $learning_progress_status = null;
    private array $competences = [];
    private array $progress_meters = [];
    private array $send_success = [];
    private array $send_info = [];
    private array $send_question = [];
    private array $send_failure = [];
    private bool $correct;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getResponseType(): int
    {
        return $this->response_type;
    }

    public function setResponseType(int $response_type): void
    {
        $this->response_type = $response_type;
    }

    public function getRecomander(): string
    {
        return $this->recomander_id;
    }

    public function setRecomander(string $recomander_id): void
    {
        $this->recomander_id = $recomander_id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessageType(): string
    {
        return $this->message_type;
    }

    public function setMessageType(string $message_type): void
    {
        $this->message_type = $message_type;
    }

    public function getAnswerResponse(): string
    {
        return $this->answer_response;
    }

    public function setAnswerResponse(string $answer_response): void
    {
        $this->answer_response = $answer_response;
    }

    public function getAnswerResponseType(): string
    {
        return $this->answer_response_type;
    }

    public function setAnswerResponseType(string $answer_response_type): void
    {
        $this->answer_response_type = $answer_response_type;
    }

    public function isProgressDisplay(): bool
    {
        return $this->progress_display;
    }

    public function setProgressDisplay(bool $progress_display): void
    {
        $this->progress_display = $progress_display;
    }

    public function getProgress(): ?float
    {
        return $this->progress;
    }

    public function setProgress(?float $progress): void
    {
        $this->progress = $progress;
    }

    public function getProgressType(): string
    {
        return $this->progress_type;
    }

    public function setProgressType(string $progress_type): void
    {
        $this->progress_type = $progress_type;
    }

    public function getLearningProgressStatus(): ?int
    {
        return $this->learning_progress_status;
    }

    public function setLearningProgressStatus(?int $learning_progress_status): void
    {
        $this->learning_progress_status = $learning_progress_status;
    }

    public function getCompetences(): array
    {
        return $this->competences;
    }

    public function setCompetences(array $competences): void
    {
        $this->competences = $competences;
    }

    public function getProgressMeters(): array
    {
        return $this->progress_meters;
    }

    public function setProgressMeters(array $progress_meters): void
    {
        $this->progress_meters = $progress_meters;
    }

    public function getSendSuccess(): array
    {
        return $this->send_success;
    }

    public function setSendSuccess(array $send_success): void
    {
        $this->send_success = $send_success;
    }

    public function getSendInfo(): array
    {
        return $this->send_info;
    }

    public function setSendInfo(array $send_info): void
    {
        $this->send_info = $send_info;
    }

    public function getSendQuestion(): array
    {
        return $this->send_question;
    }

    public function setSendQuestion(array $send_question): void
    {
        $this->send_question = $send_question;
    }

    public function getSendFailure(): array
    {
        return $this->send_failure;
    }

    public function setSendFailure(array $send_failure): void
    {
        $this->send_failure = $send_failure;
    }

    public function isCorrect(): bool
    {
        return $this->correct;
    }

    public function setCorrect(bool $correct): void
    {
        $this->correct = $correct;
    }
    
    public function addSendMessage(string $message, string $message_type = self::MESSAGE_TYPE_INFO): void
    {
        switch ($message_type) {
            case self::MESSAGE_TYPE_SUCCESS:
                $this->addSendSuccess($message);
                break;

            case self::MESSAGE_TYPE_QUESTION:
                $this->addSendQuestion($message);
                break;

            case self::MESSAGE_TYPE_FAILURE:
                $this->addSendFailure($message);
                break;

            case self::MESSAGE_TYPE_INFO:
            default:
                $this->addSendInfo($message);
                break;
        }
    }
    
    public function addSendSuccess(string $send_success): void
    {
        $this->send_success[] = $send_success;
    }
    
    public function addSendQuestion(string $send_question): void
    {
        $this->send_question[] = $send_question;
    }
    
    public function addSendFailure(string $send_failure): void
    {
        $this->send_failure[] = $send_failure;
    }

    public function addSendInfo(string $send_info): void
    {
        $this->send_info[] = $send_info;
    }

    public function sendMessages(): void
    {
        global $DIC;

        if (!empty($this->send_success)) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage("success", implode("<br><br>", $this->send_success), true);
        }

        if (!empty($this->send_info)) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage("info", implode("<br><br>", $this->send_info), true);
        }

        if (!empty($this->send_question)) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage("question", implode("<br><br>", $this->send_question), true);
        }

        if (!empty($this->send_failure)) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage("failure", implode("<br><br>", $this->send_failure), true);
        }
    }

    public function getProgressMetersHtml(): string
    {
        $progress_meter_html_list = [];
        if (count($this->progress_meters) > 0) {
            foreach ($this->progress_meters as $progress_meter) {
                if($progress_meter->getMaxReachableScore() > 0) {
                    $progress_meter_html_list[] = $this->getProgressMeterHtml($progress_meter);
                }

            }
        }

        return implode('', $progress_meter_html_list);
    }


    private function getProgressMeterHtml(DHBWProgressMeter $progress_meter): string
    {
        global $DIC;

        //$progress_meter_factory = new ProgressMeterFactory();
        $progress_meter_factory = $DIC->ui()->factory()->chart()->progressMeter();
        switch ($progress_meter->getProgressmeterType()) {
            case DHBWProgressMeter::PROGRESS_METER_TYPE_MINI:

                $ui_element = $progress_meter_factory->mini(
                    $progress_meter->getMaxReachableScore(),
                    (int) $progress_meter->getPrimaryReachedScore(),
                    $progress_meter->getRequiredScore()
                );
                break;
            default:
                $ui_element = $progress_meter_factory->standard(
                    $progress_meter->getMaxReachableScore(),
                    (int) $progress_meter->getPrimaryReachedScore(),
                    $progress_meter->getRequiredScore(),
                    (int) $progress_meter->getSecondaryReachedScore()
                );
                $ui_element->withMainText($progress_meter->getPrimaryReachedScoreLabel());
                $ui_element->withRequiredText($progress_meter->getRequiredScoreLabel());
                break;
        }

        $progress_meter_id = md5($progress_meter->getTitle());
        $progress_meter_html = "<style>
        #" . $progress_meter_id . " {
            max-width: " . $progress_meter->getMaxWidthInPixel() . "px;
             margin-bottom: 20px;
        }
        </style>";
        $progress_meter_html .= '<div class="il_Block" datatable="0">';
        $progress_meter_html .= '<div class="ilBlockHeader ui-sortable-handle" style="cursor: move;">';
        $progress_meter_html .= '<div><h3 class="ilBlockHeader ui-sortable-handle" style="cursor: move;">' . $progress_meter->getTitle() . '</h3></div>';
        $progress_meter_html .= '</div>';

        $progress_meter_html .= '<div class="ilBlockRow1">';
        $progress_meter_html .= '<div id="' . $progress_meter_id . '">';
        $progress_meter_html .= $DIC->ui()->renderer()->render($ui_element);
        $progress_meter_html .= '</div>';
        $progress_meter_html .= '</div>';
        $progress_meter_html .= '</div>';

        return $progress_meter_html;
    }


    /**
     * @return string
     */
    public function renderProgressBar() : string
    {
        if ($this->progress_display === false) {
            return "";
        }

        $progress_bar = ilProgressBar::getInstance();

        $progress_bar->setCurrent($this->progress * 100);

        switch ($this->progress_type) {
            case ilProgressBar::TYPE_SUCCESS:
                $progress_bar->setType(ilProgressBar::TYPE_SUCCESS);
                break;

            case ilProgressBar::TYPE_WARNING:
                $progress_bar->setType(ilProgressBar::TYPE_WARNING);
                break;

            case ilProgressBar::TYPE_DANGER:
                $progress_bar->setType(ilProgressBar::TYPE_DANGER);
                break;

            case ilProgressBar::TYPE_INFO:
            default:
                $progress_bar->setType(ilProgressBar::TYPE_INFO);
                break;
        }

        return $progress_bar->render();
    }

    public function getFeedbackType(): string
    {
        if ($this->correct) {
            return self::MESSAGE_TYPE_SUCCESS;
        }
        else {
            return self::MESSAGE_TYPE_FAILURE;
        }
    }

    public function getFeedback(): string
    {
        global $ilDB;
        $question_data = self::getQuestionByRecomander($this->recomander_id);
        $question_id = $question_data['question_id'];
        $question_type = $question_data['question_type_fi'];

        if (is_numeric($this->answer_response)) {
            $sql = "SELECT feedback FROM qpl_fb_specific WHERE question_fi = $question_id AND answer = $this->answer_response";
            $set = $ilDB->query($sql);

            $row = $ilDB->fetchAssoc($set);
            $feedback = $row["feedback"];
            if (!empty($feedback)) {
                return $feedback;
            }

            $cdb = (int)$this->correct;
            $sql = "SELECT feedback FROM qpl_fb_generic WHERE question_fi = $question_id AND correctness = $cdb";
            $set = $ilDB->query($sql);

            $row = $ilDB->fetchAssoc($set);
            $feedback = $row["feedback"];
            if (!empty($feedback)) {
                return $feedback;
            }
        }

        if (!empty($this->feedback) and !is_numeric($this->feedback)) {
            return $this->feedback;
        } elseif ($this->correct) {
            return "<strong>Ihre Antwort ist korrekt!</strong>";
        } else {
            return $this->getCorrectAnswer((int) $question_id, (int) $question_type);
        }
    }

    private function getCorrectAnswer(int $question, int $question_type): string
    {
        global $ilDB;

        $correct_answer = "";

        if ($question_type == 1) {
            $sql = "select answertext from qpl_a_sc where question_fi = $question and points > 0";
            $set = $ilDB->query($sql);
            $correct_answer = $ilDB->fetchAssoc($set)["answertext"];
        } elseif ($question_type == 2) {
            $sql = "select answertext from qpl_a_mc where question_fi = $question and points > 0";
            $set = $ilDB->query($sql);
            $answers = array_column($ilDB->fetchAll($set),"answertext");
            $correct_answer = "<ul> <li>" . implode("</li><li>", $answers) . "</li> </ul>";
        } elseif ($question_type == 3) {
            $sql = "select answertext from qpl_a_cloze where question_fi = $question order by gap_id";
            $set = $ilDB->query($sql);
            $answers = array_column($ilDB->fetchAll($set), "answertext");
            $correct_answer = "<ul> <li>" . implode("</li><li>", $answers) . "</li> </ul>";
        }

        return "<strong><em>Ihre Antwort ist nicht korrekt.<br>Lösung:</em></strong><br>$correct_answer";
    }

    public static function getQuestionByRecomander(string $recomander_id): array
    {
        global $ilDB;
        $sql = "SELECT * FROM qpl_questions
inner join qpl_qst_type on qpl_qst_type.question_type_id = qpl_questions.question_type_fi where qpl_questions.description LIKE " . $ilDB->quote("%[[" . $recomander_id . "]]", 'text') . "order by qpl_questions.question_id desc limit 1";

        $set = $ilDB->query($sql);

        $row = $ilDB->fetchAssoc($set);

        $row['recomander_id'] = $recomander_id;
        $row['skills'] = array();

        $question_id = $row["question_id"];
        $sql = "SELECT skill_tref_fi FROM qpl_qst_skl_assigns where question_fi = $question_id";
        $set = $ilDB->query($sql);
        while ($sk = $ilDB->fetchAssoc($set)) {
            array_push($row['skills'], $sk['skill_tref_fi']);
        }

        return $row;
    }
}