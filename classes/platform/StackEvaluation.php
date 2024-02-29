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

class StackEvaluation {

    const EVALUATION_CORRECT = 'correct';
    const EVALUATION_PARTIALLY_CORRECT = 'partially_correct';
    const EVALUATION_INCORRECT = 'incorrect';

    /**
     * Return the appropriate graded state based on a fraction. That is 0 or less
     * is $graded_incorrect, 1 is $graded_correct, otherwise it is $graded_partcorrect.
     * Appropriate allowance is made for rounding float values.
     *
     */
    public static function stateForFraction($fraction): string
    {
        if ($fraction === 1.0) {
            return self::EVALUATION_CORRECT;
        } else if ($fraction === 0.0 or $fraction === null) {
            return self::EVALUATION_INCORRECT;
        } else {
            return self::EVALUATION_PARTIALLY_CORRECT;
        }
    }
}