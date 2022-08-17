<?php

// fim: [debug] optionally set error before initialisation
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

	//Initialize question from seed
	$active_id = $_GET['active_id'];
	require_once "./Modules/Test/classes/class.ilObjTest.php";
	$pass = ilObjTest::_getPass($active_id);

	if (is_int($active_id) and is_int($pass)) {
		//test mode
	} else {
		//preview mode
		$seed = $_SESSION['q_seed_for_preview_' . $_GET['q_id'] . ''];
		$question->questionInitialisation($seed, true);
	}

	//Secure input
	$user_response = array($input_name => ilutil::stripScriptHTML($user_response));

	//Get Teacher answer
	if (array_key_exists($input_name, $question->getTas())) {
		if ($question->getTas($input_name)->is_correctly_evaluated()) {
			try {
				$teacher_answer = $question->getTas($input_name)->get_value();
			} catch (stack_exception $e) {
				return $e;
			}
		} else {
			return "not properly evaluated";
		}
	} else {
		return "no teacher answer on this input";
	}

	try {
		$status = $question->inputs[$input_name]->validate_student_response($user_response, $question->options, $teacher_answer, $question->getSecurity());
	} catch (stack_exception $e) {
		return $e->getMessage();
	}

	$result = array('input' => $user_response, 'status' => $status->status, 'message' => $question->inputs[$input_name]->render_validation($status, $input_name));

	return $result['message'];
}