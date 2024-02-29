<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

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
class PluginConfigurationBulktestingUI
{

    private static Factory $factory;
    private static Renderer $renderer;
    private static ilCtrl $control;

    /**
     * Shows the bulktesting
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
                'bulktesting',
                'run'
            );

            //TODO add UI for bulktesting
            //get sections
            $content = [];

        } catch (Exception $e) {
            $content = ['error' => self::$factory->messageBox()->failure($e->getMessage())];
        }

        return $content;
    }
}