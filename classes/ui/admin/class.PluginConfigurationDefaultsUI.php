<?php
declare(strict_types=1);

use classes\platform\StackConfig;
use classes\platform\StackDatabase;
use classes\platform\StackException;
use ILIAS\UI\Component\Input\Field\Group;
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
    private static ilCtrl $control;

    /**
     * Shows the plugin configuration Maxima settings form
     * @throws StackException|ilCtrlException
     */
    public static function show(array $data, ilPlugin $plugin_object): array
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$control = $DIC->ctrl();

        //control parameters
        self::$control->setParameterByClass(
            'ilassStackQuestionConfigGUI',
            'defaults',
            'saveDefaults'
        );

        //get sections
        $content = [
            'options' => self::getOptionsDefaultsSection($data, $plugin_object),
            'inputs' => self::getInputsDefaultsSection($data, $plugin_object),
            'feedback_styles' => self::getFeedbackStylesDefaultsSection($data, $plugin_object)
        ];


        return $content;
    }

    private static function getOptionsDefaultsSection(array $data, ilPlugin $plugin_object): Section
    {
        global $DIC;
        //Question level simplify
        $options_question_level_simplify = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_question_simplify_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_question_simplify_description")
        )->withValue($data["options_question_simplify"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_question_simplify', $v ? "1" : "0", "options");
                }
            ));

        //Assume positive
        $options_assume_positive = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_assume_positive_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_assume_positive_description")
        )->withValue($data["options_assume_positive"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_assume_positive', $v ? "1" : "0", "options");
                }
            ));

        //Assume real
        $options_assume_real = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_assume_real_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_assume_real_description")
        )->withValue($data["options_assume_real"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_assume_real', $v ? "1" : "0", "options");
                }
            ));

        //Feedback for fully correct question
        $default_value = $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_correct_default");
        $options_feedback_fully_correct = self::$factory->input()->field()->textarea(
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_correct_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_correct_description")
        )->withValue($data["feedback_fully_correct"] ?: $default_value)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    global $DIC;
                    $default_value = $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_defaults_feedback_fully_correct_default");
                    StackConfig::set('feedback_fully_correct', $v ?: $default_value, "options");
                }
            ));

        //Feedback for partially correct question
        $default_value = $plugin_object->txt("ui_admin_configuration_defaults_feedback_partially_correct_default");
        $options_feedback_partially_correct = self::$factory->input()->field()->textarea(
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_partially_correct_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_partially_correct_description")
        )->withValue($data["feedback_partially_correct"] ?: $default_value)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    global $DIC;
                    $default_value = $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_defaults_feedback_partially_correct_default");
                    StackConfig::set('feedback_partially_correct', $v ?: $default_value, "options");
                }
            ));

        //Feedback for fully incorrect question
        $default_value = $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_incorrect_default");
        $options_feedback_fully_incorrect = self::$factory->input()->field()->textarea(
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_incorrect_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_feedback_fully_incorrect_description")
        )->withValue($data["feedback_fully_incorrect"] ?: $default_value)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    global $DIC;
                    $default_value = $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_defaults_feedback_fully_incorrect_default");
                    StackConfig::set('feedback_fully_incorrect', $v ?: $default_value, "options");
                }
            ));

        //Multiplication sign
        $multiplication_sign_options = [
            "dot" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_dot"),
            "cross" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_cross"),
            "none" => $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_none")
        ];

        $options_multiplication_sign = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_title"),
            $multiplication_sign_options,
            $plugin_object->txt("ui_admin_configuration_defaults_multiplication_sign_description")
        )->withValue($data["multiplication_sign"] ?: "dot")->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('multiplication_sign', $v, "options");
                }
            ));

        //Surd for sqrt
        $options_surd_for_sqrt = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_surd_for_sqrt_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_surd_for_sqrt_description")
        )->withValue($data["options_sqrt_sign"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_sqrt_sign', $v ? "1" : "0", "options");
                }
            ));

        //Complex numbers
        $complex_numbers_options = [
            "i" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_i"),
            "j" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_j"),
            "symi" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_symi"),
            "symj" => $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_symj")
        ];

        $options_complex_numbers = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_title"),
            $complex_numbers_options,
            $plugin_object->txt("ui_admin_configuration_defaults_complex_numbers_description")
        )->withValue($data["options_complex_numbers"] ?: "i")->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_complex_numbers', $v, "options");
                }
            ));

        //Inverse trigonometric functions
        $inverse_trigonometric_options = [
            "cos-1" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_cos"),
            "acos" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_acos"),
            "arccos" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_arccos"),
            "arccos-arcosh" => $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_arccos_arcosh")
        ];

        $options_inverse_trigonometric = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_title"),
            $inverse_trigonometric_options,
            $plugin_object->txt("ui_admin_configuration_defaults_inverse_trigonometric_description")
        )->withValue($data["options_inverse_trigonometric"] ?: "cos-1")->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_inverse_trigonometric', $v, "options");
                }
            ));

        //Logic symbols
        $logic_symbols_options = [
            "lang" => $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_lang"),
            "symbol" => $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_symbolic")
        ];

        $options_logic_symbols = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_title"),
            $logic_symbols_options,
            $plugin_object->txt("ui_admin_configuration_defaults_logic_symbols_description")
        )->withValue($data["options_logic_symbol"] ?: "lang")->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_logic_symbol', $v, "options");
                }
            ));

        //Shape of Matrix Parentheses
        $matrix_parentheses_options = [
            '[' => '[',
            '(' => '(',
            ' ' => '',
            '{' => '{',
            '|' => '|',
        ];

        $options_matrix_parentheses = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_matrix_parentheses_title"),
            $matrix_parentheses_options,
            $plugin_object->txt("ui_admin_configuration_defaults_matrix_parentheses_description")
        )->withValue($data["options_matrix_parents"] ?: "[")->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('options_matrix_parents', $v, "options");
                }
            ));

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
        global $DIC;

        //Default type of input
        $input_default_type_options = [
            'algebraic' => $plugin_object->txt('ui_admin_configuration_defaults_input_type_algebraic'),
            'textarea' => $plugin_object->txt('ui_admin_configuration_defaults_input_type_textarea'),
            'matrix' => $plugin_object->txt('ui_admin_configuration_defaults_input_type_matrix')
        ];

        $inputs_default_type = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_defaults_input_type_title"),
            $input_default_type_options,
            $plugin_object->txt("ui_admin_configuration_defaults_input_type_description")
        )->withValue($data["input_type"] ?: "algebraic")->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('input_type', $v, "inputs");
                }
            ));

        //Box size
        $input_box_size_value = (string)$data["input_box_size"] ?? "15";

        $input_box_size = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_defaults_input_box_size_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_box_size_description")
        )->withValue($input_box_size_value)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    if(is_numeric($v)){
                        StackConfig::set('input_box_size', $v, "inputs");
                    } else {
                        global $DIC;
                        throw new ILIAS\Refinery\ConstraintViolationException(
                            $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_connection_cache_parsed_expressions_longer_than_validation"),
                            'not_boolean'
                        );
                    }
                }
            ));

        //Strict syntax
        $input_strict_syntax = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_strict_syntax_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_strict_syntax_description")
        )->withValue($data["strict_syntax"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('strict_syntax', $v ? "1" : "0", "inputs");
                }
            ));

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
        )->withValue($data["insert_stars"] ?: "0")->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('insert_stars', $v, "inputs");
                }
            ));

        //Forbidden words
        $input_forbidden_words_value = $data["forbidden_words"] ?? "";

        $input_forbidden_words = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbidden_words_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbidden_words_description")
        )->withValue((string)$input_forbidden_words_value)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    if(is_string($v)){
                        if(preg_match('/^([a-zA-Z0-9]+(?:,\s*[a-zA-Z0-9]+)*)?$/', $v)){
                            StackConfig::set('forbidden_words', $v, "inputs");
                        } else {
                            global $DIC;
                            throw new ILIAS\Refinery\ConstraintViolationException(
                                $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_defaults_input_forbidden_words_validation"),
                                'not_list'
                            );
                        }
                    } else {
                        global $DIC;
                        throw new ILIAS\Refinery\ConstraintViolationException(
                            $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_defaults_input_forbidden_words_validation"),
                            'not_list'
                        );
                    }
                }
            ));


        //Forbid floats
        $input_forbid_float = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbid_float_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_forbid_float_description")
        )->withValue($data["input_forbid_float"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('input_forbid_float', $v ? "1" : "0", "inputs");
                }
           ));

        //Require the lowest terms
        $input_require_lowest_terms = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_require_lowest_terms_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_require_lowest_terms_description")
        )->withValue($data["input_require_lowest_terms"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('input_require_lowest_terms', $v ? "1" : "0", "inputs");
                }
            ));

        //Check answer type
        $input_check_answer_type = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_check_answer_type_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_check_answer_type_description")
        )->withValue($data["input_check_answer_type"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('input_check_answer_type', $v ? "1" : "0", "inputs");
                }
            ));

        //Input must verify
        $input_must_verify_type = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_defaults_input_must_verify_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_input_must_verify_description")
        )->withValue($data["input_must_verify"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('input_must_verify', $v ? "1" : "0", "inputs");
                }
            ));

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

    /**
     * @throws StackException
     */
    private static function getFeedbackStylesDefaultsSection(array $data, ilPlugin $plugin_object): Group
    {
        global $DIC;

        $inputs = [];

        $style_sheets = ilObjStyleSheet::_getStandardStyles();
        $style_sheets[""] = $DIC->language()->txt("default");

        $inputs[] = self::$factory->input()->field()->section([
            self::$factory->input()->field()->select($plugin_object->txt('feedback_stylesheet_id'), $style_sheets, $plugin_object->txt('feedback_stylesheet_id_info'))
                ->withValue(isset($style_sheets[$data["feedback_stylesheet_id"]]) ? $data["feedback_stylesheet_id"] : "")
                ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                    function ($v) {
                        StackConfig::set("feedback_stylesheet_id", $v, "feedback_styles");
                    }
                ))
        ], $plugin_object->txt("feedback_stylesheet_id"));

        if (!empty($data["feedback_stylesheet_id"])) {
            $styles_from_db = StackDatabase::select("style_char", ["type" => "section", "style_id" => $data["feedback_stylesheet_id"]], ["characteristic"]);

            $styles = [];

            foreach ($styles_from_db as $style) {
                $styles[$style["characteristic"]] = $style["characteristic"];
            }

            $styles[""] = $DIC->language()->txt("default");

            for ($i = 1; $i <= 6; $i++) {
                $inputs[] = self::$factory->input()->field()->section([
                    self::$factory->input()->field()->text(
                        $plugin_object->txt("ui_admin_configuration_defaults_feedback_styles_name")
                    )->withValue($data["feedback_styles_name_$i"] ?? "Style " . $i)->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                        function ($v) use ($i) {
                            StackConfig::set("feedback_styles_name_$i", $v, "feedback_styles");
                        }
                    )),
                    self::$factory->input()->field()->select(
                        $plugin_object->txt("ui_admin_configuration_defaults_feedback_styles_style"),
                        $styles
                    )->withValue(isset($styles[$data["feedback_styles_style_$i"]]) ? $data["feedback_styles_style_$i"] : "")->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                        function ($v) use ($i) {
                            StackConfig::set("feedback_styles_style_$i", $v, "feedback_styles");
                        }
                    ))
                ], $plugin_object->txt("ui_admin_configuration_defaults_feedback_styles_title") . " $i");
            }
        }

        return self::$factory->input()->field()->group($inputs);
    }
}