<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace platform;

/**
 * Class DHBWTrainingConfig
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWTrainingConfig
{
    private static array $config = [];
    private static array $updated = [];

    /**
     * Load the plugin configuration
     * @return void
     * @throws DHBWTrainingException
     */
    public static function load(): void
    {
        $config = (new DHBWTrainingDatabase)->select('xdht_config');

        foreach ($config as $row) {
            if (isset($row['value']) && $row['value'] !== '') {
                $json_decoded = json_decode($row['value'], true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $row['value'] = $json_decoded;
                }
            }

            self::$config[$row['name']] = $row['value'];
        }
    }

    /**
     * Set the plugin configuration value for a given key to a given value
     * @param string $key
     * @param $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        if (is_bool($value)) {
            $value = (int)$value;
        }

        if (!isset(self::$config[$key]) || self::$config[$key] !== $value) {
            self::$config[$key] = $value;
            self::$updated[$key] = true;
        }
    }

    /**
     * Gets the plugin configuration value for a given key
     * @param string $key
     * @return mixed
     * @throws DHBWTrainingException
     */
    public static function get(string $key)
    {
        return self::$config[$key] ?? self::getFromDB($key);
    }

    /**
     * Gets the plugin configuration value for a given key from the database
     * @param string $key
     * @return mixed
     * @throws DHBWTrainingException
     */
    public static function getFromDB(string $key)
    {
        $config = (new DHBWTrainingDatabase)->select('xdht_config', array(
            'name' => $key
        ));

        if (count($config) > 0) {
            $json_decoded = json_decode($config[0]['value'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $config[0]['value'] = $json_decoded;
            }

            self::$config[$key] = $config[0]['value'];

            return $config[0]['value'];
        } else {
            return "";
        }
    }

    /**
     * Gets all the plugin configuration values
     * @return array
     */
    public static function getAll(): array
    {
        return self::$config;
    }

    /**
     * Save the plugin configuration if the parameter is updated
     * @return bool|string
     */
    public static function save()
    {
        foreach (self::$updated as $key => $exist) {
            if ($exist) {
                if (isset(self::$config[$key])) {
                    $data = array(
                        'name' => $key
                    );

                    if (is_array(self::$config[$key])) {
                        $data['value'] = json_encode(self::$config[$key]);
                    } else {
                        $data['value'] = self::$config[$key];
                    }

                    try {
                        (new DHBWTrainingDatabase)->insertOnDuplicatedKey('xdht_config', $data);

                        self::$updated[$key] = false;
                    } catch (DHBWTrainingException $e) {
                        return $e->getMessage();
                    }
                }
            }
        }

        // In case there is nothing to update, return true to avoid error messages
        return true;
    }

    public static function generateSalt($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}