<?php
/**
 * Copyright (c) 2017 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';

/**
 * STACK Question server Table GUI
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id: 2.4$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionServerTableGUI extends ilTable2GUI
{
    /** @var ilassStackQuestionPlugin $plugin */
    var $plugin;


	/**
	 * Constructor
	 * @param   assStackQuestionDeployedSeedsGUI $a_parent_obj
	 * @param   string $a_parent_cmd
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();

		$this->plugin = $a_parent_obj->getPlugin();

		$this->setId('assStackQuestionServers');
		$this->setPrefix('assStackQuestionServers');
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setStyle('table', 'fullwidth');

        $this->addColumn('', '', '', true);
		$this->addColumn($this->lng->txt('active'));
		$this->addColumn($this->lng->txt('srv_purpose'));
        $this->addColumn($this->lng->txt('srv_address'));

		$this->setRowTemplate("tpl.il_as_qpl_xqcas_server_row.html", $this->plugin->getDirectory());

        $this->setEnableAllCommand(false);
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(true);
        $this->setExternalSegmentation(true);
	}


	/**
	 * @param $data
	 */
	public function fillRow($deployed_seed)
	{
		$this->tpl->setCurrentBlock('column');
		$this->tpl->setVariable('CONTENT', $deployed_seed['seed']);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('column');
		$this->tpl->setVariable('CONTENT', $deployed_seed['question_note']);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('column');
		$form = $this->getDeployedSeedViewForm($deployed_seed['form']);
		$this->tpl->setVariable('CONTENT', $form->getHTML());
		$this->tpl->parseCurrentBlock();
	}
}