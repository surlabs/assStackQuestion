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
use classes\platform\StackPlatform;
use classes\platform\StackUnitTest;
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
	 * true if the question is in preview mode
	 * @var bool
	 */
	private bool $is_preview = false;

    /**
     * Auxiliary array to store the specific post data that needs to be modified
     * because from ilias8 onwards the post data cannot be modified directly
     */
    private array $specific_post_data = array();

	/**
	 * assStackQuestionGUI constructor.
	 */
	public function __construct($id = -1)
	{
        global $tpl;
        parent::__construct();

        // init the plugin object
        try {
            global $DIC;

            /** @var ilComponentRepository $component_repository */
            $component_repository = $DIC["component.repository"];

            $info = null;
            $plugin_name = 'assStackQuestion';
            $info = $component_repository->getPluginByName($plugin_name);

            /** @var ilComponentFactory $component_factory */
            $component_factory = $DIC["component.factory"];

            /** @var ilQuestionsPlugin $plugin_obj */
            $plugin_obj = $component_factory->getPlugin($info->getId());

            if (!is_null($info) && $info->isActive()) {
                $this->setPlugin($plugin_obj);
            } else {
                throw new ilPluginException($plugin_name . ' plugin is not active');
            }
        } catch (ilPluginException $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
        }

        //Initialize and loads the Stack question from DB
        $this->object = new assStackQuestion();

        StackPlatform::initialize('ilias');

        if ($id >= 0) {
            try {
                $this->object->loadFromDb($id);

            } catch (stack_exception $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
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
        $seed = assStackQuestionDB::_getSeed("test", $this->object, (int) $active_id, (int) $pass);
        $this->object->questionInitialisation($seed, true);
        $user_response = StackUserResponseIlias::getStackUserResponse('test', (int) $this->object->getId(), (int) $active_id, (int) $pass);

        if (isset($user_response["inputs"])) {
            $temp_user_response = array();
            foreach ($user_response["inputs"] as $input_name => $input) {
                foreach ($this->object->inputs[$input_name]->maxima_to_response_array($input["value"]) as $key => $value) {
                    $temp_user_response[$key] = $value;
                }

                $temp_user_response[$input_name . '_validation'] = $input["validation_display"];
            }
            $user_response = $temp_user_response;
        }

        $attempt_data = [];

        $attempt_data['response'] = $user_response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = false;
        $display_options['feedback'] = true;
        $display_options['feedback_style'] = 1;

        //Render question
        $question = StackRenderIlias::renderQuestion($attempt_data, $display_options);
        return $this->outQuestionPage('',
            $is_question_postponed,
            $active_id,
            assStackQuestionUtils::_getLatex($question),
            $show_specific_inline_feedback);
	}

    /**
     * Returns question view with a response filled in
     * It can be the user response
     * It can be the correct response
     * Depending on the context
     * Called multiple times at execution
     * This method is called from the test view and from the question pool view
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
     * @throws StackException
     */
	public function getSolutionOutput($active_id, $pass = null, $graphicalOutput = false, $result_output = false, $show_question_only = true, $show_feedback = false, $show_correct_solution = false, $show_manual_scoring = false, $show_question_text = true): string
    {
        global $tpl;

        StackRenderIlias::ensureMathJaxLoaded();

        if (!is_null($active_id) && (int)$active_id !== 0) {
            $purpose = 'test';
            if (is_null($pass)) {
                $pass = ilObjTest::_getPass($active_id);
            }
        } else {
            $purpose = 'preview';
        }

        $seed = assStackQuestionDB::_getSeed($purpose, $this->object, (int)$active_id, (int)$pass);

        //Instantiate Question if not.
        if (!$this->object->isInstantiated()) {
            try{
                $this->object->questionInitialisation($seed, true);
            } catch (stack_exception|StackException $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                return '';
            }
        }

        $user_response =  $show_correct_solution ? $this->object->getCorrectResponse() : StackUserResponseIlias::getStackUserResponse('test', (int) $this->object->getId(), (int) $active_id, (int) $pass);

        //Ensure evaluation has been done
        if (empty($this->object->getEvaluation())) {
            try{
                $this->object->evaluateQuestion($user_response);
            } catch (stack_exception|StackException $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                return '';
            }
        }

        if (isset($user_response["inputs"])) {
            $temp_user_response = array();
            foreach ($user_response["inputs"] as $input_name => $input) {
                foreach ($this->object->inputs[$input_name]->maxima_to_response_array($input["value"]) as $key => $value) {
                    $temp_user_response[$key] = $value;
                }

                $temp_user_response[$input_name . '_validation'] = $input["validation_display"];
            }
            $user_response = $temp_user_response;
        }

        $attempt_data = [];

        $attempt_data['response'] = $user_response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = true;
        $display_options['feedback'] = true;
        $display_options['feedback_style'] = 1;

        //Render question (and general feedback if solution)
        $question = assStackQuestionUtils::_getLatex(StackRenderIlias::renderQuestion($attempt_data, $display_options));

        if ($show_correct_solution) {
            global $DIC;
            $question .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->divider()->horizontal());
            $question .= assStackQuestionUtils::_getLatex(StackRenderIlias::renderGeneralFeedback($attempt_data, $display_options));
        } else {
            $question .= assStackQuestionUtils::_getLatex(StackRenderIlias::renderSpecificFeedback($attempt_data, $display_options));
        }

        if (!$show_question_only) {
            $question = $this->getILIASPage($question);
        }

        return $question;
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
		global $DIC, $tpl;
        $this->is_preview = true;

        $seed = assStackQuestionDB::_getSeed("preview", $this->object, $DIC->user()->getId());

        $user_response = [];

        if ($this->getPreviewSession()->getParticipantsSolution() !== null) {
            $user_response = StackUserResponseIlias::getStackUserResponse('preview', $this->object->getId(), $DIC->user()->getId());
        } else {
            assStackQuestionDB::_savePreviewSolution($this->object, array());
        }

        //Instantiate Question if not.
        if (!$this->object->isInstantiated()) {
            try{
                $this->object->questionInitialisation($seed, true);
            } catch (stack_exception|StackException $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                return '';
            }
        }

		//Ensure evaluation has been done
		if (empty($this->object->getEvaluation())) {
            try{
                $this->object->evaluateQuestion($user_response);
            } catch (stack_exception|StackException $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                return '';
            }
		}

        $attempt_data = [];

        $attempt_data['response'] = $user_response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = false;
        $display_options['feedback'] = true;
        $display_options['feedback_style'] = 1;
		//Render question Preview

        /*
        $question_preview = $DIC->ui()->renderer()->render($DIC->ui()->factory()->button()->standard(
            $DIC->language()->txt('stack_preview_question'),
            $DIC->ctrl()->getLinkTargetByClass(
                'assStackQuestionGUI',
                'fillWithCorrectResponses'
            )
        ));*/

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
     * @throws StackException
     * @throws stack_exception
     */
	public function getSpecificFeedbackOutput($userSolution): string
	{
        global $DIC, $tpl;

        if ($this->is_preview) {
            $seed = assStackQuestionDB::_getSeed("preview", $this->object, $DIC->user()->getId());
            $response = StackUserResponseIlias::getStackUserResponse('preview', (int)$this->object->getId(), $DIC->user()->getId());
        } else {
            if (array_key_exists('active_id', $DIC->http()->request()->getQueryParams())) {
                $active_id = $DIC->http()->request()->getQueryParams()['active_id'];
            } else {
                $active_id = null;
            }
            $pass = ilObjTest::_getPass($active_id);

            $seed = assStackQuestionDB::_getSeed("test", $this->object, (int)$active_id, (int)$pass);
            $user_response = StackUserResponseIlias::getStackUserResponse('test', (int)$this->object->getId(), (int) $active_id, (int) $pass);
            $response = [];
            if (isset($user_response["inputs"])) {
                $temp_user_response = array();
                foreach ($user_response["inputs"] as $input_name => $input) {
                    foreach ($this->object->inputs[$input_name]->maxima_to_response_array($input["value"]) as $key => $value) {
                        $temp_user_response[$key] = $value;
                    }

                    $temp_user_response[$input_name . '_validation'] = $input["validation_display"];
                }
                $response = $temp_user_response;
            }
        }

        //Instantiate Question if not.
        if (!$this->object->isInstantiated()) {
            try{
                $this->object->questionInitialisation($seed, true);
            } catch (stack_exception|StackException $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            }
        }

        //Ensure evaluation has been done
        if (empty($this->object->getEvaluation())) {
            try{
                $this->object->evaluateQuestion($response);
            } catch (stack_exception|StackException $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            }
        }

        $attempt_data = [];

        $attempt_data['response'] = $response;
        $attempt_data['question'] = $this->object;

        $display_options = [];
        $display_options['readonly'] = true;
        $display_options['feedback'] = true;
        $display_options['feedback_style'] = 1;

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
            $this->generateSpecificPostData();

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

    private function generateSpecificPostData() :void
    {
        $this->specific_post_data = array();

        $this->specific_post_data["question_text"] = ((isset($_POST['question']) and $_POST['question'] != null) ? ilUtil::stripSlashes($_POST['question'], true, $this->getRTETags()) : '');

        $this->specific_post_data["options_specific_feedback"] = ((isset($_POST['options_specific_feedback']) and $_POST['options_specific_feedback'] != null) ? ilUtil::stripSlashes($_POST['options_specific_feedback'], true, $this->getRTETags()) : '');

        foreach ($this->object->prts as $prt_name => $prt) {
            $this->specific_post_data['prt_' . $prt_name . '_value'] = ((isset($_POST['prt_' . $prt_name . '_value']) and $_POST['prt_' . $prt_name . '_value'] != null) ? trim(ilUtil::secureString($_POST['prt_' . $prt_name . '_value'])) : '');
            $this->specific_post_data['prt_' . $prt_name . '_simplify'] = ((isset($_POST['prt_' . $prt_name . '_simplify']) and $_POST['prt_' . $prt_name . '_simplify'] != null) ? trim(ilUtil::secureString($_POST['prt_' . $prt_name . '_simplify'])) : '');
            $this->specific_post_data['prt_' . $prt_name . '_feedback_variables'] = ((isset($_POST['prt_' . $prt_name . '_feedback_variables']) and $_POST['prt_' . $prt_name . '_feedback_variables'] != null) ? assStackQuestionUtils::_debugText($_POST['prt_' . $prt_name . '_feedback_variables']) : '');
            $this->specific_post_data['prt_' . $prt_name . '_first_node'] = ((isset($_POST['prt_' . $prt_name . '_first_node']) and $_POST['prt_' . $prt_name . '_first_node'] != null) ? trim(ilUtil::secureString($_POST['prt_' . $prt_name . '_first_node'])) : '');

            foreach ($this->object->prts[$prt_name]->get_nodes() as $name => $node) {
                $prefix = 'prt_' . $prt_name . '_node_' . $name;

                $this->specific_post_data[$prefix . '_description'] = (isset($_POST[$prefix . '_description']) and $_POST[$prefix . '_description'] != null) ? $_POST[$prefix . '_description'] : '';
                $this->specific_post_data[$prefix . '_pos_next'] = ((isset($_POST[$prefix . '_pos_next']) and $_POST[$prefix . '_pos_next'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_pos_next'])) : -1);
                $this->specific_post_data[$prefix . '_neg_next'] = ((isset($_POST[$prefix . '_neg_next']) and $_POST[$prefix . '_neg_next'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_neg_next'])) : -1);
                $this->specific_post_data[$prefix . '_answer_test'] = ((isset($_POST[$prefix . '_answer_test']) and $_POST[$prefix . '_answer_test'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_answer_test'])) : '');
                $this->specific_post_data[$prefix . '_student_answer'] = ((isset($_POST[$prefix . '_student_answer']) and $_POST[$prefix . '_student_answer'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_student_answer'])) : '');
                $this->specific_post_data[$prefix . '_teacher_answer'] = ((isset($_POST[$prefix . '_teacher_answer']) and $_POST[$prefix . '_teacher_answer'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_teacher_answer'])) : '');
                $this->specific_post_data[$prefix . '_options'] = ((isset($_POST[$prefix . '_options']) and $_POST[$prefix . '_options'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_options'])) : '');
                $this->specific_post_data[$prefix . '_quiet'] = ((isset($_POST[$prefix . '_quiet']) and $_POST[$prefix . '_quiet'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_quiet'])) : '');

                $this->specific_post_data[$prefix . '_pos_score'] = ((isset($_POST[$prefix . '_pos_score']) and $_POST[$prefix . '_pos_score'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_score'])) : '');
                $this->specific_post_data[$prefix . '_pos_mod'] = ((isset($_POST[$prefix . '_pos_mod']) and $_POST[$prefix . '_pos_mod'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_mod'])) : '');
                $this->specific_post_data[$prefix . '_pos_penalty'] = ((isset($_POST[$prefix . '_pos_penalty']) and $_POST[$prefix . '_pos_penalty'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_penalty'])) : '');
                $this->specific_post_data[$prefix . '_pos_answernote'] = ((isset($_POST[$prefix . '_pos_answernote']) and $_POST[$prefix . '_pos_answernote'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_pos_answernote'])) : '');
                $this->specific_post_data[$prefix . '_pos_specific_feedback'] = ((isset($_POST[$prefix . '_pos_specific_feedback']) and $_POST[$prefix . '_pos_specific_feedback'] != null) ? ilRTE::_replaceMediaObjectImageSrc(trim(ilUtil::secureString($_POST[$prefix . '_pos_specific_feedback'], false))) : '');
                $this->specific_post_data[$prefix . '_pos_feedback_class'] = ((isset($_POST[$prefix . '_pos_feedback_class']) and $_POST[$prefix . '_pos_feedback_class'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_pos_feedback_class'])) : '');

                $this->specific_post_data[$prefix . '_neg_score'] = ((isset($_POST[$prefix . '_neg_score']) and $_POST[$prefix . '_neg_score'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_score'])) : '');
                $this->specific_post_data[$prefix . '_neg_mod'] = ((isset($_POST[$prefix . '_neg_mod']) and $_POST[$prefix . '_neg_mod'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_mod'])) : '');
                $this->specific_post_data[$prefix . '_neg_penalty'] = ((isset($_POST[$prefix . '_neg_penalty']) and $_POST[$prefix . '_neg_penalty'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_penalty'])) : '');
                $this->specific_post_data[$prefix . '_neg_answernote'] = ((isset($_POST[$prefix . '_neg_answernote']) and $_POST[$prefix . '_neg_answernote'] != null) ? trim(ilUtil::secureString($_POST[$prefix . '_neg_answernote'])) : '');
                $this->specific_post_data[$prefix . '_neg_specific_feedback'] = ((isset($_POST[$prefix . '_neg_specific_feedback']) and $_POST[$prefix . '_neg_specific_feedback'] != null) ? ilRTE::_replaceMediaObjectImageSrc(trim(ilUtil::secureString($_POST[$prefix . '_neg_specific_feedback'], false))) : '');
                $this->specific_post_data[$prefix . '_neg_feedback_class'] = ((isset($_POST[$prefix . '_neg_feedback_class']) and $_POST[$prefix . '_neg_feedback_class'] != null) ? (int)trim(ilUtil::secureString($_POST[$prefix . '_neg_feedback_class'])) : '');

            }
        }
    }

    /**
     * Writes the data from $_POST into assStackQuestion
     * Called before editQuestion()
     * @throws stack_exception
     */
	public function writeQuestionSpecificPostData()
	{
        global $tpl;

		//Question Text - Reload it with RTE (already loaded in writeQuestionGenericPostData())
		$question_text = $this->specific_post_data["question_text"];
		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($question_text, 1));

		//stack_options
		$options = array();
		$options['simplify'] = ((isset($_POST['options_question_simplify']) and $_POST['options_question_simplify'] != null) ? (int)trim(ilUtil::secureString($_POST['options_question_simplify'])) : '');
		$options['assumepos'] = ((isset($_POST['options_assume_positive']) and $_POST['options_assume_positive'] != null) ? (int)trim(ilUtil::secureString($_POST['options_assume_positive'])) : '');
		$options['assumereal'] = ((isset($_POST['options_assume_real']) and $_POST['options_assume_real'] != null) ? (int)trim(ilUtil::secureString($_POST['options_assume_real'])) : '');
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
			$tpl->setOnScreenMessage('failure', $e, true);
		}

		//Load data sent as options but not part of the session options
		$this->object->question_variables = ((isset($_POST['options_question_variables']) and $_POST['options_question_variables'] != null) ? assStackQuestionUtils::_debugText($_POST['options_question_variables']) : '');
		$this->object->question_note = ((isset($_POST['options_question_note']) and $_POST['options_question_note'] != null) ? ilUtil::secureString($_POST['options_question_note']) : '');

		$this->object->specific_feedback = $this->specific_post_data["options_specific_feedback"];
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

		//Load only those prt located in the question text or in the specific feedback.
		$prt_placeholders = stack_utils::extract_placeholders($this->object->getQuestion() . $this->object->specific_feedback, 'feedback');

        $prts_array = array();

        foreach ($prt_placeholders as $prt_name) {
            //Is new? Then load Standard PRT
			if (!isset($this->object->prts[$prt_name])) {
				$this->object->loadStandardPRT($prt_name);
				$tpl->setOnScreenMessage('success', 'New PRT: ' . $prt_name . ' Created', true);
			} else {
                $prt_data = new stdClass();

				//LOAD STORED DATA
                $prt_data->name = $prt_name;
                $prt_data->value = $this->specific_post_data['prt_' . $prt_name . '_value'];
                $prt_data->autosimplify = $this->specific_post_data['prt_' . $prt_name . '_simplify'];
                $prt_data->feedbackvariables = $this->specific_post_data['prt_' . $prt_name . '_feedback_variables'];
                $prt_data->firstnodename = $this->specific_post_data['prt_' . $prt_name . '_first_node'];

                $prt_data->nodes = array();

				//Look for node info
				foreach ($this->object->prts[$prt_name]->get_nodes() as $name => $node) {
					$prefix = 'prt_' . $prt_name . '_node_' . $name;

                    $node = new stdClass();

                    $node->nodename = $name;
                    $node->description = (isset($_POST[$prefix . '_description']) and $_POST[$prefix . '_description'] != null) ? $_POST[$prefix . '_description'] : '';
                    $node->prtname = $prt_name;
                    $node->truenextnode = $this->specific_post_data[$prefix . '_pos_next'];
                    $node->falsenextnode = $this->specific_post_data[$prefix . '_neg_next'];
                    $node->answertest = $this->specific_post_data[$prefix . '_answer_test'];
                    $node->sans = $this->specific_post_data[$prefix . '_student_answer'];
                    $node->tans = $this->specific_post_data[$prefix . '_teacher_answer'];
                    $node->testoptions = $this->specific_post_data[$prefix . '_options'];
                    $node->quiet = $this->specific_post_data[$prefix . '_quiet'];

                    $node->truescore = $this->specific_post_data[$prefix . '_pos_score'];
                    $node->truescoremode = $this->specific_post_data[$prefix . '_pos_mod'];
                    $node->truepenalty = $this->specific_post_data[$prefix . '_pos_penalty'];
                    $node->trueanswernote = $this->specific_post_data[$prefix . '_pos_answernote'];
                    $node->truefeedback = $this->specific_post_data[$prefix . '_pos_specific_feedback'];
                    $node->truefeedbackformat = $this->specific_post_data[$prefix . '_pos_feedback_class'];

                    $node->falsescore = $this->specific_post_data[$prefix . '_neg_score'];
                    $node->falsescoremode = $this->specific_post_data[$prefix . '_neg_mod'];
                    $node->falsepenalty = $this->specific_post_data[$prefix . '_neg_penalty'];
                    $node->falseanswernote = $this->specific_post_data[$prefix . '_neg_answernote'];
                    $node->falsefeedback = $this->specific_post_data[$prefix . '_neg_specific_feedback'];
                    $node->falsefeedbackformat = $this->specific_post_data[$prefix . '_neg_feedback_class'];
                    $prt_data->nodes[$name] = $node;
                }

                $prts_array[$prt_name] = $prt_data;
			}

            $total_value = 0;
            $all_formative = true;

            foreach ($prts_array as $name => $prt_data) {
                $total_value += (float) $prt_data->value;
                $all_formative = false;
            }

            if ($prts_array && !$all_formative && $total_value < 0.0000001) {
                throw new stack_exception('There is an error authoring your question. ' .
                    'The $totalvalue, the marks available for the question, must be positive in question ' . $this->object->getTitle());
            }

            foreach ($prts_array as $name => $prt_data) {
                $prt_value = 0;
                if (!$all_formative) {
                    $prt_value = (float) $prt_data->value / $total_value;
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
            global $tpl;
            $tpl->setOnScreenMessage('info', stack_string('questionnotempty'));
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

		global $DIC,$tpl;
		$lng = $DIC->language();

		if (is_array($_POST['cmd']['save'])) {

			//PRT OPERATIONS
			foreach ($this->object->prts as $prt_name => $prt) {

				//PRT Copy
				if (isset($_POST['cmd']['save']['copy_prt_' . $prt_name])) {
					//Set prt name and question id into session
					$_SESSION['copy_prt'] = $this->object->getId() . "_" . $prt_name;

					$tpl->setOnScreenMessage('info', $lng->txt("qpl_qst_xqcas_prt_copied_to_clipboard"), true);
					return true;
				}

				//PRT Paste
				if (isset($_POST['cmd']['save']['paste_prt_' . $prt_name])) {

					$raw_data = explode("_", $_SESSION['copy_prt']);
					$original_question_id = $raw_data[0];
					$original_prt_name = $raw_data[1];

                    $generated_prt_name = "prt" . rand(20, 1000);

                    if (assStackQuestionDB::_copyPRTFunction($original_question_id, $original_prt_name, (string)$this->object->getId(), $generated_prt_name)) {
                        //Include placeholder in specific feedback
                        $current_specific_feedback = $this->object->specific_feedback;
                        $new_specific_feedback = "<p>" . $current_specific_feedback . "[[feedback:" . $generated_prt_name . "]]</p>";
                        $this->specific_post_data["options_specific_feedback"] = $new_specific_feedback;

                        $prt_from_db_array = assStackQuestionDB::_readPRTs($this->object->getId());

                        $total_value = 0;


                        foreach ($prt_from_db_array as $prtdt) {
                            $total_value += $prtdt->value;
                        }

                        foreach ($prt_from_db_array as $name => $prtdt) {
                            $prt_value = $prtdt->value / $total_value;
                            $this->object->prts[$name] = new stack_potentialresponse_tree_lite($prtdt, $prt_value);
                        }

                        $this->specific_post_data['prt_' . $generated_prt_name . '_value'] = $prt_from_db_array[$generated_prt_name]->value;
                        $this->specific_post_data['prt_' . $generated_prt_name . '_simplify'] = $prt_from_db_array[$generated_prt_name]->autosimplify;
                        $this->specific_post_data['prt_' . $generated_prt_name . '_feedback_variables'] = $prt_from_db_array[$generated_prt_name]->feedbackvariables;
                        $this->specific_post_data['prt_' . $generated_prt_name . '_first_node'] = $prt_from_db_array[$generated_prt_name]->firstnodename;

                        foreach ($this->object->prts[$generated_prt_name]->get_nodes() as $node_name => $node) {
                            $prefix = 'prt_' . $generated_prt_name . '_node_' . $node_name;

                            $this->specific_post_data[$prefix . '_description'] = $node->description;
                            $this->specific_post_data[$prefix . '_pos_next'] = $node->truenextnode;
                            $this->specific_post_data[$prefix . '_neg_next'] = $node->falsenextnode;
                            $this->specific_post_data[$prefix . '_answer_test'] = $node->answertest;
                            $this->specific_post_data[$prefix . '_student_answer'] = $node->sans;
                            $this->specific_post_data[$prefix . '_teacher_answer'] = $node->tans;
                            $this->specific_post_data[$prefix . '_options'] = $node->testoptions;
                            $this->specific_post_data[$prefix . '_quiet'] = $node->quiet;

                            $this->specific_post_data[$prefix . '_pos_score'] = $node->truescore;
                            $this->specific_post_data[$prefix . '_pos_mod'] = $node->truescoremode;
                            $this->specific_post_data[$prefix . '_pos_penalty'] = $node->truepenalty;
                            $this->specific_post_data[$prefix . '_pos_answernote'] = $node->trueanswernote;
                            $this->specific_post_data[$prefix . '_pos_specific_feedback'] = $node->truefeedback;
                            $this->specific_post_data[$prefix . '_pos_feedback_class'] = $node->truefeedbackformat;

                            $this->specific_post_data[$prefix . '_neg_score'] = $node->falsescore;
                            $this->specific_post_data[$prefix . '_neg_mod'] = $node->falsescoremode;
                            $this->specific_post_data[$prefix . '_neg_penalty'] = $node->falsepenalty;
                            $this->specific_post_data[$prefix . '_neg_answernote'] = $node->falseanswernote;
                            $this->specific_post_data[$prefix . '_neg_specific_feedback'] = $node->falsefeedback;
                            $this->specific_post_data[$prefix . '_neg_feedback_class'] = $node->falsefeedbackformat;
                        }

                        return true;
                    } else {
                        return false;
                    }
				}

                if (isset($_POST['cmd']['save']['add_prt'])) {
                    $generated_prt_name = "prt" . rand(20, 1000);

                    if (assStackQuestionDB::_addPRTFunction((string) $this->object->getId(), $generated_prt_name, $this->object->loadStandardPRT($generated_prt_name, true))) {
                        $this->generateSpecificPostData();

                        //Include placeholder in specific feedback
                        $current_specific_feedback = $this->object->specific_feedback;
                        $new_specific_feedback = "<p>" . $current_specific_feedback . "[[feedback:" . $generated_prt_name . "]]</p>";
                        $this->specific_post_data["options_specific_feedback"] = $new_specific_feedback;

                        return true;
                    } else {
                        return false;
                    }
                }

                //Delete PRT
                if (isset($_POST['cmd']['save']['delete_full_prt_' . $prt_name])) {

                    if (sizeof($this->object->prts) < 2) {
                        $tpl->setOnScreenMessage('failure', $this->object->getPlugin()->txt('deletion_error_not_enought_prts'));
                        return false;
                    }

                    $new_prts = $this->object->prts;
                    unset($new_prts[$prt_name]);

                    $current_question_text = $this->object->getQuestion();
                    $new_question_text = str_replace("[[feedback:" . $prt_name . "]]", "", $current_question_text);
                    $this->specific_post_data["question_text"] = $new_question_text;

                    $current_specific_feedback = $this->object->specific_feedback;
                    $new_specific_feedback = str_replace("[[feedback:" . $prt_name . "]]", "", $current_specific_feedback);
                    $this->specific_post_data["options_specific_feedback"] = $new_specific_feedback;

                    $this->object->prts = $new_prts;

                    assStackQuestionDB::_deleteStackPrts($this->object->getId(), $prt_name);

                    $tpl->setOnScreenMessage('success', "prt deleted", true);
                    return true;
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
							$tpl->setOnScreenMessage('failure', $this->object->getPlugin()->txt('deletion_error_not_enought_prt_nodes'));
							return false;
						}

						if ((int)$prt->get_first_node() == (int)$node_name) {
							$tpl->setOnScreenMessage('failure', $this->object->getPlugin()->txt('deletion_error_first_node'));
							return false;
						}

						//Actualize current question values
						$new_nodes = $prt->get_nodes();
						unset($new_nodes[$node_name]);

                        //Recorre los nodos de ese prt y actualiza los nextnode
                        foreach ($new_nodes as $n_name => $n) {
                            if ($n->truenextnode == $node_name) {
                                $n->truenextnode = "-1";
                                if (isset($this->specific_post_data['prt_' . $prt_name . '_node_' . $n_name . '_pos_next'])) {
                                    $this->specific_post_data['prt_' . $prt_name . '_node_' . $n_name . '_pos_next'] = "-1";
                                }
                            }
                            if ($n->falsenextnode == $node_name) {
                                $n->falsenextnode = "-1";
                                if (isset($this->specific_post_data['prt_' . $prt_name . '_node_' . $n_name . '_neg_next'])) {
                                    $this->specific_post_data['prt_' . $prt_name . '_node_' . $n_name . '_neg_next'] = "-1";
                                }
                            }
                        }

						$prt->setNodes($new_nodes);
						$this->object->prts[$prt_name] = $prt;

                        assStackQuestionDB::_deleteStackPrtNodes($this->object->getId(), $prt_name, $node->nodename);

                        $tpl->setOnScreenMessage('success', "nodes deleted", true);
						return true;
					}

					//Copy Node
					if (isset($_POST['cmd']['save']['copy_prt_' . $prt_name . '_node_' . $node->nodename])) {
						//Set node into session
						$_SESSION['copy_node'] = $this->object->getId() . "_" . $prt_name . "_" . $node->nodename;

						$tpl->setOnScreenMessage('info', $lng->txt("qpl_qst_xqcas_node_copied_to_clipboard"), true);
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
			if ($rbacsystem->checkAccess('write', (int)$_GET["ref_id"])) {
				// edit page
				$tabs->addTarget("edit_page", $this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"), array("edit", "insert", "exec_pg"), "", "", true);
			}

			// edit page
			$tabs->addTarget("preview", $this->ctrl->getLinkTargetByClass("ilAssQuestionPreviewGUI", "show"), array("preview"), "ilAssQuestionPageGUI", "", true);
		}

		$force_active = false;
		if ($rbacsystem->checkAccess('write', (int)$_GET["ref_id"])) {
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
			$tabs->addTarget("edit_properties", $url, array("editQuestion",
                "save",
                "cancel",
                "addSuggestedSolution",
                "cancelExplorer",
                "linkChilds",
                "removeSuggestedSolution",
                "parseQuestion",
                "saveEdit",
                "suggestRange"
            ), $classname, "", $force_active);

			$this->addTab_QuestionFeedback($tabs);

			if (in_array($_GET['cmd'], array(
                'importQuestionFromMoodleForm',
                'importQuestionFromMoodle',
                'editQuestion',
                'scoringManagement',
                'scoringManagementPanel',
                'randomisationAndSecurity',
                'deleteDeployedSeed',
                'post',
                'exportQuestiontoMoodleForm',
                'exportQuestionToMoodle',
                'generateNewVariants',
                'setAsActiveVariant',
                'runUnitTest',
                'runUnitTestForAllVariants',
                'runAllTestsForActiveVariant',
                'runAllTestsForAllVariants',
                'addCustomTestForm',
                'addStandardTest',
                'editTestcases',
                'deleteUnitTest'
            ))) {
				$tabs->addSubTab('edit_question', $this->plugin->txt('edit_question'), $this->ctrl->getLinkTargetByClass($classname, "editQuestion"));
				$tabs->addSubTab('scoring_management', $this->plugin->txt('scoring_management'), $this->ctrl->getLinkTargetByClass($classname, "scoringManagementPanel"));
				$tabs->addSubTab('randomisation_and_security', $this->plugin->txt('ui_author_randomisation_and_security_title'), $this->ctrl->getLinkTargetByClass($classname, "randomisationAndSecurity"));
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

		if (in_array($_GET['cmd'], array('importQuestionFromMoodleForm',
            'importQuestionFromMoodleForm',
            'importQuestionFromMoodle',
            'editQuestion',
            'scoringManagement',
            'scoringManagementPanel',
            'randomisationAndSecurity',
            'deleteDeployedSeed',
            'post',
            'exportQuestiontoMoodleForm',
            'exportQuestionToMoodle',
            'generateNewVariants',
            'setAsActiveVariant',
            'runUnitTest',
            'runUnitTestForAllVariants',
            'runAllTestsForActiveVariant',
            'runAllTestsForAllVariants',
            'addCustomTestForm',
            'addStandardTest',
            'editTestcases',
            'deleteUnitTest'
        ))) {
			$tabs->addSubTab('edit_question', $this->plugin->txt('edit_question'), $this->ctrl->getLinkTargetByClass($classname, "editQuestion"));
			$tabs->addSubTab('scoring_management', $this->plugin->txt('scoring_management'), $this->ctrl->getLinkTargetByClass($classname, "scoringManagementPanel"));
			$tabs->addSubTab('randomisation_and_security', $this->plugin->txt('ui_author_randomisation_and_security_title'), $this->ctrl->getLinkTargetByClass($classname, "randomisationAndSecurity"));
		}

	}

	/**
	 * Redirects to the import from MoodleXML Form
	 * @return void
	 */
	public function importQuestionFromMoodleForm()
	{
		global $DIC, $tpl;

		$lng = $DIC->language();
		$tabs = $DIC->tabs();

		//#25145
		if (isset($_REQUEST["test_ref_id"])) {
			$tpl->setOnScreenMessage('failure', $lng->txt("qpl_qst_xqcas_import_in_test_error"), TRUE);
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
		global $DIC, $tpl;
		$tabs = $DIC->tabs();

		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('import_from_moodle');

		//Getting the xml file from $_FILES
		if (file_exists($_FILES["questions_xml"]["tmp_name"])) {
			$xml_file = $_FILES["questions_xml"]["tmp_name"];
		} else {
			$tpl->setOnScreenMessage('failure', $this->plugin->txt('error_import_question_in_test'), true);
			return;
		}

		//CHECK FOR NOT ALLOW IMPROT QUESTIONS DIRECTLY IN TESTS
		if (isset($_GET['calling_test'])) {
			$tpl->setOnScreenMessage('failure', $this->plugin->txt('error_import_question_in_test'), true);
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

		global $DIC, $tpl;
		$tabs = $DIC->tabs();
		$lng = $DIC->language();

        $tpl->setOnScreenMessage('info', $lng->txt("qpl_qst_xqcas_page_editor_compatibility_info"),true);

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
		global $DIC, $tpl;
		$tabs = $DIC->tabs();
		$lng = $DIC->language();

		//require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/export/MoodleXML/class.assStackQuestionMoodleXMLExport.php';

		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('export_to_moodle');

		//Getting data from POST
		if (isset($_POST['first_question_id']) and isset($_POST['xqcas_all_from_pool'])) {
			$question_id = (int)$_POST['first_question_id'];
			$q_type_id = (int) $this->object->getQuestionTypeID();
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
				$tpl->setOnScreenMessage('failure', $e, true);
			}

		} else {
			$tpl->setOnScreenMessage('failure', $lng->txt('qpl_qst_xqcas_error_exporting_to_moodle_question_id'), true);
		}
	}


    /**
     * Redirects to the Deployed Seeds Tabs
     * @param int|null $force_active_seed
     * @return void
     * @throws StackException
     * @throws ilCtrlException
     * @throws stack_exception
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
            'question'  => $this->object,
        );
        $ui = new RandomisationAndSecurityUI($array);

		//Add MathJax (Ensure MathJax is loaded)
		//include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));

		//Add CSS
		//$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_deployed_seeds_management.css'));

		//Returns Deployed seeds form
        try {
            $this->tpl->setVariable("QUESTION_DATA", $ui->show());
        } catch (stack_exception $e) {
            $this->tpl->setVariable("QUESTION_DATA", $DIC->ui()->renderer()->render(
                $DIC->ui()->factory()->messageBox()->failure($e->getMessage()))
            );
        }
	}

	/**
	 * Deletes a deployed seed
	 */
	public function deleteDeployedSeed(): void
	{
		global $DIC;
        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $tabs = $DIC->tabs();
		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('randomisation_and_security');

		//New seed creation
        $active_variant = (int) $_GET['active_variant'];
		$seed = (int) $_GET['variant_identifier'];
		$question_id = (int) $_GET['q_id'];

        if ($seed != $active_variant) {
            //delete seed
            assStackQuestionDB::_deleteStackSeeds($question_id, '', $seed);

            $this->randomisationAndSecurity();
        } else {
            $content = $renderer->render($factory->messageBox()->failure($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_cannot_delete_active_variant')));

            $content .= $renderer->render($factory->button()->standard($DIC->language()->txt("back"), $this->ctrl->getLinkTarget($this, "randomisationAndSecurity")));

            $this->tpl->setContent($content);
        }
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
			$this->object->setErrors($this->plugin->txt('sco_invalid_value'));
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
        global $tpl;

		//Get new points value and save it to the DB
		if (isset($_POST['new_scoring']) and (float)$_POST['new_scoring'] > 0.0) {
			$this->object->setPoints((float)ilUtil::stripSlashes($_POST['new_scoring']));
			$this->object->saveQuestionDataToDb($this->object->getId());
		} else {
            $tpl->setContent
            ($this->plugin->txt('sco_invalid_value'));
		}
		//Show scoring panel
		$this->scoringManagementPanel();
	}

	/**
	 * Command for edit testcases
	 */
	public function editTestcases(): void
	{

		global $DIC;
		$tabs = $DIC->tabs();

        $globalTemplate = $DIC->ui()->mainTemplate();

		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('randomisation_and_security');

		if (isset($_GET['test_case'])) {
            $ui = new RandomisationAndSecurityUI([]);
            $unit_test_data = $this->object->getUnitTests();
            $render = $ui->showEditCustomTestForm($unit_test_data['test_cases'][$_GET['test_case']], $this->object->prts, $this->object);
		} else {
            $factory = $DIC->ui()->factory();
			$render = $DIC->ui()->renderer()->render($factory->messageBox()->failure($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_no_test_case_selected')));
		}

        $globalTemplate->setContent($render);
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
     * @return bool
     */
	public function getIsPreview(): bool
	{
		return $this->is_preview;
	}

    /**
     * @param bool $is_preview
     */
	public function setIsPreview(bool $is_preview): void
	{
		$this->is_preview = $is_preview;
	}

    public function changeActiveVariant()
    {
        $ui = new RandomisationAndSecurityUI([]);
        $this->tpl->setContent($ui->show_form_in_modal());
    }

    public function editUnitTestUI(){
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $ui = new RandomisationAndSecurityUI([]);
        $this->tpl->setContent($ui->show_form_in_modal());
    }


    public function setAsActiveVariant()
    {
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        if(isset($_GET['variant_identifier'])){
            $variant_id = $_GET['variant_identifier'];
            assStackQuestionDB::_saveSeedForPreview($this->object->getId(),(int)$variant_id);
        }
        $this->randomisationAndSecurity();
    }

    /**
     * @throws stack_exception
     */
    public function generateNewVariants()
    {
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $start_time = time();
        $generated_seeds = 0;

        $question_notes = array();

        foreach ($this->object->deployed_seeds as $seed) {
            $temp_question = clone $this->object;
            $temp_question->questionInitialisation($seed, true);
            $question_notes[$temp_question->question_note_instantiated->get_rendered()] = true;
        }

        while($generated_seeds < 10 && time() - $start_time < 15){
            $seed = rand(1111111111,9999999999);

            $temp_question = clone $this->object;
            $temp_question->questionInitialisation($seed, true);
            $question_note_instantiated = $temp_question->question_note_instantiated->get_rendered();

            if (!isset($question_notes[$question_note_instantiated])) {
                $generated_seeds++;
                $this->object->deployed_seeds[$seed] = $seed;
                $question_notes[$question_note_instantiated] = true;
                assStackQuestionDB::_saveStackSeeds($this->object,'add', $seed);
            }
        }

        $content = "";

        if ($generated_seeds > 0) {
            $content .= $renderer->render($factory->messageBox()->success((string)$generated_seeds . ' '. $DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_sucessfully_on_seeds_generation')));
        } else {
            $content .= $renderer->render($factory->messageBox()->failure($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_failed_on_seeds_generation')));
        }

        $content .= $renderer->render($factory->button()->standard($DIC->language()->txt("back"), $this->ctrl->getLinkTarget($this, "randomisationAndSecurity")));

        $this->tpl->setContent($content);
    }

    /**
     * @throws stack_exception
     * @throws StackException
     */
    public function runAllTestsForActiveVariant()
    {
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $unit_tests = $this->object->getUnitTests();
        $unit_test_results = array();

        foreach ($unit_tests["test_cases"] as $test_case => $unit_test) {
            $inputs = array();

            foreach ($unit_test["inputs"] as $name => $input) {
                $inputs[$name] = $input["value"];
            }

            $testcase = new StackUnitTest($unit_test["description"], $inputs, (int) $test_case);

            foreach ($unit_test["expected"] as $name => $expected) {
                $testcase->addExpectedResult($name, new stack_potentialresponse_tree_state(1, true, (float) $expected["score"], (float) $expected["penalty"], '', array($expected["answer_note"])));
            }

            $result = $testcase->run($this->object->getId(), (int) $_GET["active_variant_identifier"]);

            $unit_test_results[$test_case] = $result->passed();
        }

        $content = "";

        foreach ($unit_test_results as $test_case => $result) {
            if ($result === '1') {
                $content .= $renderer->render($factory->messageBox()->success(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_passed_for_seed'), $test_case, $_GET["active_variant_identifier"])));
            } elseif ($result === '0') {
                $content .= $renderer->render($factory->messageBox()->failure(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_failed_empty_for_seed'), $test_case, $_GET["active_variant_identifier"])));
            } else {
                $content .= $renderer->render($factory->messageBox()->failure(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_failed_for_seed'), $test_case, $result, $_GET["active_variant_identifier"])));
            }
        }

        $content .= $renderer->render($factory->button()->standard($DIC->language()->txt("back"), $this->ctrl->getLinkTarget($this, "randomisationAndSecurity")));

        $this->tpl->setContent($content);
    }

    /**
     * @throws stack_exception
     * @throws StackException
     */
    public function runAllTestsForAllVariants()
    {
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $unit_tests = $this->object->getUnitTests();
        $unit_test_results = array();

        foreach ($this->object->deployed_seeds as $seed) {
            foreach ($unit_tests["test_cases"] as $test_case => $unit_test) {
                $inputs = array();

                foreach ($unit_test["inputs"] as $name => $input) {
                    $inputs[$name] = $input["value"];
                }

                $testcase = new StackUnitTest($unit_test["description"], $inputs, (int) $test_case);

                foreach ($unit_test["expected"] as $name => $expected) {
                    $testcase->addExpectedResult($name, new stack_potentialresponse_tree_state(1, true, (float) $expected["score"], (float) $expected["penalty"], '', array($expected["answer_note"])));
                }

                $result = $testcase->run($this->object->getId(), (int) $seed);

                $unit_test_results[] = array(
                    'test_case' => $test_case,
                    'seed' => $seed,
                    'result' => $result->passed()
                );
            }
        }

        $content = "";

        foreach ($unit_test_results as $result) {
            if ($result['result'] === '1') {
                $content .= $renderer->render($factory->messageBox()->success(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_passed_for_seed'), $result['test_case'], $result['seed'])));
            } elseif ($result['result'] === '0') {
                $content .= $renderer->render($factory->messageBox()->failure(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_failed_empty_for_seed'), $result['test_case'], $result['seed'])));
            } else {
                $content .= $renderer->render($factory->messageBox()->failure(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_failed_for_seed'), $result['test_case'], $result['result'], $result['seed'])));
            }
        }

        $content .= $renderer->render($factory->button()->standard($DIC->language()->txt("back"), $this->ctrl->getLinkTarget($this, "randomisationAndSecurity")));

        $this->tpl->setContent($content);
    }

    /**
     * Shows the form for adding a custom test
     * @return void
     */
    public function addCustomTestForm(): void
    {
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $ui = new RandomisationAndSecurityUI([]);
        $this->tpl->setContent($ui->showCustomTestForm($this->object->inputs, $this->object->prts, $this->object));
    }

    /**
     * Called when executing a specific test
     * @throws stack_exception
     * @throws StackException
     */
    public function runUnitTest()
    {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $unit_test = $this->object->getUnitTests()["test_cases"][$_GET["test_case"]];

        $inputs = array();

        foreach ($unit_test["inputs"] as $name => $input) {
            $inputs[$name] = $input["value"];
        }

        $testcase = new StackUnitTest($unit_test["description"], $inputs, (int)$_GET["test_case"]);

        foreach ($unit_test["expected"] as $name => $expected) {
            $testcase->addExpectedResult($name,
                new stack_potentialresponse_tree_state(
                    1,
                    true,
                    (float)$expected["score"],
                    (float)$expected["penalty"],
                    '', array($expected["answer_note"]
                )));
        }

        $result = $testcase->run($this->object->getId(), (int)$_GET["variant_identifier"]);

        $message = $testcase->testCase . ': ';
        if ($result->passed() === '1') {
            $type = 'success';
            $message .= $DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_passed');
        } else {
            $type = 'failure';
            $message .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_failed'), $result->passed());
        }

        $tpl->setOnScreenMessage($type, $message, true);

        $this->randomisationAndSecurity();
    }

    /**
     * Called when executing a specific test for all variants
     * @throws stack_exception
     * @throws StackException
     */
    public function runUnitTestForAllVariants() :void {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $unit_test = $this->object->getUnitTests()["test_cases"][$_GET["test_case"]];

        $inputs = array();

        foreach ($unit_test["inputs"] as $name => $input) {
            $inputs[$name] = $input["value"];
        }

        $testcase = new StackUnitTest($unit_test["description"], $inputs, (int)$_GET["test_case"]);

        foreach ($unit_test["expected"] as $name => $expected) {
            $testcase->addExpectedResult($name,
                new stack_potentialresponse_tree_state(
                    1,
                    true,
                    (float)$expected["score"],
                    (float)$expected["penalty"],
                    '', array($expected["answer_note"]
                )));
        }

        $unit_test_results = array();

        foreach ($this->object->deployed_seeds as $seed) {
            $result = $testcase->run($this->object->getId(), (int)$seed);
            $unit_test_results[] = array(
                'seed' => $seed,
                'result' => $result->passed()
            );
        }

        $content = "";

        foreach ($unit_test_results as $result) {
            if ($result['result'] === '1') {
                $content .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_passed_for_seed'), $_GET["test_case"], $result['seed'])));
            } else {
                $content .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure(sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_failed_empty_for_seed'), $_GET["test_case"], $result['seed'])));
            }
        }

        $tpl->setContent($content);
    }

    /**
     * Called when adding a standard test
     * @throws stack_exception
     */
    public function addStandardTest(): void
    {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $seed = assStackQuestionDB::_getSeed('preview', $this->object, $DIC->user()->getId());

        if (!$this->object->isInstantiated()) {
            $this->object->questionInitialisation($seed, true);
        }

        StackUnitTest::addDefaultTestcase($this->object);

        $tpl->setOnScreenMessage('success',
            $this->object->getPlugin()->txt('ui_author_randomisation_standard_unit_test_case_added'),
            true);

        $this->randomisationAndSecurity();
    }

    /**
     * @throws stack_exception
     * @throws StackException

    protected function fillWithCorrectResponses()
    {
        global $DIC;
        $this->preview_correct = true;
        $this->tpl->setContent($this->getPreview(true, true));
    }*/

    /**
     * Called when deleting one unit test
     * @return void
     */
    public function deleteUnitTest()
    {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $test_case = (int)$_GET['test_case'];

        unset($this->object->unit_tests['ids'][$test_case]);
        unset($this->object->unit_tests['test_cases'][$test_case]);

        assStackQuestionDB::_deleteStackUnitTests($this->object->getId(), $test_case);
        $tpl->setOnScreenMessage('success',
            $this->object->getPlugin()->txt('ui_author_randomisation_unit_test_case_deleted'),
            true);

        $this->randomisationAndSecurity();
    }
}