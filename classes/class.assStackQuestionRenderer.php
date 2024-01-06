<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

/**
 * STACK Question Render Class
 * All rendering is processed here
 * GUI classes call this renderer after initialisation od assStackQuestion
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
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

		$question_text = $question->question_text_instantiated->get_rendered($question->getCasTextProcessor());
		$instant_validation = (bool)stack_utils::get_config()->ajaxvalidation;

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
			$state = $question->getInputState($name, $question->getUserResponse());
			if (is_a($state, 'stack_input_state')) {
				if (($input->get_parameter('showValidation') != 0)) {

					//Do not show validation in some inputs
					$validation_button = '';
					if (!is_a($input, 'stack_radio_input') &&
						!is_a($input, 'stack_dropdown_input') &&
						!is_a($input, 'stack_checkbox_input') &&
						!is_a($input, 'stack_boolean_input')
					) {
						if (!$instant_validation) {
							$validation_button = self::_renderValidationButton($question->getId(), $name);
						}
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
					//$question_text = str_replace("[[validation:{$name}]]", $input->render_validation($state, $field_name), $question_text);

				} else {
					//Input Placeholders
					$question_text = str_replace("[[input:{$name}]]",
						$input->render($state, $field_name, false, $ta_value),
						$question_text);

					$question_text = str_replace("[[validation:{$name}]]",
						'',
						$question_text);
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

		if (!assStackQuestionUtils::_isEmptyResponse($question->getUserResponse(), $question->inputs)) {
			//Feedback Replacements
			foreach ($question->prts as $prt_name => $prt_info) {

				$evaluation = $question->getEvaluation();
				$format = '1';
				$prt_feedback = '';

				switch ($evaluation['points'][$prt_name]['status']) {
					case 'correct':
						$prt_feedback .= $question->prt_correct_instantiated->get_rendered($question->getCasTextProcessor());
						$format = '2';

						break;
					case 'incorrect':
						$prt_feedback .= $question->prt_incorrect_instantiated->get_rendered($question->getCasTextProcessor());
						$format = '3';

						break;
					case 'partially_correct':
						$prt_feedback .= $question->prt_partially_correct_instantiated->get_rendered($question->getCasTextProcessor());
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

                    if (is_a($prt_state, 'stack_potentialresponse_tree_state')) {
                        $prt_state->set_cas_context($question->getSession(),$question->getSeed(),true);
                        $render = self::renderPRTFeedback($prt_state, $prt_name, $question);                        if(strlen($render)){
                            $prt_feedback .= $render;
                        }else{
                            foreach ($prt_state->get_feedback() as $feedback){
                                $prt_feedback .= $feedback->feedback;
                            }

                        }
                    }

				}
				$question_text = assStackQuestionUtils::_getFeedbackStyledText($question_text, 'feedback_default');

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
		$jsconfig = new stdClass();

		if ($instant_validation) {
			//Instant Validation
			$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/instant_validation.php";
			$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/instant_validation.js');
			$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.instant_validation.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');
		} else {
			//Button Validation
			$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";
			$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
			$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');
		}

		//General Validation Errors
		if (!assStackQuestionUtils::_isEmptyResponse($question->getUserResponse(), $question->inputs)) {
			$validation_error = $question->getValidationError($question->getUserResponse());

			//Show validation error only if an answer was given
			if ($validation_error) {
				$question_text .= '</br>' . $validation_error;
			}
		}

		return assStackQuestionUtils::_getLatex(stack_maxima_latex_tidy($question_text));
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
			$general_feedback_text = $question->general_feedback_instantiated;

			if ($general_feedback_text->get_errors()) {
				$question->runtime_errors[$general_feedback_text->get_errors()] = true;
			}

			return assStackQuestionUtils::_getLatex(stack_maxima_latex_tidy($general_feedback_text->get_rendered()));
		} catch (stack_exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Uses Evaluation Object -> Preview
	 * Renders the Feedback in a CAStext
	 * Including all feedback placeholders
	 * @param assStackQuestion $question
	 * @return string HTML Code with the rendered specific feedback text
	 */
	public static function _renderFeedbackForPreview(assStackQuestion $question): string
	{
        //TODO rework
        return "feedback for preview not implemented";
		//Specific feedback
		$text_to_replace = $question->specific_feedback_instantiated;

		foreach (stack_utils::extract_placeholders($text_to_replace, 'feedback') as $prt_name) {

			$evaluation = $question->getEvaluation();
			$format = '1';
			$prt_feedback = '';
			switch ($evaluation['points'][$prt_name]['status']) {
				case 'correct':
					$prt_feedback .= $question->prt_correct_instantiated;
					$format = '2';
					break;
				case 'incorrect':
					$prt_feedback .= $question->prt_incorrect_instantiated;
					$format = '3';
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
                $prt_state->set_cas_context($question->getSession(),$question->getSeed(),true);

				//Manage LaTeX explicitly
                $prt_feedback .= assStackQuestionUtils::_getLatex(stack_maxima_latex_tidy(self::renderPRTFeedback($prt_state, $prt_name, $question)));			}

			//Replace Placeholders
			$text_to_replace = assStackQuestionUtils::_replacePlaceholders($prt_name, $text_to_replace, $prt_feedback);
		}

		//Use General Feedback Style for the whole Speficic Feedback Text
		return assStackQuestionUtils::_getFeedbackStyledText($text_to_replace, 'feedback_default');
	}

	/**
	 * Uses $user_solution_from_db -> Test View
     * Calls maxima
	 * Renders the Specific Feedback text
	 * Including all feedback placeholders
	 * status in db determines feedback class
	 * Used also for Test Results
	 * @param assStackQuestion $question
	 * @param array $user_solution_from_db
	 * @return string HTML Code with the rendered specific feedback text
	 */
	public static function _renderFeedbackForTest(assStackQuestion $question, array $user_solution_from_db): string
	{
		//TST Solutions formatted entries
		$user_solution_from_db = assStackQuestionUtils::_fromTSTSolutionsToSTACK($user_solution_from_db, $question->getId(), $question->inputs, $question->prts);

        //User answer
        $user_answer = array();
        foreach($question->inputs as $input_name => $input){
            if(isset($user_solution_from_db['inputs'][$input_name]['value'])){
                $user_answer[$input_name] = $user_solution_from_db['inputs'][$input_name]['value'];
            }
        }

		//Specific feedback
		$text_to_replace = $question->specific_feedback_instantiated;

		foreach (stack_utils::extract_placeholders($text_to_replace, 'feedback') as $prt_name) {

			$prt_feedback = '';
			$format = "1";

			//Ensure points obtained are known
			if (isset($user_solution_from_db['prts'][$prt_name])) {

				$prt_info = $user_solution_from_db['prts'][$prt_name];

				//General PRT Feedback
				switch ($prt_info['status']) {
					case '1':
						$prt_feedback .= '<p>' . $question->prt_correct_instantiated . '</p>';
						$format = '2';
						break;
					case '0':
						$prt_feedback .= '<p>' . $question->prt_incorrect_instantiated . '</p>';
						$format = '3';
						break;
					default:
                        $prt_feedback .= '<p>' . $question->prt_partially_correct_instantiated . '</p>';
				}

				//Errors & Feedback
				//Ensure evaluation has been done
				//#35924
				if (isset($prt_info['feedback']) and is_string($prt_info['feedback'])) {

                    $prt_state = $question->getPrtResult($prt_name, $user_answer, true);

                    //Manage LaTeX explicitly
                    if (is_a($prt_state, 'stack_potentialresponse_tree_state')) {
                        $prt_state->set_cas_context($question->getSession(),$question->getSeed(),true);
                        $prt_feedback .= assStackQuestionUtils::_getLatex(stack_maxima_latex_tidy(self::renderPRTFeedback($prt_state)));
                    }else{
                        $prt_feedback .= '';
                    }

				} else {
                    $prt_feedback .= '';
				}

				//Replace Placeholders
				$text_to_replace = assStackQuestionUtils::_replacePlaceholders($prt_name, $text_to_replace, $prt_feedback);

			}
		}
		//Use General Feedback Style for the whole Specific Feedback Text
		return assStackQuestionUtils::_getFeedbackStyledText($text_to_replace, 'feedback_default');

	}

	/* BEST SOLUTION RENDERING */

	/**
	 * Renders a correct solution with validation for Preview and Test
	 * Uses the instanced question
	 * doesn't modify the question structure
	 * @param assStackQuestion $question
	 * @return string
	 */
	public static function _renderBestSolution(assStackQuestion $question): string
	{
		$input_correct_array = $question->getCorrectResponse();
		$question_text = $question->question_text_instantiated;

		// Get the list of placeholders before format_text.
		$input_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'input'));
		sort($input_placeholders);
		$feedback_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'feedback'));
		sort($feedback_placeholders);

		// Now format the question-text.
		$question_text = stack_maths::process_display_castext($question_text);

		//Add MathJax (Ensure MathJax is loaded)
		global $DIC;
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));

		//Inputs Replacement
		foreach ($input_placeholders as $name) {
			$field_name = 'xqcas_' . $question->getId() . '_' . $name . '_solution';

			//Matrix has a different syntax
			$state = $question->getInputState($name, $input_correct_array, false, false);

			//Input Placeholders
			$question_text = str_replace("[[input:{$name}]]",
				$question->inputs[$name]->render($state, $field_name, true, $input_correct_array),
				$question_text);
		}

		//Replace Validation placeholders
		foreach ($input_placeholders as $name) {
            if(is_a($question->inputs[$name],'stack_matrix_input')){
                $question_text = str_replace("[[validation:{$name}]]", "", $question_text);
            }else{
                $question_text = str_replace("[[validation:{$name}]]", "", $question_text);
                //error s() render validation
                //$question_text = str_replace("[[validation:{$name}]]", $question->inputs[$name]->render_validation($state, $name), $question_text);
            }
		}

		//Hide all feedback placeholders
		foreach ($feedback_placeholders as $prt_name) {
			$question_text = str_replace("[[feedback:{$prt_name}]]", '', $question_text);
		}

		//Validation
		$jsconfig = new stdClass();
		$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

		$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
		$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');

		return assStackQuestionUtils::_getLatex(stack_maxima_latex_tidy($question_text));
	}

	/**
	 * Renders the best solution for test results without instancing the question
	 * @param assStackQuestion $question
	 * @param string $active_id
	 * @param string $pass
	 * @return string
	 */
	public static function renderBestSolutionForTestResults(assStackQuestion $question, string $active_id, string $pass): string
	{
		$student_solutions = $question->getTestOutputSolutions($active_id, $pass);
		$question_text = $student_solutions['question_text'];

		// Get the list of placeholders before format_text.
		$input_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'input'));
		sort($input_placeholders);
		$feedback_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'feedback'));
		sort($feedback_placeholders);

		//Add MathJax (Ensure MathJax is loaded)
		global $DIC;
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));

		//Inputs Replacement
		foreach ($input_placeholders as $name) {

			$field_name = 'xqcas_' . $question->getId() . '_' . $name . '_solution';
			//Input Placeholders
			//#35924 $student_solutions['inputs'][$name]['correct_display'] being null
			if (is_string($student_solutions['inputs'][$name]['correct_display'])) {
				$student_solution_input = $student_solutions['inputs'][$name]['correct_display'];
			} else {
				$student_solution_input = "Error Rendering Input, question might be malformed";
			}
			$question_text = str_replace("[[input:{$name}]]", assStackQuestionUtils::_getLatex(stack_maxima_latex_tidy($student_solution_input)), $question_text);
		}

		//Replace Validation placeholders
		foreach ($input_placeholders as $name) {
			$question_text = str_replace("[[validation:{$name}]]", '', $question_text);
		}

		//Hide all feedback placeholders
		foreach ($feedback_placeholders as $prt_name) {
			$question_text = str_replace("[[feedback:{$prt_name}]]", '', $question_text);
		}

		//Validation
		$jsconfig = new stdClass();
		$jsconfig->validate_url = ilUtil::_getHttpPath() . "/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/validation.php";

		$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/assStackQuestion.js');
		$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.assStackQuestion.init(' . json_encode($jsconfig) . ',' . json_encode($question_text) . ')');
		// Now format the question-text.


		return stack_maths::process_display_castext($question_text);
	}

	/* OTHER RENDER METHODS BEGIN */

	/**
	 * Returns the button for current input field.
	 * @param string $question_id
	 * @param string $input_name
	 * @return string the HTML code of the button of validation for this input.
	 */
	public static function _renderValidationButton(string $question_id, string $input_name): string
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
    public static function renderPRTFeedback(stack_potentialresponse_tree_state $prt_state, $prt_name, $question): string	{
		$feedback = '';
		$feedback_bits = $prt_state->get_feedback();
		$feedback_array = array();

        $prt_info = $question->prts[$prt_name];

        // Add the student's responses, but only those needed by this prt.
        // Some irrelevant but invalid answers might break the CAS connection.
        $answers = $question->getUserResponse();
        //TODO REWORK
        /*
        foreach ($prt_info->get_required_variables(array_keys($answers)) as $name) {
            if (array_key_exists($name . '_val', $answers)) {
                $ans = $answers[$name . '_val'];
            } else {
                $ans = $answers[$name];
            }
            // Validating as teacher at this stage removes the problem of "allowWords" which
            // we don't have access to.  This effectively allows any words here.  But the
            // student's answer has already been through validation.
            $cs = stack_ast_container::make_from_teacher_source($ans, '', new stack_cas_security());
            // That all said, we then need to manually add in nouns to ensure these are protected.
            $cs->set_nounify(2);
            $cs->set_key($name);
            $cs->set_keyless(false);
            $prt_state->get_cas_context()->add_statement($cs);
        }*/

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
        }

        if ($question !== null and isset($prt_state->_score)) {
            $score = $prt_state->_score;
            if ($score == 1) {
                //
                //$feedback .= $question->prt_correct_instantiated . '<br>';
                $format = '2';
            } elseif ($score == 0) {
                //$feedback .= $question->prt_incorrect_instantiated . '<br>';
                $format = '3';
            } else {
                //$feedback .= $question->prt_partially_correct_instantiated . '<br>';
                $format = '1';
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

        if (is_string($feedback)) {
            return $feedback;
        } else {
            return '';
        }
	}

	/**
	 * Replaces the temporal placeholders for the feedback with the correct HTML
	 * @param string $feedback
	 * @return array|string|string[] HTML Stylized feedback
	 */
	protected static function replaceFeedbackPlaceHolders(string $feedback): string
	{
		//require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/model/configuration/class.assStackQuestionConfig.php');

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