<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Section;
use classes\core\security\StackException;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *  This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 *  originally created by Chris Sangwin.
 *
 *  The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "STACK Question" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/STACK
 *
 *  If you need support, please contact the maintainer of this software at:
 *  stack@surlabs.es
 *
 */
class PluginConfigurationHealthcheckUI
{

    private static Factory $factory;
    private static ilCtrl $control;

    /**
     * Shows the healthcheck
     */
    public static function show(array $data, ilPlugin $plugin_object): array
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$control = $DIC->ctrl();

        try {

            //control parameters
            self::$control->setParameterByClass(
                'ilassStackQuestionConfigGUI',
                'healthcheck',
                'run'
            );

            //get sections
            $content = [
                'connection' => self::getMaximaConnectionSection($data, $plugin_object),
                'display' => self::getDisplayOptionsSection($data, $plugin_object)
            ];

        } catch (Exception $e) {
            $content = ['error' => self::$factory->messageBox()->failure($e->getMessage())];
        }

        return $content;
    }

    /**
     * Gets the Maxima connection section
     * @throws StackException
     */
    private static function getMaximaConnectionSection(array $data, ilPlugin $plugin_object): Section
    {


        return self::$factory->input()->field()->section(
            [
            ],
            $plugin_object->txt("ui_admin_configuration_maxima_connection_title"),
            $plugin_object->txt("ui_admin_configuration_maxima_connection_description")
        );


    }

    /**
     * Gets the defaults validation section
     * @throws StackException
     */
    private static function getDisplayOptionsSection(array $data, ilPlugin $plugin_object): Section
    {

        return self::$factory->input()->field()->section(
            [
            ],
            $plugin_object->txt("ui_admin_configuration_defaults_display_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_display_description")
        );


    }
}