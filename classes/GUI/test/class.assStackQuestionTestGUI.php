<?php
/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';

/**
 * STACK Question Unit tests GUI class
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id: 1.8$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionTestGUI
{

	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private $plugin;

	/**
	 * @var ilTemplate for showing the deployed seeds panel
	 */
	private $template;

	/**
	 * @var int
	 */
	private $question_id;


	/**
	 * @var mixed Array with the assStackQuestionTest object of the current question.
	 */
	private $tests;

	/**
	 * @var mixed Unit test results from assStackQuestionUnitTests
	 */
	private $unit_test_results;


	/**
	 * Sets required data for unit tests management
	 * @param $plugin ilassStackQuestionPlugin instance
	 * @param $question_id int
	 * @param $unit_tests array of unit tests
	 */
	function __construct($plugin, $question_id, $unit_test_results = array())
	{
		//Set plugin and template objects
		$this->setPlugin($plugin);
		$this->setTemplate($this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_unit_tests_container.html'));
		$this->setQuestionId($question_id);

		//Set Unit tests data
		$this->setTests(assStackQuestionTest::_read($this->getQuestionId()));
		$this->setUnitTestResults($unit_test_results);
	}

	/**
	 * ### MAIN METHOD OF THIS CLASS ###
	 * @return HTML
	 */
	public function showUnitTestsPanel()
	{
		//Step #1: Fill Unit header
		$this->fillUnitTestHeader();
		//Step #2: Fill Unit tests panel
		$this->fillUnitTestsPanel();
		//Step #3: Returns the html of the panel
		return $this->getTemplate()->get();
	}

	/*
	 * FILLING TEMPLATES
	 */

	/**
	 * Fill unit tests panel headers
	 */
	private function fillUnitTestHeader()
	{
		$this->getTemplate()->setVariable('UNIT_TESTS_TABLE_TITLE', $this->getPlugin()->txt('ut_title'));
		$this->getTemplate()->setVariable('UNIT_TESTS_TABLE_SUBTITLE', $this->getPlugin()->txt('ut_subtitle'));
	}

	/**
	 * Fill unit tests panel
	 */
	private function fillUnitTestsPanel()
	{
		$this->panel_template = $this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_unit_tests_panel.html');
		foreach ($this->getTests() as $unit_test) {
			$this->panel_template->setCurrentBlock('ut_testcase');
			//Fill testcases depending on results
			if (is_array($this->getUnitTestResults($unit_test->getTestCase()))) {
				$this->fillTestcase($unit_test, TRUE);
			} else {
				$this->fillTestcase($unit_test, FALSE);
			}
			$this->panel_template->ParseCurrentBlock();
		}

		//Add form
		$this->getGeneralForm();

		//Set panel to template
		$this->getTemplate()->setVariable('UNIT_TESTS_PANEL', $this->panel_template->get());
	}

	/**
	 * Fill testcases
	 * @param assStackQuestionTest $unit_test
	 * @param boolean $mode
	 */
	private function fillTestcase(assStackQuestionTest $unit_test, $mode)
	{
		//Fill inputs part
		$this->fillInputsPart($unit_test, $mode);
		//Fill prt part
		$this->fillPRTPart($unit_test, $mode);
	}

	/**
	 * Fill inputs part
	 * @param assStackQuestionTest $unit_test
	 * @param boolean $mode
	 */
	private function fillInputsPart($unit_test, $mode)
	{
		//Fill header messages
		$this->inputs_template = $this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_unit_test_inputs_view.html');
		$this->inputs_template->setVariable('UT_TESTCASE_NAME_MESSAGE', $this->getPlugin()->txt('ut_testcase_name'));
		$this->inputs_template->setVariable('UT_TESTCASE_NAME', $unit_test->getTestCase());

		//Fill inputs
		if ($mode) {
			//EXISTS RESULTS FOR THIS TESTCASE
			$results = $this->getUnitTestResults($unit_test->getTestCase());
			$testcase_inputs = $results['inputs'];
			foreach ($testcase_inputs as $input_name => $input_value) {
				if (!preg_match('/_val/', $input_name)) {
					$this->inputs_template->setCurrentBlock('ut_inputs');
					$this->inputs_template->setVariable('UT_INPUT_NAME', $input_name);
					$this->inputs_template->setVariable('UT_SENT_ANSWER', $input_value);
					if ($results['test_passed']) {
						$this->inputs_template->setVariable('UT_TEST_PASSED', $this->getPlugin()->txt('ut_test_passed'));
					} else {
						$this->inputs_template->setVariable('UT_TEST_PASSED', $this->getPlugin()->txt('ut_test_failed'));
					}
					$this->inputs_template->setVariable('TEST_PASSED_COLOR', $this->getTestcaseColor($results['test_passed']));
					$this->inputs_template->ParseCurrentBlock();
				}
			}
		} else {
			//DOESN'T EXISTS RESULTS FOR THIS TESTCASE
			$testcase_inputs = $this->getTests($unit_test->getTestCase());
			foreach ($testcase_inputs->getTestInputs() as $input) {
				$this->inputs_template->setCurrentBlock('ut_inputs');
				$this->inputs_template->setVariable('UT_INPUT_NAME', $input->getTestInputName());
				$this->inputs_template->setVariable('UT_SENT_ANSWER', $input->getTestInputValue());
				$this->inputs_template->setVariable('TEST_PASSED_COLOR', $this->getTestcaseColor('grey'));
				$this->inputs_template->ParseCurrentBlock();
			}
		}

		//Return input part
		$this->panel_template->setVariable('INPUTS_PART', $this->inputs_template->get());
	}

	/**
	 * Fill PRT part
	 * @param assStackQuestionTest $unit_test
	 * @param boolean $mode
	 */
	private function fillPRTPart($unit_test, $mode)
	{
		//Fill header
		$this->prt_template = $this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_unit_test_prt_view.html');
		$this->fillPRTHeader($unit_test);
		//Fill PRT Data
		if ($mode) {
			//EXISTS RESULTS FOR THIS TESTCASE
			$results = $this->getUnitTestResults($unit_test->getTestCase());
			$testcase_prts = $results['prts'];
			foreach ($testcase_prts as $prt_name => $prt_data) {
				$this->prt_template->setCurrentBlock('ut_prt');
				$this->prt_template->setVariable('UT_PRT_PRTNAME', $prt_name);
				//SCORE
				$this->prt_template->setVariable('UT_PRT_EXPECTED_SCORE', $prt_data['expected_score']);
				$this->prt_template->setVariable('UT_PRT_RECEIVED_SCORE', $prt_data['received_score']);
				$this->prt_template->setVariable('UT_SCORE_TEST_COLOR', $this->getTestcaseColor($prt_data['score_test']));
				//PENALTY
				$this->prt_template->setVariable('UT_PRT_EXPECTED_PENALTY', $prt_data['expected_penalty']);
				$this->prt_template->setVariable('UT_PRT_RECEIVED_PENALTY', $prt_data['received_penalty']);
				$this->prt_template->setVariable('UT_PENALTY_TEST_COLOR', $this->getTestcaseColor($prt_data['penalty_test']));
				//ANSWERNOTE
				$this->prt_template->setVariable('UT_PRT_EXPECTED_ANSWERNOTE', $prt_data['expected_answernote']);
				$this->prt_template->setVariable('UT_PRT_RECEIVED_ANSWERNOTE', $prt_data['received_answernote']);
				$this->prt_template->setVariable('UT_ANSWERNOTE_TEST_COLOR', $this->getTestcaseColor($prt_data['answernote_test']));
				//ERRORS AND FEEDBACK
				$this->prt_template->setVariable('UT_PRT_CAS_ERRORS', $prt_data['cas_errors']);
				$this->prt_template->setVariable('UT_PRT_CAS_FEEDBACK', $prt_data['cas_feedback']);
				$this->prt_template->ParseCurrentBlock();
			}
		} else {
			//DOESN'T EXISTS RESULTS FOR THIS TESTCASE
			$testcase_prts = $this->getTests($unit_test->getTestCase());
			foreach ($testcase_prts->getTestExpected() as $prt_data) {
				$this->prt_template->setCurrentBlock('ut_prt');
				$this->prt_template->setVariable('UT_PRT_PRTNAME', $prt_data->getTestPRTName());
				//SCORE
				$this->prt_template->setVariable('UT_PRT_EXPECTED_SCORE', $prt_data->getExpectedScore());
				//PENALTY
				$this->prt_template->setVariable('UT_PRT_EXPECTED_PENALTY', $prt_data->getExpectedPenalty());
				//ANSWERNOTE
				$this->prt_template->setVariable('UT_PRT_EXPECTED_ANSWERNOTE', $prt_data->getExpectedAnswerNote());
				$this->prt_template->ParseCurrentBlock();
			}
		}
		$this->panel_template->setVariable('PRTS_PART', $this->prt_template->get());
	}

	/**
	 * Fill header of testcase
	 * @param assStackQuestionTest $unit_test
	 */
	private function fillPRTHeader($unit_test)
	{
		$this->prt_template->setVariable('UT_PRT_PRTNAME_H', $this->getTestcaseCommandsForm($unit_test));
		$this->prt_template->setVariable('UT_PRT_EXPECTED_SCORE_H', $this->getPlugin()->txt('ut_expected_mark'));
		$this->prt_template->setVariable('UT_PRT_RECEIVED_SCORE_H', $this->getPlugin()->txt('ut_received_mark'));
		$this->prt_template->setVariable('UT_PRT_EXPECTED_PENALTY_H', $this->getPlugin()->txt('ut_expected_penalty'));
		$this->prt_template->setVariable('UT_PRT_RECEIVED_PENALTY_H', $this->getPlugin()->txt('ut_received_penalty'));
		$this->prt_template->setVariable('UT_PRT_EXPECTED_ANSWERNOTE_H', $this->getPlugin()->txt('ut_expected_answer_note'));
		$this->prt_template->setVariable('UT_PRT_RECEIVED_ANSWERNOTE_H', $this->getPlugin()->txt('ut_received_answer_note'));
		$this->prt_template->setVariable('UT_PRT_CAS_ERRORS_H', $this->getPlugin()->txt('ut_cas_errors'));
		$this->prt_template->setVariable('UT_PRT_CAS_FEEDBACK_H', $this->getPlugin()->txt('ut_cas_feedback'));
	}

	/*
	 * FORMS
	 */

	/**
	 * Get form for create unit test and run all.
	 */
	private function getGeneralForm()
	{
		global $ilCtrl, $lng;

		//Initialization
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormActionByClass('assStackQuestionGUI'));

		//Values
		$question_id = new ilHiddenInputGUI('question_id');
		$question_id->setValue($this->getQuestionId());
		$form->addItem($question_id);

		//Commands
		$form->addCommandButton("createTestcases", $lng->txt("create"));
		$form->addCommandButton("runTestcases", $this->getPlugin()->txt('ut_run_all_tests'));
		$form->setShowTopButtons(FALSE);

		$this->panel_template->setVariable('UNIT_TEST_GENERAL_FORM', $form->getHTML());
	}

	/**
	 * Returns the commands form for one testcase
	 * @param assStackQuestionTest $unit_test
	 * @return HTML
	 */
	private function getTestcaseCommandsForm(assStackQuestionTest $unit_test)
	{
		global $ilCtrl;

		//Initialization
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormActionByClass('assStackQuestionGUI'));

		//Values
		$test_id = new ilHiddenInputGUI('test_id');
		$test_id->setValue($unit_test->getTestId());
		$form->addItem($test_id);

		$testcase_name = new ilHiddenInputGUI('testcase_name');
		$testcase_name->setValue($unit_test->getTestCase());
		$form->addItem($testcase_name);

		$question_id = new ilHiddenInputGUI('question_id');
		$question_id->setValue($this->getQuestionId());
		$form->addItem($question_id);

		//Commands
		$form->addCommandButton("runTestcases", $this->getPlugin()->txt('ut_run_testcase') . ' ' . $unit_test->getTestCase());
		$form->addCommandButton("editTestcases", $this->getPlugin()->txt("ut_edit_testcase") . ' ' . $unit_test->getTestCase());
		$form->addCommandButton("doDeleteTestcase", $this->getPlugin()->txt("ut_delete_testcase") . ' ' . $unit_test->getTestCase());
		$form->setShowTopButtons(FALSE);

		return $form->getHTML();
	}

	/**
	 * Return the editing form for unit test
	 * @param $testcase
	 * @return HTML
	 */
	public function editTestcaseForm($testcase)
	{
		global $ilCtrl, $lng;

		//Must be a testcase to edit
		if (!$testcase) {
			return;
		} else {
			$unit_tests = assStackQuestionTest::_read($this->getQuestionId(), $testcase);
			$unit_test = $unit_tests[$testcase];
		}

		//Initialization
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormActionByClass('assStackQuestionGUI'));

		$testcase_name = new ilHiddenInputGUI('testcase_name');
		$testcase_name->setValue($testcase);
		$form->addItem($testcase_name);

		//Student inputs
		$inputs_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_student_response'), 'inputs');
		$form->addItem($inputs_title);
		foreach ($unit_test->getTestInputs() as $input) {
			$input_field = new ilTextInputGUI($input->getTestInputName(), $input->getTestInputName());
			$input_field->setValue($input->getTestInputValue());
			$form->addItem($input_field);
		}

		//Expected score
		$expected_score_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_expected_mark'), 'expected_score_title');
		$form->addItem($expected_score_title);
		foreach ($unit_test->getTestExpected() as $prt => $expected) {
			$expected_score = new ilTextInputGUI($this->getPlugin()->txt('ut_expected_score_for') . ' ' . $expected->getTestPRTName(), 'score_' . $expected->getTestPRTName());
			$expected_score->setValue($expected->getExpectedScore());
			$form->addItem($expected_score);
		}

		//Expected penalty
		$expected_penalty_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_expected_penalty'), 'expected_penalty_title');
		$form->addItem($expected_penalty_title);
		foreach ($unit_test->getTestExpected() as $prt => $expected) {
			$expected_penalty = new ilTextInputGUI($this->getPlugin()->txt('ut_expected_penalty_for') . ' ' . $expected->getTestPRTName(), 'penalty_' . $expected->getTestPRTName());
			$expected_penalty->setValue($expected->getExpectedPenalty());
			$form->addItem($expected_penalty);
		}

		//Expected answernote
		$expected_answernote_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_expected_answer_note'), 'expected_answernote_title');
		$form->addItem($expected_answernote_title);
		foreach ($unit_test->getTestExpected() as $prt => $expected) {
			$expected_answernote = new ilTextInputGUI($this->getPlugin()->txt('ut_expected_answernote_for') . ' ' . $expected->getTestPRTName(), 'answernote_' . $expected->getTestPRTName());
			$expected_answernote->setValue($expected->getExpectedAnswerNote());
			$form->addItem($expected_answernote);
		}

		//Commands
		$form->addCommandButton("doEditTestcase", $lng->txt('save'));
		$form->addCommandButton("showUnitTests", $lng->txt('cancel'));

		return $form->getHTML();
	}

	/**
	 * Returns the unit test creation form for the current question
	 * @param $question_id
	 * @return HTML
	 */
	public function createTestcaseForm($question_id)
	{
		global $ilCtrl, $lng;

		//Initialization
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormActionByClass('assStackQuestionGUI'));

		//Guess question structure in order to create needed fields
		$structure = assStackQuestionUtils::_getInputsAndPRTStructure($question_id);

		//Student inputs
		$inputs_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_student_response'), 'inputs_title');
		$form->addItem($inputs_title);
		foreach ($structure['input'] as $input_name => $input) {
			$input_field = new ilTextInputGUI($input_name, $input_name);
			$input_field->setValue('');
			$form->addItem($input_field);
		}

		//Expected score
		$expected_score_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_expected_mark'), 'expected_score_title');
		$form->addItem($expected_score_title);
		foreach ($structure['prt'] as $prt => $expected) {
			$expected_score = new ilTextInputGUI($this->getPlugin()->txt('ut_expected_score_for') . ' ' . $expected->getPRTName(), 'score' . '_' . $expected->getPRTName());
			$expected_score->setValue('');
			$form->addItem($expected_score);
		}

		//Expected penalty
		$expected_score_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_expected_penalty'), 'expected_penalty_title');
		$form->addItem($expected_score_title);
		foreach ($structure['prt'] as $prt => $expected) {
			$expected_score = new ilTextInputGUI($this->getPlugin()->txt('ut_expected_penalty_for') . ' ' . $expected->getPRTName(), 'penalty' . '_' . $expected->getPRTName());
			$expected_score->setValue('');
			$form->addItem($expected_score);
		}

		//Expected answernote
		$expected_score_title = new ilNonEditableValueGUI($this->getPlugin()->txt('ut_expected_answer_note'), 'expected_answernote_title');
		$form->addItem($expected_score_title);
		foreach ($structure['prt'] as $prt => $expected) {
			$expected_score = new ilTextInputGUI($this->getPlugin()->txt('ut_expected_answernote_for') . ' ' . $expected->getPRTName(), 'answernote' . '_' . $expected->getPRTName());
			$expected_score->setValue('');
			$form->addItem($expected_score);
		}

		//Commands
		$form->addCommandButton("doCreateTestcase", $lng->txt('create'));
		$form->addCommandButton("showUnitTests", $lng->txt('cancel'));

		return $form->getHTML();
	}

	/*
	 * UTILS
	 */

	/**
	 * Determine color
	 * @param boolean|string $test_passed
	 * @return string
	 */
	private function getTestcaseColor($test_passed)
	{
		if (!is_bool($test_passed)) {
			return '#CCCCCC';
		} elseif ($test_passed) {
			return '#B5EEAC';
		} else {
			return '#FFCCCC';
		}
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
	 * @param int $question_id
	 */
	public function setQuestionId($question_id)
	{
		$this->question_id = $question_id;
	}

	/**
	 * @return int
	 */
	public function getQuestionId()
	{
		return $this->question_id;
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

	/**
	 * @param mixed $tests
	 */
	public function setTests($tests)
	{
		$this->tests = $tests;
	}

	/**
	 * @return mixed
	 */
	public function getTests($selector = '')
	{
		if ($selector) {
			return $this->tests[$selector];
		} else {
			return $this->tests;
		}
	}

	/**
	 * @param mixed $unit_test_results
	 */
	public function setUnitTestResults($unit_test_results)
	{
		$this->unit_test_results = $unit_test_results;
	}

	/**
	 * @return mixed
	 */
	public function getUnitTestResults($selector = '')
	{
		if ($selector) {
			return $this->unit_test_results[$selector];
		} else {
			return $this->unit_test_results;
		}
	}


}