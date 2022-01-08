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

//Initialize some STACK required parameters
include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php';

class assStackQuestionRenderer
{

	/* ILIAS REQUIRED METHODS RENDER BEGIN */

	public function getSpecificFeedbackOutput($userSolution)
	{
		// TODO: Implement getSpecificFeedbackOutput() method.
	}

	public static function _renderQuestionSolution($question, $active_id, $pass = null, $graphicalOutput = false, $result_output = false, $show_question_only = true, $show_feedback = false, $show_correct_solution = false, $show_manual_scoring = false, $show_question_text = true)
	{
		return self::_renderQuestion($question, false, true);
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
	 * @param bool $show_best_solution
	 * @return string
	 * @throws stack_exception
	 */
	public static function _renderQuestion(assStackQuestion $question, bool $show_inline_feedback = false, bool $show_best_solution = false): string
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

		//Replace Validation
		$validation_divs = array();

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

		// Initialise validation, if enabled.
		if (stack_utils::get_config()->ajaxvalidation) {
			$validation_divs = self::_initValidation($question, 'instant');
		} else {
			$validation_divs = self::_initValidation($question, 'button');
		}

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


		$result = '';
		$result .= $question_text;

		return $result;
	}

	/* INPUT RENDER END */

	public static function _renderAlgebraicInput(stack_input_state $state, string $field_name, bool $read_only, $tavalue)
	{

		return '';
		//fau: Add validation button from ILIAS
		$attributes_button = array();
		$attributes_button['name'] = 'cmd[' . $field_name . ']';
		$attributes_button['class'] = 'input-group-addon';
		$attributes_button['onclick'] = 'return false';
		$attributes_button['style'] = 'cursor: pointer;';
		$attributes_button['icon_class'] = 'glyphicon glyphicon-ok';

		//Prepare Output
		$input_output = '';
		//Input group
		$input_output .= html_writer::start_tag('span', array('class' => 'input-group', 'name' => $field_name));
		$input_output .= html_writer::empty_tag('input', $attributes);
		$input_output .= html_writer::start_tag('span', $attributes_button);
		$input_output .= html_writer::empty_tag('span', array('class' => $attributes_button['icon_class'], 'name' => $attributes_button['name']));
		$input_output .= html_writer::end_tag('span');
		$input_output .= html_writer::end_tag('span');

		return $input_output;
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
	 *
	 * Initialize Validation Settings and get the divs
	 * @param assStackQuestion $question
	 * @param string $validation_type
	 * @return array
	 */
	public static function _initValidation(assStackQuestion $question, string $validation_type): array
	{
		global $DIC;

		//Add variables to $DIC
		if ($validation_type == 'instant') {

			//Instant validation
			$js_config = new stdClass();
			$js_config->validate_url = ILIAS_HTTP_PATH . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/instant_validation.php";

			$js_texts = new stdClass();
			$js_texts->page = $question->getPlugin()->txt('page');

			$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/instant_validation.js');
			$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.instant_validation.init(' . json_encode($js_config) . ',' . json_encode($js_texts) . ')');
		} elseif ($validation_type == 'button') {

			//Button Validation
			$js_config = new stdClass();
			$js_config->validate_url = ILIAS_HTTP_PATH . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

			$js_texts = new stdClass();
			$js_texts->page = $question->getPlugin()->txt('page');

			$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
			$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($js_config) . ',' . json_encode($js_texts) . ')');
		}

		$validation_divs = array();
		foreach ($question->inputs as $input_name => $input) {
			if (is_a($input, 'stack_matrix_input')) {
				$validation_divs[$input_name] = '<div id="validation_xqcas_roll_' . $question->getId() . '_' . $input_name . '"></div><div id="validation_xqcas_' . $question->getId() . '_' . $input_name . '"></div><div id="xqcas_input_matrix_width_' . $input_name . '" style="visibility: hidden">' . $input->getWidth() . '</div><div id="xqcas_input_matrix_height_' . $input_name . '" style="visibility: hidden";>' . $input->getHeight() . '</div>';
			} elseif ($validation_type !== 'hidden') {
				$validation_divs[$input_name] = '<div id="validation_xqcas_roll_' . $question->getId() . '_' . $input_name . '"></div><div class="xqcas_input_validation"><div id="validation_xqcas_' . $question->getId() . '_' . $input_name . '"></div></div>';
			} else {
				$validation_divs[$input_name] = '';
			}
		}

		return $validation_divs;
	}

	/**
	 * Returns the button for current input field.
	 * @param string $input_name
	 * @return HTML the HTML code of the button of validation for this input.
	 */
	private function validationButton($input_name)
	{
		return "<button style=\"height:2.2em;\" class=\"\" name=\"cmd[xqcas_" . $this->getDisplay('question_id') . '_' . $input_name . "]\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span></button>";
	}


	/* OTHER RENDER METHODS END */

	/* AUTHORING INTERFACE RENDER METHODS BEGIN */


	/* AUTHORING INTERFACE RENDER METHODS END */
}