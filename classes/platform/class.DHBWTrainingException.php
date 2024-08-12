<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace platform;

use Exception;

/**
 * Class DHBWTrainingException
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWTrainingException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}