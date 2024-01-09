<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use assStackQuestion;
use classes\platform\StackEvaluation;
use classes\platform\StackException;
use classes\platform\StackRender;
use ilSetting;
use stack_exception;
use stack_maths;
use stack_utils;
use castext2_default_processor;

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
            $error_message = stack_string('prtruntimeerror',
                array('prt' => $prt_name, 'error' => implode(' ', $result->get_errors())));
        }

        $feedback = $result->get_feedback($question->getCasTextProcessor());
        // The feedback does not come as bits anymore the whole thing is concatenated in CAS
        // and CASText converts any formats to HTML already, plugin files as well.
        $feedback = stack_maths::process_display_castext($feedback);

        //ILIAS: NO GRADING DETAILS

        if (!$result->is_evaluated()) {
            throw new StackException('PRT ' . $prt_name . 'not evaluated.');
        }
        // Don't give standard feedback when we have errors.
        if (count($result->get_errors()) != 0) {
            throw new StackException('PRT' . $prt_name . ' has errors.');
        }

        $state = StackEvaluation::stateForFraction($result->get_fraction());

        // TODO: Compact and symbolic only.
        //if ($display_options['feedback_style'] === 2 || $display_options['feedback_style'] === 3) {
        //$s = get_string('symbolicprt' . $class . 'feedback', 'qtype_stack');
        //return html_writer::tag('span', $s, array('class' => $class));
        //}

        switch ($state) {
            case -1:
                // Incorrect.
                $prt_feedback_instantiated = $question->prt_incorrect_instantiated;
                break;
            case 0:
                // Partially correct.
                $prt_feedback_instantiated = $question->prt_partially_correct_instantiated;
                break;
            case 1:
                // Correct.
                $prt_feedback_instantiated = $question->prt_correct_instantiated;
                break;
            default:
                throw new StackException('Invalid state.');
        }

        $standard_prt_feedback = stack_maths::process_display_castext(
            $prt_feedback_instantiated->get_rendered($question->getCasTextProcessor())
        );

        //$tag = 'div';
        $prt_feedback_html = '';
        switch ($display_options['feedback_style']) {
            case 0:
                // Formative PRT.
                $prt_feedback_html = $error_message . $feedback;
                break;
            case 1:
                $prt_feedback_html = $standard_prt_feedback . $error_message . $feedback;
                break;
            case 2:
                // Compact.
                $prt_feedback_html = $standard_prt_feedback . $error_message . $feedback;
                //$tag = 'span';
                break;
            case 3:
                // Symbolic.
                $prt_feedback_html = $standard_prt_feedback . $error_message;
                //$tag = 'span';
                break;
            default:
                echo "i is not equal to 0, 1 or 2";
        }

        return $prt_feedback_html;
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

        $response = $attempt_data['response'];
        if (!is_array($response)) {
            throw new StackException('Invalid response type.');
        }

        $question = $attempt_data['question'];
        if (!($question instanceof assStackQuestion)) {
            throw new StackException('Invalid question type.');
        }

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

        foreach ($question->inputs as $input_name => $input) {
            // Get the actual value of the teacher's answer at this point.
            $teacher_answer_value = $question->getTeacherAnswerForInput($input_name);

            $field_name = 'xqcas_' . $question->getId() . '_' . $input_name;
            $state = $question->getInputState($input_name, $response);

            $question_text = str_replace("[[input:$input_name]]",
                $input->render($state, $field_name, $display_options['readonly'], $teacher_answer_value),
                $question_text);

            $question_text = $input->replace_validation_tags($state, $field_name, $question_text);

            if ($input->requires_validation()) {
                //TODO: INPUT REQUIRES VALIDATION
                $inputs_to_validate[] = $input_name;
            }
        }

        // Replace PRTs.
        foreach ($question->prts as $prt_name => $prt) {
            $feedback = '';
            if ($display_options['feedback']) {
                $attempt_data['prt_name'] = $prt->get_name();
                $feedback = self::renderPRTFeedback($attempt_data, $display_options);
            }
            $question_text = str_replace("[[feedback:$prt_name]]", $feedback, $question_text);
        }

        // Ensure that the MathJax library is loaded.
        self::ensureMathJaxLoaded();
        //TODO: VALIDATION
        // Se usa $inputs_to_validate

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

        //var_dump($question->specific_feedback_instantiated->get_rendered());exit;

        if ($question->specific_feedback_instantiated === null) {
            // Invalid question, otherwise this would be here.
            return '';
        }

        $feedback_text = $question->specific_feedback_instantiated->get_rendered($question->getCasTextProcessor());
        if (!$feedback_text) {
            return '';
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

        // Replace any PRT feedback.
        $all_empty = true;
        foreach ($question->prts as $prt_name => $prt) {
            $feedback = '';
            if ($display_options['feedback']) {
                $attempt_data['prt_name'] = $prt->get_name();
                $feedback = self::renderPRTFeedback($attempt_data, $display_options);
                $all_empty = $all_empty && !$feedback;
            }
            $feedback_text = str_replace("[[feedback:$prt_name]]", $feedback, $feedback_text);
        }

        //TODO: OVERALL FEEDBACK
        if ($all_empty) {
            return '';
        }

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
            return '';
        }

        $general_feedback_text = stack_maths::process_display_castext($general_feedback_text);

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


}