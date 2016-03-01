<?php

/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
require_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * STACK Question plugin config GUI
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id$
 *
 */
class ilassStackQuestionConfigGUI extends ilPluginConfigGUI
{

	/**
	 *
	 * @global type $ilCtrl
	 * @param type $cmd
	 */
	public function performCommand($cmd)
	{
		global $ilCtrl;

		//Set config object
		$this->plugin_object->includeClass("model/configuration/class.assStackQuestionConfig.php");
		$this->config = new assStackQuestionConfig($this->plugin_object);

		// control flow
		$cmd = $ilCtrl->getCmd($this, "configure");
		switch ($cmd) {
			case 'showOtherSettings':
			case 'showDisplaySettings':
			case 'showDefaultOptionsSettings':
			case 'showDefaultInputsSettings':
			case 'saveDisplaySettings':
			case 'saveDefaultInputsSettings':
			case 'saveDefaultOptionsSettings':
			case 'setDefaultSettingsForDisplay':
			case 'setDefaultSettingsForInputs':
			case 'setDefaultSettingsForOptions':
				$this->initTabs('others');
				$this->$cmd();
				break;

			default:
				$this->initTabs();
				$this->$cmd();
				break;
		}
	}

	/**
	 * @param string $a_mode
	 */
	public function initTabs($a_mode = "")
	{
		global $ilCtrl, $ilTabs;
		switch ($a_mode) {
			case 'others':
				$ilTabs->addTab("show_connection_settings", $this->plugin_object->txt('show_connection_settings'), $ilCtrl->getLinkTarget($this, 'showConnectionSettings'));
				$ilTabs->addTab("show_other_settings", $this->plugin_object->txt('show_other_settings'), $ilCtrl->getLinkTarget($this, 'showOtherSettings'));
				$ilTabs->addSubTab('show_display_settings', $this->plugin_object->txt('show_display_settings'), $ilCtrl->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showDisplaySettings'));
				$ilTabs->addSubTab('show_default_options_settings', $this->plugin_object->txt('show_default_options_settings'), $ilCtrl->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showDefaultOptionsSettings'));
				$ilTabs->addSubTab('show_default_inputs_settings', $this->plugin_object->txt('show_default_inputs_settings'), $ilCtrl->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showDefaultInputsSettings'));
				$ilTabs->addTab("show_healthcheck", $this->plugin_object->txt('show_healthcheck'), $ilCtrl->getLinkTarget($this, 'showHealthcheck'));
				break;
			default:
				$ilTabs->addTab("show_connection_settings", $this->plugin_object->txt('show_connection_settings'), $ilCtrl->getLinkTarget($this, 'showConnectionSettings'));
				$ilTabs->addTab("show_other_settings", $this->plugin_object->txt('show_other_settings'), $ilCtrl->getLinkTarget($this, 'showOtherSettings'));
				$ilTabs->addTab("show_healthcheck", $this->plugin_object->txt('show_healthcheck'), $ilCtrl->getLinkTarget($this, 'showHealthcheck'));
				break;
		}
	}

	/**
	 * Entry point for configuring the module
	 */
	function configure()
	{
		//By default show connection settings
		$this->showConnectionSettings();
	}

	/*
	 * SHOW SETTINGS CALLING METHODS
	 */

	public function showConnectionSettings()
	{
		global $tpl, $ilTabs;
		$ilTabs->setTabActive('show_connection_settings');

		$form = $this->getConnectionSettingsForm();
		$tpl->setContent($form->getHTML());
	}

	public function showOtherSettings()
	{
		global $tpl, $ilTabs, $ilCtrl;
		$ilTabs->setTabActive('show_other_settings');
		$ilTabs->setSubTabActive('show_display_settings');

		$this->showDisplaySettings();
	}

	public function showDisplaySettings()
	{
		global $tpl, $ilTabs;
		$ilTabs->setTabActive('show_other_settings');
		$ilTabs->setSubTabActive('show_display_settings');

		$form = $this->getDisplaySettingsForm();
		$tpl->setContent($form->getHTML());
	}

	public function showDefaultOptionsSettings()
	{
		global $tpl, $ilTabs;
		$ilTabs->setTabActive('show_other_settings');
		$ilTabs->setSubTabActive('show_default_options_settings');

		$form = $this->getDefaultOptionsSettingsForm();
		$tpl->setContent($form->getHTML());
	}

	public function showDefaultInputsSettings()
	{
		global $tpl, $ilTabs;
		$ilTabs->setTabActive('show_other_settings');
		$ilTabs->setSubTabActive('show_default_inputs_settings');

		$form = $this->getDefaultInputsSettingsForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Show the healthcheck screen
	 * @param string $a_mode 'reduced', 'extended' or empty
	 */
	public function showHealthcheck($a_mode = "")
	{
		global $tpl, $ilCtrl, $ilTabs;
		$ilTabs->setTabActive('show_healthcheck');

		require_once("./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php");
		$toolbar = new ilToolbarGUI();
		$toolbar->setFormAction($ilCtrl->getFormAction($this));
		$toolbar->addFormButton($this->plugin_object->txt("healthcheck_reduced"), "healthcheckReduced");
		$toolbar->addFormButton($this->plugin_object->txt("healthcheck_expanded"), "healthcheckExpanded");
		$toolbar->addFormButton($this->plugin_object->txt("clear_cache"), "clearCache");

		if ($a_mode != "") {
			//Create Healthcheck
			$this->plugin_object->includeClass("model/configuration/class.assStackQuestionHealthcheck.php");
			$healthcheck_object = new assStackQuestionHealthcheck($this->plugin_object);
			$healthcheck_data = $healthcheck_object->doHealthcheck();

			//Show healthcheck
			$this->plugin_object->includeClass("GUI/configuration/class.assStackQuestionHealthcheckGUI.php");
			$healthcheck_gui_object = new assStackQuestionHealthcheckGUI($this->plugin_object, $healthcheck_data);
			$healthcheck_gui = $healthcheck_gui_object->showHealthcheck($a_mode);

			$result_html = $healthcheck_gui->get();
		}

		$tpl->setContent($toolbar->getHTML() . $result_html);
	}

	/*
	 * FORMS CREATION METHODS
	 */

	public function getConnectionSettingsForm()
	{
		global $ilCtrl;
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		//Values from DB
		$connection_data = assStackQuestionConfig::_getStoredSettings('connection');

		//Platform selection

		//IF AUTOMATIC DETECTION IS ACTIVATED
		/*
		$platform_type = new ilNonEditableValueGUI($this->plugin_object->txt('platform_type'), 'platform_type');
		$platform_type->setInfo($this->plugin_object->txt('platform_type_info'));
		$platform_type->setValue($connection_data['platform_type']);
		$form->addItem($platform_type);
		*/

		//IF MANUAL SELECTION ACTIVATED UNCOMMENT THIS
		$platform_type = new ilSelectInputGUI($this->plugin_object->txt('platform_type'), 'platform_type');
		$platform_type->setOptions(array(
			"win" => $this->plugin_object->txt('windows'),
			"unix" => $this->plugin_object->txt('unix'),
			"server" => $this->plugin_object->txt('server')
		));
		$platform_type->setInfo($this->plugin_object->txt('platform_type_info'));
		$platform_type->setValue($connection_data['platform_type']);
		$form->addItem($platform_type);


		//Maxima version
		$maxima_version = new ilSelectInputGUI($this->plugin_object->txt('maxima_version'), 'maxima_version');
		$maxima_version->setOptions(array(
			"5.31.3" => "5.31.3",
			"5.31.2" => "5.31.2",
			"5.28.0" => "5.28.0",
			"5.27.0" => "5.27.0",
			"5.26.0" => "5.26.0"
		));
		$maxima_version->setInfo($this->plugin_object->txt('maxima_version_info'));
		$maxima_version->setValue($connection_data['maxima_version']);
		$form->addItem($maxima_version);

		//CAS connection timeout
		$cas_connection_timeout = new ilTextInputGUI($this->plugin_object->txt('cas_connection_timeout'), 'cas_connection_timeout');
		$cas_connection_timeout->setInfo($this->plugin_object->txt('cas_connection_timeout_info'));
		$cas_connection_timeout->setValue($connection_data['cas_connection_timeout']);
		$form->addItem($cas_connection_timeout);

		//CAS result caching
		//NOT USED BY ILIAS VERSION
		/*
		$cas_result_caching = new ilSelectInputGUI($this->plugin_object->txt('cas_result_caching'), 'cas_result_caching');
		$cas_result_caching->setOptions(array(
			"db" => $this->plugin_object->txt('cache_in_the_database'),
			"otherdb" => $this->plugin_object->txt('do_not_cache')
		));
		$cas_result_caching->setInfo($this->plugin_object->txt('cas_result_caching_info'));
		$cas_result_caching->setValue($connection_data['cas_result_caching']);
		$form->addItem($cas_result_caching);
		*/
		$cas_result_caching = new ilHiddenInputGUI('cas_result_caching');
		$cas_result_caching->setValue('db');
		$form->addItem($cas_result_caching);

		if ($connection_data['platform_type'] == 'win' OR $connection_data['platform_type'] == 'server') {

			//Maxima command
			$maxima_command = new ilTextInputGUI($this->plugin_object->txt('maxima_command'), 'maxima_command');
			$maxima_command->setInfo($this->plugin_object->txt('maxima_command_info'));
			$maxima_command->setValue($connection_data['maxima_command']);
			$form->addItem($maxima_command);

			//Plot command
			$plot_command = new ilTextInputGUI($this->plugin_object->txt('plot_command'), 'plot_command');
			$plot_command->setInfo($this->plugin_object->txt('plot_command_info'));
			$plot_command->setValue($connection_data['plot_command']);
			$form->addItem($plot_command);
		}

		//CAS debugging
		//NOT USED BY ILIAS VERSION
		/*
		$cas_debugging = new ilCheckboxInputGUI($this->plugin_object->txt('cas_debugging'), 'cas_debugging');
		$cas_debugging->setInfo($this->plugin_object->txt("cas_debugging_info"));
		$cas_debugging->setChecked($connection_data['cas_debugging']);
		$form->addItem($cas_debugging);
		*/
		$cas_debugging = new ilHiddenInputGUI('cas_debugging');
		$cas_debugging->setValue('0');
		$form->addItem($cas_debugging);

		$form->setTitle($this->plugin_object->txt('connection_settings'));
		$form->addCommandButton("saveConnectionSettings", $this->plugin_object->txt("save"));
		$form->addCommandButton("showConnectionSettings", $this->plugin_object->txt("cancel"));
		$form->addCommandButton("setDefaultSettingsForConnection", $this->plugin_object->txt("default_settings"));

		return $form;
	}

	public function getDisplaySettingsForm()
	{
		global $ilCtrl;
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		//Values from DB
		$display_data = assStackQuestionConfig::_getStoredSettings('display');
		$connection_data = assStackQuestionConfig::_getStoredSettings('connection');

		//Instant validation
		if ($connection_data['platform_type'] == 'server') {
			$instant_validation = new ilCheckboxInputGUI($this->plugin_object->txt('instant_validation'), 'instant_validation');
			$instant_validation->setInfo($this->plugin_object->txt("instant_validation_info"));
			$instant_validation->setChecked($display_data['instant_validation']);
		} else {
			$instant_validation = new ilCheckboxInputGUI($this->plugin_object->txt('instant_validation'), 'instant_validation');
			$instant_validation->setInfo($this->plugin_object->txt("instant_validation_info"));
			$instant_validation->setChecked(FALSE);
			$instant_validation->setDisabled(TRUE);
		}
		$form->addItem($instant_validation);

		//Maths filter
		$maths_filter = new ilSelectInputGUI($this->plugin_object->txt('maths_filter'), 'maths_filter');
		$maths_filter->setOptions(array(
			"mathjax" => "MathJax"
		));
		$maths_filter->setInfo($this->plugin_object->txt('maths_filter_info'));
		$maths_filter->setValue($display_data['maths_filter']);
		$form->addItem($maths_filter);

		//Replace dollars
		$replace_dollars = new ilCheckboxInputGUI($this->plugin_object->txt('replace_dollars'), 'replace_dollars');
		$replace_dollars->setInfo($this->plugin_object->txt("replace_dollars_info"));
		$replace_dollars->setChecked($display_data['replace_dollars']);
		$form->addItem($replace_dollars);

		$form->setTitle($this->plugin_object->txt('display_settings'));
		$form->addCommandButton("saveDisplaySettings", $this->plugin_object->txt("save"));
		$form->addCommandButton("showDisplaySettings", $this->plugin_object->txt("cancel"));
		$form->addCommandButton("setDefaultSettingsForDisplay", $this->plugin_object->txt("default_settings"));

		return $form;
	}

	public function getDefaultOptionsSettingsForm()
	{
		global $ilCtrl;
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		//Values from DB
		$options_data = assStackQuestionConfig::_getStoredSettings('options');

		//Options question simplify
		$options_question_simplify = new ilCheckboxInputGUI($this->plugin_object->txt('options_question_simplify'), 'options_question_simplify');
		$options_question_simplify->setInfo($this->plugin_object->txt('options_question_simplify_info'));
		$options_question_simplify->setChecked($options_data['options_question_simplify']);
		$form->addItem($options_question_simplify);

		//Options assume positive
		$options_assume_positive = new ilCheckboxInputGUI($this->plugin_object->txt('options_assume_positive'), 'options_assume_positive');
		$options_assume_positive->setInfo($this->plugin_object->txt('options_assume_positive_info'));
		$options_assume_positive->setChecked($options_data['options_assume_positive']);
		$form->addItem($options_assume_positive);

		//Options Standard feedback for correct answer
		$options_prt_correct = new ilTextAreaInputGUI($this->plugin_object->txt('options_prt_correct'), 'options_prt_correct');
		$options_prt_correct->setValue($options_data['options_prt_correct']);
		$form->addItem($options_prt_correct);

		//Options Standard feedback for partially correct answer
		$options_prt_partially_correct = new ilTextAreaInputGUI($this->plugin_object->txt('options_prt_partially_correct'), 'options_prt_partially_correct');
		$options_prt_partially_correct->setValue($options_data['options_prt_partially_correct']);
		$form->addItem($options_prt_partially_correct);

		//Options Standard feedback for incorrect answer
		$options_prt_incorrect = new ilTextAreaInputGUI($this->plugin_object->txt('options_prt_incorrect'), 'options_prt_incorrect');
		$options_prt_incorrect->setValue($options_data['options_prt_incorrect']);
		$form->addItem($options_prt_incorrect);

		//Options multiplication sign
		$options_multiplication_sign = new ilSelectInputGUI($this->plugin_object->txt('options_multiplication_sign'), 'options_multiplication_sign');
		$options_multiplication_sign->setOptions(array(
			"dot" => $this->plugin_object->txt('options_mult_sign_dot'),
			"cross" => $this->plugin_object->txt('options_mult_sign_cross'),
			"none" => $this->plugin_object->txt('options_mult_sign_none')
		));
		$options_multiplication_sign->setInfo($this->plugin_object->txt('options_multiplication_sign'));
		$options_multiplication_sign->setValue($options_data['options_multiplication_sign']);
		$form->addItem($options_multiplication_sign);

		//Options Sqrt sign
		$options_sqrt_sign = new ilCheckboxInputGUI($this->plugin_object->txt('options_sqrt_sign'), 'options_sqrt_sign');
		$options_sqrt_sign->setInfo($this->plugin_object->txt('options_sqrt_sign_info'));
		$options_sqrt_sign->setChecked($options_data['options_sqrt_sign']);
		$form->addItem($options_sqrt_sign);

		//Options Complex numbers
		$options_complex_numbers = new ilSelectInputGUI($this->plugin_object->txt('options_complex_numbers'), 'options_complex_numbers');
		$options_complex_numbers->setOptions(array(
			"i" => $this->plugin_object->txt('options_complex_numbers_i'),
			"j" => $this->plugin_object->txt('options_complex_numbers_j'),
			"symi" => $this->plugin_object->txt('options_complex_numbers_symi'),
			"symj" => $this->plugin_object->txt('options_complex_numbers_symj')
		));
		$options_complex_numbers->setInfo($this->plugin_object->txt('options_complex_numbers_info'));
		$options_complex_numbers->setValue($options_data['options_complex_numbers']);
		$form->addItem($options_complex_numbers);

		//Options inverse trigonometric
		$options_inverse_trigonometric = new ilSelectInputGUI($this->plugin_object->txt('options_inverse_trigonometric'), 'options_inverse_trigonometric');
		$options_inverse_trigonometric->setOptions(array(
			"cos-1" => $this->plugin_object->txt('options_inverse_trigonometric_cos'),
			"acos" => $this->plugin_object->txt('options_inverse_trigonometric_acos'),
			"arccos" => $this->plugin_object->txt('options_inverse_trigonometric_arccos')
		));
		$options_inverse_trigonometric->setInfo($this->plugin_object->txt('options_inverse_trigonometric_info'));
		$options_inverse_trigonometric->setValue($options_data['options_inverse_trigonometric']);
		$form->addItem($options_inverse_trigonometric);

		$form->setTitle($this->plugin_object->txt('default_options_settings'));

		$form->addCommandButton("saveDefaultOptionsSettings", $this->plugin_object->txt("save"));
		$form->addCommandButton("showDefaultOptionsSettings", $this->plugin_object->txt("cancel"));
		$form->addCommandButton("setDefaultSettingsForOptions", $this->plugin_object->txt("default_settings"));

		return $form;
	}

	public function getDefaultInputsSettingsForm()
	{
		global $ilCtrl;
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		//Values from DB
		$inputs_data = assStackQuestionConfig::_getStoredSettings('inputs');

		//Input type
		$input_type = new ilSelectInputGUI($this->plugin_object->txt('input_type'), 'input_type');
		$input_type->setOptions(array(
			"algebraic" => $this->plugin_object->txt('input_type_algebraic'),
			"boolean" => $this->plugin_object->txt('input_type_boolean'),
			"matrix" => $this->plugin_object->txt('input_type_matrix'),
			"singlechar" => $this->plugin_object->txt('input_type_singlechar'),
			"textarea" => $this->plugin_object->txt('input_type_textarea')
		));
		$input_type->setInfo($this->plugin_object->txt('input_type_info'));
		$input_type->setValue($inputs_data['input_type']);
		$form->addItem($input_type);

		//Input box size
		$input_box_size = new ilTextInputGUI($this->plugin_object->txt('input_box_size'), 'input_box_size');
		$input_box_size->setInfo($this->plugin_object->txt('input_box_size_info'));
		$input_box_size->setValue($inputs_data['input_box_size']);
		$form->addItem($input_box_size);

		//Input strict syntax
		$input_strict_syntax = new ilCheckboxInputGUI($this->plugin_object->txt('input_strict_syntax'), 'input_strict_syntax');
		$input_strict_syntax->setInfo($this->plugin_object->txt("input_strict_syntax_info"));
		$input_strict_syntax->setChecked($inputs_data['input_strict_syntax']);
		$form->addItem($input_strict_syntax);

		//Input insert stars
		$input_insert_stars = new ilCheckboxInputGUI($this->plugin_object->txt('input_insert_stars'), 'input_insert_stars');
		$input_insert_stars->setInfo($this->plugin_object->txt("input_insert_stars_info"));
		$input_insert_stars->setChecked($inputs_data['input_insert_stars']);
		$form->addItem($input_insert_stars);

		//Input forbidden words
		$input_forbidden_words = new ilTextInputGUI($this->plugin_object->txt('input_forbidden_words'), 'input_forbidden_words');
		$input_forbidden_words->setInfo($this->plugin_object->txt('input_forbidden_words_info'));
		$input_forbidden_words->setValue($inputs_data['input_forbidden_words']);
		$form->addItem($input_forbidden_words);

		//Input forbid float
		$input_forbid_float = new ilCheckboxInputGUI($this->plugin_object->txt('input_forbid_float'), 'input_forbid_float');
		$input_forbid_float->setInfo($this->plugin_object->txt("input_forbid_float_info"));
		$input_forbid_float->setChecked($inputs_data['input_forbid_float']);
		$form->addItem($input_forbid_float);

		//Input Require lowest terms
		$input_require_lowest_terms = new ilCheckboxInputGUI($this->plugin_object->txt('input_require_lowest_terms'), 'input_require_lowest_terms');
		$input_require_lowest_terms->setInfo($this->plugin_object->txt("input_require_lowest_terms_info"));
		$input_require_lowest_terms->setChecked($inputs_data['input_require_lowest_terms']);
		$form->addItem($input_require_lowest_terms);

		//Input Check answer type
		$input_check_answer_type = new ilCheckboxInputGUI($this->plugin_object->txt('input_check_answer_type'), 'input_check_answer_type');
		$input_check_answer_type->setInfo($this->plugin_object->txt("input_check_answer_type_info"));
		$input_check_answer_type->setChecked($inputs_data['input_check_answer_type']);
		$form->addItem($input_check_answer_type);

		//Input Student must verify
		$input_must_verify = new ilCheckboxInputGUI($this->plugin_object->txt('input_must_verify'), 'input_must_verify');
		$input_must_verify->setInfo($this->plugin_object->txt("input_must_verify_info"));
		$input_must_verify->setChecked($inputs_data['input_must_verify']);
		$form->addItem($input_must_verify);

		//Input Show validation
		$input_show_validation = new ilCheckboxInputGUI($this->plugin_object->txt('input_show_validation'), 'input_show_validation');
		$input_show_validation->setInfo($this->plugin_object->txt("input_show_validation_info"));
		$input_show_validation->setChecked($inputs_data['input_show_validation']);
		$form->addItem($input_show_validation);

		$form->setTitle($this->plugin_object->txt('default_input_settings'));
		$form->addCommandButton("saveDefaultInputsSettings", $this->plugin_object->txt("save"));
		$form->addCommandButton("showDefaultInputsSettings", $this->plugin_object->txt("cancel"));
		$form->addCommandButton("setDefaultSettingsForInputs", $this->plugin_object->txt("default_settings"));

		return $form;
	}

	public function healthcheckReduced()
	{
		$this->showHealthcheck("reduced");
	}

	public function healthcheckExpanded()
	{
		$this->showHealthcheck("expanded");
	}


	public function clearCache()
	{
		//Create Healthcheck
		$this->plugin_object->includeClass("model/configuration/class.assStackQuestionHealthcheck.php");
		$healthcheck_object = new assStackQuestionHealthcheck($this->plugin_object);
		$cache_is_clear = $healthcheck_object->clearCache();

		if ($cache_is_clear) {
			ilUtil::sendSuccess($this->plugin_object->txt('cache_successfully_deleted'));
		}

		$this->showHealthcheck("");
	}


	/*
	 * SAVE CONFIGURATION METHODS
	 */

	public function saveConnectionSettings()
	{
		$ok = $this->config->saveConnectionSettings();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_connection_changed_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showConnectionSettings();
	}

	public function saveDisplaySettings()
	{
		$ok = $this->config->saveDisplaySettings();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_display_changed_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showDisplaySettings();
	}

	public function saveDefaultOptionsSettings()
	{
		$ok = $this->config->saveDefaultOptionsSettings();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_options_changed_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showDefaultOptionsSettings();
	}

	public function saveDefaultInputsSettings()
	{
		$ok = $this->config->saveDefaultInputsSettings();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_inputs_changed_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showDefaultInputsSettings();
	}

	/*
	 * SET DEFAULT VALUES METHODS
	 */

	public function setDefaultSettingsForConnection()
	{
		$ok = $this->config->setDefaultSettingsForConnection();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_default_connection_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showConnectionSettings();
	}

	public function setDefaultSettingsForDisplay()
	{
		$ok = $this->config->setDefaultSettingsForDisplay();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_default_display_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showDisplaySettings();
	}

	public function setDefaultSettingsForOptions()
	{
		$ok = $this->config->setDefaultSettingsForOptions();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_default_options_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showDefaultOptionsSettings();
	}

	public function setDefaultSettingsForInputs()
	{
		$ok = $this->config->setDefaultSettingsForInputs();
		if ($ok) {
			ilUtil::sendSuccess($this->plugin_object->txt('config_default_inputs_message'));
		} else {
			ilUtil::sendFailure($this->plugin_object->txt('config_error_message'));
		}
		$this->showDefaultInputsSettings();
	}
}
