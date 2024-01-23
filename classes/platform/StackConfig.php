<?php
declare(strict_types=1);

namespace classes\platform;


/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 * This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 * originally created by Chris Sangwin.
 *
 * The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "STACK Question" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/STACK
 *
 * If you need support, please contact the maintainer of this software at:
 * stack@surlabs.es
 *
 *********************************************************************/

class StackConfig {
    private static array $config = [];
    private static array $categories = [];
    private static array $updated = [];

    /**
     * Load the platform configuration
     * @return void
     */
    public static function load() :void {
        $config = StackDatabase::select('xqcas_configuration');

        foreach ($config as $row) {
            if(isset($row['value']) && $row['value'] !== ''){
                $json_decoded = json_decode($row['value'], true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $row['value'] = $json_decoded;
                }
            }

            if (json_last_error() === JSON_ERROR_NONE) {
                $row['value'] = $json_decoded;
            }

            self::$config[$row['parameter_name']] = $row['value'];

            if (!isset(self::$categories[$row['group_name']])) {
                self::$categories[$row['group_name']] = array();
            }

            self::$categories[$row['parameter_name']] = $row['group_name'];
        }
    }

    /**
     * Set the platform configuration value for a given key to a given value
     * @param string $key
     * @param mixed $value
     * @param string|null $category
     * @return void
     */
    public static function set(string $key, $value, ?string $category = null): void {
        if (isset(self::$config[$key])) {
            if (is_bool($value)) {
                $value = (int) $value;
            }

            if (self::$config[$key] !== $value) {
                self::$config[$key] = $value;
                self::$updated[$key] = true;

                if (isset($category)) {
                    self::$categories[$key] = $category;
                }
            }
        }
    }

    /**
     * Gets the platform configuration value for a given key
     * @param string $key
     * @return mixed
     * @throws StackException
     */
    public static function get(string $key) {
        return self::$config[$key] ?? self::getFromDB($key);
    }

    /**
     * Gets the platform configuration value for a given key from the database
     * @param string $key
     * @return mixed
     */
    public static function getFromDB(string $key) {
        $config = StackDatabase::select('xqcas_configuration', array(
            'parameter_name' => $key
        ));

        if (count($config) > 0) {
            $json_decoded = json_decode($config[0]['value'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $config[0]['value'] = $json_decoded;
            }

            self::$config[$key] = $config[0]['value'];

            if (!isset(self::$categories[$config[0]['group_name']])) {
                self::$categories[$config[0]['group_name']] = array();
            }

            self::$categories[$key] = $config[0]['group_name'];

            return $config[0]['value'];
        } else {
            return null;
        }
    }

    /**
     * Gets all the platform configuration values
     * @param string|null $category
     * @return array
     */
    public static function getAll(?string $category = null) :array {
        if (isset($category)) {
            $result = array();

            foreach (self::$categories as $key => $value) {
                if ($value === $category) {
                    $result[$key] = self::$config[$key];
                }
            }

            return $result;
        } else {
            return self::$config;
        }
    }

    /**
     * Save the platform configuration if the parameter is updated
     * @return bool|string
     */
    public static function save() {
        foreach (self::$updated as $key => $exist) {
            if ($exist) {
                if (isset(self::$config[$key])) {
                    $data = array();

                    if (is_array(self::$config[$key])) {
                        $data['value'] = json_encode(self::$config[$key]);
                    } else {
                        $data['value'] = self::$config[$key];
                    }

                    $data['group_name'] = self::$categories[$key];

                    try {
                        StackDatabase::update('xqcas_configuration', $data, array(
                            'parameter_name' => $key
                        ));

                        self::$updated[$key] = false;
                    } catch (StackException $e) {
                        return $e->getMessage();
                    }
                }
            }
        }

        // In case there is nothing to update, return true to avoid error messages
        return true;
    }

    /**
     * Clear the platform Maxima cache
     * @return bool
     */
    public static function clearCache(): bool
    {
        global $DIC;
        $db = $DIC->database();
        $query = "TRUNCATE table xqcas_cas_cache";
        $db->manipulate($query);

        //TODO 31702
        /*Additional this two pathes should be flushed too:
        ./data/<client_id>/xqcas/stack/plots
        ./data/<client_id>/xqcas/stack/tmp*/

        return TRUE;
    }
}