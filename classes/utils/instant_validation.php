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
            $question->questionInitialisation(null, false);
        } catch (stack_exception|StackException $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }

	//Secure input
	$user_response = array($input_name => ilutil::stripScriptHTML($user_response));

	//Get Teacher answer
	if (array_key_exists($input_name, $question->getTas())) {
		if ($question->getTas($input_name)->is_correctly_evaluated()) {
			try {
				$teacher_answer = $question->getTas($input_name)->get_value();
			} catch (stack_exception $e) {
				return $e->getMessage();
			}
		} else {
			return "not properly evaluated";
		}
	} else {
		return "no teacher answer on this input";
	}

	try {
		if (is_a($input = $question->inputs[$input_name], 'stack_matrix_input')) {
			$user_response = $input->maxima_to_response_array($user_response[$input_name]);
		}
        if ($question->getCached('statement-qv') !== null) {
            $question->inputs[$input_name]->add_contextsession( new stack_secure_loader($question->getCached('statement-qv'), 'qv'));
        }
		$status = $question->inputs[$input_name]->validate_student_response($user_response, $question->options, $teacher_answer, $question->getSecurity());
	} catch (stack_exception $e) {
		return $e->getMessage();
	}

	$result = array('input' => $user_response, 'status' => $status->status, 'message' => stack_maxima_latex_tidy($question->inputs[$input_name]->render_validation($status, $input_name)));

	return $result['message'];
}