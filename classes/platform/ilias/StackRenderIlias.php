<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use castext2_default_processor;
use classes\platform\StackException;
use classes\platform\StackRender;
use classes\platform\StackUserResponse;
use stack_maths;
use stack_utils;


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

    private string $purpose;

    public function __construct(string $purpose)
    {
        $this->setPurpose($purpose);
    }

    /**
     *
     * @throws StackException
     */
    public function render($question): string
    {

        $response = [];

        // We need to provide a processor for the CASText2 post-processing,
        // basically for targetting pluginfiles
        $question->setCasTextProcessor(new castext2_default_processor());

        if (is_string($question->question_text_instantiated)) {
            // The question has not been instantiated successfully, at this level it is likely
            // a failure at compilation and that means invalid teacher code.
            return $question->question_text_instantiated;
        }

        $question_text = $question->question_text_instantiated->get_rendered($question->castextprocessor);

        // Replace inputs.
        $inputs_to_validate = array();

        // Get the list of placeholders before format_text.
        $original_input_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'input'));
        sort($original_input_placeholders);
        $original_feedback_placeholders = array_unique(stack_utils::extract_placeholders($question_text, 'feedback'));
        sort($original_feedback_placeholders);

        // Now format the question_text.
        $question_text = $question->format_text(
            stack_maths::process_display_castext($question_text, $this),
            FORMAT_HTML, // All CASText2 processed content has already been formatted to HTML.
            $qa, 'question', 'question_text', $question->id);

        // Get the list of placeholders after format_text.
        $formatedinputplaceholders = stack_utils::extract_placeholders($question_text, 'input');
        sort($formatedinputplaceholders);
        $formatedfeedbackplaceholders = stack_utils::extract_placeholders($question_text, 'feedback');
        sort($formatedfeedbackplaceholders);

        // We need to check that if the list has changed.
        // Have we lost some of the placeholders entirely?
        // Duplicates may have been removed by multi-lang,
        // No duplicates should remain.
        if ($formatedinputplaceholders !== $original_input_placeholders ||
            $formatedfeedbackplaceholders !== $originalfeedbackplaceholders) {
            throw new coding_exception('Inconsistent placeholders. Possibly due to multi-lang filtter not being active.');
        }

        foreach ($question->inputs as $name => $input) {
            // Get the actual value of the teacher's answer at this point.
            $tavalue = $question->get_ta_for_input($name);

            $fieldname = $qa->get_qt_field_name($name);
            $state = $question->get_input_state($name, $response);

            $question_text = str_replace("[[input:{$name}]]",
                $input->render($state, $fieldname, $options->readonly, $tavalue),
                $question_text);

            $question_text = $input->replace_validation_tags($state, $fieldname, $question_text);

            if ($input->requires_validation()) {
                $inputs_to_validate[] = $name;
            }
        }

        // Replace PRTs.
        foreach ($question->prts as $index => $prt) {
            $feedback = '';
            if ($options->feedback) {
                $feedback = $this->prt_feedback($index, $response, $qa, $options, $prt->get_feedbackstyle());

            } else if (in_array($qa->get_behaviour_name(), array('interactivecountback', 'adaptivemulipart'))) {
                // The behaviour name test here is a hack. The trouble is that interactive
                // behaviour or adaptivemulipart does not show feedback if the input
                // is invalid, but we want to show the CAS errors from the PRT.
                $result = $question->get_prt_result($index, $response, $qa->get_state()->is_finished());
                $errors = implode(' ', $result->get_errors());
                $feedback = html_writer::nonempty_tag('span', $errors,
                    array('class' => 'stackprtfeedback stackprtfeedback-' . $name));
            }
            $question_text = str_replace("[[feedback:{$index}]]", $feedback, $question_text);
        }

        // Initialise automatic validation, if enabled.
        if (stack_utils::get_config()->ajaxvalidation) {
            // Once we cen rely on everyone being on a Moodle version that includes the fix for
            // MDL-65029 (3.5.6+, 3.6.4+, 3.7+) we can remove this if and just call the method.
            if (method_exists($qa, 'get_outer_question_div_unique_id')) {
                $questiondivid = $qa->get_outer_question_div_unique_id();
            } else {
                $questiondivid = 'q' . $qa->get_slot();
            }
            $this->page->requires->js_call_amd('qtype_stack/input', 'initInputs',
                [$questiondivid, $qa->get_field_prefix(),
                    $qa->get_database_id(), $inputs_to_validate]);
        }

        $result = '';
        $result .= $this->question_tests_link($question, $options) . $question_text;

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('span',
                $question->get_validation_error($response),
                array('class' => 'validationerror'));
        }

        return $result;
        switch ($this->getPurpose()) {
            case 'ilias_question_text':
                return $this->composeIliasQuestionText();
            case 'ilias_specific_feedback':
                return $this->composeIliasSpecificFeedback();
            default:
                throw new StackException('Invalid purpose selected: ' . $this->getPurpose() . '.');
        }
    }

    private function composeIliasQuestionText(): string
    {
        return 'composeIliasQuestionText';
    }

    private function composeIliasSpecificFeedback(): string
    {
        return 'composeIliasSpecificFeedback';
    }


    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): void
    {
        $this->purpose = $purpose;
    }
}