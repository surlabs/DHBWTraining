<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace objects;

use DateTime;
use Exception;
use ilLPStatus;
use platform\DHBWTrainingDatabase;
use platform\DHBWTrainingException;

/**
 * Class Participant
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class Participant
{
    private int $id;
    private int $training_obj_id;
    private int $usr_id;
    private int $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    private DateTime $created;
    private DateTime $updated_status;
    private DateTime $last_access;
    private int $created_usr_id = 0;
    private int $updated_usr_id = 0;
    private string $full_name;
    /**
     * @throws DHBWTrainingException
     */
    public function __construct(?int $id = null)
    {
        $this->created = new DateTime();
        $this->updated_status = new DateTime();
        $this->last_access = new DateTime();

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

    public function getTrainingObjId(): int
    {
        return $this->training_obj_id;
    }

    public function setTrainingObjId(int $training_obj_id): void
    {
        $this->training_obj_id = $training_obj_id;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function setUsrId(int $usr_id): void
    {
        $this->usr_id = $usr_id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getUpdatedStatus(): DateTime
    {
        return $this->updated_status;
    }

    public function setUpdatedStatus(DateTime $updated_status): void
    {
        $this->updated_status = $updated_status;
    }

    public function getLastAccess(): DateTime
    {
        return $this->last_access;
    }

    public function setLastAccess(DateTime $last_access): void
    {
        $this->last_access = $last_access;
    }

    public function getCreatedUsrId(): int
    {
        return $this->created_usr_id;
    }

    public function setCreatedUsrId(int $created_usr_id): void
    {
        $this->created_usr_id = $created_usr_id;
    }

    public function getUpdatedUsrId(): int
    {
        return $this->updated_usr_id;
    }

    public function setUpdatedUsrId(int $updated_usr_id): void
    {
        $this->updated_usr_id = $updated_usr_id;
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }

    /**
     * @throws DHBWTrainingException
     * @throws Exception
     */
    public function loadFromDB(): void
    {
        $database = new DHBWTrainingDatabase();

        $result = $database->select("rep_robj_xdht_partic", ["id" => $this->getId()]);

        if (isset($result[0])) {
            $this->setTrainingObjId((int) $result[0]["training_obj_id"]);
            $this->setUsrId((int) $result[0]["usr_id"]);
            $this->setStatus((int) $result[0]["status"]);
            $this->setCreated(new DateTime($result[0]["created"]));
            $this->setUpdatedStatus(new DateTime($result[0]["updated_status"]));
            $this->setLastAccess(new DateTime($result[0]["last_access"]));
            $this->setCreatedUsrId((int) $result[0]["created_usr_id"]);
            $this->setUpdatedUsrId((int) $result[0]["updated_usr_id"]);
            $this->setFullName($result[0]["full_name"]);
        }
    }

    /**
     * @throws DHBWTrainingException
     */
    public function save(): void
    {
        $database = new DHBWTrainingDatabase();


        if (!isset($this->id) || $this->id == 0) {
            $this->id = $database->nextId("rep_robj_xdht_partic");
        }

        $database->insertOnDuplicatedKey("rep_robj_xdht_partic", array(
            "id" => $this->getId(),
            "training_obj_id" => $this->getTrainingObjId(),
            "usr_id" => $this->getUsrId(),
            "status" => $this->getStatus(),
            "created" => $this->getCreated()->format("Y-m-d H:i:s"),
            "updated_status" => $this->getUpdatedStatus()->format("Y-m-d H:i:s"),
            "last_access" => $this->getLastAccess()->format("Y-m-d H:i:s"),
            "created_usr_id" => $this->getCreatedUsrId(),
            "updated_usr_id" => $this->getUpdatedUsrId(),
            "full_name" => $this->getFullName()
        ));
    }

    /**
     * @throws DHBWTrainingException
     */
    public function delete(): void
    {
        $database = new DHBWTrainingDatabase();

        $database->delete("rep_robj_xdht_partic", ["id" => $this->getId()]);
    }

    /**
     * @throws DHBWTrainingException
     */
    public static function findOrCreateParticipantByUsrAndTrainingObjectId(int $usr_id, int $training_id): Participant
    {
        $database = new DHBWTrainingDatabase();

        $result = $database->select("rep_robj_xdht_partic", ["usr_id" => $usr_id, "training_obj_id" => $training_id]);

        if (isset($result[0])) {
            return new Participant((int) $result[0]["id"]);
        }

        global $ilUser;

        $participant = new Participant();

        $participant->setUsrId($usr_id);
        $participant->setTrainingObjId($training_id);
        $participant->setCreatedUsrId( $ilUser->getId());
        $participant->setUpdatedUsrId( $ilUser->getId());
        $participant->setFullName($ilUser->getFirstname() . " " . $ilUser->getLastName());


        return $participant;
    }
}