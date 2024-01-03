<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Renderer;
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
class PluginConfigurationQualityUI
{

    private static Factory $factory;
    private static Renderer $renderer;
    private static ilCtrl $control;

    /**
     * Shows the plugin configuration Maxima settings form
     */
    public static function show(array $data, ilPlugin $plugin_object): string
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$renderer = $DIC->ui()->renderer();
        self::$control = $DIC->ctrl();

        try {

            $rendered_content =
                self::$renderer->render(self::getHealthcheckButton($plugin_object)) .
                self::$renderer->render(self::getBulktestingButton($plugin_object));

        } catch (Exception $e) {
            $rendered_content =
                self::$renderer->render(self::$factory->messageBox()->failure($e->getMessage()));
        }

        return $rendered_content;
    }

    /**
     * Gets the healthcheck button for the plugin configuration
     * @throws ilCtrlException
     */
    private static function getHealthcheckButton(ilPlugin $plugin_object): Bulky
    {
        return self::$factory->button()->bulky(
            self::$factory->symbol()->icon()->standard(
                'nota',
                $plugin_object->txt('ui_admin_configuration_security_button_label'),
                'medium'
            ),
            $plugin_object->txt('ui_admin_configuration_security_button_label'),
            self::$control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "healthcheck")
        );
    }

    /**
     * Gets the Bulktesting button for the plugin configuration
     * @throws ilCtrlException
     */
    private static function getBulktestingButton(ilPlugin $plugin_object): Bulky
    {
        return self::$factory->button()->bulky(
            self::$factory->symbol()->icon()->standard(
                'nota',
                $plugin_object->txt('ui_admin_configuration_bulktesting_button_label'),
                'medium'
            ),
            $plugin_object->txt('ui_admin_configuration_bulktesting_button_label'),
            self::$control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "bulktesting")
        );
    }
}