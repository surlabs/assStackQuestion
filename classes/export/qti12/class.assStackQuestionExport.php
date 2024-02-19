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

/**
 * STACK Question EXPORT OF QUESTIONS to ILIAS
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 *
 */
//include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

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

		//include_once("./Services/Xml/classes/class.ilXmlWriter.php");
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

		// additional content editing information
		$a_xml_writer->xmlStartTag("qtimetadatafield");
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
			foreach ($prt->get_nodes() as $node) {
				$this->object->addQTIMaterial($a_xml_writer, $node->truefeedback);
				$this->object->addQTIMaterial($a_xml_writer, $node->falsefeedback);
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