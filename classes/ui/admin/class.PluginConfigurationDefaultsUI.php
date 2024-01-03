<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Section;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *  This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 *  originally created by Chris Sangwin.
 *
 *  The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "STACK Question" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/STACK
 *
 *  If you need support, please contact the maintainer of this software at:
 *  stack@surlabs.es
 *
 */
class PluginConfigurationDefaultsUI
{

    private static Factory $factory;
    private static ilCtrlInterface $control;

    /**
     * Shows the plugin configuration Maxima settings form
     */
    public static function show(array $data, ilPlugin $plugin_object): array
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$control = $DIC->ctrl();

        try {

            //control parameters
            self::$control->setParameterByClass(
                'ilassStackQuestionConfigGUI',
                'defaults',
                'saveDefaults'
            );

            //get sections
            $content = [
                'options' => self::getOptionsDefaultsSection($data, $plugin_object),
                'inputs' => self::getInputsDefaultsSection($data, $plugin_object)
            ];

        } catch (Exception $e) {
            $content = [self::$factory->messageBox()->failure($e->getMessage())];
        }

        return $content;
    }

    /**
     * Gets the defaults options section
     * @throws StackException
     */
    private static function getOptionsDefaultsSection(array $data, ilPlugin $plugin_object): Section
    {
        //Question level simplify
        if (isset($data["options_question_simplify"]) && $data["options_question_simplify"] == "1") {
            $options_question_level_simplify_value = true;
        } else {
            $options_question_level_simplify_value = false;
        }
        $options_question_level_simplify = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_question_simplify_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_question_simplify_description")
        )->withValue($options_question_level_simplify_value);

        //Assume positive
        if (isset($data["options_assume_positive"]) && $data["options_assume_positive"] == "1") {
            $options_assume_positive_value = true;
        } else {
            $options_assume_positive_value = false;
        }
        $options_assume_positive = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_assume_positive_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_assume_positive_description")
        )->withValue($options_assume_positive_value);

        //Assume real
        if (isset($data["options_assume_real"]) && $data["options_assume_real"] == "1") {
            $options_assume_real_value = true;
        } else {
            $options_assume_real_value = false;
        }

        $options_assume_real = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_assume_real_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_assume_real_description")
        )->withValue($options_assume_real_value);

        //Feedback for fully correct question
        if (isset($data["options_prt_correct"]) && is_string($data["options_prt_correct"])) {
            $options_feedback_fully_correct_value = $data["options_prt_correct"];
        } else {
            $options_feedback_fully_correct_value = $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_correct_default");
        }

        $options_feedback_fully_correct = self::$factory->input()->field()->textarea(
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_correct_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_correct_description")
        )->withValue($options_feedback_fully_correct_value);

        //Feedback for partially correct question
        if (isset($data["options_prt_partially_correct"]) && is_string($data["options_prt_partially_correct"])) {
            $options_feedback_partially_correct_value = $data["options_prt_partially_correct"];
        } else {
            $options_feedback_partially_correct_value = $plugin_object->txt("ui_admin_configuration_defaults_feedback_partially_correct_default");
        }

        $options_feedback_partially_correct = self::$factory->input()->field()->textarea(
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_partially_correct_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_partially_correct_description")
        )->withValue($options_feedback_partially_correct_value);

        //Feedback for fully incorrect question
        if (isset($data["options_prt_incorrect"]) && is_string($data["options_prt_incorrect"])) {
            $options_feedback_fully_incorrect_value = $data["options_prt_incorrect"];
        } else {
            $options_feedback_fully_incorrect_value = $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_incorrect_default");
        }

        $options_feedback_fully_incorrect = self::$factory->input()->field()->textarea(
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_incorrect_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_incorrect_description")
        )->withValue($options_feedback_fully_incorrect_value);

        //Multiplication sign
        $multiplication_sign_options = [
            "dot" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_dot"),
            "cross" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_cross"),
            "none" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_none")
        ];

        if (isset($data["options_multiplication_sign"]) && array_key_exists($data["options_multiplication_sign"], $multiplication_sign_options)) {
            $options_multiplication_sign_value = $data["options_multiplication_sign"];
        } else {
            throw new StackException("Invalid value for multiplication sign.");
        }

        $options_multiplication_sign = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_title"),
            $multiplication_sign_options,
            $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_description")
        )->withValue($options_multiplication_sign_value)->withRequired(true);

        //Surd for sqrt
        if (isset($data["options_sqrt_sign"]) && $data["options_sqrt_sign"] == "1") {
            $options_sqrt_sign_value = true;
        } else {
            $options_sqrt_sign_value = false;
        }

        $options_surd_for_sqrt = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_surd_for_sqrt_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_surd_for_sqrt_description")
        )->withValue($options_sqrt_sign_value);

        //Complex numbers
        $complex_numbers_options = [
            "i" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_i"),
            "j" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_j"),
            "symi" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_symi"),
            "symj" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_symj")
        ];

        if (isset($data["options_complex_numbers"]) && array_key_exists($data["options_complex_numbers"], $complex_numbers_options)) {
            $options_complex_numbers_value = $data["options_complex_numbers"];
        } else {
            throw new StackException("Invalid value for complex numbers.");
        }

        $options_complex_numbers = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_title"),
            $complex_numbers_options,
            $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_description")
        )->withValue($options_complex_numbers_value)->withRequired(true);

        //Inverse trigonometric functions
        $inverse_trigonometric_options = [
            "cos-1" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_cos"),
            "acos" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_acos"),
            "arccos" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_arccos"),
            "arccos-arcosh" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_arccos_arcosh")
        ];

        if (isset($data["options_inverse_trigonometric"]) && array_key_exists($data["options_inverse_trigonometric"], $inverse_trigonometric_options)) {
            $options_inverse_trigonometric_value = $data["options_inverse_trigonometric"];
        } else {
            throw new StackException("Invalid value for inverse trigonometric functions.");
        }

        $options_inverse_trigonometric = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_title"),
            $inverse_trigonometric_options,
            $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_description")
        )->withValue($options_inverse_trigonometric_value)->withRequired(true);

        //Logic symbols
        $logic_symbols_options = [
            "lang" => $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_lang"),
            "symbol" => $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_symbolic")
        ];

        if (isset($data["options_logic_symbol"]) && array_key_exists($data["options_logic_symbol"], $logic_symbols_options)) {
            $options_logic_symbols_value = $data["options_logic_symbol"];
        } else {
            throw new StackException("Invalid value for logic symbols");
        }

        $options_logic_symbols = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_title"),
            $logic_symbols_options,
            $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_description")
        )->withValue($options_logic_symbols_value)->withRequired(true);

        //Shape of Matrix Parentheses
        $matrix_parentheses_options = [
            '[' => '[',
            '(' => '(',
            '' => '',
            '{' => '{',
            '|' => '|',
        ];

        if (isset($data["options_matrix_parents"]) && array_key_exists($data["options_matrix_parents"], $matrix_parentheses_options)) {
            $options_matrix_parentheses_value = $data["options_matrix_parents"];
        } else {
            throw new StackException("Invalid value for matrix parentheses");
        }

        $options_matrix_parentheses = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_matrix_parentheses_title"),
            $matrix_parentheses_options,
            $plugin_object->txt("ui_admin_configuration_defaults_matrix_parentheses_description")
        )->withValue($options_matrix_parentheses_value)->withRequired(true);

        return self::$factory->input()->field()->section(
            [
                'options_question_level_simplify' => $options_question_level_simplify,
                'options_assume_positive' => $options_assume_positive,
                'options_assume_real' => $options_assume_real,
                'options_feedback_fully_correct' => $options_feedback_fully_correct,
                'options_feedback_partially_correct' => $options_feedback_partially_correct,
                'options_feedback_fully_incorrect' => $options_feedback_fully_incorrect,
                'options_multiplication_sign' => $options_multiplication_sign,
                'options_surd_for_sqrt' => $options_surd_for_sqrt,
                'options_complex_numbers' => $options_complex_numbers,
                'options_inverse_trigonometric' => $options_inverse_trigonometric,
                'options_logic_symbols' => $options_logic_symbols,
                'options_matrix_parentheses' => $options_matrix_parentheses
            ],
            $plugin_object->txt("ui_admin_configuration_defaults_options_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_options_description")
        );
    }

    /**
     * Gets the defaults input section
     */
    private static function getInputsDefaultsSection(array $data, ilPlugin $plugin_object): Section
    {

        //Default type of input
        $input_default_type_options = [
            'algebraic' => $plugin_object->txt('ui_admin_configuration_defaults_input_type_algebraic'),
            'textarea' => $plugin_object->txt('ui_admin_configuration_defaults_input_type_textarea'),
            'matrix' => $plugin_object->txt('ui_admin_configuration_defaults_input_type_matrix')
        ];

        if (isset($data["input_type"]) && array_key_exists($data["input_type"], $input_default_type_options)) {
            $default_input_options_value = $data["input_type"];
        } else {
            //Default value is algebraic
            $default_input_options_value = "algebraic";
        }

        $inputs_default_type = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_input_type_title"),
            $input_default_type_options,
            $plugin_object->txt("ui_admin_configuration_defaults_input_type_description")
        )->withValue($default_input_options_value)->withRequired(true);

        //Box size
        $input_box_size_value = (string)$data["input_box_size"] ?? "15";

        $input_box_size = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_defaults_input_box_size_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_box_size_description")
        )->withValue($input_box_size_value);

        //Strict syntax
        if (isset($data["input_strict_syntax"]) && $data["input_strict_syntax"] == "1") {
            $input_strict_syntax_value = true;
        } else {
            $input_strict_syntax_value = false;
        }

        $input_strict_syntax = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_strict_syntax_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_strict_syntax_description")
        )->withValue($input_strict_syntax_value);

        //Insert stars
        $input_insert_stars_options = [
            '0' => $plugin_object->txt('ui_admin_configuration_defaults_insert_star_options_0'),
            '1' => $plugin_object->txt('ui_admin_configuration_defaults_insert_star_options_1'),
            '2' => $plugin_object->txt('ui_admin_configuration_defaults_insert_star_options_2'),
            '3' => $plugin_object->txt('ui_admin_configuration_defaults_insert_star_options_3'),
            '4' => $plugin_object->txt('ui_admin_configuration_defaults_insert_star_options_4'),
            '5' => $plugin_object->txt('ui_admin_configuration_defaults_insert_star_options_5')
        ];

        $input_insert_stars = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_input_insert_stars_title"),
            $input_insert_stars_options,
            $plugin_object->txt("ui_admin_configuration_defaults_input_insert_stars_description")
        )->withValue(true)->withRequired(true);

        //Forbidden words
        $input_forbidden_words_value = $data["input_forbidden_words"] ?? "";

        $input_forbidden_words = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbidden_words_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbidden_words_description")
        )->withValue($input_forbidden_words_value);

        //Forbid floats
        if (isset($data["input_forbid_float"]) && $data["input_forbid_float"] == "1") {
            $input_forbid_float_value = true;
        } else {
            $input_forbid_float_value = false;
        }

        $input_forbid_float = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbid_float_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbid_float_description")
        )->withValue($input_forbid_float_value);

        //Require the lowest terms
        if (isset($data["input_require_lowest_terms"]) && $data["input_require_lowest_terms"] == "1") {
            $input_require_lowest_terms_value = true;
        } else {
            $input_require_lowest_terms_value = false;
        }

        $input_require_lowest_terms = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_require_lowest_terms_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_require_lowest_terms_description")
        )->withValue($input_require_lowest_terms_value);

        //Check answer type
        if (isset($data["input_check_answer_type"]) && $data["input_check_answer_type"] == "1") {
            $input_check_answer_type_value = true;
        } else {
            $input_check_answer_type_value = false;
        }

        $input_check_answer_type = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_check_answer_type_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_check_answer_type_description")
        )->withValue($input_check_answer_type_value);

        //Input must verify
        if (isset($data["input_must_verify"]) && $data["input_must_verify"] == "1") {
            $input_must_verify_value = true;
        } else {
            $input_must_verify_value = false;
        }

        $input_must_verify_type = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_must_verify_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_must_verify_description")
        )->withValue($input_must_verify_value);

        //Input show validation
        $input_show_validation_options = [
            '0' => $plugin_object->txt('ui_admin_configuration_defaults_input_show_validation_options_0'),
            '1' => $plugin_object->txt('ui_admin_configuration_defaults_input_show_validation_options_1'),
            '2' => $plugin_object->txt('ui_admin_configuration_defaults_input_show_validation_options_2'),
            '3' => $plugin_object->txt('ui_admin_configuration_defaults_input_show_validation_options_3'),
        ];

        if (isset($data["input_show_validation"]) && array_key_exists($data["input_show_validation"], $input_show_validation_options)) {
            $input_show_validation_value = $data["input_show_validation"];
        } else {
            //Default value is algebraic
            $input_show_validation_value = "algebraic";
        }

        $input_show_validation = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_input_show_validation_title"),
            $input_show_validation_options,
            $plugin_object->txt("ui_admin_configuration_defaults_input_show_validation_description")
        )->withValue($input_show_validation_value)->withRequired(true);

        return self::$factory->input()->field()->section(
            [
                'default_type' => $inputs_default_type,
                'box_size' => $input_box_size,
                'strict_syntax' => $input_strict_syntax,
                'insert_stars' => $input_insert_stars,
                'forbidden_words' => $input_forbidden_words,
                'forbid_float' => $input_forbid_float,
                'require_lowest_terms' => $input_require_lowest_terms,
                'check_answer_type' => $input_check_answer_type,
                'must_verify' => $input_must_verify_type,
                'show_validation' => $input_show_validation
            ],
            $plugin_object->txt("ui_admin_configuration_defaults_inputs_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_inputs_description")
        );
    }
}