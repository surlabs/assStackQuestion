<?php
/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
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

// fim: [debug] optionally set error before initialisation
use classes\platform\StackException;

error_reporting(E_ALL);
ini_set("display_errors", "on");
// fim.

chdir("../../../../../../../../../");

// Avoid redirection to start screen
// (see ilInitialisation::InitILIAS for details)

require_once "./include/inc.header.php";
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';
//Initialization (load of stack wrapper classes)
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php';

header('Content-type: application/json; charset=utf-8');
echo json_encode(checkUserResponse($_REQUEST['question_id'], $_REQUEST['input_name'], $_REQUEST['input_value']));
exit;

/**
 * Gets the students answer and send it to maxima in order to get the validation.
 * @param $question_id
 * @param $input_name
 * @param $user_response
 * @return string the Validation message.
 */
function checkUserResponse($question_id, $input_name, $user_response)
{
	require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/class.assStackQuestion.php';

	$question = new assStackQuestion();
    try {
        $question->loadFromDb($question_id);
    } catch (stack_exception $e) {
        return $e;
    }

    //Instantiate Question if not.
    if (!$question->isInstantiated()) {
        try{
            $question->questionInitialisation(1, true);
        } catch (stack_exception|StackException $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }

	$user_response = array($input_name => $user_response);
    try {
        if (is_a($input = $question->inputs[$input_name], 'stack_matrix_input')) {
            $user_response = $input->maxima_to_response_array($user_response[$input_name]);
        }
        $status = $question->getInputState($input_name, $user_response);
    } catch (stack_exception $e) {
        return $e->getMessage();
    }

    return stack_maxima_latex_tidy($question->inputs[$input_name]->render_validation($status, $input_name));
}