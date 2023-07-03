<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';
require_once('Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php');

/**
 * STACK Question authoring GUI class
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionAuthoringGUI
{

	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private $plugin;

	/**
	 * The question already evaluated
	 * @var assStackQuestionGUI
	 */
	private $question_gui;

	/**
	 * @var ilPropertyFormGUI
	 */
	private $form;

	/**
	 * @var ilTemplate the global template
	 */
	private $template;


	/**
	 * Object constructor
	 * @param $plugin ilassStackQuestionPlugin
	 * @param $question assStackQuestionGUI
	 */
	function __construct($plugin, $question)
	{
		global $DIC;

		//Set global vars
		$this->setPlugin($plugin);
		$this->setQuestionGUI($question);

		//Set templates
		$this->setTemplate($this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_authoring_container.html'));

		//Set toolbar
		require_once("./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php");
		$toolbar = new ilToolbarGUI();
		include_once('./Services/UIComponent/Button/classes/class.ilButton.php');

        /*
		$show_info_button = ilButton::getInstance();
		$show_info_button->setCaption($this->getPlugin()->txt("enable_disable_info"), FALSE);
		$show_info_button->setId("enable_disable_info");
		$toolbar->addButtonInstance($show_info_button);

		$show_link_button = ilButton::getInstance();
		$show_link_button->setCaption($this->getPlugin()->txt("auth_guide_name"), FALSE);
		$show_link_button->setId("auth_guide_name");
		$toolbar->addButtonInstance($show_link_button);
*/
		$this->getTemplate()->setVariable("TOOLBAR", $toolbar->getHTML());

		//Set form
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPlugin()->txt('edit_cas_question'));
		$ctrl = $DIC->ctrl();
		$form->setFormAction($ctrl->getFormActionByClass('assStackQuestionGUI'));
		$lng = $DIC->language();
		$form->addCommandButton('save', $lng->txt('save'));
		$form->addCommandButton('editQuestion', $lng->txt('cancel'));

		//Set show info;
		$this->setForm($form);
	}


	public function showAuthoringPanel()
	{

		//https://mantis.ilias.de/view.php?id=25290
		require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/model/configuration/class.assStackQuestionConfig.php');
		$this->default = assStackQuestionConfig::_getStoredSettings("all");

		//Add general properties to form like question text, title, author...
		//ADD predefined input and validation fields
		if ($this->getQuestionGUI()->object->getQuestion() == "") {
			$this->new_question = TRUE;
			$this->getQuestionGUI()->object->setQuestion("[[input:ans1]] [[validation:ans1]]");
			$this->getQuestionGUI()->object->setPoints("1");
		}

		//Add question title when blank
		if ($this->getQuestionGUI()->object->getTitle() == NULL) {
			$this->getQuestionGUI()->object->setTitle($this->getPlugin()->txt('untitled_question'));
		}

		$this->getQuestionGUI()->addBasicQuestionFormProperties($this->getForm());
		$this->getQuestionGUI()->setRTESupport($this->getForm()->getItemByPostVar('question'));

		//Save basic data of the question
		if (!$this->getQuestionGUI()->object->options) {
			$this->getQuestionGUI()->object->saveToDb("");
		}

		$question_text = $this->getForm()->getItemByPostVar('question');
		$question_text->setInfo($this->getPlugin()->txt("authoring_input_creation_info"));

		$points = new ilNonEditableValueGUI($this->getPlugin()->txt('preview_points_message_p3'));
		$points->setInfo($this->getPlugin()->txt('authoring_points_info'));
		$points->setValue($this->getQuestionGUI()->object->getPoints());
		$this->getForm()->addItem($points);

		//Add options part
		$this->addOptions();

		//Add inputs part
		$this->addInputs();

		//Add PRTs
		$this->addPRTs();

		//Add Taxonomies
		$this->getQuestionGUI()->populateTaxonomyFormSection($this->getForm());

		//FILL TPL
		$this->getTemplate()->setVariable("FORM", $this->getForm()->getHTML());

		//Show error messages if exists
		$this->manageErrorMessages();

		return $this->getTemplate()->get();
	}

	/**
	 * Add the accordion object for the options
	 */
	public function addOptions()
	{
		//Question variables
		$question_variables = new ilTextAreaInputGUI($this->getPlugin()->txt('options_question_variables'), 'options_question_variables');
		$question_variables_info_text = $this->getPlugin()->txt('options_question_variables_info') . "</br>";
		$question_variables_info_text .= $this->addInfoTooltip("cas_expression");
		$question_variables->setInfo($question_variables_info_text);
		$question_variables->setValue($this->getQuestionGUI()->object->question_variables);
		$this->getForm()->addItem($question_variables);

		//Question note
		$question_note = new ilTextAreaInputGUI($this->getPlugin()->txt('options_question_note'), 'options_question_note');
		$question_note_info_text = $this->getPlugin()->txt('options_question_note_info') . "</br>";
		$question_note_info_text .= $this->addInfoTooltip("cas_text");
		$question_note->setInfo($question_note_info_text);
		$question_note->setValue($this->getQuestionGUI()->object->question_note);
		$this->getForm()->addItem($question_note);

		//Question specific feedback
		$question_specific_feedback = new ilTextAreaInputGUI($this->getPlugin()->txt('options_specific_feedback'), 'options_specific_feedback');
		$question_specific_feedback_info_text = $this->getPlugin()->txt('options_specific_feedback_info') . "</br>";
		$question_specific_feedback_info_text .= $this->addInfoTooltip("cas_text");
		$question_specific_feedback->setValue($this->getQuestionGUI()->object->specific_feedback);
		$question_specific_feedback->setInfo($question_specific_feedback_info_text);
		$this->getQuestionGUI()->setRTESupport($question_specific_feedback);
		$this->getForm()->addItem($question_specific_feedback);

		//Options
		$options = new ilAccordionFormPropertyGUI($this->getPlugin()->txt('options'), "question_options", 12, TRUE);

		if (is_a($this->getQuestionGUI()->object->options, 'stack_options')) {
			//In case of edition
			$options_part = $this->getOptionsPart($this->getQuestionGUI()->object->options, $this->getQuestionGUI()->object->general_feedback);
			$options->addPart($options_part);
		}

		//Title as section header
		$options_section_header = new ilFormSectionHeaderGUI();
		$options_section_header->setTitle($this->getPlugin()->txt('options'));
		$this->getForm()->addItem($options_section_header);
		$this->getForm()->addItem($options);
	}

	/**
	 * Add the accordion object for the inputs
	 */
	public function addInputs()
	{
		$inputs = new ilAccordionFormPropertyGUI($this->getPlugin()->txt('inputs'), 'question_inputs', 12, TRUE);

		//Title as section header
		$inputs_section_header = new ilFormSectionHeaderGUI();
		$inputs_section_header->setTitle($this->getPlugin()->txt('inputs'));
		$this->getForm()->addItem($inputs_section_header);

		if (!empty($this->getQuestionGUI()->object->inputs)) {
			//In case of edition
			foreach ($this->getQuestionGUI()->object->inputs as $input_name => $input) {
				$input_part = $this->getInputPart($input);
				$input_part->setTitle($this->getPlugin()->txt('auth_inputs') . " " . $input_name . "<font color='red'> *</font>");
				$inputs->addPart($input_part);
			}
			$this->getForm()->addItem($inputs);
		} else {
			//load standard input
			$standard_input = assStackQuestionConfig::_getStoredSettings('inputs');

			$required_parameters = stack_input_factory::get_parameters_used();

			$all_parameters = array(
				'boxWidth' => $standard_input['input_box_size'],
				'strictSyntax' => $standard_input['input_strict_syntax'],
				'insertStars' => $standard_input['input_insert_stars'],
				'syntaxHint' => $standard_input['input_syntax_hint'],
				'syntaxAttribute' => '',
				'forbidWords' => $standard_input['input_forbidden_words'],
				'allowWords' => $standard_input['input_allow_words'],
				'forbidFloats' => $standard_input['input_forbid_float'],
				'lowestTerms' => $standard_input['input_require_lowest_terms'],
				'sameType' => $standard_input['input_check_answer_type'],
				'mustVerify' => $standard_input['input_must_verify'],
				'showValidation' => $standard_input['input_show_validation'],
				'options' => $standard_input['input_extra_options'],
			);

			$parameters = array();
			foreach ($required_parameters[$standard_input['input_type']] as $parameter_name) {
				if ($parameter_name == 'inputType') {
					continue;
				}
				$parameters[$parameter_name] = $all_parameters[$parameter_name];
			}
			$input = stack_input_factory::make('algebraic', 'ans1', 1, $this->getQuestionGUI()->object->options, $parameters);
			$input_part = $this->getInputPart($input);
			$input_part->setTitle($this->getPlugin()->txt('auth_inputs') . " ans1");
			$inputs->addPart($input_part);
			$this->getForm()->addItem($inputs);
		}
	}

	/**
	 * Adds the tabs per each PRT
	 */
	public function addPRTs()
	{
		$prts = new ilTabsFormPropertyGUI($this->getPlugin()->txt('prts'), "question_prts", 12, FALSE);

		if (!empty($this->getQuestionGUI()->object->prts)) {
			foreach ($this->getQuestionGUI()->object->prts as $prt_name => $prt) {
				$prt_part = $this->getPRTPart($prt);
				$prt_part->setType($prt_name);
				$prts->addPart($prt_part);
			}
		}

		//TODO
		/*
		//Add extra PRT with extra Node
		$new_prt = new assStackQuestionPRT(-1, $this->getQuestionGUI()->object->getId());
		$new_prt->setPRTName('new_prt');
		$new_prt->setPRTValue(1);
		//https://mantis.ilias.de/view.php?id=25290
		$new_prt->setAutoSimplify($this->default["prt_simplify"]);
		$new_prt->checkPRT(TRUE);

		//https://mantis.ilias.de/view.php?id=25290
		$new_prt_node = new assStackQuestionPRTNode(-1, $this->getQuestionGUI()->object->getId(), 'new_prt', '1', -1, -1);
		$new_prt_node->setAnswerTest($this->default["prt_node_answer_test"]);
		$new_prt_node->setQuiet($this->default["prt_node_quiet"]);
		$new_prt_node->setTestOptions($this->default["prt_node_options"]);

		$new_prt_node->setTrueScoreMode($this->default["prt_pos_mod"]);
		$new_prt_node->setFalseScoreMode($this->default["prt_neg_mod"]);

		$new_prt_node->setTrueScore($this->default["prt_pos_score"]);
		$new_prt_node->setFalseScore($this->default["prt_neg_score"]);

		$new_prt_node->setTruePenalty($this->default["prt_pos_penalty"]);
		$new_prt_node->setFalsePenalty($this->default["prt_neg_penalty"]);

		$new_prt_node->checkPRTNode(TRUE);
		$new_prt->setPRTNodes(array('0' => $new_prt_node));
		$new_prt->setFirstNodeName($new_prt->getFirstNodeName(TRUE));

		$new_prt_part = $this->getPRTPart($new_prt);
		$new_prt_part->setType('new_prt');
		$new_prt_part->setTitle($this->getPlugin()->txt('add_new_prt'));
		$prts->addPart($new_prt_part);
		//Set width
		$prts->setWidthDivision(array('title' => 0, 'content' => 12, 'footer' => 0));

		//Title as section header
		$prts_section_header = new ilFormSectionHeaderGUI();
		$prts_section_header->setTitle($this->getPlugin()->txt('prts'));
		$this->getForm()->addItem($prts_section_header);
*/

		$this->getForm()->addItem($prts);
	}

	/**
	 * TODO PENALTY AND HIDDEN IN FORM FEATURE
	 * Gets the options part
	 * @return ilMultipartFormPart
	 */
	public function getOptionsPart(stack_options $options, string $general_feedback)
	{
		$part = new ilMultipartFormPart($this->getPlugin()->txt('show_options'));

		//Options question simplify
		$options_question_simplify = new ilCheckboxInputGUI($this->getPlugin()->txt('options_question_simplify'), 'options_question_simplify');
		$options_question_simplify->setInfo($this->getPlugin()->txt('options_question_simplify_info'));

		//Options assume positive
		$options_assume_positive = new ilCheckboxInputGUI($this->getPlugin()->txt('options_assume_positive'), 'options_assume_positive');
		$options_assume_positive->setInfo($this->getPlugin()->txt('options_assume_positive_info'));

		//Options Standard feedback for correct answer
		$options_prt_correct = new ilTextAreaInputGUI($this->getPlugin()->txt('options_prt_correct'), 'options_prt_correct');
		$this->getQuestionGUI()->setRTESupport($options_prt_correct);
		$options_prt_correct->setInfo($this->addInfoTooltip("html"));

		//Options Standard feedback for partially correct answer
		$options_prt_partially_correct = new ilTextAreaInputGUI($this->getPlugin()->txt('options_prt_partially_correct'), 'options_prt_partially_correct');
		$this->getQuestionGUI()->setRTESupport($options_prt_partially_correct);
		$options_prt_partially_correct->setInfo($this->addInfoTooltip("html"));

		//Options Standard feedback for incorrect answer
		$options_prt_incorrect = new ilTextAreaInputGUI($this->getPlugin()->txt('options_prt_incorrect'), 'options_prt_incorrect');
		$this->getQuestionGUI()->setRTESupport($options_prt_incorrect);
		$options_prt_incorrect->setInfo($this->addInfoTooltip("html"));

		//Options multiplication sign
		$options_multiplication_sign = new ilSelectInputGUI($this->getPlugin()->txt('options_multiplication_sign'), 'options_multiplication_sign');
		$options_multiplication_sign->setOptions(array("dot" => $this->getPlugin()->txt('options_mult_sign_dot'), "cross" => $this->getPlugin()->txt('options_mult_sign_cross'), "none" => $this->getPlugin()->txt('options_mult_sign_none')));
		$options_multiplication_sign->setInfo($this->getPlugin()->txt('options_multiplication_sign'));

		//Options Sqrt sign
		$options_sqrt_sign = new ilCheckboxInputGUI($this->getPlugin()->txt('options_sqrt_sign'), 'options_sqrt_sign');
		$options_sqrt_sign->setInfo($this->getPlugin()->txt('options_sqrt_sign_info'));

		//Options Complex numbers
		$options_complex_numbers = new ilSelectInputGUI($this->getPlugin()->txt('options_complex_numbers'), 'options_complex_numbers');
		$options_complex_numbers->setOptions(array("i" => $this->getPlugin()->txt('options_complex_numbers_i'), "j" => $this->getPlugin()->txt('options_complex_numbers_j'), "symi" => $this->getPlugin()->txt('options_complex_numbers_symi'), "symj" => $this->getPlugin()->txt('options_complex_numbers_symj')));
		$options_complex_numbers->setInfo($this->getPlugin()->txt('options_complex_numbers_info'));

		//Options inverse trigonometric
		$options_inverse_trigonometric = new ilSelectInputGUI($this->getPlugin()->txt('options_inverse_trigonometric'), 'options_inverse_trigonometric');
		$options_inverse_trigonometric->setOptions(array("cos-1" => $this->getPlugin()->txt('options_inverse_trigonometric_cos'), "acos" => $this->getPlugin()->txt('options_inverse_trigonometric_acos'), "arccos" => $this->getPlugin()->txt('options_inverse_trigonometric_arccos')));
		$options_inverse_trigonometric->setInfo($this->getPlugin()->txt('options_inverse_trigonometric_info'));

		//Matrix Parens
		$options_matrix_parens = new ilSelectInputGUI($this->getPlugin()->txt('options_matrix_parens'), 'options_matrix_parens');
		$options_matrix_parens->setInfo($this->getPlugin()->txt('options_matrix_parens_info'));
		$options_matrix_parens->setOptions(array("[" => "[", "(" => "(", "" => "", "{" => "{", "|" => "|"));

		//How to solve
		$how_to_solve = new ilTextAreaInputGUI($this->getPlugin()->txt('options_how_to_solve'), 'options_how_to_solve');
		$how_to_solve_info_text = $this->getPlugin()->txt('options_how_to_solve_info') . "</br>";
		$how_to_solve_info_text .= $this->addInfoTooltip("cas_text");
		$how_to_solve->setInfo($how_to_solve_info_text);

		$this->getQuestionGUI()->setRTESupport($how_to_solve);

		//Set value if exists if not default values
		if (isset($this->new_question) and $this->new_question === true) {
			$options_question_simplify->setChecked((int)$this->default["options_question_simplify"] ?? false);
			$options_assume_positive->setChecked((int)$this->default["options_assume_positive"] ?? false);
			$options_prt_correct->setValue($this->default["options_prt_correct"]);
			$options_prt_partially_correct->setValue($this->default["options_prt_partially_correct"]);
			$options_prt_incorrect->setValue($this->default["options_prt_incorrect"]);
			$options_multiplication_sign->setValue($this->default["options_multiplication_sign"]);
			$options_sqrt_sign->setChecked((int)$this->default["options_sqrt_sign"] ?? false);
			$options_complex_numbers->setValue($this->default["options_complex_numbers"]);
			$options_inverse_trigonometric->setValue($this->default["options_inverse_trigonometric"]);
			$options_matrix_parens->setValue($this->default["options_matrix_parens"] ?? false);
		} else {
			$options_question_simplify->setChecked($options->get_option('simplify') ?? false);
			$options_assume_positive->setChecked((int)$options->get_option('assumepos') ?? false);
			$options_prt_correct->setValue($this->getQuestionGUI()->object->prt_correct);
			$options_prt_partially_correct->setValue($this->getQuestionGUI()->object->prt_partially_correct);
			$options_prt_incorrect->setValue($this->getQuestionGUI()->object->prt_incorrect);
			$options_multiplication_sign->setValue($options->get_option('multiplicationsign'));
			$options_sqrt_sign->setChecked($options->get_option('sqrtsign') ?? false);
			$options_complex_numbers->setValue($options->get_option('complexno'));
			$options_inverse_trigonometric->setValue($options->get_option('inversetrig'));
			$options_matrix_parens->setValue($options->get_option('matrixparens'));
		}

		$how_to_solve->setValue($this->getQuestionGUI()->object->general_feedback);

		//Add to form
		$part->addFormProperty($options_question_simplify);
		$part->addFormProperty($options_assume_positive);
		$part->addFormProperty($options_prt_correct);
		$part->addFormProperty($options_prt_partially_correct);
		$part->addFormProperty($options_prt_incorrect);
		$part->addFormProperty($options_multiplication_sign);
		$part->addFormProperty($options_sqrt_sign);
		$part->addFormProperty($options_complex_numbers);
		$part->addFormProperty($options_inverse_trigonometric);
		$part->addFormProperty($options_matrix_parens);
		$part->addFormProperty($how_to_solve);

		return $part;
	}

	/**
	 * Get input part
	 * @param stack_input $input
	 * @return ilMultipartFormPart
	 */
	public function getInputPart(stack_input $input)
	{
		$input_name = $input->get_name();

		$part = new ilMultipartFormPart($this->getPlugin()->txt('show_input') . ' ' . $input_name);

		$input_type = new ilSelectInputGUI($this->getPlugin()->txt('input_type'), $input_name . '_input_type');
		//TODO CHANGE TO STACK METHOD
		$input_type->setOptions(array("algebraic" => $this->getPlugin()->txt('input_type_algebraic'),
			"boolean" => $this->getPlugin()->txt('input_type_boolean'),
			"matrix" => $this->getPlugin()->txt('input_type_matrix'),
			"singlechar" => $this->getPlugin()->txt('input_type_singlechar'),
			"textarea" => $this->getPlugin()->txt('input_type_textarea'),
			"checkbox" => $this->getPlugin()->txt('input_type_checkbox'),
			"dropdown" => $this->getPlugin()->txt('input_type_dropdown'),
			"equiv" => $this->getPlugin()->txt('input_type_equiv'),
			"notes" => $this->getPlugin()->txt('input_type_notes'),
			"radio" => $this->getPlugin()->txt('input_type_radio'),
			"units" => $this->getPlugin()->txt('input_type_units'),
			"string" => $this->getPlugin()->txt('input_type_string'),
			"numerical" => $this->getPlugin()->txt('input_type_numerical')));
		$input_type->setInfo($this->getPlugin()->txt('input_type_info'));
		$input_type->setRequired(TRUE);

		$input_model_answer = new ilTextInputGUI($this->getPlugin()->txt('input_model_answer'), $input_name . '_input_model_answer');
		$input_model_answer_info_text = $this->getPlugin()->txt('input_model_answer_info') . "</br>";
		$input_model_answer_info_text .= $this->addInfoTooltip("cas_expression");
		$input_model_answer->setInfo($input_model_answer_info_text);
		$input_model_answer->setRequired(TRUE);

		$input_box_size = new ilTextInputGUI($this->getPlugin()->txt('input_box_size'), $input_name . '_input_box_size');
		$input_box_size->setInfo($this->getPlugin()->txt('input_box_size_info'));

		$input_strict_syntax = new ilCheckboxInputGUI($this->getPlugin()->txt('input_strict_syntax'), $input_name . '_input_strict_syntax');
		$input_strict_syntax->setInfo($this->getPlugin()->txt("input_strict_syntax_info"));

		$input_insert_stars = new ilSelectInputGUI($this->getPlugin()->txt('input_insert_stars'), $input_name . '_input_insert_stars');
		$input_insert_stars->setOptions(array(
			"0" => $this->getPlugin()->txt('input_stars_no_stars'),
			"1" => $this->getPlugin()->txt('input_stars_implied'),
			"2" => $this->getPlugin()->txt('input_stars_singlechar'),
			"3" => $this->getPlugin()->txt('input_stars_spaces'),
			"4" => $this->getPlugin()->txt('input_stars_implied_spaces'),
			"5" => $this->getPlugin()->txt('input_type_implied_spaces_single')));

		$input_insert_stars->setInfo($this->getPlugin()->txt("input_insert_stars_info"));

		$input_syntax_hint = new ilTextInputGUI($this->getPlugin()->txt('input_syntax_hint'), $input_name . '_input_syntax_hint');
		$input_syntax_hint->setInfo($this->getPlugin()->txt('input_syntax_hint_info'));

		$input_forbidden_words = new ilTextInputGUI($this->getPlugin()->txt('input_forbidden_words'), $input_name . '_input_forbidden_words');
		$input_forbidden_words->setInfo($this->getPlugin()->txt('input_forbidden_words_info'));

		$input_allow_words = new ilTextInputGUI($this->getPlugin()->txt('input_allow_words'), $input_name . '_input_allow_words');
		$input_allow_words->setInfo($this->getPlugin()->txt('input_allow_words_info'));

		$input_forbid_float = new ilCheckboxInputGUI($this->getPlugin()->txt('input_forbid_float'), $input_name . '_input_forbid_float');
		$input_forbid_float->setInfo($this->getPlugin()->txt("input_forbid_float_info"));

		$input_require_lowest_terms = new ilCheckboxInputGUI($this->getPlugin()->txt('input_require_lowest_terms'), $input_name . '_input_require_lowest_terms');
		$input_require_lowest_terms->setInfo($this->getPlugin()->txt("input_require_lowest_terms_info"));

		$input_check_answer_type = new ilCheckboxInputGUI($this->getPlugin()->txt('input_check_answer_type'), $input_name . '_input_check_answer_type');
		$input_check_answer_type->setInfo($this->getPlugin()->txt("input_check_answer_type_info"));

		$input_must_verify = new ilCheckboxInputGUI($this->getPlugin()->txt('input_must_verify'), $input_name . '_input_must_verify');
		$input_must_verify->setInfo($this->getPlugin()->txt("input_must_verify_info"));

		$input_show_validation = new ilSelectInputGUI($this->getPlugin()->txt('input_show_validation'), $input_name . '_input_show_validation');
		$input_show_validation->setOptions(array(0 => $this->getPlugin()->txt('show_validation_no'), 1 => $this->getPlugin()->txt('show_validation_yes_with_vars'), 2 => $this->getPlugin()->txt('show_validation_yes_without_vars')));
		$input_show_validation->setInfo($this->getPlugin()->txt("input_show_validation_info"));

		$input_options = new ilTextInputGUI($this->getPlugin()->txt('input_options'), $input_name . '_input_options');
		$input_options_info_text = $this->getPlugin()->txt('input_options_info') . "</br>";
		$input_options_info_text .= $this->addInfoTooltip("cas_expression");
		$input_options->setInfo($input_options_info_text);

		//Set value if exists if not default values
        if (isset($this->new_question) and $this->new_question === true) {
			$input_type->setValue($this->default["input_type"]);
			//$input_model_answer->setValue($this->default[""]);
			$input_box_size->setValue($this->default["input_box_size"]);
			$input_strict_syntax->setChecked((int)$this->default["input_strict_syntax"] ?? false);
			$input_insert_stars->setValue((int)$this->default["input_insert_stars"]);
			$input_syntax_hint->setValue($this->default["input_syntax_hint"]);
			$input_forbidden_words->setValue($this->default["input_forbidden_words"]);
			$input_allow_words->setValue($this->default["input_allow_words"]);
			$input_forbid_float->setChecked((int)$this->default["input_forbid_float"] ?? false);
			$input_require_lowest_terms->setChecked((int)$this->default["input_require_lowest_terms"] ?? false);
			$input_check_answer_type->setChecked((int)$this->default["input_check_answer_type"] ?? false);
			$input_must_verify->setChecked((int)$this->default["input_must_verify"] ?? false);
			$input_show_validation->setValue((int)$this->default["input_show_validation"]);
			$input_options->setValue($this->default["input_extra_options"]);
		} else {
			$input_type->setValue(assStackQuestionUtils::_getInputType($input));
			$input_model_answer->setValue($input->get_teacher_answer());
			$input_box_size->setValue($input->get_parameter('boxWidth'));
			$input_strict_syntax->setChecked($input->get_parameter('strictSyntax') ?? false);
			$input_insert_stars->setValue($input->get_parameter('insertStars'));
			$input_syntax_hint->setValue($input->get_parameter('syntaxHint'));
			$input_forbidden_words->setValue($input->get_parameter('forbidWords'));
			$input_allow_words->setValue($input->get_parameter('allowWords'));
			$input_forbid_float->setChecked($input->get_parameter('forbidFloats') ?? false);
			$input_require_lowest_terms->setChecked($input->get_parameter('lowestTerms') ?? false);
			$input_check_answer_type->setChecked($input->get_parameter('sameType') ?? false);
			$input_must_verify->setChecked($input->get_parameter('mustVerify') ?? false);
			$input_show_validation->setValue($input->get_parameter('showValidation'));
			$input_options->setValue($input->get_parameter('options'));
		}

		//Add form properties
		$part->addFormProperty($input_type);
		$part->addFormProperty($input_model_answer);
		$part->addFormProperty($input_box_size);
		$part->addFormProperty($input_strict_syntax);
		$part->addFormProperty($input_insert_stars);
		$part->addFormProperty($input_syntax_hint);
		$part->addFormProperty($input_forbidden_words);
		$part->addFormProperty($input_forbid_float);
		$part->addFormProperty($input_allow_words);
		$part->addFormProperty($input_require_lowest_terms);
		$part->addFormProperty($input_check_answer_type);
		$part->addFormProperty($input_must_verify);
		$part->addFormProperty($input_show_validation);
		$part->addFormProperty($input_options);

		return $part;
	}

	/**
	 * Create PRT Part
	 * @param stack_potentialresponse_tree $prt
	 * @return ilMultipartFormPart
	 */
	public function getPRTPart(stack_potentialresponse_tree $prt)
	{
		$prt_name = $prt->get_name();
		//Create part and columns object
		$part = new ilMultipartFormPart($prt_name);
		$prt_columns_container = new ilColumnsFormPropertyGUI($this->getPlugin()->txt('prt_columns'), 'prt_' . $prt_name . '_columns', 12, TRUE);

		//Add First column for representation
		$graphical_column = new ilMultipartFormPart($this->getPlugin()->txt('graphical_title'));
		try {
			$graphical_column->addFormProperty($this->getGraphicalPart($prt));
		} catch (assStackQuestionException $exception) {
			$this->question_gui->object->setErrors($exception->getMessage());
		}
		$prt_columns_container->addPart($graphical_column, 3);

		//Add Second column for PRT general settings and nodes
		$settings_column = new ilMultipartFormPart($this->getPlugin()->txt('settings_title'));

		//Add general settings
		//Creation of properties of this part
		if ($prt_name == 'new_prt') {
			$prt_name_input = new ilTextInputGUI($this->getPlugin()->txt('prt_name'), 'prt_' . $prt_name . '_name');
		} else {
			$prt_name_input = new ilNonEditableValueGUI($this->getPlugin()->txt('prt_name'), 'prt_' . $prt_name . '_name');
		}
		$prt_name_input->setInfo($this->getPlugin()->txt('prt_name_info'));
		$prt_name_input->setRequired(TRUE);

		//If new question, name first prt directly as prt1
        if (isset($this->new_question) and $this->new_question === true) {
			$prt_name_input->setValue("prt1");
		} else {
			$prt_name_input->setValue($prt_name);
		}
		$settings_column->addFormProperty($prt_name_input);

		$prt_first_node = new ilSelectInputGUI($this->getPlugin()->txt('prt_first_node'), 'prt_' . $prt_name . '_first_node');
		$node_list = array();
		//Get list of nodes
		foreach ($prt->getNodes() as $node_name => $prt_node) {
			$node_list[$node_name] = $node_name;
		}
		$prt_first_node->setOptions($node_list);
		$prt_first_node->setValue($prt->getFirstNode());
		$settings_column->addFormProperty($prt_first_node);

		//Paste node
		if (isset($_SESSION["copy_node"])) {
			$paste_node = new ilButtonFormProperty($this->getPlugin()->txt('paste_node'), 'paste_node_in_' . $prt_name);
			$paste_node->setAction('paste_node_in_' . $prt_name);
			$paste_node->setCommand('save');
			$settings_column->addFormProperty($paste_node);
		}

		//Paste prt
		if (isset($_SESSION["copy_prt"])) {
			$paste_prt = new ilButtonFormProperty($this->getPlugin()->txt('paste_prt'), 'paste_prt');
			$paste_prt->setAction('paste_prt');
			$paste_prt->setCommand('save');
			$settings_column->addFormProperty($paste_prt);
		} else {
			//Copy prt
			$copy_prt = new ilButtonFormProperty($this->getPlugin()->txt('copy_prt'), 'copy_prt_' . $prt_name);
			$copy_prt->setAction('copy_prt_' . $prt_name);
			$copy_prt->setCommand('save');
			$settings_column->addFormProperty($copy_prt);
		}

		//Add Node
		$add_node_to_prt = new ilButtonFormProperty($this->getPlugin()->txt('add_node'), 'add_node_to_' . $prt_name);
		$add_node_to_prt->setAction('add_node_to_' . $prt_name);
		$add_node_to_prt->setCommand('save');
		$settings_column->addFormProperty($add_node_to_prt);

		$settings_column->addFormProperty($this->getSettingsPart($prt, 12));
		//Add node pos neg part
		$settings_column->addFormProperty($this->getNodesPart($prt, 12));
		$prt_columns_container->addPart($settings_column, 9);

		//Add columns property and set inner division
		$part->addFormProperty($prt_columns_container);
		$prt_columns_container->setWidthDivision(array('title' => 0, 'content' => 12, 'footer' => 0));

		return $part;
	}

	/**
	 * Creates the graph for each PRT
	 * @param stack_potentialresponse_tree $prt
	 * @return ilCustomInputGUI
	 */
	public function getGraphicalPart(stack_potentialresponse_tree $prt)
	{
		//Graph Creation procedure
		$graph = new stack_abstract_graph();
		$first_node_name = $prt->getFirstNode();
		$nodes = array();

		$prt_name = $prt->get_name();

		//Show all nodes
		foreach ($prt->getNodes() as $node_name => $node) {
			$nodes[$node_name] = $node;
		}

		foreach ($nodes as $node_name => $node) {
			if (is_a($node, "stack_potentialresponse_node")) {
				$branches_info = $node->summarise_branches();
				if ($branches_info->truenextnode == -1) {
					$left = null;
				} else {
					$left = $branches_info->truenextnode;
				}
				if ($branches_info->falsenextnode == -1) {
					$right = null;
				} else {
					$right = $branches_info->falsenextnode;
				}
				$graph->add_node($node_name, $left, $right, $branches_info->truescoremode . round($branches_info->truescore, 2), $branches_info->falsescoremode . round($branches_info->falsescore, 2), '#fgroup_id_' . $prt_name . 'node_' . $node_name);
			}
		}

		//Renderisation
		try {
			$graph->layout();
			$svg = stack_abstract_graph_svg_renderer::render($graph, $prt_name . 'graphsvg');
		} catch (stack_exception $e) {
			ilUtil::sendFailure($e->getMessage(), true);
		}

		//TODO Create new class to avoid deprecated custom property
		$form_property = new ilCustomInputGUI($this->getPlugin()->txt('prt_graph'), 'prt_' . $prt_name . '_graphical');
		$form_property->setHtml($svg);

		return $form_property;
	}

	/**
	 * The settings part is an accordeon with just one part for the general settings of each PRT
	 * @param stack_potentialresponse_tree $prt
	 * @param string $container_width
	 * @return ilAccordionFormPropertyGUI
	 */
	public function getSettingsPart(stack_potentialresponse_tree $prt, $container_width = "")
	{
		global $DIC;

		$lng = $DIC->language();
		$prt_name = $prt->get_name();

		//Creation of Form properties
		$part = new ilAccordionFormPropertyGUI($this->getPlugin()->txt('prt_settings_and_nodes'), 'prt_' . $prt_name . '_settings', 12, TRUE);
		$settings = new ilMultipartFormPart($this->getPlugin()->txt('prt_settings'));

		$prt_value = new ilTextInputGUI($this->getPlugin()->txt('prt_value'), 'prt_' . $prt_name . '_value');
		$prt_value->setInfo($this->getPlugin()->txt('prt_value_info'));

		$prt_simplify = new ilSelectInputGUI($this->getPlugin()->txt('prt_simplify'), 'prt_' . $prt_name . '_simplify');
		$prt_simplify->setOptions(array(TRUE => $lng->txt('yes'), FALSE => $lng->txt('no'),));
		$prt_simplify->setInfo($this->getPlugin()->txt('prt_simplify_info'));

		$prt_feedback_variables = new ilTextAreaInputGUI($this->getPlugin()->txt('prt_feedback_variables'), 'prt_' . $prt_name . '_feedback_variables');
		$prt_feedback_variables_info_text = $this->getPlugin()->txt('prt_feedback_variables_info') . "</br>";
		$prt_feedback_variables_info_text .= $this->addInfoTooltip("cas_expression");
		$prt_feedback_variables->setInfo($prt_feedback_variables_info_text);

		//$delete_prt = new ilButtonFormProperty($this->getPlugin()->txt('delete_prt'), 'delete_full_prt_' . $prt_name);
		//$delete_prt->setAction('delete_full_prt_' . $prt_name);
		//$delete_prt->setCommand('save');

		//Set value of parts in case there are parts.
		$prt_value->setValue($prt->get_value());
		//Set value if exists if not default values
        if (isset($this->new_question) and $this->new_question === true) {
			$prt_simplify->setValue($this->default["prt_simplify"]);
		} else {
			$prt_simplify->setValue($prt->isSimplify());
		}
		$prt_feedback_variables->setValue($prt->get_feedbackvariables_keyvals());

		//Add properties to form
		$settings->addFormProperty($prt_value);
		$settings->addFormProperty($prt_simplify);
		$settings->addFormProperty($prt_feedback_variables);
		//$settings->addFormProperty($delete_prt);

		$part->addPart($settings);

		return $part;
	}

	/**
	 * Tabs for different nodes
	 * @param stack_potentialresponse_tree $prt
	 * @param string $container_width
	 * @return ilTabsFormPropertyGUI
	 */
	public function getNodesPart(stack_potentialresponse_tree $prt, $container_width = "")
	{
		//Creation of tabs property
		$nodes = new ilTabsFormPropertyGUI($this->getPlugin()->txt('prt_nodes'), 'prt_' . $prt->get_name() . '_nodes', $container_width, FALSE);

		$q_nodes = $prt->getNodes();
		if (!empty($q_nodes)) {
			foreach ($q_nodes as $node_name => $node) {
				if ($prt->getFirstNode() == $node_name) {
					$first_node = $node;
					unset($q_nodes[$node_name]);
					array_unshift($q_nodes, $first_node);
				}
			}
		}
		//Add tab per node in the current PRT
		if (!empty($q_nodes)) {
			foreach ($q_nodes as $node) {
				$node_part = $this->getNodePart($prt, $node);
				$node_part->setType($prt->get_name() . '-' . $node->nodeid);
				$nodes->addPart($node_part);
			}
		}

		//TODO
		//Add new node tab if not in new prt tab
		/*
		if ($prt->get_name() != 'new_prt') {
			$new_prt_node = new stack_potentialresponse_node(-1, $this->getQuestionGUI()->object->getId(), $prt->get_name(), 0, -1, -1);
			//https://mantis.ilias.de/view.php?id=25290

			/*
			$new_prt_node->setAnswerTest($this->default["prt_node_answer_test"]);
			$new_prt_node->setQuiet($this->default["prt_node_quiet"]);
			$new_prt_node->setTestOptions($this->default["prt_node_options"]);

			$new_prt_node->setTrueScoreMode($this->default["prt_pos_mod"]);
			$new_prt_node->setFalseScoreMode($this->default["prt_neg_mod"]);

			$new_prt_node->setTrueScore($this->default["prt_pos_score"]);
			$new_prt_node->setFalseScore($this->default["prt_neg_score"]);

			$new_prt_node->setTruePenalty($this->default["prt_pos_penalty"]);
			$new_prt_node->setFalsePenalty($this->default["prt_neg_penalty"]);

			$new_prt_node->checkPRTNode(TRUE);
			$new_node_part = $this->getNodePart($prt, $new_prt_node);
			$new_node_part->setTitle($this->getPlugin()->txt('add_new_node'));
			$new_node_part->setType($prt->get_name() . '_new_node');

			$nodes->addPart($new_node_part);
		}*/

		//Set width division within the tab content.
		$nodes->setWidthDivision(array('title' => 0, 'content' => 12, 'footer' => 0));

		return $nodes;
	}

	/**
	 * Gte content for each tab, two columns.
	 * @param stack_potentialresponse_tree $prt
	 * @param stack_potentialresponse_node $node
	 * @return ilMultipartFormPart
	 */
	public function getNodePart(stack_potentialresponse_tree $prt, stack_potentialresponse_node $node)
	{
		//Create columns property
		$part = new ilMultipartFormPart($node->nodeid);

		$positive_negative_columns = new ilColumnsFormPropertyGUI($this->getPlugin()->txt('prt_node_posneg'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_positive_negative', 12, TRUE);

		//Add positive and negative columns (both width half width of the content)
		$positive_negative_columns->addPart($this->getNodePositivePart($prt, $node), 6);
		$positive_negative_columns->addPart($this->getNodeNegativePart($prt, $node), 6);

		//Set width division within the columns and add property
		$positive_negative_columns->setWidthDivision(array('title' => 5, 'content' => 7, 'footer' => 0));

		//Add common , positive and negative parts.
		$part->addFormProperty($this->getCommonNodePart($prt, $node));
		$part->addFormProperty($positive_negative_columns);

		return $part;
	}

	/**
	 * @param stack_potentialresponse_tree $prt
	 * @param stack_potentialresponse_node $node
	 * @return ilColumnsFormPropertyGUI
	 */
	public function getCommonNodePart(stack_potentialresponse_tree $prt, stack_potentialresponse_node $node)
	{
		global $DIC;

		$lng = $DIC->language();

		$common_column = new ilColumnsFormPropertyGUI($this->getPlugin()->txt('prt_node_common'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_common', 12, TRUE);

		//Common part
		$common_node_part = new ilMultipartFormPart($node->nodeid . '_common');
		$common_node_part->setType('common_node');

		//Creation of Form properties
		$answer_test = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_answer_test'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_answer_test');
		// Prepare answer test types.
		$answertests = stack_ans_test_controller::get_available_ans_tests();
		$answertestchoices = array();
		foreach ($answertests as $test => $string) {
			$answertestchoices[$test] = stack_string($string);
		}
		$answer_test->setOptions($answertestchoices);
		$answer_test->setInfo($this->getPlugin()->txt('prt_node_answer_test_info'));

		$node_student_answer = new ilTextInputGUI($this->getPlugin()->txt('prt_node_student_answer'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_student_answer');
		$node_student_answer_info_text = $this->getPlugin()->txt('prt_node_student_answer_info') . "</br>";
		$node_student_answer_info_text .= $this->addInfoTooltip("cas_expression");
		$node_student_answer->setInfo($node_student_answer_info_text);
		$node_student_answer->setRequired(TRUE);

		$node_teacher_answer = new ilTextInputGUI($this->getPlugin()->txt('prt_node_teacher_answer'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_teacher_answer');
		$node_teacher_answer_info_text = $this->getPlugin()->txt('prt_node_teacher_answer_info') . "</br>";
		$node_teacher_answer_info_text .= $this->addInfoTooltip("cas_expression");
		$node_teacher_answer->setInfo($node_teacher_answer_info_text);
		$node_teacher_answer->setRequired(TRUE);

		$node_options = new ilTextInputGUI($this->getPlugin()->txt('prt_node_options'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_options');
		$node_options_info_text = $this->getPlugin()->txt('prt_node_options_info') . "</br>";
		$node_options_info_text .= $this->addInfoTooltip("cas_expression");
		$node_options->setInfo($node_options_info_text);

		$node_quiet = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_quiet'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_quiet');
		$node_quiet->setOptions(array(true => $lng->txt('yes'), false => $lng->txt('no'),));
		$node_quiet->setInfo($this->getPlugin()->txt('prt_node_quiet_info'));

        if (isset($this->new_question) and $this->new_question === true) {
			$answer_test->setValue($this->default["prt_node_answer_test"]);
			//$node_student_answer->setValue($this->default[""]);
			//$node_teacher_answer->setValue($this->default[""]);
			$node_options->setValue($this->default["prt_node_options"]);
			$node_quiet->setValue($this->default["prt_node_quiet"]);
		} else {
			$answer_test->setValue($node->get_test());
			$node_student_answer->setValue($node->getRawSans() == " " ? '' : $node->getRawSans());
			$node_teacher_answer->setValue($node->getRawTans() == " " ? '' : $node->getRawTans());
			$node_options->setValue((string)$node->getAtoptions());
			$node_quiet->setValue($node->isQuiet() ? 1 : 0);
		}

		$common_node_part->addFormProperty($answer_test);
		$common_node_part->addFormProperty($node_student_answer);
		$common_node_part->addFormProperty($node_teacher_answer);
		$common_node_part->addFormProperty($node_options);
		$common_node_part->addFormProperty($node_quiet);

		if ($node->nodeid !== $prt->get_name() . '_new_node') {
			$delete_node = new ilButtonFormProperty($this->getPlugin()->txt('delete_node'), 'delete_prt_' . $prt->get_name() . '_node_' . $node->nodeid);
			$delete_node->setAction('delete_prt_' . $prt->get_name() . '_node_' . $node->nodeid);
			$delete_node->setCommand('save');
			$common_node_part->addFormProperty($delete_node);

			//Copy node
			$copy_node = new ilButtonFormProperty($this->getPlugin()->txt('copy_node'), 'copy_prt_' . $prt->get_name() . '_node_' . $node->nodeid);
			$copy_node->setAction('copy_prt_' . $prt->get_name() . '_node_' . $node->nodeid);
			$copy_node->setCommand('save');
			$common_node_part->addFormProperty($copy_node);
		} else {
			$creation_node_info = new ilNonEditableValueGUI($this->getPlugin()->txt('node_creation_hint'));
			$creation_node_info->setValue($this->getPlugin()->txt('node_creation_hint_text'));
			$common_node_part->addFormProperty($creation_node_info);
		}

		$common_column->addPart($common_node_part, 12);
		$common_column->setWidthDivision(array('title' => 5, 'content' => 7, 'footer' => 0));

		return $common_column;
	}

	/**
	 * Get content for positive column in node
	 * @param stack_potentialresponse_tree $prt
	 * @param stack_potentialresponse_node $node
	 * @return ilMultipartFormPart
	 */
	public function getNodePositivePart(stack_potentialresponse_tree $prt, stack_potentialresponse_node $node): ilMultipartFormPart
	{
		//Create part and set parameters for customisation
		$positive_part = new ilMultipartFormPart($this->getPlugin()->txt('node_pos_title'));
		$positive_part->setType('positive_column');

		//Creation of Form properties
		$node_pos_mode = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_pos_mod'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_pos_mod');
		$node_pos_mode->setOptions(array("=" => "=", "+" => "+", "-" => "-"));
		$node_pos_mode->setInfo($this->getPlugin()->txt('prt_node_pos_mod_info'));

		$node_pos_score = new ilTextInputGUI($this->getPlugin()->txt('prt_node_pos_score'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_pos_score');
		$node_pos_score->setInfo($this->getPlugin()->txt('prt_node_pos_score_info'));

		$node_pos_penalty = new ilTextInputGUI($this->getPlugin()->txt('prt_node_pos_penalty'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_pos_penalty');
		$node_pos_penalty->setInfo($this->getPlugin()->txt('prt_node_pos_penalty_info'));

		$node_pos_next_node = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_pos_next'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_pos_next');
		$node_list = array(-1 => $this->getPlugin()->txt('end'));

		//Get list of nodes
		foreach ($prt->get_nodes_summary() as $node_name => $prt_node) {
			if ($node_name != $node->nodeid) {
				$node_list[$node_name] = $node_name;
			}
		}

		$node_pos_next_node->setOptions($node_list);
		$node_pos_next_node->setInfo($this->getPlugin()->txt('prt_node_pos_next_info'));

		$node_pos_answernote = new ilTextInputGUI($this->getPlugin()->txt('prt_node_pos_answernote'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_pos_answernote');
		$node_pos_answernote->setInfo($this->getPlugin()->txt('prt_node_pos_answernote_info'));

		$node_pos_specific_feedback = new ilTextAreaInputGUI($this->getPlugin()->txt('prt_node_pos_specific_feedback'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_pos_specific_feedback');
		$node_pos_specific_feedback_info_text = $this->getPlugin()->txt('prt_node_pos_specific_feedback_info') . "</br>";
		$node_pos_specific_feedback_info_text .= $this->addInfoTooltip("cas_text");
		$node_pos_specific_feedback->setInfo($node_pos_specific_feedback_info_text);

		$node_pos_feedback_class = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_pos_feedback_class'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_pos_feedback_class');
		$node_pos_feedback_class->setOptions($this->getFeedbackOptions());
		$node_pos_feedback_class->setInfo($this->getPlugin()->txt('prt_node_pos_feedback_class_info'));

		$this->getQuestionGUI()->setRTESupport($node_pos_specific_feedback);

		//Set value if exists if not default values
        if (isset($this->new_question) and $this->new_question === true) {
			$node_pos_mode->setValue($this->default["prt_pos_mod"]);
			$node_pos_score->setValue($this->default["prt_pos_score"]);
			$node_pos_penalty->setValue($this->default["prt_pos_penalty"]);
			//$node_pos_next_node->setValue($this->default[""]);
			$node_pos_answernote->setValue($this->default["prt_pos_answernote"]);
			//$node_pos_specific_feedback->setValue($this->default[""]);
			$node_pos_feedback_class->setValue(1);
		} else {
			$node_data = $node->summarise_branches();
			$feedback_data = $node->getFeedbackFromNode();

			$node_pos_mode->setValue($node_data->truescoremode);
			$node_pos_score->setValue($node_data->truescore);
			$node_pos_next_node->setValue($node_data->truenextnode);
			$node_pos_answernote->setValue($node_data->truenote);

			$node_pos_penalty->setValue($feedback_data['true_penalty']);
			$node_pos_specific_feedback->setValue($feedback_data['true_feedback']);
			$node_pos_feedback_class->setValue($feedback_data['true_feedback_format']);
		}


		//Add part to form
		$positive_part->addFormProperty($node_pos_mode);
		$positive_part->addFormProperty($node_pos_score);
		$positive_part->addFormProperty($node_pos_penalty);
		if ($node->nodeid !== $prt->get_name() . '_new_node') {
			$positive_part->addFormProperty($node_pos_next_node);
		}
		$positive_part->addFormProperty($node_pos_answernote);
		$positive_part->addFormProperty($node_pos_specific_feedback);
		$positive_part->addFormProperty($node_pos_feedback_class);

		return $positive_part;
	}

	/**
	 * Get content for negative column in node*
	 * @param stack_potentialresponse_tree $prt
	 * @param stack_potentialresponse_node $node
	 * @return ilMultipartFormPart
	 */
	public function getNodeNegativePart(stack_potentialresponse_tree $prt, stack_potentialresponse_node $node): ilMultipartFormPart
	{
		//Create part and set parameters for customisation
		$negative_part = new ilMultipartFormPart($this->getPlugin()->txt('node_neg_title'));
		$negative_part->setType('negative_column');

		//Creation of Form properties
		$node_neg_mode = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_neg_mod'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_neg_mod');
		$node_neg_mode->setOptions(array("=" => "=", "+" => "+", "-" => "-"));
		$node_neg_mode->setInfo($this->getPlugin()->txt('prt_node_neg_mod_info'));

		$node_neg_score = new ilTextInputGUI($this->getPlugin()->txt('prt_node_neg_score'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_neg_score');
		$node_neg_score->setInfo($this->getPlugin()->txt('prt_node_neg_score_info'));

		$node_neg_penalty = new ilTextInputGUI($this->getPlugin()->txt('prt_node_neg_penalty'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_neg_penalty');
		$node_neg_penalty->setInfo($this->getPlugin()->txt('prt_node_neg_penalty_info'));

		$node_neg_next_node = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_neg_next'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_neg_next');
		$node_list = array(-1 => $this->getPlugin()->txt('end'));

		//Get list of nodes
		foreach ($prt->get_nodes_summary() as $node_name => $prt_node) {
			if ($node_name != $node->nodeid) {
				$node_list[$node_name] = $node_name;
			}
		}

		$node_neg_next_node->setOptions($node_list);
		$node_neg_next_node->setInfo($this->getPlugin()->txt('prt_node_neg_next_info'));

		$node_neg_answernote = new ilTextInputGUI($this->getPlugin()->txt('prt_node_neg_answernote'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_neg_answernote');
		$node_neg_answernote->setInfo($this->getPlugin()->txt('prt_node_neg_answernote_info'));

		$node_neg_specific_feedback = new ilTextAreaInputGUI($this->getPlugin()->txt('prt_node_neg_specific_feedback'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_neg_specific_feedback');
		$node_neg_specific_feedback_info_text = $this->getPlugin()->txt('prt_node_neg_specific_feedback_info') . "</br>";
		$node_neg_specific_feedback_info_text .= $this->addInfoTooltip("cas_text");
		$node_neg_specific_feedback->setInfo($node_neg_specific_feedback_info_text);

		$node_neg_feedback_class = new ilSelectInputGUI($this->getPlugin()->txt('prt_node_neg_feedback_class'), 'prt_' . $prt->get_name() . '_node_' . $node->nodeid . '_neg_feedback_class');
		$node_neg_feedback_class->setOptions($this->getFeedbackOptions());
		$node_neg_feedback_class->setInfo($this->getPlugin()->txt('prt_node_neg_feedback_class_info'));

		$this->getQuestionGUI()->setRTESupport($node_neg_specific_feedback);

		//Set value if exists if not default values
        if (isset($this->new_question) and $this->new_question === true) {
			$node_neg_mode->setValue($this->default["prt_neg_mod"]);
			$node_neg_score->setValue($this->default["prt_neg_score"]);
			$node_neg_penalty->setValue($this->default["prt_neg_penalty"]);
			//$node_neg_next_node->setValue($this->default[""]);
			$node_neg_answernote->setValue($this->default["prt_neg_answernote"]);
			//$node_neg_specific_feedback->setValue($this->default[""]);
			$node_neg_feedback_class->setValue(1);
		} else {
			$node_data = $node->summarise_branches();
			$feedback_data = $node->getFeedbackFromNode();

			$node_neg_mode->setValue($node_data->falsescoremode);
			$node_neg_score->setValue($node_data->falsescore);
			$node_neg_next_node->setValue($node_data->falsenextnode);
			$node_neg_answernote->setValue($node_data->falsenote);

			$node_neg_penalty->setValue($feedback_data['false_penalty']);
			$node_neg_specific_feedback->setValue($feedback_data['false_feedback']);
			$node_neg_feedback_class->setValue($feedback_data['false_feedback_format']);
		}

		//Add properties to form
		$negative_part->addFormProperty($node_neg_mode);
		$negative_part->addFormProperty($node_neg_score);
		$negative_part->addFormProperty($node_neg_penalty);
		if ($node->nodeid !== $prt->get_name() . '_new_node') {
			$negative_part->addFormProperty($node_neg_next_node);
		}
		$negative_part->addFormProperty($node_neg_answernote);
		$negative_part->addFormProperty($node_neg_specific_feedback);
		$negative_part->addFormProperty($node_neg_feedback_class);


		return $negative_part;
	}

	/**
	 * This function shows error messages from CAS validation and also from ILIAS validation
	 */
	public function manageErrorMessages()
	{

		//Check Maxima Connection
		if (!$this->getQuestionGUI()->object->checkMaximaConnection()) {
			ilUtil::sendFailure($this->getPlugin()->txt('hc_connection_status_display_error'), true);
		}

		// If exists error messages stored in session
		$session_error_message = "";
		$session_info_message = "";

		if (isset($_SESSION["stack_authoring_errors"][$this->getQuestionGUI()->object->getId()])) {
			if (!empty($_SESSION["stack_authoring_errors"][$this->getQuestionGUI()->object->getId()])) {
				foreach ($_SESSION["stack_authoring_errors"][$this->getQuestionGUI()->object->getId()] as $session_error) {
					$session_error_message .= $session_error . "</br>";
				}
			}
		}

		//Clean session errors
		$_SESSION["stack_authoring_errors"][$this->getQuestionGUI()->object->getId()] = array();

		//Add </br> if there are ilias validation message between it and session error message
		if ($session_error_message != "") {
			ilUtil::sendFailure($session_error_message, TRUE);
		}
	}

	/**
	 * Add a tooltip for the input type of the current input.
	 * @param $a_type
	 * @return bool|string
	 */
	public function addInfoTooltip($a_type)
	{
		$comment_id = rand(100000, 999999);
		$text = "";

		switch ($a_type) {
			case "cas_expression":
				ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $this->getPlugin()->txt("casexpression_info"));
				$text .= "<span id=\"ilAssStackQuestion" . $comment_id . "\">" . $this->getPlugin()->txt('info_allowed') . "<a href='javascript:;'> " . $this->getPlugin()->txt('casexpression_name') . "</a>" . "</span>";
				break;
			case "cas_text":
				ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $this->getPlugin()->txt("castext_info"));
				$text .= "<span id=\"ilAssStackQuestion" . $comment_id . "\">" . $this->getPlugin()->txt('info_allowed') . "<a href='javascript:;'> " . $this->getPlugin()->txt('castext_name') . "</a>" . "</span>";
				break;
			case "html":
				ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $this->getPlugin()->txt("html_info"));
				$text .= "<span id=\"ilAssStackQuestion" . $comment_id . "\">" . $this->getPlugin()->txt('info_allowed') . "<a href='javascript:;'> " . $this->getPlugin()->txt('html_name') . "</a>" . "</span>";
				break;
			default:
				$text = FALSE;
		}

		return $text;
	}


	/*
	 * GETTERS AND SETTERS
	 */

	/**
	 * @param \ilassStackQuestionPlugin $plugin
	 */
	public function setPlugin($plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * @return \ilassStackQuestionPlugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}

	/**
	 * @param \assStackQuestionGUI $question_gui
	 */
	public function setQuestionGUI($question_gui)
	{
		$this->question_gui = $question_gui;
	}

	/**
	 * @return \assStackQuestionGUI
	 */
	public function getQuestionGUI()
	{
		return $this->question_gui;
	}

	/**
	 * @param \ilPropertyFormGUI $form
	 */
	public function setForm($form)
	{
		$this->form = $form;
	}

	/**
	 * @return \ilPropertyFormGUI
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * @param \ilTemplate $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * @return \ilTemplate
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	public function getFeedbackOptions()
	{
		global $DIC;
		$lng = $DIC->language();
		$options = array();

		//Add default option

		/*
		 * AS WE ARE USING THE TRUE/FALSE FEEDBACK FORMAT FIELD OF THE DATABASE
		 * WHICH IS NOT USED AT THE MOMENT, AND IS ALWAYS 0 OR 1. WE HAVE TO
		 * DEFINE VALUES FOR EACH OF THE FEEDBACK STYLES, BEGINNING BY 2, TO DISTINGUISH
		 * QUESTION WHICH USES THIS STYLES AND THOSE WHICH NOT.
		 */

		$options[1] = $lng->txt("default");
		$options[2] = $this->getPlugin()->txt("feedback_node_right");
		$options[3] = $this->getPlugin()->txt("feedback_node_wrong");
		$options[4] = $this->getPlugin()->txt("feedback_solution_hint");
		$options[5] = $this->getPlugin()->txt("feedback_extra_info");
		$options[6] = $this->getPlugin()->txt("feedback_plot_feedback");

		return $options;
	}
}