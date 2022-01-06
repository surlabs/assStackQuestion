<?php
/**
 * Copyright (c) 2022 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * STACK Question Import from MoodleXML
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id: 4.0$
 *
 */
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';
require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';

class assStackQuestionMoodleImport
{
	/**
	 * Plugin instance for language management
	 * @var ilassStackQuestionPlugin
	 */
	private ilassStackQuestionPlugin $plugin;

	/**
	 * The current question
	 * @var assStackQuestion
	 */
	private assStackQuestion $question;

	/**
	 * Question_id for the first question to import
	 * (When only one question, use this as Question_Id)
	 * @var int If first question this var is higher than 0.
	 */
	private int $first_question;

	/**
	 * @var array
	 */
	private array $error_log = array();

	/**
	 * @var string    allowed html tags, e.g. "<em><strong>..."
	 */
	private string $rte_tags = "";


	/**
	 * media objects created for an imported question
	 * This list will be cleared for every new question
	 * @var array    id => object
	 */
	private array $media_objects = array();


	/**
	 * Set all the parameters for this question, including the creation of
	 * the first assStackQuestion object.
	 * @param ilassStackQuestionPlugin $plugin
	 * @param int $first_question_id the question_id for the first question to import.
	 * @param assStackQuestion $question
	 */
	function __construct(ilassStackQuestionPlugin $plugin, int $first_question_id, assStackQuestion $question)
	{
		//Set Plugin and first question id.
		$this->setPlugin($plugin);
		$this->setFirstQuestion($first_question_id);

		//Creation of the first question.
		$this->getPlugin()->includeClass('class.assStackQuestion.php');
		$this->setQuestion($question);

		//Initialization and load of stack wrapper classes
		$this->getPlugin()->includeClass('utils/class.assStackQuestionInitialization.php');
	}

	/**
	 * ### MAIN METHOD OF THIS CLASS ###
	 * This method is called from assStackQuestion to import the questions from an MoodleXML file.
	 * @param string $xml_file the MoodleXML file
	 */
	public function import($xml_file)
	{
		//Step 1: Get data from XML.
		//LIBXML_NOCDATA Merge CDATA as Textnodes
		$xml = simplexml_load_file($xml_file, null, LIBXML_NOCDATA);

		//Step 2: Initialize question in ILIAS
		$number_of_questions_created = 0;

		foreach ($xml->question as $question) {

			//New list of media objects for each question
			$this->clearMediaObjects();

			//If import process has been successful, save question to DB.
			if ($this->importQuestions($question)) {

				$this->getQuestion()->setId(-1);
				$this->getQuestion()->createNewQuestion();
				$this->getQuestion()->saveToDb();
				$number_of_questions_created++;

			}
		}

		if (!empty($this->error_log)) {
			//Show Errors
		}
	}

	/**
	 * Initializes $this->getQuestion with the values from the XML object.
	 * @param SimpleXMLElement $question
	 * @return bool
	 */
	public function importQuestions(SimpleXMLElement $question): bool
	{
		//STEP 1: load standard question fields
		if (!isset($question->name->text) or $question->name->text == '') {
			$this->error_log[] = $this->getPlugin()->txt('error_import_no_title');
		}
		$question_title = (string)$question->name->text;

		if (!isset($question->questiontext->text) or $question->questiontext->text == '') {
			$this->error_log[] = $this->getPlugin()->txt('error_import_no_question_text') . ' in question: ' . $question_title;
		}
		$question_text = (string)$question->questiontext->text;

		if (!isset($question->defaultgrade) or $question->defaultgrade == '') {
			$this->error_log[] = $this->getPlugin()->txt('error_import_no_points') . ' in question: ' . $question_title;
		}
		$points = (string)$question->defaultgrade;

		//question text mapping for images
		if (isset($question->questiontext->file)) {
			$mapping = $this->getMediaObjectsFromXML($question->questiontext->file);
			$question_text = $this->replaceMediaObjectReferences($question_text, $mapping);
		}

		//set standard question fields as current.
		$this->getQuestion()->setTitle(ilUtil::secureString($question_title));
		$this->getQuestion()->setPoints(ilUtil::secureString($points));
		$this->getQuestion()->setQuestion(ilUtil::secureString($question_text));
		$this->getQuestion()->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());

		//STEP 2: load xqcas_options fields

		//question variables
		$this->getQuestion()->question_variables = ilUtil::secureString((string)$question->questionvariables);

		//specific feedback
		$specific_feedback = (string)$question->specificfeedback->text;
		if (isset($question->specificfeedback->file)) {
			$mapping = $this->getMediaObjectsFromXML($question->specificfeedback->file);
			$specific_feedback = $this->replaceMediaObjectReferences($specific_feedback, $mapping);
		}
		$this->getQuestion()->specific_feedback = ilUtil::secureString($specific_feedback);

		$this->getQuestion()->specific_feedback_format = 1;

		//question note
		if (isset($question->questionnote)) {
			$this->getQuestion()->question_note = ilUtil::secureString((string)$question->questionnote);
		}

		//prt correct feedback
		$prt_correct = (string)$question->prtcorrect->text;
		if (isset($question->prtcorrect->file)) {
			$mapping = $this->getMediaObjectsFromXML($question->prtcorrect->file);
			$prt_correct = $this->replaceMediaObjectReferences($prt_correct, $mapping);
		}
		$this->getQuestion()->prt_correct = ilUtil::secureString($prt_correct);

		$this->getQuestion()->prt_correct_format = 1;

		//prt partially correct
		$prt_partially_correct = (string)$question->prtpartiallycorrect->text;
		if (isset($question->prtpartiallycorrect->file)) {
			$mapping = $this->getMediaObjectsFromXML($question->prtpartiallycorrect->file);
			$prt_partially_correct = $this->replaceMediaObjectReferences($prt_partially_correct, $mapping);
		}
		$this->getQuestion()->prt_partially_correct = ilUtil::secureString($prt_partially_correct);

		$this->getQuestion()->prt_partially_correct_format = 1;

		//prt incorrect
		$prt_incorrect = (string)$question->prtincorrect->text;
		if (isset($question->prtincorrect->file)) {
			$mapping = $this->getMediaObjectsFromXML($question->prtincorrect->file);
			$prt_incorrect = $this->replaceMediaObjectReferences($prt_incorrect, $mapping);
		}
		$this->getQuestion()->prt_incorrect = ilUtil::secureString($prt_incorrect);
		$this->getQuestion()->prt_incorrect_format = 1;

		//variants selection seeds
		$this->getQuestion()->variants_selection_seed = ilUtil::secureString((string)$question->variantsselectionseed);

		//options
		$options = array();
		$options['simplify'] = ((int)$question->questionsimplify);
		$options['assumepos'] = ((int)$question->assumepositive);
		$options['multiplicationsign'] = ilUtil::secureString((string)$question->multiplicationsign);
		$options['sqrtsign'] = ((int)$question->sqrtsign);
		$options['complexno'] = ilUtil::secureString((string)$question->complexno);
		$options['inversetrig'] = ilUtil::secureString((string)$question->inversetrig);
		$options['matrixparens'] = ilUtil::secureString((string)$question->matrixparens);

		//load options
		try {
			$this->getQuestion()->options = new stack_options($options);
		} catch (stack_exception $e) {
			ilUtil::sendFailure($e, true);
		}

		//STEP 3: load xqcas_inputs fields

		$required_parameters = stack_input_factory::get_parameters_used();

		//load all inputs present in the XML
		foreach ($question->input as $input) {

			$input_name = ilUtil::secureString((string)$input->name);
			$input_type = ilUtil::secureString((string)$input->type);

			$all_parameters = array(
				'boxWidth' => ilUtil::secureString((string)$question->boxsize),
				'strictSyntax' => ilUtil::secureString((string)$question->strictsyntax),
				'insertStars' => ilUtil::secureString((string)$question->insertstars),
				'syntaxHint' => ilUtil::secureString((string)$question->syntaxhint),
				'syntaxAttribute' => ilUtil::secureString((string)$question->syntaxattribute),
				'forbidWords' => ilUtil::secureString((string)$question->forbidwords),
				'allowWords' => ilUtil::secureString((string)$question->allowwords),
				'forbidFloats' => ilUtil::secureString((string)$question->forbidfloat),
				'lowestTerms' => ilUtil::secureString((string)$question->requirelowestterms),
				'sameType' => ilUtil::secureString((string)$question->checkanswertype),
				'mustVerify' => ilUtil::secureString((string)$question->mustverify),
				'showValidation' => ilUtil::secureString((string)$question->showvalidation),
				'options' => ilUtil::secureString((string)$question->options),
			);

			$parameters = array();
			foreach ($required_parameters[$input_type] as $parameter_name) {
				if ($parameter_name == 'inputType') {
					continue;
				}
				$parameters[$parameter_name] = $all_parameters[$parameter_name];
			}

			//load inputs
			$this->getQuestion()->inputs[$input_name] = stack_input_factory::make($input_type, $input_name, ilUtil::secureString((string)$input->tans), $this->getQuestion()->options, $parameters);
		}

		//STEP 4:load PRTs and PRT nodes

		//Values
		$total_value = 0;

		foreach ($question->prt as $prt_data) {
			$total_value += (float)ilUtil::secureString((string)$prt_data->value);
		}

		if ($total_value < 0.0000001) {
			try {
				throw new stack_exception('There is an error authoring your question. ' .
					'The $totalvalue, the marks available for the question, must be positive in question ' .
					$this->getQuestion()->getTitle());
			} catch (stack_exception $e) {
				echo $e;
				exit;
			}
		}

		foreach ($question->prt as $prt) {

			$prt_name = ilUtil::secureString((string)$prt->name);
			$nodes = array();
			$is_first_node = true;

			foreach ($prt->node as $xml_node) {

				$node_name = ilUtil::secureString((string)$xml_node->name);
				$raw_sans = ilUtil::secureString((string)$xml_node->sans);
				$raw_tans = ilUtil::secureString((string)$xml_node->tans);

				$sans = stack_ast_container::make_from_teacher_source('PRSANS' . $node_name . ':' . $raw_sans, '', new stack_cas_security());
				$tans = stack_ast_container::make_from_teacher_source('PRTANS' . $node_name . ':' . $raw_tans, '', new stack_cas_security());

				//Penalties management, penalties are not an ILIAS Feature
				$false_penalty = ilUtil::secureString((string)$xml_node->falsepenalty);
				$true_penalty = ilUtil::secureString((string)$xml_node->truepenalty);

				try {
					//Create Node and add it to the
					$node = new stack_potentialresponse_node($sans, $tans, ilUtil::secureString((string)$xml_node->answertest), ilUtil::secureString((string)$xml_node->testoptions), (bool)$xml_node->testoptions, '', (int)$node_name, $raw_sans, $raw_tans);

					//manage images in false feedback
					$false_feedback = (string)$xml_node->falsefeedback->text;
					if (isset($xml_node->falsefeedback->file)) {
						$mapping = $this->getMediaObjectsFromXML($xml_node->falsefeedback->file);
						$false_feedback = $this->replaceMediaObjectReferences($false_feedback, $mapping);
					}

					//manage images in true feedback
					$true_feedback = (string)$xml_node->truefeedback->text;
					if (isset($xml_node->truefeedback->file)) {
						$mapping = $this->getMediaObjectsFromXML($xml_node->truefeedback->file);
						$true_feedback = $this->replaceMediaObjectReferences($true_feedback, $mapping);
					}

					$node->add_branch(0, ilUtil::secureString((string)$xml_node->falsescoremode), ilUtil::secureString((string)$xml_node->falsescore), $false_penalty, ilUtil::secureString((string)$xml_node->falsenextnode), $false_feedback, 1, ilUtil::secureString((string)$xml_node->falseanswernote));
					$node->add_branch(1, ilUtil::secureString((string)$xml_node->truescoremode), ilUtil::secureString((string)$xml_node->truescore), $true_penalty, ilUtil::secureString((string)$xml_node->truenextnode), $true_feedback, 1, ilUtil::secureString((string)$xml_node->trueanswernote));

					$nodes[$node_name] = $node;

					//set first node
					if ($is_first_node) {
						$first_node = $node_name;
						$is_first_node = false;
					}

				} catch (stack_exception $e) {
					echo $e;
					exit;
				}
			}

			if ((string)$prt->feedbackvariables->text) {
				try {
					$feedback_variables = new stack_cas_keyval(ilUtil::secureString((string)$prt->feedbackvariables->text));
					$feedback_variables = $feedback_variables->get_session();
				} catch (stack_exception $e) {
					echo $e;
					exit;
				}
			} else {
				$feedback_variables = null;
			}

			$prt_value = (float)$prt->value / $total_value;

			try {
				$this->getQuestion()->prts[$prt_name] = new stack_potentialresponse_tree($prt_name, '', (bool)$prt->autosimplify, $prt_value, $feedback_variables, $nodes, $first_node, 1);
			} catch (stack_exception $e) {
				echo $e;
				exit;
			}

			//TODO SEEDS; TESTS; EXTRA INFO
			return true;
		}
	}

	public function deletePredefinedQuestionData($question_id)
	{
		global $DIC;
		$db = $DIC->database();

		$query = 'DELETE FROM xqcas_options WHERE question_id = ' . $question_id;
		$db->manipulate($query);

		$query = 'DELETE FROM xqcas_inputs WHERE question_id = ' . $question_id;
		$db->manipulate($query);
	}


	private function checkQuestionType($data)
	{
		$has_name = array_key_exists('name', $data);
		$has_question_variables = array_key_exists('questionvariables', $data);
		$has_inputs = array_key_exists('input', $data);
		$has_prts = array_key_exists('prt', $data);

		if ($has_name and $has_question_variables and $has_inputs and $has_prts) {
			return TRUE;
		} else {
			ilUtil::sendInfo($this->cas_question->getPlugin()->txt('error_importing_question_malformed'));

			return FALSE;
		}
	}


	private function getTestsFromXML($data)
	{
		$this->getPlugin()->includeClass('model/ilias_object/test/class.assStackQuestionTest.php');
		$tests = array();

		foreach ($data as $test) {
			//Main attributes needed to create an TestOBJ
			$test_case = (int)$test['testcase'];
			$new_test = new assStackQuestionTest(-1, $this->getQuestion()->getId(), $test_case);

			//Creation of inputs
			$test_inputs = $this->getTestInputsFromXML($test['testinput'], $this->getQuestion()->getId(), $test_case);
			$new_test->setTestInputs($test_inputs);

			//Creation of expected results
			$test_expected = $this->getTestExpectedFromXML($test['expected'], $this->getQuestion()->getId(), $test_case);
			$new_test->setTestExpected($test_expected);

			$tests[] = $new_test;
		}

		//array of assStackQuestionTest
		return $tests;
	}

	private function getTestInputsFromXML($data, $question_id, $test_case)
	{
		$this->getPlugin()->includeClass('model/ilias_object/test/class.assStackQuestionTestInput.php');
		$test_inputs = array();

		foreach ($data as $input) {
			$new_test_input = new assStackQuestionTestInput(-1, $this->getQuestion()->getId(), $test_case);

			$new_test_input->setTestInputName($input['name']);
			$new_test_input->setTestInputValue($input['value']);

			$test_inputs[] = $new_test_input;
		}

		//array of assStackQuestionTestInput
		return $test_inputs;
	}

	private function getTestExpectedFromXML($data, $question_id, $test_case)
	{
		$this->getPlugin()->includeClass('model/ilias_object/test/class.assStackQuestionTestExpected.php');
		$test_expected = array();

		foreach ($data as $expected) {
			//Getting the PRT name
			$prt_name = strip_tags($expected['name']);
			$new_test_expected = new assStackQuestionTestExpected(-1, $this->getQuestion()->getId(), $test_case, $prt_name);

			$new_test_expected->setExpectedScore(strip_tags($expected['expectedscore']));
			$new_test_expected->setExpectedPenalty(strip_tags($expected['expectedpenalty']));
			$new_test_expected->setExpectedAnswerNote($expected['expectedanswernote']);

			$test_expected[] = $new_test_expected;
		}

		//array of assStackQuestionTestExpected
		return $test_expected;
	}

	private function getDeployedSeedsFromXML($data)
	{
		$this->getPlugin()->includeClass('model/ilias_object/class.assStackQuestionDeployedSeed.php');
		$deployed_seeds = array();

		foreach ($data as $deployed_seed_string) {
			$deployed_seed = new assStackQuestionDeployedSeed(-1, $this->getQuestion()->getId(), (int)$deployed_seed_string);
			$deployed_seeds[] = $deployed_seed;
		}

		//array of assStackQuestionDeployedSeed
		return $deployed_seeds;
	}

	private function getExtraInfoFromXML($data)
	{
		$this->getPlugin()->includeClass('model/ilias_object/class.assStackQuestionExtraInfo.php');
		$extra_info = new assStackQuestionExtraInfo(-1, $this->getQuestion()->getId());

		//General feedback property
		$mapping = $this->getMediaObjectsFromXML($data['generalfeedback'][0]['file']);
		$how_to_solve = assStackQuestionUtils::_casTextConverter($this->replaceMediaObjectReferences($data['generalfeedback'][0]['text'], $mapping), $this->getQuestion()->getTitle(), TRUE);
		$extra_info->setHowToSolve(ilUtil::secureString($how_to_solve, true, $this->getRTETags()));
		//Penalty property
		$penalty = $data['penalty'];
		$extra_info->setPenalty($penalty);
		//Hidden property
		$hidden = $data['hidden'];
		$extra_info->setHidden($hidden);

		//assStackQuestionExtraInfo
		return $extra_info;
	}

	/**
	 * Create media objects from array converted file elements
	 * @param array $data [['_attributes' => ['name' => string, 'path' => string], '_content' => string], ...]
	 * @return    array             filename => object_id
	 */
	private function getMediaObjectsFromXML($data = array())
	{
		$mapping = array();
		foreach ((array)$data as $file) {
			$name = $file['_attributes']['name'];
			$path = $file['_attributes']['path'];
			$src = $file['_content'];

			$temp = ilUtil::ilTempnam();
			file_put_contents($temp, base64_decode($src));
			$media_object = ilObjMediaObject::_saveTempFileAsMediaObject($name, $temp, false);
			@unlink($temp);

			$this->media_objects[$media_object->getId()] = $media_object;
			$mapping[$name] = $media_object->getId();
		}

		return $mapping;
	}

	/**
	 * Replace references to media objects in a text
	 * @param string    text from moodleXML with local references
	 * @param array    mapping of filenames to media object IDs
	 * @return    string    text with paths to media objects
	 */
	private function replaceMediaObjectReferences($text = "", $mapping = array())
	{
		foreach ($mapping as $name => $id) {
			$text = str_replace('src="@@PLUGINFILE@@/' . $name, 'src="' . ILIAS_HTTP_PATH . '/data/' . CLIENT_ID . '/mobs/mm_' . $id . "/" . $name . '"', $text);
		}

		return $text;
	}

	/**
	 * Clear the list of media objects
	 * This should be called for every new question import
	 */
	private function clearMediaObjects()
	{
		$this->media_objects = array();
	}

	/**
	 * Save the usages of media objects in a question
	 * @param integer $question_id
	 */
	private function saveMediaObjectUsages($question_id)
	{
		foreach ($this->media_objects as $id => $media_object) {
			ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $question_id);
		}
		$this->media_objects = array();
	}

	/**
	 * Purge the media objects colleted for a not imported question
	 */
	private function purgeMediaObjects()
	{
		foreach ($this->media_objects as $id => $media_object) {
			$media_object->delete();
		}
		$this->media_objects = array();
	}


	/**
	 * Check if the question has all data needed to work properly
	 * In this method is done the check for new syntax in CASText from STACK 4.0
	 * @return boolean if question has been properly created
	 */
	public function checkQuestion(assStackQuestion $question)
	{
		//Step 1: Check if there is one option object and at least one input, one prt with at least one node;
		if (!is_a($question->getOptions(), 'assStackQuestionOptions')) {
			return false;
		}
		if (is_array($question->getInputs())) {
			foreach ($question->getInputs() as $input) {
				if (!is_a($input, 'assStackQuestionInput')) {
					return false;
				}
			}
		} else {
			return false;
		}
		if (is_array($question->getPotentialResponsesTrees())) {
			foreach ($question->getPotentialResponsesTrees() as $prt) {
				if (!is_a($prt, 'assStackQuestionPRT')) {
					return false;
				} else {
					foreach ($prt->getPRTNodes() as $node) {
						if (!is_a($node, 'assStackQuestionPRTNode')) {
							return false;
						}
					}
				}
			}
		} else {
			return false;
		}

		//Step 2: Check options
		$options_are_ok = $question->getOptions()->checkOptions(TRUE);

		//Step 3: Check inputs
		foreach ($question->getInputs() as $input) {
			$inputs_are_ok = $input->checkInput(TRUE);
			if ($inputs_are_ok == FALSE) {
				break;
			}
		}

		//Step 4A: Check PRT
		if (is_array($question->getPotentialResponsesTrees())) {
			foreach ($question->getPotentialResponsesTrees() as $PRT) {
				$PRTs_are_ok = $PRT->checkPRT(TRUE);
				if ($PRTs_are_ok == FALSE) {
					break;
				} else {
					//Step 4B: Check Nodes
					if (is_array($PRT->getPRTNodes())) {
						foreach ($PRT->getPRTNodes() as $node) {
							$Nodes_are_ok = $node->checkPRTNode(TRUE);
							if ($Nodes_are_ok == FALSE) {
								break;
							}
						}
					}
					//Step 4C: Check if nodes make a PRT
				}
			}
		}

		//Step 5: Check tests
		if (!empty($question->getTests())) {
			foreach ($question->getTests() as $test) {
				if (!is_a($test, 'assStackQuestionTest')) {
					return false;
				} else {
					$tests_creation_is_ok = $test->checkTest(TRUE);
					//Step 5B: Check inputs
					foreach ($test->getTestInputs() as $input) {
						$test_inputs_are_ok = $input->checkTestInput(TRUE);
						if ($test_inputs_are_ok == FALSE) {
							break;
						}
					}
					//Step 5C: Check expected
					foreach ($test->getTestExpected() as $expected) {
						$test_expected_are_ok = $expected->checkTestExpected(TRUE);
						if ($test_expected_are_ok == FALSE) {
							break;
						}
					}
					if ($tests_creation_is_ok and $test_inputs_are_ok and $test_expected_are_ok) {
						$test_are_ok = TRUE;
					} else {
						$test_are_ok = FALSE;
					}
				}
			}
		} else {
			$test_are_ok = TRUE;
		}

		if ($options_are_ok and $inputs_are_ok and $PRTs_are_ok and $Nodes_are_ok and $test_are_ok) {
			return true;
		} else {
			return false;
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
	 * @param \assStackQuestion $question
	 */
	public function setQuestion($question)
	{
		$this->question = $question;
	}

	/**
	 * @return \assStackQuestion
	 */
	public function getQuestion()
	{
		return $this->question;
	}

	/**
	 * @param int $first_question
	 */
	public function setFirstQuestion($first_question)
	{
		$this->first_question = $first_question;
	}

	/**
	 * @return int
	 */
	public function getFirstQuestion()
	{
		return $this->first_question;
	}

	/**
	 * @return string    allowed html tags, e.g. "<em><strong>..."
	 */
	public function setRTETags($tags)
	{
		$this->rte_tags = $tags;
	}

	/**
	 * @return string    allowed html tags, e.g. "<em><strong>..."
	 */
	public function getRTETags()
	{
		return $this->rte_tags;
	}

	public function php72Format($raw_data)
	{
		$full_data = array();

		foreach ($raw_data as $question_data) {
			$data = array();
			//Check for not category
			if (is_array($question_data['category'])) {
				continue;
			}

			//Question Name
			if (isset($question_data['name'][0]['text'][0]["_content"])) {
				$data['name'][0]['text'] = $question_data['name'][0]['text'][0]["_content"];
			} elseif (isset($question_data['name'][0]['text'])) {
				$data['name'][0]['text'] = $question_data['name'][0]['text'];
			} else {
				echo "Unknown title format";
				exit;
			}

			//Question text
			$data['questiontext'][0]['text'] = $question_data['questiontext'][0]['text'];

			//General feedback
			$data['generalfeedback'][0]['text'] = $question_data['generalfeedback'][0]['text'];

			//default grade
			$data['defaultgrade'] = $question_data['defaultgrade'][0]["_content"];

			//penalty
			$data['penalty'] = $question_data['penalty'][0]["_content"];

			//hidden
			if (isset($question_data['hidden'][0]["_content"])) {
				$data['hidden'] = $question_data['hidden'][0]["_content"];
			} else {
				$data['hidden'] = "";
			}

			//stackversion
			if (isset($question_data['stackversion'][0]["_content"])) {
				$data['stackversion'][0]['text'] = $question_data['stackversion'][0]['text'];
			} else {
				$data['stackversion'][0]['text'] = "";
			}

			//questionvariables 2 versions to solve problems with question variables tarting with comments
			if (isset($question_data['questionvariables'][0]['text'][0]['_content'])) {
				$data['questionvariables'][0]['text'] = $question_data['questionvariables'][0]['text'][0]['_content'];
			} elseif (isset($question_data['questionvariables'][0]['text']) and is_string($question_data['questionvariables'][0]['text'])) {
				$data['questionvariables'][0]['text'] = $question_data['questionvariables'][0]['text'];
			} else {
				$data['questionvariables'][0]['text'] = "";
			}

			//specificfeedback:
			if (isset($question_data['specificfeedback'][0]['text'][0]['_content'])) {
				$data['specificfeedback'][0]['text'] = $question_data['specificfeedback'][0]['text'][0]['_content'];
			} elseif (isset($question_data['specificfeedback'][0]['text']) and is_string($question_data['specificfeedback'][0]['text'])) {
				$data['specificfeedback'][0]['text'] = $question_data['specificfeedback'][0]['text'];
			} else {
				$data['specificfeedback'][0]['text'] = "";
			}

			//questionnote
			if (isset($question_data['questionnote'][0]['text'][0]['_content'])) {
				$data['questionnote'][0]['text'] = $question_data['questionnote'][0]['text'][0]['_content'];
			} elseif (isset($question_data['questionnote'][0]['text']) and is_string($question_data['questionnote'][0]['text'])) {
				$data['questionnote'][0]['text'] = $question_data['questionnote'][0]['text'];
			} else {
				$data['questionnote'][0]['text'] = "";
			}

			//questionsimplify
			if (isset($question_data['questionsimplify'][0]["_content"])) {
				$data['questionsimplify'] = $question_data['questionsimplify'][0]["_content"];
			} else {
				$data['questionsimplify'] = "";
			}

			//assumepositive
			if (isset($question_data['assumepositive'][0]["_content"])) {
				$data['assumepositive'] = $question_data['assumepositive'][0]["_content"];
			} else {
				$data['assumepositive'] = "";
			}

			//assumereal
			if (isset($question_data['assumereal'][0]["_content"])) {
				$data['assumereal'] = $question_data['assumereal'][0]["_content"];
			} else {
				$data['assumereal'] = "";
			}

			//prtcorrect
			if (isset($question_data['prtcorrect'][0]["_content"])) {
				$data['prtcorrect'][0]['text'] = $question_data['prtcorrect'][0]['text'];
			} else {
				$data['prtcorrect'][0]['text'] = "";
			}

			//prtpartiallycorrect
			if (isset($question_data['prtpartiallycorrect'][0]["_content"])) {
				$data['prtpartiallycorrect'][0]['text'] = $question_data['prtpartiallycorrect'][0]['text'];
			} else {
				$data['prtpartiallycorrect'][0]['text'] = "";
			}

			//prtincorrect
			if (isset($question_data['prtincorrect'][0]["_content"])) {
				$data['prtincorrect'][0]['text'] = $question_data['prtincorrect'][0]['text'];
			} else {
				$data['prtincorrect'][0]['text'] = "";
			}

			//multiplicationsign
			if (isset($question_data['multiplicationsign'][0]["_content"])) {
				$data['multiplicationsign'] = $question_data['multiplicationsign'][0]["_content"];
			} else {
				$data['multiplicationsign'] = "";
			}

			//sqrtsign
			if (isset($question_data['sqrtsign'][0]["_content"])) {
				$data['sqrtsign'] = $question_data['sqrtsign'][0]["_content"];
			} else {
				$data['sqrtsign'] = "";
			}

			//complexno
			if (isset($question_data['complexno'][0]["_content"])) {
				$data['complexno'] = $question_data['complexno'][0]["_content"];
			} else {
				$data['complexno'] = "";
			}

			//inversetrig
			if (isset($question_data['inversetrig'][0]["_content"])) {
				$data['inversetrig'] = $question_data['inversetrig'][0]["_content"];
			} else {
				$data['inversetrig'] = "";
			}

			//matrixparens
			if (isset($question_data['matrixparens'][0]["_content"])) {
				$data['matrixparens'] = $question_data['matrixparens'][0]["_content"];
			} else {
				$data['matrixparens'] = "";
			}

			//variantsselectionseed
			if (isset($question_data['variantsselectionseed'])) {
				$data['variantsselectionseed'] = $question_data['variantsselectionseed'];
			} else {
				$data['variantsselectionseed'] = "";
			}

			//Inputs
			if (is_array($question_data['input'])) {
				foreach ($question_data['input'] as $input_raw) {
					$input_data = array();

					//name
					if (isset($input_raw['name'][0]["_content"])) {
						$input_data['name'] = $input_raw['name'][0]["_content"];
					} else {
						$input_data['name'] = "";
					}

					//type
					if (isset($input_raw['type'][0]["_content"])) {
						$input_data['type'] = $input_raw['type'][0]["_content"];
					} else {
						$input_data['type'] = "";
					}

					//tans
					if (isset($input_raw['tans'][0]["_content"])) {
						$input_data['tans'] = $input_raw['tans'][0]["_content"];
					} else {
						$input_data['tans'] = "";
					}

					//boxsize
					if (isset($input_raw['boxsize'][0]["_content"])) {
						$input_data['boxsize'] = $input_raw['boxsize'][0]["_content"];
					} else {
						$input_data['boxsize'] = "";
					}

					//strictsyntax
					if (isset($input_raw['strictsyntax'][0]["_content"])) {
						$input_data['strictsyntax'] = $input_raw['strictsyntax'][0]["_content"];
					} else {
						$input_data['strictsyntax'] = "";
					}

					//insertstars
					if (isset($input_raw['insertstars'][0]["_content"])) {
						$input_data['insertstars'] = $input_raw['insertstars'][0]["_content"];
					} else {
						$input_data['insertstars'] = "";
					}

					//syntaxhint
					if (isset($input_raw['syntaxhint'][0]["_content"])) {
						$input_data['syntaxhint'] = $input_raw['syntaxhint'][0]["_content"];
					} else {
						$input_data['syntaxhint'] = "";
					}

					//syntaxattribute
					if (isset($input_raw['syntaxattribute'][0]["_content"])) {
						$input_data['syntaxattribute'] = $input_raw['syntaxattribute'][0]["_content"];
					} else {
						$input_data['syntaxattribute'] = "";
					}

					//forbidwords
					if (isset($input_raw['forbidwords'][0]["_content"])) {
						$input_data['forbidwords'] = $input_raw['forbidwords'][0]["_content"];
					} else {
						$input_data['forbidwords'] = "";
					}

					//allowwords
					if (isset($input_raw['allowwords'][0]["_content"])) {
						$input_data['allowwords'] = $input_raw['allowwords'][0]["_content"];
					} else {
						$input_data['allowwords'] = "";
					}

					//forbidfloat
					if (isset($input_raw['forbidfloat'][0]["_content"])) {
						$input_data['forbidfloat'] = $input_raw['forbidfloat'][0]["_content"];
					} else {
						$input_data['forbidfloat'] = "";
					}

					//requirelowestterms
					if (isset($input_raw['requirelowestterms'][0]["_content"])) {
						$input_data['requirelowestterms'] = $input_raw['requirelowestterms'][0]["_content"];
					} else {
						$input_data['requirelowestterms'] = "";
					}

					//checkanswertype
					if (isset($input_raw['checkanswertype'][0]["_content"])) {
						$input_data['checkanswertype'] = $input_raw['checkanswertype'][0]["_content"];
					} else {
						$input_data['checkanswertype'] = "";
					}

					//mustverify
					if (isset($input_raw['mustverify'][0]["_content"])) {
						$input_data['mustverify'] = $input_raw['mustverify'][0]["_content"];
					} else {
						$input_data['mustverify'] = "";
					}

					//showvalidation
					if (isset($input_raw['showvalidation'][0]["_content"])) {
						$input_data['showvalidation'] = $input_raw['showvalidation'][0]["_content"];
					} else {
						$input_data['showvalidation'] = "";
					}

					//options
					if (isset($input_raw['options'][0]["_content"])) {
						$input_data['options'] = $input_raw['options'][0]["_content"];
					} else {
						$input_data['options'] = "";
					}

					//Add to question
					$data['input'][] = $input_data;
				}
			}

			//PRT
			if (is_array($question_data['prt'])) {
				foreach ($question_data['prt'] as $prt_raw) {
					$prt_data = array();

					//name
					if (isset($prt_raw['name'][0]["_content"])) {
						$prt_data['name'] = $prt_raw['name'][0]["_content"];
					} else {
						$prt_data['name'] = "";
					}

					//value
					if (isset($prt_raw['value'][0]["_content"])) {
						$prt_data['value'] = $prt_raw['value'][0]["_content"];
					} else {
						$prt_data['value'] = "";
					}

					//autosimplify
					if (isset($prt_raw['autosimplify'][0]["_content"])) {
						$prt_data['autosimplify'] = $prt_raw['autosimplify'][0]["_content"];
					} else {
						$prt_data['autosimplify'] = "";
					}

					//feedbackvariables
					if (isset($prt_raw['feedbackvariables'][0]['text'][0]['_content'])) {
						$prt_data['feedbackvariables'][0]['text'] = $prt_raw['feedbackvariables'][0]['text'][0]['_content'];
					} elseif (isset($prt_raw['feedbackvariables'][0]['text']) and is_string($prt_raw['feedbackvariables'][0]['text'])) {
						$prt_data['feedbackvariables'][0]['text'] = $prt_raw['feedbackvariables'][0]['text'];
					} else {
						$prt_data['feedbackvariables'][0]['text'] = "";
					}

					//Nodes
					if (is_array($prt_raw['node'])) {
						foreach ($prt_raw['node'] as $node_raw) {
							$node_data = array();

							//name
							if (isset($node_raw['name'][0]["_content"])) {
								$node_data['name'] = $node_raw['name'][0]["_content"];
							} else {
								$node_data['name'] = "";
							}

							//answertest
							if (isset($node_raw['answertest'][0]["_content"])) {
								$node_data['answertest'] = $node_raw['answertest'][0]["_content"];
							} else {
								$node_data['answertest'] = "";
							}

							//sans
							if (isset($node_raw['sans'][0]["_content"])) {
								$node_data['sans'] = $node_raw['sans'][0]["_content"];
							} else {
								$node_data['sans'] = "";
							}

							//tans
							if (isset($node_raw['tans'][0]["_content"])) {
								$node_data['tans'] = $node_raw['tans'][0]["_content"];
							} else {
								$node_data['tans'] = "";
							}

							//testoptions
							if (isset($node_raw['testoptions'][0]["_content"])) {
								$node_data['testoptions'] = $node_raw['testoptions'][0]["_content"];
							} else {
								$node_data['testoptions'] = "";
							}

							//quiet
							if (isset($node_raw['quiet'][0]["_content"])) {
								$node_data['quiet'] = $node_raw['quiet'][0]["_content"];
							} else {
								$node_data['quiet'] = "";
							}

							//truescoremode
							if (isset($node_raw['truescoremode'][0]["_content"])) {
								$node_data['truescoremode'] = $node_raw['truescoremode'][0]["_content"];
							} else {
								$node_data['truescoremode'] = "";
							}

							//truescore
							if (isset($node_raw['truescore'][0]["_content"])) {
								$node_data['truescore'] = $node_raw['truescore'][0]["_content"];
							} else {
								$node_data['truescore'] = "";
							}

							//truepenalty
							if (isset($node_raw['truepenalty'][0]["_content"])) {
								$node_data['truepenalty'] = $node_raw['truepenalty'][0]["_content"];
							} else {
								$node_data['truepenalty'] = "";
							}

							//truenextnode
							if (isset($node_raw['truenextnode'][0]["_content"])) {
								$node_data['truenextnode'] = $node_raw['truenextnode'][0]["_content"];
							} else {
								$node_data['truenextnode'] = "";
							}

							//trueanswernote
							if (isset($node_raw['trueanswernote'][0]["_content"])) {
								$node_data['trueanswernote'] = $node_raw['trueanswernote'][0]["_content"];
							} else {
								$node_data['trueanswernote'] = "";
							}

							//truefeedback
							if (isset($node_raw['truefeedback'][0]["text"][0]["_content"])) {
								$node_data['truefeedback'][0]["text"] = $node_raw['truefeedback'][0]["text"][0]["_content"];
							} else {
								$node_data['truefeedback'][0]["text"] = "";
							}

							//falsescoremode
							if (isset($node_raw['falsescoremode'][0]["_content"])) {
								$node_data['falsescoremode'] = $node_raw['falsescoremode'][0]["_content"];
							} else {
								$node_data['falsescoremode'] = "";
							}

							//falsescore
							if (isset($node_raw['falsescore'][0]["_content"])) {
								$node_data['falsescore'] = $node_raw['falsescore'][0]["_content"];
							} else {
								$node_data['falsescore'] = "";
							}

							//falsepenalty
							if (isset($node_raw['falsepenalty'][0]["_content"])) {
								$node_data['falsepenalty'] = $node_raw['falsepenalty'][0]["_content"];
							} else {
								$node_data['falsepenalty'] = "";
							}

							//falsenextnode
							if (isset($node_raw['falsenextnode'][0]["_content"])) {
								$node_data['falsenextnode'] = $node_raw['falsenextnode'][0]["_content"];
							} else {
								$node_data['falsenextnode'] = "";
							}

							//falseanswernote
							if (isset($node_raw['falseanswernote'][0]["_content"])) {
								$node_data['falseanswernote'] = $node_raw['falseanswernote'][0]["_content"];
							} else {
								$node_data['falseanswernote'] = "";
							}

							//falsefeedback
							if (isset($node_raw['falsefeedback'][0]["text"][0]["_content"])) {
								$node_data['falsefeedback'][0]["text"] = $node_raw['falsefeedback'][0]["text"][0]["_content"];
							} else {
								$node_data['falsefeedback'][0]["text"] = "";
							}

							//Add to prt
							$prt_data['node'][] = $node_data;
						}
					}

					//Add to question
					$data['prt'][] = $prt_data;

					//qtest
					if (is_array($question_data['qtest'])) {
						foreach ($question_data['qtest'] as $qtest_raw) {
							$qtest_data = array();

							//testcase
							if (isset($qtest_raw['testcase'][0]["_content"])) {
								$qtest_data['testcase'] = $qtest_raw['testcase'][0]["_content"];
							} else {
								$qtest_data['testcase'] = "";
							}

							//testinput
							if (isset($qtest_raw['testinput'][0]['name'][0]["_content"]) and isset($qtest_raw['testinput'][0]['value'][0]["_content"])) {
								$qtest_data['testinput'][0]['name'] = $qtest_raw['testinput'][0]['name'][0]["_content"];
								$qtest_data['testinput'][0]['value'] = $qtest_raw['testinput'][0]['value'][0]["_content"];
							} else {
								$qtest_data['testinput'][0]['name'] = "";
								$qtest_data['testinput'][0]['value'] = "";
							}

							//expected
							if (isset($qtest_raw['expected'][0]['name'][0]["_content"]) and isset($qtest_raw['expected'][0]['expectedscore'][0]["_content"]) and isset($qtest_raw['expected'][0]['expectedanswernote'][0]["_content"])) {
								$qtest_data['expected'][0]['name'] = $qtest_raw['expected'][0]['name'][0]["_content"];
								$qtest_data['expected'][0]['expectedscore'] = $qtest_raw['expected'][0]['expectedscore'][0]["_content"];
								$qtest_data['expected'][0]['expectedpenalty'] = $qtest_raw['expected'][0]['expectedpenalty'][0]["_content"];
								$qtest_data['expected'][0]['expectedanswernote'] = $qtest_raw['expected'][0]['expectedanswernote'][0]["_content"];

							} else {
								$qtest_data['expected'][0]['name'] = "";
								$qtest_data['expected'][0]['expectedscore'] = "";
								$qtest_data['expected'][0]['expectedpenalty'] = "";
								$qtest_data['expected'][0]['expectedanswernote'] = "";
							}


							//Add to question
							$data['qtest'][] = $qtest_data;
						}
					}

				}
			}

			//Add to full data
			$full_data['question'][] = $data;

		}

		return $full_data;
	}

}
