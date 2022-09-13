<?php

/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * Class with STATIC METHODS used in the whole STACK Question
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id 1.6$
 *
 */
class assStackQuestionUtils
{

    /**
     * Prevent comparison operators being interpreted as HTML tags
     * This would cause errors if CASText is processed with strip_tags.
     *
     * Not used anymore because RTE fields convert < and >to &lt; and &gt;
     * The question variables field is now read without strip_tags
     *
     * @param $text
     * @return mixed
     */
    public static function _debugText($text)
    {
        $text1 = str_replace("<", "< ", $text);
        $text2 = str_replace(">", " >", $text1);

        return $text2;
    }

    /**
     * Replace key brackets by their ascii code, to avoid
     * Bug: http://www.ilias.de/mantis/view.php?id=12878
     * @param string $text the original text.
     * @return string the text with corrections done.
     */
    public static function _solveKeyBracketsBug($text)
    {
        $text1 = str_replace("{", "&#123;", $text);

        return str_replace("}", "&#125;", $text1);
    }

    public static function _removeLaTeX($text)
    {
        $text1 = str_replace('\[', '', $text);

        return str_replace('\]', '', $text1);
    }

    public static function _addLatex($text)
    {
        $text1 = '\[' . $text;

        return $text1 . '\]';
    }

    public static function _replacePlaceholders($text, $replacement = '')
    {
        return preg_replace('/\[\[feedback:(.*?)\]\]/', $replacement, $text);
    }

    /**
     * Transforms an answer from STACK evaluation to ILIAS Display format.
     * @param array $student_answer
     * @return array the student_answer with correct display format.
     */
    public static function _fromEvaluationToDisplayFormat($student_answer)
    {
        $display_format = array();
        foreach ($student_answer as $input_name => $value) {
            $display_format['xqcas_input_' . $input_name . '_value'] = $value;
        }

        return $display_format;
    }

    /**
     * Redo changes done by self::_debugText for a few tags
     * (Deprecated, not used anymore)
     *
     * @param $text
     * @return mixed
     * @deprecated
     */
    public static function _solveHTMLProblems($text)
    {
        $text1 = str_replace('< p >', '<p>', $text);
        $text2 = str_replace('< /p >', '</p>', $text1);
        $text3 = str_replace('< br >', '<br>', $text2);
        $text4 = str_replace('< /br >', '</br>', $text3);
        $text5 = str_replace('< br / >', '<br/>', $text4);

        return $text5;
    }


    /**
     * @param $array_of_seeds /array of deployed seeds
     * @param $seed /string created for this pass and active id
     * @return int chosen seed
     */
    public static function _chooseSeedForTestPass($array_of_seeds, $seed)
    {
        //Prepare variables
        $keys = array_keys($array_of_seeds);
        $most_appearances_key = 0;
        $most_appearances_value = 0;

        //Look for most appearances of a key in the seed given
        foreach ($keys as $value => $key) {
            $count = substr_count($seed, $value);
            if ($count > $most_appearances_value) {
                $most_appearances_key = $key;
                $most_appearances_value = $count;
            }
        }

        //Returns seed which appears more times in the seed, otherwise return last seed.
        if ($most_appearances_key > 0) {
            return $array_of_seeds[$most_appearances_key]->getSeed();
        } else {
            return end($array_of_seeds)->getSeed();
        }
    }

    /**
     * @param array $user_response
     * @param $question_id
     * @param array $inputs
     * @param $format
     */
    public static function _getUserResponse($question_id, array $inputs, array $previous_response = array())
    {
        $current_response = array();
        $user_response_from_db = array();

        if (!empty($previous_response)) {
            foreach ($previous_response["prt"] as $prt_name => $prt_info) {
                if (!empty($prt_info["response"])) {
                    foreach ($prt_info["response"] as $input_name => $input_info) {
                        $user_response_from_db[$input_name] = $input_info["value"];
                    }
                }
            }
        }

        $user_response = array();
        foreach ($inputs as $input_name => $input) {
            //Check if its an ILIAS object, or a STACK object
            if (is_a($input, "assStackQuestionInput")) {
                //We have an ILIAS object input

            } elseif (is_subclass_of($input, "stack_input")) {
                $user_response[$input_name] = $input->maxima_to_response_array($user_response_from_db[$input_name]);
            } else {
                //We have something wrong
                ilUtil::sendFailure("Error in manageUserResponse, inputs provided are neither ILIAS or STACK inputs", TRUE);
            }
        }

        return $user_response;
    }

    /**
     * @param $user_response
     * @param $question_id
     * @param $inputs
     * @param $change
     * @return array|bool
     * @throws stack_exception
     */
    public static function _changeUserResponseStyle($user_response, $question_id, $inputs, $change, $mode = '')
    {
        //Initialisation of parameters
        $new_user_response_array = array();
        switch ($change) {
            case 'full_to_reduced':
                //From full to reduced
                foreach ($inputs as $input_name => $input) {
                    //If input is not matrix

                    if ($mode == 'p') {
                        if (is_a($input, 'stack_checkbox_input')) {
                        } elseif (!is_a($input, 'stack_matrix_input')) {
                            if (isset($user_response['xqcas_' . $question_id . '_' . $input_name])) {
                                $new_user_response_array[$input_name] = $user_response['xqcas_' . $question_id . '_' . $input_name];
                            }
                        } else {
                            if (is_array($user_response)) {
                                foreach ($user_response as $index => $user_response) {
                                    $new_index = str_replace('xqcas_' . $question_id . '_', '', $index);
                                    $new_user_response_for_matrix[$new_index] = $user_response;
                                }
                            }
                            if (is_array($new_user_response_for_matrix)) {
                                $new_user_response_array = $new_user_response_for_matrix;
                            }
                        }
                    } elseif ($mode == 't') {

                        if (is_a($input, 'stack_checkbox_input')) {
                        } elseif (!is_a($input, 'stack_matrix_input')) {
                            if ($user_response['xqcas_' . $question_id . '_' . $input_name]) {
                                $new_user_response_array[$input_name] = $user_response['xqcas_' . $question_id . '_' . $input_name];
                            }
                        } else {
                            foreach ($user_response as $index => $user_response) {
                                $new_index = str_replace('xqcas_' . $question_id . '_', '', $index);
                                $new_user_response_for_matrix[$new_index] = $user_response;
                            }
                            if (is_array($new_user_response_for_matrix)) {
                                $new_user_response_array = $new_user_response_for_matrix;
                            }
                        }
                    }
                }
                break;
            case 'full_to_value':
                //from full to value
                foreach ($inputs as $input_name => $input) {
                    //If input is not matrix
                    if (is_a($input, 'stack_checkbox_input')) {

                    } elseif (!is_a($input, 'stack_matrix_input')) {
                        if (isset($user_response['xqcas_' . $question_id . '_' . $input_name])) {
                            $new_user_response_array['xqcas_input_' . $input_name . '_value'] = $user_response['xqcas_' . $question_id . '_' . $input_name];
                        }
                    } else {
                        //Don't change
                        $new_user_response_array = $user_response;
                    }
                }
                break;
            case 'value_to_reduced':
                //from value to reduced
                foreach ($inputs as $input_name => $input) {
                    //If input is not matrix
                    if (is_a($input, 'stack_checkbox_input')) {

                    } elseif (!is_a($input, 'stack_matrix_input')) {
                        if (isset($user_response['xqcas_input_' . $input_name . '_value'])) {
                            $new_user_response_array[$input_name] = $user_response['xqcas_input_' . $input_name . '_value'];
                        }
                    } else {
                        if (isset($user_response['xqcas_input_' . $input_name . '_value'])) {
                            $new_user_response_array = $input->get_expected_data($user_response['xqcas_input_' . $input_name . '_value']);
                        }
                        unset($new_user_response_array[$input_name . '_val']);
                    }
                }
                break;
            case 'reduced_to_value':
                //from reduced to value
                foreach ($inputs as $input_name => $input) {
                    //If input is not matrix
                    if (is_subclass_of($input, "stack_dropdown_input")) {
                        $new_user_response_array['xqcas_input_' . $input_name . '_value'] = $input->maxima_to_response_array($user_response[$input_name]);
                    } elseif (!is_a($input, 'stack_matrix_input')) {
                        if (isset($user_response[$input_name])) {
                            $new_user_response_array['xqcas_input_' . $input_name . '_value'] = $user_response[$input_name];
                        }
                    } else {
                        $new_user_response_array['xqcas_input_' . $input_name . '_value'] = $input->maxima_to_response_array($user_response[$input_name]);
                        unset($new_user_response_array['xqcas_input_' . $input_name . '_value'][$input_name . '_val']);
                    }
                }
                break;
            default:
                throw new stack_exception('exception_unknown_change_of_style');
                break;
        }

        return $new_user_response_array;
    }


    /**
     * Creates stack_options from an assStackQuestionOptions object.
     * @param assStackQuestionOptions $ilias_options
     */
    public static function _createOptions(assStackQuestionOptions $ilias_options)
    {
        $parameters = array( // Array of public class settings for this class.
            'display' => array('type' => 'list', 'value' => 'LaTeX', 'strict' => true, 'values' => array('LaTeX', 'MathML', 'String'), 'caskey' => 'OPT_OUTPUT', 'castype' => 'string',), 'multiplicationsign' => array('type' => 'list', 'value' => $ilias_options->getMultiplicationSign(), 'strict' => true, 'values' => array('dot', 'cross', 'none'), 'caskey' => 'make_multsgn', 'castype' => 'fun',), 'complexno' => array('type' => 'list', 'value' => $ilias_options->getComplexNumbers(), 'strict' => true, 'values' => array('i', 'j', 'symi', 'symj'), 'caskey' => 'make_complexJ', 'castype' => 'fun',), 'inversetrig' => array('type' => 'list', 'value' => $ilias_options->getInverseTrig(), 'strict' => true, 'values' => array('cos-1', 'acos', 'arccos'), 'caskey' => 'make_arccos', 'castype' => 'fun',), 'floats' => array('type' => 'boolean', 'value' => 1, 'strict' => true, 'values' => array(), 'caskey' => 'OPT_NoFloats', 'castype' => 'ex',), 'sqrtsign' => array('type' => 'boolean', 'value' => $ilias_options->getSqrtSign(), 'strict' => true, 'values' => array(), 'caskey' => 'sqrtdispflag', 'castype' => 'ex',), 'simplify' => array('type' => 'boolean', 'value' => $ilias_options->getQuestionSimplify(), 'strict' => true, 'values' => array(), 'caskey' => 'simp', 'castype' => 'ex',), 'assumepos' => array('type' => 'boolean', 'value' => $ilias_options->getAssumePositive(), 'strict' => true, 'values' => array(), 'caskey' => 'assume_pos', 'castype' => 'ex',),);

        require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionStackFactory.php';
        $stack_factory = new assStackQuestionStackFactory();

        return $stack_factory->get("options", $parameters);
    }

    /**
     * @param $array .
     * @return bool
     */
    public static function _isArrayEmpty($array, $inputs)
    {
        //If array is not empty returns it, otherwise return FALSE;
        foreach ($array as $input_name => $value) {
            if ($value != '' AND $value != '[]') {
                return FALSE;
            }
            //Check emptyanswer
            if ($value == "") {
			    $input = $inputs[$input_name];
			    if(is_a($input,"assStackQuestionInput")){
					$options = $input->getOptions();
					if (strpos($options,"allowempty") !== FALSE){
						return FALSE;
					}
			    }
            }
        }

        return TRUE;
    }

    /**
     * Checks wheter a question uses randomisation or not
     * @param $question_variables_text string the question variables
     * @return boolean
     */
    public static function _questionHasRandomVariables($question_variables_text)
    {
        return (boolean)preg_match('~\brand~', $question_variables_text);
    }

    /**
     * Checks wheter a question uses randomisation or not
     * @param $question_variables_text string the question variables
     * @return boolean
     */
    public static function _getInputsAndPRTStructure($question_id)
    {
        $structure = array();
        $structure['input'] = assStackQuestionInput::_read($question_id);
        $structure['prt'] = assStackQuestionPRT::_read($question_id);

        return $structure;
    }

    public static function _useInstantValidation()
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'SELECT value FROM xqcas_configuration WHERE parameter_name = "instant_validation"';

        $result = $db->query($query);
        while ($row = $db->fetchAssoc($result)) {
            if ((int)$row['value']) {
                return TRUE;
            } else {
                return FALSE;
            }
        }

    }

    public static function _getSeedFromTest($question_id, $active_id, $pass, $prt_name)
    {
        global $DIC;
        $db = $DIC->database();
        $query = 'SELECT value2 FROM tst_solutions WHERE question_fi = ' . $question_id;
        $query .= ' AND active_fi = ' . $active_id;
        $query .= ' AND pass = ' . $pass;
        $query .= ' AND value1 = "xqcas_prt_' . $prt_name . '_seed"';

        $result = $db->query($query);
        while ($row = $db->fetchAssoc($result)) {
            if ((int)$row['value2']) {
                return (int)$row['value2'];
            } else {
                return FALSE;
            }
        }

    }

    public static function _isInputEvaluated($prt, $input_name)
    {
        foreach ($prt->getPRTNodes() as $node_name => $node) {
            if (strpos($node->getStudentAnswer(), $input_name) !== false OR strpos($node->getTeacherAnswer(), $input_name)) {
                return TRUE;
            }
        }

        return FALSE;

    }

    /**#
     * Used for show Info labels in inputs or PRT
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function _endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * This function returns the LaTeX rendered version of $text
     * @param $text The raw text
     * @return string
     */
    public static function _getLatex($text)
    {
        /*
         * Step 1 check current platform's LaTeX delimiters
         */
        //Replace dollars but using mathjax settings in each platform.
        $mathJaxSetting = new ilSetting("MathJax");
        //By default [tex]
        $start = '[tex]';
        $end = '[/tex]';

        switch ((int)$mathJaxSetting->setting['limiter']) {
            case 0:
                /*\(...\)*/
                $start = '\(';
                $end = '\)';
                break;
            case 1:
                /*[tex]...[/tex]*/
                $start = '[tex]';
                $end = '[/tex]';
                break;
            case 2:
                /*&lt;span class="math"&gt;...&lt;/span&gt;*/
                $start = '&lt;span class="math"&gt;';
                $end = '&lt;/span&gt;';
                break;
            default:

        }
        /*
         * Step 2 Replace $$ from STACK and all other LaTeX delimiter to the current platform's delimiter.
         */
        //Get all $$ to replace it
        $text = preg_replace('~(?<!\\\\)\$\$(.*?)(?<!\\\\)\$\$~', $start . '$1' . $end, $text);
        $text = preg_replace('~(?<!\\\\)\$(.*?)(?<!\\\\)\$~', $start . '$1' . $end, $text);

        //Comment this in order to have different ebhaviour between display and inline mode of LaTeX,
        //Solving bug 20783
        //Search for all /(/) and change it to the current limiter in Mathjaxsettings
        //$text = str_replace('\(', $start, $text);
        //$text = str_replace('\)', $end, $text);

        //Search for all \[\] and change it to the current limiter in Mathjaxsettings
        //$text = str_replace('\[', $start, $text);
        //$text = str_replace('\]', $end, $text);

        //Search for all [tex] and change it to the current limiter in Mathjaxsettings
        $text = str_replace('[tex]', $start, $text);
        $text = str_replace('[/tex]', $end, $text);
        //Search for all &lt;span class="math"&gt;...&lt;/span&gt; and change it to the current limiter in Mathjaxsettings
        $text = preg_replace('/<span class="math">(.*?)<\/span>/', $start . '$1' . $end, $text);

        //Search for all &lt;span class="latex"&gt;...&lt;/span&gt; and change it to the current limiter in Mathjaxsettings
        $text = preg_replace('/<span class="latex">(.*?)<\/span>/', $start . '$1' . $end, $text);

        // replace special characters to prevent problems with the ILIAS template system
        // eg. if someone uses {1} as an answer, nothing will be shown without the replacement
        $text = str_replace("{", "&#123;", $text);
        $text = str_replace("}", "&#125;", $text);
        $text = str_replace("\\", "&#92;", $text);

        /*
         * Step 3 User ilMathJax::getInstance()->insertLatexImages to deliver the LaTeX code.
         */
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/mathsoutput/mathsoutput.class.php';
        //ilMathJax::getInstance()->insertLatexImages cannot render \( delimiters so we change it to [tex]
        if ($start == '\(') {
            return stack_maths::process_display_castext(ilMathJax::getInstance()->insertLatexImages($text));
        } else {
            return stack_maths::process_display_castext(ilMathJax::getInstance()->insertLatexImages($text, $start, $end));
        }
    }

    public static function _getNewTestCaseNumber($question_id)
    {
        global $DIC;
        $db = $DIC->database();

        $query = 'SELECT MAX(test_case) FROM xqcas_qtests WHERE question_id = ' . $question_id;

        $result = $db->query($query);
        while ($row = $db->fetchAssoc($result)) {
            if ((int)$row['MAX(test_case)']) {
                return ((int)$row['MAX(test_case)'] + 1);
            } else {
                return 1;
            }
        }
    }

    /**
     * This method convert a text with old delimiters such $$ or @ to the new {@ and platform delimiter
     * and also to the platform delimiter for LaTeX in case this delimiter is different as the one used in the question.
     * This come from version 4.0 of STACK in Moodle
     * @param $old_text string Text to be converted
     * @param $platform_latex_delimiter string
     * @return array
     */
    public static function _updateMathDelimiters($old_text, $platform_latex_delimiter)
    {
        $results = array();

        return $results;
    }

    /**
     * @return array of available type names.
     * Refactoring of stack_input_factory::get_availavle_types
     */
    public static function _getAvailableTypes()
    {

        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/algebraic/algebraic.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/boolean/boolean.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/checkbox/checkbox.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/dropdown/dropdown.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/equiv/equiv.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/matrix/matrix.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/notes/notes.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/radio/radio.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/singlechar/singlechar.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/textarea/textarea.class.php';
        include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/input/units/units.class.php';

        $types = array('algebraic' => 'stack_algebraic_input', 'boolean' => 'stack_boolean_input', 'checkbox' => 'stack_checkbox_input', 'dropdown' => 'stack_dropdown_input', 'equiv' => 'stack_equiv_input', 'matrix' => 'stack_matrix_input', 'notes' => 'stack_notes_input', 'radio' => 'stack_radio_input', 'singlechar' => 'stack_singlechar_input', 'textarea' => 'stack_textarea_input', 'units' => 'stack_units_input');

        return $types;
    }

    /**
     * This function will be use in the import routines, in order to check if the questions follow the new syntax for STACK questions.
     * @param string $a_text
     * @return string The converted text.
     */
    public static function _casTextConverter($a_text, $a_question_title = "", $a_show_alert = FALSE)
    {
        global $DIC;
        $lng = $DIC->language();
        require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/mathsoutput/mathsoutput.class.php';
        require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php');

        //Do replacement
        //#22779 a_strip_html must be false
        $new_text = ilUtil::secureString(stack_maths::replace_dollars($a_text), FALSE);

        //STEP 4 Send back the fixed text
        return $new_text;
    }

    public static function _adaptUserResponseTo($user_response, $question_id, $format)
    {
        $adapted_user_response = array();
        foreach ($user_response as $input_name => $input_value) {
            if ($format == "only_input_names") {
                $adapted_user_response[str_replace("xqcas_" . $question_id . "_", "", $input_name)] = ilUtil::stripScriptHTML($input_value);
            }
        }
        return $adapted_user_response;
    }


    public static function stack_output_castext($castext)
    {
        require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/mathsoutput/mathsoutput.class.php';
        require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php');

        return stack_maths::process_display_castext($castext);
    }

    /**
     * Returns the ID of each content styles available in the platform.
     */
    public static function _getContentStylesAvailable()
    {
        global $DIC;
        $db = $DIC->database();

        $styles_id = array();
        $query = "SELECT id FROM style_data WHERE active = '1'";
        $result = $db->query($query);
        while ($row = $db->fetchAssoc($result)) {
            $styles_id[] = $row["id"];
        }

        return $styles_id;
    }

    /**
     * Returns a text with a format from the content style
     * @param $a_text
     * @param $a_format
     * @return string
     */
    public static function _getFeedbackStyledText($a_text, $a_format)
    {
        require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/model/configuration/class.assStackQuestionConfig.php');

        //Get Styles assigned to Formats
        $config_options = assStackQuestionConfig::_getStoredSettings("feedback");
        require_once "./Services/Style/Content/classes/class.ilObjStyleSheet.php";

        //Return text depending Format
        if (strlen($a_text)) {
            switch ($a_format) {
                case "feedback_default":
                    if ($config_options["feedback_default"] == "0") {
                        return '<div class="alert alert-warning" role="alert">' . $a_text . '</div>';
                    } else {
                        $style_assigned = $config_options[$a_format];

                        return '<div class="ilc_text_block_' . $style_assigned . ' ilPositionStatic">' . $a_text . '</div>';
                    }
                default:
                    //Use specific feedback style
                    $style_assigned = $config_options[$a_format];

                    return '<div class="ilc_text_block_' . $style_assigned . ' ilPositionStatic">' . $a_text . '</div>';
            }
        } else {
            return $a_text;
        }

    }

    public static function _getActiveContentStyleId()
    {
        global $DIC;
        $db = $DIC->database();

        $styles_id = array();
        $query = "SELECT value FROM xqcas_configuration WHERE parameter_name = 'feedback_stylesheet_id'";
        $result = $db->query($query);
        while ($row = $db->fetchAssoc($result)) {
            return $row["value"];
        }
    }

    public static function _replaceFeedbackPlaceHolders($feedback)
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

    public static function _isPhP72()
    {
        $php_version = phpversion();

        $version = substr($php_version, 0, 3);
        if ($version < 7.2) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

	/**
	 * Collects the data of a assStackQuestion into an array
	 * @param assStackQuestion $question
	 * @return array
	 */
	public static function _questionToArray(assStackQuestion $question): array
	{

		global $ilias;
		$array = array();
		$plugin = $question->getPlugin();
		require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/class.assStackQuestionDB.php';

		/**
		 * question_type
		 * question_id
		 * question_title
		 * question_author
		 * question_text
		 *
		 * ilias_version
		 * plugin_version
		 *
		 * question_options[]
		 * inputs[]
		 * prts[]
		 * deployed_variants[]
		 * extra_info[]
		 * unit_tests[]
		 */

		$array['question_type'] = $question->getQuestionType();
		$array['question_id'] = $question->getId();
		$array['question_title'] = $question->getTitle();
		$array['question_author'] = $question->getAuthor();
		$array['question_text'] = $question->getQuestion();

		$array['ilias_version'] = $ilias->getSetting("ilias_version");
		$array['plugin_version'] = $plugin->getVersion();

		//OPTIONS
		$array['options'] = assStackQuestionDB::_readOptions($question->getId());

		//INPUTS
		$array['inputs'] = assStackQuestionDB::_readInputs($question->getId());

		//PRTS
		$array['prts'] = assStackQuestionDB::_readPRTs($question->getId());

		//DEPLOYED VARIANTS
		$array['deployed_variants'] = assStackQuestionDB::_readDeployedVariants($question->getId());

		//EXTRA INFORMATION
		$array['extra_information'] = assStackQuestionDB::_readExtraInformation($question->getId());

		//UNIT TEXT
		$array['unit_tests'] = assStackQuestionDB::_readUnitTests($question->getId());

		return $array;
	}

	/**
	 * Sets the array's data into assStackQuestion
	 * @param array $array
	 * @param assStackQuestion $question
	 * @return assStackQuestion
	 */
	public static function _arrayToQuestion(array $array, assStackQuestion $question): assStackQuestion
	{

		$question_id = $question->getId();

		//load options
		try {
			$options = new stack_options($array['options']['options']);
			//SET OPTIONS
			$question->options = $options;
		} catch (stack_exception $e) {
			ilUtil::sendFailure($e->getMessage(), true);
		}

		//load Data stored in options but not part of the session options
		$question->question_variables = $array['options']['ilias_options']['question_variables'];
		$question->question_note = $array['options']['ilias_options']['question_note'];

		$question->specific_feedback = $array['options']['ilias_options']['specific_feedback'];
		$question->specific_feedback_format = $array['options']['ilias_options']['specific_feedback_format'];

		$question->prt_correct = $array['options']['ilias_options']['prt_correct'];
		$question->prt_correct_format = $array['options']['ilias_options']['prt_correct_format'];
		$question->prt_partially_correct = $array['options']['ilias_options']['prt_partially_correct'];
		$question->prt_partially_correct_format = $array['options']['ilias_options']['prt_partially_correct_format'];
		$question->prt_incorrect = $array['options']['ilias_options']['prt_incorrect'];
		$question->prt_incorrect_format = $array['options']['ilias_options']['prt_incorrect_format'];

		$question->variants_selection_seed = $array['options']['ilias_options']['variants_selection_seed'];

		//stack version
		if (isset($array['plugin_version'])) {
			$question->stack_version = $array['plugin_version'];
		} else {
			//Stack version TODO CONFIG
			$question->stack_version = '2021120900';
		}

		//load inputs
		$inputs_from_array = $array['inputs'];
		$required_parameters = stack_input_factory::get_parameters_used();

		//load only those inputs appearing in the question text
		foreach (stack_utils::extract_placeholders($question->getQuestion(), 'input') as $input_name) {

			$input_data = $inputs_from_array['inputs'][$input_name];
			$input_type = $input_data['type'];

			//Adjust syntax Hint for Textareas
			//Firstline shown as irstlin
			/*
			if ($input_data['type'] == 'equiv' || $input_data['type'] == 'textarea') {
				if (strlen($input_data['syntax_hint']) and !str_starts_with($input_data['syntax_hint'], '[')) {
					$input_data['syntax_hint'] = '[' . $input_data['syntax_hint'] . ']';
				}
				if (strlen($input_data['tans']) and !str_starts_with($input_data['tans'], '[')) {
					$input_data['tans'] = '[' . $input_data['tans'] . ']';
				}
			}*/

			$all_parameters = array(
				'boxWidth' => $input_data['box_size'],
				'strictSyntax' => $input_data['strict_syntax'],
				'insertStars' => $input_data['insert_stars'],
				'syntaxHint' => $input_data['syntax_hint'],
				'syntaxAttribute' => $input_data['syntax_attribute'],
				'forbidWords' => $input_data['forbid_words'],
				'allowWords' => $input_data['allow_words'],
				'forbidFloats' => $input_data['forbid_float'],
				'lowestTerms' => $input_data['require_lowest_terms'],
				'sameType' => $input_data['check_answer_type'],
				'mustVerify' => $input_data['must_verify'],
				'showValidation' => $input_data['show_validation'],
				'options' => $input_data['options'],
			);

			$parameters = array();
			foreach ($required_parameters[$input_type] as $parameter_name) {
				if ($parameter_name == 'inputType') {
					continue;
				}
				$parameters[$parameter_name] = $all_parameters[$parameter_name];
			}

			//SET INPUTS
			$question->inputs[$input_name] = stack_input_factory::make($input_data['type'], $input_data['name'], $input_data['tans'], $question->options, $parameters);
		}

		//load PRTs and PRT nodes
		$prt_from_array = $array['prts'];;

		//Values
		$total_value = 0;

		//in ILIAS all attempts are graded
		$grade_all = true;

		foreach ($prt_from_array as $prt_name => $prt_data) {
			$total_value += $prt_data['value'];
		}

		if ($prt_from_array && $grade_all && $total_value < 0.0000001) {
			try {
				throw new stack_exception('There is an error authoring your question. ' .
					'The $totalvalue, the marks available for the question, must be positive in question ' .
					$question->getTitle());
			} catch (stack_exception $e) {
				ilUtil::sendFailure($e);
				$total_value = 1.0;
			}
		}

		//get PRT and PRT Nodes from DB

		$prt_names = self::_getPRTNamesFromQuestion($question->getQuestion(), $array['options']['ilias_options']['specific_feedback'], $prt_from_array);

		if (!empty($prt_names)) {
			foreach ($prt_names as $prt_name) {

				$prt_data = $prt_from_array[$prt_name];
				$nodes = array();

				if (isset($prt_data['nodes']) and !empty($prt_data['nodes'])) {
					foreach ($prt_data['nodes'] as $node_name => $node_data) {

						$sans = stack_ast_container::make_from_teacher_source('PRSANS' . $node_name . ':' . $node_data['sans'], '', new stack_cas_security());
						$tans = stack_ast_container::make_from_teacher_source('PRTANS' . $node_name . ':' . $node_data['tans'], '', new stack_cas_security());

						//Penalties management, penalties are not an ILIAS Feature
						if (is_null($node_data['false_penalty']) || $node_data['false_penalty'] === '') {
							$false_penalty = 0;
						} else {
							$false_penalty = $node_data['false_penalty'];
						}

						if (is_null(($node_data['true_penalty']) || $node_data['true_penalty'] === '')) {
							$true_penalty = 0;
						} else {
							$true_penalty = $node_data['true_penalty'];
						}

						try {
							//Create Node and add it to the
							$node = new stack_potentialresponse_node($sans, $tans, $node_data['answer_test'], $node_data['test_options'], (bool)$node_data['quiet'], '', (int)$node_name, $node_data['sans'], $node_data['tans']);

							$node->add_branch(0, $node_data['false_score_mode'], $node_data['false_score'], $false_penalty, $node_data['false_next_node'], $node_data['false_feedback'], $node_data['false_feedback_format'], $node_data['false_answer_note']);
							$node->add_branch(1, $node_data['true_score_mode'], $node_data['true_score'], $true_penalty, $node_data['true_next_node'], $node_data['true_feedback'], $node_data['true_feedback_format'], $node_data['true_answer_note']);

							$nodes[$node_name] = $node;
						} catch (stack_exception $e) {
							ilUtil::sendFailure($e->getMessage(), true);
						}
					}
				} else {
					break;
				}

				if ($prt_data['feedback_variables']) {
					try {
						$feedback_variables = new stack_cas_keyval($prt_data['feedback_variables']);
						$feedback_variables = $feedback_variables->get_session();
					} catch (stack_exception $e) {
						ilUtil::sendFailure($e->getMessage(), true);
					}
				} else {
					$feedback_variables = null;
				}

				if ($total_value == 0) {
					//TODO Non gradable question
					$prt_value = 0.0;
				} else {
					$prt_value = $prt_data['value'];
				}

				try {
					$question->prts[$prt_name] = new stack_potentialresponse_tree($prt_name, '', (bool)$prt_data['auto_simplify'], $prt_value, $feedback_variables, $nodes, (string)$prt_data['first_node_name'], 1);
				} catch (stack_exception $e) {
					ilUtil::sendFailure($e, true);
				}
			}
		}

		//load seeds
		$deployed_seeds = $array['deployed_variants'];

		//Needs deployed seeds as key for initialisation
		$depured_deployed_seeds = array();
		foreach ($deployed_seeds as $deployed_seed) {
			$depured_deployed_seeds[$deployed_seed] = $deployed_seed;
		}
		$question->deployed_seeds = $depured_deployed_seeds;

		//load extra info
		$extra_info = $array['extra_information'];
		if (is_array($extra_info)) {
			$question->general_feedback = $extra_info['general_feedback'];
			$question->penalty = (float)$extra_info['penalty'];
			$question->hidden = (bool)$extra_info['hidden'];
		} else {
			$question->general_feedback = '';
			$question->penalty = 0.0;
			$question->hidden = false;
		}

		//load unit tests
		$unit_tests = $array['unit_tests'];
		$question->setUnitTests($unit_tests);

		//Returns question
		return $question;
	}
}
