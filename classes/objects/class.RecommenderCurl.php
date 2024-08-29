<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace objects;

use Exception;
use ilCurlConnection;
use ilCurlConnectionException;
use ilDHBWTrainingPlugin;
use ilSession;
use platform\DHBWTrainingConfig;
use platform\DHBWTrainingException;

/**
 * Class RecommenderCurl
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class RecommenderCurl
{
    const KEY_RESPONSE_TIME_START = "xdht_response_time_start";
    const KEY_RESPONSE_PROGRESS_METER = "xdht_response_progress_meter";
    const KEY_RESPONSE_PROGRESS_BAR = "xdht_response_progress_bar";

    private Training $training;
    private RecommenderResponse $response;
    private ilDHBWTrainingPlugin $plugin;

    public function __construct(Training $training, RecommenderResponse $response)
    {
        $this->training = $training;
        $this->response = $response;
        $this->plugin = ilDHBWTrainingPlugin::getInstance();
    }

    public function getTraining(): Training
    {
        return $this->training;
    }

    public function setTraining(Training $training): void
    {
        $this->training = $training;
    }

    public function getResponse(): RecommenderResponse
    {
        return $this->response;
    }

    public function setResponse(RecommenderResponse $response): void
    {
        $this->response = $response;
    }

    /**
     * @throws DHBWTrainingException
     */
    public function start(): void
    {
        global $DIC;

        ilSession::clear(self::KEY_RESPONSE_TIME_START);

        $headers = [
            "Accept: application/json",
            "Content-Type: application/json"
        ];
        $data = [
            "secret"               => $this->training->getSecret(),
            "installation_key"     => $this->training->getInstallationKey(),
            "user_id"              => $this->getAnonymizedUserHash(),
            "lang_key"             => $DIC->user()->getLanguage(),
            "training_obj_id"      => $this->training->getId(),
            "question_pool_obj_id" => $this->training->getQuestionPoolId()
        ];

        $this->doRequest("/v1/start", $headers, $data);
    }

    /**
     * @throws DHBWTrainingException
     */
    protected function getAnonymizedUserHash() : string
    {
        global $ilUser;
        $alg = 'sha256'; // put new desired hashing algo here
        if (!in_array($alg, hash_algos())) {
            $alg = 'md5'; // Fallback to md5 if $alg not included in php
        }

        return hash($alg,DHBWTrainingConfig::get("salt") . $ilUser->getId());
    }

    private function doRequest(string $rest_url, array $headers, array $post_data = [])
    {
        $curlConnection = null;

        try {
            $curlConnection = $this->initCurlConnection($rest_url, $headers);

            $response_time_start = intval(ilSession::get(self::KEY_RESPONSE_TIME_START));
            if (!empty($response_time_start)) {
                $post_data["response_time"] = (time() - $response_time_start);
            }

            if ($this->training->isLog()) {
                $this->response->addSendInfo('<pre>post_data:' . json_encode($post_data, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT) . ' </pre>');
            }

            $curlConnection->setOpt(CURLOPT_POST, true);
            $curlConnection->setOpt(CURLOPT_POSTFIELDS, json_encode($post_data, JSON_FORCE_OBJECT));

            $raw_response = $curlConnection->exec();


            if (empty($raw_response)) {
                $this->response->addSendFailure($this->plugin->txt("error_recommender_system_not_reached"));

                return;
            }

            $result = json_decode($raw_response, true);
            
            if (empty($result) || !is_array($result)) {
                if ($this->training->isLog()) {
                    $this->response->addSendInfo('<pre>raw_response:' . $raw_response . ' </pre>');
                }

                $this->response->addSendFailure($this->plugin->txt("error_recommender_system_not_reached"));

                return;
            }

            if ($this->training->isLog()) {
                if ($this->training->getRecommenderSystemServer() == 2) {
                    if (!empty($result['debug_server'])) {
                        $this->response->addSendInfo('<pre>' . $this->plugin->txt("recommender_system_server_built_in_debug") . ':' . json_encode($result['debug_server'], JSON_PRETTY_PRINT) . '</pre>');
                    }
                    unset($result['debug_server']);
                }

                $this->response->addSendInfo('<pre>response:' . json_encode($result, JSON_PRETTY_PRINT) . ' </pre>');
            }

            if (!empty($result['status'])) {
                $this->response->setStatus(strval($result['status']));
            } else {
                $this->response->addSendFailure($this->plugin->txt("error_recommender_system_no_status"));

                return;
            }

            if (!empty($result['recomander_id'])) {
                $this->response->setRecomander(strval($result['recomander_id']));
            }

            if (!empty($result['response_type'])) {
                $this->response->setResponseType(intval($result['response_type']));
            }

            if (isset($result['answer_response'])) {
                $this->response->setAnswerResponse(strval($result['answer_response']));
            }

            if (!empty($result['answer_response_type'])) {
                $this->response->setAnswerResponseType(strval($result['answer_response_type']));
            }

            if (!empty($result['message'])) {
                $this->response->setMessage(strval($result['message']));
            }

            if (!empty($result['message_type'])) {
                $this->response->setMessageType(strval($result['message_type']));
            }

            if(!empty($result['progress_display'])) {
                $this->response->setProgressDisplay((bool)($result['progress_display']));
            }

            if (isset($result['progress']) && !empty($result['progress_type'])) {
                $this->response->setProgress(floatval($result['progress']));
                $this->response->setProgressType(strval($result['progress_type']));

                ilSession::set(self::KEY_RESPONSE_PROGRESS_BAR, serialize(['progress' => $result['progress'],'progress_type' => $result['progress_type']]));
            } else {
                if(strlen(ilSession::get(self::KEY_RESPONSE_PROGRESS_BAR)) > 0) {
                    $progress_bar = unserialize(ilSession::get(self::KEY_RESPONSE_PROGRESS_BAR));

                    $this->response->setProgress(floatval($progress_bar['progress']));
                    $this->response->setProgressType((string)floatval($progress_bar['progress_type']));
                }
            }


            if (isset($result['learning_progress_status'])) {
                $this->response->setLearningProgressStatus(intval($result['learning_progress_status']));
            }

            if (!empty($result['competences'])) {
                $this->response->setCompetences((array) $result['competences']);
            }

            if (!empty($result['progress_meters'])) {


                $progress_meter_list = [];
                foreach($result['progress_meters'] as $value){
                    $progress_meter_list[] = DHBWProgressMeter::newFromArray($value);
                }

                ilSession::set(self::KEY_RESPONSE_PROGRESS_METER, serialize($progress_meter_list));


                $this->response->setProgressmeters($progress_meter_list);
            } else {
                if(strlen(ilSession::get(self::KEY_RESPONSE_PROGRESS_METER)) > 0) {
                    $this->response->setProgressmeters((array) unserialize(ilSession::get(self::KEY_RESPONSE_PROGRESS_METER)));
                }
            }

            if (isset($result['correct'])) {
                $this->response->setCorrect($result['correct']);
            }
        } catch (Exception $ex) {
            if ($this->training->isLog()) {
                $this->response->addSendFailure($ex->getMessage());
            } else {
                $this->response->addSendFailure($this->plugin->txt("error_recommender_system_not_reached"));
            }
        } finally {
            // Close Curl connection
            if ($curlConnection !== null) {
                $curlConnection->close();
                $curlConnection = null;
            }
        }

        ilSession::set(self::KEY_RESPONSE_TIME_START, time());
    }

    /**
     * @throws ilCurlConnectionException
     */
    private function initCurlConnection(string $rest_url, array $headers) : ilCurlConnection
    {
        $curlConnection = new ilCurlConnection();

        $curlConnection->init();

        $curlConnection->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curlConnection->setOpt(CURLOPT_VERBOSE, true);
        $curlConnection->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curlConnection->setOpt(CURLOPT_SSL_VERIFYHOST, false);

        if ($this->training->getRecommenderSystemServer() == 1) {
            $url = rtrim($this->training->getUrl(), "/") . $rest_url;
        } else {
            $url = ILIAS_HTTP_PATH . "/" . $this->plugin->getDirectory() . "/classes/Recommender/debug/" . trim($rest_url, "/") . ".php?obj_id=" . $this->training->getId();
            $curlConnection->setOpt(CURLOPT_COOKIE, session_name() . '=' . session_id() . ";XDEBUG_SESSION=" . $_COOKIE["XDEBUG_SESSION"]);
        }

        $curlConnection->setOpt(CURLOPT_URL, $url);

        $curlConnection->setOpt(CURLOPT_HTTPHEADER, $headers);

        return $curlConnection;
    }

    /**
     * @throws DHBWTrainingException
     */
    public function answer(string $recomander_id, int $question_type, int $question_max_points, array $skill, $answer)/*:void*/
    {
        global $DIC;

        $headers = [
            "Accept: application/json",
            "Content-Type: application/json"
        ];

        $data = [
            "secret"               => $this->training->getSecret(),
            "installation_key"     => $this->training->getInstallationKey(),
            "user_id"              => $this->getAnonymizedUserHash(),
            "lang_key"             => $DIC->user()->getLanguage(),
            "training_obj_id"      => $this->training->getId(),
            "question_pool_obj_id" => $this->training->getQuestionPoolId(),
            "recomander_id"        => $recomander_id,
            "question_type"        => $question_type,
            "question_max_points"  => $question_max_points,
            "answer"               => $answer,
            "skills"               => $skill
        ];

        $this->doRequest("/v1/answer", $headers, $data);
    }

    /**
     * @throws DHBWTrainingException
     */
    public function sendRating(string $recomander_id, int $rating)/*:void*/
    {
        global $DIC;

        $headers = [
            "Accept: application/json",
            "Content-Type: application/json"
        ];

        $data = [
            "secret"               => $this->training->getSecret(),
            "installation_key"     => $this->training->getInstallationKey(),
            "user_id"              => $this->getAnonymizedUserHash(),
            "lang_key"             => $DIC->user()->getLanguage(),
            "training_obj_id"      => $this->training->getId(),
            "question_pool_obj_id" => $this->training->getQuestionPoolId(),
            "recomander_id"        => $recomander_id,
            "rating"               => $rating
        ];

        $this->doRequest("/v1/rating", $headers, $data);
    }
}