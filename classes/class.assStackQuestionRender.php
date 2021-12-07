<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
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
class assStackQuestionRender
{
	/* ILIAS REQUIRED METHODS RENDER BEGIN */

	public function getSpecificFeedbackOutput($userSolution)
	{
		// TODO: Implement getSpecificFeedbackOutput() method.
	}

	public function getSolutionOutput($active_id, $pass = null, $graphicalOutput = false, $result_output = false, $show_question_only = true, $show_feedback = false, $show_correct_solution = false, $show_manual_scoring = false, $show_question_text = true)
	{
		// TODO: Implement getSolutionOutput() method.
	}

	/**
	 * @param assStackQuestion $question
	 * @param bool $show_inline_feedback
	 * @return string
	 * @throws stack_exception
	 */
	public static function _renderQuestionPreview(assStackQuestion $question, bool $show_inline_feedback = false): string
	{
		return self::_renderQuestion($question, $show_inline_feedback);
	}

	public function getTestOutput($active_id, $pass, $is_question_postponed, $user_post_solutions, $show_specific_inline_feedback)
	{
		// TODO: Implement getTestOutput() method.
	}

	/* ILIAS REQUIRED METHODS RENDER END */

	/* OTHER RENDER METHODS BEGIN */

	/**
	 * @param assStackQuestion $question
	 * @param bool $show_inline_feedback
	 * @return string
	 * @throws stack_exception
	 */
	public static function _renderQuestion(assStackQuestion $question, bool $show_inline_feedback = false): string
	{
		$response = $question->getUserResponse();

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

		// We need to check that if the list has changed.
		// Have we lost some of the placeholders entirely?
		// Duplicates may have been removed by multi-lang,
		// No duplicates should remain.
		if ($formatted_input_placeholders !== $original_input_placeholders ||
			$formatted_feedback_placeholders !== $original_feedback_placeholders) {
			throw new stack_exception('Inconsistent placeholders. Possibly due to multi-lang filtter not being active.');
		}

		foreach ($question->inputs as $name => $input) {
			// Get the actual value of the teacher's answer at this point.
			$ta_value = $question->getTeacherAnswerForInput($name);

			$field_name = 'xqcas_' . $question->getId() . '_' . $name;
			$state = $question->getInputState($name, $response);

			$question_text = str_replace("[[input:{$name}]]", $input->render($state, $field_name, false, $ta_value), $question_text);

			$question_text = $input->replace_validation_tags($state, $field_name, $question_text);

			if ($input->requires_validation()) {
				$inputs_to_validate[] = $name;
			}
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

		// Initialise automatic validation, if enabled.
		if (stack_utils::get_config()->ajaxvalidation) {
			//TODO automatic validation
			/*
			if (method_exists($qa, 'get_outer_question_div_unique_id')) {
				$questiondivid = $qa->get_outer_question_div_unique_id();
			} else {
				$questiondivid = 'q' . $qa->get_slot();
			}
			$this->page->requires->js_call_amd('qtype_stack/input', 'initInputs',
				[$questiondivid, $qa->get_field_prefix(),
					$qa->get_database_id(), $inputs_to_validate]);*/
		}

		$result = '';
		$result .= $question_text;

		return $result;
	}

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

			if (is_null($format)) {
				$format = FORMAT_HTML;
			}

			$feedback = $result->substitue_variables_in_feedback(implode(' ', $feedback));
			$feedback = stack_maths::process_display_castext($feedback, stack_utils::get_config()->replacedollars);
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


	/* OTHER RENDER METHODS END */

}