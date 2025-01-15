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
use classes\platform\StackCheckPrtPlaceholders;
use classes\platform\StackException;
use classes\platform\StackPlatform;
use classes\platform\StackUnitTest;
use classes\ui\author\RandomisationAndSecurityUI;
use classes\ui\author\StackQuestionAuthoringUI;


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
     * @var array|string[]
     */
    private array $required_tags;
    private stdClass $info_config;

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
        $display_options['feedback'] = false;

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
        global $DIC, $tpl;

        StackRenderIlias::ensureMathJaxLoaded();

        if (!is_null($active_id) && (int)$active_id !== 0) {
            $purpose = 'test';
            if (is_null($pass)) {
                $pass = ilObjTest::_getPass($active_id);
            }
        } else {
            $purpose = 'preview';
            $active_id = $DIC->user()->getId();
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
        $display_options['show_correct_solution'] = $show_correct_solution;
        $display_options['feedback'] = true;

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

        if (!is_null($this->getPreviewSession()) && $this->getPreviewSession()->getParticipantsSolution() !== null) {
            $user_response = StackUserResponseIlias::getStackUserResponse('preview', $this->object->getId(), $DIC->user()->getId());
        } else {
            assStackQuestionDB::_savePreviewSolution($this->object, array(), $seed);
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

        $question_preview .= StackRenderIlias::renderQuestionVariables(StackRandomisationIlias::getRandomisationData($this->object, $this->object->getSeed()));

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

        //Render question specific feedback
        $specific_feedback_preview = StackRenderIlias::renderSpecificFeedback($attempt_data, $display_options);

        return assStackQuestionUtils::_getLatex($specific_feedback_preview);
    }

    /**
     * @throws ilCtrlException
     * @throws stack_exception
     */
    public function writePostData($always = FALSE): int
	{
        $authoring_ui = new StackQuestionAuthoringUI($this->plugin, $this->object);

        $authoring_ui->writePostData();

        return 0;
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
     */
	public function editQuestion(bool $check_only = false)
	{
		global $DIC;

		$tabs = $DIC->tabs();
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('edit_question');

		$this->getQuestionTemplate();

		$authoring_gui = new StackQuestionAuthoringUI($this->plugin, $this->object);

        $this->tpl->setVariable("QUESTION_DATA", $authoring_gui->showAuthoringPanel());
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
            $commands = $_POST["cmd"] ?? array();
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
                'confirmDeleteAllVariants',
                'deleteAllVariants',
                'editTestcases',
                'confirmRegenerateUnitTest',
                'regenerateUnitTest',
                'deleteUnitTest',
                'checkPrtPlaceholders'
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

		if ((isset($_GET["calling_test"]) && $_GET["calling_test"] > 0) ||
            (isset($_GET["test_ref_id"]) && ($_GET["test_ref_id"] > 0))) {
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
            'confirmDeleteAllVariants',
            'deleteAllVariants',
            'editTestcases',
            'confirmRegenerateUnitTest',
            'regenerateUnitTest',
            'deleteUnitTest',
            'checkPrtPlaceholders'
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
				$tpl->setOnScreenMessage('failure', $e->getMessage(), true);
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

        $tabs = $DIC->tabs();
		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('randomisation_and_security');

		//New seed creation
		$seed = (int) $_GET['variant_identifier'];
		$question_id = (int) $_GET['q_id'];

        //delete seed
        assStackQuestionDB::_deleteStackSeeds($question_id, '', $seed);

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
     * @throws StackException|ilCtrlException
     */
    public function runAllTestsForActiveVariant(): void
    {
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $unit_tests = $this->object->getUnitTests();

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

            $unit_tests['test_cases'][$test_case]['results'][] = $testcase->resultToArray((int) $_GET["active_variant_identifier"], $result);
        }

        $ui = new RandomisationAndSecurityUI(array(
            'unit_tests' => $unit_tests
        ));

        $content = $ui->getTestOverviewPanel();

        $content .= $renderer->render($factory->button()->standard($DIC->language()->txt("back"), $this->ctrl->getLinkTarget($this, "randomisationAndSecurity")));

        $this->tpl->setContent($content);
    }

    /**
     * @throws stack_exception
     * @throws StackException|ilCtrlException
     */
    public function runAllTestsForAllVariants(): void
    {
        global $DIC;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $unit_tests = $this->object->getUnitTests();
        $unit_tests_to_show = array();

        $content = "";

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

                $unit_tests['test_cases'][$test_case]['results'][] = $testcase->resultToArray((int) $seed, $result);
            }

            $ui = new RandomisationAndSecurityUI(array(
                'unit_tests' => $unit_tests,
            ));

            $content .= $ui->getTestOverviewPanel();
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

        $result = $testcase->run($this->object->getId(), (int)$_GET["active_variant_identifier"]);

        $message = $testcase->testCase . ': ';
        if ($result->passed() === '1') {
            $type = 'success';
            $message .= $DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_passed');
        } else {
            $type = 'failure';
            $message .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_author_randomisation_unit_test_case_failed'), $result->passed());
        }

        $unit_tests = assStackQuestionDB::_readUnitTests($this->object->getId());
        $this->object->setUnitTests($unit_tests);

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

        $content = "";

        foreach ($this->object->deployed_seeds as $seed) {
            $result = $testcase->run($this->object->getId(), (int)$seed);

            $unit_test["results"][] = $testcase->resultToArray((int)$seed, $result);

            $ui = new RandomisationAndSecurityUI(array(
                'unit_tests' => array(
                    'ids' => array($_GET["test_case"]),
                    'test_cases' => array($_GET["test_case"] => $unit_test)
                )
            ));

            $content .= $ui->getTestOverviewPanel();
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
     * Called to delete all seeds
     */
    public function confirmDeleteAllVariants(): void
    {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $content = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->confirmation($this->object->getPlugin()->txt('ui_author_randomisation_confirm_delete_all_seeds')));

        $content .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->button()->standard($DIC->language()->txt("yes"), $this->ctrl->getLinkTarget($this, "deleteAllVariants")));
        $content .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->button()->standard($DIC->language()->txt("no"), $this->ctrl->getLinkTarget($this, "randomisationAndSecurity")));

        $tpl->setContent($content);
    }

    /**
     * Called to delete all seeds
     */
    public function deleteAllVariants(): void
    {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        assStackQuestionDB::_deleteStackSeeds($this->object->getId());

        $tpl->setOnScreenMessage('success',
            $this->object->getPlugin()->txt('ui_author_randomisation_all_seeds_deleted'),
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

    /**
     * @throws ilCtrlException
     */
    public function confirmRegenerateUnitTest()
    {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $content = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->confirmation($this->object->getPlugin()->txt('ui_author_randomisation_confirm_regenerate_unit_test')));

        $DIC->ctrl()->setParameter($this, 'test_case', $_GET['test_case']);
        $content .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->button()->standard($DIC->language()->txt("yes"), $this->ctrl->getLinkTarget($this, "regenerateUnitTest")));
        $content .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->button()->standard($DIC->language()->txt("no"), $this->ctrl->getLinkTarget($this, "randomisationAndSecurity")));

        $tpl->setContent($content);
    }

    /**
     * @throws ilCtrlException
     * @throws stack_exception
     * @throws StackException
     */
    public function regenerateUnitTest(): void
    {
        global $DIC, $tpl;
        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $test_case = (int)$_GET['test_case'];

        if (isset($this->object->unit_tests['test_cases'][$test_case])) {
            $seed = assStackQuestionDB::_getSeed('preview', $this->object, $DIC->user()->getId());

            if (!$this->object->isInstantiated()) {
                $this->object->questionInitialisation($seed, true);
            }

            $this->object->unit_tests['test_cases'][$test_case] = StackUnitTest::generateDefaultTestcase($this->object, $test_case);

            assStackQuestionDB::_deleteStackUnitTestResults($this->object->getId(), $test_case);

            assStackQuestionDB::_saveStackUnitTests($this->object, "");

            $tpl->setOnScreenMessage('success', $this->object->getPlugin()->txt('ui_author_randomisation_unit_test_case_regenerated'), true);

            $this->randomisationAndSecurity();
        }
    }

    /**
     * Check if the PRT placeholders are correctly set
     * @return void
     * @throws ilCtrlException|stack_exception
     */
    public function checkPrtPlaceholders()
    {
        global $DIC;

        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');

        $rendered = "";

        if (isset($_GET['calling_test'])) {
            $questions = assStackQuestionDB::_getAllQuestionsFromTest((int) $this->object->getId(), (int) $this->object->getQuestionTypeID(), true);
        } else {
            $questions = assStackQuestionDB::_getAllQuestionsFromPool((int) $this->object->getId(), (int) $this->object->getQuestionTypeID(), true);
        }

        foreach (StackCheckPrtPlaceholders::getErrors($questions) as $question_id => $missing) {
            if (!empty($missing["missing"])) {
                $pane = '<div style="display: flex; width: 100%; justify-content: space-between;">';
                $pane .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_missing_placeholders'), $question_id, implode(', ', $missing["missing"]));
                $this->ctrl->setParameterByClass("assStackQuestionGUI", "question_id", $question_id);
                $pane .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->button()->standard("Fix", $this->ctrl->getLinkTargetByClass("assStackQuestionGUI", "fixPrtPlaceholders")));
                $pane .= '</div>';
                $pane .= '<br><strong>Title: </strong>' . $missing["title"];

                $rendered .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->confirmation($pane));
            } else if (!empty($missing["badname"])) {
                $pane = '<div style="display: flex; width: 100%; justify-content: space-between;">';
                $pane .= sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_bad_name'), $question_id, implode(', ', $missing["badname"]));
                $this->ctrl->setParameterByClass("assStackQuestionGUI", "question_id", $question_id);
                $pane .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->button()->standard("Fix", $this->ctrl->getLinkTargetByClass("assStackQuestionGUI", "fixPrtName")));
                $pane .= '</div>';
                $pane .= '<br><strong>Title: </strong>' . $missing["title"];

                $rendered .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->info($pane));
            } else {
                $pane = sprintf($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_no_prts'), $question_id);
                $pane .= '<br><br><strong>Title: </strong>' . $missing["title"];

                $rendered .= $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($pane));
            }
        }

        if ($rendered == "") {
            $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_all_ok')));
        }

        $this->tpl->setContent($rendered);
    }

    /**
     * Fix the PRT placeholders
     */
    public function fixPrtPlaceholders() {
        global $DIC;

        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');


        if (isset($_GET['question_id'])) {
            $result = StackCheckPrtPlaceholders::fixMissings($_GET['question_id']);

            $rendered = "<h2>" . $DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_placeholders_fixed') . "</h2>";
            $rendered .= "<br><strong>Title: </strong><br>" . $result["title"];
            $rendered .= "<br><br><strong>" . $DIC->language()->txt('qpl_qst_xqcas_options_specific_feedback') . "</strong>:<br>" . $result["specific_feedback"];

            $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($rendered));
        } else {
            $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure("Unknown error"));
        }

        $this->tpl->setContent($rendered);
    }

    /**
     * Fix the PRT names
     */
    public function fixPrtName() {
        global $DIC;

        $tabs = $DIC->tabs();

        $tabs->activateTab('edit_properties');
        $tabs->activateSubTab('randomisation_and_security');


        if (isset($_GET['question_id'])) {
            $result = StackCheckPrtPlaceholders::fixBadNames($_GET['question_id']);

            $rendered = "<h2>" . $DIC->language()->txt('qpl_qst_xqcas_ui_admin_configuration_quality_check_prt_names_fixed') . "</h2>";
            $rendered .= "<br><strong>Title: </strong><br>" . $result["title"];
            $rendered .= "<br><br>" . $result["changed"];

            $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($rendered));
        } else {
            $rendered = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure("Unknown error"));
        }

        $this->tpl->setContent($rendered);
    }
}