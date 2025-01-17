<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use assStackQuestion;
use assStackQuestionUtils;
use classes\platform\StackConfig;
use classes\platform\StackEvaluation;
use classes\platform\StackException;
use classes\platform\StackRender;
use ilObjStyleSheet;
use ilSetting;
use ilUtil;
use stack_exception;
use stack_maths;
use stack_utils;
use castext2_default_processor;
use stdClass;
use Expand;

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
class StackRenderIlias extends StackRender
{

    /**
     * Generates the HTML for the feedback of a specific potential response tree.
     * @param array $attempt_data
     * @param array $display_options
     * @return string
     * @throws StackException|stack_exception
     */
    public static function renderPRTFeedback(array $attempt_data, array $display_options): string
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $language = $DIC->language();

        $prt_name = $attempt_data['prt_name'];

        $response = $attempt_data['response'];
        if (!is_array($response)) {
            throw new StackException('Invalid response type.');
        }

        $question = $attempt_data['question'];
        if (!($question instanceof assStackQuestion)) {
            throw new StackException('Invalid question type.');
        }

        $result = $question->getPrtResult($prt_name, $response, true);

        $error_message = '';
        if ($result->get_errors()) {
            $error_message = stack_string('prtruntimeerror', array('prt' => $prt_name, 'error' => implode('</br>', $result->get_errors())));
            $error_message = $renderer->render($factory->messageBox()->failure($error_message));
        }

        $feedback = $result->get_feedback($question->getCasTextProcessor());

        $feedback = stack_maths::process_display_castext($feedback);

        if (!$result->is_evaluated()) {
            if ($question->isAnyInputBlank($response)) {
                return $renderer->render($factory->messageBox()->failure($language->txt('qpl_qst_xqcas_error_inputs_missing')));
            }
        }

        $state = StackEvaluation::stateForFraction($result->get_score());

        $prt_feedback_instantiated = match ($state) {
            'incorrect' => $question->prt_incorrect_instantiated->get_rendered($question->getCasTextProcessor()),
            'partially_correct' => $question->prt_partially_correct_instantiated->get_rendered($question->getCasTextProcessor()),
            'correct' => $question->prt_correct_instantiated->get_rendered($question->getCasTextProcessor()),
            default => throw new StackException('Invalid state.'),
        };

        if (trim($feedback) === '') {
            $feedback = $prt_feedback_instantiated;
        } elseif (trim($prt_feedback_instantiated) !== '' && trim($prt_feedback_instantiated) !== "<p></p>\n<p></p>") {
            $feedback = $prt_feedback_instantiated . '</br>' . $feedback;
        }

        if (trim($feedback) === '') {
            return '';
        }

        $notes = $result->get_answernotes();

        $result_note = explode("-", end($notes));

        $style_id = "";

        $result_note[0] = strpos($result_note[0], "prt") === 0 ? $result_note[0] : "prt" . $result_note[0];

        if (isset($question->prts[$result_note[0]])) {
            $nodes = $question->prts[$result_note[0]]->get_nodes();

            if (isset($nodes[(int) $result_note[1]])) {
                $node = $nodes[(int) $result_note[1]];

                $feedbackformat = $result_note[2] == "F" ? $node->falsefeedbackformat : $node->truefeedbackformat;

                if ((int) $feedbackformat > 0) {
                    $stylesheet_id = assStackQuestionUtils::_getActiveContentStyleId();
                    if (!empty($stylesheet_id)) {
                        $DIC->globalScreen()->layout()->meta()->addCss(ilObjStyleSheet::getContentStylePath((int)$stylesheet_id));
                    }

                    $style_id = StackConfig::get("feedback_styles_style_" . $feedbackformat);
                }
            }
        }

        if (!empty($style_id)) {
            return "<div class='ilc_section_$style_id'>" . assStackQuestionUtils::_getLatex($feedback) . '</div>';
        } else {
            $standard_prt_feedback = match ($state) {
                'incorrect' => $factory->messageBox()->failure(assStackQuestionUtils::_getLatex($feedback)),
                'partially_correct' => $factory->messageBox()->info(assStackQuestionUtils::_getLatex($feedback)),
                'correct' => $factory->messageBox()->success(assStackQuestionUtils::_getLatex($feedback)),
                default => throw new StackException('Invalid state.'),
            };

            return $renderer->render($standard_prt_feedback) . '</br>' . $error_message;
        }
    }

    /**
     * Generates the HTML for the question.
     * @param array $attempt_data
     * @param array $display_options
     * @return string
     * @throws StackException|stack_exception
     */
    public static function renderQuestion(array $attempt_data, array $display_options): string
    {
        global $DIC;

        $response = $attempt_data['response'];
        if (!is_array($response)) {
            throw new StackException('Invalid response type.');
        }

        $question = $attempt_data['question'];
        if (!($question instanceof assStackQuestion)) {
            throw new StackException('Invalid question type.');
        }

        $show_correct_solution = $display_options['show_correct_solution'] ?? false;

        $instant_validation = StackConfig::getAll()["instant_validation"];

        // We need to provide a processor for the CASText2 post-processing,
        // basically for targeting plugin files
        $question->setCasTextProcessor(new castext2_default_processor());

        $question_text = $question->question_text_instantiated->get_rendered($question->getCasTextProcessor());

        // Replace inputs.
        //TODO: INPUT REQUIRES VALIDATION
        $inputs_to_validate = array();

        // Get the list of placeholders before format_text.
        $original_input_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'input'));
        sort($original_input_placeholders);
        $original_feedback_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'feedback'));
        sort($original_feedback_placeholders);

        // Now format the question_text.
        $question_text = stack_maths::process_display_castext($question_text);

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
            throw new StackException('Inconsistent placeholders. Possibly due to multi-lang filtter not being active.');
        }

        foreach ($formatted_input_placeholders as $input_name) {

            if (isset($question->inputs[$input_name])) {
                $input = $question->inputs[$input_name];
            } else {
                throw new StackException('Input ' . $input_name . 'not found.');
            }

            // Get the actual value of the teacher's answer at this point.
            $teacher_answer_value = $question->getTeacherAnswerForInput($input_name);

            //Do not show validation in some inputs
            $validation_button = '';
            $validation_rendered = '';
            if (($input->get_parameter('showValidation') != 0)) {
                if (!is_a($input, 'stack_radio_input') &&
                    !is_a($input, 'stack_dropdown_input') &&
                    !is_a($input, 'stack_checkbox_input') &&
                    !is_a($input, 'stack_boolean_input')
                ) {
                    if (!$instant_validation && !$show_correct_solution) {
                        $validation_button = self::_renderValidationButton((int)$question->getId(), $input_name);
                    }
                    $validation_rendered = $response[$input_name . '_validation'] ?? '';
                }
            }

            $field_name = 'xqcas_' . $question->getId() . '_' . $input_name;
            $state = $question->getInputState($input_name, $response);

            $question_text = str_replace("[[input:$input_name]]",
                $input->render($state, $field_name, $show_correct_solution, $teacher_answer_value)." ".$validation_button,
                $question_text);

            $ilias_validation = "";

            //Validation Placeholders
            if (!$show_correct_solution) {
                if (is_a($input, 'stack_matrix_input')) {
                    $ilias_validation = '<div class="xqcas_input_validation">
                        <div id="validation_xqcas_' . $question->getId() . '_' . $input_name . '">' . $validation_rendered. '</div>
                    </div>'.
                        '<div id="xqcas_input_matrix_width_' . $input_name . '" style="visibility: hidden">' . $input->getWidth() . '</div>
                    <div id="xqcas_input_matrix_height_' . $input_name . '" style="visibility: hidden">' . $input->getHeight() . '</div>';
                } else {
                    $ilias_validation = '<div class="xqcas_input_validation">
                        <div id="validation_xqcas_' . $question->getId() . '_' . $input_name . '">' . $validation_rendered. '</div>
                    </div>';
                }
            } else {
                if (StackConfig::get("correct_solution_in_validation") == "1") {
                    $ilias_validation = "<div style='padding: 20px; margin-bottom: 5px'>{$question->getTeacherAnswerDisplay($input_name)}</div>";
                }
            }

            $question_text = $input->replace_validation_tags($state, $field_name, $question_text, $ilias_validation);

            if ($input->requires_validation()) {
                $inputs_to_validate[] = $input_name;
            }

        }

        // Replace PRTs.
        foreach ($formatted_feedback_placeholders as $prt_name) {
            if (!isset($question->prts[$prt_name])) {
                throw new StackException('PRT ' . $prt_name . 'not found.');
            }
            $prt = $question->prts[$prt_name];
            $feedback = '';
            if (!$show_correct_solution || ($_GET["cmd"] == "post" && $_GET["fallbackCmd"] == "print")) {
                if ($display_options['feedback'] && !empty($response)) {
                    $attempt_data['prt_name'] = $prt->get_name();
                    $feedback = self::renderPRTFeedback($attempt_data, $display_options);
                }
            }
            $question_text = str_replace("[[feedback:$prt_name]]", $feedback, $question_text);
        }

        // Ensure that the MathJax library is loaded.
        self::ensureMathJaxLoaded();
        //TODO: VALIDATION
        // Se usa $inputs_to_validate

        //Validation
        $jsconfig = new stdClass();

        $DIC->globalScreen()->layout()->meta()->addCss('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/css/styles.css');

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

        return $question_text;
    }

    /**
     * Generates the HTML for specific feedback section.
     * @param array $attempt_data
     * @param array $display_options
     * @return string
     * @throws StackException|stack_exception
     */
    public static function renderSpecificFeedback(array $attempt_data, array $display_options): string
    {
        $response = $attempt_data['response'];
        if (!is_array($response)) {
            throw new StackException('Invalid response type.');
        }

        $question = $attempt_data['question'];
        if (!($question instanceof assStackQuestion)) {
            throw new StackException('Invalid question type.');
        }

        if ($question->specific_feedback_instantiated === null) {
            // Invalid question, otherwise this would be here.
            return '';
        }

        $feedback_text = $question->specific_feedback_instantiated->get_rendered($question->getCasTextProcessor());
        if (!$feedback_text) {
            return '';
        }
        // Get the list of placeholders before format_text.
        $original_feedback_placeholders = array_unique(stack_utils::extract_placeholders($feedback_text, 'feedback'));
        sort($original_feedback_placeholders);

        // Now format the question_text.
        $feedback_text = stack_maths::process_display_castext($feedback_text);

        // Get the list of placeholders after format_text.
        $formatted_feedback_placeholders = stack_utils::extract_placeholders($feedback_text, 'feedback');
        sort($formatted_feedback_placeholders);

        // We need to check that if the list has changed.
        // Have we lost some of the placeholders entirely?
        // Duplicates may have been removed by multi-lang,
        // No duplicates should remain.
        if ($formatted_feedback_placeholders !== $original_feedback_placeholders) {
            throw new StackException('Inconsistent placeholders. Possibly due to multi-lang filtter not being active.');
        }

        $feedback_text = stack_maths::process_display_castext($feedback_text);

        //TODO: OVERALL FEEDBACK
        /*
        $individualfeedback = count($question->prts) == 1;
        if ($individualfeedback) {
            $overallfeedback = '';
        } else {
            $overallfeedback = $this->overall_standard_prt_feedback($qa, $question, $response);
        }*/

        // Replace PRTs.
        foreach ($formatted_feedback_placeholders as $prt_name) {
            if (!isset($question->prts[$prt_name])) {
                throw new StackException('PRT ' . $prt_name . 'not found.');
            }
            $feedback = '';
            if (!empty($response)) {
                $attempt_data['prt_name'] = $prt_name;
                $feedback = self::renderPRTFeedback($attempt_data, $display_options);
            }
            $feedback_text = str_replace("[[feedback:$prt_name]]", $feedback, $feedback_text);
        }

        //TODO: OVERALL FEEDBACK

        // Ensure that the MathJax library is loaded.
        self::ensureMathJaxLoaded();
        return $feedback_text;
    }

    /**
     * Generates the HTML for the general feedback section.
     * @param array $attempt_data
     * @param array $display_options
     * @return string
     * @throws StackException
     */
    public static function renderGeneralFeedback(array $attempt_data, array $display_options): string
    {
        $question = $attempt_data['question'];
        if (!($question instanceof assStackQuestion)) {
            throw new StackException('Invalid question type.');
        }

        if ($question->general_feedback_instantiated === null) {
            throw new StackException('General feedback not set.');
        }

        $general_feedback_text = $question->general_feedback_instantiated->get_rendered($question->getCasTextProcessor());

        if (!$general_feedback_text) {
            $general_feedback_text = '';
        }

        $general_feedback_text = stack_maths::process_display_castext($general_feedback_text);

        if (!StackConfig::get("correct_solution_in_validation") == "1") {
            $general_feedback_text .= $question->formatCorrectResponse();
        }

        // Ensure that the MathJax library is loaded.
        self::ensureMathJaxLoaded();
        return $general_feedback_text;
    }

    /**
     * Ensure that the MathJax library is loaded.
     */
    public static function ensureMathJaxLoaded(): void
    {
        global $DIC;
        $mathjax = new ilSetting("MathJax");
        $DIC->globalScreen()->layout()->meta()->addJs($mathjax->get("path_to_mathjax"));
    }

    /**
     * Returns the button for current input field.
     * @param string $question_id
     * @param string $input_name
     * @return string the HTML code of the button of validation for this input.
     */
    public static function _renderValidationButton(int $question_id, string $input_name): string
    {
        return "<button class=\"xqcas btn btn-default\" name=\"cmd[xqcas_" . $question_id . '_' . $input_name . "]\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span></button>";
    }

    public static function renderQuestionVariables(array $randomisation_data): string
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $language = $DIC->language();

        $active_variant_question_variables = "Not found";
        $active_variant_feedback_variables = "Not found";

        if (isset($randomisation_data[""])) {
            $active_variant_question_variables = $randomisation_data[""]["question_variables"];
            $active_variant_feedback_variables = $randomisation_data[""]["feedback_variables"];
        }

        $randomisation = "";

        if (trim($active_variant_question_variables) != "") {
            $randomisation = "<strong>" . $language->txt("qpl_qst_xqcas_debug_info_question_variables") . "</strong>";
            $randomisation .= assStackQuestionUtils::parseToHTMLWithoutLatex($active_variant_question_variables);
        }

        if (trim($active_variant_feedback_variables) != "") {
            if ($randomisation != "") {
                $randomisation .= $renderer->render($factory->divider()->horizontal());
            }

            $randomisation .= "<strong>" . $language->txt("qpl_qst_xqcas_debug_info_feedback_variables") . "</strong>";
            $randomisation .= assStackQuestionUtils::parseToHTMLWithoutLatex($active_variant_feedback_variables);
        }

        if ($randomisation != "") {
            if (isset($randomisation_data[""]["seed"])) {
                $randomisation .= $renderer->render($factory->divider()->horizontal()) . "<strong>Seed: </strong>" . $randomisation_data[""]["seed"];
            }

            $panel = $factory->panel()->standard($language->txt("qpl_qst_xqcas_debug_info_message"), $factory->legacy(
                $randomisation
            ))->withViewControls(array(
                new Expand(),
            ));

            return $renderer->render($panel);
        } else  {
            return "";
        }
    }
}