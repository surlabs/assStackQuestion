<?php
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
 * Button form property GUI class
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class ilButtonFormProperty extends ilFormPropertyGUI
{
	protected $template;

	protected $value;

	protected $command;

	protected $action;


	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);

		//Set template for button
		$template = new ilTemplate('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/tpl.button_form_property.html', TRUE, TRUE);
		$this->setTemplate($template);
	}

	/**
	 * Insert property html
	 *
	 * @return    int    Size
	 */
	function insert(&$a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Insert property html
	 */
	function render()
	{
		$this->getTemplate()->setCurrentBlock("prop_button");
		$this->getTemplate()->setVariable("BUTTON_TYPE", "delete_node");
		$this->getTemplate()->setVariable("BUTTON_TITLE", $this->getTitle());
		if ($this->getAction()) {
			$this->getTemplate()->setVariable("ACTION", "[" . $this->getAction() . "]");
		}
		if ($this->getCommand()) {
			$this->getTemplate()->setVariable("COMMAND", "[" . $this->getCommand() . "]");
		}
		$this->getTemplate()->parseCurrentBlock();

		return $this->getTemplate()->get();
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * @return mixed
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * @param mixed $command
	 */
	public function setCommand($command)
	{
		$this->command = $command;
	}

	/**
	 * @return mixed
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * @param mixed $action
	 */
	public function setAction($action)
	{
		$this->action = $action;
	}

	/**
	 * @return mixed
	 */
	public function getAction()
	{
		return $this->action;
	}


}