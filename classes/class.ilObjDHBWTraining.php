<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

/**
 * Class ilObjDHBWTraining
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilObjDHBWTraining extends ilObjectPlugin
{

    /**
     * Creates a new object
     * @param bool $clone_mode
     */
    protected function doCreate(bool $clone_mode = false): void
    {
        //TODO: Implement doCreate() method.
    }

    /**
     * Read the object
     */
    protected function doRead(): void
    {
        //TODO: Implement doRead() method.
    }

    /**
     * Deletes the object
     */
    protected function doDelete(): void
    {
        //TODO: Implement doDelete() method.
    }

    /**
     * Updates the object
     */
    protected function doUpdate(): void
    {
        //TODO: Implement doUpdate() method.
    }

    protected function initType(): void
    {
        $this->setType(ilDHBWTrainingPlugin::PLUGIN_ID);
    }

}