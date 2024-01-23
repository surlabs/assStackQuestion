<?php
declare(strict_types=1);

use classes\platform\StackException;
use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Section;

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
class PluginConfigurationMainUI
{

    private static Factory $factory;
    private static ilCtrl $control;

    /**
     * Shows the plugin configuration overview sections
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
                'configure',
                'saveMain'
            );

            //get sections
            $content = [
                'connection' => self::getMaximaConnectionSection($data, $plugin_object),
                'display' => self::getDisplayOptionsSection($data, $plugin_object)
            ];

        } catch (Exception $e) {
            $content = [self::$factory->messageBox()->failure($e->getMessage())];
        }

        return $content;
    }

    /**
     * Gets the Maxima connection section
     * @throws StackException
     */
    private static function getMaximaConnectionSection(array $data, ilPlugin $plugin_object): Section
    {

        if (isset($data['platform_type']) && $data['platform_type'] == 'linux') {
            $maxima_connection_value = $data['platform_type'];
        } elseif (isset($data['platform_type']) && $data['platform_type'] == 'server') {
            $maxima_connection_value = $data['platform_type'];
        } else {
            throw new StackException("Error: Maxima connection value not valid: " . $data['platform_type']);
        }

        $maxima_connection_options = self::$factory->input()->field()->radio(
            "",
            ""
        )
            ->withOption('linux',
                $plugin_object->txt("ui_admin_configuration_maxima_connection_unix_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_maxima_connection_unix_description"))
            ->withOption('server',
                $plugin_object->txt("ui_admin_configuration_defaults_maxima_connection_server_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_maxima_connection_server_description")
            )
            ->withValue($maxima_connection_value);

        return self::$factory->input()->field()->section(
            [
                'platform_type' => $maxima_connection_options
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

        //Validation mode
        if (isset($data['instant_validation']) && $data['instant_validation'] === 0) {
            $validation_value = '0';
        } elseif (isset($data['instant_validation']) && $data['instant_validation'] === 1) {
            $validation_value = '1';
        } else {
            throw new StackException("Error: instant_validation value not found");
        }

        $validation_options = self::$factory->input()->field()->radio(
            $plugin_object->txt("ui_admin_configuration_defaults_validation_title"),
            ""
        )
            ->withOption('0',
                $plugin_object->txt("ui_admin_configuration_defaults_user_validation_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_user_validation_description"))
            ->withOption('1',
                $plugin_object->txt("ui_admin_configuration_defaults_instant_validation_title"),
                $plugin_object->txt("ui_admin_configuration_defaults_instant_validation_description")
            )
            ->withValue($validation_value);

        //Allow JSXGraph
        if (isset($data['allow_jsx_graph']) && $data['allow_jsx_graph'] === 1) {
            $allow_jsxgraph_value = "1";
        } elseif (isset($data['allow_jsx_graph']) && ($data['allow_jsx_graph'] === 0)) {
            $allow_jsxgraph_value = "0";
        } else {
            //TODO throw new StackException("Error: allow_jsx_graph value not found");
            //set by default to 1
            $allow_jsxgraph_value = "1";
        }

        $allow_jsxgraph_options = self::$factory->input()->field()->radio(
            $plugin_object->txt("ui_admin_configuration_allow_jsxgraph_title"),
            ""
        )
            ->withOption('0',
                $plugin_object->txt("ui_admin_configuration_dont_allow_jsxgraph_title"),
                $plugin_object->txt("ui_admin_configuration_dont_allow_jsxgraph_description"))
            ->withOption('1',
                $plugin_object->txt("ui_admin_configuration_do_allow_jsxgraph_title"),
                $plugin_object->txt("ui_admin_configuration_do_allow_jsxgraph_description")
            )
            ->withValue($allow_jsxgraph_value);

        return self::$factory->input()->field()->section(
            [
                'instant_validation' => $validation_options,
                'allow_jsx_graph' => $allow_jsxgraph_options
            ],
            $plugin_object->txt("ui_admin_configuration_defaults_display_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_display_description")
        );


    }

}