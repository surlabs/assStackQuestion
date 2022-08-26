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

	/* QUESTION TEXT RENDERING */

	/**
	 * Renders the question text as a CASText
	 * Replaces input, validation & feedback placeholders
	 * @param assStackQuestion $question
	 * @param bool $show_inline_feedback
	 * @return string
	 */
	public static function _renderQuestionText(assStackQuestion $question, bool $show_inline_feedback = true): string
	{
		global $DIC;

		$question_text = $question->question_text_instantiated;

		// Replace inputs.
		$inputs_to_validate = array();

		// Get the list of placeholders before format_text.
		$input_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'input'));
		sort($input_placeholders);
		$feedback_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'feedback'));
		sort($feedback_placeholders);

		// Now format the question-text.
		$question_text = stack_maths::process_display_castext($question_text);

		//Add MathJax (Ensure MathJax is loaded)
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));

		//Inputs Replacement
		foreach ($question->inputs as $name => $input) {

			// Get the actual value of the teacher's answer at this point.
			$ta_value = $question->getTas($name);

			$field_name = 'xqcas_' . $question->getId() . '_' . $name;
			$state = $question->getInputStates($name);
			if (is_a($state, 'stack_input_state')) {
				if (($input->get_parameter('showValidation') != 0)) {

					//Do not show validation in some inputs
					$validation_button = '';
					if (!is_a($input, 'stack_radio_input') &&
						!is_a($input, 'stack_dropdown_input') &&
						!is_a($input, 'stack_checkbox_input') &&
						!is_a($input, 'stack_boolean_input')) {
						$validation_button = self::_renderValidationButton($question->getId(), $name);
					}

					//Input Placeholders
					$question_text = str_replace("[[input:{$name}]]",
						$input->render($state, $field_name, false, $ta_value) . ' ' . $validation_button,
						$question_text);

					//Validation Placeholders
					if (is_a($input, 'stack_matrix_input')) {
						$ilias_validation = '<div id="validation_xqcas_' . $question->getId() . '_' . $name . '"></div>
												<div class="xqcas_input_validation">
													<div id="validation_xqcas_' . $question->getId() . '_' . $name . '"></div> 
												</div>' .
							'<div id="xqcas_input_matrix_width_' . $name . '" style="visibility: hidden">' . $input->getWidth() . '</div>
											<div id="xqcas_input_matrix_height_' . $name . '" style="visibility: hidden">' . $input->getHeight() . '</div>';
					} else {
						$ilias_validation = '<div id="validation_xqcas_' . $question->getId() . '_' . $name . '"></div>
												<div class="xqcas_input_validation">
													<div id="validation_xqcas_' . $question->getId() . '_' . $name . '"></div>
												</div>';
					}

					$question_text = $input->replace_validation_tags($state, $field_name, $question_text, $ilias_validation);
				}
			} else {
				//Show malformed input error
				$question_text = str_replace("[[input:{$name}]]",
					'Error rendering input: ' . $name,
					$question_text);
			}

			if ($input->requires_validation()) {
				$inputs_to_validate[] = $name;
			}
		}

		if ($show_inline_feedback and !assStackQuestionUtils::_isEmptyResponse($question->getUserResponse(), $question->inputs)) {
			//Feedback Replacements
			foreach (stack_utils::extract_placeholders($question_text, 'feedback') as $prt_name) {

				$evaluation = $question->getEvaluation();

				$prt_feedback = '';

				switch ($evaluation['points'][$prt_name]['status']) {
					case 'correct':
						$prt_feedback .= $question->prt_correct_instantiated;
						break;
					case 'incorrect':
						$prt_feedback .= $question->prt_incorrect_instantiated;
						break;
					case 'partially_correct':
						$prt_feedback .= $question->prt_partially_correct_instantiated;
						break;
					default:
						$prt_feedback .= '';
				}

				//Errors & Feedback
				//Ensure evaluation has been done
				if (!isset($evaluation['prts'][$prt_name]) or !is_a($evaluation['prts'][$prt_name], 'stack_potentialresponse_tree_state')) {

					$prt_feedback .= 'WARNING: No evaluation state for prt: ' . $prt_name . '</br>';

				} else {

					$prt_state = $evaluation['prts'][$prt_name];

					$prt_feedback .= self::renderPRTFeedbackForPreview($prt_state);

				}

				//Replace Placeholders
				$question_text = assStackQuestionUtils::_replacePlaceholders($prt_name, $question_text, $prt_feedback);
			}
		} else {
			//Hide all feedback placeholders
			foreach ($feedback_placeholders as $prt_name) {
				$question_text = str_replace("[[feedback:{$prt_name}]]", '', $question_text);
			}
		}

		//Validation
		//Button Validation
		$jsconfig = new stdClass();
		$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

		$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
		$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');

		//General Validation Errors
		if (!assStackQuestionUtils::_isEmptyResponse($question->getUserResponse(), $question->inputs)) {
			$validation_error = $question->getValidationError($question->getUserResponse());

			//Show validation error only if an answer was given
			if ($validation_error) {
				$question_text .= '</br>' . $validation_error;
			}
		}

		return assStackQuestionUtils::_getLatex($question_text);
	}

	public static function renderQuestionTextForTestView()
	{

	}

	public static function renderQuestionTextForTestResults()
	{

	}

	/* GENERAL + SPECIFIC FEEDBACK RENDERING */

	/**
	 * Uses Evaluation Object -> Test & Preview
	 * Renders the General Feedback text
	 * @param assStackQuestion $question
	 * @return string HTML Code with the rendered specific feedback text
	 */
	public static function _renderGeneralFeedback(assStackQuestion $question): string
	{
		try {
			$general_feedback_text = new stack_cas_text($question->general_feedback, $question->session, $question->seed);

			if ($general_feedback_text->get_errors()) {
				$question->runtime_errors[$general_feedback_text->get_errors()] = true;
			}

			return assStackQuestionUtils::_getLatex($general_feedback_text->get_display_castext());
		} catch (stack_exception $e) {
			return $general_feedback_text->get_errors();
		}
	}

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
			throw new stack_exception('Inconsistent placeholders. Possibly due to multi-lang filter not being active.');
		}

		$input_number = 1;
		foreach ($question->inputs as $name => $input) {
			// Get the actual value of the teacher's answer at this point.
			$ta_value = $question->getTeacherAnswerForInput($name);

			//$field_name = 'q' . $question->getId() . ':' . $input_number . '_' . $name;
			$field_name = 'xqcas_' . $question->getId() . '_' . $name;
			$state = $question->getInputState($name, $response);
			if (is_a($state, 'stack_input_state')) {
				if (($input->get_parameter('showValidation') != 0) and !is_a($input, 'stack_boolean_input')) {
					//Input and Validation Button
					if (!$show_best_solution) {
						//Do not show validation in some inputs
						$validation_button = '';
						if (!is_a($input, 'stack_radio_input') && !is_a($input, 'stack_dropdown_input') && !is_a($input, 'stack_checkbox_input')) {
							$validation_button = self::_renderValidationButton($question->getId(), $name);
						}
						$question_text = str_replace("[[input:{$name}]]", ' ' . $input->render($state, $field_name, false, $ta_value) . ' ' . $validation_button, $question_text);
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
						$question_text = str_replace("[[validation:{$name}]]", '</br>', $question_text);
					} else {
						$question_text = str_replace("[[input:{$name}]]", ' ' . $input->render($state, $field_name, false, $ta_value), $question_text);
						$question_text = str_replace("[[validation:{$name}]]", '</br>', $question_text);
					}
				}
			}

			$input_number++;
		}

		// Replace PRTs.
		foreach ($question->prts as $index => $prt) {

			$feedback = '';
			$prt_results = $question->getPrtResult($index, $response, true);

			if ($show_inline_feedback) {

				if ($prt_results->_score <= 0) {
					$feedback .= $question->prt_incorrect_instantiated;
				} elseif ($prt_results->_score >= 1) {
					$feedback .= $question->prt_correct_instantiated;
				} else {
					$feedback .= $question->prt_partially_correct_instantiated;
				}

				$feedback .= '<div>' . self::_prtFeedbackDisplay($index, $prt_results, $prt->get_feedbackstyle()) . '</div>';

			} else {
				// The behaviour name test here is a hack. The trouble is that interactive
				// behaviour or adaptivemulipart does not show feedback if the input
				// is invalid, but we want to show the CAS errors from the PRT.
				$feedback = html_writer::nonempty_tag('span', $prt_results->errors, array('class' => 'stackprtfeedback stackprtfeedback-' . $name));
			}

			$points = $prt_results->_score * $prt_results->_weight;

			$question_text = str_replace("[[feedback:{$index}]]", $feedback, $question_text);
		}

		//Validation
		//Button Validation
		$jsconfig = new stdClass();
		$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

		$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
		$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');

		//General Validation Errors
		if (!assStackQuestionUtils::_isEmptyResponse($question->getUserResponse(), $question->inputs)) {
			$validation_error = $question->getValidationError($question->getUserResponse());

			//Show validation error only if an answer was given
			if ($validation_error) {
				$question_text .= '</br>' . $validation_error;
			}
		}

		//Show validation error only if an answer was given
		if ($validation_error and !empty($response)) {
			$question_text .= '</br>' . $validation_error;
		}

		return assStackQuestionUtils::_getLatex($question_text);
	}


	/**
	 * Uses Evaluation Object -> Preview
	 * Renders the Feedback in a CAStext
	 * Including all feedback placeholders
	 * @param assStackQuestion $question
	 * @param string $mode specific|text
	 * @return string HTML Code with the rendered specific feedback text
	 */
	public static function _renderFeedbackForPreview(assStackQuestion $question): string
	{
		//Specific feedback
		$text_to_replace = $question->specific_feedback_instantiated;

		foreach (stack_utils::extract_placeholders($text_to_replace, 'feedback') as $prt_name) {

			$evaluation = $question->getEvaluation();

			$prt_feedback = '';

			switch ($evaluation['points'][$prt_name]['status']) {
				case 'correct':
					$prt_feedback .= $question->prt_correct_instantiated;
					break;
				case 'incorrect':
					$prt_feedback .= $question->prt_incorrect_instantiated;
					break;
				case 'partially_correct':
					$prt_feedback .= $question->prt_partially_correct_instantiated;
					break;
				default:
					$prt_feedback .= '';
			}

			//Errors & Feedback
			//Ensure evaluation has been done
			if (!isset($evaluation['prts'][$prt_name]) or !is_a($evaluation['prts'][$prt_name], 'stack_potentialresponse_tree_state')) {

				$prt_feedback .= 'WARNING: No evaluation state for prt: ' . $prt_name . '</br>';

			} else {

				$prt_state = $evaluation['prts'][$prt_name];

				//Manage LaTeX explicitly
				$prt_feedback .= assStackQuestionUtils::_getLatex(self::renderPRTFeedbackForPreview($prt_state));
			}

			//Replace Placeholders
			$text_to_replace = assStackQuestionUtils::_replacePlaceholders($prt_name, $text_to_replace, $prt_feedback);
		}

		//Use General Feedback Style for the whole Speficic Feedback Text
		return assStackQuestionUtils::_getFeedbackStyledText($text_to_replace, 'feedback_default');

	}

	/**
	 * Uses $user_solution_from_db -> Test View
	 * Renders the Specific Feedback text
	 * Including all feedback placeholders
	 * status in db determines feedback class
	 * Used also for Test Results
	 * @param assStackQuestion $question
	 * @param array $user_solution_from_db
	 * @param string $mode specific|text
	 * @return string HTML Code with the rendered specific feedback text
	 */
	public static function _renderFeedbackForTest(assStackQuestion $question, array $user_solution_from_db, string $mode): string
	{
		if ($mode == 'specific') {
			//Specific feedback
			$text_to_replace = $question->specific_feedback_instantiated;
		} elseif ($mode == 'text') {
			//Text feedback
			$text_to_replace = $question->question_text_instantiated;
		} else {
			return 'ERROR: No mode given for feedback in preview';
		}

		foreach (stack_utils::extract_placeholders($text_to_replace, 'feedback') as $prt_name) {

			$prt_feedback = '';
			$format = "1";

			//Ensure points obtained are known
			if (isset($user_solution_from_db['xqcas_prt_' . $prt_name . '_status'])) {

				$prt_status = (float)$user_solution_from_db['xqcas_prt_' . $prt_name . '_status'];

				if ($prt_status == $question->getPoints()) {
					$prt_feedback .= $question->prt_correct_instantiated;
					$format = '2';
				} elseif ($prt_status <= 0.0) {
					$prt_feedback .= $question->prt_incorrect_instantiated;
					$format = '3';
				} else {
					$prt_feedback .= $question->prt_partially_correct_instantiated;
				}

				if (isset($user_solution_from_db['xqcas_prt_' . $prt_name . '_feedback'])) {

					//Substitute Variables in Feedback text
					$prt_feedback .= self::substituteVariablesInFeedback(null, $user_solution_from_db['xqcas_prt_' . $prt_name . '_feedback'], $format, 'test');

					//Ensure LaTeX is properly render
					$prt_feedback = stack_maths::process_display_castext($prt_feedback, null);

					//Replace Temporal Style Placeholders
					$prt_feedback = self::replaceFeedbackPlaceHolders($prt_feedback);
				}
			}

			$text_to_replace = stack_utils::replace_feedback_placeholders($text_to_replace, $prt_feedback);
		}

		//Use General Feedback Style for the whole Speficic Feedback Text
		if ($mode == 'specific') {
			return assStackQuestionUtils::_getFeedbackStyledText($text_to_replace, 'feedback_default');
		} else {
			return $text_to_replace;
		}
	}

	public static function renderSpecificFeedbackForTestResults()
	{

	}

	/* BEST SOLUTION RENDERING */

	public
	static function renderBestSolutionForPreview()
	{

	}

	public
	static function renderBestSolutionForTestView()
	{

	}

	public
	static function renderBestSolutionForTestResults()
	{

	}

	/* ILIAS REQUIRED METHODS RENDER BEGIN */

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
	 * @throws stack_exception
	 */
	public static function _renderQuestionSolution(assStackQuestion $question, int $active_id, int $pass = null, bool $graphicalOutput = false, bool $result_output = false, bool $show_question_only = true, bool $show_feedback = false, bool $show_correct_solution = false, bool $show_manual_scoring = false, bool $show_question_text = true): string
	{
		$correct_solution = array();

		if ($active_id === 0 and $pass === 0) {

			//Preview Mode
			$question_text = $question->question_text_instantiated;
			$correct_solution = $question->getCorrectResponse();

		} else {
			//Test Mode
			$user_solutions_from_db = $question->getTestOutputSolutions($active_id, $pass);

			$question_text = $user_solutions_from_db['question_text'];
			if (isset($user_solutions_from_db['inputs'])) {
				foreach ($user_solutions_from_db['inputs'] as $input_name => $input) {
					$teacher_solution = $input['correct_value'];

					//TEXTAREAS EQUIV, User response from DB tuning
					if (isset($question->inputs[$input_name]) && (is_a($question->inputs[$input_name], 'stack_textarea_input') or is_a($question->inputs[$input_name], 'stack_equiv_input'))) {
						$teacher_solution = substr($teacher_solution, 1, -1);
						$teacher_solution = explode(',', $teacher_solution);
						$teacher_solution = implode("\n", $teacher_solution);
					}
					$correct_solution[$input_name] = $teacher_solution;
				}
			}
		}

		//Replace Input placeholders
		foreach ($question->inputs as $input_name => $input) {

			// Get the actual value of the teacher's answer at this point.
			$teacher_answer_input = $input;

			$correct_state = $teacher_answer_input->validate_student_response($correct_solution, $question->options, $correct_solution[$input_name], $question->getSecurity(), false);

			$field_name = 'xqcas_solution_' . $question->getId() . '_' . $input_name;

			$question_text = str_replace("[[input:{$input_name}]]", '&nbsp' . $input->render($correct_state, $field_name, true, $correct_solution[$input_name]), $question_text);
			$question_text = str_replace("[[validation:{$input_name}]]", '</br>', $question_text);

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
				$user_response_from_db = $user_solutions_from_db['inputs'][$input_name]['value'];

				//TEXTAREAS EQUIV, User response from DB tuning
				if (is_a($input, 'stack_textarea_input') or is_a($input, 'stack_equiv_input')) {
					$user_response_from_db = substr($user_response_from_db, 1, -1);
					$user_response_from_db = explode(',', $user_response_from_db);
					$user_response_from_db = implode("\n", $user_response_from_db);
				}

				$state = $question->getInputState($input_name, array($input_name => $user_response_from_db));

				$validation_button = '';
				//Do not show validation in some inputs
				if (!is_a($input, 'stack_radio_input') && !is_a($input, 'stack_dropdown_input') && !is_a($input, 'stack_checkbox_input')) {
					$validation_button = self::_renderValidationButton($question->getId(), $input_name);
				}

				if ($input->get_parameter('showValidation') != 0) {
					$question_text = str_replace("[[input:{$input_name}]]", ' ' . $input->render($state, $field_name, false, $ta_value) .
						' ' . $validation_button, $question_text);
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
		return $feedback;
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

	/* FEEDBACK RENDERING HELPER METHODS BEGIN */

	/**
	 * Uses Evaluation Object -> Preview
	 * Renders the feedback from a single PRT evaluation
	 * @param stack_potentialresponse_tree_state $prt_state
	 * @return string HTML Code with the rendered PRT feedback
	 */
	protected static function renderPRTFeedbackForPreview(stack_potentialresponse_tree_state $prt_state): string
	{
		$feedback = '';
		$feedback_bits = $prt_state->get_feedback();
		$feedback_array = array();

		if ($feedback_bits) {
			$format = "1";
			foreach ($feedback_bits as $bit) {
				$feedback_array[] = $bit->feedback;
				if (!is_null($bit->format)) {
					if (is_null($format)) {
						$format = $bit->format;
					}
					if ($bit->format != $format) {
						ilutil::sendFailure('Inconsistent feedback formats found in PRT ', true);
					}
				}
			}

			//Substitute Variables in Feedback text
			$feedback .= self::substituteVariablesInFeedback($prt_state, $feedback_array, $format, 'preview');

			//Ensure LaTeX is properly render
			$feedback = stack_maths::process_display_castext($feedback, null);

			//Replace Temporal Placeholders
			$feedback = assStackQuestionUtils::_getFeedbackStyledText($feedback, $format);
		}

		return self::replaceFeedbackPlaceHolders($feedback);
	}

	/**
	 * Add temporal placeholders for feedback styles while replace variables in feedback
	 * @param stack_potentialresponse_tree_state|null $prt_state
	 * @param array|string $feedback
	 * @param string $format
	 * @param string $mode
	 * @return string
	 */
	protected static function substituteVariablesInFeedback(?stack_potentialresponse_tree_state $prt_state, $feedback, string $format, string $mode): string
	{
		if ($mode == 'preview') {
			switch ($format) {
				case "2":
					$feedback = "[[feedback_node_right]]" . $prt_state->substitue_variables_in_feedback(implode(' ', $feedback)) . "[[feedback_node_right_close]]";
					break;
				case "3":
					$feedback = "[[feedback_node_wrong]]" . $prt_state->substitue_variables_in_feedback(implode(' ', $feedback)) . "[[feedback_node_wrong_close]]";
					break;
				case "4":
					$feedback = "[[feedback_solution_hint]]" . $prt_state->substitue_variables_in_feedback(implode(' ', $feedback)) . "[[feedback_solution_hint_close]]";
					break;
				case "5":
					$feedback = "[[feedback_extra_info]]" . $prt_state->substitue_variables_in_feedback(implode(' ', $feedback)) . "[[feedback_extra_info_close]]";
					break;
				case "6":
					$feedback = "[[feedback_plot_feedback]]" . $prt_state->substitue_variables_in_feedback(implode(' ', $feedback)) . "[[feedback_plot_feedback_close]]";
					break;
				default:
					//By default, add no style
					$feedback = $prt_state->substitue_variables_in_feedback(implode(' ', $feedback));
					break;
			}
		} elseif ($mode == 'test') {
			switch ($format) {
				case "2":
					$feedback = "[[feedback_node_right]]" . $feedback . "[[feedback_node_right_close]]";
					break;
				case "3":
					$feedback = "[[feedback_node_wrong]]" . $feedback . "[[feedback_node_wrong_close]]";
					break;
				case "4":
					$feedback = "[[feedback_solution_hint]]" . $feedback . "[[feedback_solution_hint_close]]";
					break;
				case "5":
					$feedback = "[[feedback_extra_info]]" . $feedback . "[[feedback_extra_info_close]]";
					break;
				case "6":
					$feedback = "[[feedback_plot_feedback]]" . $feedback . "[[feedback_plot_feedback_close]]";
					break;
				default:
					//By default, add no style
					break;
			}
		}

		return $feedback;
	}

	/**
	 * Replaces the temporal placeholders for the feedback with the correct HTML
	 * @param string $feedback
	 * @return array|string|string[] HTML Stylized feedback
	 */
	protected static function replaceFeedbackPlaceHolders(string $feedback): string
	{
		require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/model/configuration/class.assStackQuestionConfig.php');

		//Get Styles assigned to Formats
		$config_options = assStackQuestionConfig::_getStoredSettings("feedback");

		$text = $feedback;
		//Search for right feedback
		$style_assigned = $config_options["feedback_node_right"];
		$text = str_replace("[[feedback_node_right]]", '<div class="ilc_text_block_' . $style_assigned . ' ilPositionStatic">', $text);
		$text = str_replace("[[feedback_node_right_close]]", '</div>', $text);

		//Search for wrong feedback
		$style_assigned = $config_options["feedback_node_wrong"];
		$text = str_replace("[[feedback_node_wrong]]", '<div class="ilc_text_block_' . $style_assigned . ' ilPositionStatic">', $text);
		$text = str_replace("[[feedback_node_wrong_close]]", '</div>', $text);

		//Search for wrong feedback
		$style_assigned = $config_options["feedback_solution_hint"];
		$text = str_replace("[[feedback_solution_hint]]", '<div class="ilc_text_block_' . $style_assigned . ' ilPositionStatic">', $text);
		$text = str_replace("[[feedback_solution_hint_close]]", '</div>', $text);

		//Replace Extra info
		$style_assigned = $config_options["feedback_extra_info"];
		$text = str_replace("[[feedback_extra_info]]", '<div class="ilc_text_block_' . $style_assigned . ' ilPositionStatic">', $text);
		$text = str_replace("[[feedback_extra_info_close]]", '</div>', $text);

		//Replace Extra info
		$style_assigned = $config_options["feedback_plot_feedback"];
		$text = str_replace("[[feedback_plot_feedback]]", '<div class="ilc_text_block_' . $style_assigned . ' ilPositionStatic">', $text);
		$text = str_replace("[[feedback_plot_feedback_close]]", '</div>', $text);

		return $text;
	}

	/* FEEDBACK RENDERING HELPER METHODS END */


	/* OTHER RENDER METHODS END */


	/* IMPORT / EXPORT RENDER METHODS END */

	/* AUTHORING INTERFACE RENDER METHODS BEGIN */

	/* AUTHORING INTERFACE RENDER METHODS END */
}