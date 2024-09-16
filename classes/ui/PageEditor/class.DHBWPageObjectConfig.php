<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

/**
 * Class DHBWPageObjectConfig
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWPageObjectConfig extends ilPageConfig
{
    public function init(): void
    {
        $this->setPreventHTMLUnmasking(true);
        $this->setEnableInternalLinks(false);
        $this->setEnableWikiLinks(false);
        $this->setEnableActivation(false);
    }
}