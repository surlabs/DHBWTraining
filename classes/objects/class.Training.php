<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace objects;

use platform\DHBWTrainingDatabase;
use platform\DHBWTrainingException;

/**
 * Class Training
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class Training
{
    private int $id;
    private int $question_pool_id = 0;
    private bool $online = false;
    private string $installation_key = "";
    private string $secret = "";
    private string $url = "";
    private bool $log = false;
    private int $recommender_system_server = 1;
    private array $rec_sys_ser_bui_in_deb_comp = [];
    private array $rec_sys_ser_bui_in_deb_progm = [];
    private bool $learning_progress = false;

    /**
     * @throws DHBWTrainingException
     */
    public function __construct(?int $id = null)
    {
        if ($id !== null && $id > 0) {
            $this->id = $id;

            $this->loadFromDB();
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getQuestionPoolId(): int
    {
        return $this->question_pool_id;
    }

    public function setQuestionPoolId(int $question_pool_id): void
    {
        $this->question_pool_id = $question_pool_id;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): void
    {
        $this->online = $online;
    }

    public function getInstallationKey(): string
    {
        return $this->installation_key;
    }

    public function setInstallationKey(string $installation_key): void
    {
        $this->installation_key = $installation_key;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function isLog(): bool
    {
        return $this->log;
    }

    public function setLog(bool $log): void
    {
        $this->log = $log;
    }

    public function getRecommenderSystemServer(): int
    {
        return $this->recommender_system_server;
    }

    public function setRecommenderSystemServer(int $recommender_system_server): void
    {
        $this->recommender_system_server = $recommender_system_server;
    }

    public function getRecSysSerBuiInDebComp(): array
    {
        return $this->rec_sys_ser_bui_in_deb_comp;
    }

    public function setRecSysSerBuiInDebComp(?array $rec_sys_ser_bui_in_deb_comp = []): void
    {
        if ($rec_sys_ser_bui_in_deb_comp === null) {
            $rec_sys_ser_bui_in_deb_comp = [];
        }

        $this->rec_sys_ser_bui_in_deb_comp = $rec_sys_ser_bui_in_deb_comp;
    }

    public function getRecSysSerBuiInDebProgm(): array
    {
        return $this->rec_sys_ser_bui_in_deb_progm;
    }

    public function setRecSysSerBuiInDebProgm(?array $rec_sys_ser_bui_in_deb_progm = []): void
    {
        if ($rec_sys_ser_bui_in_deb_progm === null) {
            $rec_sys_ser_bui_in_deb_progm = [];
        }

        $this->rec_sys_ser_bui_in_deb_progm = $rec_sys_ser_bui_in_deb_progm;
    }

    public function isLearningProgress(): bool
    {
        return $this->learning_progress;
    }

    public function setLearningProgress(bool $learning_progress): void
    {
        $this->learning_progress = $learning_progress;
    }

    /**
     * @throws DHBWTrainingException
     */
    public function loadFromDB(): void
    {
        $database = new DHBWTrainingDatabase();

        $result = $database->select("rep_robj_xdht_settings", ["dhbw_training_object_id" => $this->getId()]);

        if (isset($result[0])) {
            $this->setQuestionPoolId((int) $result[0]["question_pool_id"]);
            $this->setOnline((bool) $result[0]["is_online"]);
            $this->setInstallationKey($result[0]["installation_key"]);
            $this->setSecret($result[0]["secret"]);
            $this->setUrl($result[0]["url"]);
            $this->setLog((bool) $result[0]["log"]);
            $this->setRecommenderSystemServer((int) $result[0]["recommender_system_server"]);
            $this->setRecSysSerBuiInDebComp(json_decode($result[0]["rec_sys_ser_bui_in_deb_comp"], true));
            $this->setRecSysSerBuiInDebProgm(json_decode($result[0]["rec_sys_ser_bui_in_deb_progm"], true));
            $this->setLearningProgress((bool) $result[0]["learning_progress"]);
        }
    }

    /**
     * @throws DHBWTrainingException
     */
    public function save(): void
    {
        if (!isset($this->id) || $this->id == 0) {
            throw new DHBWTrainingException("Training::save() - Training ID is 0");
        }

        $database = new DHBWTrainingDatabase();

        $database->insertOnDuplicatedKey("rep_robj_xdht_settings", array(
            "dhbw_training_object_id" => $this->id,
            "question_pool_id" => $this->question_pool_id,
            "is_online" => (int) $this->online,
            "installation_key" => $this->installation_key,
            "secret" => $this->secret,
            "url" => $this->url,
            "log" => (int) $this->log,
            "recommender_system_server" => $this->recommender_system_server,
            "rec_sys_ser_bui_in_deb_comp" => json_encode($this->rec_sys_ser_bui_in_deb_comp),
            "rec_sys_ser_bui_in_deb_progm" => json_encode($this->rec_sys_ser_bui_in_deb_progm),
            "learning_progress" => (int) $this->learning_progress
        ));
    }

    /**
     * @throws DHBWTrainingException
     */
    public function delete(): void
    {
        $database = new DHBWTrainingDatabase();

        $database->delete("rep_robj_xdht_settings", ["dhbw_training_object_id" => $this->getId()]);
    }
}