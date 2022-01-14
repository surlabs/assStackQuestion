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

	public function getSpecificFeedbackOutput($userSolution)
	{
		//TODO: Implement getSpecificFeedbackOutput() method.
	}


	public static function _renderQuestionSolution($question, $active_id, $pass = null, $graphicalOutput = false, $result_output = false, $show_question_only = true, $show_feedback = false, $show_correct_solution = false, $show_manual_scoring = false, $show_question_text = true): string
	{
		try {
			return self::_renderQuestion($question, $active_id, $pass, false, true);
		} catch (stack_exception$e) {
			ilUtil::sendFailure($e);
		}
	}

	/**
	 * @param assStackQuestion $question
	 * @param bool $show_inline_feedback
	 * @return string
	 * @throws stack_exception
	 */
	public static function _renderQuestionPreview(assStackQuestion $question, bool $show_inline_feedback = false): string
	{
		return self::_renderQuestion($question, 1, 1, $show_inline_feedback);
	}

	public function getTestOutput($active_id, $pass, $is_question_postponed, $user_post_solutions, $show_specific_inline_feedback)
	{
		// TODO: Implement getTestOutput() method.
	}

	/* ILIAS REQUIRED METHODS RENDER END */

	/* OTHER RENDER METHODS BEGIN */

	/**
	 * @param assStackQuestion $question
	 * @param int $active_id
	 * @param int $pass
	 * @param bool $show_inline_feedback
	 * @param bool $show_best_solution
	 * @return string
	 * @throws stack_exception
	 */
	public static function _renderQuestion(assStackQuestion $question, int $active_id, int $pass, bool $show_inline_feedback = false, bool $show_best_solution = false): string
	{
		global $DIC;

		if (!$show_best_solution) {
			//USER SOLUTION
			$response = $question->getUserResponse();
		} else {
			//BEST SOLUTION
			$response = $question->getCorrectResponse();
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
		$question_text = stack_maths::process_display_castext($question_text, null);

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
		// Have we lost some of the placeholders entirely?
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
				$question_text = str_replace("[[input:{$name}]]", ' ' . $input->render($state, $field_name, false, $ta_value) . ' ' . self::_renderValidationButton($question->getId(), $name), $question_text);

				//Validation tags
				$ilias_validation = '<div id="validation_xqcas_roll_' . $question->getId() . '_' . $name . '"></div><div class="xqcas_input_validation"><div id="validation_xqcas_' . $question->getId() . '_' . $name . '"></div></div>';

				$question_text = $input->replace_validation_tags($state, $field_name, $question_text, $ilias_validation);
			} else {
				$question_text = str_replace("[[input:{$name}]]", ' ' . $input->render($state, $field_name, false, $ta_value), $question_text);
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
		$jsconfig->validate_url = ILIAS_HTTP_PATH . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

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
	public static function _prtFeedbackDisplay(string $name, stack_potentialresponse_tree_state $result, $feedback_style): string
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
	 * @param assStackQuestion $question
	 * @param array $user_solution
	 * @return string
	 */
	public static function _renderSpecificFeedback(assStackQuestion $question, array $user_solution): string
	{
		$specific_feedback = $question->specific_feedback_instantiated;

		if (!$specific_feedback) {
			return '';
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
		return $specific_feedback_text;
	}

	/**
	 * Returns the button for current input field.
	 * @param string $question_id
	 * @param string $input_name
	 * @return string the HTML code of the button of validation for this input.
	 */
	public static function _renderValidationButton(string $question_id, string $input_name): string
	{
		return "<button style=\"height:1.8em;\" class=\"\" name=\"cmd[xqcas_" . $question_id . '_' . $input_name . "]\" id=\"cmd[xqcas_" . $question_id . '_' . $input_name . "]\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span></button>";
	}


	/* OTHER RENDER METHODS END */

	/* AUTHORING INTERFACE RENDER METHODS BEGIN */


	/* AUTHORING INTERFACE RENDER METHODS END */
}