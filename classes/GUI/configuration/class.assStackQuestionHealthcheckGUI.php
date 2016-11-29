<?php
/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';

/**
 * STACK Question Healthcheck GUI
 * This class provides a GUI to the Healthcheck of the STACK Question plugin
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version    $Id: 2.3$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionHealthcheckGUI
{
	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private $plugin;

	/**
	 * @var ilTemplate for showing the preview
	 */
	private $template;

	/**
	 * @var array with the data from assStackQuestionHealthcheck
	 */
	private $healthcheck_data;

	/**
	 * Set all the data needed for call the getQuestionDisplayGUI() method.
	 * @param ilassStackQuestionPlugin $plugin
	 * @param array $healthcheck_data
	 */
	function __construct(ilassStackQuestionPlugin $plugin, $healthcheck_data)
	{
		global $tpl;
		//Set plugin object
		$this->setPlugin($plugin);

		//Add MathJax (Ensure MathJax is loaded)
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$tpl->addJavaScript($mathJaxSetting->get("path_to_mathjax"));

		$this->setHealthcheckData($healthcheck_data);
	}


	public function showHealthCheck($a_mode = 'reduced')
	{
		$connection_status_template = $this->getPlugin()->getTemplate('tpl.il_as_qpl_xqcas_healthcheck.html');

		if ($a_mode == 'expanded')
		{
			//EXPANDED HEALTHCHECK EXPLANATION
			$connection_status_template->setVariable('HEALTHCHECK_TITLE', $this->getPlugin()->txt('hc_connection_expanded_title'));
			$connection_status_template->setVariable('HEALTHCHECK_CONNECTION_EXPLANATION', $this->getPlugin()->txt('hc_connection_expanded_explanation'));
			$connection_status_template->setVariable('HC_EXPANDED', 'hc_expanded_shown');
		}
		else
		{
			//REDUCED HEALTHCHECK EXPLANATION
			$connection_status_template->setVariable('HEALTHCHECK_TITLE', $this->getPlugin()->txt('hc_connection_reduced_title'));
			$connection_status_template->setVariable('HEALTHCHECK_CONNECTION_EXPLANATION', $this->getPlugin()->txt('hc_connection_reduced_explanation'));
			$connection_status_template->setVariable('HC_EXPANDED', 'hc_expanded_hidden');
		}

		//CONNECTION STATUS AND SAMPLE COMMAND
		if (!$this->getHealthcheckData('error_cas_sample_display')) {
			//TITLE
			$connection_status_template->setVariable('CONNECTION_STATUS_TITLE', $this->getPlugin()->txt('hc_connection_status_title'));
			$connection_status_template->setVariable('CONNECTION_STATUS_TITLE_STATUS', $this->getPlugin()->txt('hc_passed'));
			$connection_status_template->setVariable('SAMPLE_COMMAND_TITLE', $this->getPlugin()->txt('hc_sample_command_title'));
			$connection_status_template->setVariable('SAMPLE_COMMAND_TITLE_STATUS', $this->getPlugin()->txt('hc_passed'));

			//Connection status
			$connection_status_template->setVariable('CONNECTION_STATUS_MESSAGE', $this->getPlugin()->txt('hc_connection_status_display'));
			$connection_status_template->setVariable('CONNECTION_STATUS', $this->getHealthcheckData('connection_status_display'));
			$connection_status_template->setVariable('CONNECTION_STATUS_COLOR', 'hc_color_passed');

			//Sample command
			$connection_status_template->setVariable('SAMPLE_COMMAND_MESSAGE_1', $this->getPlugin()->txt('hc_sample_command'));
			$connection_status_template->setVariable('SAMPLE_COMMAND_1', assStackQuestionUtils::_removeLaTeX($this->getHealthcheckData('sample_command')));
			$connection_status_template->setVariable('SAMPLE_COMMAND_2', assStackQuestionUtils::_solveKeyBracketsBug($this->getHealthcheckData('cas_sample_display')));

		} else {
			//TITLE
			$connection_status_template->setVariable('CONNECTION_STATUS_TITLE', $this->getPlugin()->txt('hc_connection_status_title'));
			$connection_status_template->setVariable('CONNECTION_STATUS_TITLE_STATUS', $this->getPlugin()->txt('hc_failed'));
			$connection_status_template->setVariable('SAMPLE_COMMAND_TITLE', $this->getPlugin()->txt('hc_sample_command_title'));
			$connection_status_template->setVariable('SAMPLE_COMMAND_TITLE_STATUS', $this->getPlugin()->txt('hc_failed'));

			$connection_status_template->setVariable('CONNECTION_STATUS_MESSAGE', $this->getPlugin()->txt('hc_connection_status_display_error'));
			$connection_status_template->setVariable('CONNECTION_STATUS', $this->getHealthcheckData('error_connection_status_display'));
			$connection_status_template->setVariable('SAMPLE_COMMAND_1', $this->getHealthcheckData('debug_cas_sample_display'));
			$connection_status_template->setVariable('CONNECTION_STATUS_COLOR', 'hc_color_failed');
		}

		//MAXIMA CONNECTION
		/*
		 * TODO: not completed
		if($this->getHealthcheckData('maxima_version')){

		}else{
			$connection_status_template->setVariable('MAXIMA_VERSION_TITLE', $this->getPlugin()->txt('hc_maxima_version_title'));
			$connection_status_template->setVariable('MAXIMA_VERSION_TITLE_STATUS', $this->getPlugin()->txt('hc_failed'));

			$connection_status_template->setVariable('MAXIMA_VERSION_MESSAGE', $this->getPlugin()->txt('hc_maxima_version_error'));
			$connection_status_template->setVariable('MAXIMA_VERSION', $this->getHealthcheckData('error_mismatch_maxima_version'));
			$connection_status_template->setVariable('MAXIMA_VERSION_COLOR', 'hc_color_failed');
		}
		*/

		return $connection_status_template;
	}


	/*
	 * GETTERS AND SETTERS
	 */

	/**
	 * @param array $healthcheck_data
	 */
	public function setHealthcheckData($healthcheck_data)
	{
		$this->healthcheck_data = $healthcheck_data;
	}

	/**
	 * @return array
	 */
	public function getHealthcheckData($selector = '')
	{
		if ($selector) {
			return $this->healthcheck_data[$selector];
		} else {
			return $this->healthcheck_data;
		}
	}

	/**
	 * @param \ilassStackQuestionPlugin $plugin
	 */
	public function setPlugin($plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * @return \ilassStackQuestionPlugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}

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