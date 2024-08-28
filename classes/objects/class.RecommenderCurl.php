<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace objects;

use DateTime;
use Exception;
use ilSession;
use platform\DHBWTrainingConfig;
use platform\DHBWTrainingDatabase;
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

    public function __construct(Training $training, RecommenderResponse $response)
    {
        $this->training = $training;
        $this->response = $response;
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

    private function doRequest(string $string, array $headers, array $data)
    {
        // TODO: Implement doRequest() method.
    }
}