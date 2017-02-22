<?php
/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/FormProperties/class.ilMultipartFormPropertyGUI.php';
require_once './Services/Accordion/classes/class.ilAccordionGUI.php';

/**
 * Accordion property GUI class
 * This object implements the ILIAS Accordion object as a Form property.
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 *
 */
class ilAccordionFormPropertyGUI extends ilMultipartFormPropertyGUI
{

	/**
	 * @var ilTemplate
	 */
	private $template;

	/**
	 * @var float
	 */
	private $width;

	function __construct($a_title = "", $a_postvar = "", $a_container_width = "", $a_show_title = "")
	{
		parent::__construct($a_title, $a_postvar, $a_container_width, $a_show_title);

		//Set template for accordion
		$template = new ilTemplate('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/tpl.accordion_form_property.html', TRUE, TRUE);
		$this->setTemplate($template);
	}

	/**
	 * @return HTML for this form property
	 */
	protected function render()
	{
		//Create Accordion object
		$accordion = new ilAccordionGUI();
		$accordion->setId($this->getTitle());

		//Marko's suggestion allow multiopened
		$accordion->setAllowMultiOpened(TRUE);

		//Set container width
		$this->getTemplate()->setVariable("CONTAINER_WIDTH", $this->getContainerWidth());

		//Filling parts
		foreach ($this->getParts() as $part)
		{
			//Addition of form properties
			foreach ($part->getContent() as $form_property)
			{
				$this->getTemplate()->setVariable("PART_TYPE", $part->getType());

				//Fill Title and Info
				$this->getTemplate()->setCurrentBlock('prop_container');
				$this->getTemplate()->setVariable("PART_TYPE", $part->getType());

				if ($this->getShowTitle())
				{
					if ($form_property->getRequired())
					{
						$this->getTemplate()->setVariable("PROP_TITLE", $form_property->getTitle() . "<font color=\"red\"> *</font>");
					} else
					{
						$this->getTemplate()->setVariable("PROP_TITLE", $form_property->getTitle());
					}
				}
				//Set width
				$this->getTemplate()->setVariable("TITLE_WIDTH", $this->getWidthDivision('title'));
				$this->getTemplate()->setVariable("FOOTER_WIDTH", $this->getWidthDivision('footer'));
				$this->getTemplate()->setVariable("PROP_INFO", $form_property->getInfo());

				//Add specific test info
				$castext_english = "In this field you can use CAS Text. CASText is CAS-enabled text. CASText is simply HTML into which LaTeX mathematics and CAS commands can be embedded. These CAS commands are executed before the question is displayed to the user. Use only simple LaTeX mathematics structures. Only a small part of core LaTeX is supported.";
				$castext_german = "In diesem Feld können Sie CAS Text verwenden, CASText ist CAS-aktivierter Text. CASText ist einfach HTML, in das LaTeX-Mathematik und CAS-Befehle eingebettet werden können. Diese CAS-Befehle werden ausgeführt, bevor die Frage dem Benutzer angezeigt wird. Verwenden Sie nur einfache LaTeX-Mathematikstrukturen. Nur ein kleiner Teil des LaTeX-Kerns wird unterstützt.";
				$html_english = "In this field, only HTML elements are allowed, CASText won't be rendered";
				$html_german = "In diesem Feld sind nur HTML-Elemente erlaubt, CASText wird nicht gerendert";
				$casexpresion_english = "In this field, you can only use CAS expresion, but not HTML code.";
				$casexpresion_german = "In diesem Feld können sie nur CAS-Ausdruck verwenden, kein HTML-Code.";

				global $lng;

				if ($form_property->postvar == "options_prt_correct")
				{
					$comment_id = rand(100000, 999999);
					require_once("Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
					ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $lng->getUserLanguage() == "de" ? $html_german : $html_english);
					$this->getTemplate()->setVariable("COMMENT_ID", $comment_id);
					$this->getTemplate()->setVariable("SPECIFIC_TEXT_INFO", "<a href='javascript:;'>[HTML]</a>");
				}
				if ($form_property->postvar == "options_prt_partially_correct")
				{
					$comment_id = rand(100000, 999999);
					require_once("Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
					ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $lng->getUserLanguage() == "de" ? $html_german : $html_english);
					$this->getTemplate()->setVariable("COMMENT_ID", $comment_id);
					$this->getTemplate()->setVariable("SPECIFIC_TEXT_INFO", "<a href='javascript:;'>[HTML]</a>");
				}
				if ($form_property->postvar == "options_prt_incorrect")
				{
					$comment_id = rand(100000, 999999);
					require_once("Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
					ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $lng->getUserLanguage() == "de" ? $html_german : $html_english);
					$this->getTemplate()->setVariable("COMMENT_ID", $comment_id);
					$this->getTemplate()->setVariable("SPECIFIC_TEXT_INFO", "<a href='javascript:;'>[HTML]</a>");
				}
				if ($form_property->postvar == "options_how_to_solve")
				{
					$comment_id = rand(100000, 999999);
					require_once("Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
					ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $lng->getUserLanguage() == "de" ? $castext_german : $castext_english);
					$this->getTemplate()->setVariable("COMMENT_ID", $comment_id);
					$this->getTemplate()->setVariable("SPECIFIC_TEXT_INFO", "<a href='javascript:;'>[CAS Text]</a>");
				}

				include_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';
				if (assStackQuestionUtils::_endsWith($form_property->postvar, "_input_options"))
				{
					$comment_id = rand(100000, 999999);
					require_once("Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
					ilTooltipGUI::addTooltip('ilAssStackQuestion' . $comment_id, $lng->getUserLanguage() == "de" ? $casexpresion_german : $casexpresion_english);
					$this->getTemplate()->setVariable("COMMENT_ID", $comment_id);
					$this->getTemplate()->setVariable("SPECIFIC_TEXT_INFO", $lng->getUserLanguage() == "de" ? "<a href='javascript:;'>[CAS Ausdruck]</a>" : "<a href='javascript:;'>[CAS Expresion]</a>");
				}

				//Fill Form property
				$form_property->insert($this->getTemplate(), $this->getWidthDivision('content'));

				//Fill info and footer
				$this->getTemplate()->setCurrentBlock('prop_container');
				$this->getTemplate()->setVariable("TITLE_WIDTH", $this->getWidthDivision('title'));
				$this->getTemplate()->setVariable("CONTENT_WIDTH", $this->getWidthDivision('content'));
				$this->getTemplate()->setVariable("FOOTER_WIDTH", $this->getWidthDivision('footer'));
				$this->getTemplate()->parseCurrentBlock();
			}
			$accordion->addItem($part->getTitle(), $this->getTemplate()->get());
			//Set template for accordion
			$template = new ilTemplate('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/tpl.accordion_form_property.html', TRUE, TRUE);
			$this->setTemplate($template);
		}

		return $accordion->getHTML();
	}

	/*
	 * GETTERS AND SETTERS
	 */

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

}