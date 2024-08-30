<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use objects\DHBWTraining;
use platform\DHBWTrainingException;

/**
 * Class ilObjDHBWTraining
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilObjDHBWTraining extends ilObjectPlugin
{
    private DHBWTraining $training;

    /**
     * Creates a new object
     * @param bool $clone_mode
     * @throws DHBWTrainingException
     */
    protected function doCreate(bool $clone_mode = false): void
    {
        $this->training = new DHBWTraining($this->getId());

        $this->training->save();
    }

    /**
     * Read the object
     * @throws DHBWTrainingException
     */
    protected function doRead(): void
    {
        $this->training = new DHBWTraining($this->getId());
    }

    /**
     * Deletes the object
     * @throws DHBWTrainingException
     */
    protected function doDelete(): void
    {
        $this->training->delete();
    }

    /**
     * Updates the object
     * @throws DHBWTrainingException
     */
    protected function doUpdate(): void
    {
        $this->training->save();
    }

    protected function initType(): void
    {
        $this->setType(ilDHBWTrainingPlugin::PLUGIN_ID);
    }

    public function getTraining(): DHBWTraining
    {
        return $this->training;
    }
}