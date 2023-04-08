<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

/**
 * STACK Question scoring GUI class
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionScoringGUI
{

	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private ilassStackQuestionPlugin $plugin;

	/**
	 * @var ilTemplate for showing the scoring panel
	 */
	private ilTemplate $template;

	/**
	 * @var assStackQuestion Question.
	 */
	private assStackQuestion $question;

    /**
     * @var ?float Question points.
     */
    private ?float $question_points;

    /**
     * @var stack_potentialresponse_tree[] Question PRTs.
     */
    private array $potentialresponse_trees;

    /**
     * @param ilassStackQuestionPlugin $plugin
     * @param assStackQuestion $question
     * @param $question_points
     */
    public function __construct(ilassStackQuestionPlugin $plugin, assStackQuestion $question, $question_points)
    {
        $this->setPlugin($plugin);
        $this->setQuestion($question);
        $this->setTemplate($this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_scoring_panel.html'));
        $this->setQuestionPoints($question_points);
        $this->setPotentialresponseTrees($question->prts);
    }

	/**
	 * ### MAIN METHOD OF THIS CLASS ###
	 * Creates and returns the scoring panel
	 * @return string
	 */
	public function showScoringPanel($new_question_points = ''): string
    {
		//Step #1: Get points per PRT and set the strcuture as PRT
		$this->setPotentialresponseTrees($this->reScalePotentialresponseTrees($this->getQuestion()->getPoints()));
		//Step #2: Fill form and general data in the scoring template
		$this->fillGeneralData($new_question_points);
		//Step #3: Fill specific PRT data
		$this->fillPRTspecific('current');
		//Step #4: Fill specific PRT data when comparison is required
		if (is_float($new_question_points)) {
			//Set new points and get the new structure for comparison
			$this->setQuestionPoints($new_question_points);
			$this->setPotentialresponseTrees($this->reScalePotentialresponseTrees($this->getQuestionPoints()));
			$this->fillPRTspecific('new');
		}
		//Step #5: Return HTML
		return $this->getTemplate()->get();
	}

    public function reScalePotentialresponseTrees($question_points)
    {
        //Set variables
        $this->setQuestionPoints($question_points);
        $max_weight = 0.0;
        $structure = array();

        //Get max weight of$prt the PRT
        foreach ($this->getPotentialresponseTrees() as $prt_name => $prt) {
            $max_weight += $prt->get_value();
        }

        //fill the structure
        foreach ($this->getPotentialresponseTrees() as $prt_name => $prt) {
            $prt_max_weight = $prt->get_value();
            $prt_max_points = ($prt_max_weight / $max_weight) * $this->getQuestionPoints();
            $structure[$prt_name]['max_points'] = $prt_max_points;
            foreach ($prt->get_nodes_summary() as $node_name => $node) {
                $structure[$prt_name][$node_name]['true_mode'] = $node->truescoremode;
                $structure[$prt_name][$node_name]['true_value'] = ($node->truescore * $prt_max_points);
                $structure[$prt_name][$node_name]['false_mode'] = $node->falsescoremode;
                $structure[$prt_name][$node_name]['false_value'] = ($node->falsescore * $prt_max_points);
            }
        }

        return $structure;
    }

	/*
	 * FILL TEMPLATE METHODS
	 */

	/**
	 * Fill general data as the title and the points form.
	 * @param float $new_question_points
	 */
	private function fillGeneralData($new_question_points = '')
	{
		//Fill Title
		$this->getTemplate()->setVariable('SCORING_TABLE_TITLE', $this->getPlugin()->txt('sco_scoring'));
		$this->getTemplate()->setVariable('SCORING_TABLE_SUBTITLE', $this->getPlugin()->txt('sco_subtitle'));
		//Fill Forms
		$this->getTemplate()->setVariable('SCORING_FORM', $this->getScoringCreationForm($new_question_points)->getHTML());
	}

	/**
	 * Points management form creation
	 * @param float $new_question_points_value
	 * @return ilPropertyFormGUI
	 */
	private function getScoringCreationForm($new_question_points_value = '')
	{
		global $DIC;

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$ctrl = $DIC->ctrl();
		$form->setFormAction($ctrl->getFormActionByClass('assStackQuestionGUI'));
		$form->setTitle($this->getPlugin()->txt("sco_scoring_form"));

		//Current points field
		$current_question_points = new ilNonEditableValueGUI($this->getPlugin()->txt("sco_current_scoring_form_input"), 'current_scoring');
		$current_question_points->setValue($this->getQuestionPoints());
		$current_question_points->setInfo($this->getPlugin()->txt("sco_current_scoring_info"));
		$form->addItem($current_question_points);

		//New points field
		$new_question_points = new ilTextInputGUI($this->getPlugin()->txt("sco_new_scoring_form_input"), 'new_scoring');
		$new_question_points->setValue($new_question_points_value);
		$new_question_points->setInfo($this->getPlugin()->txt("sco_new_scoring_info"));
		$form->addItem($new_question_points);

		//2.3.9 Show info about behaviour of this scoring page
		$scoring_info = new ilNonEditableValueGUI("", "scoring_info");
		$scoring_info->setValue($this->getPlugin()->txt("sco_info"));
		$form->addItem($scoring_info);

		//This command is used when the user want to show a comparison but no to set the input as point value.
		$form->addCommandButton("showScoringComparison", $this->getPlugin()->txt("sco_show_new_scoring_form_button"));
		//This command is used when the wants to set the input value as point value.
		$form->addCommandButton("saveNewScoring", $this->getPlugin()->txt("sco_set_new_scoring_form_button"));
		$form->setShowTopButtons(FALSE);

		return $form;
	}

	/**
	 * Fill PRT part
	 * @param string $mode
	 */
	private function fillPRTspecific($mode)
	{
		if ($mode == 'current') {
			//Fill the current PRT info.
			foreach ($this->getPotentialresponseTrees() as $prt_name => $prt) {
				$this->getTemplate()->setCurrentBlock('prt_part');
				$this->getTemplate()->setVariable('PRT_NAME_MESSAGE', $this->getPlugin()->txt('sco_prt_name'));
				$this->getTemplate()->setVariable('PRT_NAME', $prt_name);
				$this->getTemplate()->setVariable('PRT_POINTS_MESSAGE', $this->getPlugin()->txt('sco_prt_value'));
				$this->getTemplate()->setVariable('PRT_POINTS', $prt['max_points']);
				unset($prt['max_points']);
				//Fill nodes
				foreach ($prt as $node_name => $node) {
					$this->getTemplate()->setCurrentBlock('node_part');
					$this->fillNodeSpecific($mode, $node_name, $node);
					$this->getTemplate()->ParseCurrentBlock();
				}
				$this->getTemplate()->setCurrentBlock('prt_part');
				$this->getTemplate()->ParseCurrentBlock();
			}
		} elseif ($mode == 'new') {
			//Fill the new PRT info in order to compare it with current one.
			foreach ($this->getPotentialresponseTrees() as $prt_name => $prt) {
				$this->getTemplate()->setCurrentBlock('n_prt_part');
				$this->getTemplate()->setVariable('N_PRT_NAME_MESSAGE', $this->getPlugin()->txt('sco_prt_name'));
				$this->getTemplate()->setVariable('N_PRT_NAME', $prt_name);
				$this->getTemplate()->setVariable('N_PRT_POINTS_MESSAGE', $this->getPlugin()->txt('sco_prt_value'));
				$this->getTemplate()->setVariable('N_PRT_POINTS', $prt['max_points']);
				unset($prt['max_points']);
				//Fill Nodes
				foreach ($prt as $node_name => $node) {
					$this->getTemplate()->setCurrentBlock('n_node_part');
					$this->fillNodeSpecific($mode, $node_name, $node);
					$this->getTemplate()->ParseCurrentBlock();
				}
				$this->getTemplate()->setCurrentBlock('n_prt_part');
				$this->getTemplate()->ParseCurrentBlock();
			}
		}
	}

	/**
	 * Fill node specific part
	 * @param string $mode
	 * @param string $node_name
	 * @param array $node
	 */
	private function fillNodeSpecific($mode, $node_name, $node)
	{
		if ($mode == 'current') {
			//Fill the current node info.
			$this->getTemplate()->setVariable('NODE_NAME_MESSAGE', $this->getPlugin()->txt('sco_node_name'));
			$this->getTemplate()->setVariable('NODE_NAME', $node_name);
			$this->getTemplate()->setVariable('TRUE_SCORING', $node['true_mode'] . $node['true_value']);
			$this->getTemplate()->setVariable('FALSE_SCORING', $node['false_mode'] . $node['false_value']);
		} elseif ($mode == 'new') {
			//Fill the new node info in order to compare it with current one.
			$this->getTemplate()->setVariable('N_NODE_NAME_MESSAGE', $this->getPlugin()->txt('sco_node_name'));
			$this->getTemplate()->setVariable('N_NODE_NAME', $node_name);
			$this->getTemplate()->setVariable('N_TRUE_SCORING', $node['true_mode'] . $node['true_value']);
			$this->getTemplate()->setVariable('N_FALSE_SCORING', $node['false_mode'] . $node['false_value']);
		}
	}

	/*
	 * GETTERS AND SETTERS
	 */


    /**
     * @return ilassStackQuestionPlugin
     */
    public function getPlugin(): ilassStackQuestionPlugin
    {
        return $this->plugin;
    }

    /**
     * @param ilassStackQuestionPlugin $plugin
     */
    public function setPlugin(ilassStackQuestionPlugin $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @return ilTemplate
     */
    public function getTemplate(): ilTemplate
    {
        return $this->template;
    }

    /**
     * @param ilTemplate $template
     */
    public function setTemplate(ilTemplate $template): void
    {
        $this->template = $template;
    }

    /**
     * @return assStackQuestion
     */
    public function getQuestion(): assStackQuestion
    {
        return $this->question;
    }

    /**
     * @param assStackQuestion $question
     */
    public function setQuestion(assStackQuestion $question): void
    {
        $this->question = $question;
    }

    /**
     * @return float|null
     */
    public function getQuestionPoints(): ?float
    {
        return $this->question_points;
    }

    /**
     * @param float|null $question_points
     */
    public function setQuestionPoints(?float $question_points): void
    {
        $this->question_points = $question_points;
    }

    /**
     * @return array
     */
    public function getPotentialresponseTrees(): array
    {
        return $this->potentialresponse_trees;
    }

    /**
     * @param array $potentialresponse_trees
     */
    public function setPotentialresponseTrees(array $potentialresponse_trees): void
    {
        $this->potentialresponse_trees = $potentialresponse_trees;
    }


} 