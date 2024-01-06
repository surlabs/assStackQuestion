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

abstract class StackUserResponse {

    abstract public static function getStackUserResponse(string $purpose, int $question_id): array;

    protected static function checkStackUserResponse(array $stack_user_response): bool {
        //TODO: SUR
        if(is_array($stack_user_response)) {
            return true;
        } else {
            return false;
        }
    }

}