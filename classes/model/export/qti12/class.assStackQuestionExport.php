<?php
/**
 * Copyright (c) 2022 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * STACK Question EXPORT OF QUESTIONS to ILIAS
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id: 5.7$
 *
 */
include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

class assStackQuestionExport extends assQuestionExport
{
	/** @var assStackQuestion */
	var $object;

	/**
	 * Returns a QTI xml representation of the question
	 *
	 * @return string The QTI xml representation of the question
	 * @access public
	 */
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
	{
		global $ilias;

		//get Question Array
		$question_array = assStackQuestionUtils::_questionToArray($this->object);

		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;

		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
			"title" => $this->object->getTitle(),
			"maxattempts" => $this->object->getNrOfTries()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);

		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->object->getComment());

		// add estimated working time
		$workingtime = $this->object->getEstimatedWorkingTime();
		$duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
		$a_xml_writer->xmlElement("duration", NULL, $duration);

		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "QUESTIONTYPE");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getQuestionType());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");

		// additional content editing information
		$this->addAdditionalContentEditingModeInformation($a_xml_writer);
		$this->addGeneralMetadata($a_xml_writer);

		$a_xml_writer->xmlElement("fieldlabel", NULL, "POINTS");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getPoints());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		//QUESTION
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "stack_question");
		$a_xml_writer->xmlElement("fieldentry", NULL, base64_encode(serialize($question_array)));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		$a_xml_writer->xmlEndTag("qtimetadata");
		$a_xml_writer->xmlEndTag("itemmetadata");

		//QTI presentation
		$attrs = array(
			"label" => $this->object->getTitle()
		);
		$a_xml_writer->xmlStartTag("presentation", $attrs);
		// add flow to presentation
		$a_xml_writer->xmlStartTag("flow");

		$question_text = $this->object->getQuestion() ?: '&nbsp;';
		$this->object->addQTIMaterial($a_xml_writer, $question_text);

		foreach ($this->object->prts as $prt) {
			foreach ($prt->getNodes() as $node) {
				$feedback = $node->getFeedbackFromNode();

				$this->object->addQTIMaterial($a_xml_writer, $feedback['true_feedback']);
				$this->object->addQTIMaterial($a_xml_writer, $feedback['false_feedback']);
			}
		}

		$this->object->addQTIMaterial($a_xml_writer, $this->object->specific_feedback);

		$this->object->addQTIMaterial($a_xml_writer, $this->object->prt_correct);
		$this->object->addQTIMaterial($a_xml_writer, $this->object->prt_partially_correct);
		$this->object->addQTIMaterial($a_xml_writer, $this->object->prt_incorrect);

		$this->object->addQTIMaterial($a_xml_writer, $this->object->general_feedback);

		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");
		$a_xml_writer->xmlEndTag("item");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(false);
		if (!$a_include_header) {
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}

	/**
	 * Exports the evaluation data to the Microsoft Excel file format
	 *
	 * @param bool $deliver
	 * @param string $filterby
	 * @param string $filtertext Filter text for the user data
	 * @param boolean $passedonly TRUE if only passed user datasets should be exported, FALSE otherwise
	 *
	 * @return string
	 */
	public function exportToExcel($deliver = TRUE, $filterby = "", $filtertext = "", $passedonly = FALSE): string
	{
		if (strcmp($this->mode, "aggregated") == 0) return $this->aggregatedResultsToExcel($deliver);

		require_once './Services/Excel/classes/class.ilExcelWriterAdapter.php';
		$excelfile = ilUtil::ilTempnam();
		$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
		$testname = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->test_obj->getTitle())) . ".xls";
		$workbook = $adapter->getWorkbook();
		$workbook->setVersion(8); // Use Excel97/2000 Format
		// Creating a worksheet
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_datetime =& $workbook->addFormat();
		$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		require_once './Services/Excel/classes/class.ilExcelUtils.php';
		$worksheet =& $workbook->addWorksheet(ilExcelUtils::_convert_text($this->lng->txt("tst_results")));
		$additionalFields = $this->test_obj->getEvaluationAdditionalFields();
		$row = 0;
		$col = 0;

		if ($this->test_obj->getAnonymity()) {
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("counter")), $format_title);
		} else {
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("name")), $format_title);
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("login")), $format_title);
		}
		if (count($additionalFields)) {
			foreach ($additionalFields as $fieldname) {
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt($fieldname)), $format_title);
			}
		}
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_resultspoints")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("maximum_points")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_resultsmarks")), $format_title);
		if ($this->test_obj->ects_output) {
			$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("ects_grade")), $format_title);
		}
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_qworkedthrough")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_qmax")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_pworkedthrough")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_timeofwork")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_atimeofwork")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_firstvisit")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_lastvisit")), $format_title);

		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_mark_median")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_rank_participant")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_rank_median")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_total_participants")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("tst_stat_result_median")), $format_title);
		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("scored_pass")), $format_title);

		$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("pass")), $format_title);

		$counter = 1;
		$data =& $this->test_obj->getCompleteEvaluationData(TRUE, $filterby, $filtertext);
		$firstrowwritten = false;
		foreach ($data->getParticipants() as $active_id => $userdata) {
			$remove = FALSE;
			if ($passedonly) {
				if ($data->getParticipant($active_id)->getPassed() == FALSE) {
					$remove = TRUE;
				}
			}
			if (!$remove) {
				$row++;
				if ($this->test_obj->isRandomTest() || $this->test_obj->getShuffleQuestions()) {
					$row++;
				}
				$col = 0;
				if ($this->test_obj->getAnonymity()) {
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($counter));
				} else {
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getName()));
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getLogin()));
				}
				if (count($additionalFields)) {
					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($additionalFields as $fieldname) {
						if (strcmp($fieldname, "gender") == 0) {
							$worksheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt("gender_" . $userfields[$fieldname])));
						} else {
							$worksheet->write($row, $col++, ilExcelUtils::_convert_text($userfields[$fieldname]));
						}
					}
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getReached()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getMaxpoints()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getMark()));
				if ($this->test_obj->ects_output) {
					$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getECTSMark()));
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getQuestionsWorkedThrough()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getParticipant($active_id)->getNumberOfQuestions()));
				$worksheet->write($row, $col++, $data->getParticipant($active_id)->getQuestionsWorkedThroughInPercent() / 100.0, $format_percent);
				$time = $data->getParticipant($active_id)->getTimeOfWork();
				$time_seconds = $time;
				$time_hours = floor($time_seconds / 3600);
				$time_seconds -= $time_hours * 3600;
				$time_minutes = floor($time_seconds / 60);
				$time_seconds -= $time_minutes * 60;
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)));
				$time = $data->getParticipant($active_id)->getQuestionsWorkedThrough() ? $data->getParticipant($active_id)->getTimeOfWork() / $data->getParticipant($active_id)->getQuestionsWorkedThrough() : 0;
				$time_seconds = $time;
				$time_hours = floor($time_seconds / 3600);
				$time_seconds -= $time_hours * 3600;
				$time_minutes = floor($time_seconds / 60);
				$time_seconds -= $time_minutes * 60;
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds)));
				$fv = getdate($data->getParticipant($active_id)->getFirstVisit());
				$firstvisit = ilUtil::excelTime(
					$fv["year"],
					$fv["mon"],
					$fv["mday"],
					$fv["hours"],
					$fv["minutes"],
					$fv["seconds"]
				);
				$worksheet->write($row, $col++, $firstvisit, $format_datetime);
				$lv = getdate($data->getParticipant($active_id)->getLastVisit());
				$lastvisit = ilUtil::excelTime(
					$lv["year"],
					$lv["mon"],
					$lv["mday"],
					$lv["hours"],
					$lv["minutes"],
					$lv["seconds"]
				);
				$worksheet->write($row, $col++, $lastvisit, $format_datetime);

				$median = $data->getStatistics()->getStatistics()->median();
				$pct = $data->getParticipant($active_id)->getMaxpoints() ? $median / $data->getParticipant($active_id)->getMaxpoints() * 100.0 : 0;
				$mark = $this->test_obj->mark_schema->getMatchingMark($pct);
				$mark_short_name = "";
				if (is_object($mark)) {
					$mark_short_name = $mark->getShortName();
				}
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($mark_short_name));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getStatistics()->getStatistics()->rank($data->getParticipant($active_id)->getReached())));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getStatistics()->getStatistics()->rank_median()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($data->getStatistics()->getStatistics()->count()));
				$worksheet->write($row, $col++, ilExcelUtils::_convert_text($median));
				if ($this->test_obj->getPassScoring() == SCORE_BEST_PASS) {
					$worksheet->write($row, $col++, $data->getParticipant($active_id)->getBestPass() + 1);
				} else {
					$worksheet->write($row, $col++, $data->getParticipant($active_id)->getLastPass() + 1);
				}
				$startcol = $col;
				for ($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++) {
					$col = $startcol;
					$finishdate = $this->test_obj->getPassFinishDate($active_id, $pass);
					if ($finishdate > 0) {
						if ($pass > 0) {
							$row++;
							if ($this->test_obj->isRandomTest() || $this->test_obj->getShuffleQuestions()) {
								$row++;
							}
						}
						$worksheet->write($row, $col++, ilExcelUtils::_convert_text($pass + 1));
						if (is_object($data->getParticipant($active_id)) && is_array($data->getParticipant($active_id)->getQuestions($pass))) {
							foreach ($data->getParticipant($active_id)->getQuestions($pass) as $question) {
								$question_data = $data->getParticipant($active_id)->getPass($pass)->getAnsweredQuestionByQuestionId($question["id"]);
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($question_data["reached"]));
								if ($this->test_obj->isRandomTest() || $this->test_obj->getShuffleQuestions()) {
									$worksheet->write($row - 1, $col, ilExcelUtils::_convert_text(preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"]))), $format_title);
								} else {
									if ($pass == 0 && !$firstrowwritten) {
										$worksheet->write(0, $col, ilExcelUtils::_convert_text(preg_replace("/<.*?>/", "", $data->getQuestionTitle($question["id"]))), $format_title);
									}
								}
								$col++;
							}
							$firstrowwritten = true;
						}
					}
				}
				$counter++;
			}
		}
		if ($this->test_obj->getExportSettingsSingleChoiceShort() && !$this->test_obj->isRandomTest() && $this->test_obj->hasSingleChoiceQuestions()) {
			// special tab for single choice tests
			$titles =& $this->test_obj->getQuestionTitlesAndIndexes();
			$positions = array();
			$pos = 0;
			$row = 0;
			foreach ($titles as $id => $title) {
				$positions[$id] = $pos;
				$pos++;
			}
			$usernames = array();
			$participantcount = count($data->getParticipants());
			$allusersheet = false;
			$pages = 0;
			$resultsheet =& $workbook->addWorksheet($this->lng->txt("eval_all_users"));

			$col = 0;
			$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('name')), $format_title);
			$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('login')), $format_title);
			if (count($additionalFields)) {
				foreach ($additionalFields as $fieldname) {
					if (strcmp($fieldname, "matriculation") == 0) {
						$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('matriculation')), $format_title);
					}
				}
			}
			$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('test')), $format_title);
			foreach ($titles as $title) {
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($title), $format_title);
			}
			$row++;

			foreach ($data->getParticipants() as $active_id => $userdata) {
				$username = (!is_null($userdata) && ilExcelUtils::_convert_text($userdata->getName())) ? ilExcelUtils::_convert_text($userdata->getName()) : "ID $active_id";
				if (array_key_exists($username, $usernames)) {
					$usernames[$username]++;
					$username .= " ($i)";
				} else {
					$usernames[$username] = 1;
				}
				$col = 0;
				$resultsheet->write($row, $col++, $username);
				$resultsheet->write($row, $col++, $userdata->getLogin());
				if (count($additionalFields)) {
					$userfields = ilObjUser::_lookupFields($userdata->getUserID());
					foreach ($additionalFields as $fieldname) {
						if (strcmp($fieldname, "matriculation") == 0) {
							if (strlen($userfields[$fieldname])) {
								$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($userfields[$fieldname]));
							} else {
								$col++;
							}
						}
					}
				}
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->test_obj->getTitle()));
				$pass = $userdata->getScoredPass();
				if (is_object($userdata) && is_array($userdata->getQuestions($pass))) {
					foreach ($userdata->getQuestions($pass) as $question) {
						$objQuestion =& $this->test_obj->_instanciateQuestion($question["aid"]);
						if (is_object($objQuestion) && strcmp($objQuestion->getQuestionType(), 'assSingleChoice') == 0) {
							$solution = $objQuestion->getSolutionValues($active_id, $pass);
							$pos = $positions[$question["aid"]];
							$selectedanswer = "x";
							foreach ($objQuestion->getAnswers() as $id => $answer) {
								if (strlen($solution[0]["value1"]) && $id == $solution[0]["value1"]) {
									$selectedanswer = $answer->getAnswertext();
								}
							}
							$resultsheet->write($row, $col + $pos, ilExcelUtils::_convert_text($selectedanswer));
						}
					}
				}
				$row++;
			}
			if ($this->test_obj->isSingleChoiceTestWithoutShuffle()) {
				// special tab for single choice tests without shuffle option
				$pos = 0;
				$row = 0;
				$usernames = array();
				$allusersheet = false;
				$pages = 0;
				$resultsheet =& $workbook->addWorksheet($this->lng->txt("eval_all_users") . " (2)");

				$col = 0;
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('name')), $format_title);
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('login')), $format_title);
				if (count($additionalFields)) {
					foreach ($additionalFields as $fieldname) {
						if (strcmp($fieldname, "matriculation") == 0) {
							$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('matriculation')), $format_title);
						}
					}
				}
				$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->lng->txt('test')), $format_title);
				foreach ($titles as $title) {
					$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($title), $format_title);
				}
				$row++;

				foreach ($data->getParticipants() as $active_id => $userdata) {
					$username = (!is_null($userdata) && ilExcelUtils::_convert_text($userdata->getName())) ? ilExcelUtils::_convert_text($userdata->getName()) : "ID $active_id";
					if (array_key_exists($username, $usernames)) {
						$usernames[$username]++;
						$username .= " ($i)";
					} else {
						$usernames[$username] = 1;
					}
					$col = 0;
					$resultsheet->write($row, $col++, $username);
					$resultsheet->write($row, $col++, $userdata->getLogin());
					if (count($additionalFields)) {
						$userfields = ilObjUser::_lookupFields($userdata->getUserID());
						foreach ($additionalFields as $fieldname) {
							if (strcmp($fieldname, "matriculation") == 0) {
								if (strlen($userfields[$fieldname])) {
									$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($userfields[$fieldname]));
								} else {
									$col++;
								}
							}
						}
					}
					$resultsheet->write($row, $col++, ilExcelUtils::_convert_text($this->test_obj->getTitle()));
					$pass = $userdata->getScoredPass();
					if (is_object($userdata) && is_array($userdata->getQuestions($pass))) {
						foreach ($userdata->getQuestions($pass) as $question) {
							$objQuestion =& $this->test_obj->_instanciateQuestion($question["aid"]);
							if (is_object($objQuestion) && strcmp($objQuestion->getQuestionType(), 'assSingleChoice') == 0) {
								$solution = $objQuestion->getSolutionValues($active_id, $pass);
								$pos = $positions[$question["aid"]];
								$selectedanswer = chr(65 + $solution[0]["value1"]);
								$resultsheet->write($row, $col + $pos, ilExcelUtils::_convert_text($selectedanswer));
							}
						}
					}
					$row++;
				}
			}
		} else {
			// test participant result export
			$usernames = array();
			$participantcount = count($data->getParticipants());
			$allusersheet = false;
			$pages = 0;
			$i = 0;
			foreach ($data->getParticipants() as $active_id => $userdata) {
				$i++;

				$username = (!is_null($userdata) && ilExcelUtils::_convert_text($userdata->getName())) ? ilExcelUtils::_convert_text($userdata->getName()) : "ID $active_id";
				if (array_key_exists($username, $usernames)) {
					$usernames[$username]++;
					$username .= " ($i)";
				} else {
					$usernames[$username] = 1;
				}
				if ($participantcount > 250) {
					if (!$allusersheet || ($pages - 1) < floor($row / 64000)) {
						$resultsheet =& $workbook->addWorksheet($this->lng->txt("eval_all_users") . (($pages > 0) ? " (" . ($pages + 1) . ")" : ""));
						$allusersheet = true;
						$row = 0;
						$pages++;
					}
				} else {
					$resultsheet =& $workbook->addWorksheet($username);
				}
				if (method_exists($resultsheet, "writeString")) {
					$pass = $userdata->getScoredPass();
					$row = ($allusersheet) ? $row : 0;
					$resultsheet->writeString($row, 0, ilExcelUtils::_convert_text(sprintf($this->lng->txt("tst_result_user_name_pass"), $pass + 1, $userdata->getName())), $format_bold);
					$row += 2;
					if (is_object($userdata) && is_array($userdata->getQuestions($pass))) {
						foreach ($userdata->getQuestions($pass) as $question) {
							require_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
							$question = assQuestion::_instanciateQuestion($question["id"]);
							if (is_object($question)) {
								$row = $question->setExportDetailsXLS($resultsheet, $row, $active_id, $pass, $format_title, $format_bold);
							}
						}
					}
				}
			}
		}
		$workbook->close();
		if ($deliver) {
			ilUtil::deliverFile($excelfile, $testname, "application/vnd.ms-excel", false, true);
			exit;
		} else {
			return $excelfile;
		}
	}
	/**
	 * Add an RTE text
	 * This will change the media references and wrap the text in CDATA
	 * @param ilXmlWriter    XML writer
	 * @param string        text to add
	 * @param string        tag for the element
	 * @param array        attributes
	 */
	private static function _addRTEText($a_xml_writer, $a_text, $a_tag = 'text', $a_attr = null)
	{
		$text = preg_replace('/src=".*\/mobs\/mm_([0-9]+)\/([^"]+)"/', 'src="@@PLUGINFILE@@/$2"', $a_text);

		$text = '<![CDATA[' . $text . ']]>';

		$a_xml_writer->xmlElement($a_tag, NULL, $text, false, false);
	}


	/**
	 * Add media files as <file> elements
	 * @param ilXmlWriter        XML writer
	 * @param array            name => content
	 * @param string            tag for the element
	 */
	private static function _addRTEMedia($a_xml_writer, $a_media, $a_tag = 'file')
	{
		foreach ($a_media as $name => $content) {
			$attr = array('name' => $name, 'path' => '/', 'encoding' => 'base64');
			$a_xml_writer->xmlElement('file', $attr, base64_encode($content), false, false);
		}
	}

	/**
	 * Get the media files used in an RTE text
	 * @param string        text to analyze
	 * @param assStackQuestion question
	 * @return    array        name => file content
	 */
	private static function _getRTEMedia($a_text, $stack_question = ""): array
	{
		$media = array();
		$matches = array();
		preg_match_all('/src=".*\/mobs\/mm_([0-9]+)\/([^"]+)"/', $a_text, $matches);

		for ($i = 0; $i < count($matches[0]); $i++) {
			$id = $matches[1][$i];
			$name = $matches[2][$i];

			$new_match = explode('?', $name);

			if (is_file(ilUtil::getWebspaceDir() . "/mobs/mm_" . $id . '/' . $new_match[0])) {
				$media[$new_match[0]] = file_get_contents(ilUtil::getWebspaceDir() . "/mobs/mm_" . $id . '/' . $new_match[0]);
			}
		}

		return $media;
	}
} 