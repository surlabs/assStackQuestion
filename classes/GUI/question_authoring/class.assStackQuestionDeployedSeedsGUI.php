<?php
/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';

/**
 * STACK Question deployed seeds authoring GUI class
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id: 1.6.2$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionDeployedSeedsGUI
{

	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private $plugin;

	/**
	 * @var ilTemplate for showing the deployed seeds panel
	 */
	private $template;

	/**
	 * @var mixed Array with the assStackQuestionDeployedSeed object of the current question.
	 */
	private $deployed_seeds;

	/**
	 * @var int
	 */
	private $question_id;

	/**
	 * Sets required data for deployed seeds management
	 * @param $plugin ilassStackQuestionPlugin instance
	 * @param $question_id int
	 */
	function __construct($plugin, $question_id)
	{
		//Set plugin and template objects
		$this->setPlugin($plugin);
		$this->setTemplate($this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_deployed_seeds_panel.html'));
		$this->setQuestionId($question_id);

		//Get deployed seeds for current question
		$this->setDeployedSeeds(assStackQuestionDeployedSeed::_read($this->getQuestionId()));
	}

	/**
	 * ### MAIN METHOD OF THIS CLASS ###
	 * @return HTML
	 */
	public function showDeployedSeedsPanel()
	{
		//Step #1: Checks if question has deployed seeds and gets the question note per each seed
		if (!$this->createDataToDisplay()) {
			return;
		}

		//Step #2: Fill the template
		$this->fillTemplate();

		//Step #3: Returns the html of the panel
		return $this->getTemplate()->get();
	}

	/**
	 * Checks if question has deployed seeds in order to determine what to show.
	 * @return bool
	 */
	private function createDataToDisplay()
	{
		if (assStackQuestionUtils::_isArrayEmpty($this->getDeployedSeeds())) {
			//Question hasn't deployed seeds
			//#Step 1.1: Check if question uses randomisation in order to determine if new seeds can be created or a message should be shown.
			$question_options = assStackQuestionOptions::_read($this->getQuestionId());
			if (!assStackQuestionUtils::_questionHasRandomVariables($question_options->getQuestionVariables())) {
				//Question doesn't use randomisation, show an information message
				ilUtil::sendInfo($this->getPlugin()->txt('dsm_question_doesnt_use_randomisation'));
				return FALSE;
			} else {
				//Continue to show form for creation of seeds
				return TRUE;
			}
		} else {
			//Question has deployed seeds, continue with process
			$this->getQuestionNotesForSeeds();
			return TRUE;
		}
	}

	private function getQuestionNotesForSeeds()
	{
		//Create ILIAS options objects and raws
		$ilias_options = assStackQuestionOptions::_read($this->getQuestionId());
		$question_variables_raw = $ilias_options->getQuestionVariables();
		$question_note_raw = $ilias_options->getQuestionNote();
		//Create STACK question
		$this->getPlugin()->includeClass('model/class.assStackQuestionStackQuestion.php');
		$stack_question = new assStackQuestionStackQuestion();
		$stack_question->createOptions($ilias_options);

		//Get question note for each different seed
		foreach ($this->getDeployedSeeds() as $deployed_seed) {
			$deployed_seed->setQuestionNote($stack_question->getQuestionNoteForSeed($deployed_seed->getSeed(), $question_variables_raw, $question_note_raw, $this->getQuestionId()));
		}

		//Avoid duplicates bugr 16727#
		$valid_seeds = array();
		$number_of_valid_seeds = 0;
		foreach ($this->getDeployedSeeds() as $deployed_seed) {
			$q_note = $deployed_seed->getQuestionNote();
			$include = TRUE;

			if (sizeof($valid_seeds)) {
				foreach ($valid_seeds as $valid_seed) {
					if ($valid_seed->getQuestionNote() == $q_note) {
						$deployed_seed->delete();
						$include = FALSE;
						break;
					}
				}
			}

			if($include){
				$number_of_valid_seeds++;
				$valid_seeds[] = $deployed_seed;
			}
		}

		$this->setDeployedSeeds($valid_seeds);
	}

	private function fillTemplate()
	{
		global $ilCtrl;

		//Step #1: Fill deployed seeds part
		$this->getTemplate()->setVariable('DEPLOYED_SEEDS_TABLE_SUBTITLE', $this->getPlugin()->txt('dsm_subtitle'));
		$this->getTemplate()->setVariable('DEPLOYED_SEEDS_TABLE_TITLE', $this->getPlugin()->txt('dsm_deployed_seeds'));

		//Fill Headers
		$this->getTemplate()->setVariable('DEPLOYED_SEEDS_HEADER', $this->getPlugin()->txt('dsm_deployed_seeds_header'));
		$this->getTemplate()->setVariable('QUESTION_NOTES_HEADER', $this->getPlugin()->txt('dsm_question_notes_header'));
		$this->getTemplate()->setVariable('VIEW_FORM_HEADER', $this->getPlugin()->txt('dsm_view_form_header'));

		if (!assStackQuestionUtils::_isArrayEmpty($this->getDeployedSeeds())) {
			$this->fillDeployedSeedsPart();
		} else {
			$this->getTemplate()->setCurrentBlock('deployed_seeds_overview');
			$this->getTemplate()->setVariable('DEPLOYED_SEED', '');
			$this->getTemplate()->setVariable('QUESTION_NOTE', $this->getPlugin()->txt('dsm_no_deployed_seeds'));
			$this->getTemplate()->setVariable('VIEW_FORM', '');
			$this->getTemplate()->ParseCurrentBlock();
		}

		//Step #2: Fill form part
		$this->getTemplate()->setVariable('NEW_DEPLOYED_SEED_FORM', $this->getDeployedSeedCreationForm()->getHTML());

	}

	private function fillDeployedSeedsPart()
	{
		//Fill deployed seeds
		foreach ($this->getDeployedSeeds() as $deployed_seed) {
			$this->getTemplate()->setCurrentBlock('deployed_seeds_overview');
			$this->getTemplate()->setVariable('DEPLOYED_SEED', $deployed_seed->getSeed());
			$this->getTemplate()->setVariable('QUESTION_NOTE', assStackQuestionUtils::_getLatex(assStackQuestionUtils::_solveKeyBracketsBug($deployed_seed->getQuestionNote())));
			$this->getTemplate()->setVariable('VIEW_FORM', $this->getDeployedSeedViewForm($deployed_seed->getSeed())->getHTML());
			$this->getTemplate()->ParseCurrentBlock();
		}
	}

	private function getDeployedSeedCreationForm()
	{
		global $ilCtrl;

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormActionByClass('assStackQuestionGUI'));
		$form->setTitle($this->getPlugin()->txt("dsm_new_deployed_seed_form"));

		//Input field
		$random_seed = mt_rand(1000000000, 9000000000);
		$new_seed = new ilNumberInputGUI($this->getPlugin()->txt("dsm_new_deployed_seed_form_input"), 'deployed_seed');
		$new_seed->setValue($random_seed);
		$form->addItem($new_seed);

		$question_id = new ilHiddenInputGUI('question_id');
		$question_id->setValue($this->getQuestionId());
		$form->addItem($question_id);

		$form->addCommandButton("createNewDeployedSeed", $this->getPlugin()->txt("dsm_new_deployed_seed_form_button"));
		$form->setShowTopButtons(FALSE);

		return $form;
	}

	private function getDeployedSeedViewForm($seed)
	{
		global $ilCtrl;

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormActionByClass('assStackQuestionGUI'));

		$delete_seed = new ilHiddenInputGUI('deployed_seed');
		$delete_seed->setValue($seed);
		$form->addItem($delete_seed);

		$fixed_seed = new ilHiddenInputGUI('fixed_seed');
		$fixed_seed->setValue($seed);
		$form->addItem($fixed_seed);

		$question_id = new ilHiddenInputGUI('question_id');
		$question_id->setValue($this->getQuestionId());
		$form->addItem($question_id);

		$ilCtrl->setParameterByClass("ilAssQuestionPageGUI", "fixed_seed", $seed);

		$ftpl = new ilTemplate("tpl.external_settings.html", true, true, "Services/Administration");

		$ftpl->setCurrentBlock("edit_bl");
		$ftpl->setVariable("URL_EDIT", $ilCtrl->getLinkTargetByClass("ilassquestionpagegui", "preview"));
		$ftpl->setVariable("TXT_EDIT", $this->getPlugin()->txt("dsm_fix_deployed_seed_form_button"));
		$ftpl->parseCurrentBlock();

		$ext = new ilCustomInputGUI($this->getPlugin()->txt("dsm_fix_deployed_seed_form_button_text"));
		$ext->setHtml($ftpl->get());
		$form->addItem($ext);

		$form->addCommandButton("deleteDeployedSeed", $this->getPlugin()->txt("dsm_delete_deployed_seed_form_button"));

		$form->setShowTopButtons(FALSE);

		return $form;
	}

	/*
	 * GETTERS AND SETTERS
	 */

	/**
	 * @param \ilassStackQuestionPlugin $plugin
	 */
	private function setPlugin($plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * @return \ilassStackQuestionPlugin
	 */
	private function getPlugin()
	{
		return $this->plugin;
	}

	/**
	 * @param ilTemplate $template
	 */
	private function setTemplate(ilTemplate $template)
	{
		$this->template = $template;
	}

	/**
	 * @return ilTemplate
	 */
	private function getTemplate()
	{
		return $this->template;
	}

	/**
	 * @param mixed $deployed_seeds
	 */
	private function setDeployedSeeds($deployed_seeds)
	{
		$this->deployed_seeds = $deployed_seeds;
	}

	/**
	 * @return mixed
	 */
	private function getDeployedSeeds()
	{
		return $this->deployed_seeds;
	}

	/**
	 * @param int $question_id
	 */
	private function setQuestionId($question_id)
	{
		$this->question_id = $question_id;
	}

	/**
	 * @return int
	 */
	private function getQuestionId()
	{
		return $this->question_id;
	}


}