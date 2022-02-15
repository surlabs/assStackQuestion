<?php
/**
 * Copyright (c) 2022 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * STACK Question Render Class
 * All rendering is processed here
 * GUI classes call this renderer after initialisation od assStackQuestion
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id: 4.0$
 *
 */

//Initialize some STACK required parameters
include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php';

class assStackQuestionRenderer
{

	/* ILIAS REQUIRED METHODS RENDER BEGIN */

	/**
	 * @param assStackQuestion $question
	 * @param array $user_solution
	 * @return string
	 */
	public static function _renderSpecificFeedback(assStackQuestion $question, array $user_solution): string
	{
		//General Feedback output
		$general_feedback = self::_renderGeneralFeedback($question, $user_solution);

		//Specific feedback
		$specific_feedback = $question->specific_feedback_instantiated;

		if (!$specific_feedback) {
			return $general_feedback;
		}

		$specific_feedback_text = stack_maths::process_display_castext($specific_feedback);

		//TODO Connect with feedback styles should be done here.

		// Replace specific feedback placeholders.
		try {
			foreach (stack_utils::extract_placeholders($question->specific_feedback_instantiated, 'feedback') as $prt_name) {
				$feedback = self::_prtFeedbackDisplay($prt_name, $question->getPrtResult($prt_name, $user_solution, true), $question->prts[$prt_name]->get_feedbackstyle());
				$specific_feedback_text = str_replace("[[feedback:{$prt_name}]]", stack_maths::process_display_castext($feedback), $specific_feedback_text);
			}
		} catch (stack_exception $e) {
			$specific_feedback_text = $e->getMessage();
		}

		return $specific_feedback_text . $general_feedback;
	}

	/**
	 * @param assStackQuestion $question
	 * @param array $user_solution
	 * @return string
	 */
	public static function _renderGeneralFeedback(assStackQuestion $question, array $user_solution): string
	{
		try {
			$general_feedback_text = new stack_cas_text($question->general_feedback, $question->session, $question->seed);

			if ($general_feedback_text->get_errors()) {
				$question->runtime_errors[$general_feedback_text->get_errors()] = true;
			}

			return assStackQuestionUtils::_getLatex($general_feedback_text->get_display_castext());
		} catch (stack_exception $e) {
			return '';
		}
	}

	/**
	 * @param assStackQuestion $question
	 * @param int $active_id
	 * @param int|null $pass
	 * @param bool $graphicalOutput
	 * @param bool $result_output
	 * @param bool $show_question_only
	 * @param bool $show_feedback
	 * @param bool $show_correct_solution
	 * @param bool $show_manual_scoring
	 * @param bool $show_question_text
	 * @return string
	 */
	public static function _renderQuestionSolution(assStackQuestion $question, int $active_id, int $pass = null, bool $graphicalOutput = false, bool $result_output = false, bool $show_question_only = true, bool $show_feedback = false, bool $show_correct_solution = false, bool $show_manual_scoring = false, bool $show_question_text = true): string
	{
		$correct_solution = array();

		if ($active_id === 0 and $pass === 0) {
			//Preview Mode
			$question_text = $question->question_text_instantiated;

			foreach ($question->inputs as $input_name => $input) {
				$correct_solution[$input_name] = $question->getTas($input_name)->get_dispvalue();
			}

		} else {
			//Test Mode
			$user_solutions_from_db = $question->getTestOutputSolutions($active_id, $pass);

			$question_text = $user_solutions_from_db['question_text'];
			if (isset($user_solutions_from_db['inputs'])) {
				foreach ($user_solutions_from_db['inputs'] as $input_name => $input) {
					$correct_solution[$input_name] = $input['correct_value'];
				}
			}
		}

		//Replace Input placeholders
		foreach ($question->inputs as $input_name => $input) {

			// Get the actual value of the teacher's answer at this point.
			$ta_value = $question->getTeacherAnswerForInput($input_name);

			$field_name = 'xqcas_solution_' . $question->getId() . '_' . $input_name;
			$state = $question->getInputState($input_name, array($input_name => $correct_solution[$input_name]));

			if ($input->get_parameter('showValidation') != 0) {
				$question_text = str_replace("[[input:{$input_name}]]", ' ' . $input->render($state, $field_name, true, $ta_value), $question_text);
				$ilias_validation = '';
				$question_text = $input->replace_validation_tags($state, $field_name, $question_text, $ilias_validation);
			} else {
				$question_text = str_replace("[[input:{$input_name}]]", ' ' . $input->render($state, $field_name, true, $ta_value), $question_text);
				$ilias_validation = '';
				$question_text = $input->replace_validation_tags($state, $field_name, $question_text, $ilias_validation);
			}
		}

		//Replace PRT placeholders
		foreach ($question->prts as $prt_name => $prt) {
			$question_text = str_replace("[[feedback:{$prt_name}]]", $user_solutions_from_db['prts'][$prt_name]['feedback'] . $user_solutions_from_db['prts'][$prt_name]['errors'], $question_text);
		}

		//Return question text
		return assStackQuestionUtils::_getLatex($question_text);
	}


	/**
	 * @param assStackQuestion $question
	 * @param bool $show_inline_feedback
	 * @return string
	 */
	public static function _renderQuestionPreview(assStackQuestion $question, bool $show_inline_feedback = false): string
	{
		try {
			return self::_renderQuestion($question, true, false, $show_inline_feedback);
		} catch (stack_exception$e) {
			return $e->getMessage();
		}
	}

	/**
	 * @param assStackQuestion $question
	 * @param int $active_id
	 * @param int $pass
	 * @param bool $user_solutions
	 * @param bool $show_specific_inline_feedback
	 * @param bool $is_question_postponed
	 * @return string
	 * @throws stack_exception
	 */
	public static function _renderQuestionTest(assStackQuestion $question, int $active_id, int $pass, bool $user_solutions, bool $show_specific_inline_feedback, bool $is_question_postponed = false): string
	{
		if (empty($user_solutions_from_db = $question->getTestOutputSolutions($active_id, $pass))) {
			//Render question from scratch
			return self::_renderQuestion($question, $show_specific_inline_feedback, false, $active_id, $pass);
		} else {
			//Question has been already evaluated, use DB Data
			$question_text = $user_solutions_from_db['question_text'];

			//Replace Input placeholders
			foreach ($question->inputs as $input_name => $input) {

				// Get the actual value of the teacher's answer at this point.
				$ta_value = $question->getTeacherAnswerForInput($input_name);

				$field_name = 'xqcas_' . $question->getId() . '_' . $input_name;
				$state = $question->getInputState($input_name, array($input_name => $user_solutions_from_db['inputs'][$input_name]['value']));

				if ($input->get_parameter('showValidation') != 0) {
					$question_text = str_replace("[[input:{$input_name}]]", ' ' . $input->render($state, $field_name, false, $ta_value) . ' ' . self::_renderValidationButton($question->getId(), $input_name), $question_text);
					$ilias_validation =
						'<div id="validation_xqcas_' . $question->getId() . '_' . $input_name . '">'
						. $user_solutions_from_db['inputs'][$input_name]['validation_display'] .
						'</div><div class="xqcas_input_validation"><div id="validation_xqcas_'
						. $question->getId() . '_' . $input_name . '"></div></div>';
					$question_text = $input->replace_validation_tags($state, $field_name, $question_text, $ilias_validation);
				} else {
					$question_text = str_replace("[[input:{$input_name}]]", ' ' . $input->render($state, $field_name, false, $ta_value), $question_text);
				}
			}

			//Replace PRT placeholders
			foreach ($question->prts as $prt_name => $prt) {
				$question_text = str_replace("[[feedback:{$prt_name}]]", $user_solutions_from_db['prts'][$prt_name]['feedback'] . $user_solutions_from_db['prts'][$prt_name]['errors'], $question_text);
			}

			//Validation
			//Button Validation
			global $DIC;
			$jsconfig = new stdClass();
			$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

			$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
			$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');

			return assStackQuestionUtils::_getLatex($question_text);
		}
	}

	/* ILIAS REQUIRED METHODS RENDER END */

	/* OTHER RENDER METHODS BEGIN */

	/**
	 * @param assStackQuestion $question
	 * @param bool $show_inline_feedback
	 * @param bool $show_best_solution
	 * @param int|null $active_id
	 * @param int|null $pass
	 * @return string
	 * @throws stack_exception
	 */
	public static function _renderQuestion(assStackQuestion $question, bool $show_inline_feedback = false, bool $show_best_solution = false, int $active_id = null, int $pass = null): string
	{
		global $DIC;

		if ($active_id !== null and $pass !== null) {
			//QUESTION
			$response = $question->getUserResponse();
		} else {
			//Render Preview Version
			if (!$show_best_solution) {
				//QUESTION
				$response = $question->getUserResponse();
			} else {
				//BEST SOLUTION
				$response = $question->getCorrectResponse();
			}
		}

		$question_text = $question->question_text_instantiated;
		// Replace inputs.
		$inputs_to_validate = array();

		// Get the list of placeholders before format_text.
		$original_input_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'input'));
		sort($original_input_placeholders);

		$original_feedback_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'feedback'));
		sort($original_feedback_placeholders);

		// Now format the question-text.
		$question_text = stack_maths::process_display_castext($question_text);

		// Get the list of placeholders after format_text.
		$formatted_input_placeholders = stack_utils::extract_placeholders($question_text, 'input');
		sort($formatted_input_placeholders);
		$formatted_feedback_placeholders = stack_utils::extract_placeholders($question_text, 'feedback');
		sort($formatted_feedback_placeholders);

		//Add MathJax (Ensure MathJax is loaded)
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));

		// We need to check that if the list has changed.
		// Have we lost some placeholders entirely?
		// Duplicates may have been removed by multi-lang,
		// No duplicates should remain.
		if ($formatted_input_placeholders !== $original_input_placeholders ||
			$formatted_feedback_placeholders !== $original_feedback_placeholders) {
			throw new stack_exception('Inconsistent placeholders. Possibly due to multi-lang filtter not being active.');
		}

		$input_number = 1;
		foreach ($question->inputs as $name => $input) {
			// Get the actual value of the teacher's answer at this point.
			$ta_value = $question->getTeacherAnswerForInput($name);

			//$field_name = 'q' . $question->getId() . ':' . $input_number . '_' . $name;
			$field_name = 'xqcas_' . $question->getId() . '_' . $name;
			$state = $question->getInputState($name, $response);

			if ($input->get_parameter('showValidation') != 0) {
				//Input and Validation Button
				if (!$show_best_solution) {
					$question_text = str_replace("[[input:{$name}]]", ' ' . $input->render($state, $field_name, false, $ta_value) . ' ' . self::_renderValidationButton($question->getId(), $name), $question_text);
				} else {
					$field_name = 'xqcas_solution_' . $question->getId() . '_' . $name;
					$question_text = str_replace("[[input:{$name}]]", ' ' . $input->render($state, $field_name, true, $ta_value), $question_text);
				}

				//Validation tags
				if (!$show_best_solution) {
					$ilias_validation = '<div id="validation_xqcas_' . $question->getId() . '_' . $name . '"></div><div class="xqcas_input_validation"><div id="validation_xqcas_' . $question->getId() . '_' . $name . '"></div></div>';
					$question_text = $input->replace_validation_tags($state, $field_name, $question_text, $ilias_validation);
				} else {
					$question_text = str_replace("[[validation:{$name}]]", '</br>', $question_text);
				}
			} else {
				if (!$show_best_solution) {
					$question_text = str_replace("[[input:{$name}]]", ' ' . $input->render($state, $field_name, false, $ta_value), $question_text);
				} else {
					$question_text = str_replace("[[validation:{$name}]]", '</br>', $question_text);
				}
			}

			if ($input->requires_validation()) {
				$inputs_to_validate[] = $name;
			}

			$input_number++;
		}

		// Replace PRTs.
		foreach ($question->prts as $index => $prt) {
			$feedback = '';
			if ($show_inline_feedback) {
				$feedback = self::_prtFeedbackDisplay($index, $question->getPrtResult($index, $response, true), $prt->get_feedbackstyle());

			} else {
				// The behaviour name test here is a hack. The trouble is that interactive
				// behaviour or adaptivemulipart does not show feedback if the input
				// is invalid, but we want to show the CAS errors from the PRT.
				$result = $question->getPrtResult($index, $response, true);
				$feedback = html_writer::nonempty_tag('span', $result->errors, array('class' => 'stackprtfeedback stackprtfeedback-' . $name));
			}
			$question_text = str_replace("[[feedback:{$index}]]", $feedback, $question_text);
		}

		//Validation
		//Button Validation
		$jsconfig = new stdClass();
		$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

		$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
		$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');

		//General Validation Errors
		$validation_error = $question->getValidationError($response);

		//Show validation error only if an answer was given
		if ($validation_error and !empty($response)) {
			$question_text .= '</br>' . $validation_error;
		}

		return assStackQuestionUtils::_getLatex($question_text);
	}

	/* INPUT RENDER END */

	/**
	 * Generates the display of the PRT feedback
	 * @param string $name
	 * @param stack_potentialresponse_tree_state $result
	 * @param $feedback_style
	 * @return string
	 * @throws stack_exception
	 */
	public
	static function _prtFeedbackDisplay(string $name, stack_potentialresponse_tree_state $result, $feedback_style): string
	{
		$err = '';
		if ($result->errors) {
			$err = $result->errors;
		}

		$feedback = '';
		$feedback_bits = $result->get_feedback();
		if ($feedback_bits) {
			$feedback = array();
			$format = null;
			foreach ($feedback_bits as $bit) {
				$feedback[] = $bit->feedback;
				if (!is_null($bit->format)) {
					if (is_null($format)) {
						$format = $bit->format;
					}
					if ($bit->format != $format) {
						throw new stack_exception('Inconsistent feedback formats found in PRT ' . $name);
					}
				}
			}

			$feedback = $result->substitue_variables_in_feedback(implode(' ', $feedback));
			$feedback = stack_maths::process_display_castext($feedback, null);
		}

		//TODO Generate the standard PRT feedback for a particular score.
		//$standard_feedback = $this->standard_prt_feedback($qa, $question, $result, $feedbackstyle);

		$tag = 'div';
		switch ($feedback_style) {
			case 0:
				// Formative PRT.
				$fb = $err . $feedback;
				break;
			case 1:
				$fb = $err . $feedback;
				break;
			case 2:
				// Compact.
				$fb = $err . $feedback;
				$tag = 'span';
				break;
			case 3:
				// Symbolic.
				$fb = $err;
				$tag = 'span';
				break;
			default:
				echo "i is not equal to 0, 1 or 2";
		}

		return html_writer::nonempty_tag($tag, $fb, array('class' => 'stackprtfeedback stackprtfeedback-' . $name));
	}

	/**
	 * Returns the button for current input field.
	 * @param string $question_id
	 * @param string $input_name
	 * @return string the HTML code of the button of validation for this input.
	 */
	public
	static function _renderValidationButton(string $question_id, string $input_name): string
	{
		return "<button style=\"height:1.8em;\" class=\"xqcas\" name=\"cmd[xqcas_" . $question_id . '_' . $input_name . "]\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span></button>";
	}


	/* OTHER RENDER METHODS END */


	/* IMPORT / EXPORT RENDER METHODS END */

	/* AUTHORING INTERFACE RENDER METHODS BEGIN */


	/* AUTHORING INTERFACE RENDER METHODS END */
}