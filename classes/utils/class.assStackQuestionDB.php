<?php
declare(strict_types=1);
/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *  This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 *  originally created by Chris Sangwin.
 *
 *  The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "STACK Question" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/STACK
 *
 *  If you need support, please contact the maintainer of this software at:
 *  stack@surlabs.es
 *
 */

/**
 * STACK Question DB Manager Class
 * All DB Stuff is placed here
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
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
			//include_once("./Services/RTE/classes/class.ilRTE.php");

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
			$ilias_options['stack_version'] = ($row->stack_version);
			$ilias_options['compiled_cache'] = ($row->compiled_cache);

			$options['simplify'] = ((int)$row->question_simplify);
			$options['assumepos'] = ((int)$row->assume_positive);
			$options['multiplicationsign'] = ($row->multiplication_sign);
			$options['sqrtsign'] = ((int)$row->sqrt_sign);
			$options['complexno'] = ($row->complex_no);
			$options['inversetrig'] = ($row->inverse_trig);
			$options['matrixparens'] = ($row->matrix_parens);
			$options['assumereal'] = ((int)$row->assume_real);
			$options['logicsymbol'] = ((int)$row->logic_symbol);

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
			$inputs[$input_name]['syntax_attribute'] = (isset($row['syntax_attribute']) and $row['syntax_attribute'] != null) ? trim($row['syntax_attribute']) : 0;
			$inputs[$input_name]['syntax_hint'] = (isset($row['syntax_hint']) and $row['syntax_hint'] != null) ? trim($row['syntax_hint']) : '';
			$inputs[$input_name]['forbid_words'] = $row['forbid_words'];
			$inputs[$input_name]['allow_words'] = $row['allow_words'];
			$inputs[$input_name]['forbid_float'] = (bool)$row['forbid_float'];
			$inputs[$input_name]['require_lowest_terms'] = (bool)$row['require_lowest_terms'];
            // Bug in CAS causes wrong notes and feedback - so following (check_answer_type) effectively ignored HSLU
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

            $prt_ids[$prt_name] = new stdClass();
            $potential_response_trees[$prt_name] = new stdClass();

			$ilias_prts[$prt_name]['id'] = (int)$row['id'];
			if ($just_id) {
				$prt_ids[$prt_name]->prt_id = $ilias_prts[$prt_name]['id'];
			}

            $potential_response_trees[$prt_name]->name = $row['name'];
			$potential_response_trees[$prt_name]->value = (float)$row['value'];
			$potential_response_trees[$prt_name]->autosimplify = $row['auto_simplify'];
			$potential_response_trees[$prt_name]->feedbackvariables = $row['feedback_variables'];
			$potential_response_trees[$prt_name]->firstnodename = $row['first_node_name'];

			//Reading nodes

			if ($just_id) {
				$prt_ids[$prt_name]->nodes = self::_readPRTNodes((int)$question_id, $prt_name, true);
			} else {
				$potential_response_trees[$prt_name]->nodes = self::_readPRTNodes((int)$question_id, $prt_name);
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
			//include_once("./Services/RTE/classes/class.ilRTE.php");

			$prt_node_name = $row['node_name'];
			$ilias_prts_nodes[$prt_node_name] = (int)$row['id'];

            $potential_response_tree_nodes[$prt_node_name] = new stdClass();

            $potential_response_tree_nodes[$prt_node_name]->nodename = $row['node_name'];
			$potential_response_tree_nodes[$prt_node_name]->truenextnode = $row['true_next_node'];
			$potential_response_tree_nodes[$prt_node_name]->falsenextnode = $row['false_next_node'];
			$potential_response_tree_nodes[$prt_node_name]->answertest = $row['answer_test'];
			$potential_response_tree_nodes[$prt_node_name]->sans = $row['sans'];
			$potential_response_tree_nodes[$prt_node_name]->tans = $row['tans'];
			$potential_response_tree_nodes[$prt_node_name]->testoptions = $row['test_options'];
			$potential_response_tree_nodes[$prt_node_name]->quiet = (int)$row['quiet'];

			$potential_response_tree_nodes[$prt_node_name]->truescore = $row['true_score'];
			$potential_response_tree_nodes[$prt_node_name]->truescoremode = $row['true_score_mode'];
			$potential_response_tree_nodes[$prt_node_name]->truepenalty = $row['true_penalty'];
			$potential_response_tree_nodes[$prt_node_name]->trueanswernote = $row['true_answer_note'];
			$potential_response_tree_nodes[$prt_node_name]->truefeedback = ilRTE::_replaceMediaObjectImageSrc($row['true_feedback'], 1);
			$potential_response_tree_nodes[$prt_node_name]->truefeedbackformat = (int)$row['true_feedback_format'];

			$potential_response_tree_nodes[$prt_node_name]->falsescore = $row['false_score'];
			$potential_response_tree_nodes[$prt_node_name]->falsescoremode = $row['false_score_mode'];
			$potential_response_tree_nodes[$prt_node_name]->falsepenalty = $row['false_penalty'];
			$potential_response_tree_nodes[$prt_node_name]->falseanswernote = $row['false_answer_note'];
			$potential_response_tree_nodes[$prt_node_name]->falsefeedback = ilRTE::_replaceMediaObjectImageSrc($row['false_feedback'], 1);
			$potential_response_tree_nodes[$prt_node_name]->falsefeedbackformat = (int)$row['false_feedback_format'];
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

			//include_once("./Services/RTE/classes/class.ilRTE.php");

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
	public static function _readUnitTests($question_id, bool $just_id = false): array
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
                $unit_tests['test_cases'][$testcase_name]['description'] = $row['description'] ?? '';
                $unit_tests['test_cases'][$testcase_name]['time_modified'] = $row['time_modified'] ? (int) $row['time_modified'] : 0;
				$unit_tests['test_cases'][$testcase_name]['inputs'] = self::_readUnitTestInputs((int)$question_id, $testcase_name);
				$unit_tests['test_cases'][$testcase_name]['expected'] = self::_readUnitTestExpected((int)$question_id, $testcase_name);
                $unit_tests['test_cases'][$testcase_name]['results'] = self::_readUnitTestResults((int)$question_id, $testcase_name);
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

    /**
     * @param int $question_id
     * @param int $testcase_name
     * @param bool $just_id
     * @return array
     */
    private static function _readUnitTestResults(int $question_id, int $testcase_name, bool $just_id = false): array
    {
        global $DIC;
        $db = $DIC->database();

        //Select tests query
        $query = /** @lang text */
            'SELECT * FROM xqcas_qtest_results WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND test_case = ' . $db->quote((string)$testcase_name, 'text') . ' ORDER BY xqcas_qtest_results.test_case';
        $res = $db->query($query);

        $testcase_results = array();

        //If there is a result returns array, otherwise returns false.
        while ($row = $db->fetchAssoc($res)) {
            $seed = (int) $row['seed'];
            $result = (int) $row['result'];
            $timerun = (int) $row['timerun'];

            $testcase_results[] = array(
                'seed' => $seed,
                'result' => $result,
                'timerun' => $timerun
            );
        }

        return $testcase_results;
    }

	/* READ QUESTION FROM DB END */

	/* SAVE QUESTION INTO DB BEGIN */

	/**
	 * SAVES STACK QUESTION INTO THE DB
	 * Called from saveToDB()->saveAdditionalQuestionDataToDb();
	 * @param assStackQuestion $question
	 * @param string $purpose
	 * @return bool
	 * @throws stack_exception
	 */
	public static function _saveStackQuestion(assStackQuestion $question, string $purpose = ''): bool
	{
		//Save Options
		$options_saved = self::_saveStackOptions($question);

		//Save Inputs
		$inputs_saved = self::_saveStackInputs($question, $purpose);

		//Save Prts
		$prts_saved = self::_saveStackPRTs($question, $purpose);

		//Save Seeds
		$seeds_saved = self::_saveStackSeeds($question, $purpose);

		//Extra Info
		$extra_saved = self::_saveStackExtraInformation($question);

		//Unit Tests
		$unit_tests_saved = self::_saveStackUnitTests($question, $purpose);

		//Validate from form, popup errors
		if ($options_saved
			and $inputs_saved
			and $prts_saved
			and $seeds_saved
			and $extra_saved
			and $unit_tests_saved) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param assStackQuestion $question
	 * @return bool
	 * @throws stack_exception
	 */
	private static function _saveStackOptions(assStackQuestion $question): bool
	{
		global $DIC;
		$db = $DIC->database();
		//include_once("./Services/RTE/classes/class.ilRTE.php");

		$options_id = self::_readOptions($question->getId(), true);

		if ($options_id < 0) {
			//CREATE
			$db->insert("xqcas_options", array(
				"id" => array("integer", $db->nextId('xqcas_options')),
				"question_id" => array("integer", $question->getId()),
				"question_variables" => array("clob", $question->question_variables),
				"specific_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->specific_feedback)),
				"specific_feedback_format" => array("integer", 1),
				"question_note" => array("text", $question->question_note),
				"question_simplify" => array("integer", $question->options->get_option('simplify')),
				"assume_positive" => array("integer", $question->options->get_option('assumepos')),
				"prt_correct" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->prt_correct)),
				"prt_correct_format" => array("integer", 1),
				"prt_partially_correct" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->prt_partially_correct)),
				"prt_partially_correct_format" => array("integer", 1),
				"prt_incorrect" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->prt_incorrect)),
				"prt_incorrect_format" => array("integer", 1),
				"multiplication_sign" => array("text", $question->options->get_option('multiplicationsign') == null ? "dot" : $question->options->get_option('multiplicationsign')),
				"sqrt_sign" => array("integer", $question->options->get_option('sqrtsign')),
				"complex_no" => array("text", $question->options->get_option('complexno') == null ? "i" : $question->options->get_option('complexno')),
				"inverse_trig" => array("text", $question->options->get_option('inversetrig')),
				"variants_selection_seed" => array("text", $question->variants_selection_seed),
				"matrix_parens" => array("text", $question->options->get_option('matrixparens')),
				"assume_real" => array("integer", $question->options->get_option('assumereal')),
				"logic_symbol" => array("text", $question->options->get_option('logicsymbol')),
				"stack_version" => array("text", $question->stack_version)
			));
		} else {
			//UPDATE
			$db->replace('xqcas_options',
				array(
					"id" => array('integer', $options_id)),
				array(
					"question_id" => array("integer", $question->getId()),
					"question_variables" => array("clob", $question->question_variables),
					"specific_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->specific_feedback)),
					"specific_feedback_format" => array("integer", 1),
					"question_note" => array("text", $question->question_note),
					"question_simplify" => array("integer", $question->options->get_option('simplify')),
					"assume_positive" => array("integer", $question->options->get_option('assumepos')),
					"prt_correct" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->prt_correct)),
					"prt_correct_format" => array("integer", 1),
					"prt_partially_correct" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->prt_partially_correct)),
					"prt_partially_correct_format" => array("integer", 1),
					"prt_incorrect" => array("clob", ilRTE::_replaceMediaObjectImageSrc($question->prt_incorrect)),
					"prt_incorrect_format" => array("integer", 1),
					"multiplication_sign" => array("text", $question->options->get_option('multiplicationsign') == null ? "dot" : $question->options->get_option('multiplicationsign')),
					"sqrt_sign" => array("integer", $question->options->get_option('sqrtsign')),
					"complex_no" => array("text", $question->options->get_option('complexno') == null ? "i" : $question->options->get_option('complexno')),
					"inverse_trig" => array("text", $question->options->get_option('inversetrig')),
					"variants_selection_seed" => array("text", $question->variants_selection_seed),
					"matrix_parens" => array("text", $question->options->get_option('matrixparens')),
					"assume_real" => array("integer", $question->options->get_option('assumereal')),
					"logic_symbol" => array("text", $question->options->get_option('logicsymbol')),
					"stack_version" => array("text", $question->stack_version)
				));
		}
		return true;
	}

	/**
	 * @param assStackQuestion $question
	 * @param string $purpose
	 * @return bool
	 */
	private static function _saveStackInputs(assStackQuestion $question, string $purpose = ''): bool
	{
		global $DIC;
		$db = $DIC->database();

		$question_id = $question->getId();

		//Saves the current loaded inputs
		foreach ($question->inputs as $input_name => $input) {

			//Authoring interface saveToDB command
			$input_ids = self::_readInputs($question_id, true);

			if (!array_key_exists($input_name, $input_ids) or empty($input_ids) or $purpose == 'import') {
				//CREATE
				self::_saveInput((string)$question_id, $input);
			} else {
				//UPDATE
				$db->replace('xqcas_inputs',
					array(
						"id" => array('integer', $input_ids[$input_name])),
					array(
						"question_id" => array("integer", $question_id),
						"name" => array("text", $input->get_name()),
						"type" => array("text", assStackQuestionUtils::_getInputType($input)),
						"tans" => array("text", $input->get_teacher_answer() !== null ? $input->get_teacher_answer() : ''),
						"box_size" => array("integer", $input->get_parameter('boxWidth') !== null ? $input->get_parameter('boxWidth') : ''),
						"strict_syntax" => array("integer", $input->get_parameter('strictSyntax') !== null ? $input->get_parameter('strictSyntax') : ''),
						"insert_stars" => array("integer", $input->get_parameter('insertStars') !== null ? $input->get_parameter('insertStars') : ''),
						"syntax_hint" => array("text", $input->get_parameter('syntaxHint') !== null ? $input->get_parameter('syntaxHint') : ''),
						"syntax_attribute" => array("integer", $input->get_parameter('syntaxAttribute') !== null ? $input->get_parameter('syntaxAttribute') : ''),
						"forbid_words" => array("text", $input->get_parameter('forbidWords') !== null ? $input->get_parameter('forbidWords') : ''),
						"allow_words" => array("text", $input->get_parameter('allowWords') !== null ? $input->get_parameter('allowWords') : ''),
						"forbid_float" => array("integer", $input->get_parameter('forbidFloats') !== null ? $input->get_parameter('forbidFloats') : ''),
						"require_lowest_terms" => array("integer", $input->get_parameter('lowestTerms') !== null ? $input->get_parameter('lowestTerms') : ''),
						"check_answer_type" => array("integer", $input->get_parameter('sameType') !== null ? $input->get_parameter('sameType') : ''),
						"must_verify" => array("integer", $input->get_parameter('mustVerify') !== null ? $input->get_parameter('mustVerify') : ''),
						"show_validation" => array("integer", $input->get_parameter('showValidation') !== null ? $input->get_parameter('showValidation') : ''),
                        "options" => array("clob", $input->get_parameter('options') !== null ? $input->get_parameter('options') : ''),
					)
				);
			}

		}
		return true;
	}

	/**
	 * @param assStackQuestion $question
	 * @param string $purpose
	 * @return bool
	 */
	private static function _saveStackPRTs(assStackQuestion $question, string $purpose = ''): bool
	{
		global $DIC;
		$db = $DIC->database();

		$question_id = $question->getId();

		foreach ($question->prts as $prt_name => $prt) {

			$prt_ids = self::_readPRTs($question_id, true);

			if (!array_key_exists($prt_name, $prt_ids) or empty($prt_ids) or $purpose == 'import') {
				//IF a PRT doesn't exist in the question, if the there is no prts in the question, or if we are importing a question
				//CREATE
				$db->insert("xqcas_prts", array(
					"id" => array("integer", $db->nextId('xqcas_prts')),
					"question_id" => array("integer", $question_id),
					"name" => array("text", $question->prts[$prt_name]->get_name()),
					"value" => array("text", $question->prts[$prt_name]->get_value() == null ? "1.0" : $question->prts[$prt_name]->get_value()),
					"auto_simplify" => array("integer", $question->prts[$prt_name]->isSimplify() == null ? 0 : $question->prts[$prt_name]->isSimplify()),
					"feedback_variables" => array("clob", $question->prts[$prt_name]->get_feedbackvariables_keyvals() == null ? "" : $question->prts[$prt_name]->get_feedbackvariables_keyvals()),
					"first_node_name" => array("text", $question->prts[$prt_name]->get_first_node() == null ? '-1' : $question->prts[$prt_name]->get_first_node()),
				));

				//Insert nodes
				foreach ($prt->get_nodes() as $node) {
					self::_saveStackPRTNodes($node, (int)$question_id, $prt_name, -1);
				}

			} else {

				//UPDATE
				$db->replace('xqcas_prts',
					array(
						"id" => array('integer', $prt_ids[$prt_name]->prt_id)),
					array(
						"question_id" => array("integer", $question_id),
						"name" => array("text", $question->prts[$prt_name]->get_name()),
						"value" => array("text", $question->prts[$prt_name]->get_value() == null ? "1.0" : $question->prts[$prt_name]->get_value()),
						"auto_simplify" => array("integer", $question->prts[$prt_name]->isSimplify() == null ? 0 : $question->prts[$prt_name]->isSimplify()),
						"feedback_variables" => array("clob", $question->prts[$prt_name]->get_feedbackvariables_keyvals() == null ? "" : $question->prts[$prt_name]->get_feedbackvariables_keyvals()),
						"first_node_name" => array("text", $question->prts[$prt_name]->get_first_node() == null ? '-1' : $question->prts[$prt_name]->get_first_node()),
					)
				);

				//Update/Insert Nodes
				$prt_node_ids = self::_readPRTNodes((int)$question_id, $prt_name, true);

				foreach ($prt->get_nodes() as $node_name => $node) {
					if (!array_key_exists($node_name, $prt_node_ids) or empty($prt_node_ids)) {
						//CREATE
						self::_saveStackPRTNodes($node, (int)$question_id, $prt_name, -1);
					} else {
						//UPDATE
						if (isset($prt_ids[$prt_name]->nodes[$node_name])) {
							self::_saveStackPRTNodes($node, (int)$question_id, $prt_name, $prt_ids[$prt_name]->nodes[$node_name]);
						} else {
                            global $tpl;
                            $tpl->setOnScreenMessage('failure', 'question:' . (int)$question_id . $prt_name . $node_name, true);
						}
					}
				}
			}

		}
		return true;
	}

    /**
     * @param stdClass $node
     * @param int $question_id
     * @param string $prt_name
     * @param int $id
     */
	private static function _saveStackPRTNodes(stdClass $node, int $question_id, string $prt_name, int $id = -1): void
	{
		global $DIC;
		$db = $DIC->database();
		//include_once("./Services/RTE/classes/class.ilRTE.php");

		if ($id < 0) {
			//CREATE
			$db->insert("xqcas_prt_nodes", array(
				"id" => array("integer", $db->nextId('xqcas_prt_nodes')),
				"question_id" => array("integer", $question_id),
				"prt_name" => array("text", $prt_name),
				"node_name" => array("text", (string)$node->nodename),
				"answer_test" => array("text", $node->answertest),
				"sans" => array("text", $node->sans),
				"tans" => array("text", $node->tans),
				"test_options" => array("text", assStackQuestionUtils::_serializeExtraOptions($node->testoptions)),
				"quiet" => array("integer", $node->quiet),
				"true_score_mode" => array("text", $node->truescoremode),
                "true_score" => array("text", $node->truescore),
                "true_penalty" => array("text", $node->truepenalty),
                "true_next_node" => array("text", $node->truenextnode),
                "true_answer_note" => array("text", $node->trueanswernote),
                "true_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($node->truefeedback)),
                "true_feedback_format" => array("integer", (int)$node->truefeedbackformat),
                "false_score_mode" => array("text", $node->falsescoremode),
                "false_score" => array("text", $node->falsescore),
                "false_penalty" => array("text", $node->falsepenalty),
                "false_next_node" => array("text", $node->falsenextnode),
                "false_answer_note" => array("text", $node->falseanswernote),
                "false_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($node->falsefeedback)),
                "false_feedback_format" => array("integer", (int)$node->falsefeedbackformat),
			));
		} else {
			//UPDATE
			$db->replace('xqcas_prt_nodes',
				array(
					"id" => array('integer', $id)),
				array(
					"question_id" => array("integer", $question_id),
					"prt_name" => array("text", $prt_name),
					"node_name" => array("text", (string) $node->nodename),
					"answer_test" => array("text", $node->answertest),
					"sans" => array("text", $node->sans),
					"tans" => array("text", $node->tans),
					"test_options" => array("text", assStackQuestionUtils::_serializeExtraOptions($node->testoptions)),
					"quiet" => array("integer", $node->quiet),
					"true_score_mode" => array("text", $node->truescoremode),
                    "true_score" => array("text", $node->truescore),
                    "true_penalty" => array("text", $node->truepenalty),
                    "true_next_node" => array("text", $node->truenextnode),
                    "true_answer_note" => array("text", $node->trueanswernote),
                    "true_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($node->truefeedback)),
                    "true_feedback_format" => array("integer", (int)$node->truefeedbackformat),
                    "false_score_mode" => array("text", $node->falsescoremode),
                    "false_score" => array("text", $node->falsescore),
                    "false_penalty" => array("text", $node->falsepenalty),
                    "false_next_node" => array("text", $node->falsenextnode),
                    "false_answer_note" => array("text", $node->falseanswernote),
                    "false_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($node->falsefeedback)),
                    "false_feedback_format" => array("integer", (int)$node->falsefeedbackformat),
				)
			);
		}
	}

    /**
     * @param assStackQuestion $question
     * @param string $purpose
     * @param int|null $added_seed
     * @return bool
     */
	public static function _saveStackSeeds(assStackQuestion $question, string $purpose = '', int $added_seed = null): bool
	{
        global $DIC;
        $db = $DIC->database();

        $question_id = $question->getId();
        $deployed_seeds_from_db = self::_readDeployedVariants($question_id);

        //Avoid duplicated seed at saving questions
        if ($DIC->ctrl()->getCmd() != 'save') {
            if (!array_key_exists($added_seed, $deployed_seeds_from_db) and $purpose == 'add') {
                $db->insert(
                    'xqcas_deployed_seeds',
                    array(
                        'id' => array('integer', $db->nextId('xqcas_deployed_seeds')),
                        'question_id' => array('integer', $question_id),
                        'seed' => array('integer', $added_seed)
                    )
                );
            } else {
                //mass operations
                foreach ($question->deployed_seeds as $id => $seed) {
                    if (!array_key_exists(
                            $seed, $deployed_seeds_from_db
                        ) or empty($deployed_seeds_from_db) or $purpose == 'import') {
                        //create
                        $db->insert(
                            'xqcas_deployed_seeds',
                            array(
                                'id' => array('integer', $db->nextId('xqcas_deployed_seeds')),
                                'question_id' => array('integer', $question_id),
                                'seed' => array('integer', $seed)
                            )
                        );
                    } else {
                        //UPDATE
                        $db->replace(
                            'xqcas_deployed_seeds',
                            array('id' => array('integer', $id)),
                            array(
                                'question_id' => array('integer', $question_id),
                                'seed' => array('integer', $seed)
                            )
                        );
                    }
                }
            }
        }

		return true;
	}

	/**
	 * @param assStackQuestion $question
	 * @return bool
	 */
	private static function _saveStackExtraInformation(assStackQuestion $question): bool
	{
		global $DIC;
		$db = $DIC->database();

		$question_id = $question->getId();
		$extra_info_from_db = self::_readExtraInformation($question_id);

		if (is_array($extra_info_from_db) and !empty($extra_info_from_db)) {
			//UPDATE
			$db->replace('xqcas_extra_info',
				array('id' => array('integer', $extra_info_from_db['id'])),
				array(
					'question_id' => array('integer', $question_id),
					'general_feedback' => array('clob', $question->general_feedback),
					'penalty' => array('string', (string)$question->getPenalty()),
					'hidden' => array('integer', $question->getHidden())
				));
		} else {
			//CREATE
			$db->insert('xqcas_extra_info',
				array('id' => array('integer', $db->nextId('xqcas_extra_info')),
					'question_id' => array('integer', $question_id),
					'general_feedback' => array('clob', $question->general_feedback),
					'penalty' => array('string', (string)$question->getPenalty()),
					'hidden' => array('integer', $question->getHidden())
				));
		}
		return true;
	}

	public static function _saveStackUnitTests(assStackQuestion $question, string $purpose): bool
	{
		global $DIC;
		$db = $DIC->database();

		$question_id = $question->getId();

		if (isset($question->getUnitTests()['test_cases'])) {
			foreach ($question->getUnitTests()['test_cases'] as $testcase_name => $test_case) {

				$testcases_ids = self::_readUnitTests($question_id, true);

				if (!array_key_exists($testcase_name, $testcases_ids) or empty($testcases_ids) or $purpose == 'import') {

					//CREATE Test Case
					$db->insert("xqcas_qtests", array(
						"id" => array("integer", $db->nextId('xqcas_qtests')),
						"question_id" => array("integer", $question_id),
						"test_case" => array("integer", $testcase_name),
                        "description" => array("text", $test_case['description']),
                        "time_modified" => array("integer", time())
					));

					//Create Unit Tests Input
					foreach ($test_case['inputs'] as $input_name => $input) {
						self::_saveStackUnitTestInput((int)$question_id, $testcase_name, $input_name, $input['value'], -1);
					}

					//Create Unit Tests Expected
					foreach ($test_case['expected'] as $prt_name => $expected) {
						self::_saveStackUnitTestExpected((int)$question_id, $testcase_name, $prt_name, $expected, -1);
					}


				} else {
					//UPDATE
					$db->replace('xqcas_qtests',
						array(
							"id" => array('integer', $testcases_ids[$testcase_name])),
						array(
							"question_id" => array("integer", $question_id),
							"test_case" => array("integer", $testcase_name),
                            "description" => array("text", $test_case['description']),
                            "time_modified" => array("integer", time())
						)
					);

					//Manage Unit Tests Input
					$testcase_input_ids = self::_readUnitTestInputs((int)$question_id, $testcase_name, true);

					foreach ($test_case['inputs'] as $input_name => $input) {
						if (!array_key_exists($input_name, $testcase_input_ids) or empty($testcase_input_ids)) {
							//CREATE
							self::_saveStackUnitTestInput((int)$question_id, $testcase_name, $input_name, $input['value'], -1);
						} else {
							//UPDATE
							if (isset($input['value'])) {
								self::_saveStackUnitTestInput((int)$question_id, $testcase_name, $input_name, $input['value'], $testcase_input_ids[$input_name]);
							} else {
                                global $tpl;
                                $tpl->setOnScreenMessage('failure', 'question test inputs:' . $question_id . $testcase_name . $input_name, true);
							}
						}
					}

					//Manage Unit Tests Expected
					$testcase_expected_ids = self::_readUnitTestExpected($question_id, $testcase_name, true);

					foreach ($test_case['expected'] as $prt_name => $expected) {
						if (!array_key_exists($prt_name, $testcase_expected_ids) or empty($testcase_expected_ids)) {
							//CREATE
							self::_saveStackUnitTestExpected($question_id, $testcase_name, $prt_name, $expected, -1);
						} else {
							//UPDATE
							if (isset($expected['score']) and isset($expected['penalty']) and isset($expected['answer_note'])) {
								self::_saveStackUnitTestExpected($question_id, $testcase_name, $prt_name, $expected, $testcase_expected_ids[$prt_name]);
							} else {
                                global $tpl;
                                $tpl->setOnScreenMessage('failure', 'question test expected:' . $question_id . $testcase_name . $prt_name, true);
							}
						}
					}


				}
			}
		}

		return true;
	}

	/**
	 * @param int $question_id
	 * @param int $testcase_name
	 * @param string $input_name
	 * @param string $input_value
	 * @param int $id
	 * @return void
	 */
	private static function _saveStackUnitTestInput(int $question_id, int $testcase_name, string $input_name, string $input_value, int $id): void
	{
		global $DIC;
		$db = $DIC->database();

		if ($id < 0) {
			//CREATE
			$db->insert("xqcas_qtest_inputs", array(
				"id" => array("integer", $db->nextId('xqcas_qtest_inputs')),
				"question_id" => array("integer", $question_id),
				"test_case" => array("integer", $testcase_name),
				"input_name" => array("text", $input_name),
				"value" => array("text", $input_value)
			));
		} else {
			//UPDATE
			$db->replace('xqcas_qtest_inputs',
				array(
					"id" => array('integer', $id)),
				array(
					"question_id" => array("integer", $question_id),
					"test_case" => array("integer", $testcase_name),
					"input_name" => array("text", $input_name),
					"value" => array("text", $input_value)
				)
			);
		}
	}

	/**
	 * @param int $question_id
	 * @param int $testcase_name
	 * @param string $prt_name
	 * @param array $expected
	 * @param int $id
	 * @return void
	 */
	private static function _saveStackUnitTestExpected(int $question_id, int $testcase_name, string $prt_name, array $expected, int $id): void
	{
		global $DIC;
		$db = $DIC->database();

		if ($id < 0) {
			//CREATE
			$db->insert("xqcas_qtest_expected", array(
				"id" => array("integer", $db->nextId('xqcas_qtest_expected')),
				"question_id" => array("integer", $question_id),
				"test_case" => array("integer", $testcase_name),
				"prt_name" => array("text", $prt_name),
				"expected_score" => array("text", $expected['score']),
				"expected_penalty" => array("text", $expected['penalty']),
				"expected_answer_note" => array("text", $expected['answer_note'])
			));
		} else {
			//UPDATE
			$db->replace('xqcas_qtest_expected',
				array(
					"id" => array('integer', $id)),
				array(
					"question_id" => array("integer", $question_id),
					"test_case" => array("integer", $testcase_name),
					"prt_name" => array("text", $prt_name),
					"expected_score" => array("text", $expected['score']),
					"expected_penalty" => array("text", $expected['penalty']),
					"expected_answer_note" => array("text", $expected['answer_note'])
				)
			);
		}
	}

	/**
	 *
	 * @param string $question_id
	 * @param stack_input $input
	 * @return bool
	 */
	public static function _saveInput(string $question_id, stack_input $input): bool
	{
		global $DIC;
		$db = $DIC->database();

		//CREATE
		$db->insert("xqcas_inputs", array(
			"id" => array("integer", $db->nextId('xqcas_inputs')),
			"question_id" => array("integer", $question_id),
			"name" => array("text", $input->get_name()),
			"type" => array("text", assStackQuestionUtils::_getInputType($input)),
			"tans" => array("text", $input->get_teacher_answer() !== null ? $input->get_teacher_answer() : ''),
			"box_size" => array("integer", $input->get_parameter('boxWidth') !== null ? $input->get_parameter('boxWidth') : ''),
			"strict_syntax" => array("integer", $input->get_parameter('strictSyntax') !== null ? $input->get_parameter('strictSyntax') : ''),
			"insert_stars" => array("integer", $input->get_parameter('insertStars') !== null ? $input->get_parameter('insertStars') : ''),
			"syntax_hint" => array("text", $input->get_parameter('syntaxHint') !== null ? $input->get_parameter('syntaxHint') : ''),
			"syntax_attribute" => array("integer", $input->get_parameter('syntaxAttribute') !== null ? $input->get_parameter('syntaxAttribute') : ''),
			"forbid_words" => array("text", $input->get_parameter('forbidWords') !== null ? $input->get_parameter('forbidWords') : ''),
			"allow_words" => array("text", $input->get_parameter('allowWords') !== null ? $input->get_parameter('allowWords') : ''),
			"forbid_float" => array("integer", $input->get_parameter('forbidFloats') !== null ? $input->get_parameter('forbidFloats') : ''),
			"require_lowest_terms" => array("integer", $input->get_parameter('lowestTerms') !== null ? $input->get_parameter('lowestTerms') : ''),
			"check_answer_type" => array("integer", $input->get_parameter('sameType') !== null ? $input->get_parameter('sameType') : ''),
			"must_verify" => array("integer", $input->get_parameter('mustVerify') !== null ? $input->get_parameter('mustVerify') : ''),
			"show_validation" => array("integer", $input->get_parameter('showValidation') !== null ? $input->get_parameter('showValidation') : ''),
			"options" => array("clob", assStackQuestionUtils::_serializeExtraOptions($input->get_extra_options()) !== null ? assStackQuestionUtils::_serializeExtraOptions($input->get_extra_options()) : ''),
		));

		return true;
	}

	/* SAVE QUESTION INTO DB END */

	/* DELETE QUESTION IN DB BEGIN */

	/**
	 * @param int $question_id
	 * @return bool
	 */
	public static function _deleteStackQuestion(int $question_id): bool
	{
		$options = self::_deleteStackOptions($question_id);

		$inputs = self::_deleteStackInputs($question_id);

		$prts = self::_deleteStackPrts($question_id);

		$seeds = self::_deleteStackSeeds($question_id);

		$extra = self::_deleteStackExtraInfo($question_id);

		$unit_tests = self::_deleteStackUnitTests($question_id);

		return $options and $inputs and $prts and $seeds and $extra and $unit_tests;
	}

	/**
	 * @param int $question_id
	 * @return bool
	 */
	private static function _deleteStackOptions(int $question_id): bool
	{
		global $DIC;
		$db = $DIC->database();
		$query = /** @lang text */
			'DELETE FROM xqcas_options WHERE question_id = ' . $db->quote($question_id, 'integer');
		if ($db->manipulate($query) != false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param int $question_id
	 * @param string $input_name
	 * @return bool
	 */
	private static function _deleteStackInputs(int $question_id, string $input_name = ''): bool
	{
		global $DIC;
		$db = $DIC->database();
		if ($input_name == '') {
			//delete all inputs
			$query = /** @lang text */
				'DELETE FROM xqcas_inputs WHERE question_id = ' . $db->quote($question_id, 'integer');
		} else {
			//delete only $input_name
			$query = /** @lang text */
				'DELETE FROM xqcas_inputs WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND name = ' . $db->quote($input_name, 'text');
		}
		if ($db->manipulate($query) != false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param int $question_id
	 * @param string $prt_name
	 * @return bool
	 */
	private static function _deleteStackPrts(int $question_id, string $prt_name = ''): bool
	{
		global $DIC;
		$db = $DIC->database();
		if ($prt_name == '') {
			//delete all prts
			$query = /** @lang text */
				'DELETE FROM xqcas_prts WHERE question_id = ' . $db->quote($question_id, 'integer');
			$prts_deleted = $db->manipulate($query);
			//delete all nodes in question
			$nodes_deleted = self::_deleteStackPrtNodes($question_id);
		} else {
			//delete only $prt_name
			$query = /** @lang text */
				'DELETE FROM xqcas_prts WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND name = ' . $db->quote($prt_name, 'text');
			$prts_deleted = $db->manipulate($query);
			//delete nodes on that tree
			$nodes_deleted = self::_deleteStackPrtNodes($question_id, $prt_name);
		}

		if ($prts_deleted and $nodes_deleted) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param int $question_id
	 * @param string $prt_name
	 * @param string $node_name
	 * @return bool
	 */
	public static function _deleteStackPrtNodes(int $question_id, string $prt_name = '', string $node_name = ''): bool
	{
		global $DIC;
		$db = $DIC->database();
		if ($prt_name == '') {
			//delete all nodes of the question
			$query = /** @lang text */
				'DELETE FROM xqcas_prt_nodes WHERE question_id = ' . $db->quote($question_id, 'integer');
		} else {
			if ($node_name == '') {
				//delete all nodes from the prt $prt_name
				$query = /** @lang text */
					'DELETE FROM xqcas_prt_nodes WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND prt_name = ' . $db->quote($prt_name, 'text');
			} else {
				//delete only $node_name from prt $prt_name
				$query = /** @lang text */
					'DELETE FROM xqcas_prt_nodes WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND prt_name = ' . $db->quote($prt_name, 'text') . ' AND node_name = ' . $db->quote($node_name, 'text');
			}
		}
		if ($db->manipulate($query) != false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param int $question_id
	 * @param string $seed_id
	 * @return bool
	 */
	public static function _deleteStackSeeds(int $question_id, string $seed_id = '', int $delete_seed = null): bool
	{
		global $DIC;
		$db = $DIC->database();
        if ($delete_seed !== null) {
            //delete only seed name in that question
            $query = /** @lang text */
                'DELETE FROM xqcas_deployed_seeds WHERE question_id = ' . $db->quote($question_id, 'integer').' and seed = '. $db->quote($delete_seed, 'integer');

        } elseif ($seed_id == '') {
			//delete all seeds of the question
			$query = /** @lang text */
				'DELETE FROM xqcas_deployed_seeds WHERE question_id = ' . $db->quote($question_id, 'integer');
		} else {
			//delete only $seed_id
			$query = /** @lang text */
				'DELETE FROM xqcas_deployed_seeds WHERE id = ' . $db->quote($seed_id, 'integer');
		}
		if ($db->manipulate($query) != false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param int $question_id
	 * @return bool
	 */
	private static function _deleteStackExtraInfo(int $question_id): bool
	{
		global $DIC;
		$db = $DIC->database();
		//delete all seeds of the question
		$query = /** @lang text */
			'DELETE FROM xqcas_extra_info WHERE question_id = ' . $db->quote($question_id, 'integer');

		if ($db->manipulate($query) != false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param int $question_id
	 * @return bool
	 */
	public static function _deleteStackUnitTests(int $question_id, ?int $test_case = null): bool
	{
		global $DIC;
		$db = $DIC->database();

		$query = /** @lang text */
			'DELETE FROM xqcas_qtests WHERE question_id = ' . $db->quote($question_id, 'integer');

        if ($test_case !== null) {
            $query .= ' AND test_case = ' . $db->quote($test_case, 'integer');
        }

		if ($db->manipulate($query) != false) {
			return self::_deleteStackUnitTestInputs($question_id, $test_case) and self::_deleteStackUnitTestExpected($question_id, $test_case);
		} else {
			return false;
		}
	}

	/**
	 * @param int $question_id
	 * @return bool
	 */
	public static function _deleteStackUnitTestInputs(int $question_id, ?int $test_case = null): bool
    {
        global $DIC;
        $db = $DIC->database();

        $query = /** @lang text */
            'DELETE FROM xqcas_qtest_inputs WHERE question_id = ' . $db->quote($question_id, 'integer');

        if ($test_case !== null) {
            $query .= ' AND test_case = ' . $db->quote($test_case, 'integer');
        }

        if ($db->manipulate($query) != false) {
            return true;
        } else {
            return false;
        }
    }

	/**
	 * @param int $question_id
	 * @return bool
	 */
	public static function _deleteStackUnitTestExpected(int $question_id, ?int $test_case = null): bool
    {
        global $DIC;
        $db = $DIC->database();

        $query = /** @lang text */
            'DELETE FROM xqcas_qtest_expected WHERE question_id = ' . $db->quote($question_id, 'integer');

        if ($test_case !== null) {
            $query .= ' AND test_case = ' . $db->quote($test_case, 'integer');
        }

        if ($db->manipulate($query) != false) {
            return true;
        } else {
            return false;
        }
    }

	/* DELETE QUESTION IN DB END */

    /**
     * @param string $purpose
     * @param assStackQuestion $question
     * @param int $id Is the active_id for test or the user_id for preview
     * @param int|null $pass
     * @return int
     */
    public static function _getSeed(string $purpose, assStackQuestion $question, int $id, ?int $pass = 0) :int {
        if ($purpose == 'test') {
            return self::_getSeedForTestPass($question, $id, $pass);
        } elseif ($purpose == 'preview') {
            return self::_getSeedForPreview($question, $id);
        } else {
            return 0;
        }
    }

	/**
     * Get the seed for a test pass
     *
	 * @param assStackQuestion $question
	 * @param int $active_id
	 * @param int $pass
	 * @return int
	 */
	public static function _getSeedForTestPass(assStackQuestion $question, int $active_id, ?int $pass = null): int
	{
		$seed = 0;
		//Does this question uses randomisation?
		global $DIC;
		$db = $DIC->database();
		$question_id = $question->getId();
        if(is_null($pass)) {
            $pass = $question->getPass();
        }
		//Search for a seed in DB
		//returns seed if exists
		$query = /** @lang text */
			'SELECT seed FROM xqcas_test_seeds WHERE question_id = '
			. $db->quote($question_id, 'integer') . ' AND active_id = '
			. $db->quote($active_id, 'integer') . ' AND pass = '
			. $db->quote($pass, 'integer') . ' ORDER BY xqcas_test_seeds.stamp';

		$res = $db->query($query);
		if (isset($res)) if (!empty($res)) {
			$seed_found = 0;
			while ($row = $db->fetchAssoc($res)) {
				//set actual seed stored in DB
				$seed = (int)$row['seed'];
				if ($seed_found === 0) {
					$seed_found = $seed;
				} else {
                    echo "ERROR: Trying to create a new seed where there is already one assigned";
                    echo "<br>question_id: ".$question_id;
                    echo "<br>active_id: ".$active_id;
                    echo "<br>pass: ".$pass;
                    echo "<br>seed: ".$seed;
                    echo "<br>seed_found: ".$seed_found;
                    exit;
				}
			}
		}

		if ($seed <= 0) {
			//Create new seed
			$variants = self::_readDeployedVariants($question_id, true);

			//If there are variants
			if (!empty($variants)) {
				//Choose between deployed seeds
				$chosen_seed = array_rand($variants);
				//Set random selected seed
				$seed = (int)$chosen_seed;
			} else {
				//Complete randomisation
				if ($question->hasRandomVariants()) {
					$seed = rand(1111111111, 9999999999);
				} else {
					$seed = 1;
				}
			}

			//Save into xqcas_test_seeds
			$db->insert("xqcas_test_seeds", array(
				'question_id' => array('integer', $question_id),
				'active_id' => array('integer', $active_id),
				'pass' => array('integer', $pass),
				'seed' => array('integer', $seed),
				'stamp' => array('integer', time())));
		}

		return $seed;
	}

    /**
     * Get the seed for a preview
     *
     * @param assStackQuestion $question
     * @param int $active_id
     * @param int $pass
     * @return int
     */
    public static function _getSeedForPreview(assStackQuestion $question, int $user_id): int
    {
        $seed = 0;

        //Does this question uses randomisation?
        global $DIC;
        $db = $DIC->database();
        $question_id = $question->getId();
        //Search for a seed in DB
        //returns seed if exists
        $query = /** @lang text */
            'SELECT seed FROM xqcas_preview WHERE question_id = ' . $db->quote($question_id, 'integer')
            . ' AND user_id = ' . $db->quote($user_id, 'integer')
            . ' AND is_active = ' . $db->quote(1, 'integer')
            . ' ORDER BY xqcas_preview.stamp';

        $res = $db->query($query);
        if (isset($res)) if (!empty($res)) {
            $seed_found = 0;
            while ($row = $db->fetchAssoc($res)) {
                //set actual seed stored in DB
                $seed = (int)$row['seed'];
                if ($seed_found === 0) {
                    $seed_found = $seed;
                } else {
                    global $tpl;
                    $tpl->setOnScreenMessage('failure', "ERROR: Trying to create a new seed where there is already one assigned", true);
                    return 0;
                }
            }
        }

        if ($seed < 1) {
            //Create new seed
            $variants = self::_readDeployedVariants($question_id, true);

            //If there are variants
            if (!empty($variants)) {
                //Choose between deployed seeds
                $chosen_seed = array_rand($variants);
                //Set random selected seed
                $seed = (int)$chosen_seed;
            } else {
                //Complete randomisation
                if ($question->hasRandomVariants()) {
                    $seed = rand(1111111111, 9999999999);
                } else {
                    $seed = 1;
                }
            }

            //Deactivate previous seeds
            $db->update("xqcas_preview",
                array(
                    'is_active' => array('integer', 0)
                ),
                array(
                    'question_id' => array('integer', $question_id),
                    'user_id' => array('integer', $user_id),
                    'is_active' => array('integer', 1)
                )
            );

            //Save into xqcas_preview
            $db->insert("xqcas_preview", array(
                'question_id' => array('integer', $question_id),
                'user_id' => array('integer', $user_id),
                'is_active' => array('integer', 1),
                'seed' => array('integer', $seed),
                'stamp' => array('integer', time())));
        }

        return $seed;
    }

	/**
	 * @param assStackQuestion $question
	 * @param int $active_id
	 * @param int $pass
	 * @param bool $authorized
	 * @return int
	 */
	public static function _saveUserTestSolution(assStackQuestion $question, int $active_id, int $pass, bool $authorized): int
	{
        global $tpl;
        $raw_solution = array();

        //Save question text
        $raw_solution["question_text"] = $question->question_text_instantiated->get_rendered();
        //Save question note
        $raw_solution["question_note"] = $question->getQuestionSummary();
        //Save general feedback
        $raw_solution["general_feedback"] = $question->general_feedback;
        //Save Seed
        $raw_solution["question_seed"] = $question->seed;

        //Save inputs
        $raw_solution["inputs"] = array();

        foreach ($question->getEvaluation()['inputs']['states'] as $input_name => $input_state) {
            $raw_input = array();

            //Ensure only input data is stored
            if (array_key_exists($input_name, $question->inputs)) {
                if (isset($question->inputs[$input_name]) && is_a($question->inputs[$input_name], "stack_notes_input")) {
                    $raw_input["value"] = $input_state->__get("contents")[0];
                } else {
                    $raw_input["value"] = $input_state->contentsmodified;
                }
            }

            $raw_input["display"] = $input_state->contentsdisplayed;

            if (isset($question->getEvaluation()['inputs']['validation'][$input_name])) {
                $raw_input["validation_display"] = $question->getEvaluation()['inputs']['validation'][$input_name];
            }

            try {
                $raw_input["correct_value"] = $question->getTas($input_name)->get_value();

                $raw_input["correct_display"] = $question->getTas($input_name)->get_display();
            } catch (stack_exception $e) {
                $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            }

            $raw_solution["inputs"][$input_name] = $raw_input;
        }

        //Save prts
        $raw_solution["prts"] = array();

        if (isset($question->getEvaluation()['prts'])) {
            foreach ($question->getEvaluation()['prts'] as $prt_name => $prt) {
                $raw_prt = array();

                $raw_prt["name"] = $prt_name;

                $raw_prt["points"] = $question->getEvaluation()['points'][$prt_name]['prt_points'];

                $raw_prt["errors"] = $prt["prt_result"]->get_errors();

                $raw_prt["feedback"] = $prt["prt_result"]->get_feedback();

                $obtained_points = (float)$question->getEvaluation()['points'][$prt_name]['prt_points'];
                $max_prt_points = (float)$question->prts[$prt_name]->get_value();
                if ($max_prt_points != 0.0) {
                    $fraction = $obtained_points / $max_prt_points;
                } else {
                    $fraction = 0.0;
                }
                $raw_prt["status"] = (string)$fraction;

                $raw_prt["answer_notes"] = implode(';', $prt["prt_result"]->get_answernotes());

                $raw_solution["prts"][$prt_name] = $raw_prt;
            }
        }

        //Save total points
        $raw_solution["total_points"] = $question->getEvaluation()['points']['total'];

        $question->saveCurrentSolution($active_id, $pass, "xqcas_raw_data", json_encode($raw_solution));

        // We return 1 because after the rework only one data is inserted in the tst_solutions table.
		return 1;
	}

	/**
	 * @param assStackQuestion $question
	 * @param int $active_id
	 * @param int $pass
	 * @param string $prt_name
	 * @param float $points
	 * @param bool|null $authorized
	 */
	public static function _addPointsToPRTDBEntry(assStackQuestion $question, int $active_id, int $pass, string $prt_name, float $points, bool $authorized = null)
	{
		global $DIC;
		$db = $DIC->database();

		//Get solutionID as getCurrentSolutionResultSet is protected we have to overwrite this method
		$query = /** @lang text */
			"
				SELECT solution_id
				FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
				AND pass = %s
				AND value1 = %s
				AND authorized = %s
			";

		$result = $db->queryF(
			$query,
			array('integer', 'integer', 'integer', 'text', 'integer'),
			array($active_id, $question->getId(), $pass, 'xqcas_prt_' . $prt_name . '_name', (int)$authorized)
		);

		$row = $db->fetchAssoc($result);
		$solution_id = $row["solution_id"];

		//Prepare data to update
		$field_data = array('points' => array('float', $points));

		//Get step in case it exists
		if ($question->getStep() !== null) {
			$field_data['step'] = array('integer', $question->getStep());
		}

		//Replace points in tst_solution solution_id entry
		if ($solution_id != null) {
			$db->update('tst_solutions', $field_data, array('solution_id' => array('integer', (int)$solution_id)));
		}
	}

	/**
	 * @param assStackQuestion $question
	 * @param int $active_id
	 * @param int $pass
	 * @param string $input_name
	 * @return void
	 */
	public static function _saveModelAnswerIntoDB(assStackQuestion $question, int $active_id, int $pass, string $input_name, string $input_value, string $input_display)
	{
		try {
			//value1 = xqcas_input_*_model_answer, value2 = teacher answer for this question input in raw format but initialised
			$question->saveCurrentSolution($active_id, $pass, 'xqcas_input_' . $input_name . '_model_answer', $input_value);

			//value1 = xqcas_input_*_model_answer_display_, value2 = teacher answer for this question input validation display
			$question->saveCurrentSolution($active_id, $pass, 'xqcas_input_' . $input_name . '_model_answer_display', $input_display);

		} catch (stack_exception $e) {
            global $tpl;
            $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
		}
	}

	/**
	 * @return assStackQuestion[]
	 * @throws stack_exception
	 */
	public static function _getAllQuestionsFromPool(int $question_id, int $q_type_id): array
	{
		global $DIC;
		$db = $DIC->database();

		$questions_array = array();

		if ($question_id > 0 and $q_type_id) {
			$result = $db->queryF(/** @lang text */ "SELECT question_id FROM qpl_questions AS qpl
									WHERE qpl.obj_fi = (SELECT obj_fi FROM qpl_questions WHERE question_id = %s)
									AND qpl.question_type_fi = %s", array('integer', 'integer'), array($question_id, $q_type_id));

			while ($row = $db->fetchAssoc($result)) {
				$new_question_id = $row['question_id'];

				$ilias_question = new assStackQuestion();
				$ilias_question->loadFromDb($new_question_id);

				$questions_array[$new_question_id] = $ilias_question;
			}
		}

		return $questions_array;
	}

	/**
	 * @return assStackQuestion[]
	 * @throws stack_exception
	 */
	public static function _getAllQuestionsFromTest(int $question_id, int $q_type_id): array
	{
		global $DIC;
		$db = $DIC->database();

		$questions_array = array();

		if ($question_id > 0 and $q_type_id) {
			$result = $db->queryF(/** @lang text */ "SELECT question_fi FROM tst_test_question AS tst INNER JOIN qpl_questions AS qpl
								WHERE tst.question_fi = qpl.question_id
								AND tst.test_fi = (SELECT test_fi FROM tst_test_question WHERE question_fi = %s)
								AND qpl.question_type_fi = %s", array('integer', 'integer'), array($question_id, $q_type_id));

			while ($row = $db->fetchAssoc($result)) {
				$new_question_id = $row['question_fi'];

				$ilias_question = new assStackQuestion();
				$ilias_question->loadFromDb($new_question_id);

				$questions_array[$new_question_id] = $ilias_question;
			}
		}

		return $questions_array;
	}

	/**
	 * Manages the copy PRT function from Authoring interface
	 * @param string $original_question_id
	 * @param string $original_prt_name
	 * @param string $original_node_id
	 * @param string $new_question_id
	 * @param string $new_prt_name
	 * @param string $new_node_name
	 * @return bool
	 */
	public static function _copyPRTFunction(string $original_question_id, string $original_prt_name, string $new_question_id, string $new_prt_name): bool
	{
		//Manage PRTS
		$prts = self::_readPRTs($original_question_id);
		$db_original_prt = $prts[$original_prt_name];

		global $DIC;

		//CREATE PRT WITH ORIGINAL PRT STATS IN NEW QUESTION
		$DIC->database()->insert("xqcas_prts", array(
			"id" => array("integer", $DIC->database()->nextId('xqcas_prts')),
			"question_id" => array("integer", (int)$new_question_id),
			"name" => array("text", $new_prt_name),
			"value" => array("text", $db_original_prt['value']),
			"auto_simplify" => array("integer", (int)$db_original_prt['auto_simplify']),
			"feedback_variables" => array("clob", $db_original_prt['feedback_variables']),
			"first_node_name" => array("text", $db_original_prt['first_node_name']),
		));

		//Manage Nodes
		$db_original_nodes = self::_readPRTNodes((int)$original_question_id, $original_prt_name);
		foreach ($db_original_nodes as $node_id => $node) {

			//CREATE NODE WITH ORIGINAL NODE STATS IN NEW QUESTION PRT
			$DIC->database()->insert("xqcas_prt_nodes", array(
				"id" => array("integer", $DIC->database()->nextId('xqcas_prt_nodes')),
				"question_id" => array("integer", (int)$new_question_id),
				"prt_name" => array("text", $new_prt_name),
				"node_name" => array("text", $node_id),
				"answer_test" => array("text", $node->answer_test),
				"sans" => array("text", $node->sans),
				"tans" => array("text", $node->tans),
				"test_options" => array("text", $node->test_options),
				"quiet" => array("integer", (int)$node->quiet),
				"true_score_mode" => array("text", $node->true_score_mode),
				"true_score" => array("text", $node->true_score),
				"true_penalty" => array("text", $node->true_penalty),
				"true_next_node" => array("text", $node->true_next_node),
				"true_answer_note" => array("text", $new_prt_name . '-' . $node_id . '-T'),
				"true_feedback" => array("clob", $node->true_feedback),
				"true_feedback_format" => array("integer", (int)$node->true_feedback_format),
				"false_score_mode" => array("text", $node->false_score_mode),
				"false_score" => array("text", $node->false_score),
				"false_penalty" => array("text", $node->false_penalty),
				"false_next_node" => array("text", $node->false_next_node),
				"false_answer_note" => array("text", $new_prt_name . '-' . $node_id . '-F'),
				"false_feedback" => array("clob", $node->false_feedback),
				"false_feedback_format" => array("integer", (int)$node->false_feedback_format),
			));
		}

		unset($_SESSION['copy_prt']);
        global $tpl;
		$tpl->setOnScreenMessage('info', $DIC->language()->txt("qpl_qst_xqcas_prt_paste"), true);

		return true;
	}

	/**
	 * Manages the add node function from Authoring interface
	 * @param string $original_question_id
	 * @param string $original_prt_name
	 * @param string $original_node_id
	 * @param string $new_question_id
	 * @param string $new_prt_name
	 * @param string $new_node_name
	 * @return bool
	 */
	public static function _addNodeFunction(string $question_id, string $prt_name, string $new_node_name): bool
	{

		$standard_prt = assStackQuestionConfig::_getStoredSettings('prts');

		global $DIC;

		//CREATE NODE WITH ORIGINAL NODE STATS IN NEW QUESTION PRT
		$DIC->database()->insert("xqcas_prt_nodes", array(
			"id" => array("integer", $DIC->database()->nextId('xqcas_prt_nodes')),
			"question_id" => array("integer", (int)$question_id),
			"prt_name" => array("text", $prt_name),
			"node_name" => array("text", $new_node_name),
			"answer_test" => array("text", $standard_prt['prt_node_answer_test']),
			"sans" => array("text", ""),
			"tans" => array("text", ""),
			"test_options" => array("text", $standard_prt['prt_node_options']),
			"quiet" => array("integer", (int)$standard_prt['prt_node_quiet']),
			"true_score_mode" => array("text", $standard_prt['prt_pos_mod']),
			"true_score" => array("text", $standard_prt['prt_pos_score']),
			"true_penalty" => array("text", $standard_prt['prt_pos_penalty']),
			"true_next_node" => array("text", "-1"),
			"true_answer_note" => array("text", $prt_name . '-' . $new_node_name . '-T'),
			"true_feedback" => array("clob", ""),
			"true_feedback_format" => array("integer", 1),
			"false_score_mode" => array("text", $standard_prt['prt_neg_mod']),
			"false_score" => array("text", $standard_prt['prt_neg_score']),
			"false_penalty" => array("text", $standard_prt['prt_neg_penalty']),
			"false_next_node" => array("text", "-1"),
			"false_answer_note" => array("text", $prt_name . '-' . $new_node_name . '-F'),
			"false_feedback" => array("clob", ""),
			"false_feedback_format" => array("integer", 1),
		));

		unset($_SESSION['copy_node']);
        global $tpl;
		$tpl->setOnScreenMessage('info', $DIC->language()->txt("qpl_qst_xqcas_node_paste"), true);

		return true;
	}

	/**
	 * Manages the copy node function from Authoring interface
	 * @param string $original_question_id
	 * @param string $original_prt_name
	 * @param string $original_node_id
	 * @param string $new_question_id
	 * @param string $new_prt_name
	 * @param string $new_node_name
	 * @return bool
	 */
	public static function _copyNodeFunction(string $original_question_id, string $original_prt_name, string $original_node_id, string $new_question_id, string $new_prt_name, string $new_node_name): bool
	{

		$nodes = self::_readPRTNodes((int)$original_question_id, $original_prt_name);
		$db_original_node = $nodes[$original_node_id];

		global $DIC;

		//CREATE NODE WITH ORIGINAL NODE STATS IN NEW QUESTION PRT
		$DIC->database()->insert("xqcas_prt_nodes", array(
			"id" => array("integer", $DIC->database()->nextId('xqcas_prt_nodes')),
			"question_id" => array("integer", (int)$new_question_id),
			"prt_name" => array("text", $new_prt_name),
			"node_name" => array("text", $new_node_name),
			"answer_test" => array("text", $db_original_node->answer_test),
			"sans" => array("text", $db_original_node->sans),
			"tans" => array("text", $db_original_node->tans),
			"test_options" => array("text", $db_original_node->test_options),
			"quiet" => array("integer", (int)$db_original_node->quiet),
			"true_score_mode" => array("text", $db_original_node->true_score_mode),
			"true_score" => array("text", $db_original_node->true_score),
			"true_penalty" => array("text", $db_original_node->true_penalty),
			"true_next_node" => array("text", "-1"),
			"true_answer_note" => array("text", $new_prt_name . '-' . $new_node_name . '-T'),
			"true_feedback" => array("clob", $db_original_node->true_feedback),
			"true_feedback_format" => array("integer", (int)$db_original_node->true_feedback_format),
			"false_score_mode" => array("text", $db_original_node->false_score_mode),
			"false_score" => array("text", $db_original_node->false_score),
			"false_penalty" => array("text", $db_original_node->false_penalty),
			"false_next_node" => array("text", "-1"),
			"false_answer_note" => array("text", $new_prt_name . '-' . $new_node_name . '-F'),
			"false_feedback" => array("clob", $db_original_node->false_feedback),
			"false_feedback_format" => array("integer", (int)$db_original_node->false_feedback_format),
		));

		unset($_SESSION['copy_node']);
        global $tpl;
		$tpl->setOnScreenMessage('info', $DIC->language()->txt("qpl_qst_xqcas_node_paste"), true);

		return true;
	}

    /**
     * Get the qtest results for a question
     *
     * @param int $question_id
     * @return array
     */
    public static function _readQtestResult(int $question_id) :array
    {
        global $DIC;
        $db = $DIC->database();

        $query = /** @lang text */
            'SELECT * FROM xqcas_qtest_results WHERE question_id = ' . $db->quote($question_id, 'integer');

        $result = $db->query($query);

        $qtest_results = array();

        while ($row = $db->fetchAssoc($result)) {
            $qtest_results[$row['test_case']] = $row;
        }

        return $qtest_results;
    }

    /**
     * Save a qtest result for a question
     *
     * @param int $question_id
     * @param array $data
     * @return void
     */
    public static function _saveQtestResult(int $question_id, array $data)
    {
        global $DIC;
        $db = $DIC->database();

        $db->insert("xqcas_qtest_results", array(
            'id' => array('integer', $db->nextId('xqcas_qtest_results')),
            'question_id' => array('integer', $question_id),
            'test_case' => array('integer', $data['test_case']),
            'seed' => array('integer', $data['seed']),
            'result' => array('text', $data['result']),
            'timerun' => array('integer', $data['timerun']),
        ));
    }

    /**
     * Delete a qtest result for a question
     *
     * @param int $question_id
     * @param int $test_case
     * @return bool
     */
    public static function _deleteQtestResult(int $question_id, int $test_case): bool
    {
        global $DIC;
        $db = $DIC->database();

        $query = /** @lang text */
            'DELETE FROM xqcas_qtest_results WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND test_case = ' . $db->quote($test_case, 'integer');

        if ($db->manipulate($query)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Read the current active seed for a question
     *
     * @param int $question_id
     * @return array|null
     */
    public static function _readActiveSeed(int $question_id) {
        global $DIC;
        $db = $DIC->database();

        $res = $db->query("SELECT seed FROM xqcas_preview WHERE question_id = " .
            $db->quote($question_id, 'integer') . " AND user_id = " .
            $db->quote($DIC->user()->getId(), 'integer') . " AND is_active = 1");

        $row = $db->fetchAssoc($res);
        if ($row) {
            return (string)$row['seed'];
        }

        return null;
    }

    /**
     * Saves the active seed
     *
     * @param int $question_id
     * @param array $data
     * @return void
     */
    public static function _saveSeedForPreview(int $question_id, int $seed) :void {
        global $DIC;
        $db = $DIC->database();

        $db->update("xqcas_preview", array(
            'seed' => array('integer', $seed)
        ), array(
            'question_id' => array('integer', $question_id),
            'user_id' => array('integer', $DIC->user()->getId()),
            'is_active' => array('integer', 1)
        ));
    }

    /**
     * Save a preview solution for a question
     *
     * @param int $question_id
     * @param array $data
     * @return void
     */
    public static function _savePreviewSolution(int $question_id, array $data) :void {
        global $DIC;
        $db = $DIC->database();

        $db->update("xqcas_preview", array(
            'submitted_answer' => array('clob', json_encode($data))
        ), array(
            'question_id' => array('integer', $question_id),
            'user_id' => array('integer', $DIC->user()->getId()),
            'is_active' => array('integer', 1)
        ));
    }

    /**
     * Read a preview solution for a question
     *
     * @param int $question_id
     * @return array|null
     */
    public static function _readPreviewSolution(int $question_id, int $user_id) {
        global $DIC;
        $db = $DIC->database();

        $res = $db->query("SELECT submitted_answer FROM xqcas_preview WHERE question_id = " .
            $db->quote($question_id, 'integer') . " AND user_id = " .
            $db->quote($user_id, 'integer') . " AND is_active = 1");

        $row = $db->fetchAssoc($res);
        if ($row) {
            return json_decode($row['submitted_answer'], true);
        }

        return null;
    }

    public static function _readTestSolution(int $question_id, int $active_id, int $pass = 0) {
        global $DIC;
        $db = $DIC->database();

        $solution = $db->query("SELECT value1, value2 FROM tst_solutions WHERE question_fi = " .
            $db->quote($question_id, 'integer') . " AND active_fi = " .
            $db->quote($active_id, 'integer') . " AND pass = " .
            $db->quote($pass, 'integer'));

        $solution_db = array();

        while ($row = $db->fetchAssoc($solution)) {
            $solution_db[] = $row;
        }

        $solution_db_parsed = assStackQuestionUtils::_fromTSTSolutionsToSTACK($solution_db, (string) $question_id);

        //Old Test
        if (!isset($solution_db_parsed['inputs'])) {
            $old_student_solutions = assStackQuestionUtils::_fromDBToReadableFormat($solution_db);

            if (isset($old_student_solutions["prt"])) {
                foreach ($old_student_solutions["prt"] as $prt_name => $prt_info) {
                    if (isset($prt_info["response"])) {
                        foreach ($prt_info["response"] as $input_name => $input_info) {
                            $old_student_solutions["inputs"][$input_name]['value'] = $input_info['value'];
                            $old_student_solutions["inputs"][$input_name]['display'] = $input_info['display'];
                            $old_student_solutions["inputs"][$input_name]['validation_display'] = $input_info['display'];
                            $old_student_solutions["inputs"][$input_name]['correct_value'] = $input_info['model_answer'];
                            $old_student_solutions["inputs"][$input_name]['correct_display'] = $input_info['model_answer_display'];
                        }
                    }
                }
            }

            $solution_db_parsed = $old_student_solutions;
        }

        return $solution_db_parsed;
    }
}