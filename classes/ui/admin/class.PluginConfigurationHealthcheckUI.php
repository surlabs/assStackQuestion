<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Section;
use classes\platform\StackException;

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
    private static $renderer;

    /**
     * Shows the healthcheck
     */
    public static function show(array $data, ilPlugin $plugin_object): array
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$control = $DIC->ctrl();
        self::$renderer = $DIC->ui()->renderer();

        //try {

            //control parameters
            self::$control->setParameterByClass(
                'ilassStackQuestionConfigGUI',
                'healthcheck',
                'run'
            );

            $serverAddress = $data["maxima_pool_url"];
            $healthcheck = new stack_cas_healthcheck($data);
            $data = $healthcheck->get_test_results();

            $sections = [];

            //add mbstring info
            $mbstring_loaded = self::isMbstringLoaded($plugin_object);
            $sections["mbstring"] = self::$factory->messageBox()->{$mbstring_loaded["type"]}($mbstring_loaded["message"]);

            $sections["server-info"] = self::$factory->messageBox()->info(
                $plugin_object->txt("srv_address") . ":<br \>"
                . $serverAddress);


            foreach ($data as $value) {

                $form_fields = [];

                if (isset($value['details'])) {
                    $form_fields["details"] = self::$factory->legacy($value["details"]);

                    $sections[$value["tag"]] = self::$factory->panel()->standard(
                        $plugin_object->txt("ui_admin_configuration_defaults_section_title_healthcheck_" . $value["tag"]),
                        self::$factory->legacy(
                            self::$renderer->render($form_fields)
                        )
                    );
                }
            }

/*
        } catch (Exception $e) {
            $sections = ['error' => self::$factory->messageBox()->failure($e->getMessage())];
        }*/

        return $sections;
    }


    /**
     * Checks if the mbstring extension is loaded
     * @return array
     */
    private static function isMbstringLoaded(ilPlugin $plugin_object): array
    {
        if (!extension_loaded('mbstring')) {
            $data = [
                'type' => 'failure',
                'data' => null,
                'message' => $plugin_object->txt('ui_admin_configuration_quality_healthcheck_mbstring_false'),
            ];
        } else {
            $data = [
                'type' => 'success',
                'data' => null,
                'message' => $plugin_object->txt('ui_admin_configuration_quality_healthcheck_mbstring_true'),
            ];
        }
        return $data;
    }
}