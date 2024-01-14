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
 * Multipart form part object class
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 *
 */
class ilMultipartFormPart
{

	/**
	 * Title of the part.
	 * @var string
	 */
	private $title;

	/**
	 * Array of form properties objects included in this part.
	 * @var array
	 */
	private $content = array();

	/**
	 * type of the part
	 * @var string
	 */
	private $type;

	/**
	 * OBJECT CONSTRUCTOR
	 * @param $a_title string the title of this part
	 */
	function __construct($a_title, $a_postvar = "")
	{
		$this->setTitle($a_title);
	}

	/**
	 * Add a form property to the end of the list of content
	 * @param $a_form_property
	 */
	public function addFormProperty($a_form_property)
	{
		$this->content[] = $a_form_property;
	}


	/*
	 * GETTERS AND SETTERS
	 */

	/**
	 * @param string $a_title
	 */
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param array $a_content
	 */
	public function setContent($a_content)
	{
		$this->content = $a_content;
	}

	/**
	 * @return array
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

}