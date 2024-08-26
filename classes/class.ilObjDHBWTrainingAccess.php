<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use objects\Training;
use platform\DHBWTrainingException;

/**
 * Class ilObjDHBWTrainingAccess
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilObjDHBWTrainingAccess extends ilObjectPluginAccess
{
    public static function hasWriteAccess($ref_id = null, $user_id = null): bool
    {
        return self::hasAccess('write', $ref_id, $user_id);
    }

    protected static function hasAccess(string $permission, $ref_id = null, $user_id = null): bool
    {
        global $DIC;
        $ref_id = (int)$ref_id ?: (int)$_GET['ref_id'];
        $user_id = $user_id ?: $DIC->user()->getId();

        return $DIC->access()->checkAccessOfUser($user_id, $permission, '', $ref_id);
    }

    /**
     * Check if the object is offline
     *
     * @param int $a_obj_id
     * @return bool
     * @throws DHBWTrainingException
     */
    public static function _isOffline($a_obj_id): bool
    {
        $training = new Training((int) $a_obj_id);
        return !$training->isOnline();
    }
}