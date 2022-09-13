<?php
/**
 * Copyright (c) 2022 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * STACK Question DB Manager Class
 * All DB Stuff is placed here
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id: 4.0$
 *
 */
class assStackQuestionDB
{

	/* READ QUESTION FROM DB BEGIN*/

	/**
	 * @param $question_id
	 * @param bool $just_id
	 * @return array|int
	 */
	public static function _readOptions($question_id, bool $just_id = false)
	{
		global $DIC;
		$db = $DIC->database();

		$query = /** @lang text */
			'SELECT * FROM xqcas_options WHERE question_id = ' . $db->quote($question_id, 'integer');
		$res = $db->query($query);
		$row = $db->fetchObject($res);

		//If there is a result returns object, otherwise returns false.
		if ($row) {
			include_once("./Services/RTE/classes/class.ilRTE.php");

			$options = array();
			$ilias_options = array();

			//Filling object with data from DB
			$ilias_options['id'] = ((int)$row->id);
			if ($just_id) {
				return $ilias_options['id'];
			}
			$ilias_options['question_id'] = ((int)$row->question_id);
			$ilias_options['question_variables'] = ($row->question_variables);
			$ilias_options['specific_feedback'] = (ilRTE::_replaceMediaObjectImageSrc($row->specific_feedback, 1));
			$ilias_options['specific_feedback_format'] = ((int)$row->specific_feedback_format);
			$ilias_options['question_note'] = ($row->question_note);
			$ilias_options['prt_correct'] = (ilRTE::_replaceMediaObjectImageSrc($row->prt_correct, 1));
			$ilias_options['prt_correct_format'] = ((int)$row->prt_correct_format);
			$ilias_options['prt_partially_correct'] = (ilRTE::_replaceMediaObjectImageSrc($row->prt_partially_correct, 1));
			$ilias_options['prt_partially_correct_format'] = ((int)$row->prt_partially_correct_format);
			$ilias_options['prt_incorrect'] = (ilRTE::_replaceMediaObjectImageSrc($row->prt_incorrect, 1));
			$ilias_options['prt_incorrect_format'] = ((int)$row->prt_incorrect_format);
			$ilias_options['variants_selection_seed'] = ($row->variants_selection_seed);
			//$ilias_options['stack_version'] = ($row->stack_version);

			$options['simplify'] = ((int)$row->question_simplify);
			$options['assumepos'] = ((int)$row->assume_positive);
			$options['multiplicationsign'] = ($row->multiplication_sign);
			$options['sqrtsign'] = ((int)$row->sqrt_sign);
			$options['complexno'] = ($row->complex_no);
			$options['inversetrig'] = ($row->inverse_trig);
			$options['matrixparens'] = ($row->matrix_parens);

			//$options['assumereal'] = ((int)$row->assume_real);
			//$options['logicsymbol'] = ((int)$row->logic_symbol);

			return array('options' => $options, 'ilias_options' => $ilias_options);
		} else {
			return -1;
		}
	}

	/**
	 * @param $question_id
	 * @param bool $just_id
	 * @return array
	 */
	public static function _readInputs($question_id, bool $just_id = false): array
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = /** @lang text */
			'SELECT * FROM xqcas_inputs WHERE question_id = ' . $db->quote($question_id, 'integer');
		$res = $db->query($query);

		$inputs = array();
		$ilias_inputs = array();
		$input_ids = array();

		while ($row = $db->fetchAssoc($res)) {

			$input_name = $row['name'];

			$ilias_inputs[$input_name]['id'] = (int)$row['id'];
			if ($just_id) {
				$input_ids[$input_name] = $ilias_inputs[$input_name]['id'];
			}
			$inputs[$input_name]['tans'] = $row['tans'];
			$inputs[$input_name]['name'] = $row['name'];
			$inputs[$input_name]['type'] = $row['type'];
			$inputs[$input_name]['box_size'] = $row['box_size'];
			$inputs[$input_name]['strict_syntax'] = $row['strict_syntax'];
			$inputs[$input_name]['insert_stars'] = (int)$row['insert_stars'];
			//$inputs[$input_name]['syntax_attribute'] = (isset($row['syntax_attribute']) and $row['syntax_attribute'] != null) ? trim($row['syntax_attribute']) : 0;
			$inputs[$input_name]['syntax_hint'] = (isset($row['syntax_hint']) and $row['syntax_hint'] != null) ? trim($row['syntax_hint']) : '';
			$inputs[$input_name]['forbid_words'] = $row['forbid_words'];
			$inputs[$input_name]['allow_words'] = $row['allow_words'];
			$inputs[$input_name]['forbid_float'] = (bool)$row['forbid_float'];
			$inputs[$input_name]['require_lowest_terms'] = (bool)$row['require_lowest_terms'];
			$inputs[$input_name]['check_answer_type'] = (bool)$row['check_answer_type'];
			$inputs[$input_name]['must_verify'] = (bool)$row['must_verify'];
			$inputs[$input_name]['show_validation'] = $row['show_validation'];
			$inputs[$input_name]['options'] = $row['options'];
		}

		if ($just_id) {
			return $input_ids;
		} else {
			return array('inputs' => $inputs, 'ilias_inputs' => $ilias_inputs);
		}
	}

	/**
	 * READS PRT AND PRT NODES FROM THE DB
	 * @param $question_id
	 * @param bool $just_id
	 * @return array
	 */
	public static function _readPRTs($question_id, bool $just_id = false): array
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = /** @lang text */
			'SELECT * FROM xqcas_prts WHERE question_id = ' . $db->quote($question_id, 'integer') . ' ORDER BY xqcas_prts.id';
		$res = $db->query($query);

		$potential_response_trees = array();
		$ilias_prts = array(); //Stores only ID Unused
		$prt_ids = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {

			$prt_name = $row['name'];

			$ilias_prts[$prt_name]['id'] = (int)$row['id'];
			if ($just_id) {
				$prt_ids[$prt_name]['prt_id'] = $ilias_prts[$prt_name]['id'];
			}

			$potential_response_trees[$prt_name]['value'] = $row['value'];
			$potential_response_trees[$prt_name]['auto_simplify'] = $row['auto_simplify'];
			$potential_response_trees[$prt_name]['feedback_variables'] = $row['feedback_variables'];
			$potential_response_trees[$prt_name]['first_node_name'] = $row['first_node_name'];

			//Reading nodes

			if ($just_id) {
				$prt_ids[$prt_name]['nodes'] = self::_readPRTNodes($question_id, $prt_name, true);
			} else {
				$potential_response_trees[$prt_name]['nodes'] = self::_readPRTNodes($question_id, $prt_name);
			}
		}
		if ($just_id) {
			return $prt_ids;
		} else {
			return $potential_response_trees;
		}
	}

	/**
	 * READS PRT NODES FROM DB
	 * This function is always called by _readPRTs()
	 * @param int $question_id
	 * @param string $prt_name
	 * @param bool $just_id
	 * @return array
	 */
	private static function _readPRTNodes(int $question_id, string $prt_name, bool $just_id = false): array
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = /** @lang text */
			'SELECT * FROM xqcas_prt_nodes WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND prt_name = ' . $db->quote($prt_name, 'text');
		$res = $db->query($query);

		$potential_response_tree_nodes = array();
		$ilias_prts_nodes = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {
			include_once("./Services/RTE/classes/class.ilRTE.php");

			$prt_node_name = $row['node_name'];
			$ilias_prts_nodes[$prt_node_name] = (int)$row['id'];

			$potential_response_tree_nodes[$prt_node_name]['true_next_node'] = $row['true_next_node'];
			$potential_response_tree_nodes[$prt_node_name]['false_next_node'] = $row['false_next_node'];
			$potential_response_tree_nodes[$prt_node_name]['answer_test'] = $row['answer_test'];
			$potential_response_tree_nodes[$prt_node_name]['sans'] = $row['sans'];
			$potential_response_tree_nodes[$prt_node_name]['tans'] = $row['tans'];
			$potential_response_tree_nodes[$prt_node_name]['test_options'] = $row['test_options'];
			$potential_response_tree_nodes[$prt_node_name]['quiet'] = (int)$row['quiet'];

			$potential_response_tree_nodes[$prt_node_name]['true_score'] = $row['true_score'];
			$potential_response_tree_nodes[$prt_node_name]['true_score_mode'] = $row['true_score_mode'];
			$potential_response_tree_nodes[$prt_node_name]['true_penalty'] = $row['true_penalty'];
			$potential_response_tree_nodes[$prt_node_name]['true_answer_note'] = $row['true_answer_note'];
			$potential_response_tree_nodes[$prt_node_name]['true_feedback'] = ilRTE::_replaceMediaObjectImageSrc($row['true_feedback'], 1);
			$potential_response_tree_nodes[$prt_node_name]['true_feedback_format'] = (int)$row['true_feedback_format'];

			$potential_response_tree_nodes[$prt_node_name]['false_score'] = $row['false_score'];
			$potential_response_tree_nodes[$prt_node_name]['false_score_mode'] = $row['false_score_mode'];
			$potential_response_tree_nodes[$prt_node_name]['false_penalty'] = $row['false_penalty'];
			$potential_response_tree_nodes[$prt_node_name]['false_answer_note'] = $row['false_answer_note'];
			$potential_response_tree_nodes[$prt_node_name]['false_feedback'] = ilRTE::_replaceMediaObjectImageSrc($row['false_feedback'], 1);
			$potential_response_tree_nodes[$prt_node_name]['false_feedback_format'] = (int)$row['false_feedback_format'];
		}

		if ($just_id) {
			return $ilias_prts_nodes;
		} else {
			return $potential_response_tree_nodes;
		}
	}

	/**
	 * READS DEPLOYED SEEDS FROM THE DB
	 * @param $question_id
	 * @param bool $seeds_as_keys
	 * @return array
	 */
	public static function _readDeployedVariants($question_id, bool $seeds_as_keys = false): array
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = /** @lang text */
			'SELECT * FROM xqcas_deployed_seeds WHERE question_id = ' . $db->quote($question_id, 'integer');
		$res = $db->query($query);

		//Seeds array
		$variants = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {
			if ($seeds_as_keys) {
				$variants[(int)$row['seed']] = (int)$row['seed'];
			} else {
				$variants[(int)$row['id']] = (int)$row['seed'];
			}
		}

		return $variants;
	}

	/**
	 * READS EXTRA INFO FROM THE DB
	 * @param $question_id
	 * @param bool $just_id
	 * @return array|false|int
	 */
	public static function _readExtraInformation($question_id, bool $just_id = false)
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = /** @lang text */
			'SELECT * FROM xqcas_extra_info WHERE question_id = ' . $db->quote($question_id, 'integer');
		$res = $db->query($query);
		$row = $db->fetchObject($res);

		//Extra Info array
		$extra_info = array();

		if ($row) {

			$extra_info['id'] = (int)$row->id;
			if ($just_id) {
				return $extra_info['id'];
			}

			include_once("./Services/RTE/classes/class.ilRTE.php");

			$extra_info['general_feedback'] = ilRTE::_replaceMediaObjectImageSrc($row->general_feedback, 1);
			$extra_info['penalty'] = $row->penalty;
			$extra_info['hidden'] = $row->hidden;

			return $extra_info;
		} else {
			return false;
		}
	}


	/**
	 * READS UNIT TESTS, TEST INPUTS AND TEST EXPECTED FROM THE DB
	 * @param int $question_id
	 * @param bool $just_id
	 * @return array
	 */
	public static function _readUnitTests(int $question_id, bool $just_id = false): array
	{
		global $DIC;
		$db = $DIC->database();

		//Select tests query
		$query = /** @lang text */
			'SELECT * FROM xqcas_qtests WHERE question_id = ' . $db->quote($question_id, 'integer') . ' ORDER BY xqcas_qtests.id';
		$res = $db->query($query);

		$unit_tests = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {

			$testcase_name = (int)$row['test_case'];

			if ($just_id) {
				$unit_tests[$testcase_name] = (int)$row['id'];
			} else {
				$unit_tests['ids'][$testcase_name] = (int)$row['id'];
				$unit_tests['test_cases'][$testcase_name]['inputs'] = self::_readUnitTestInputs($question_id, $testcase_name);
				$unit_tests['test_cases'][$testcase_name]['expected'] = self::_readUnitTestExpected($question_id, $testcase_name);
			}
		}

		return $unit_tests;
	}

	/**
	 * @param int $question_id
	 * @param int $testcase_name
	 * @param bool $just_id
	 * @return array
	 */
	private static function _readUnitTestInputs(int $question_id, int $testcase_name, bool $just_id = false): array
	{
		global $DIC;
		$db = $DIC->database();

		//Select tests query
		$query = /** @lang text */
			'SELECT * FROM xqcas_qtest_inputs WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND test_case = ' . $db->quote((string)$testcase_name, 'text') . ' ORDER BY xqcas_qtest_inputs.test_case';
		$res = $db->query($query);

		$testcase_inputs = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {
			$input_name = (string)$row['input_name'];
			$value = (string)$row['value'];

			if ($just_id) {
				$testcase_inputs[$input_name] = (int)$row['id'];
			} else {
				$testcase_inputs[$input_name]['id'] = (int)$row['id'];
				$testcase_inputs[$input_name]['value'] = $value;
			}

		}

		return $testcase_inputs;
	}

	/**
	 * @param int $question_id
	 * @param int $testcase_name
	 * @param bool $just_id
	 * @return array
	 */
	private static function _readUnitTestExpected(int $question_id, int $testcase_name, bool $just_id = false): array
	{
		global $DIC;
		$db = $DIC->database();

		//Select tests query
		$query = /** @lang text */
			'SELECT * FROM xqcas_qtest_expected WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND test_case = ' . $db->quote((string)$testcase_name, 'text') . ' ORDER BY xqcas_qtest_expected.test_case';
		$res = $db->query($query);

		$testcase_expected = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {

			$prt_name = (string)$row['prt_name'];

			if ($just_id) {
				$testcase_expected[$prt_name] = (int)$row['id'];
			} else {
				$testcase_expected[$prt_name]['id'] = (int)$row['id'];
				$testcase_expected[$prt_name]['score'] = (string)$row['expected_score'];
				$testcase_expected[$prt_name]['penalty'] = (string)$row['expected_penalty'];
				$testcase_expected[$prt_name]['answer_note'] = (string)$row['expected_answer_note'];
			}

		}

		return $testcase_expected;
	}

}