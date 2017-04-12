<?php

/**
 * Copyright (c) 2014 Institut fÃ¼r Lern-Innovation, Friedrich-Alexander-UniversitÃ¤t Erlangen-NÃ¼rnberg
 * GPLv2, see LICENSE
 */

/**
 * STACK Question plugin config class
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id 1.6$
 *
 */
class assStackQuestionConfig
{

	public function __construct($plugin_object = "")
	{
		$this->plugin_object = $plugin_object;
	}

	/*
	 * GET SETTINGS FROM DATABASE
	 */

	/**
	 * This class can be called from anywhere to get configuration
	 * @param $selector // a string for select  the type of settings needed
	 * @return array // of selected settings
	 */
	public static function _getStoredSettings($selector)
	{
		global $DIC;
		$db = $DIC->database();
		$settings = array();
		if ($selector == 'all') {
			$query = 'SELECT * FROM xqcas_configuration';
		} else {
			$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "' . $selector . '"';
		}
		$result = $db->query($query);
		while ($row = $db->fetchAssoc($result)) {
			$settings[$row['parameter_name']] = $row['value'];
		}

		return $settings;
	}

	/*
	 * SAVE SETTINGS TO DATABASE
	*/

	/**
	 * Saves new connection to maxima settings to the DB
	 */
	public function saveConnectionSettings()
	{
		//Old settings

		$saved_connection_data = self::_getStoredSettings('connection');
		//New settings
		$new_connection_data = $this->getAdminInput();

		/*
		 * IF AUTOMATIC DETECTION OF PLATFORM
		 * USE THIS
		 *

		$uname = strtolower(php_uname());
		if (strpos($uname, "darwin") !== false) {
			$new_connection_data['platform_type'] = 'unix';
		} else if (strpos($uname, "win") !== false) {
			$new_connection_data['platform_type'] = 'win';
		} else if (strpos($uname, "linux") !== false) {
			$new_connection_data['platform_type'] = 'unix';
		} else {
			$new_connection_data['platform_type'] = 'unix';
		}
		*/

		//Checkboxes workaround
		if (!array_key_exists('cas_debugging', $new_connection_data)) {
			$new_connection_data['cas_debugging'] = 0;
		}

		//Save to DB
		foreach ($saved_connection_data as $paremeter_name => $saved_value) {
			if (array_key_exists($paremeter_name, $new_connection_data) AND $saved_connection_data[$paremeter_name] != $new_connection_data[$paremeter_name]) {
				$this->saveToDB($paremeter_name, $new_connection_data[$paremeter_name], 'connection');
			}
		}

		//Create new maximalocal file
		require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/cas/installhelper.class.php');
		stack_cas_configuration::create_maximalocal();

		return TRUE;
	}

	/**
	 * Saves new Maths display settings to the DB
	 */
	public function saveDisplaySettings()
	{
		//Old settings
		$saved_display_data = self::_getStoredSettings('display');
		//New settings
		$new_display_data = $this->getAdminInput();

		//Checkboxes workaround
		if (!array_key_exists('instant_validation', $new_display_data)) {
			$new_display_data['instant_validation'] = 0;
		}
		if (!array_key_exists('replace_dollars', $new_display_data)) {
			$new_display_data['replace_dollars'] = 0;
		}

		//Save to DB
		foreach ($saved_display_data as $paremeter_name => $saved_value) {
			if (array_key_exists($paremeter_name, $new_display_data) AND $saved_display_data[$paremeter_name] != $new_display_data[$paremeter_name]) {
				$this->saveToDB($paremeter_name, $new_display_data[$paremeter_name], 'display');
			}
		}
		return TRUE;
	}

	/**
	 * Saves new default options settings to the DB
	 */
	public function saveDefaultOptionsSettings()
	{
		//Old settings
		$saved_options_data = self::_getStoredSettings('options');
		//New settings
		$new_options_data = $this->getAdminInput();

		//Checkboxes workaround
		if (!array_key_exists('options_question_simplify', $new_options_data)) {
			$new_options_data['options_question_simplify'] = 0;
		}
		if (!array_key_exists('options_assume_positive', $new_options_data)) {
			$new_options_data['options_assume_positive'] = 0;
		}
		if (!array_key_exists('options_sqrt_sign', $new_options_data)) {
			$new_options_data['options_sqrt_sign'] = 0;
		}

		//Save to DB
		foreach ($saved_options_data as $paremeter_name => $saved_value) {
			if (array_key_exists($paremeter_name, $new_options_data) AND $saved_options_data[$paremeter_name] != $new_options_data[$paremeter_name]) {
				$this->saveToDB($paremeter_name, $new_options_data[$paremeter_name], 'options');
			}
		}
		return TRUE;
	}

	/**
	 * Saves new default inputs settings to the DB
	 */
	public function saveDefaultInputsSettings()
	{
		//Old settings
		$saved_inputs_data = self::_getStoredSettings('inputs');
		//New settings
		$new_inputs_data = $this->getAdminInput();

		//Checkboxes workaround
		if (!array_key_exists('input_strict_syntax', $new_inputs_data)) {
			$new_inputs_data['input_strict_syntax'] = 0;
		}
		if (!array_key_exists('input_insert_stars', $new_inputs_data)) {
			$new_inputs_data['input_insert_stars'] = 0;
		}
		if (!array_key_exists('input_forbid_float', $new_inputs_data)) {
			$new_inputs_data['input_forbid_float'] = 0;
		}
		if (!array_key_exists('input_require_lowest_terms', $new_inputs_data)) {
			$new_inputs_data['input_require_lowest_terms'] = 0;
		}
		if (!array_key_exists('input_check_answer_type', $new_inputs_data)) {
			$new_inputs_data['input_check_answer_type'] = 0;
		}
		if (!array_key_exists('input_must_verify', $new_inputs_data)) {
			$new_inputs_data['input_must_verify'] = 0;
		}
		if (!array_key_exists('input_show_validation', $new_inputs_data)) {
			$new_inputs_data['input_show_validation'] = 0;
		}

		//Save to DB
		foreach ($saved_inputs_data as $paremeter_name => $saved_value) {
			if (array_key_exists($paremeter_name, $new_inputs_data) AND $saved_inputs_data[$paremeter_name] != $new_inputs_data[$paremeter_name]) {
				$this->saveToDB($paremeter_name, $new_inputs_data[$paremeter_name], 'inputs');
			}
		}
		return TRUE;
	}

	/**
	 * @param $parameter_name //Is the of the parameter to modify (this is the Primary Key in DB)
	 * @param $value //Is the value of the parameter
	 * @param $group_name //Is the selector for different categories of data
	 */
	private function saveToDB($parameter_name, $value, $group_name)
	{
		global $DIC;
		$db = $DIC->database();
		$db->replace('xqcas_configuration',
			array(
				'parameter_name' => array('text', $parameter_name)
			),
			array(
				'value' => array('clob', $value),
				'group_name' => array('text', $group_name),
			)
		);
	}

	/*
	 * GET DATA FROM POST
	 */

	/**
	 * @return array|mixed|string //The data sent by post
	 */
	public function getAdminInput()
	{
		$data = ilUtil::stripSlashesRecursive($_POST);
		//Clean array
		unset($data['cmd']);
		return $data;
	}

	/*
	 * SET DEFAULT CONFIGURATION
	 */

	/**
	 * Sets connection configuration to default values.
	 */
	public function setDefaultSettingsForConnection()
	{
		//Default values for connection
		$connection_default_values = array(
			'platform_type' => 'unix',
			'maxima_version' => '5.31.2',
			'cas_connection_timeout' => '5',
			'cas_result_caching' => 'db',
			'maxima_command' => '',
			'plot_command' => '',
			'cas_debugging' => '0'
		);
		foreach ($connection_default_values as $paremeter_name => $value) {
			$this->saveToDB($paremeter_name, $value, 'connection');
		}

		//Create new maximalocal file
		require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionInitialization.php');
		require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/stack/cas/installhelper.class.php');
		stack_cas_configuration::create_maximalocal();

		return TRUE;
	}

	/**
	 * Sets display configuration to default values.
	 */
	public function setDefaultSettingsForDisplay()
	{
		//Default values for display
		$display_default_values = array(
			'instant_validation' => '0',
			'maths_filter' => 'mathjax',
			'replace_dollars' => '1'
		);
		foreach ($display_default_values as $paremeter_name => $value) {
			$this->saveToDB($paremeter_name, $value, 'display');
		}
		return TRUE;
	}

	/**
	 * Sets default options configuration to default values.
	 */
	public function setDefaultSettingsForOptions()
	{
		//Default values for options
		$options_default_values = array(
			'options_question_simplify' => '1',
			'options_assume_positive' => '0',
			'options_prt_correct' => $this->plugin_object->txt('default_prt_correct_message'),
			'options_prt_partially_correct' => $this->plugin_object->txt('default_prt_partially_correct_message'),
			'options_prt_incorrect' => $this->plugin_object->txt('default_prt_incorrect_message'),
			'options_multiplication_sign' => 'dot',
			'options_sqrt_sign' => '1',
			'options_complex_numbers' => 'i',
			'options_inverse_trigonometric' => 'cos-1'
		);
		foreach ($options_default_values as $paremeter_name => $value) {
			$this->saveToDB($paremeter_name, $value, 'options');
		}
		return TRUE;
	}

	/**
	 * Sets default inputs configuration to default values.
	 */
	public function setDefaultSettingsForInputs()
	{
		//Default values for inputs
		$inputs_default_values = array(
			'input_type' => 'algebraic',
			'input_box_size' => '15',
			'input_strict_syntax' => '1',
			'input_insert_stars' => '0',
			'input_forbidden_words' => '',
			'input_forbid_float' => '1',
			'input_require_lowest_terms' => '0',
			'input_check_answer_type' => '0',
			'input_must_verify' => '1',
			'input_show_validation' => '1'
		);
		//Is not the first time, replace current values by default values
		foreach ($inputs_default_values as $paremeter_name => $value) {
			$this->saveToDB($paremeter_name, $value, 'inputs');
		}
		return TRUE;
	}
}