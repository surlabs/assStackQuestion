<#1>
<?php
/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 *
 *
 * Database creation script.
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 *
 * $Id$
 */
/*
 * Create the new question type
 */

$res = $ilDB->queryF("SELECT * FROM qpl_qst_type WHERE type_tag = %s", array('text'), array('assStackQuestion')
);

if ($res->numRows() == 0)
{
    $res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
    $data = $ilDB->fetchAssoc($res);
    $max = $data["maxid"] + 1;

    $affectedRows = $ilDB->manipulateF("INSERT INTO qpl_qst_type (question_type_id, type_tag, plugin) VALUES (%s, %s, %s)", array("integer", "text", "integer"), array($max, 'assStackQuestion', 1)
    );
}
?>
<#2>
<?php
/*
 * STACK name: options "Stores the main options for each Stack question"
 */
if (!$ilDB->tableExists('xqcas_options'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_variables' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'specific_feedback' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'specific_feedback_format' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        ),
        'question_note' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'question_simplify' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'assume_positive' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'prt_correct' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'prt_correct_format' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        ),
        'prt_partially_correct' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'prt_partially_correct_format' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        ),
        'prt_incorrect' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'prt_incorrect_format' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        ),
        'multiplication_sign' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => true,
            'default' => 'dot'
        ),
        'sqrt_sign' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'complex_no' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => true,
            'default' => 'i'
        ),
        'inverse_trig' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => true,
            'default' => 'cos-1'
        ),
        'variants_selection_seed' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false,
            'default' => NULL
        )
    );
    $ilDB->createTable("xqcas_options", $fields);
    $ilDB->createSequence("xqcas_options");
    $ilDB->addPrimaryKey("xqcas_options", array("id"));

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
if (!$ilDB->tableExists('xqcas_inputs'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'type' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'tans' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'box_size' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 15
        ),
        'strict_syntax' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'insert_stars' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'syntax_hint' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'forbid_words' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'forbid_float' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'require_lowest_terms' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'check_answer_type' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'must_verify' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'show_validation' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'options' => array(
            'type' => 'clob',
            'notnull' => true
        )
    );
    $ilDB->createTable("xqcas_inputs", $fields);
    $ilDB->createSequence("xqcas_inputs");
    $ilDB->addPrimaryKey("xqcas_inputs", array("id"));

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
if (!$ilDB->tableExists('xqcas_prts'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 21,
            'notnull' => true
        ),
        'auto_simplify' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'feedback_variables' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'first_node_name' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => true
        )
    );
    $ilDB->createTable("xqcas_prts", $fields);
    $ilDB->createSequence("xqcas_prts");
    $ilDB->addPrimaryKey("xqcas_prts", array("id"));

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
if (!$ilDB->tableExists('xqcas_prt_nodes'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'prt_name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'node_name' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => true
        ),
        'answer_test' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'sans' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'tans' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'test_options' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'quiet' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'true_score_mode' => array(
            'type' => 'text',
            'length' => 4,
            'notnull' => true,
            'default' => '='
        ),
        'true_score' => array(
            'type' => 'text',
            'length' => 21,
            'notnull' => true
        ),
        'true_penalty' => array(
            'type' => 'text',
            'length' => 21,
            'notnull' => false,
            'default' => NULL
        ),
        'true_next_node' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => false,
            'default' => NULL
        ),
        'true_answer_note' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'true_feedback' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'true_feedback_format' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        ),
        'false_score_mode' => array(
            'type' => 'text',
            'length' => 4,
            'notnull' => true,
            'default' => '='
        ),
        'false_score' => array(
            'type' => 'text',
            'length' => 21,
            'notnull' => true
        ),
        'false_penalty' => array(
            'type' => 'text',
            'length' => 21,
            'notnull' => false,
            'default' => NULL
        ),
        'false_next_node' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => false,
            'default' => NULL
        ),
        'false_answer_note' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'false_feedback' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'false_feedback_format' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        )
    );
    $ilDB->createTable("xqcas_prt_nodes", $fields);
    $ilDB->createSequence("xqcas_prt_nodes");
    $ilDB->addPrimaryKey("xqcas_prt_nodes", array("id"));

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
if (!$ilDB->tableExists('xqcas_cas_cache'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'hash' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true
        ),
        'command' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'result' => array(
            'type' => 'clob',
            'notnull' => true
        )
    );
    $ilDB->createTable("xqcas_cas_cache", $fields);
    $ilDB->createSequence("xqcas_cas_cache");
    $ilDB->addPrimaryKey("xqcas_cas_cache", array("id"));

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
if (!$ilDB->tableExists('xqcas_qtests'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'test_case' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        )
    );
    $ilDB->createTable("xqcas_qtests", $fields);
    $ilDB->createSequence("xqcas_qtests");
    $ilDB->addPrimaryKey("xqcas_qtests", array("id"));

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
if (!$ilDB->tableExists('xqcas_qtest_inputs'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'test_case' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'input_name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        )
    );
    $ilDB->createTable("xqcas_qtest_inputs", $fields);
    $ilDB->createSequence("xqcas_qtest_inputs");
    $ilDB->addPrimaryKey("xqcas_qtest_inputs", array("id"));

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
if (!$ilDB->tableExists('xqcas_qtest_expected'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'test_case' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'prt_name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'expected_score' => array(
            'type' => 'text',
            'length' => 21,
            'notnull' => false,
            'default' => NULL
        ),
        'expected_penalty' => array(
            'type' => 'text',
            'length' => 21,
            'notnull' => false,
            'default' => NULL
        ),
        'expected_answer_note' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        )
    );
    $ilDB->createTable("xqcas_qtest_expected", $fields);
    $ilDB->createSequence("xqcas_qtest_expected");
    $ilDB->addPrimaryKey("xqcas_qtest_expected", array("id"));

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
if (!$ilDB->tableExists('xqcas_deployed_seeds'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'seed' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        )
    );
    $ilDB->createTable("xqcas_deployed_seeds", $fields);
    $ilDB->createSequence("xqcas_deployed_seeds");
    $ilDB->addPrimaryKey("xqcas_deployed_seeds", array("id"));

    /*
     * 3 indexes to be created
     */
}
?>
<#11>
<#12>
<?php
$allow_words_column = array(
    'type' => 'text',
    'length' => 255,
    'notnull' => true
);
if (!$ilDB->tableColumnExists("xqcas_inputs", "allow_words"))
{
    $ilDB->addTableColumn("xqcas_inputs", "allow_words", $allow_words_column);
}
?>
<#13>
<#14>
<?php
if (!$ilDB->tableExists('xqcas_ilias_specific'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'question_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        'general_feedback' => array(
            'type' => 'clob'
        )
    );
    $ilDB->createTable("xqcas_ilias_specific", $fields);
    $ilDB->createSequence("xqcas_ilias_specific");
    $ilDB->addPrimaryKey("xqcas_ilias_specific", array("id"));
}
?>
<#15>
<#16>
<#17>
<?php
if (!$ilDB->tableExists('xqcas_configuration'))
{
	$fields = array(
		'parameter_name' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'value' => array(
			'type' => 'clob'
		),
		'group_name' => array(
			'type' => 'text',
			'length' => 255
		)
	);
	$ilDB->createTable("xqcas_configuration", $fields);
	$ilDB->addPrimaryKey("xqcas_configuration", array("parameter_name"));
}
?>
<#18>
<?php
//Check if connection entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "connection"';
$result = $ilDB->query($query);
if (!$ilDB->fetchAssoc($result)) {
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
		$ilDB->insert("xqcas_configuration",
			array(
				'parameter_name' => array('text', $paremeter_name),
				'value' => array('clob', $value),
				'group_name' => array('text', 'connection')
			));
	}
}

//Check if display entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "display"';
$result = $ilDB->query($query);
if (!$ilDB->fetchAssoc($result)) {
	$display_default_values = array(
		'instant_validation' => '0',
		'maths_filter' => 'mathjax',
		'replace_dollars' => '1'
	);
	foreach ($display_default_values as $paremeter_name => $value) {
		$ilDB->insert("xqcas_configuration",
			array(
				'parameter_name' => array('text', $paremeter_name),
				'value' => array('clob', $value),
				'group_name' => array('text', 'display')
			));
	}
}

//Check if default options entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "options"';
$result = $ilDB->query($query);
if (!$ilDB->fetchAssoc($result)) {
	$options_default_values = array(
		'options_question_simplify' => '1',
		'options_assume_positive' => '0',
		'options_prt_correct' => 'Correct answer, well done.',
		'options_prt_partially_correct' => 'Your answer is partially correct.',
		'options_prt_incorrect' => 'Incorrect answer.',
		'options_multiplication_sign' => 'dot',
		'options_sqrt_sign' => '1',
		'options_complex_numbers' => 'i',
		'options_inverse_trigonometric' => 'cos-1'
	);
	foreach ($options_default_values as $paremeter_name => $value) {
		$ilDB->insert("xqcas_configuration",
			array(
				'parameter_name' => array('text', $paremeter_name),
				'value' => array('clob', $value),
				'group_name' => array('text', 'options')
			));
	}
}


//Check if default input entries in DB have been created, otherwise create it.
$query = 'SELECT * FROM xqcas_configuration WHERE group_name = "inputs"';
$result = $ilDB->query($query);
if (!$ilDB->fetchAssoc($result)) {
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
	foreach ($inputs_default_values as $paremeter_name => $value) {
		$ilDB->insert("xqcas_configuration",
			array(
				'parameter_name' => array('text', $paremeter_name),
				'value' => array('clob', $value),
				'group_name' => array('text', 'inputs')
			));
	}
}

require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/model/configuration/class.assStackQuestionConfig.php');
$config = new assStackQuestionConfig();
$config->setDefaultSettingsForConnection();
?>
<#19>
<?php
global $ilDB;
if (!$ilDB->tableExists('xqcas_ilias_specific'))
{

//Inserting index
//Inputs
$ilDB->addIndex('xqcas_inputs', array('question_id','name'),'i1', FALSE);
//PRT Nodes
$ilDB->addIndex('xqcas_prt_nodes', array('question_id','prt_name', 'node_name'),'i2', FALSE);
//Cache
$ilDB->addIndex('xqcas_cas_cache', array('hash'),'i3', FALSE);
//Tests
$ilDB->addIndex('xqcas_qtest_inputs', array('question_id','test_case', 'input_name'),'i4', FALSE);
$ilDB->addIndex('xqcas_qtest_expected', array('question_id','test_case', 'prt_name'),'i5', FALSE);
//Seeds
$ilDB->addIndex('xqcas_deployed_seeds', array('question_id','seed'),'i6', FALSE);
}
?>
<#20>
<?php
//Adding extra fields in moodle XML
//Penalty
$penalty_column = array(
	'type' => 'text',
	'length' => 21
);
if (!$ilDB->tableColumnExists("xqcas_ilias_specific", "penalty"))
{
	$ilDB->addTableColumn("xqcas_ilias_specific", "penalty", $penalty_column);
}
//Hidden

$hidden_column = array(
	'type' => 'integer',
	'length' => 4
);
if (!$ilDB->tableColumnExists("xqcas_ilias_specific", "hídden"))
{
	$ilDB->addTableColumn("xqcas_ilias_specific", "hidden", $hidden_column);
}
?>
<#21>
<#22>
<#23>
<?php
global $ilDB;
//Change name to ilias_specific and sequence
if ($ilDB->tableExists('xqcas_ilias_specific'))
{
	$ilDB->dropTable("xqcas_ilias_specific", FALSE);
	$ilDB->dropTable("xqcas_ilias_specific_seq", FALSE);
}
if (!$ilDB->tableExists('xqcas_extra_info'))
{
	$fields = array(
		'id' => array(
			'type' => 'integer',
			'length' => 8,
			'notnull' => true
		),
		'question_id' => array(
			'type' => 'integer',
			'length' => 8,
			'notnull' => true
		),
		'general_feedback' => array(
			'type' => 'clob'
		),
		'penalty' => array(
			'type' => 'text',
			'length' => 21
		),
		'hidden' => array(
			'type' => 'integer',
			'length' => 4
		)
	);
	$ilDB->createTable("xqcas_extra_info", $fields);
	$ilDB->createSequence("xqcas_extra_info");
	$ilDB->addPrimaryKey("xqcas_extra_info", array("id"));
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

$res = $ilDB->queryF("SELECT * FROM qpl_qst_type WHERE type_tag = %s", array('text'), array('assCasQuestion')
);

if ($res->numRows() != 0)
{
	//Update the old plugin name
	$res = $ilDB->query("UPDATE qpl_qst_type SET type_tag = 'assStackQuestion' WHERE type_tag = 'assCasQuestion'");
	//Get last id
	$res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
	$data = $ilDB->fetchAssoc($res);
	$max = $data["maxid"];
	//Delete new plugin
	$res = $ilDB->query("DELETE FROM qpl_qst_type WHERE question_type_id = " . $max);
}
?>
<#28>
<?php
//Add matrix parens column for STACK 3.3
$matrix_parens = array(
	'type' => 'text',
	'length' => 8
);
if ($ilDB->tableExists('xqcas_options'))
{
	if (!$ilDB->tableColumnExists("xqcas_options", "matrix_parens"))
	{
		$ilDB->addTableColumn("xqcas_options", "matrix_parens", $matrix_parens);
	}
}
?>
<#29>
<?php
global $lng;
//Adding of all feedback placeholder in question specific feedback
if ($ilDB->tableExists('xqcas_options') AND $ilDB->tableExists('xqcas_prts'))
{
	$counter = 0;

	//Get specific feedback text and question_id
	$options_result = $ilDB->query("SELECT question_id, specific_feedback FROM xqcas_options");
	while ($options_row = $ilDB->fetchAssoc($options_result)) {
		$question_id = $options_row['question_id'];
		$specific_feedback_text = $options_row['specific_feedback'];

		//Get question text of those STACK questions
		$question_result = $ilDB->query("SELECT question_text FROM qpl_questions WHERE question_id = '" . $question_id . "'");
		$question_row = $ilDB->fetchAssoc($question_result);
		$question_text = $question_row['question_text'];

		//If no feedback placeholder in question text and specific_feedback
		if (!preg_match('/\[\[feedback:(.*?)\]\]/', $question_text) AND !preg_match('/\[\[feedback:(.*?)\]\]/', $specific_feedback_text)) {
			require_once('./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/model/ilias_object/class.assStackQuestionOptions.php');
			$options = assStackQuestionOptions::_read($question_id);

			//get PRT name
			$prt_results = $ilDB->query("SELECT name FROM xqcas_prts WHERE question_id = '" . $question_id . "'");
			while ($prt_row = $ilDB->fetchAssoc($prt_results)) {
				$specific_feedback_text .= "<p>[[feedback:";
				$specific_feedback_text .= $prt_row['name'];
				$specific_feedback_text .= "]]</p>";
			}

			//Add placeholder to specific_feedback
			$options->setSpecificFeedback($specific_feedback_text);
			$options->save();
			$counter++;
		}
	}
	ilUtil::sendInfo($lng->txt("qpl_qst_xqcas_questions_updated_new_feedback_system") . ": " . $counter. ". ". $lng->txt("qpl_qst_xqcas_questions_updated_new_feedback_system"));
}
?>