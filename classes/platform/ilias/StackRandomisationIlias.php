<?php
declare(strict_types=1);

namespace classes\platform\ilias;


use assStackQuestion;
use assStackQuestionDB;
use maxima_parser_utils;
use stack_exception;

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
class StackRandomisationIlias
{

    /**
     * @throws stack_exception
     */
    public static function getRandomisationData(assStackQuestion $question, ?int $force_active_seed): array
    {
        $valid_seeds = array();
        $number_of_valid_seeds = 0;

        $variants = assStackQuestionDB::_readDeployedVariants($question->getId());

        if ($force_active_seed !== null && !array_key_exists($force_active_seed, $variants)) {
            $variants[null] = $force_active_seed;
        }

        //Get question note for each different seed
        foreach ($variants as $id => $deployed_seed) {
            $question->questionInitialisation($deployed_seed, true, true);

            //Format question variables
            $question_variables = $question->getQuestionSessionKeyvalRepresentation();
            $question_text_instantiated = $question->question_text_instantiated;
            $question_note_instantiated = $question->question_note_instantiated;
            $feedback_variables = '';

            foreach ($question->prts as $prt) {
                $vars = $prt->get_feedbackvariables_keyvals();

                if (!empty($vars)) {
                    $feedback_variables .= "<h3><u><i>Prt: " . $prt->get_name() . "</i></u></h3>" . $prt->get_feedbackvariables_keyvals() . "<br>";
                }
            }

            $valid_seeds[$id] = array('seed' => $deployed_seed,
                'note' => $question_note_instantiated,
                'question_id' => $question->getId(),
                'question_text' => $question_text_instantiated,
                'question_variables' => $question_variables,
                'feedback_variables' => $feedback_variables
            );
        }

        return $valid_seeds;
    }
}