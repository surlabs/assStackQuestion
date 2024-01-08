<?php
declare(strict_types=1);
/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
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

use classes\platform\ilias\StackRandomisationIlias;
use classes\platform\ilias\StackRenderIlias;
use classes\platform\ilias\StackUserResponseIlias;
use classes\platform\StackException;
use classes\ui\author\RandomisationAndSecurityUI;


/**
 * STACK Question GUI
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 * @ingroup    ModulesTestQuestionPool
 * @ilCtrl_isCalledBy assStackQuestionGUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 * @ilCtrl_Calls assStackQuestionGUI: ilFormPropertyDispatchGUI
 *
 */
class assStackQuestionGUI extends assQuestionGUI
{

	/**
	 * @var ilassStackQuestionPlugin
	 */
	private ilassStackQuestionPlugin $plugin;

	//RTE Support variables

	/**
	 *
	 * @var string
	 */
	protected string $rte_module = "xqcas";

	/**
	 * @var array
	 */
	protected array $rte_tags = array();

	/**
	 * Stores the preview data while on preview mode
	 * Otherwise empty
	 * @var array
	 */
	private array $is_preview;

	/**
	 * assStackQuestionGUI constructor.
	 */
	public function __construct($id = -1)
	{
        parent::__construct();

        //Initialize plugin object
        //require_once './Services/Component/classes/class.ilPlugin.php';
        try {
            $plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, 'TestQuestionPool', 'qst', 'assStackQuestion');
            if (!is_a($plugin, 'ilassStackQuestionPlugin')) {
                ilUtil::sendFailure('Not ilassStackQuestionPlugin object', true);
            } else {
                $this->setPlugin($plugin);
            }
        } catch (ilPluginException $e) {
            ilUtil::sendFailure($e, true);
        }

        //Initialize and loads the Stack question from DB
        $this->object = new assStackQuestion();

        if ($id >= 0) {
            try {
                $this->object->loadFromDb($id);

            } catch (stack_exception $e) {
                ilUtil::sendFailure($e, true);
            }
        }
        //Initialize some STACK required parameters
        require_once __DIR__ . '/utils/class.assStackQuestionInitialization.php';
        require_once(__DIR__ . '/utils/locallib.php');
    }

    /**
     * Returns the HTML for the Test View
     * @param $active_id
     * @param $pass
     * @param $is_question_postponed
     * @param $user_post_solutions
     * @param $show_specific_inline_feedback
     * @return false|mixed|string|void|null
     * @throws StackException
     * @throws stack_exception
     */
	public function getTestOutput($active_id, $pass, $is_question_postponed, $user_post_solutions, $show_specific_inline_feedback)
	{
        $seed = assStackQuestionDB::_getSeed("test", $this->object, (int) $active_id);
        $this->object->questionInitialisation($seed, true);
        $user_response = StackUserResponseIlias::getStackUserResponse('test', (int) $this->object->getId(), (int) $active_id);

        //TODO: Check if this is correct.
        // Is it necessary to store so much data in tst_solutions?
        // If it is not necessary to save so much data, do not save it and remove this part of the code.
        $temp_user_response = array();
        foreach ($user_response["inputs"] as $input_name => $input) {
            $temp_user_response[$input_name] = $input["value"];
        }
        $user_response = $temp_user_response;


        $attempt_data = [];

        $attempt_data['response'] = $user_response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = false;
        $display_options['feedback'] = true;

        //Render question
        $question = StackRenderIlias::renderQuestion($attempt_data, $display_options);

        $question = $this->getILIASPage($question);

        return assStackQuestionUtils::_getLatex($question);
	}

	/**
	 * Returns question view with correct response filled in
	 * @param integer $active_id The active user id
	 * @param integer|null $pass The test pass
	 * @param boolean $graphicalOutput Show visual feedback for right/wrong answers
	 * @param boolean $result_output Show the reached points for parts of the question
	 * @param boolean $show_question_only Show the question without the ILIAS content around
	 * @param boolean $show_feedback Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring Show specific information for the manual scoring output
	 * @param bool $show_question_text
	 * @return string
	 */
	public function getSolutionOutput($active_id, $pass = null, $graphicalOutput = false, $result_output = false, $show_question_only = true, $show_feedback = false, $show_correct_solution = false, $show_manual_scoring = false, $show_question_text = true): string
    {
        $seed = assStackQuestionDB::_getSeed($show_correct_solution ? "correct" : "test", $this->object, (int)$active_id);
        $this->object->questionInitialisation($seed, true);
        $user_response =  $this->object->getCorrectResponse();

        if (isset($user_response["inputs"])) {
            //TODO: Check if this is correct.
            // Is it necessary to store so much data in tst_solutions?
            // If it is not necessary to save so much data, do not save it and remove this part of the code.
            $temp_user_response = array();
            foreach ($user_response["inputs"] as $input_name => $input) {
                $temp_user_response[$input_name] = $input["value"];
            }
            $user_response = $temp_user_response;
        }

        $attempt_data = [];

        $attempt_data['response'] = $user_response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = true;
        $display_options['feedback'] = true;

        //Render question
        $question = StackRenderIlias::renderQuestion($attempt_data, $display_options);

        if (!$show_question_only) {
            $question = $this->getILIASPage($question);
        }

        return assStackQuestionUtils::_getLatex($question);
	}

    /**
     * Returns the HTML for the question Preview
     * @param bool $show_question_only
     * @param bool $showInlineFeedback
     * @return string HTML
     * @throws StackException|stack_exception
     */
	public function getPreview($show_question_only = false, $showInlineFeedback = false): string
	{
		global $DIC;

        $seed = assStackQuestionDB::_getSeed("preview", $this->object, $DIC->user()->getId());
        $this->object->questionInitialisation($seed, true);
        $user_response = StackUserResponseIlias::getStackUserResponse('preview', $this->object->getId(), $DIC->user()->getId());

		//Ensure evaluation has been done
		if (empty($this->object->getEvaluation())) {
			$this->object->evaluateQuestion($user_response);
		}

        $attempt_data = [];

        $attempt_data['response'] = $user_response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = false;
        $display_options['feedback'] = $showInlineFeedback;

		//Render question Preview
        $question_preview = StackRenderIlias::renderQuestion($attempt_data, $display_options);

		//Returns output (with page if needed)
		if (!$show_question_only) {
			// get page object output
			$question_preview = $this->getILIASPage($question_preview);
		}

		return assStackQuestionUtils::_getLatex($question_preview);
	}

	/**
	 * Returns the HTML for the specific feedback output
	 * @param $userSolution
	 * @return string HTML Code with the rendered specific feedback text including the general feedback
	 */
	public function getSpecificFeedbackOutput($userSolution): string
	{
        global $DIC;

        $seed = assStackQuestionDB::_getSeed("preview", $this->object, $DIC->user()->getId());
        $this->object->questionInitialisation($seed, true);
        $user_response = StackUserResponseIlias::getStackUserResponse('preview', $this->object->getId(), $DIC->user()->getId());

        $attempt_data = [];

        $attempt_data['response'] = $user_response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = false;

        //Render question specific feedback
        $specific_feedback_preview = StackRenderIlias::renderSpecificFeedback($attempt_data, $display_options);

        return assStackQuestionUtils::_getLatex($specific_feedback_preview);
    }

    /**
     * Evaluates a posted edit form and writes the form data in the question object
     * (called frm generic commands in assQuestionGUI)
     * Converts the data from post into assStackQuestion ($this->object)
     * Called before editQuestion()
     *
     * @return integer    0: question can be saved / 1: form is not complete
     * @throws stack_exception
     */
	public function writePostData($always = FALSE): int
	{
		$hasErrors = !$always && $this->editQuestion(TRUE);
		if (!$hasErrors) {

			$this->questionCheck();

			//Parent
			$this->writeQuestionGenericPostData();
			$this->writeQuestionSpecificPostData();

			//Taxonomies
			$this->saveTaxonomyAssignments();

			return 0;
		}
		return 1;
	}

    /**
     * Writes the data from $_POST into assStackQuestion
     * Called before editQuestion()
     * @throws stack_exception
     */
	public function writeQuestionSpecificPostData()
	{
		//require_once("./Services/RTE/classes/class.ilRTE.php");

		//Question Text - Reload it with RTE (already loaded in writeQuestionGenericPostData())
		$question_text = ((isset($_POST['question']) and $_POST['question'] != null) ? ilUtil::stripSlashes($_POST['question'], true, $this->getRTETags()) : '');
		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($question_text, 1));

		//stack_options
		$options = array();
		$options['simplify'] = ((isset($_POST['options_question_simplify']) and $_POST['options_question_simplify'] != null) ? (int)trim(ilUtil::secureString($_POST['options_question_simplify'])) : '');
		$options['assumepos'] = ((isset($_POST['options_assume_positive']) and $_POST['options_assume_positive'] != null) ? (int)trim(ilUtil::secureString($_POST['options_assume_positive'])) : '');
		$options['multiplicationsign'] = ((isset($_POST['options_multiplication_sign']) and $_POST['options_multiplication_sign'] != null) ? trim(ilUtil::secureString($_POST['options_multiplication_sign'])) : '');
		$options['sqrtsign'] = ((isset($_POST['options_sqrt_sign']) and $_POST['options_sqrt_sign'] != null) ? (int)trim(ilUtil::secureString($_POST['options_sqrt_sign'])) : '');
		$options['complexno'] = ((isset($_POST['options_complex_numbers']) and $_POST['options_complex_numbers'] != null) ? trim(ilUtil::secureString($_POST['options_complex_numbers'])) : '');
		$options['inversetrig'] = ((isset($_POST['options_inverse_trigonometric']) and $_POST['options_inverse_trigonometric'] != null) ? trim(ilUtil::secureString($_POST['options_inverse_trigonometric'])) : '');
		$options['matrixparens'] = ((isset($_POST['options_matrix_parens']) and $_POST['options_matrix_parens'] != null) ? $_POST['options_matrix_parens'] : '');

		try {
			$options = new stack_options($options);
			//SET OPTIONS
			$this->object->options = $options;
		} catch (stack_exception $e) {
			ilUtil::sendFailure($e, true);
		}

		//Load data sent as options but not part of the session options
		$this->object->question_variables = ((isset($_POST['options_question_variables']) and $_POST['options_question_variables'] != null) ? assStackQuestionUtils::_debugText($_POST['options_question_variables']) : '');
		$this->object->question_note = ((isset($_POST['options_question_note']) and $_POST['options_question_note'] != null) ? ilUtil::secureString($_POST['options_question_note']) : '');

		$this->object->specific_feedback = ((isset($_POST['options_specific_feedback']) and $_POST['options_specific_feedback'] != null) ? ilUtil::stripSlashes($_POST['options_specific_feedback'], true, $this->getRTETags()) : '');
		$this->object->specific_feedback_format = 1;

		$this->object->prt_correct = ((isset($_POST['options_prt_correct']) and $_POST['options_prt_correct'] != null) ? ilUtil::stripSlashes($_POST['options_prt_correct'], true, $this->getRTETags()) : '');
		$this->object->prt_correct_format = 1;
		$this->object->prt_partially_correct = ((isset($_POST['options_prt_partially_correct']) and $_POST['options_prt_partially_correct'] != null) ? ilUtil::stripSlashes($_POST['options_prt_partially_correct'], true, $this->getRTETags()) : '');
		$this->object->prt_partially_correct_format = 1;
		$this->object->prt_incorrect = ((isset($_POST['options_prt_incorrect']) and $_POST['options_prt_incorrect'] != null) ? ilUtil::stripSlashes($_POST['options_prt_incorrect'], true, $this->getRTETags()) : '');
		$this->object->prt_incorrect_format = 1;

		$this->object->general_feedback = ((isset($_POST['options_how_to_solve']) and $_POST['options_how_to_solve'] != null) ? ilUtil::stripSlashes($_POST['options_how_to_solve'], true, $this->getRTETags()) : '');

		//TODO
		//$this->object->variants_selection_seed = ?;

		//stack_inputs
		$required_parameters = stack_input_factory::get_parameters_used();

		//load only those inputs appearing in the question text
		foreach (stack_utils::extract_placeholders($this->object->getQuestion(), 'input') as $input_name) if (isset($_POST[$input_name . '_input_type'])) {

			$type = ((isset($_POST[$input_name . '_input_type']) and $_POST[$input_name . '_input_type'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_type'])) : '');

			$all_parameters = array(
				'boxWidth' => ((isset($_POST[$input_name . '_input_box_size']) and $_POST[$input_name . '_input_box_size'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_box_size'])) : ''),
				'strictSyntax' => ((isset($_POST[$input_name . '_input_strict_syntax']) and $_POST[$input_name . '_input_strict_syntax'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_strict_syntax'])) : ''),
				'insertStars' => ((isset($_POST[$input_name . '_input_insert_stars']) and $_POST[$input_name . '_input_insert_stars'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_insert_stars'])) : ''),
				'syntaxHint' => ((isset($_POST[$input_name . '_input_syntax_hint']) and $_POST[$input_name . '_input_syntax_hint'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_syntax_hint'])) : ''),
				'syntaxAttribute' => ((isset($_POST[$input_name . '_input_syntax_attribute']) and $_POST[$input_name . '_input_syntax_attribute'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_syntax_attribute'])) : ''),
				'forbidWords' => ((isset($_POST[$input_name . '_input_forbidden_words']) and $_POST[$input_name . '_input_forbidden_words'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_forbidden_words'])) : ''),
				'allowWords' => ((isset($_POST[$input_name . '_input_allow_words']) and $_POST[$input_name . '_input_allow_words'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_allow_words'])) : ''),
				'forbidFloats' => ((isset($_POST[$input_name . '_input_forbid_float']) and $_POST[$input_name . '_input_forbid_float'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_forbid_float'])) : ''),
				'lowestTerms' => ((isset($_POST[$input_name . '_input_require_lowest_terms']) and $_POST[$input_name . '_input_require_lowest_terms'] != null) ? (bool)trim(ilUtil::secureString($_POST[$input_name . '_input_require_lowest_terms'])) : ''),
				'sameType' => ((isset($_POST[$input_name . '_input_check_answer_type']) and $_POST[$input_name . '_input_check_answer_type'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_check_answer_type'])) : ''),
				'mustVerify' => ((isset($_POST[$input_name . '_input_must_verify']) and $_POST[$input_name . '_input_must_verify'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_must_verify'])) : ''),
				'showValidation' => ((isset($_POST[$input_name . '_input_show_validation']) and $_POST[$input_name . '_input_show_validation'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_show_validation'])) : ''),
				'options' => ((isset($_POST[$input_name . '_input_options']) and $_POST[$input_name . '_input_options'] != null) ? trim(ilUtil::secureString($_POST[$input_name . '_input_options'])) : ''),
			);

			$teacher_answer = ilUtil::secureString($_POST[$input_name . '_input_model_answer']);

			$parameters = array();
			foreach ($required_parameters[$type] as $parameter_name) {
				if ($parameter_name == 'inputType') {
					continue;
				}
				$parameters[$parameter_name] = $all_parameters[$parameter_name];
			}

			//SET INPUTS
			$this->object->inputs[$input_name] = stack_input_factory::make($type, $input_name, $teacher_answer, $this->object->options, $parameters);
		}

		//stack_potentialresponse_tree
		//Values
		$total_value = 0;

		//in ILIAS all attempts are graded
		$grade_all = true;

		//Load only those prt located in the question text or in the specific feedback.
		$prt_placeholders = stack_utils::extract_placeholders($this->object->getQuestion() . $this->object->specific_feedback, 'feedback');

        $prts_array = array();

        foreach ($prt_placeholders as $prt_name) {
            $prt_data = array();

            //Is new? Then load Standard PRT
			if (!isset($this->object->prts[$prt_name])) {
				$this->object->loadStandardPRT($prt_name);
				ilUtil::sendSuccess('New PRT: ' . $prt_name . ' Created', true);
			} else {
                $prt_data = new stdClass();

				//LOAD STORED DATA
                $prt_data->name = $prt_name;
                $prt_data->value = ((isset($_POST['prt_' . $prt_name . '_value']) and $_POST['prt_' . $prt_name . '_value'] != null) ? trim(ilUtil::secureString($_POST['prt_' . $prt_name . '_value'])) : '');
                $prt_data->autosimplify = ((isset($_POST['prt_' . $prt_name . '_simplify']) and $_POST['prt_' . $prt_name . '_simplify'] != null) ? trim(ilUtil::secureString($_POST['prt_' . $prt_name . '_simplify'])) : '');
                $prt_data->feedbackvariables = ((isset($_POST['prt_' . $prt_name . '_feedback_variables']) and $_POST['prt_' . $prt_name . '_feedback_variables'] != null) ? assStackQuestionUtils::_debugText($_POST['prt_' . $prt_name . '_feedback_variables']) : '');
                $prt_data->firstnodename = ((isset($_POST['prt_' . $prt_name . '_first_node']) and $_POST['prt_' . $prt_name . '_first_node'] != null) ? trim(ilUtil::secureString($_POST['prt_' . $prt_name . '_first_node'])) : '');

                $prt_data->nodes = array();

				//Look for node info
				foreach ($this->object->prts[$prt_name]->get_nodes_summary() as $name => $node) {
					$prefix = 'prt_' . $prt_name . '_node_' . $name;

                    $node = new stdClass();

                    $node->nodename = $name;
                    $node->truenextnode = ((isset($_POST[$prefix . '_pos_next']) and $_POST[$prefix . '_pos_next'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_pos_next'])) : -1);
                    $node->falsenextnode = ((isset($_POST[$prefix . '_neg_next']) and $_POST[$prefix . '_neg_next'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_neg_next'])) : -1);
                    $node->answertest = ((isset($_POST[$prefix . '_answer_test']) and $_POST[$prefix . '_answer_test'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_answer_test'])) : '');
                    $node->sans = ((isset($_POST[$prefix . '_student_answer']) and $_POST[$prefix . '_student_answer'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_student_answer'])) : '');
                    $node->tans = ((isset($_POST[$prefix . '_teacher_answer']) and $_POST[$prefix . '_teacher_answer'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_teacher_answer'])) : '');
                    $node->testoptions = ((isset($_POST[$prefix . '_options']) and $_POST[$prefix . '_options'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_options'])) : '');
                    $node->quiet = ((isset($_POST[$prefix . '_quiet']) and $_POST[$prefix . '_quiet'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_quiet'])) : '');

                    $node->truescore = ((isset($_POST[$prefix . '_pos_score']) and $_POST[$prefix . '_pos_score'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_score'])) : '');
                    $node->truescoremode = ((isset($_POST[$prefix . '_pos_mod']) and $_POST[$prefix . '_pos_mod'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_mod'])) : '');
                    $node->truepenalty = ((isset($_POST[$prefix . '_pos_penalty']) and $_POST[$prefix . '_pos_penalty'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_penalty'])) : '');
                    $node->trueanswernote = ((isset($_POST[$prefix . '_pos_answernote']) and $_POST[$prefix . '_pos_answernote'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_answernote'])) : '');
                    $node->truefeedback = ((isset($_POST[$prefix . '_pos_specific_feedback']) and $_POST[$prefix . '_pos_specific_feedback'] != null) ? ilRTE::_replaceMediaObjectImageSrc(trim(ilUtil::secureString($_POST[$prefix . '_pos_specific_feedback'], false))) : '');
                    $node->truefeedbackformat = ((isset($_POST[$prefix . '_pos_feedback_class']) and $_POST[$prefix . '_pos_feedback_class'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_pos_feedback_class'])) : '');

                    $node->falsescore = ((isset($_POST[$prefix . '_neg_score']) and $_POST[$prefix . '_neg_score'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_score'])) : '');
                    $node->falsescoremode = ((isset($_POST[$prefix . '_neg_mod']) and $_POST[$prefix . '_neg_mod'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_mod'])) : '');
                    $node->falsepenalty = ((isset($_POST[$prefix . '_neg_penalty']) and $_POST[$prefix . '_neg_penalty'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_penalty'])) : '');
                    $node->falseanswernote = ((isset($_POST[$prefix . '_neg_answernote']) and $_POST[$prefix . '_neg_answernote'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_answernote'])) : '');
                    $node->falsefeedback = ((isset($_POST[$prefix . '_neg_specific_feedback']) and $_POST[$prefix . '_neg_specific_feedback'] != null) ? ilRTE::_replaceMediaObjectImageSrc(trim(ilUtil::secureString($_POST[$prefix . '_neg_specific_feedback'], false))) : '');
                    $node->falsefeedbackformat = ((isset($_POST[$prefix . '_neg_feedback_class']) and $_POST[$prefix . '_neg_feedback_class'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_neg_feedback_class'])) : '');

                    $prt_data->nodes[$name] = $node;
                }

                $prts_array[$prt_name] = $prt_data;
			}

            $total_value = 0;
            $all_formative = true;

            foreach ($prts_array as $name => $prt_data) {
                $total_value += $prt_data->value;
                $all_formative = false;
            }

            $this->object->setPoints($total_value);

            if ($prts_array && !$all_formative && $total_value < 0.0000001) {
                throw new stack_exception('There is an error authoring your question. ' .
                    'The $totalvalue, the marks available for the question, must be positive in question ' . $this->object->getTitle());
            }

            foreach ($prts_array as $name => $prt_data) {
                $prt_value = 0;
                if (!$all_formative) {
                    $prt_value = $prt_data->value / $total_value;
                }
                $this->object->prts[$name] = new stack_potentialresponse_tree_lite($prt_data, $prt_value);
            }
		}
	}

	/**
	 * Populate taxonomy section in a form
	 * (made public to be called from authoring GUI)
	 *
	 * @param ilPropertyFormGUI $form
	 */
	public function populateTaxonomyFormSection(ilPropertyFormGUI $form):void
	{
		parent::populateTaxonomyFormSection($form);
	}

	/**
	 * Returns the answer generic feedback depending on the results of the question
	 *
	 * @param integer $active_id Active ID of the user
	 * @param integer $pass Active pass
	 * @return string HTML Code with the answer specific feedback
	 * @access public
	 * @deprecated Use getGenericFeedbackOutput instead.
	 */
	function getAnswerFeedbackOutput($active_id, $pass): string
	{
		return $this->getGenericFeedbackOutput($active_id, $pass);
	}

	/* ILIAS OVERWRITTEN METHODS END */

	/* ILIAS GUI COMMANDS METHODS BEGIN */

	/**
	 * Creates an output of the edit form for the question
	 *
	 * @param bool $check_only
	 *
	 */
	public function editQuestion(bool $check_only = false)
	{
		$save = $this->isSaveCommand();

		global $DIC;

		//Tabs management
		//TODO Aware on the Learning Modules tab if $this->object->getSelfAssessmentEditingMode() is active
		$tabs = $DIC->tabs();
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('edit_question');

		//TODO Is working still in ILIAS7? see comments
		$this->getQuestionTemplate();

		//Create GUI object
		//$this->plugin->includeClass('GUI/question_authoring/class.assStackQuestionAuthoringGUI.php');
		$authoring_gui = new assStackQuestionAuthoringGUI($this->plugin, $this);

		//Add CSS
		$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_authoring.css'));
		$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/multipart_form.css'));

		//Javascript

		//Show info messages
		$this->info_config = new stdClass();
		$ctrl = $DIC->ctrl();
		$this->info_config->ajax_url = $ctrl->getLinkTargetByClass("assstackquestiongui", "saveInfoState", "", TRUE);

		//Set to user's session value
		if (isset($_SESSION['stack_authoring_show'])) {
			$this->info_config->show = (int)$_SESSION['stack_authoring_show'];
		} else {
			//first time must be shown
			$this->info_config->show = 1;
		}
		$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/ilEnableDisableInfo.js');
		$DIC->globalScreen()->layout()->meta()->addOnLoadCode('il.EnableDisableInfo.initInfoMessages(' . json_encode($this->info_config) . ')');

		//Reform authoring interface
		$DIC->globalScreen()->layout()->meta()->addJs('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/js/ilMultipartFormProperty.js');

        //35855 ensure warning if shown if no question note is added when randomised
        if(assStackQuestionUtils::_showRandomisationWarning($this->object)){
            ilUtil::sendInfo(stack_string('questionnotempty'));
        }

		//Returns Question Authoring form
		if (!$check_only) {
			$this->tpl->setVariable("QUESTION_DATA", $authoring_gui->showAuthoringPanel());
		}
	}


	/**
	 * Called by writePostData
	 * handles the copy/paste of PRT/nodes.
	 * Access the DB
	 * TODO
	 */
	public function questionCheck(): bool
	{

		global $DIC;
		$lng = $DIC->language();

		if (is_array($_POST['cmd']['save'])) {

			//PRT OPERATIONS
			foreach ($this->object->prts as $prt_name => $prt) {

				//PRT Copy
				if (isset($_POST['cmd']['save']['copy_prt_' . $prt_name])) {
					//Set prt name and question id into session
					$_SESSION['copy_prt'] = $this->object->getId() . "_" . $prt_name;

					ilUtil::sendInfo($lng->txt("qpl_qst_xqcas_prt_copied_to_clipboard"), true);
					return true;
				}

				//PRT Paste
				if (isset($_POST['cmd']['save']['paste_prt'])) {

					$raw_data = explode("_", $_SESSION['copy_prt']);
					$original_question_id = $raw_data[0];
					$original_prt_name = $raw_data[1];

					//Generate the new prt name,
					$generated_prt_name = "prt" . (string)rand(20, 1000);

					if (assStackQuestionDB::_copyPRTFunction($original_question_id, $original_prt_name, (string)$this->object->getId(), $generated_prt_name)) {

						//Include placeholder in specific feedback
						$current_specific_feedback = $this->object->specific_feedback;
						$new_specific_feedback = "<p>" . $current_specific_feedback . "[[feedback:" . $generated_prt_name . "]]</p>";
						$_POST["options_specific_feedback"] = $new_specific_feedback;

						return true;
					} else {
						return false;
					}

				}

				//NODE OPERATIONS
				foreach ($prt->get_nodes() as $node_name => $node) {

					//Add Node
					if (isset($_POST['cmd']['save']['add_node_to_' . $prt_name])) {

						//Check the new node name,
						//We set as id the following to the current bigger node id
						$max = 0;
						foreach ($prt->get_nodes() as $temp_node_name => $temp_node) {
							(int)$temp_node_name > $max ? $max = (int)$temp_node_name : "";
						}
						$new_node_name = $max + 1;

						if (assStackQuestionDB::_addNodeFunction((string)$this->object->getId(), $prt_name, (string)$new_node_name)) {
							return true;
						} else {
							return false;
						}
					}

					//Delete node
					if (isset($_POST['cmd']['save']['delete_prt_' . $prt_name . '_node_' . $node->nodename])) {

						if (sizeof($prt->get_nodes()) < 2) {
							ilUtil::sendFailure($this->object->getPlugin()->txt('deletion_error_not_enought_prt_nodes'));
							return false;
						}

						if ((int)$prt->get_first_node() == (int)$node_name) {
							ilUtil::sendFailure($this->object->getPlugin()->txt('deletion_error_first_node'));
							return false;
						}

						assStackQuestionDB::_deleteStackPrtNodes($this->object->getId(), $prt_name, $node->nodename);

						//Actualize current question values
						$new_nodes = $prt->get_nodes();
						unset($new_nodes[$node_name]);

						$prt->setNodes($new_nodes);
						$this->object->prts[$prt_name] = $prt;

						ilUtil::sendSuccess("nodes deleted", true);
						return true;
					}

					//Copy Node
					if (isset($_POST['cmd']['save']['copy_prt_' . $prt_name . '_node_' . $node->nodename])) {
						//Set node into session
						$_SESSION['copy_node'] = $this->object->getId() . "_" . $prt_name . "_" . $node->nodename;

						ilUtil::sendInfo($lng->txt("qpl_qst_xqcas_node_copied_to_clipboard"), true);
						return true;
					}

					//Paste Node
					if (isset($_POST['cmd']['save']['paste_node_in_' . $prt_name])) {

						//Do node paste here
						$raw_data = explode("_", $_SESSION['copy_node']);
						$original_question_id = $raw_data[0];
						$original_prt_name = $raw_data[1];
						$original_node_name = $raw_data[2];

						//Check the new node name,
						//We set as id the following to the current bigger node id
						$max = 0;
						foreach ($prt->get_nodes() as $temp_node_name => $temp_node) {
							(int)$temp_node_name > $max ? $max = (int)$temp_node_name : "";
						}
						$new_node_name = $max + 1;

						if (assStackQuestionDB::_copyNodeFunction($original_question_id, $original_prt_name, $original_node_name, (string)$this->object->getId(), $prt_name, (string)$new_node_name)) {
							return true;
						} else {
							return false;
						}
					}
				}

			}

		}

		return false;
	}



	/* RTE, Javascript, Ajax, jQuery etc. METHODS BEGIN */

	/**
	 * Decides whether to show the information fields in the session
	 * Called by editQuestion onLoad
	 */
	public function enableDisableInfo()
	{

		if (isset($_SESSION['show_input_info_fields_in_form'])) {
			if ($_SESSION['show_input_info_fields_in_form'] == TRUE) {
				$_SESSION['show_input_info_fields_in_form'] = FALSE;
			} else {
				$_SESSION['show_input_info_fields_in_form'] = TRUE;
			}
		} else {
			$_SESSION['show_input_info_fields_in_form'] = TRUE;
		}

		//Redirects to show Question Form
		$this->editQuestion();
	}

	/**
	 * Save the showing info messages state in the user session
	 * (This keeps info messages state between page moves)
	 * @see self::addToPage()
	 */
	public function saveInfoState()
	{
		$_SESSION['stack_authoring_show'] = (int)$_GET['show'];

		// debugging output (normally ignored by the js part)
		echo json_encode(array('show' => $_SESSION['stack_authoring_show']));
		exit;
	}

	/**
	 * Init the STACK specific rich text editing support
	 * The allowed html tags are stored in an own settings module instead of "assessment"
	 * This enabled an independent tag set from the editor settings in ILIAS administration
	 * Text area fields will be initialized with SetRTESupport using this module
	 */
	public function initRTESupport()
	{
		//include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->rte_tags = ilObjAdvancedEditing::_getUsedHTMLTags($this->rte_module);

		$this->required_tags = array("a", "blockquote", "br", "cite", "code", "div", "em", "h1", "h2", "h3", "h4", "h5", "h6", "hr", "img", "li", "ol", "p", "pre", "span", "strike", "strong", "sub", "sup", "table", "caption", "thead", "th", "td", "tr", "u", "ul", "i", "b", "gap");

		if (serialize($this->rte_tags) != serialize(($this->required_tags))) {

			$this->rte_tags = $this->required_tags;
			$obj_advance = new ilObjAdvancedEditing();
			$obj_advance->setUsedHTMLTags($this->rte_tags, $this->rte_module);
		}
	}


	/**
	 * Set the STACK specific rich text editing support in textarea fields
	 * This uses an own module instead of "assessment" to determine the allowed tags
	 */
	public function setRTESupport(ilTextAreaInputGUI $field)
	{
		if (empty($this->rte_tags)) {
			$this->initRTESupport();
		}
		$field->setUseRte(true);
		$field->setRteTags($this->rte_tags);
		$field->addPlugin("latex");
		$field->addButton("latex");
		$field->addButton("pastelatex");
		$field->setRTESupport($this->object->getId(), "qpl", $this->rte_module);
	}

	/**
	 * Sets the ILIAS tabs for this question type
	 * called from ilObjTestGUI and ilObjQuestionPoolGUI
	 */
	public function setQuestionTabs():void
	{
		global $DIC, $rbacsystem;

		$tabs = $DIC->tabs();

		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		//include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		//$this->plugin->includeClass('class.ilAssStackQuestionFeedback.php');

		$q_type = $this->object->getQuestionType();

		if (strlen($q_type)) {
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"]) {
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"])) {
				// edit page
				$tabs->addTarget("edit_page", $this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"), array("edit", "insert", "exec_pg"), "", "", "");
			}

			// edit page
			$tabs->addTarget("preview", $this->ctrl->getLinkTargetByClass("ilAssQuestionPreviewGUI", "show"), array("preview"), "ilAssQuestionPageGUI", "", "");
		}

		$force_active = false;
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"])) {
			$url = "";

			if ($classname) {
				$url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			}
			$commands = $_POST["cmd"];
			if (is_array($commands)) {
				foreach ($commands as $key => $value) {
					if (preg_match("/^suggestrange_.*/", $key, $matches)) {
						$force_active = true;
					}
				}
			}
			// edit question properties
			$tabs->addTarget("edit_properties", $url, array("editQuestion", "save", "cancel", "addSuggestedSolution", "cancelExplorer", "linkChilds", "removeSuggestedSolution", "parseQuestion", "saveEdit", "suggestRange"), $classname, "", $force_active);

			$this->addTab_QuestionFeedback($tabs);

			if (in_array($_GET['cmd'], array('importQuestionFromMoodleForm', 'importQuestionFromMoodle', 'editQuestion', 'scoringManagement', 'scoringManagementPanel', 'randomisationAndSecurity', 'createNewDeployedSeed', 'deleteDeployedSeed', 'showUnitTests', 'runTestcases', 'createTestcases', 'post', 'exportQuestiontoMoodleForm', 'exportQuestionToMoodle','generateNewVariants', 'runAllTestsForActiveVariant', 'runAllTestsForAllVariants', 'addCustomTest'))) {
				$tabs->addSubTab('edit_question', $this->plugin->txt('edit_question'), $this->ctrl->getLinkTargetByClass($classname, "editQuestion"));
				$tabs->addSubTab('scoring_management', $this->plugin->txt('scoring_management'), $this->ctrl->getLinkTargetByClass($classname, "scoringManagementPanel"));
				$tabs->addSubTab('randomisation_and_security', $this->plugin->txt('ui_author_randomisation_and_security_title'), $this->ctrl->getLinkTargetByClass($classname, "randomisationAndSecurity"));
				//$tabs->addSubTab('unit_tests', $this->plugin->txt('ut_title'), $this->ctrl->getLinkTargetByClass($classname, "showUnitTests"));
				$tabs->addSubTab('import_from_moodle', $this->plugin->txt('import_from_moodle'), $this->ctrl->getLinkTargetByClass($classname, "importQuestionFromMoodleForm"));
				$tabs->addSubTab('export_to_moodle', $this->plugin->txt('export_to_moodle'), $this->ctrl->getLinkTargetByClass($classname, "exportQuestiontoMoodleForm"));
			}

		}

		// Assessment of questions sub menu entry
		if ($_GET["q_id"]) {
			$tabs->addTarget("statistics", $this->ctrl->getLinkTargetByClass($classname, "assessment"), array("assessment"), $classname, "");
		}

		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0)) {
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) {
				$ref_id = $_GET["test_ref_id"];
			}
			$tabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		} else {
			$tabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}

	}

	/**
	 * For Learning Module Rendering
	 * @return void
	 */
	public function getLearningModuleTabs()
	{
		global $DIC;
		$tabs = $DIC->tabs();

		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		//include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		//$this->plugin->includeClass('class.ilAssStackQuestionFeedback.php');

		$q_type = $this->object->getQuestionType();

		if (strlen($q_type)) {
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $this->object->getId());
		}

		$force_active = false;
		$url = "";

		if ($classname) {
			$url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
		}
		$commands = $_POST["cmd"];
		if (is_array($commands)) {
			foreach ($commands as $key => $value) {
				if (preg_match("/^suggestrange_.*/", $key, $matches)) {
					$force_active = true;
				}
			}
		}
		// edit question properties
		$tabs->addTarget("edit_properties", $url, array("editQuestion", "save", "cancel", "addSuggestedSolution", "cancelExplorer", "linkChilds", "removeSuggestedSolution", "parseQuestion", "saveEdit", "suggestRange"), $classname, "", $force_active);

		if (in_array($_GET['cmd'], array('importQuestionFromMoodleForm', 'importQuestionFromMoodle', 'editQuestion', 'scoringManagement', 'scoringManagementPanel', 'randomisationAndSecurity', 'createNewDeployedSeed', 'deleteDeployedSeed', 'showUnitTests', 'runTestcases', 'createTestcases', 'post', 'exportQuestiontoMoodleForm', 'exportQuestionToMoodle','generateNewVariants', 'runAllTestsForActiveVariant', 'runAllTestsForAllVariants', 'addCustomTest'))) {
			$tabs->addSubTab('edit_question', $this->plugin->txt('edit_question'), $this->ctrl->getLinkTargetByClass($classname, "editQuestion"));
			$tabs->addSubTab('scoring_management', $this->plugin->txt('scoring_management'), $this->ctrl->getLinkTargetByClass($classname, "scoringManagementPanel"));
			$tabs->addSubTab('randomisation_and_security', $this->plugin->txt('ui_author_randomisation_and_security_title'), $this->ctrl->getLinkTargetByClass($classname, "randomisationAndSecurity"));
			//$tabs->addSubTab('unit_tests', $this->plugin->txt('ut_title'), $this->ctrl->getLinkTargetByClass($classname, "showUnitTests"));
		}

	}

	/**
	 * Redirects to the import from MoodleXML Form
	 * @return void
	 */
	public function importQuestionFromMoodleForm()
	{
		global $DIC;

		$lng = $DIC->language();
		$tabs = $DIC->tabs();

		//#25145
		if (isset($_REQUEST["test_ref_id"])) {
			ilUtil::sendFailure($lng->txt("qpl_qst_xqcas_import_in_test_error"), TRUE);
			$DIC->ctrl()->redirect($this, 'editQuestion');
		}

		if ($this->object->getSelfAssessmentEditingMode()) {
			$this->getLearningModuleTabs();
		}
		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('import_from_moodle');

		//require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($lng->txt("qpl_qst_xqcas_import_xml"));

		//Upload XML file
		$item = new ilFileInputGUI($lng->txt("qpl_qst_xqcas_import_xml_file"), 'questions_xml');
		$item->setSuffixes(array('xml'));
		$form->addItem($item);

		$hiddenFirstId = new ilHiddenInputGUI('first_question_id');
		$hiddenFirstId->setValue($_GET['q_id']);
		$form->addItem($hiddenFirstId);

		$form->addCommandButton("importQuestionFromMoodle", $lng->txt("import"));
		$form->addCommandButton("editQuestion", $lng->txt("cancel"));

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * Actually runs the Importing of questions
	 * @return void
	 */
	public function importQuestionFromMoodle()
	{
		global $DIC;
		$tabs = $DIC->tabs();

		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('import_from_moodle');

		//Getting the xml file from $_FILES
		if (file_exists($_FILES["questions_xml"]["tmp_name"])) {
			$xml_file = $_FILES["questions_xml"]["tmp_name"];
		} else {
			ilUtil::sendFailure($this->plugin->txt('error_import_question_in_test'), true);
			return;
		}

		//CHECK FOR NOT ALLOW IMPROT QUESTIONS DIRECTLY IN TESTS
		if (isset($_GET['calling_test'])) {
			ilUtil::sendFailure($this->plugin->txt('error_import_question_in_test'), true);
		} else {
			//Include import class and prepare object
			//$this->plugin->includeClass('model/import/MoodleXML/class.assStackQuestionMoodleImport.php');
			$import = new assStackQuestionMoodleImport($this->plugin, (int)$_POST['first_question_id'], $this->object);
			$import->setRTETags($this->getRTETags());
			$import->import($xml_file);

			$DIC->ctrl()->redirect($this, 'editQuestion');
		}
	}

	/**
	 * Redirects to the export from MoodleXML Form
	 * @return void
	 */
	public function exportQuestiontoMoodleForm()
	{

		global $DIC;
		$tabs = $DIC->tabs();
		$lng = $DIC->language();

        ilUtil::sendInfo($lng->txt("qpl_qst_xqcas_page_editor_compatibility_info"),true);

		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('export_to_moodle');

		//require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($lng->txt("qpl_qst_xqcas_export_to_moodlexml"));

		$options = new ilRadioGroupInputGUI($lng->txt("qpl_qst_xqcas_all_from_pool"), "xqcas_all_from_pool");
		$only_question = new ilRadioOption($lng->txt("qpl_qst_xqcas_export_only_this"), "xqcas_export_only_this", $lng->txt("qpl_qst_xqcas_export_only_this_info"));
		if (isset($_GET['calling_test'])) {
			$all_from_pool = new ilRadioOption($lng->txt("qpl_qst_xqcas_export_all_from_test"), "xqcas_export_all_from_test", $lng->txt("qpl_qst_xqcas_export_all_from_test_info"));
		} else {
			$all_from_pool = new ilRadioOption($lng->txt("qpl_qst_xqcas_export_all_from_pool"), "xqcas_export_all_from_pool", $lng->txt("qpl_qst_xqcas_export_all_from_pool_info"));
		}

		$options->addOption($only_question);
		$options->addOption($all_from_pool);

		if (isset($_GET['calling_test'])) {
			$options->setValue("xqcas_export_all_from_test");
		} else {
			$options->setValue("xqcas_export_all_from_pool");
		}

		$form->addItem($options);

		$hiddenFirstId = new ilHiddenInputGUI('first_question_id');
		$hiddenFirstId->setValue($_GET['q_id']);
		$form->addItem($hiddenFirstId);

		$form->addCommandButton("exportQuestionToMoodle", $lng->txt("export"));
		$form->addCommandButton("editQuestion", $lng->txt("cancel"));

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * Actually runs the export to MoodleXML
	 * @return void
	 */
	public function exportQuestionToMoodle()
	{
		global $DIC;
		$tabs = $DIC->tabs();
		$lng = $DIC->language();

		//require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/export/MoodleXML/class.assStackQuestionMoodleXMLExport.php';

		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('export_to_moodle');

		//Getting data from POST
		if (isset($_POST['first_question_id']) and isset($_POST['xqcas_all_from_pool'])) {
			$question_id = (int)$_POST['first_question_id'];
			$q_type_id = $this->object->getQuestionTypeID();
			try {
				if ($_POST['xqcas_all_from_pool'] == 'xqcas_export_all_from_pool') {
					//Get all questions from a pool
					$questions = assStackQuestionDB::_getAllQuestionsFromPool($question_id, $q_type_id);
					$export_to_moodle = new assStackQuestionMoodleXMLExport($questions);
				} elseif ($_POST['xqcas_all_from_pool'] == 'xqcas_export_only_this') {
					//get current stack question info.
					$export_to_moodle = new assStackQuestionMoodleXMLExport(array($question_id => $this->object));
				} elseif ($_POST['xqcas_all_from_pool'] == 'xqcas_export_all_from_test') {
					//get current stack question info.
					$questions = assStackQuestionDB::_getAllQuestionsFromTest($question_id, $q_type_id);
					$export_to_moodle = new assStackQuestionMoodleXMLExport($questions);
				}

				$export_to_moodle->toMoodleXML();

			} catch (stack_exception $e) {
				ilUtil::sendFailure($e, true);
			}

		} else {
			ilUtil::sendFailure($lng->txt('qpl_qst_xqcas_error_exporting_to_moodle_question_id'), true);
		}
	}


    /**
     * Redirects to the Deployed Seeds Tabs
     * @param int|null $force_active_seed
     * @return void
     */
	public function randomisationAndSecurity(?int $force_active_seed = null): void
	{
		global $DIC;
		$tabs = $DIC->tabs();

		if ($this->object->getSelfAssessmentEditingMode()) {
			$this->getLearningModuleTabs();
		}
		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('randomisation_and_security');
		$this->getQuestionTemplate();

        $deployed_seed_data = StackRandomisationIlias::getRandomisationData($this->object, $force_active_seed);

        $array = array(
            'deployed_seeds' => $deployed_seed_data,
            'question_id' => $this->object->getId(),
            'unit_tests' => $this->object->getUnitTests(),
        );
        $ui = new RandomisationAndSecurityUI($array);

		//Add MathJax (Ensure MathJax is loaded)
		//include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));

		//Add CSS
		//$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_deployed_seeds_management.css'));

		//Returns Deployed seeds form
		$this->tpl->setVariable("QUESTION_DATA", $ui->show(true));
	}

	/**
	 * Deploys new seed in the Deployed Seeds Tabs
	 * @return void
	 */
	public function createNewDeployedSeed()
	{
		global $DIC;
		$tabs = $DIC->tabs();
		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('randomisation_and_security');
		$this->getQuestionTemplate();

		//New seed creation
		$seed = (int)$_POST['deployed_seed'];
		$question_id = (int)$_POST['question_id'];

        //save seed
        assStackQuestionDB::_saveStackSeeds($this->object,'add',$seed);

		$this->randomisationAndSecurity();
	}

	/*
	 * Deletes a deployed seed
	 */
	public function deleteDeployedSeed()
	{
		global $DIC;
		$tabs = $DIC->tabs();
		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('randomisation_and_security');
		$this->getQuestionTemplate();

		//New seed creation
		$seed = (int)$_POST['deployed_seed'];
		$question_id = (int)$_POST['question_id'];

        //delete seed
        assStackQuestionDB::_deleteStackSeeds($question_id,'',$seed);

		$this->randomisationAndSecurity();
	}

	/**
	 * This function is called when scoring tab is activated.
	 * Shows the evaluation structure of the question by potentialresponse tree and a simulation
	 * of the value of each PRT in real points, in order to change it.
	 * @param float $new_question_points
	 */
	public function scoringManagementPanel($new_question_points = '')
	{
		global $DIC;
		$tabs = $DIC->tabs();
		if ($this->object->getSelfAssessmentEditingMode()) {
			$this->getLearningModuleTabs();
		}
		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('scoring_management');
		$this->getQuestionTemplate();

		//Create GUI object
		//$this->plugin->includeClass('GUI/question_authoring/class.assStackQuestionScoringGUI.php');
		$scoring_gui = new assStackQuestionScoringGUI($this->plugin, $this->object, $this->object->getPoints());

		//Add CSS
		$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_scoring_management.css'));

		//Returns Deployed seeds form
		$this->tpl->setVariable("QUESTION_DATA", $scoring_gui->showScoringPanel($new_question_points));
	}

	/**
	 * This command is called when user requires a comparison between current evaluation
	 * structure and a new one with the point value he insert in the input field.
	 */
	public function showScoringComparison()
	{
		//Get new points value
		if (isset($_POST['new_scoring']) and (float)$_POST['new_scoring'] > 0.0) {
			$new_question_points = (float)ilUtil::stripSlashes($_POST['new_scoring']);
		} else {
			$this->question_gui->object->setErrors($this->plugin->txt('sco_invalid_value'));
		}
		//Show scoring panel with comparison
		$this->scoringManagementPanel($new_question_points);
	}

	/**
	 * This command is called when the user wants to change the points value of the
	 * question to the value inserted in the input field.
	 */
	public function saveNewScoring()
	{
		//Get new points value and save it to the DB
		if (isset($_POST['new_scoring']) and (float)$_POST['new_scoring'] > 0.0) {
			$this->object->setPoints(ilUtil::stripSlashes($_POST['new_scoring']));
			$this->object->saveQuestionDataToDb($this->object->getId());
		} else {
			$this->question_gui->object->setErrors($this->plugin->txt('sco_invalid_value'));
		}
		//Show scoring panel
		$this->scoringManagementPanel();
	}

    public function showUnitTests()
    {
        $this->object->instantiateUnitTests();
        $unit_test_data = $this->object->getUnitTests();

        $ui = new RandomisationAndSecurityUI([]);
        $this->tpl->setContent($ui->show(true));
    }

	/**
	 * Command for run testcases
	 */
	public function runTestcases()
	{
		global $DIC;
		$tabs = $DIC->tabs();

		//Set all parameters required
		//$this->plugin->includeClass('utils/class.assStackQuestionStackFactory.php');
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('unit_tests');
		$this->getQuestionTemplate();

		//get Post vars
		if (isset($_POST['test_id'])) {
			$test_id = $_POST['test_id'];
		}
		if (isset($_POST['question_id'])) {
			$question_id = $_POST['question_id'];
		}
		if (isset($_POST['testcase_name'])) {
			$testcase_name = $_POST['testcase_name'];
		} else {
			$testcase_name = FALSE;
		}

		//Create STACK Question object if doesn't exists
		if (!is_a($this->object->getStackQuestion(), 'assStackQuestionStackQuestion')) {
			//$this->plugin->includeClass("model/class.assStackQuestionStackQuestion.php");
			$this->object->setStackQuestion(new assStackQuestionStackQuestion());
			$this->object->getStackQuestion()->init($this->object);
		}

		//Create Unit test object
		//$this->plugin->includeClass("model/ilias_object/test/class.assStackQuestionUnitTests.php");
		$unit_tests_object = new assStackQuestionUnitTests($this->plugin, $this->object);
		$unit_test_results = $unit_tests_object->runTest($testcase_name);

		//Create GUI object
		//$this->plugin->includeClass('GUI/test/class.assStackQuestionTestGUI.php');
		$unit_test_gui = new assStackQuestionTestGUI($this, $this->plugin, $unit_test_results);

		//Add CSS
		$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_unit_tests.css'));

		//Returns Deployed seeds form
		$this->tpl->setVariable("QUESTION_DATA", $unit_test_gui->showUnitTestsPanel(TRUE));
	}

	/**
	 * Command for edit testcases
	 */
	public function editTestcases()
	{
		global $DIC;
		$tabs = $DIC->tabs();

		//Set all parameters required
		//$this->plugin->includeClass('utils/class.assStackQuestionStackFactory.php');
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('unit_tests');
		$this->getQuestionTemplate();

		//get Post vars
		if (isset($_POST['test_id'])) {
			$test_id = $_POST['test_id'];
		}
		if (isset($_POST['question_id'])) {
			$question_id = $_POST['question_id'];
		}
		if (isset($_POST['testcase_name'])) {
			$testcase_name = $_POST['testcase_name'];
		} else {
			$testcase_name = FALSE;
		}

		//Create unit test object
		//$this->plugin->includeClass("model/ilias_object/test/class.assStackQuestionUnitTests.php");
		$unit_tests_object = new assStackQuestionUnitTests($this->plugin, $this->object);

		//Create GUI object
		//$this->plugin->includeClass('GUI/test/class.assStackQuestionTestGUI.php');
		$unit_test_gui = new assStackQuestionTestGUI($this, $this->plugin);

		//Add CSS
		$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_unit_tests.css'));

		//Returns Deployed seeds form
		$this->tpl->setVariable("QUESTION_DATA", $unit_test_gui->editTestcaseForm($testcase_name, $this->object->inputs, $this->object->prts));
	}

	/**
	 * Calling command for edit testcases
	 */
	public function doEditTestcase()
	{
		if (isset($_POST['testcase_name'])) {
			$testcase_name = $_POST['testcase_name'];
			$test = $this->object->getTests($testcase_name);
		} else {
			$testcase_name = FALSE;
		}

		if (is_a($test, 'assStackQuestionTest')) {
			//Creation of inputs
			foreach ($this->object->getInputs() as $input_name => $q_input) {
				$exists = FALSE;
				foreach ($test->getTestInputs() as $input) {
					if ($input->getTestInputName() == $input_name) {
						if (isset($_REQUEST[$input->getTestInputName()])) {
							$input->setTestInputValue($_REQUEST[$input->getTestInputName()]);
							$input->checkTestInput();
							$input->save();
							$exists = TRUE;
						}
					}
				}

				//Correct current mistakes
				if (!$exists) {
					$new_test_input = new assStackQuestionTestInput(-1, $this->object->getId(), $testcase_name);
					$new_test_input->setTestInputName($input_name);
					$new_test_input->setTestInputValue("");
					$new_test_input->save();
				}
			}


			//Creation of expected results
			foreach ($test->getTestExpected() as $index => $prt) {
				if (isset($_REQUEST['score_' . $prt->getTestPRTName()])) {
					$prt->setExpectedScore(ilUtil::stripSlashes($_REQUEST['score_' . $prt->getTestPRTName()]));
				}
				if (isset($_REQUEST['penalty_' . $prt->getTestPRTName()])) {
					$prt->setExpectedPenalty(ilUtil::stripSlashes($_REQUEST['penalty_' . $prt->getTestPRTName()]));
				}
				if (isset($_REQUEST['answernote_' . $prt->getTestPRTName()])) {
					$prt->setExpectedAnswerNote(ilUtil::stripSlashes($_REQUEST['answernote_' . $prt->getTestPRTName()]));
				}
				$prt->checkTestExpected();
				$prt->save();
			}
		}

		$this->showUnitTests();
	}

	/*
	 * Command for create testcases
	 */
	public function createTestcases()
	{
		global $DIC;
		$tabs = $DIC->tabs();
		//Set all parameters required
		//$this->plugin->includeClass('utils/class.assStackQuestionStackFactory.php');
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('unit_tests');
		$this->getQuestionTemplate();

		//Create GUI object
		//$this->plugin->includeClass('GUI/test/class.assStackQuestionTestGUI.php');
		$unit_test_gui = new assStackQuestionTestGUI($this, $this->plugin);

		//Add CSS
		$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_unit_tests.css'));

		//Returns Deployed seeds form
		$testcase_name = assStackQuestionUtils::_getNewTestCaseNumber($this->object->getId());
		$this->tpl->setVariable("QUESTION_DATA", $unit_test_gui->createTestcaseForm($testcase_name, $this->object->getInputs(), $this->object->getPotentialResponsesTrees()));
	}

	/*
	 * Calling command for create testcases
	 */
	public function doCreateTestcase()
	{
		//boolean correct
		$testcase = assStackQuestionUtils::_getNewTestCaseNumber($this->object->getId());
		$new_test = new assStackQuestionTest(-1, $this->object->getId(), $testcase);

		//Creation of inputs
		foreach ($this->object->getInputs() as $input_name => $input) {
			$new_test_input = new assStackQuestionTestInput(-1, $this->object->getId(), $testcase);
			$new_test_input->setTestInputName($input_name);

			if (isset($_REQUEST[$input_name])) {
				$new_test_input->setTestInputValue(ilUtil::stripSlashes($_REQUEST[$input_name]));
			} else {
				$new_test_input->setTestInputValue("");
			}

			$new_test_input->save();
			$test_inputs[] = $new_test_input;
		}

		//Creation of expected results
		foreach ($this->object->getPotentialResponsesTrees() as $prt_name => $prt) {
			//Getting the PRT name
			$new_test_expected = new assStackQuestionTestExpected(-1, $this->object->getId(), $testcase, $prt_name);

			if (isset($_REQUEST['score_' . $prt_name])) {
				$new_test_expected->setExpectedScore(ilUtil::stripSlashes($_REQUEST['score_' . $prt_name]));
			} else {
				$new_test_expected->setExpectedScore("");
			}

			if (isset($_REQUEST['penalty_' . $prt_name])) {
				$new_test_expected->setExpectedPenalty(ilUtil::stripSlashes($_REQUEST['penalty_' . $prt_name]));
			} else {
				$new_test_expected->setExpectedPenalty("");
			}

			if (isset($_REQUEST['answernote_' . $prt_name])) {
				$new_test_expected->setExpectedAnswerNote(ilUtil::stripSlashes($_REQUEST['answernote_' . $prt_name]));
			} else {
				$new_test_expected->setExpectedAnswerNote("");
			}
			$new_test_expected->save();
			$test_expected[] = $new_test_expected;
		}

		$new_test->setTestExpected($test_expected);
		$new_test->setTestInputs($test_inputs);
		$new_test->save();

		$this->showUnitTests();
	}

	/*
	 * Command for deleting testcases
	 */
	public function doDeleteTestcase()
	{
		//get Post vars
		if (isset($_POST['test_id'])) {
			$test_id = $_POST['test_id'];
		}
		if (isset($_POST['question_id'])) {
			$question_id = $_POST['question_id'];
		}
		if (isset($_POST['testcase_name'])) {
			$testcase_name = $_POST['testcase_name'];
		} else {
			$testcase_name = FALSE;
		}

		$new_tests = assStackQuestionTest::_read($question_id, $testcase_name);
		$new_test = $new_tests[$testcase_name];
		$new_test->delete($question_id, $testcase_name);

		$this->showUnitTests();
	}

	/* GETTERS AND SETTERS */

	/**
	 * @return ilassStackQuestionPlugin
	 */
	public function getPlugin(): ilPlugin
	{
		return $this->plugin;
	}

	/**
	 * @param ilassStackQuestionPlugin $plugin
	 */
	public function setPlugin(ilPlugin $plugin): void
	{
		$this->plugin = $plugin;
	}

	/**
	 * @return string
	 */
	public function getRteModule(): string
	{
		return $this->rte_module;
	}

	/**
	 * @param string $rte_module
	 */
	public function setRteModule(string $rte_module): void
	{
		$this->rte_module = $rte_module;
	}

	/**
	 * Get a list of allowed RTE tags
	 * This is used for ilUtil::stripSpashes() when saving the RTE fields
	 *
	 * @return string    allowed html tags, e.g. "<em><strong>..."
	 */
	public function getRTETags()
	{
		if (empty($this->rte_tags)) {
			$this->initRTESupport();
		}

		return '<' . implode('><', $this->rte_tags) . '>';
	}

	/**
	 * @param array $rte_tags
	 */
	public function setRteTags(array $rte_tags): void
	{
		$this->rte_tags = $rte_tags;
	}

	/**
	 * @return array
	 */
	public function getIsPreview(): array
	{
		return $this->is_preview;
	}

	/**
	 * @param array $is_preview
	 */
	public function setIsPreview(array $is_preview): void
	{
		$this->is_preview = $is_preview;
	}

    public function changeActiveVariant()
    {
        $ui = new RandomisationAndSecurityUI([]);
        $this->tpl->setContent($ui->show_form_in_modal());
    }

    public function editUnitTestUI(){
        $ui = new RandomisationAndSecurityUI([]);
        $this->tpl->setContent($ui->show_form_in_modal());
    }


    public function setAsActiveVariant()
    {
        if(isset($_GET['set_active_variant_identifier'])){
            $variant_id = $_GET['set_active_variant_identifier'];
            assStackQuestionDB::_saveSeedForPreview($this->object->getId(),(int)$variant_id);
        }
        $this->randomisationAndSecurity();
    }

    public function changeToRandomSeed()
    {
        $seed = rand(1111111111,9999999999);
        $this->object->deployed_seeds[$seed] = $seed;
        assStackQuestionDB::_saveSeedForPreview($this->object->getId(),$seed);
        $this->randomisationAndSecurity($seed);
    }

    public function generateNewVariants()
    {
        // TODO: Implement generateNewVariants() method.
    }

    public function runAllTestsForActiveVariant()
    {
        // TODO: Implement runAllTestsForActiveVariant() method.
    }

    public function runAllTestsForAllVariants()
    {
        // TODO: Implement runAllTestsForAllVariants() method.
    }

    public function addCustomTest()
    {
        // TODO: Implement addCustomTest() method.
    }
}