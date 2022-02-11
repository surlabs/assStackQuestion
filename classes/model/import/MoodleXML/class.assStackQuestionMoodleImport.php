<?php
/**
 * Copyright (c) 2022 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * STACK Question Import from MoodleXML
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id: 7.0$
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

	/* MAIN METHODS BEGIN */

	/**
	 * ### MAIN METHOD OF THIS CLASS ###
	 * This method is called from assStackQuestion to import the questions from an MoodleXML file.
	 * @param $xml_file
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

			//Set current question Id to -1 if we have created already one question, to ensure creation of the others
			if ($number_of_questions_created > 0) {
				$this->getQuestion()->setId(-1);
			}

			//Delete predefined inputs and prts
			$this->getQuestion()->inputs = array();
			$this->getQuestion()->prts = array();

			//If import process has been successful, save question to DB.
			if ($this->loadFromMoodleXML($question)) {

				//Save standard question data
				$this->getQuestion()->saveQuestionDataToDb();
				$this->getPlugin()->includeClass('class.assStackQuestionDB.php');
				try {
					//Save STACK Parameters forcing insert.
					if (assStackQuestionDB::_saveStackQuestion($this->getQuestion(), 'import')) {
						$this->saveMediaObjectUsages($this->getQuestion()->getId());
						$number_of_questions_created++;
					}
				} catch (stack_exception $e) {
					$this->error_log[] = 'question was not saved: ' . $this->getQuestion()->getTitle();
				}
			} else {
				//Do not allow not well created questions
				//Send Error Message
				$error_message = '';
				foreach ($this->error_log as $error) {
					$error_message .= $error . '</br>';
				}
				ilUtil::sendFailure('fau Error message for malformed questions: ' . $this->getQuestion()->getTitle() . ' ' . $error_message, true);
				//Purge media objects as we didn't import the question
				$this->purgeMediaObjects();
				//Delete Question
				$this->getQuestion()->delete($this->getQuestion()->getId());
			}
		}
	}

	/**
	 * Initializes $this->getQuestion with the values from the XML object.
	 * @param SimpleXMLElement $question
	 * @return bool
	 */
	public function loadFromMoodleXML(SimpleXMLElement $question): bool
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

		//Save current values, to set the Id properly.
		$this->getQuestion()->saveQuestionDataToDb();

		//STEP 2: load xqcas_options fields

		//question variables
		if (isset($question->questionvariables->text)) {
			$this->getQuestion()->question_variables = ilUtil::secureString((string)$question->questionvariables->text);
		}

		//specific feedback
		if (isset($question->specificfeedback->text)) {
			$specific_feedback = (string)$question->specificfeedback->text;

			if (isset($question->specificfeedback->file)) {
				$mapping = $this->getMediaObjectsFromXML($question->specificfeedback->file);
				$specific_feedback = $this->replaceMediaObjectReferences($specific_feedback, $mapping);
			}
			$this->getQuestion()->specific_feedback = ilUtil::secureString($specific_feedback);

			$this->getQuestion()->specific_feedback_format = 1;
		}

		//question note
		if (isset($question->questionnote->text)) {
			$this->getQuestion()->question_note = ilUtil::secureString((string)$question->questionnote->text);
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
		$options['assumereal'] = ((int)$question->assumereal);
		$options['multiplicationsign'] = ilUtil::secureString((string)$question->multiplicationsign);
		$options['sqrtsign'] = ((int)$question->sqrtsign);
		$options['complexno'] = ilUtil::secureString((string)$question->complexno);
		$options['inversetrig'] = ilUtil::secureString((string)$question->inversetrig);
		$options['matrixparens'] = ilUtil::secureString((string)$question->matrixparens);
		$options['logicsymbol'] = ilUtil::secureString((string)$question->logicsymbol);

		//load options
		try {
			$this->getQuestion()->options = new stack_options($options);
			//set stack version
			if (isset($question->stackversion->text)) {
				$this->getQuestion()->stack_version = (string)ilUtil::secureString((string)$question->stackversion->text);
			}
		} catch (stack_exception $e) {
			$this->error_log[] = $question_title . ': options not created';
		}

		//STEP 3: load xqcas_inputs fields

		$required_parameters = stack_input_factory::get_parameters_used();

		//load all inputs present in the XML
		foreach ($question->input as $input) {

			$input_name = ilUtil::secureString((string)$input->name);
			$input_type = ilUtil::secureString((string)$input->type);

			$all_parameters = array(
				'boxWidth' => ilUtil::secureString((string)$input->boxsize),
				'strictSyntax' => ilUtil::secureString((string)$input->strictsyntax),
				'insertStars' => ilUtil::secureString((string)$input->insertstars),
				'syntaxHint' => ilUtil::secureString((string)$input->syntaxhint),
				'syntaxAttribute' => ilUtil::secureString((string)$input->syntaxattribute),
				'forbidWords' => ilUtil::secureString((string)$input->forbidwords),
				'allowWords' => ilUtil::secureString((string)$input->allowwords),
				'forbidFloats' => ilUtil::secureString((string)$input->forbidfloat),
				'lowestTerms' => ilUtil::secureString((string)$input->requirelowestterms),
				'sameType' => ilUtil::secureString((string)$input->checkanswertype),
				'mustVerify' => ilUtil::secureString((string)$input->mustverify),
				'showValidation' => ilUtil::secureString((string)$input->showvalidation),
				'options' => ilUtil::secureString((string)$input->options),
			);

			$parameters = array();
			foreach ($required_parameters[$input_type] as $parameter_name) {
				if ($parameter_name == 'inputType') {
					continue;
				}
				$parameters[$parameter_name] = $all_parameters[$parameter_name];
			}

			//load inputs
			try {
				$this->getQuestion()->inputs[$input_name] = stack_input_factory::make($input_type, $input_name, ilUtil::secureString((string)$input->tans), $this->getQuestion()->options, $parameters);
			} catch (stack_exception $e) {
				$this->error_log[] = $this->getQuestion()->getTitle() . ': ' . $e;
			}
		}

		//STEP 4:load PRTs and PRT nodes

		//Values
		$total_value = 0;

		foreach ($question->prt as $prt_data) {
			$total_value += (float)ilUtil::secureString((string)$prt_data->value);
		}

		if ($total_value < 0.0000001) {
			$total_value = 1.0;
		}

		foreach ($question->prt as $prt) {
			$first_node = 1;

			$prt_name = ilUtil::secureString((string)$prt->name);
			$nodes = array();
			$is_first_node = true;
			$invalid_node = false;

			//Check for non "0" nodes
			foreach ($prt->node as $xml_node) {
				if ($xml_node->name == '0') {
					$invalid_node = true;
				}
			}

			foreach ($prt->node as $xml_node) {
				//Check for non "0" nodes
				if ($invalid_node) {
					$new_node_name = ((int)$xml_node->name) + 1;
					$node_name = ilUtil::secureString((string)$new_node_name);
				} else {
					$node_name = ilUtil::secureString((string)$xml_node->name);
				}

				$raw_sans = ilUtil::secureString((string)$xml_node->sans);
				$raw_tans = ilUtil::secureString((string)$xml_node->tans);

				$sans = stack_ast_container::make_from_teacher_source('PRSANS' . $node_name . ':' . $raw_sans, '', new stack_cas_security());
				$tans = stack_ast_container::make_from_teacher_source('PRTANS' . $node_name . ':' . $raw_tans, '', new stack_cas_security());

				//Penalties management, penalties are not an ILIAS Feature
				$false_penalty = ilUtil::secureString((string)$xml_node->falsepenalty);
				$true_penalty = ilUtil::secureString((string)$xml_node->truepenalty);

				try {
					//Create Node and add it to the
					$node = new stack_potentialresponse_node($sans, $tans, ilUtil::secureString((string)$xml_node->answertest), ilUtil::secureString((string)$xml_node->testoptions), (bool)$xml_node->quiet, '', (int)$node_name, $raw_sans, $raw_tans);

					//manage images in true feedback
					if (isset($xml_node->falsefeedback->text)) {
						$false_feedback = (string)$xml_node->falsefeedback->text;

						if (isset($xml_node->falsefeedback->file)) {
							$mapping = $this->getMediaObjectsFromXML($xml_node->falsefeedback->file);
							$false_feedback = $this->replaceMediaObjectReferences($false_feedback, $mapping);
						}
					} else {
						$false_feedback = '';
					}

					//manage images in true feedback
					if (isset($xml_node->truefeedback->text)) {
						$true_feedback = (string)$xml_node->truefeedback->text;

						if (isset($xml_node->truefeedback->file)) {
							$mapping = $this->getMediaObjectsFromXML($xml_node->truefeedback->file);
							$true_feedback = $this->replaceMediaObjectReferences($true_feedback, $mapping);
						}
					} else {
						$true_feedback = '';
					}

					//Check for non "0" next nodes
					$true_next_node = $xml_node->truenextnode;
					$false_next_node = $xml_node->falsenextnode;

					//If certain nodes point node 0 as next node (not usual)
					//The next node will now be -1, so, end of the prt.
					//If we are already in node 1, we cannot point ourselves
					if ($true_next_node == '-1') {
						$true_next_node = -1;
					} else {
						$true_next_node = $true_next_node + 1;
					}

					if ($false_next_node == '-1') {
						$false_next_node = -1;
					} else {
						$false_next_node = $false_next_node + 1;
					}

					//Check for non "0" answer notes
					if ($invalid_node) {
						$true_answer_note = $prt_name . '-' . $node_name . '-T';
						$false_answer_note = $prt_name . '-' . $node_name . '-F';
					} else {
						$true_answer_note = $xml_node->trueanswernote;
						$false_answer_note = $xml_node->falseanswernote;
					}

					$node->add_branch(0, ilUtil::secureString((string)$xml_node->falsescoremode), ilUtil::secureString((string)$xml_node->falsescore), $false_penalty, ilUtil::secureString((string)$false_next_node), ilUtil::secureString($false_feedback), 1, ilUtil::secureString((string)$false_answer_note));
					$node->add_branch(1, ilUtil::secureString((string)$xml_node->truescoremode), ilUtil::secureString((string)$xml_node->truescore), $true_penalty, ilUtil::secureString((string)$true_next_node), ilUtil::secureString($true_feedback), 1, ilUtil::secureString((string)$true_answer_note));

					$nodes[$node_name] = $node;

					//set first node
					if ($is_first_node) {
						$first_node = $node_name;
						$is_first_node = false;
					}

				} catch (stack_exception $e) {
					$this->error_log[] = $this->getQuestion()->getTitle() . ': ' . $e;
				}
			}

			$feedback_variables = null;
			if ((string)$prt->feedbackvariables->text) {
				try {
					$feedback_variables = new stack_cas_keyval(ilUtil::secureString((string)$prt->feedbackvariables->text));
					$feedback_variables = $feedback_variables->get_session();
				} catch (stack_exception $e) {
					$this->error_log[] = $this->getQuestion()->getTitle() . ': ' . $e;
				}
			}

			$prt_value = (float)$prt->value / $total_value;

			try {
				$this->getQuestion()->prts[$prt_name] = new stack_potentialresponse_tree($prt_name, '', (bool)$prt->autosimplify, $prt_value, $feedback_variables, $nodes, (string)$first_node, 1);
			} catch (stack_exception $e) {
				$this->error_log[] = $this->getQuestion()->getTitle() . ': ' . $e;
			}
		}

		//seeds
		$seeds = array();
		if (isset($question->deployedseed)) {
			foreach ($question->deployedseed as $seed) {
				$seeds[] = (int)ilUtil::secureString((string)$seed);
			}
		}
		$this->getQuestion()->deployed_seeds = $seeds;

		//Extra Information

		//General feedback / How to Solve
		if (isset($question->generalfeedback->text)) {
			$general_feedback = (string)$question->generalfeedback->text;

			if (isset($question->generalfeedback->file)) {
				$mapping = $this->getMediaObjectsFromXML($question->generalfeedback->file);
				$general_feedback = $this->replaceMediaObjectReferences($general_feedback, $mapping);
			}
			$this->getQuestion()->general_feedback = ilUtil::secureString($general_feedback);
		}

		//Penalty
		if (isset($question->penalty) and $question->penalty != '') {
			$this->getQuestion()->setPenalty((float)$question->penalty);
		}

		//Hidden
		$this->getQuestion()->setHidden(0);

		//Unit Tests
		$unit_tests = array();
		if (isset($question->qtest)) {
			foreach ($question->qtest as $testcase) {

				$testcase_name = ilUtil::secureString((string)$testcase->testcase);
				$unit_tests['test_cases'][$testcase_name] = array();

				foreach ($testcase->testinput as $testcase_input) {

					$input_name = ilUtil::secureString((string)$testcase_input->name);
					$input_value = ilUtil::secureString((string)$testcase_input->value);

					$unit_tests['test_cases'][$testcase_name]['inputs'][$input_name]['value'] = $input_value;

				}

				foreach ($testcase->expected as $testcase_expected) {

					$prt_name = ilUtil::secureString((string)$testcase_expected->name);
					$expected_score = ilUtil::secureString((string)$testcase_expected->expectedscore);
					$expected_penalty = ilUtil::secureString((string)$testcase_expected->expectedpenalty);
					$expected_answer_note = ilUtil::secureString((string)$testcase_expected->expectedanswernote);

					$unit_tests['test_cases'][$testcase_name]['expected'][$prt_name]['score'] = $expected_score;
					$unit_tests['test_cases'][$testcase_name]['expected'][$prt_name]['penalty'] = $expected_penalty;
					$unit_tests['test_cases'][$testcase_name]['expected'][$prt_name]['answer_note'] = $expected_answer_note;

				}
			}
		}

		$this->getQuestion()->setUnitTests($unit_tests);

		//Return status
		if (empty($this->error_log)) {
			return true;
		} else {
			return false;
		}
	}

	/* MAIN METHODS END */

	/* HELPER METHODS BEGIN */

	/**
	 * Create media objects from array converted file elements
	 * @param SimpleXMLElement $data [['_attributes' => ['name' => string, 'path' => string], '_content' => string], ...]
	 * @return    array             filename => object_id
	 */
	private function getMediaObjectsFromXML(SimpleXMLElement $data): array
	{
		$mapping = array();
		foreach ($data as $file) {
			$name = $file['_attributes']['name'];
			//$path = $file['_attributes']['path'];
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
	private function replaceMediaObjectReferences($text = "", $mapping = array()): string
	{
		foreach ($mapping as $name => $id) {
			$text = str_replace('src="@@PLUGINFILE@@/' . $name, 'src="' . ilUtil::_getHttpPath() . '/data/' . CLIENT_ID . '/mobs/mm_' . $id . "/" . $name . '"', $text);
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
	private function saveMediaObjectUsages(int $question_id)
	{
		foreach ($this->media_objects as $media_object) {
			ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $question_id);
		}
		$this->media_objects = array();
	}

	/**
	 * Purge the media objects collected for a not imported question
	 */
	private function purgeMediaObjects()
	{
		foreach ($this->media_objects as $media_object) {
			$media_object->delete();
		}
		$this->media_objects = array();
	}

	/* HELPER METHODS END */

	/* GETTERS AND SETTERS BEGIN */

	/**
	 * @param ilassStackQuestionPlugin $plugin
	 */
	public function setPlugin(ilassStackQuestionPlugin $plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * @return ilassStackQuestionPlugin
	 */
	public function getPlugin(): ilassStackQuestionPlugin
	{
		return $this->plugin;
	}

	/**
	 * @param assStackQuestion $question
	 */
	public function setQuestion(assStackQuestion $question)
	{
		$this->question = $question;
	}

	/**
	 * @return assStackQuestion
	 */
	public function getQuestion(): assStackQuestion
	{
		return $this->question;
	}

	/**
	 * @param int $first_question
	 */
	public function setFirstQuestion(int $first_question)
	{
		$this->first_question = $first_question;
	}

	/**
	 * @return int
	 */
	public function getFirstQuestion(): int
	{
		return $this->first_question;
	}

	/**
	 * @param $tags
	 */
	public function setRTETags($tags)
	{
		$this->rte_tags = $tags;
	}

	/**
	 * @return string    allowed html tags, e.g. "<em><strong>..."
	 */
	public function getRTETags(): string
	{
		return $this->rte_tags;
	}

	/* GETTERS AND SETTERS END */

}
