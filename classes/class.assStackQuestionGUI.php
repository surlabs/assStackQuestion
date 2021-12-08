<?php

/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */


/**
 * STACK Question GUI
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version    $Id: 4.0$$
 * @ingroup    ModulesTestQuestionPool
 * @ilCtrl_isCalledBy assStackQuestionGUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 * @ilCtrl_Calls assStackQuestionGUI: ilFormPropertyDispatchGUI
 *
 */
class assStackQuestionGUI extends assQuestionGUI
{
	/* ILIAS CORE ATTRIBUTES BEGIN */

	/* ILIAS CORE ATTRIBUTES END */

	/* ILIAS VERSION SPECIFIC ATTRIBUTES BEGIN */

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

	/* ILIAS VERSION SPECIFIC ATTRIBUTES END */

	/* ILIAS REQUIRED METHODS BEGIN */

	/**
	 * assStackQuestionGUI constructor.
	 */
	public function __construct($id = -1)
	{
		parent::__construct();

		//Initialize plugin object
		require_once './Services/Component/classes/class.ilPlugin.php';
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
			$this->object->loadFromDb($id);
		}

		//Initialize some STACK required parameters
		$this->getPlugin()->includeClass('utils/class.assStackQuestionInitialization.php');
	}

	public function getSpecificFeedbackOutput($userSolution)
	{
		// TODO: Implement getSpecificFeedbackOutput() method.
		echo "getSpecificFeedbackOutput";
	}

	public function getSolutionOutput($active_id, $pass = null, $graphicalOutput = false, $result_output = false, $show_question_only = true, $show_feedback = false, $show_correct_solution = false, $show_manual_scoring = false, $show_question_text = true)
	{
		// TODO: Implement getSolutionOutput() method.
		echo "getSolutionOutput";
	}

	/**
	 * @param bool $show_question_only
	 * @param bool $show_inline_feedback
	 * @return string HTML
	 */
	public function getPreview($show_question_only = false, $show_inline_feedback = false): string
	{
		global $DIC;

		//User response from session
		$this->object->setUserResponse(is_object($this->getPreviewSession()) ? (array)$this->getPreviewSession()->getParticipantsSolution() : array());

		//Variant management
		if (isset($_REQUEST['fixed_seed'])) {
			$variant = (int)$_REQUEST['fixed_seed'];
			$_SESSION['q_seed_for_preview_' . $this->object->getId() . ''] = $variant;
		} else {
			if (isset($_SESSION['q_seed_for_preview_' . $this->object->getId() . ''])) {
				$variant = (int)$_SESSION['q_seed_for_preview_' . $this->object->getId() . ''];
			} else {
				$variant = 1;
			}
		}

		//Initialise the question
		$this->object->questionInitialisation($variant);

		//Render question Preview
		$this->getPlugin()->includeClass('class.assStackQuestionRenderer.php');
		$question_preview = assStackQuestionRenderer::_renderQuestionPreview($this->object, $show_inline_feedback);

		//Tab management
		$tabs = $DIC->tabs();
		if ($_GET['cmd'] == 'edit') {
			$tabs->activateTab('edit_page');
		} elseif ($_GET['cmd'] == 'preview') {
			$tabs->activateTab('preview');
		}

		//Returns output (with page if needed)
		if (!$show_question_only) {
			// get page object output
			$question_preview = $this->getILIASPage($question_preview);
		}

		return $question_preview;
	}

	public function getTestOutput($active_id, $pass, $is_question_postponed, $user_post_solutions, $show_specific_inline_feedback)
	{
		echo "getTestOutput";

		// TODO: Implement getTestOutput() method.
	}

	/* ILIAS REQUIRED METHODS END */

	/* ILIAS OVERWRITTEN METHODS BEGIN */

	/**
	 * CALLED BEFORE EDITQUESTION()
	 * Evaluates a posted edit form and writes the form data in the question object
	 * (called frm generic commands in assQuestionGUI)
	 * Converts the data from post into assStackQuestion ($this->object)
	 *
	 * @return integer    0: question can be saved / 1: form is not complete
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
	 * Converts the data from post into assStackQuestion ($this->object)
	 */
	public function writeQuestionSpecificPostData(): void
	{
		//OPTIONS
		$this->object->getOptions()->writePostData($this->getRTETags());
		$this->object->getExtraInfo()->writePostData($this->getRTETags());

		//INPUTS
		/*
		 * Management of Input addition and deletion done here
		 * In STACK new inputs are created if a placeholder in question text exist so, addition and deletion must be managed here.
		 */
		$text_inputs = stack_utils::extract_placeholders($this->object->getQuestion(), 'input');

		//Edition and Deletion of inputs
		foreach ($this->object->getInputs() as $input_name => $input) {
			if (in_array($input_name, $text_inputs)) {
				//Check if there exists placeholder in text
				if (isset($_POST[$input_name . '_input_type'])) {
					$input->writePostData($input_name);
				}
			} else {
				//If doesn' exist, check if must be deleted
				if (is_array($this->object->getInputs())) {
					if (sizeof($this->object->getInputs()) < 2) {
						//If there are less than two inputs you cannot delete it
						//Add placeholder to question text
						$this->object->setQuestion($this->object->getQuestion() . " [[input:{$input_name}]]  [[validation:{$input_name}]]");
					}
				} else {
					//Delete input from object
					$db_inputs = $this->object->getInputs();
					unset($db_inputs[$input_name]);
					$this->object->setInputs($db_inputs);
					//Delete input from DB
					$input->delete();
				}
			}
		}
		//Addition of inputs
		foreach ($text_inputs as $input_name) {
			if (is_null($this->object->getInputs($input_name))) {
				//Create new Input
				$new_input = new assStackQuestionInput(-1, $this->object->getId(), $input_name, 'algebraic', "");
				$new_input->getDefaultInput();
				$new_input->checkInput(TRUE);
				$new_input->save();
				$this->object->setInputs($input, $input_name);

				//$this->object->setErrors(array("new_input" => $this->object->getPlugin()->txt("new_input_info_message")));
			}
		}

		//PRT
		if (is_array($this->object->getPotentialResponsesTrees())) {
			foreach ($this->object->getPotentialResponsesTrees() as $prt_name => $prt) {
				if (isset($_POST['prt_' . $prt_name . '_value'])) {
					$prt->writePostData($prt_name, "", $this->getRTETags());
				}
				//Add new node if info is filled in
				if ($_POST['prt_' . $prt->getPRTName() . '_node_' . $prt->getPRTName() . '_new_node_student_answer'] != "" and $_POST['prt_' . $prt->getPRTName() . '_node_' . $prt->getPRTName() . '_new_node_teacher_answer'] != "") {
					$new_node = new assStackQuestionPRTNode(-1, $this->object->getId(), $prt->getPRTName(), $prt->getLastNodeName() + 1, $_POST['prt_' . $prt->getPRTName() . '_node_' . $prt->getPRTName() . '_new_node_pos_next'], $_POST['prt_' . $prt->getPRTName() . '_node_' . $prt->getPRTName() . '_new_node_neg_next']);
					$new_node->writePostData($prt_name, $prt_name . '_new_node', "", $new_node->getNodeName(), $this->getRTETags());
				}
			}
		}

		//Addition of PRT and Nodes
		//New PRT (and node) if the new prt is filled
		if (isset($_POST['prt_new_prt_name']) and $_POST['prt_new_prt_name'] != 'new_prt' and !preg_match('/\s/', $_POST['prt_new_prt_name'])) {
			//the prt name given is not used in this question
			$new_prt = new assStackQuestionPRT(-1, $this->object->getId());
			$new_prt_node = new assStackQuestionPRTNode(-1, $this->object->getId(), ilUtil::stripSlashes($_POST['prt_new_prt_name']), '1', -1, -1);
			$new_prt->setPRTNodes(array('0' => $new_prt_node));
			$new_prt->writePostData('new_prt', ilUtil::stripSlashes($_POST['prt_new_prt_name']), $this->getRTETags());

			//Add new Token
			$specific_feedback = $this->object->getOptions()->getSpecificFeedback();
			$specific_feedback .= "<p>[[feedback:" . ilUtil::stripSlashes($_POST['prt_new_prt_name']) . "]]</p>";
			$this->object->getOptions()->setSpecificFeedback($specific_feedback);
		}

		if (preg_match('/\s/', $_POST['prt_new_prt_name'])) {
			$this->question_gui->object->setErrors($this->object->getPlugin()->txt('error_not_valid_prt_name'));
		}

	}

	/**
	 * Populate taxonomy section in a form
	 * (made public to be called from authoring GUI)
	 *
	 * @param ilPropertyFormGUI $form
	 */
	public function populateTaxonomyFormSection(ilPropertyFormGUI $form)
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
	 * @param bool $checkonly
	 *
	 * @return bool
	 */
	public function editQuestion($checkonly = false)
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
		$this->plugin->includeClass('GUI/question_authoring/class.assStackQuestionAuthoringGUI.php');
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

		//Returns Question Authoring form
		if (!$checkonly) {
			$this->tpl->setVariable("QUESTION_DATA", $authoring_gui->showAuthoringPanel());
		}
	}

	/* ILIAS GUI COMMANDS METHODS END */

	/* METHODS TO REDESIGN BEGIN */

	/**
	 * old deletionManagement()
	 * Called by writePostData
	 * Not only delete unused objects but handles also the copy/paste of nodes.
	 * Access the DB
	 * TODO
	 */
	public function questionCheck(): void
	{
		echo "questionCheck";

		//TODO
	}


	public function checkPRTForDeletion(assStackQuestionPRT $prt)
	{
		echo "checkPRTForDeletion";

		if (is_array($this->object->getPotentialResponsesTrees())) {
			if (sizeof($this->object->getPotentialResponsesTrees()) < 2) {
				$this->object->setErrors($this->object->getPlugin()->txt('deletion_error_not_enought_prts'));

				return TRUE;
			}
		}


		return FALSE;
	}

	public function checkPRTNodeForDeletion(assStackQuestionPRT $prt, assStackQuestionPRTNode $node)
	{
		echo "checkPRTNodeForDeletion";

		if (is_array($prt->getPRTNodes())) {
			if (sizeof($prt->getPRTNodes()) < 2) {
				$this->object->setErrors($this->object->getPlugin()->txt('deletion_error_not_enought_prt_nodes'));

				return TRUE;
			}
		}


		if ((int)$prt->getFirstNodeName() == (int)$node->getNodeName()) {
			$this->object->setErrors($this->object->getPlugin()->txt('deletion_error_first_node'));

			return TRUE;
		}

		foreach ($prt->getPRTNodes() as $prt_node) {
			if ($prt_node->getTrueNextNode() == $node->getNodeName() or $prt_node->getFalseNextNode() == $node->getNodeName()) {
				$this->object->setErrors($this->object->getPlugin()->txt('deletion_error_connected_node'));

				return TRUE;
			}
		}

		return FALSE;
	}



	/* METHODS TO REDESIGN END */

	/* RTE, Javascript, Ajax, jQuery etc. METHODS BEGIN */

	/**
	 * Decides whether to show the information fields in the session
	 * Called by editQuestion onLoad
	 */
	public function enableDisableInfo()
	{
		echo "enableDisableInfo";

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
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
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



	/* RTE, Javascript, Ajax, jQuery etc. METHODS END */

	/* TABS MANAGEMENT BEGIN */

	/**
	 * Sets the ILIAS tabs for this question type
	 * called from ilObjTestGUI and ilObjQuestionPoolGUI
	 */
	public function setQuestionTabs()
	{
		global $DIC, $rbacsystem;

		$tabs = $DIC->tabs();

		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$this->plugin->includeClass('class.ilAssStackQuestionFeedback.php');

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

			if (in_array($_GET['cmd'], array('importQuestionFromMoodleForm', 'importQuestionFromMoodle', 'editQuestion', 'scoringManagement', 'scoringManagementPanel', 'deployedSeedsManagement', 'createNewDeployedSeed', 'deleteDeployedSeed', 'showUnitTests', 'runTestcases', 'createTestcases', 'post', 'exportQuestiontoMoodleForm', 'exportQuestionToMoodle',))) {
				$tabs->addSubTab('edit_question', $this->plugin->txt('edit_question'), $this->ctrl->getLinkTargetByClass($classname, "editQuestion"));
				$tabs->addSubTab('scoring_management', $this->plugin->txt('scoring_management'), $this->ctrl->getLinkTargetByClass($classname, "scoringManagementPanel"));
				$tabs->addSubTab('deployed_seeds_management', $this->plugin->txt('dsm_deployed_seeds'), $this->ctrl->getLinkTargetByClass($classname, "deployedSeedsManagement"));
				$tabs->addSubTab('unit_tests', $this->plugin->txt('ut_title'), $this->ctrl->getLinkTargetByClass($classname, "showUnitTests"));
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

	public function getLearningModuleTabs()
	{
		global $DIC;
		$tabs = $DIC->tabs();

		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$this->plugin->includeClass('class.ilAssStackQuestionFeedback.php');

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

		if (in_array($_GET['cmd'], array('importQuestionFromMoodleForm', 'importQuestionFromMoodle', 'editQuestion', 'scoringManagement', 'scoringManagementPanel', 'deployedSeedsManagement', 'createNewDeployedSeed', 'deleteDeployedSeed', 'showUnitTests', 'runTestcases', 'createTestcases', 'post', 'exportQuestiontoMoodleForm', 'exportQuestionToMoodle',))) {
			$tabs->addSubTab('edit_question', $this->plugin->txt('edit_question'), $this->ctrl->getLinkTargetByClass($classname, "editQuestion"));
			$tabs->addSubTab('scoring_management', $this->plugin->txt('scoring_management'), $this->ctrl->getLinkTargetByClass($classname, "scoringManagementPanel"));
			$tabs->addSubTab('deployed_seeds_management', $this->plugin->txt('dsm_deployed_seeds'), $this->ctrl->getLinkTargetByClass($classname, "deployedSeedsManagement"));
			$tabs->addSubTab('unit_tests', $this->plugin->txt('ut_title'), $this->ctrl->getLinkTargetByClass($classname, "showUnitTests"));
		}

	}

	/* TABS MANAGEMENT END */

	/* IMPORT / EXPORT TO MOODLE BEGIN */

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

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

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
			$this->object->setErrors($this->plugin->txt('error_import_question_in_test'), true);

			return;
		}

		//CHECK FOR NOT ALLOW IMPROT QUESTIONS DIRECTLY IN TESTS
		if (isset($_GET['calling_test'])) {
			$this->object->setErrors($this->plugin->txt('error_import_question_in_test'), true);

			return;
		} else {
			//Include import class and prepare object
			$this->plugin->includeClass('model/import/MoodleXML/class.assStackQuestionMoodleImport.php');
			$import = new assStackQuestionMoodleImport($this->plugin, (int)$_POST['first_question_id'], $this->object);
			$import->setRTETags($this->getRTETags());
			$import->import($xml_file);
		}
	}

	public function exportQuestiontoMoodleForm()
	{
		global $DIC;
		$tabs = $DIC->tabs();
		$lng = $DIC->language();

		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('export_to_moodle');

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

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

	public function exportQuestionToMoodle()
	{
		global $DIC;
		$tabs = $DIC->tabs();
		$lng = $DIC->language();

		require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/export/MoodleXML/class.assStackQuestionMoodleXMLExport.php';

		//Set all parameters required
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('export_to_moodle');

		//Getting data from POST
		if (isset($_POST['first_question_id']) and isset($_POST['xqcas_all_from_pool'])) {
			$id = $_POST['first_question_id'];
			$mode = "";
			if ($_POST['xqcas_all_from_pool'] == 'xqcas_export_all_from_pool') {
				//Get all questions from a pool
				$export_to_moodle = new assStackQuestionMoodleXMLExport($this->object->getAllQuestionsFromPool());
				$xml = $export_to_moodle->toMoodleXML();
			} elseif ($_POST['xqcas_all_from_pool'] == 'xqcas_export_only_this') {
				//get current stack question info.
				$export_to_moodle = new assStackQuestionMoodleXMLExport(array($id => $this->object));
				$xml = $export_to_moodle->toMoodleXML();
			} elseif ($_POST['xqcas_all_from_pool'] == 'xqcas_export_all_from_test') {
				//get current stack question info.
				$export_to_moodle = new assStackQuestionMoodleXMLExport($this->object->getAllQuestionsFromTest());
				$xml = $export_to_moodle->toMoodleXML();
			} else {
				throw new Exception($lng->txt('qpl_qst_xqcas_error_exporting_to_moodle_mode'));
			}
		} else {
			throw new Exception($lng->txt('qpl_qst_xqcas_error_exporting_to_moodle_question_id'));
		}
	}

	/* IMPORT / EXPORT TO MOODLE END */

	/* UNIT TESTS COMMANDS BEGIN */

	/**
	 * Command for run testcases
	 */
	public function runTestcases()
	{
		global $DIC;
		$tabs = $DIC->tabs();

		//Set all parameters required
		$this->plugin->includeClass('utils/class.assStackQuestionStackFactory.php');
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
			$this->plugin->includeClass("model/class.assStackQuestionStackQuestion.php");
			$this->object->setStackQuestion(new assStackQuestionStackQuestion());
			$this->object->getStackQuestion()->init($this->object);
		}

		//Create Unit test object
		$this->plugin->includeClass("model/ilias_object/test/class.assStackQuestionUnitTests.php");
		$unit_tests_object = new assStackQuestionUnitTests($this->plugin, $this->object);
		$unit_test_results = $unit_tests_object->runTest($testcase_name);

		//Create GUI object
		$this->plugin->includeClass('GUI/test/class.assStackQuestionTestGUI.php');
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
		$this->plugin->includeClass('utils/class.assStackQuestionStackFactory.php');
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
		$this->plugin->includeClass("model/ilias_object/test/class.assStackQuestionUnitTests.php");
		$unit_tests_object = new assStackQuestionUnitTests($this->plugin, $this->object);

		//Create GUI object
		$this->plugin->includeClass('GUI/test/class.assStackQuestionTestGUI.php');
		$unit_test_gui = new assStackQuestionTestGUI($this, $this->plugin);

		//Add CSS
		$DIC->globalScreen()->layout()->meta()->addCss($this->plugin->getStyleSheetLocation('css/qpl_xqcas_unit_tests.css'));

		//Returns Deployed seeds form
		$this->tpl->setVariable("QUESTION_DATA", $unit_test_gui->editTestcaseForm($testcase_name, $this->object->getInputs(), $this->object->getPotentialResponsesTrees()));
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
		$this->plugin->includeClass('utils/class.assStackQuestionStackFactory.php');
		$tabs->activateTab('edit_properties');
		$tabs->activateSubTab('unit_tests');
		$this->getQuestionTemplate();

		//Create GUI object
		$this->plugin->includeClass('GUI/test/class.assStackQuestionTestGUI.php');
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

	/* UNIT TESTS COMMANDS END */

	/* GETTERS AND SETTERS BEGIN */

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


	/* GETTERS AND SETTERS END */

}