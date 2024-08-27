<?php /** @noinspection PhpDeprecationInspection */

/**
 * Class LearningProgressStatusRepresentation
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

namespace legacy;

use ilDHBWTrainingPlugin;
use ilLPStatus;
use ilUtil;

class LearningProgressStatusRepresentation
{

    // User has called the training object.
    const PROGR_STATUS_NOT_ATTEMPTED_NUM = 1;
    // User has called a question from the training object.
    const PROGR_STATUS_IN_PROGRESS_NUM = 2;
    // User has achieved the target level
    const PROGR_STATUS_COMPLETED_NUM = 3;
    protected static array $ARR_PROGR_STATUS
        = array(
            self::PROGR_STATUS_NOT_ATTEMPTED_NUM => 'status_not_attempted'
        ,
            self::PROGR_STATUS_IN_PROGRESS_NUM   => 'status_in_progress'
        ,
            self::PROGR_STATUS_COMPLETED_NUM     => 'status_completed'
        );
    protected static array $dropdown_cache;


    //for the filter in the participant table gui the status should begin at 1. Otherwise if not attempted is 0 the list view shows only the entries with this status even though no filter is selected.
    public static function mappProgrStatusToLPStatus($status_num)
    {
        if ($status_num == 1) {
            return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
        if ($status_num == 2) {
            return ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
        }
        if ($status_num == 3) {
            return ilLPStatus::LP_STATUS_COMPLETED_NUM;
        }

        return '';
    }


    /**
     * Get a user readable representation of a status.
     */
    public static function statusToRepr($status)
    {

        $pl = ilDHBWTrainingPlugin::getInstance();

        if ($status == ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM) {
            return $pl->txt("status_not_attempted");
        }
        if ($status == ilLPStatus::LP_STATUS_IN_PROGRESS_NUM) {
            return $pl->txt("status_in_progress");
        }
        if ($status == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            return $pl->txt("status_completed");
        }

        return '';
    }


    static public function getStatusImage($status): string
    {

        switch ($status) {
            case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                return ilUtil::getImagePath('scorm/not_attempted.svg');
            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                return ilUtil::getImagePath('scorm/incomplete.svg');
            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return ilUtil::getImagePath('scorm/complete.svg');
        }

        return '';
    }


    public static function getDropdownDataLocalized($pl_obj): array
    {
        $data = self::getDropdownData();
        foreach ($data as $key => $entry) {
            if ($entry != '') {
                $data[$key] = $pl_obj->txt($entry);
            }
        }

        return $data;
    }


    public static function getDropdownData(): array
    {
        if (!isset(self::$dropdown_cache) || self::$dropdown_cache == null) {
            self::$dropdown_cache = self::$ARR_PROGR_STATUS;
        }

        return self::$dropdown_cache;
    }
}