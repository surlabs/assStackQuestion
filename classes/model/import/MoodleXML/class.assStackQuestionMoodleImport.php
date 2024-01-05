<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

/**
 * STACK Question Import from MoodleXML
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 *
 */
//require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';
//require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';

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

            $type = (string)$question->attributes()['type'];

            if ($type == 'stack') {
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
		//If we do secure strings, html is lost
		$this->getQuestion()->setQuestion($question_text);
		$this->getQuestion()->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());

		//Save current values, to set the Id properly.
		$this->getQuestion()->saveQuestionDataToDb();

		//STEP 2: load xqcas_options fields

		//question variables
		if (isset($question->questionvariables->text)) {
			$this->getQuestion()->question_variables = assStackQuestionUtils::_debugText((string)$question->questionvariables->text);
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
        $totalvalue = 0;
        $allformative = true;

        foreach ($question->prt as $prt_data) {
            if ($prt_data->feedbackstyle > 0) {
                $totalvalue += $prt_data->value;
                $allformative = false;
            }
        }

        if ($question->prt && !$allformative && $totalvalue < 0.0000001) {
            throw new stack_exception('There is an error on import your question. ' .
                'The $totalvalue, the marks available for the question, must be positive in question ' .
                $question->name->text);
        }

        foreach ($question->prt as $prt_data) {
            $temp_prt_data = new stdClass();

            $temp_prt_data->name = ilUtil::secureString((string) $prt_data->name);
            $temp_prt_data->value = (float) $prt_data->value;
            $temp_prt_data->autosimplify = (int) $prt_data->autosimplify;
            $temp_prt_data->feedbackvariables = ilUtil::secureString((string) $prt_data->feedbackvariables);
            $temp_prt_data->nodes = array();

            foreach ($prt_data->node as $node_data) {
                $node = new stdClass();

                $node->nodename = ilUtil::secureString((string) $node_data->name);
                $node->answertest = ilUtil::secureString((string) $node_data->answertest);
                $node->sans = ilUtil::secureString((string) $node_data->sans);
                $node->tans = ilUtil::secureString((string) $node_data->tans);
                $node->testoptions = ilUtil::secureString((string) $node_data->testoptions);
                $node->quiet = ilUtil::secureString((int) $node_data->quiet);
                $node->truescoremode = ilUtil::secureString((string) $node_data->truescoremode);
                $node->truescore = ilUtil::secureString((string) $node_data->truescore);
                $node->truepenalty = ilUtil::secureString((int) $node_data->truepenalty);
                $node->truenextnode = ilUtil::secureString((string) $node_data->truenextnode);
                $node->trueanswernote = ilUtil::secureString((string) $node_data->trueanswernote);
                $node->truefeedback = ilUtil::secureString((string) $node_data->truefeedback->text);
                $node->falsescoremode = ilUtil::secureString((string) $node_data->falsescoremode);
                $node->falsescore = ilUtil::secureString((string) $node_data->falsescore);
                $node->falsepenalty = ilUtil::secureString((int) $node_data->falsepenalty);
                $node->falsenextnode = ilUtil::secureString((string) $node_data->falsenextnode);
                $node->falseanswernote = ilUtil::secureString((string) $node_data->falseanswernote);
                $node->falsefeedback = ilUtil::secureString((string) $node_data->falsefeedback->text);

                $temp_prt_data->nodes[$node->nodename] = $node;
            }

            $temp_prt_data->firstnodename = ilUtil::secureString((string) array_keys($temp_prt_data->nodes)[0]);

            $prt_data = $temp_prt_data;

            $prtvalue = 0;
            if (!$allformative) {
                $prtvalue = $prt_data->value / $totalvalue;
            }


            $this->getQuestion()->prts[(string) $prt_data->name] = new stack_potentialresponse_tree_lite($prt_data, $prtvalue);
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
			$media_object = ilObjMediaObject::_saveTempFileAsMediaObject($name, $temp, true);
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
