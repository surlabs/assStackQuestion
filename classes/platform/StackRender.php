<?php
declare(strict_types=1);

namespace classes\platform;


use cas_evaluatable;

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

abstract class StackRender {

    /**
     * Generates the HTML for the question.
     * @param array $attempt_data
     * @param array $display_options
     * @return string
     */
    abstract public static function renderQuestion(array $attempt_data, array $display_options): string;

    abstract public static function renderSpecificFeedback(array $attempt_data, array $display_options): string;

    abstract public static function renderPRTFeedback(array $attempt_data, array $display_options): string;

    abstract public static function renderGeneralFeedback(array $attempt_data, array $display_options): string;

}