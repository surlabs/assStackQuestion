<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use classes\core\external\cas\stack_cas_configuration;
use classes\platform\StackConfig;

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
class StackHealthcheckIlias
{

    /**
     * Performs a healthcheck on the maxima settings and platform requirements
     * @return array
     */
    public static function doHealthcheck(): array
    {
        $healthcheck_data = [];

        //Check if the platform config is loaded
        $healthcheck_data['stack_config'] = self::isStackConfigLoaded();

        //Check if the mbstring extension is loaded
        $healthcheck_data['mbstring'] = self::isMbstringLoaded();

        //Checks the current supported maxima libraries
        list($maxima_libraries_info, $message, $live_testcases) = stack_cas_configuration::validate_maximalibraries();
        $healthcheck_data['maxima_libraries'] = self::validateMaximaLibraries($maxima_libraries_info, $message, $live_testcases);

        return $healthcheck_data;
    }

    /**
     * Checks if the platform config is loaded
     * @return array
     */
    private static function isStackConfigLoaded(): array
    {
        $platform_data = StackConfig::getAll();
        if (!empty ($platform_data))
            $data = [
                'type' => 'success',
                'data' => $platform_data,
                'message' => 'Platform data retrieved successfully.'
            ];
        else {
            $data = [
                'type' => 'error',
                'data' => null,
                'message' => 'Platform data could not be retrieved.'
            ];
        }
        return $data;
    }

    /**
     * Checks if the mbstring extension is loaded
     * @return array
     */
    private static function isMbstringLoaded(): array
    {
        if (!extension_loaded('mbstring')) {
            $data = [
                'type' => 'error',
                'data' => null,
                'message' => 'STACK requires the PHP mbstring extension to be used. STACK questions might not work properly until this is installed.',
            ];
        } else {
            $data = [
                'type' => 'success',
                'data' => null,
                'message' => 'The PHP mbstring extension is installed.',
            ];
        }
        return $data;
    }

    /**
     * Checks the current supported maxima libraries
     * @param array $maxima_libraries_info
     * @param string $message
     * @param array $live_testcases
     * @return array
     */
    private static function validateMaximaLibraries(array $maxima_libraries_info, string $message, array $live_testcases): array
    {
        if (!empty($maxima_libraries_info)) {
            $data = [
                'type' => 'success',
                'data' => $maxima_libraries_info,
                'message' => $message,
                'live_testcases' => $live_testcases
            ];
        } else {
            $data = [
                'type' => 'error',
                'data' => null,
                'message' => $message,
                'live_testcases' => $live_testcases
            ];
        }
        return $data;
    }

}