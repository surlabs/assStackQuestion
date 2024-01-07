<#1>
<?php
/**
 * Copyright (c) 2022 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 *
 *
 * Database creation script.
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 *
 * $Id 5.2$
 */
/*
 * Create the new question type
 */
global $DIC;
$db = $DIC->database();

$res = $db->queryF("SELECT * FROM qpl_qst_type WHERE type_tag = %s", array('text'), array('assStackQuestion'));

if ($res->numRows() == 0) {
	$res = $db->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
	$data = $db->fetchAssoc($res);
	$max = $data["maxid"] + 1;

	$affectedRows = $db->manipulateF("INSERT INTO qpl_qst_type (question_type_id, type_tag, plugin) VALUES (%s, %s, %s)", array("integer", "text", "integer"), array($max, 'assStackQuestion', 1));
}
?>
<#2>
<?php
/*
 * STACK name: options "Stores the main options for each Stack question"
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_options')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_variables' => array('type' => 'clob', 'notnull' => true), 'specific_feedback' => array('type' => 'clob', 'notnull' => true), 'specific_feedback_format' => array('type' => 'integer', 'length' => 2, 'notnull' => true, 'default' => 0), 'question_note' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'question_simplify' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1), 'assume_positive' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0), 'prt_correct' => array('type' => 'clob', 'notnull' => true), 'prt_correct_format' => array('type' => 'integer', 'length' => 2, 'notnull' => true, 'default' => 0), 'prt_partially_correct' => array('type' => 'clob', 'notnull' => true), 'prt_partially_correct_format' => array('type' => 'integer', 'length' => 2, 'notnull' => true, 'default' => 0), 'prt_incorrect' => array('type' => 'clob', 'notnull' => true), 'prt_incorrect_format' => array('type' => 'integer', 'length' => 2, 'notnull' => true, 'default' => 0), 'multiplication_sign' => array('type' => 'text', 'length' => 8, 'notnull' => true, 'default' => 'dot'), 'sqrt_sign' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1), 'complex_no' => array('type' => 'text', 'length' => 8, 'notnull' => true, 'default' => 'i'), 'inverse_trig' => array('type' => 'text', 'length' => 8, 'notnull' => true, 'default' => 'cos-1'), 'variants_selection_seed' => array('type' => 'text', 'length' => 255, 'notnull' => false, 'default' => NULL));
	$db->createTable("xqcas_options", $fields);
	$db->createSequence("xqcas_options");
	$db->addPrimaryKey("xqcas_options", array("id"));

	/*
	 * 2 indexes to be created
	 */
}
?>
<#3>
<?php
/*
 * STACK name: inputs "One row for each input in the question."
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_inputs')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'name' => array('type' => 'text', 'length' => 32, 'notnull' => true), 'type' => array('type' => 'text', 'length' => 32, 'notnull' => true), 'tans' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'box_size' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 15), 'strict_syntax' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1), 'insert_stars' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0), 'syntax_hint' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'forbid_words' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'forbid_float' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1), 'require_lowest_terms' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0), 'check_answer_type' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0), 'must_verify' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1), 'show_validation' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1), 'options' => array('type' => 'clob', 'notnull' => true));
	$db->createTable("xqcas_inputs", $fields);
	$db->createSequence("xqcas_inputs");
	$db->addPrimaryKey("xqcas_inputs", array("id"));

	/*
	 * 3 indexes to be created
	 */
}
?>
<#4>
<?php
/*
 * STACK name: prts "One row for each PRT in the question."
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_prts')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'name' => array('type' => 'text', 'length' => 32, 'notnull' => true), 'value' => array('type' => 'text', 'length' => 21, 'notnull' => true), 'auto_simplify' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1), 'feedback_variables' => array('type' => 'clob', 'notnull' => true), 'first_node_name' => array('type' => 'text', 'length' => 8, 'notnull' => true));
	$db->createTable("xqcas_prts", $fields);
	$db->createSequence("xqcas_prts");
	$db->addPrimaryKey("xqcas_prts", array("id"));

	/*
	 * 3 indexes to be created
	 */
}
?>
<#5>
<?php
/*
 * STACK name: prt_nodes "One row for each node in each PRT in the question."
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_prt_nodes')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'prt_name' => array('type' => 'text', 'length' => 32, 'notnull' => true), 'node_name' => array('type' => 'text', 'length' => 8, 'notnull' => true), 'answer_test' => array('type' => 'text', 'length' => 32, 'notnull' => true), 'sans' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'tans' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'test_options' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'quiet' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0), 'true_score_mode' => array('type' => 'text', 'length' => 4, 'notnull' => true, 'default' => '='), 'true_score' => array('type' => 'text', 'length' => 21, 'notnull' => true), 'true_penalty' => array('type' => 'text', 'length' => 21, 'notnull' => false, 'default' => NULL), 'true_next_node' => array('type' => 'text', 'length' => 8, 'notnull' => false, 'default' => NULL), 'true_answer_note' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'true_feedback' => array('type' => 'clob', 'notnull' => true), 'true_feedback_format' => array('type' => 'integer', 'length' => 2, 'notnull' => true, 'default' => 0), 'false_score_mode' => array('type' => 'text', 'length' => 4, 'notnull' => true, 'default' => '='), 'false_score' => array('type' => 'text', 'length' => 21, 'notnull' => true), 'false_penalty' => array('type' => 'text', 'length' => 21, 'notnull' => false, 'default' => NULL), 'false_next_node' => array('type' => 'text', 'length' => 8, 'notnull' => false, 'default' => NULL), 'false_answer_note' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'false_feedback' => array('type' => 'clob', 'notnull' => true), 'false_feedback_format' => array('type' => 'integer', 'length' => 2, 'notnull' => true, 'default' => 0));
	$db->createTable("xqcas_prt_nodes", $fields);
	$db->createSequence("xqcas_prt_nodes");
	$db->addPrimaryKey("xqcas_prt_nodes", array("id"));

	/*
	 * 3 indexes to be created
	 */
}
?>
<#6>
<?php
/*
 * STACK name: cas_cache "Caches the resuts of calls to Maxima."
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_cas_cache')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'hash' => array('type' => 'text', 'length' => 40, 'notnull' => true), 'command' => array('type' => 'clob', 'notnull' => true), 'result' => array('type' => 'clob', 'notnull' => true));
	$db->createTable("xqcas_cas_cache", $fields);
	$db->createSequence("xqcas_cas_cache");
	$db->addPrimaryKey("xqcas_cas_cache", array("id"));

	/*
	 * 2 indexes to be created
	 */
}
?>
<#7>
<?php
/*
 * STACK name: qtests "One row for each questiontest for each question."
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_qtests')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'test_case' => array('type' => 'integer', 'length' => 8, 'notnull' => true));
	$db->createTable("xqcas_qtests", $fields);
	$db->createSequence("xqcas_qtests");
	$db->addPrimaryKey("xqcas_qtests", array("id"));

	/*
	 * 3 indexes to be created
	 */
}
?>
<#8>
<?php
/*
 * STACK name: qtest_inputs "The value for each input for the question tests."
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_qtest_inputs')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'test_case' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'input_name' => array('type' => 'text', 'length' => 32, 'notnull' => true), 'value' => array('type' => 'text', 'length' => 255, 'notnull' => true));
	$db->createTable("xqcas_qtest_inputs", $fields);
	$db->createSequence("xqcas_qtest_inputs");
	$db->addPrimaryKey("xqcas_qtest_inputs", array("id"));

	/*
	 * 3 indexes to be created
	 */
}
?>
<#9>
<?php
/*
 * STACK name: qtest_expected "Holds the expected outcomes for each PRT for this question t"
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_qtest_expected')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'test_case' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'prt_name' => array('type' => 'text', 'length' => 32, 'notnull' => true), 'expected_score' => array('type' => 'text', 'length' => 21, 'notnull' => false, 'default' => NULL), 'expected_penalty' => array('type' => 'text', 'length' => 21, 'notnull' => false, 'default' => NULL), 'expected_answer_note' => array('type' => 'text', 'length' => 255, 'notnull' => true));
	$db->createTable("xqcas_qtest_expected", $fields);
	$db->createSequence("xqcas_qtest_expected");
	$db->addPrimaryKey("xqcas_qtest_expected", array("id"));

	/*
	 * 3 indexes to be created
	 */
}
?>
<#10>
<?php
/*
 * STACK name: deployed_seeds "Holds the seeds for the variants of each question that have "
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_deployed_seeds')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'seed' => array('type' => 'integer', 'length' => 8, 'notnull' => true));
	$db->createTable("xqcas_deployed_seeds", $fields);
	$db->createSequence("xqcas_deployed_seeds");
	$db->addPrimaryKey("xqcas_deployed_seeds", array("id"));

	/*
	 * 3 indexes to be created
	 */
}
?>
<#11>
<#12>
<?php
global $DIC;
$db = $DIC->database();
$allow_words_column = array('type' => 'text', 'length' => 255, 'notnull' => true);
if (!$db->tableColumnExists("xqcas_inputs", "allow_words")) {
	$db->addTableColumn("xqcas_inputs", "allow_words", $allow_words_column);
}
?>
<#13>
<#14>
<?php
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_ilias_specific')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'general_feedback' => array('type' => 'clob'));
	$db->createTable("xqcas_ilias_specific", $fields);
	$db->createSequence("xqcas_ilias_specific");
	$db->addPrimaryKey("xqcas_ilias_specific", array("id"));
}
?>
<#15>
<#16>
<#17>
<?php
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_configuration')) {
	$fields = array('parameter_name' => array('type' => 'text', 'length' => 255, 'notnull' => true), 'value' => array('type' => 'clob'), 'group_name' => array('type' => 'text', 'length' => 255));
	$db->createTable("xqcas_configuration", $fields);
	$db->addPrimaryKey("xqcas_configuration", array("parameter_name"));
}
?>
<#18>
<?php
global $DIC;
$db = $DIC->database();
//Check if connection entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "connection"';
$result = $db->query($query);
if (!$db->fetchAssoc($result)) {
	//Default values for connection
	$connection_default_values = array('platform_type' => 'server', 'maxima_version' => '5.31.2', 'cas_connection_timeout' => '5', 'cas_result_caching' => 'db', 'maxima_command' => '', 'plot_command' => '', 'cas_debugging' => '0');
	foreach ($connection_default_values as $paremeter_name => $value) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', $paremeter_name), 'value' => array('clob', $value), 'group_name' => array('text', 'connection')));
	}
}


//Check if display entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "display"';
$result = $db->query($query);
if (!$db->fetchAssoc($result)) {
	$display_default_values = array('instant_validation' => '0', 'maths_filter' => 'mathjax', 'replace_dollars' => '1');
	foreach ($display_default_values as $paremeter_name => $value) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', $paremeter_name), 'value' => array('clob', $value), 'group_name' => array('text', 'display')));
	}
}

//Check if default options entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "options"';
$result = $db->query($query);
if (!$db->fetchAssoc($result)) {
	$options_default_values = array('options_question_simplify' => '1', 'options_assume_positive' => '0', 'options_prt_correct' => 'Correct answer, well done.', 'options_prt_partially_correct' => 'Your answer is partially correct.', 'options_prt_incorrect' => 'Incorrect answer.', 'options_multiplication_sign' => 'dot', 'options_sqrt_sign' => '1', 'options_complex_numbers' => 'i', 'options_inverse_trigonometric' => 'cos-1');
	foreach ($options_default_values as $paremeter_name => $value){
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', $paremeter_name), 'value' => array('clob', $value), 'group_name' => array('text', 'options')));
	}
}


//Check if default input entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "inputs"';
$result = $db->query($query);
if (!$db->fetchAssoc($result)) {
	$inputs_default_values = array('input_type' => 'algebraic', 'input_box_size' => '15', 'input_strict_syntax' => '1', 'input_insert_stars' => '0', 'input_forbidden_words' => '', 'input_forbid_float' => '1', 'input_require_lowest_terms' => '0', 'input_check_answer_type' => '0', 'input_must_verify' => '1', 'input_show_validation' => '1');
	foreach ($inputs_default_values as $paremeter_name => $value) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', $paremeter_name), 'value' => array('clob', $value), 'group_name' => array('text', 'inputs')));
	}
}
$config = new assStackQuestionConfig();
$config->setDefaultSettingsForConnection();
?>
<#19>
<?php
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_ilias_specific')) {

//Inserting index
//Inputs
	$db->addIndex('xqcas_inputs', array('question_id', 'name'), 'i1', FALSE);
//PRT Nodes
	$db->addIndex('xqcas_prt_nodes', array('question_id', 'prt_name', 'node_name'), 'i2', FALSE);
//Cache
	$db->addIndex('xqcas_cas_cache', array('hash'), 'i3', FALSE);
//Tests
	$db->addIndex('xqcas_qtest_inputs', array('question_id', 'test_case', 'input_name'), 'i4', FALSE);
	$db->addIndex('xqcas_qtest_expected', array('question_id', 'test_case', 'prt_name'), 'i5', FALSE);
//Seeds
	$db->addIndex('xqcas_deployed_seeds', array('question_id', 'seed'), 'i6', FALSE);
}
?>
<#20>
<?php
global $DIC;
$db = $DIC->database();
//Adding extra fields in moodle XML
//Penalty
$penalty_column = array('type' => 'text', 'length' => 21);
if (!$db->tableColumnExists("xqcas_ilias_specific", "penalty")) {
	$db->addTableColumn("xqcas_ilias_specific", "penalty", $penalty_column);
}
//Hidden

$hidden_column = array('type' => 'integer', 'length' => 4);
if (!$db->tableColumnExists("xqcas_ilias_specific", "hídden")) {
	$db->addTableColumn("xqcas_ilias_specific", "hidden", $hidden_column);
}
?>
<#21>
<#22>
<#23>
<?php
global $DIC;
$db = $DIC->database();
//Change name to ilias_specific and sequence
if ($db->tableExists('xqcas_ilias_specific')) {
	$db->dropTable("xqcas_ilias_specific", FALSE);
	$db->dropTable("xqcas_ilias_specific_seq", FALSE);
}
if (!$db->tableExists('xqcas_extra_info')) {
	$fields = array('id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true), 'general_feedback' => array('type' => 'clob'), 'penalty' => array('type' => 'text', 'length' => 21), 'hidden' => array('type' => 'integer', 'length' => 4));
	$db->createTable("xqcas_extra_info", $fields);
	$db->createSequence("xqcas_extra_info");
	$db->addPrimaryKey("xqcas_extra_info", array("id"));
}
?>
<#24>
<#25>
<#26>
<#27>
<?php
/*
 * add id to old version of the plugin
 */
global $DIC;
$db = $DIC->database();
$res = $db->queryF("SELECT * FROM qpl_qst_type WHERE type_tag = %s", array('text'), array('assCasQuestion'));

if ($res->numRows() != 0) {
	//Update the old plugin name
	$res = $db->query("UPDATE qpl_qst_type SET type_tag = 'assStackQuestion' WHERE type_tag = 'assCasQuestion'");
	//Get last id
	$res = $db->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
	$data = $db->fetchAssoc($res);
	$max = $data["maxid"];
	//Delete new plugin
	$res = $db->query("DELETE FROM qpl_qst_type WHERE question_type_id = " . $max);
}
?>
<#28>
<?php
global $DIC;
$db = $DIC->database();
//Add matrix parens column for STACK 3.3
$matrix_parens = array('type' => 'text', 'length' => 8);
if ($db->tableExists('xqcas_options')) {
	if (!$db->tableColumnExists("xqcas_options", "matrix_parens")) {
		$db->addTableColumn("xqcas_options", "matrix_parens", $matrix_parens);
	}
}
?>
<#29>
<?php
//No longer needed
?>
<#30>
<?php
//No longer needed
?>
<#31>
<?php
global $DIC;
$db = $DIC->database();
if ($db->tableExists('xqcas_options')) {
	$db->modifyTableColumn("xqcas_options", "question_variables", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "specific_feedback", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "specific_feedback_format", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "question_note", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "question_simplify", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "assume_positive", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "prt_correct", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "prt_correct_format", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "prt_partially_correct", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "prt_partially_correct_format", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "prt_incorrect", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "prt_incorrect_format", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "multiplication_sign", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "sqrt_sign", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "complex_no", array("notnull" => false));
	$db->modifyTableColumn("xqcas_options", "inverse_trig", array("notnull" => false));
}

if ($db->tableExists('xqcas_inputs')) {
	$db->modifyTableColumn("xqcas_inputs", "name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "type", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "tans", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "box_size", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "strict_syntax", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "insert_stars", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "syntax_hint", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "forbid_words", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "forbid_float", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "require_lowest_terms", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "check_answer_type", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "must_verify", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "show_validation", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "options", array("notnull" => false));
	$db->modifyTableColumn("xqcas_inputs", "allow_words", array("notnull" => false));
}

if ($db->tableExists('xqcas_prts')) {
	$db->modifyTableColumn("xqcas_prts", "name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prts", "value", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prts", "auto_simplify", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prts", "feedback_variables", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prts", "first_node_name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prts", "name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prts", "name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prts", "name", array("notnull" => false));
}

if ($db->tableExists('xqcas_prt_nodes')) {
	$db->modifyTableColumn("xqcas_prt_nodes", "prt_name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "node_name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "answer_test", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "sans", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "tans", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "test_options", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "quiet", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "true_score_mode", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "true_score", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "true_answer_note", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "true_feedback", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "true_feedback_format", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "false_score_mode", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "false_score", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "false_answer_note", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "false_feedback", array("notnull" => false));
	$db->modifyTableColumn("xqcas_prt_nodes", "false_feedback_format", array("notnull" => false));
}

if ($db->tableExists('xqcas_qtests')) {
	$db->modifyTableColumn("xqcas_qtests", "test_case", array("notnull" => false));
}

if ($db->tableExists('xqcas_qtest_inputs')) {
	$db->modifyTableColumn("xqcas_qtest_inputs", "test_case", array("notnull" => false));
	$db->modifyTableColumn("xqcas_qtest_inputs", "input_name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_qtest_inputs", "value", array("notnull" => false));
}

if ($db->tableExists('xqcas_qtest_expected')) {
	$db->modifyTableColumn("xqcas_qtest_expected", "test_case", array("notnull" => false));
	$db->modifyTableColumn("xqcas_qtest_expected", "prt_name", array("notnull" => false));
	$db->modifyTableColumn("xqcas_qtest_expected", "expected_answer_note", array("notnull" => false));
}

?>
<#32>
<?php
//No longer needed
?>
<#33>
<?php
global $DIC;
$db = $DIC->database();
if ($db->tableExists('xqcas_configuration')) {
	$db->replace("xqcas_configuration", array('parameter_name' => array('text', 'cas_maxima_libraries'), 'value' => array('clob', ''), 'group_name' => array('text', 'connection')), array());
}
?>
<#34>
<?php
global $DIC;
$db = $DIC->database();

//Inserting index that were not inserted in step 19

//Inputs
if (!$db->indexExistsByFields('xqcas_inputs', array('question_id', 'name'))) {
	$db->addIndex('xqcas_inputs', array('question_id', 'name'), 'i1', FALSE);
}

//PRT Nodes
if (!$db->indexExistsByFields('xqcas_prt_nodes', array('question_id', 'prt_name', 'node_name'))) {
	$db->addIndex('xqcas_prt_nodes', array('question_id', 'prt_name', 'node_name'), 'i2', FALSE);
}

//Cache
if (!$db->indexExistsByFields('xqcas_cas_cache', array('hash'))) {
	$db->addIndex('xqcas_cas_cache', array('hash'), 'i3', FALSE);
}

//Tests
if (!$db->indexExistsByFields('xqcas_qtest_inputs', array('question_id', 'test_case', 'input_name'))) {
	$db->addIndex('xqcas_qtest_inputs', array('question_id', 'test_case', 'input_name'), 'i4', FALSE);
}
if (!$db->indexExistsByFields('xqcas_qtest_expected', array('question_id', 'test_case', 'prt_name'))) {
	$db->addIndex('xqcas_qtest_expected', array('question_id', 'test_case', 'prt_name'), 'i5', FALSE);
}

//Seeds
if (!$db->indexExistsByFields('xqcas_deployed_seeds', array('question_id', 'seed'))) {
	$db->addIndex('xqcas_deployed_seeds', array('question_id', 'seed'), 'i6', FALSE);
}
?>
<#35>
<?php
global $DIC;
$db = $DIC->database();
//Default Option for Matrix Parenthesis
if ($db->tableExists('xqcas_configuration')) {

	$existing_entries = array();

	$result = $db->query("SELECT parameter_name  FROM xqcas_configuration");
	while ($row = $db->fetchAssoc($result)) {
		$existing_entries[$row["parameter_name"]] = "";
	}

	//Options
	if (!array_key_exists("options_matrix_parents", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "options_matrix_parents"), 'value' => array('clob', '['), 'group_name' => array('text', 'options')));
	}
	//Inputs
	if (!array_key_exists("input_syntax_hint", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "input_syntax_hint"), 'value' => array('clob', ''), 'group_name' => array('text', 'inputs')));
	}
	if (!array_key_exists("input_allow_words", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "input_allow_words"), 'value' => array('clob', ''), 'group_name' => array('text', 'inputs')));
	}
	if (!array_key_exists("input_extra_options", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "input_extra_options"), 'value' => array('clob', ''), 'group_name' => array('text', 'inputs')));
	}
	//PRTs
	if (!array_key_exists("prt_simplify", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_simplify"), 'value' => array('clob', '1'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_node_answer_test", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_node_answer_test"), 'value' => array('clob', 'AlgEquiv'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_node_options", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_node_options"), 'value' => array('clob', ''), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_node_quiet", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_node_quiet"), 'value' => array('clob', '1'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_pos_mod", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_pos_mod"), 'value' => array('clob', '+'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_pos_score", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_pos_score"), 'value' => array('clob', '1'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_pos_penalty", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_pos_penalty"), 'value' => array('clob', '0'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_pos_answernote", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_pos_answernote"), 'value' => array('clob', 'prt1-1-T'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_neg_mod", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_neg_mod"), 'value' => array('clob', '+'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_neg_score", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_neg_score"), 'value' => array('clob', '0'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_neg_penalty", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_neg_penalty"), 'value' => array('clob', '0'), 'group_name' => array('text', 'prts')));
	}
	if (!array_key_exists("prt_neg_answernote", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "prt_neg_answernote"), 'value' => array('clob', 'prt1-1-F'), 'group_name' => array('text', 'prts')));
	}
}
?>
<#36>
<?php
//UzK only step
?>
<#37>
<?php
global $DIC;
$db = $DIC->database();
//Create feedback styles
if ($db->tableExists('xqcas_configuration')) {
	$existing_entries = array();

	$result = $db->query('SELECT * FROM xqcas_configuration WHERE group_name = "feedback"');
	while ($row = $db->fetchAssoc($result)) {
		$existing_entries[$row["parameter_name"]] = "";
	}

	if (!array_key_exists("feedback_default", $existing_entries)) {
		//Feedback style 1 will be used as default, in case feedback_default is chosen, the value in true/false_feedback_format will be 1, and no specific style will be used, but platform style
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_default"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
	//Specific feedback formats.
	if (!array_key_exists("feedback_node_right", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_node_right"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
	if (!array_key_exists("feedback_node_wrong", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_node_wrong"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
	if (!array_key_exists("feedback_node_partially", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_node_partially"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
	if (!array_key_exists("feedback_solution_hint", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_solution_hint"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
	if (!array_key_exists("feedback_extra_info", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_extra_info"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
	if (!array_key_exists("feedback_plot_feedback", $existing_entries)) {
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_plot_feedback"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
}
?>
<#38>
<?php
global $DIC;
$db = $DIC->database();
//Create feedback styles
if ($db->tableExists('xqcas_configuration')) {
	$existing_entries = array();

	$result = $db->query('SELECT * FROM xqcas_configuration WHERE group_name = "feedback"');
	while ($row = $db->fetchAssoc($result)) {
		$existing_entries[$row["parameter_name"]] = "";
	}
	if (!array_key_exists("feedback_stylesheet_id", $existing_entries)) {
		//We have to store the id of the content style we want to use for stack feedback styles
		$db->insert("xqcas_configuration", array('parameter_name' => array('text', "feedback_stylesheet_id"), 'value' => array('clob', ''), 'group_name' => array('text', 'feedback')));
	}
}
?>
<#39>
<?php
//Script only step
?>
<#40>
<?php

//modify all prts with 0 as node names, no longer supported.
/* NOT ACTIVATED

global $DIC;
$db = $DIC->database();

//Select queries
$query = 'SELECT question_id, name, id FROM xqcas_prts ORDER BY xqcas_prts.question_id';
$res_prt = $db->query($query);


//Question_id, prt _name from xqcas_prts
$questions = array();

while ($row_prt = $db->fetchAssoc($res_prt)) {
	$questions[$row_prt['question_id']][$row_prt['name']]['id'] = $row_prt['id'];

	$query = 'SELECT id, question_id, node_name, true_next_node, false_next_node, true_answer_note, false_answer_note FROM xqcas_prt_nodes  WHERE prt_name =' . $db->quote($row_prt['name'], 'string') . ' ORDER BY xqcas_prt_nodes.question_id';
	$res_nodes = $db->query($query);
	while ($row_nodes = $db->fetchAssoc($res_nodes)) {
		$questions[$row_prt['question_id']][$row_prt['name']]['nodes'][$row_nodes['node_name']]['id'] = (int)$row_nodes['id'];
		$questions[$row_prt['question_id']][$row_prt['name']]['nodes'][$row_nodes['node_name']]['true_next_node'] = (int)$row_nodes['true_next_node'];
		$questions[$row_prt['question_id']][$row_prt['name']]['nodes'][$row_nodes['node_name']]['false_next_node'] = (int)$row_nodes['false_next_node'];
	}
}

//get node_name and id


foreach ($questions as $question_id => $prts) {

	foreach ($prts as $prt_name => $prt_nodes) {

		//Change Node name and all derived parameters like answer note or next node.
		$invalid_nodes = false;

		foreach ($prt_nodes['nodes'] as $node_name => $node) {

			//If there is a node 0 in a PRT, change all nodes to node_name +1
			if (array_key_exists(0, $prt_nodes['nodes'])) {
				$invalid_nodes = true;

				$new_node_name = (int)$node_name + 1;
				//Check for non "0" next nodes
				$true_next_node = $node['true_next_node'];
				$false_next_node = $node['false_next_node'];

				//If certain nodes point node 0 as next node (not usual)
				//The next node will now be -1, so, end of the prt.
				//If we are already in node 1, we cannot point ourselves
				if ($true_next_node == '-1') {
					$true_next_node = -1;
				} else {
					$true_next_node = $true_next_node + 1;
				}

				if ($false_next_node == '-1') {
					$false_next_node = -1;
				} else {
					$false_next_node = $false_next_node + 1;
				}

				//answer note
				$true_answer_note = $prt_name . '-' . $new_node_name . '-T';
				$false_answer_note = $prt_name . '-' . $new_node_name . '-F';

			} else {
				//node name
				$new_node_name = (int)$node_name;

				//next node
				$true_next_node = $node['true_next_node'];
				$false_next_node = $node['false_next_node'];

				//answer note
				$true_answer_note = $node['true_answer_note'];
				$false_answer_note = $node['false_answer_note'];

			}

			//We ensure the answer notes are properly created (prt_name-node name-true or false)
			$query = 'UPDATE xqcas_prt_nodes
				SET node_name =' . $db->quote($new_node_name, 'string')
				. ', true_answer_note=' . $db->quote($true_answer_note, 'string')
				. ', true_next_node=' . $db->quote($true_next_node, 'integer')
				. ', false_answer_note=' . $db->quote($false_answer_note, 'string')
				. ', false_next_node=' . $db->quote($false_next_node, 'integer')
				. ' WHERE id=' . $db->quote($node['id'], 'integer');
			$db->query($query);
		}

		if ($invalid_nodes) {
			//We ensure first node is not set to 0.
			$query = 'UPDATE xqcas_prts
				SET first_node_name =' . $db->quote('1', 'string')
				. ' WHERE id=' . $db->quote($prt_nodes['id'], 'integer');
			$db->query($query);
		}
	}
}

if ($db->tableExists('xqcas_configuration')) {

	$existing_entries = array();

	$result = $db->query("SELECT parameter_name, value  FROM xqcas_configuration");
	while ($row = $db->fetchAssoc($result)) {
		$existing_entries[$row['parameter_name']] = $row['value'];
	}
}

//Ensure default answer note doesn't use 0 as node name.
if (array_key_exists('prt_pos_answernote', $existing_entries)) {
	$db->replace("xqcas_configuration", array('parameter_name' => array('text', 'prt_pos_answernote'), 'value' => array('text', 'prt1-1-T'), 'group_name' => array('text', 'prts')), array());
}
if (array_key_exists('prt_neg_answernote', $existing_entries)) {
	$db->replace("xqcas_configuration", array('parameter_name' => array('text', 'prt_neg_answernote'), 'value' => array('text', 'prt1-1-F'), 'group_name' => array('text', 'prts')), array());
}*/
?>
<#41>
<?php
//add new columns to xqcas question tables.
global $DIC;
$db = $DIC->database();

//assume real in options
$assume_real = array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0);
if (!$db->tableColumnExists("xqcas_options", "assume_real")) {
	$db->addTableColumn("xqcas_options", "assume_real", $assume_real);
}

//logic symbol in options
$logic_symbol = array('type' => 'text', 'length' => 8, 'notnull' => true, 'default' => 'lang');
if (!$db->tableColumnExists("xqcas_options", "logic_symbol")) {
	$db->addTableColumn("xqcas_options", "logic_symbol", $logic_symbol);
}

//stack version in options
$stack_version = array('type' => 'clob', 'notnull' => false, 'default' => null);
if (!$db->tableColumnExists("xqcas_options", "stack_version")) {
	$db->addTableColumn("xqcas_options", "stack_version", $stack_version);
}

//compiled cache in options
$compiled_cache = array('type' => 'clob', 'notnull' => false, 'default' => null);
if (!$db->tableColumnExists("xqcas_options", "compiled_cache")) {
	$db->addTableColumn("xqcas_options", "compiled_cache", $compiled_cache);
}

//Syntax attribute for Inputs
$syntax_attribute = array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0);
if (!$db->tableColumnExists("xqcas_inputs", "syntax_attribute")) {
	$db->addTableColumn("xqcas_inputs", "syntax_attribute", $syntax_attribute);
}

//Add default values to configuration
if ($db->tableExists('xqcas_configuration')) {

    //options
	$existing_entries = array();
	$result = $db->query('SELECT * FROM xqcas_configuration WHERE group_name = "options"');
	while ($row = $db->fetchAssoc($result)) {
		$existing_entries[$row['parameter_name']] = '';
	}

	//assume real
	if (!array_key_exists('options_assume_real', $existing_entries)) {
		//We have to store the id of the content style we want to use for stack feedback styles
		$db->insert('xqcas_configuration', array('parameter_name' => array('text', 'options_assume_real'), 'value' => array('clob', '0'), 'group_name' => array('text', 'options')));
	}

	//logic symbol
	if (!array_key_exists('options_logic_symbol', $existing_entries)) {
		//We have to store the id of the content style we want to use for stack feedback styles
		$db->insert('xqcas_configuration', array('parameter_name' => array('text', 'options_logic_symbol'), 'value' => array('clob', 'lang'), 'group_name' => array('text', 'options')));
	}

	//inputs
	$existing_entries = array();
	$result = $db->query('SELECT * FROM xqcas_configuration WHERE group_name = "inputs"');
	while ($row = $db->fetchAssoc($result)) {
		$existing_entries[$row['parameter_name']] = '';
	}

	//logic symbol
	if (!array_key_exists('input_syntax_attribute', $existing_entries)) {
		//We have to store the id of the content style we want to use for stack feedback styles
		$db->insert('xqcas_configuration', array('parameter_name' => array('text', 'input_syntax_attribute'), 'value' => array('clob', '0'), 'group_name' => array('text', 'inputs')));
	}
}
?>
<#42>
<?php
/*
 * New Test Seed Management
 */
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('xqcas_test_seeds')) {
	$fields = array('question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'active_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'pass' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
		'seed' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
		'stamp' => array('type' => 'integer', 'length' => 8, 'notnull' => true));
	$db->createTable('xqcas_test_seeds', $fields);
	$db->addPrimaryKey('xqcas_test_seeds', array('question_id', 'active_id', 'pass'));

	if (!$db->indexExistsByFields('xqcas_test_seeds', array('active_id', 'pass'))) {
		$db->addIndex('xqcas_test_seeds', array('active_id', 'pass'), 'ts1');
	}
	if (!$db->indexExistsByFields('xqcas_test_seeds', array('seed'))) {
		$db->addIndex('xqcas_test_seeds', array('seed'), 'ts2');
	}
	if (!$db->indexExistsByFields('xqcas_test_seeds', array('stamp'))) {
		$db->addIndex('xqcas_test_seeds', array('stamp'), 'ts3');
	}
}
?>
<#43>
<?php
global $DIC;
$db = $DIC->database();

//Create feedback styles
if ($db->tableExists('xqcas_configuration')) {
    $existing_entries = array();

    $result = $db->query('SELECT * FROM xqcas_configuration WHERE group_name = "display"');
    while ($row = $db->fetchAssoc($result)) {
        $existing_entries[$row["parameter_name"]] = "";
    }

    if (!array_key_exists("allow_jsx_graph", $existing_entries)) {
        $db->insert("xqcas_configuration", array('parameter_name' => array('text', "allow_jsx_graph"), 'value' => array('clob', ''), 'group_name' => array('text', 'display')));
    }
}
?>
<#44>
<?php
global $DIC;
$db = $DIC->database();

//Add question unit test data
if ($db->tableExists('xqcas_qtests')) {
    $seed_column = array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0);
    if (!$db->tableColumnExists("xqcas_qtests", "seed")) {
        $db->addTableColumn("xqcas_qtests", "seed", $seed_column);
    }
    $status_column = array('type' => 'text', 'length' => 32, 'notnull' => true);
    if (!$db->tableColumnExists("xqcas_qtests", "status")) {
        $db->addTableColumn("xqcas_qtests", "status", $status_column);
    }
    $data_column = array('type' => 'clob', 'notnull' => true);
    if (!$db->tableColumnExists("xqcas_qtests", "data")) {
        $db->addTableColumn("xqcas_qtests", "data", $data_column);
    }
}
?>
<#45>
<?php
global $DIC;
$db = $DIC->database();

//Add question deployed seed data
if ($db->tableExists('xqcas_deployed_seeds')) {
    $testing_status_column = array('type' => 'text', 'length' => 32, 'notnull' => true);
    if (!$db->tableColumnExists("xqcas_deployed_seeds", "testing_status")) {
        $db->addTableColumn("xqcas_deployed_seeds", "testing_status", $testing_status_column);
    }
    $data_column = array('type' => 'clob', 'notnull' => true);
    if (!$db->tableColumnExists("xqcas_deployed_seeds", "data")) {
        $db->addTableColumn("xqcas_deployed_seeds", "data", $data_column);
    }
    $active_column = array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0);
    if (!$db->tableColumnExists("xqcas_qtests", "active")) {
        $db->addTableColumn("xqcas_qtests", "active", $active_column);
    }
}
?>
<#46>
<?php
global $DIC;
$db = $DIC->database();

//Create feedback styles
if ($db->tableExists("xqcas_configuration")) {
    $existing_entries = array();

    $result = $db->query("SELECT * FROM xqcas_configuration");
    while ($row = $db->fetchAssoc($result)) {
        $existing_entries[] = $row["parameter_name"];
    }

    if (in_array("allow_jsx_graph", $existing_entries)) {
        $db->update("xqcas_configuration", array("value" => array("clob", "1")), array("parameter_name" => array("text", "allow_jsx_graph")));
    }

    if (!in_array("preparse_all", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "preparse_all"), "value" => array("clob", "1"), "group_name" => array("text", "common")));
    }
    if (!in_array("cache_parsed_expressions_longer_than", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "cache_parsed_expressions_longer_than"), "value" => array("clob", "50"), "group_name" => array("text", "common")));
    }
    if (!in_array("maxima_pool_url", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "maxima_pool_url"), "value" => array("clob", ""), "group_name" => array("text", "server")));
    }
    if (!in_array("maxima_pool_server_username_password", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "maxima_pool_server_username_password"), "value" => array("clob", ""), "group_name" => array("text", "server")));
    }
    if (!in_array("maxima_uses_proxy", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "maxima_uses_proxy"), "value" => array("clob", ""), "group_name" => array("text", "server")));
    }
    if (!in_array("question_level_simplify", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "question_level_simplify"), "value" => array("clob", "1"), "group_name" => array("text", "options")));
    }
    if (!in_array("assume_positive", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "assume_positive"), "value" => array("clob", ""), "group_name" => array("text", "options")));
    }
    if (!in_array("assume_real", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "assume_real"), "value" => array("clob", ""), "group_name" => array("text", "options")));
    }
    if (!in_array("feedback_fully_correct", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "feedback_fully_correct"), "value" => array("clob", "Correct answer, well done."), "group_name" => array("text", "options")));
    }
    if (!in_array("feedback_partially_correct", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "feedback_partially_correct"), "value" => array("clob", "Your answer is partially correct."), "group_name" => array("text", "options")));
    }
    if (!in_array("feedback_fully_incorrect", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "feedback_fully_incorrect"), "value" => array("clob", "Incorrect answer."), "group_name" => array("text", "options")));
    }
    if (!in_array("multiplication_sign", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "multiplication_sign"), "value" => array("clob", "dot"), "group_name" => array("text", "options")));
    }
    if (!in_array("surd_for_sqrt", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "surd_for_sqrt"), "value" => array("clob", "1"), "group_name" => array("text", "options")));
    }
    if (!in_array("complex_numbers", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "complex_numbers"), "value" => array("clob", "i"), "group_name" => array("text", "options")));
    }
    if (!in_array("inverse_trigonometric", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "inverse_trigonometric"), "value" => array("clob", "cos-1"), "group_name" => array("text", "options")));
    }
    if (!in_array("logic_symbols", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "logic_symbols"), "value" => array("clob", "lang"), "group_name" => array("text", "options")));
    }
    if (!in_array("matrix_parentheses", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "matrix_parentheses"), "value" => array("clob", "["), "group_name" => array("text", "options")));
    }
    if (!in_array("default_type", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "default_type"), "value" => array("clob", "algebraic"), "group_name" => array("text", "inputs")));
    }
    if (!in_array("box_size", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "box_size"), "value" => array("clob", "15"), "group_name" => array("text", "inputs")));
    }
    if (!in_array("strict_syntax", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "strict_syntax"), "value" => array("clob", "1"), "group_name" => array("text", "inputs")));
    }
    if (!in_array("insert_stars", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "insert_stars"), "value" => array("clob", "5"), "group_name" => array("text", "inputs")));
    }
    if (!in_array("forbidden_words", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "forbidden_words"), "value" => array("clob", ""), "group_name" => array("text", "inputs")));
    }
    if (!in_array("forbid_float", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "forbid_float"), "value" => array("clob", "1"), "group_name" => array("text", "inputs")));
    }
    if (!in_array("require_lowest_terms", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "require_lowest_terms"), "value" => array("clob", ""), "group_name" => array("text", "inputs")));
    }
    if (!in_array("check_answer_type", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "check_answer_type"), "value" => array("clob", ""), "group_name" => array("text", "inputs")));
    }
    if (!in_array("must_verify", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "must_verify"), "value" => array("clob", "1"), "group_name" => array("text", "inputs")));
    }
    if (!in_array("show_validation", $existing_entries)) {
        $db->insert("xqcas_configuration", array("parameter_name" => array("text", "show_validation"), "value" => array("clob", "1"), "group_name" => array("text", "inputs")));
    }
}
?>
<#47>
<?php
global $DIC;
$db = $DIC->database();

if ($db->tableExists('xqcas_options') && !$db->tableColumnExists("xqcas_options", "question_description")) {
    $db->addTableColumn("xqcas_options", "question_description", array('type' => 'clob', 'notnull' => false, 'default' => null));
    $db->addTableColumn("xqcas_options", "question_description_format", array('type' => 'integer', 'length' => 2, 'notnull' => false, 'default' => null));
}
?>
<#48>
<?php
global $DIC;
$db = $DIC->database();

if ($db->tableExists('xqcas_configuration') && $db->tableColumnExists("xqcas_configuration", "value")) {
    $db->update("xqcas_configuration", array("value" => array("clob", "linux")), array("parameter_name" => array("text", "platform_type"), "value" => array("clob", "unix")));
}
?>
<#49>
<?php
global $DIC;
$db = $DIC->database();

if ($db->tableExists('xqcas_prts') && !$db->tableColumnExists("xqcas_prts", "feedback_style")) {
    $db->addTableColumn("xqcas_prts", "feedback_style", array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 1));
}

if ($db->tableExists('xqcas_prt_nodes') && !$db->tableColumnExists("xqcas_prt_nodes", "description")) {
    $db->addTableColumn("xqcas_prt_nodes", "description", array('type' => 'text'));
}

if ($db->tableExists('xqcas_qtests')) {
    if (!$db->tableColumnExists("xqcas_qtests", "description")) {
        $db->addTableColumn("xqcas_qtests", "description", array('type' => 'text'));
    }
    if (!$db->tableColumnExists("xqcas_qtests", "time_modified")) {
        $db->addTableColumn("xqcas_qtests", "time_modified", array('type' => 'integer', 'length' => 8, 'notnull' => false, 'default' => null));
    }
}
?>
<#50>
<?php
global $DIC;
$db = $DIC->database();

if ($db->tableExists('xqcas_qtests')) {
    if ($db->tableColumnExists("xqcas_qtests", "seed")) {
        $db->dropTableColumn("xqcas_qtests", "seed");
    }

    if ($db->tableColumnExists("xqcas_qtests", "status")) {
        $db->dropTableColumn("xqcas_qtests", "status");
    }

    if ($db->tableColumnExists("xqcas_qtests", "data")) {
        $db->dropTableColumn("xqcas_qtests", "data");
    }

    if ($db->tableColumnExists("xqcas_qtests", "active")) {
        $db->dropTableColumn("xqcas_qtests", "active");
    }
}

if (!$db->tableExists("xqcas_qtest_results")) {
    $fields = array(
        'id' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'test_case' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'seed' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'result' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
        'timerun' => array('type' => 'integer', 'length' => 8, 'notnull' => true)
    );

    $db->createTable('xqcas_qtest_results', $fields);
    $db->createSequence("xqcas_qtest_results");
    $db->addPrimaryKey('xqcas_qtest_results', array('id'));
}
?>
<#51>
<?php
global $DIC;
$db = $DIC->database();

if (!$db->tableExists('xqcas_preview')) {
    $fields = array(
        'question_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'user_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'is_active' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'seed' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'stamp' => array('type' => 'integer', 'length' => 8, 'notnull' => true),
        'submitted_answer' => array('type' => 'clob', 'notnull' => true, 'default' => "{}")
    );

    $db->createTable('xqcas_preview', $fields);
    $db->addPrimaryKey('xqcas_preview', array('question_id', 'user_id', 'is_active'));

    if (!$db->indexExistsByFields('xqcas_preview', array('user_id', 'is_active'))) {
        $db->addIndex('xqcas_preview', array('user_id', 'is_active'), 'ts1');
    }
    if (!$db->indexExistsByFields('xqcas_preview', array('seed'))) {
        $db->addIndex('xqcas_preview', array('seed'), 'ts2');
    }
    if (!$db->indexExistsByFields('xqcas_preview', array('stamp'))) {
        $db->addIndex('xqcas_preview', array('stamp'), 'ts3');
    }
}
?>