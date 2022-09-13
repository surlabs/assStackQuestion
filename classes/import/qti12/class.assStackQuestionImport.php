<?php

/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';

/**
 * STACK Question IMPORT OF QUESTIONS from an ILIAS file
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version    $Id: 1.8$$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionImport extends assQuestionImport
{
	/**
	 * Receives parameters from a QTI parser and creates a valid ILIAS question object
	 *
	 * @param object $item The QTI item object
	 * @param integer $questionpool_id The id of the parent questionpool
	 * @param integer $tst_id The id of the parent test if the question is part of a test
	 * @param object $tst_object A reference to the parent test object
	 * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	 * @param array $import_mapping An array containing references to included ILIAS objects
	 * @access public
	 */
	function fromXML2(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		global $ilUser;

		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);
		$presentation = $item->getPresentation();
		$duration = $item->getDuration();
		$shuffle = 0;
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
		$answers = array();

		//Obtain question general datae
		$this->addGeneralMetadata($item);
		$this->object->setTitle($item->getTitle());
		$this->object->setNrOfTries($item->getMaxattempts());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
		$this->object->setObjId($questionpool_id);
		$this->object->setPoints((float)$item->getMetadataEntry("POINTS"));
		$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);

		$this->object->saveQuestionDataToDb();

		//OPTIONS
		/* @var assStackQuestionOptions $options */
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionOptions.php");
		$options = unserialize(base64_decode($item->getMetadataEntry('options')));
		$options->setSpecificFeedback($this->processNonAbstractedImageReferences($options->getSpecificFeedback(), $item->getIliasSourceNic()));
		$options->setPRTCorrect($this->processNonAbstractedImageReferences($options->getPRTCorrect(), $item->getIliasSourceNic()));
		$options->setPRTIncorrect($this->processNonAbstractedImageReferences($options->getPRTIncorrect(), $item->getIliasSourceNic()));
		$options->setPRTPartiallyCorrect($this->processNonAbstractedImageReferences($options->getPRTPartiallyCorrect(), $item->getIliasSourceNic()));
		$options->setQuestionNote($this->processNonAbstractedImageReferences($options->getQuestionNote(), $item->getIliasSourceNic()));
		$this->object->setOptions($options);

		//Inputs
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionInput.php");
		$this->object->setInputs(unserialize(base64_decode($item->getMetadataEntry('inputs'))));

		//PRTs
		/* @var assStackQuestionPRT $prt */
		/* @var assStackQuestionPRTNode $node */
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionPRT.php");
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionPRTNode.php");
		$prts = unserialize(base64_decode($item->getMetadataEntry('prts')));
		foreach ($prts as $prt_name => $prt) {
			foreach ($prt->getPRTNodes() as $node_name => $node) {
				$node->setFalseFeedback($this->processNonAbstractedImageReferences($node->getFalseFeedback(), $item->getIliasSourceNic()));
				$node->setTrueFeedback($this->processNonAbstractedImageReferences($node->getTrueFeedback(), $item->getIliasSourceNic()));
			}
		}
		$this->object->setPotentialResponsesTrees($prts);

		//SEEDS
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionDeployedSeed.php");
		$this->object->setDeployedSeeds(unserialize(base64_decode($item->getMetadataEntry('seeds'))));

		//TESTS
		$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTest.php");
		$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTestInput.php");
		$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTestExpected.php");
		$this->object->setTests(unserialize(base64_decode($item->getMetadataEntry('tests'))));

		//EXTRA INFO
		/* @var assStackQuestionExtraInfo $extra_info */
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionExtraInfo.php");
		$extra_info = unserialize(base64_decode($item->getMetadataEntry('extra_info')));
		$extra_info->setHowToSolve($this->processNonAbstractedImageReferences($extra_info->getHowToSolve(), $item->getIliasSourceNic()));
		$this->object->setExtraInfo($extra_info);


		// Don't save the question additionally to DB before media object handling
		// this would create double rows for options, prts etc.

		/*********************************
		 * Media object handling
		 * @see assClozeTestImport
		 ********************************/


		// handle the import of media objects in XHTML code
		$questiontext = $this->object->getQuestion();

		if (is_array($_SESSION["import_mob_xhtml"])) {
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob) {
				if ($tst_id > 0) {
					//#22754
					$importfile = $this->getTstImportArchivDirectory() . '/' . current(explode('?', $mob["uri"]));
				} else {
					//#22754
					$importfile = $this->getQplImportArchivDirectory() . '/' . current(explode('?', $mob["uri"]));
				}

				$GLOBALS['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

				$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
				ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());

				$questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
				$options->setSpecificFeedback(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getSpecificFeedback()));
				$options->setPRTCorrect(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getPRTCorrect()));
				$options->setPRTIncorrect(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getPRTIncorrect()));
				$options->setPRTPartiallyCorrect(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getPRTPartiallyCorrect()));
				$extra_info->setHowToSolve(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $extra_info->getHowToSolve()));
				foreach ($prts as $prt_name => $prt) {
					foreach ($prt->getPRTNodes() as $node_name => $node) {
						$node->setFalseFeedback(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $node->getFalseFeedback()));
						$node->setTrueFeedback(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $node->getTrueFeedback()));
					}
				}
			}
		}
		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
		$options->setSpecificFeedback(ilRTE::_replaceMediaObjectImageSrc($options->getSpecificFeedback(), 1));
		$options->setPRTCorrect(ilRTE::_replaceMediaObjectImageSrc($options->getPRTCorrect(), 1));
		$options->setPRTIncorrect(ilRTE::_replaceMediaObjectImageSrc($options->getPRTIncorrect(), 1));
		$options->setPRTPartiallyCorrect(ilRTE::_replaceMediaObjectImageSrc($options->getPRTPartiallyCorrect(), 1));
		$extra_info->setHowToSolve(ilRTE::_replaceMediaObjectImageSrc($extra_info->getHowToSolve(), 1));
		foreach ($prts as $prt_name => $prt) {
			foreach ($prt->getPRTNodes() as $node_name => $node) {
				$node->setFalseFeedback(ilRTE::_replaceMediaObjectImageSrc($node->getFalseFeedback(), 1));
				$node->setTrueFeedback(ilRTE::_replaceMediaObjectImageSrc($node->getTrueFeedback(), 1));
			}
		}

		// now save the question as a whole
		$this->object->saveToDb("", TRUE);

		if ($tst_id > 0) {
			$q_1_id = $this->object->getId();
			$question_id = $this->object->duplicate(true, null, null, null, $tst_id);
			$tst_object->questions[$question_counter++] = $question_id;
			$import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
		} else {
			$import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
		}
	}

	/** @var assStackQuestion */
	//var $object;

	/**
	 * Receives parameters from a QTI parser and creates a valid ILIAS question object
	 *
	 * @param object $item The QTI item object
	 * @param integer $questionpool_id The id of the parent questionpool
	 * @param integer $tst_id The id of the parent test if the question is part of a test
	 * @param object $tst_object A reference to the parent test object
	 * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	 * @param array $import_mapping An array containing references to included ILIAS objects
	 * @access public
	 */
	function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		global $ilUser;

		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);
		$presentation = $item->getPresentation();
		$duration = $item->getDuration();
		$shuffle = 0;
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
		$answers = array();

		//Obtain question general datae
		$this->addGeneralMetadata($item);
		$this->object->setTitle($item->getTitle());
		$this->object->setNrOfTries($item->getMaxattempts());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
		$this->object->setObjId($questionpool_id);
		$this->object->setPoints((float)$item->getMetadataEntry("POINTS"));
		$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);

		$this->object->saveQuestionDataToDb();

		//Imported question
		$stack_question = unserialize(base64_decode($item->getMetadataEntry('stack_question')));
		$question_id = $this->object->getId();

		//OPTIONS
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionOptions.php");

		$options = new assStackQuestionOptions(-1, $question_id);
		$options->setQuestionSimplify((int)ilUtil::secureString($stack_question['options']['options']['simplify']));
		$options->setAssumePositive((int)ilUtil::secureString($stack_question['options']['options']['assumepos']));
		$options->setMultiplicationSign(ilUtil::secureString($stack_question['options']['options']['multiplicationsign']));
		$options->setSqrtSign((int)ilUtil::secureString($stack_question['options']['options']['sqrtsign']));
		$options->setComplexNumbers(ilUtil::secureString($stack_question['options']['options']['complexno']));
		$options->setInverseTrig(ilUtil::secureString($stack_question['options']['options']['inversetrig']));
		$options->setMatrixParens(ilUtil::secureString($stack_question['options']['options']['matrixparens']));

		//Not using secure string because the format would be lost
		$options->setQuestionVariables(assStackQuestionUtils::_debugText($stack_question['options']['ilias_options']['question_variables']));
		$options->setQuestionNote(assStackQuestionUtils::_debugText($stack_question['options']['ilias_options']['question_note']));

		$options->setSpecificFeedback(ilUtil::secureString($stack_question['options']['ilias_options']['specific_feedback'], false));

		$options->setPRTCorrect(ilUtil::secureString($stack_question['options']['ilias_options']['prt_correct'], false));
		$options->setPRTCorrectFormat(1);
		$options->setPRTPartiallyCorrect(ilUtil::secureString($stack_question['options']['ilias_options']['prt_partially_correct'], false));
		$options->setPRTPartiallyCorrectFormat(1);
		$options->setPRTIncorrect(ilUtil::secureString($stack_question['options']['ilias_options']['prt_incorrect'], false));
		$options->setPRTIncorrectFormat(1);

		$options->setVariantsSelectionSeeds(ilUtil::secureString($stack_question['options']['ilias_options']['variants_selection_seed']));

		$this->object->setOptions($options);

		//INPUTS
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionInput.php");
		$inputs_from_array = $stack_question['inputs']['inputs'];
		$inputs = array();

		//load only those inputs appearing in the question text
		foreach ($inputs_from_array as $input_name => $input_data) {

			$input_type = $input_data['type'];

			$input = new assStackQuestionInput(-1, $question_id, $input_name, $input_type, $input_data["tans"]);

			$input->setBoxSize((int)ilUtil::secureString($input_data["box_size"]));
			$input->setStrictSyntax((boolean)ilUtil::secureString($input_data["strict_syntax"]));
			$input->setInsertStars((int)ilUtil::secureString($input_data["insert_stars"]));
			$input->setTeacherAnswer(ilUtil::secureString($input_data["tans"]));
			$input->setSyntaxHint((isset($input_data["syntax_hint"]) and $input_data["syntax_hint"] != NULL) ? trim(ilUtil::secureString($input_data["syntax_hint"])) : "");
			$input->setForbidWords(ilUtil::secureString($input_data["forbid_words"]));
			$input->setAllowWords(ilUtil::secureString($input_data["allow_words"]));
			$input->setForbidFloat((boolean)ilUtil::secureString($input_data["forbid_float"]));
			$input->setRequireLowestTerms((boolean)ilUtil::secureString($input_data["require_lowest_terms"]));
			$input->setCheckAnswerType((boolean)ilUtil::secureString($input_data["check_answer_type"]));
			$input->setMustVerify((boolean)ilUtil::secureString($input_data["must_verify"]));
			$input->setShowValidation((int)ilUtil::secureString($input_data["show_validation"]));
			$input->setOptions(ilUtil::secureString($input_data["options"]));
			$inputs[$input_name] = $input;

		}
		$this->object->setInputs($inputs);

		//PRTs
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionPRT.php");
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionPRTNode.php");
		$prts_from_array = $stack_question['prts'];
		$prts = array();

		foreach ($prts_from_array as $prt_name => $prt_data) {

			$prt = new assStackQuestionPRT(-1, $question_id);
			$prt_nodes = array();

			//Filling object with data from DB
			$prt->setPRTName(ilUtil::secureString($prt_name));
			$prt->setPRTValue(ilUtil::secureString($prt_data["value"]));
			$prt->setAutoSimplify((int)ilUtil::secureString($prt_data["auto_simplify"]));

			$prt->setPRTFeedbackVariables(assStackQuestionUtils::_debugText($prt_data["feedback_variables"]));

			$prt->setFirstNodeName(ilUtil::secureString($prt_data["first_node_name"]));

			//Reading nodes
			foreach ($prt_data['nodes'] as $node_name => $node_data) {
				$node = new assStackQuestionPRTNode(-1, $question_id, $prt_name, $node_name, ilUtil::secureString($node_data["true_next_node"]), ilUtil::secureString($node_data["false_next_node"]));

				$node->setAnswerTest(ilUtil::secureString($node_data["answer_test"]));
				$node->setStudentAnswer(ilUtil::secureString($node_data["sans"]));
				$node->setTeacherAnswer(ilUtil::secureString($node_data["tans"]));
				$node->setTestOptions(ilUtil::secureString($node_data["test_options"]));
				$node->setQuiet((int)ilUtil::secureString($node_data["quiet"]));
				$node->setTrueScore(ilUtil::secureString($node_data["true_score"]));
				$node->setTrueScoreMode(ilUtil::secureString($node_data["true_score_mode"]));
				$node->setTruePenalty(ilUtil::secureString($node_data["true_penalty"]));
				$node->setTrueAnswerNote(ilUtil::secureString($node_data["true_answer_note"]));
				$node->setTrueFeedback(ilUtil::secureString($node_data["true_feedback"], false));
				$node->setTrueFeedbackFormat((int)ilUtil::secureString($node_data["true_feedback_format"]));
				$node->setFalseScore(ilUtil::secureString($node_data["false_score"]));
				$node->setFalseScoreMode(ilUtil::secureString($node_data["false_score_mode"]));
				$node->setFalsePenalty(ilUtil::secureString($node_data["false_penalty"]));
				$node->setFalseAnswerNote(ilUtil::secureString($node_data["false_answer_note"]));
				$node->setFalseFeedback(ilUtil::secureString($node_data["false_feedback"], false));
				$node->setFalseFeedbackFormat((int)ilUtil::secureString($node_data["false_feedback_format"]));

				$prt_nodes[$node_name] = $node;
			}

			$prt->setPRTNodes($prt_nodes);
			$prt->setNumberOfNodes(sizeof($prt_nodes));

			$prts[$prt_name] = $prt;
		}
		$this->object->setPotentialResponsesTrees($prts);

		//SEEDS
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionDeployedSeed.php");
		$seeds = array();
		foreach ($stack_question['deployed_variants'] as $seed_id => $deployed_variant) {
			$seeds[] = new assStackQuestionDeployedSeed(-1, $question_id, $deployed_variant);
		}
		$this->object->setDeployedSeeds($seeds);

		//TESTS
		if (isset($stack_question['unit_tests']['test_cases']) and is_array($stack_question['unit_tests']['test_cases'])) {

			$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTest.php");
			$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTestInput.php");
			$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTestExpected.php");

			$tests = array();
			foreach ($stack_question['unit_tests']['test_cases'] as $test_case_name => $test_case_data) {

				$test = new assStackQuestionTest(-1, $question_id, (int)$test_case_name);
				$tests_input = array();
				$tests_expected = array();

				//Inputs
				foreach ($test_case_data["inputs"] as $input_name => $test_inputs_data) {
					$test_input = new assStackQuestionTestInput(-1, $question_id, (int)$test_case_name);

					$test_input->setTestInputName($input_name);
					$test_input->setTestInputValue($test_inputs_data["value"]);
					$tests_input[] = $test_input;
				}
				$test->setTestInputs($tests_input);

				//Expected
				foreach ($test_case_data["expected"] as $prt_name => $test_expected_data) {
					$expected = new assStackQuestionTestExpected(-1, $question_id, $test_case_name, $prt_name);
					$expected->setExpectedAnswerNote($test_expected_data["answer_note"]);
					$expected->setExpectedScore($test_expected_data["score"]);
					$expected->setExpectedPenalty($test_expected_data["penalty"]);
					$tests_expected[] = $expected;
				}
				$test->setTestExpected($tests_expected);

				$test->setNumberOfTests(!empty($test->getTestInputs()));
				$tests[$test->getTestCase()] = $test;
			}
		}

		//EXTRA INFO
		$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionExtraInfo.php");
		$extra_info = new assStackQuestionExtraInfo(-1, $question_id);
		$extra_info->setHowToSolve(ilUtil::secureString($stack_question['extra_information']['general_feedback'], false));
		$this->object->setExtraInfo($extra_info);

		// Don't save the question additionally to DB before media object handling
		// this would create double rows for options, prts etc.

		/*********************************
		 * Media object handling
		 * @see assClozeTestImport
		 ********************************/


		// handle the import of media objects in XHTML code
		$questiontext = $this->object->getQuestion();

		if (is_array($_SESSION["import_mob_xhtml"])) {
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob) {

				if ($tst_id > 0) {
					//#22754
					$importfile = $this->getTstImportArchivDirectory() . '/' . current(explode('?', $mob["uri"]));
				} else {
					//#22754
					$importfile = $this->getQplImportArchivDirectory() . '/' . current(explode('?', $mob["uri"]));
				}

				$GLOBALS['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);
				try {
					$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
					ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());

					$questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
					$options->setSpecificFeedback(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getSpecificFeedback()));
					$options->setPRTCorrect(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getPRTCorrect()));
					$options->setPRTIncorrect(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getPRTIncorrect()));
					$options->setPRTPartiallyCorrect(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $options->getPRTPartiallyCorrect()));
					$extra_info->setHowToSolve(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $extra_info->getHowToSolve()));
					foreach ($prts as $prt_name => $prt) {
						foreach ($prt->getPRTNodes() as $node_name => $node) {
							$node->setFalseFeedback(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $node->getFalseFeedback()));
							$node->setTrueFeedback(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $node->getTrueFeedback()));
						}
					}
				} catch (Exception $e) {
					ilUtil::sendFailure($e->getMessage(), true);
				}
			}
		}

		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
		$options->setSpecificFeedback(ilRTE::_replaceMediaObjectImageSrc($options->getSpecificFeedback(), 1));
		$options->setPRTCorrect(ilRTE::_replaceMediaObjectImageSrc($options->getPRTCorrect(), 1));
		$options->setPRTIncorrect(ilRTE::_replaceMediaObjectImageSrc($options->getPRTIncorrect(), 1));
		$options->setPRTPartiallyCorrect(ilRTE::_replaceMediaObjectImageSrc($options->getPRTPartiallyCorrect(), 1));
		$extra_info->setHowToSolve(ilRTE::_replaceMediaObjectImageSrc($extra_info->getHowToSolve(), 1));
		foreach ($prts as $prt_name => $prt) {
			foreach ($prt->getPRTNodes() as $node_name => $node) {
				$node->setFalseFeedback(ilRTE::_replaceMediaObjectImageSrc($node->getFalseFeedback(), 1));
				$node->setTrueFeedback(ilRTE::_replaceMediaObjectImageSrc($node->getTrueFeedback(), 1));
			}
		}

		// now save the question as a whole
		$this->object->saveToDb("", TRUE);

		if ($tst_id > 0) {
			$q_1_id = $this->object->getId();
			$question_id = $this->object->duplicate(true, null, null, null, $tst_id);
			$tst_object->questions[$question_counter++] = $question_id;
			$import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
		} else {
			$import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
		}
	}
}
