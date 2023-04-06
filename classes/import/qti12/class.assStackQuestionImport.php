<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

/**
 * STACK Question IMPORT OF QUESTIONS from an ILIAS file
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 * @ingroup    ModulesTestQuestionPool
 *
 */

require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
require_once './Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php';


class assStackQuestionImport extends assQuestionImport
{
	/** @var assStackQuestion */
	var $object;

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
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{

		global $DIC;
		$ilUser = $DIC['ilUser'];
		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);

		$presentation = $item->getPresentation();
		$duration = $item->getDuration();
		$shuffle = 0;
		$selectionLimit = null;
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
		$answers = array();

		//Obtain question general data
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

		//question
		$stack_question = unserialize(base64_decode($item->getMetadataEntry('stack_question')));
		$this->object = assStackQuestionUtils::_arrayToQuestion($stack_question,$this->object);

		// Don't save the question additionally to DB before media object handling
		// this would create double rows for options, prts etc.

		/*********************************
		 * Media object handling
		 * @see assClozeTestImport
		 ********************************/

		// handle the import of media objects in XHTML code
		$question_text = $this->object->getQuestion();

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

				$media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
				ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());

				$question_text = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $question_text);

				$this->object->specific_feedback = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->specific_feedback);

				$this->object->prt_correct = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->prt_correct);
				$this->object->prt_partially_correct = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->prt_partially_correct);
				$this->object->prt_incorrect = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->prt_incorrect);

				$this->object->general_feedback = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->general_feedback);

				foreach ($this->object->prts as $prt) {
					foreach ($prt->getNodes() as $node) {
						$feedback = $node->getFeedbackFromNode();
						$node->setBranchFeedback(0, str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $feedback['false_feedback']));
						$node->setBranchFeedback(1, str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $feedback['true_feedback']));
					}
				}
			}
		}

		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($question_text, 1));

		$this->object->specific_feedback = ilRTE::_replaceMediaObjectImageSrc($this->object->specific_feedback, 1);

		$this->object->prt_correct = ilRTE::_replaceMediaObjectImageSrc($this->object->prt_correct, 1);
		$this->object->prt_partially_correct = ilRTE::_replaceMediaObjectImageSrc($this->object->prt_partially_correct, 1);
		$this->object->prt_incorrect = ilRTE::_replaceMediaObjectImageSrc($this->object->prt_incorrect, 1);

		$this->object->general_feedback = ilRTE::_replaceMediaObjectImageSrc($this->object->general_feedback, 1);

		foreach ($this->object->prts as $prt) {
			foreach ($prt->getNodes() as $node) {

				$feedback = $node->getFeedbackFromNode();

				$node->setBranchFeedback(0, ilRTE::_replaceMediaObjectImageSrc($feedback['false_feedback'], 1));
				$node->setBranchFeedback(1, ilRTE::_replaceMediaObjectImageSrc($feedback['true_feedback'], 1));
			}
		}

		// now save the question as a whole
		$this->object->saveToDb();

		if ($tst_id > 0) {
			$q_1_id = $this->object->getId();
			$question_id = $this->object->duplicate(true, null, null, null, $tst_id);
			$tst_object->questions[$question_counter++] = $question_id;
			$import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
		} else {
			$import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
		}

        return $import_mapping;
	}
}
