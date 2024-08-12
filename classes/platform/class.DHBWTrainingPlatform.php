<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace platform;

/**
 * Class DHBWTrainingPlatform
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWTrainingPlatform
{
    /**
     * Gets the platform translation of a string
     * @param string $str
     * @param array|null $params
     * @return string|null
     */
    public static function getTranslation(string $str, ?array $params = null): ?string
    {
        global $DIC;

        $txt = $DIC->language()->txt($str);

        if (isset($params)) {
            $txt = vsprintf($txt, $params);
        }

        return $txt;
    }
}