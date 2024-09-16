<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

/**
 * Class DHBWPageObject
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWPageObject extends ilPageObject
{

    public function getParentType(): string
    {
        return ilDhbwTrainingPlugin::PLUGIN_ID;
    }
}