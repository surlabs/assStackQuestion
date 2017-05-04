<?php
/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionStackFactory.php';

/**
 * STACK Question Healthcheck
 * This class checks that all parameters are properly set for running a STACK Question
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version    $Id: 2.3$$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionHealthcheck
{

	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private $plugin;

	/**
	 * @var mixed configuration settings stored in DB
	 */
	private $config;

	/**
	 * @var assStackQuestionStackFactory the clas for create stack objects
	 */
	private $stack_factory;

	/**
	 * @var mixed The current status of the maxima connection
	 */
	private $maxima_connection_status;


	function __construct(ilassStackQuestionPlugin $plugin)
	{
		//Set plugin object
		$this->setPlugin($plugin);

		//Set configuration settings from DB
		$this->setConfig(assStackQuestionConfig::_getStoredSettings('all'));

		//Create STACK factory
		$this->setStackFactory(new assStackQuestionStackFactory());
	}

	public function doHealthcheck($a_mode = 'reduced')
	{
		global $tpl;
		//Include all classes needed
		$this->getPlugin()->includeClass('utils/class.assStackQuestionInitialization.php');
		$this->getPlugin()->includeClass('../exceptions/class.assStackQuestionException.php');

		$this->checkMaximaConnection();

		//Add MathJax (Ensure MathJax is loaded)
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$mathJaxSetting = new ilSetting("MathJax");
		$tpl->addJavaScript($mathJaxSetting->get("path_to_mathjax"));
		//Ad CSS to Templates
		$tpl->addCss($this->getPlugin()->getStyleSheetLocation('css/qpl_xqcas_healthcheck.css'));

		return $this->getMaximaConnectionStatus();
	}

	public function checkMaximaConnection()
	{
		global $CFG;
		$this->getPlugin()->includeClass('stack/cas/installhelper.class.php');

		//Platform settings
		$stored_platform_type = $this->getConfig('platform_type');

		//Get maxima bat location
		$bat_location = $this->getMaximaBatLocation($stored_platform_type);
		if (is_array($bat_location)) {
			//ERROR
			$error_message = '';
			foreach ($bat_location as $location) {
				$error_message .= $location . '</br>';
			}
			$this->setMaximaConnectionStatus($error_message, 'error_bat_location');
		} else {
			//SUCCESS
			$this->setMaximaConnectionStatus($bat_location, 'bat_location');
		}

		//Create maximalocal file
		if ($this->getMaximaConnectionStatus('bat_location')) {
			stack_cas_configuration::create_maximalocal();
			//Get maximalocal location
			$this->setMaximaConnectionStatus(stack_cas_configuration::maximalocal_location(), 'maximalocal_location');
		}

		if (is_file($this->getMaximaConnectionStatus('maximalocal_location'))) {
			$this->setMaximaConnectionStatus(stack_cas_configuration::generate_maximalocal_contents(), 'maximalocal_contents');
		} else {
			$this->setMaximaConnectionStatus(stack_cas_configuration::maximalocal_location(), 'error_maximalocal_contents');
		}

		//Show status of connection

		//Create sample command
		$this->setMaximaConnectionStatus('The derivative of @ x^4/(1+x^4) @ is \[ \frac{d}{dx} \frac{x^4}{1+x^4} = @ diff(x^4/(1+x^4),x) @. \]', 'sample_command');
		$sample_CAS_text = $this->showCASText($this->getMaximaConnectionStatus('sample_command'));
		$this->setMaximaConnectionStatus($sample_CAS_text['display'], 'cas_sample_display');
		$this->setMaximaConnectionStatus($sample_CAS_text['errors'], 'error_cas_sample_display');
		$this->setMaximaConnectionStatus($sample_CAS_text['debug'], 'debug_cas_sample_display');

		//Check connection status
		$genuine_connect = stack_connection_helper::stackmaxima_genuine_connect();
		$this->setMaximaConnectionStatus($genuine_connect[1], 'connection_status_display');
		$this->setMaximaConnectionStatus($genuine_connect[0], 'error_connection_status_display');

		//Check Maxima version
		/*TODO. not completed
		 * $maxima_version = stack_connection_helper::stackmaxima_version_healthcheck();
		if ($maxima_version[0] == 'healthchecksstackmaximaversionok') {
			$this->setMaximaConnectionStatus($maxima_version, 'maxima_version');
		} elseif ($maxima_version[0] == 'healthchecksstackmaximaversionmismatch') {
			$this->setMaximaConnectionStatus($maxima_version[1]['fix'], 'error_mismatch_maxima_version');
		}
		 */

	}

	public function getMaximaBatLocation($stored_platform_type)
	{
		global $CFG;

		switch ($stored_platform_type) {
			case 'win':
				$locations = array();
				$locations[] = 'C:/Program Files/Maxima/bin/maxima.bat';
				$locations[] = 'C:/Program Files (x86)/Maxima/bin/maxima.bat';
				$locations[] = 'C:/Maxima/bin/maxima.bat';
				foreach ($locations as $location) {
					if (file_exists($location)) {
						return $location;
					}
				}
				return $locations;
				break;
			case 'unix':
			case 'unix-optimised':
				if (file_exists($CFG->dataroot . '/stack/maxima.bat')) {
					return $CFG->dataroot . '/stack/maxima.bat';
				} else {
					return array($CFG->dataroot . '/stack/maxima.bat');
				}
				break;
			case 'server':
				if (file_exists($CFG->dataroot . '/stack/maxima.bat')) {
					return $CFG->dataroot . '/stack/maxima.bat';
				} else {
					return array($CFG->dataroot . '/stack/maxima.bat');
				}
				break;
			default:
				throw new stack_exception('error_platform_type');
				break;
		}
	}

	public function showCASText($cas_text)
	{
		$this->getPlugin()->includeClass('stack/mathsoutput/mathsoutput.class.php');
		$CAS_text_content = array();
		//Create CAS text
		$ct = $this->getStackFactory()->get('cas_text', array('raw' => $cas_text));
		//Set content to array
		$CAS_text_content['display'] = stack_maths::process_display_castext($ct["text"]);
		$CAS_text_content['errors'] = $ct["errors"];
		$CAS_text_content['debug_info'] = $ct["debug"];

		return $CAS_text_content;
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
	 * @param mixed $config
	 */
	public function setConfig($config)
	{
		$this->config = $config;
	}

	/**
	 * @return mixed
	 */
	public function getConfig($selector = '')
	{
		if ($selector) {
			return $this->config[$selector];
		} else {
			return $this->config;
		}
	}

	/**
	 * @param \assStackQuestionStackFactory $stack_factory
	 */
	public function setStackFactory($stack_factory)
	{
		$this->stack_factory = $stack_factory;
	}

	/**
	 * @return \assStackQuestionStackFactory
	 */
	public function getStackFactory()
	{
		return $this->stack_factory;
	}

	/**
	 * @param mixed $maxima_connection_status
	 */
	public function setMaximaConnectionStatus($maxima_connection_status, $selector = '')
	{
		if ($selector) {
			$this->maxima_connection_status[$selector] = $maxima_connection_status;
		} else {
			$this->maxima_connection_status = $maxima_connection_status;
		}
	}

	/**
	 * @return mixed
	 */
	public function getMaximaConnectionStatus($selector = '')
	{
		if ($selector) {
			return $this->maxima_connection_status[$selector];
		} else {
			return $this->maxima_connection_status;
		}
	}

	public function clearCache()
	{
		global $DIC;
		$db = $DIC->database();
		$query = "TRUNCATE table xqcas_cas_cache";
		$db->manipulate($query);

		return TRUE;
	}


}