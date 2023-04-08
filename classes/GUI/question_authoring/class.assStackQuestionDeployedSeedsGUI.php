<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';

/**
 * STACK Question deployed seeds authoring GUI class
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 *
 * @ilCtrl_isCalledBy assStackQuestionDeployedSeedsGUI: ilObjQuestionPoolGUI
 */
class assStackQuestionDeployedSeedsGUI
{

	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private ilassStackQuestionPlugin $plugin;

	/**
	 * @var ilTemplate for showing the deployed seeds panel
	 */
	private ilTemplate $template;

	/**
	 * @var array Array with the assStackQuestionDeployedSeed object of the current question.
	 */
	private array $deployed_seeds;

	/**
	 * @var int
	 */
	private int $question_id;

	/**
	 * @var assStackQuestionGUI Parent GUI Class
	 */
	private assStackQuestionGUI $parent_obj;

	/**
	 * Sets required data for deployed seeds management
	 * @param $plugin ilassStackQuestionPlugin instance
	 * @param $question_id int
	 * @param assStackQuestionGUI $parent_obj
	 */
	function __construct(ilassStackQuestionPlugin $plugin, int $question_id, assStackQuestionGUI $parent_obj)
	{
		//Set plugin and template objects
		$this->setPlugin($plugin);
		$this->setTemplate($this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_deployed_seeds_panel.html'));
		$this->setQuestionId($question_id);

		//Get deployed seeds for current question
		$this->getPlugin()->includeClass('class.assStackQuestionDB.php');
		$variants = assStackQuestionDB::_readDeployedVariants($this->getQuestionId());

		$this->setDeployedSeeds($variants);
		$this->setParentObj($parent_obj);
	}

	/**
	 * ### MAIN METHOD OF THIS CLASS ###
	 * @return HTML
	 */
	public function showDeployedSeedsPanel()
	{
		require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/GUI/tables/class.assStackQuestionSeedsTableGUI.php';
		$seeds_table = new assStackQuestionSeedsTableGUI($this->getParentObj(), "deployedSeedsManagement");

		$this->getQuestionNotesForSeeds();
		$seeds_table->prepareData($this->getDeployedSeeds());

		return $this->getDeployedSeedCreationForm()->getHTML() . $seeds_table->getHTML();
	}


	private function getQuestionNotesForSeeds()
	{
		$valid_seeds = array();
		$number_of_valid_seeds = 0;
		//Get question note for each different seed
		foreach ($this->getDeployedSeeds() as $id => $deployed_seed) {
			$this->getParentObj()->object->questionInitialisation($deployed_seed, true, true);
			$question_note_instantiated = $this->getParentObj()->object->getQuestionNoteInstantiated();
			$number_of_valid_seeds++;
			$valid_seeds[$id] = array('seed' => $deployed_seed, 'note' => $question_note_instantiated,'question_id' => $this->getParentObj()->object->getId());
		}

		$this->setDeployedSeeds($valid_seeds);
	}

	private function getDeployedSeedCreationForm()
	{
		global $DIC;

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$ctrl = $DIC->ctrl();
		$form->setFormAction($ctrl->getFormActionByClass('assStackQuestionGUI'));
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



	/*
	 * GETTERS AND SETTERS
	 */

	/**
	 * @param ilassStackQuestionPlugin $plugin
	 */
	protected function setPlugin(ilassStackQuestionPlugin $plugin)
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
	 * @param ilTemplate $template
	 */
	private function setTemplate(ilTemplate $template)
	{
		$this->template = $template;
	}

	/**
	 * @return assStackQuestionGUI
	 */
	public function getParentObj(): assStackQuestionGUI
	{
		return $this->parent_obj;
	}

	/**
	 * @param assStackQuestionGUI $parent_obj
	 */
	public function setParentObj(assStackQuestionGUI $parent_obj): void
	{
		$this->parent_obj = $parent_obj;
	}

	/**
	 * @return ilTemplate
	 */
	private function getTemplate(): ilTemplate
	{
		return $this->template;
	}

	/**
	 * @param array $deployed_seeds
	 */
	private function setDeployedSeeds(array $deployed_seeds)
	{
		$this->deployed_seeds = $deployed_seeds;
	}

	/**
	 * @return array
	 */
	public function getDeployedSeeds(): array
	{
		return $this->deployed_seeds;
	}

	/**
	 * @param int $question_id
	 */
	private function setQuestionId(int $question_id)
	{
		$this->question_id = $question_id;
	}

	/**
	 * @return int
	 */
	private function getQuestionId(): int
	{
		return $this->question_id;
	}


}