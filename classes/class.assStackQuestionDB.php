<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * STACK Question DB Manager Class
 * All DB Stuff will be placed here
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id: 4.0$
 *
 */
class assStackQuestionDB
{

	/**
	 * @param $question_id
	 * @param bool $just_id
	 * @return array|int
	 */
	public static function _readOptions($question_id, bool $just_id = false)
	{
		global $DIC;
		$db = $DIC->database();

		$query = 'SELECT * FROM xqcas_options WHERE question_id = ' . $db->quote($question_id, 'integer');
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

			$options['simplify'] = ((int)$row->question_simplify);
			$options['assumepos'] = ((int)$row->assume_positive);
			$options['multiplicationsign'] = ($row->multiplication_sign);
			$options['sqrtsign'] = ((int)$row->sqrt_sign);
			$options['complexno'] = ($row->complex_no);
			$options['inversetrig'] = ($row->inverse_trig);
			$options['matrixparens'] = ($row->matrix_parens);

			//TODO OPTIONS FEATURES
			/*
			$this->set_option('logicsymbol', $stackconfig->logicsymbol);
			$this->set_option('floats', (bool) $stackconfig->inputforbidfloat);
			$this->set_option('assumereal', (bool) $stackconfig->assumereal);
			*/

			return array('options' => $options, 'ilias_options' => $ilias_options);
		} else {
			return -1;
		}
	}

	/**
	 * @param $question_id
	 * @param bool $just_id
	 * @return array|int
	 */
	public static function _readInputs($question_id, bool $just_id = false)
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = 'SELECT * FROM xqcas_inputs WHERE question_id = ' . $db->quote($question_id, 'integer');
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
			$inputs[$input_name]['syntax_hint'] = (isset($row['syntax_hint']) and $row['syntax_hint'] != null) ? trim($row['syntax_hint']) : '';
			$inputs[$input_name]['forbid_words'] = $row['forbid_words'];
			$inputs[$input_name]['allow_words'] = $row['allow_words'];
			$inputs[$input_name]['forbid_float'] = (bool)$row['forbid_float'];
			$inputs[$input_name]['require_lowest_terms'] = (bool)$row['require_lowest_terms'];
			$inputs[$input_name]['check_answer_type'] = (bool)$row['check_answer_type'];
			$inputs[$input_name]['must_verify'] = (bool)$row['must_verify'];
			$inputs[$input_name]['show_validation'] = $row['show_validation'];
			$inputs[$input_name]['options'] = $row['options'];

			//TODO OPTIONS FEATURES
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
	public static function _readPRTs($question_id, bool $just_id = false)
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = 'SELECT * FROM xqcas_prts WHERE question_id = ' . $db->quote($question_id, 'integer') . ' ORDER BY xqcas_prts.id';
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
				$potential_response_trees[$prt_name]['nodes'] = self::_readPRTNodes($question_id, $prt_name, false);
			}
		}
		if ($just_id) {
			return $prt_ids;
		} else {
			return $potential_response_trees;
		}
		//TODO FEATURE ADD DESCRIPTION TO PRT
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
		$query = 'SELECT * FROM xqcas_prt_nodes WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND prt_name = ' . $db->quote($prt_name, 'text');
		$res = $db->query($query);

		$potential_response_tree_nodes = array();
		$ilias_prts_nodes = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {

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
	 * @return array|false
	 */
	public static function _readDeployedVariants($question_id)
	{
		global $DIC;
		$db = $DIC->database();

		//Select query
		$query = 'SELECT * FROM xqcas_deployed_seeds WHERE question_id = ' . $db->quote($question_id, 'integer');
		$res = $db->query($query);

		//Seeds array
		$variants = array();

		//If there is a result returns array, otherwise returns false.
		while ($row = $db->fetchAssoc($res)) {
			$variants[] = (int)$row["seed"];
		}

		if (empty($variants)) {
			return false;
		} else {
			return $variants;
		}
	}

	/**
	 * READS UNIT TESTS FROM THE DB
	 * @param $question_id
	 * @return array|false
	 */
	public static function _readUnitTests($question_id)
	{
		//TODO
		return array();
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
		$query = 'SELECT * FROM xqcas_extra_info WHERE question_id = ' . $db->quote($question_id, 'integer');
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
	 * SAVES STACK QUESTION INTO THE DB
	 * Called from saveToDB()->saveAdditionalQuestionDataToDb();
	 * @param assStackQuestion $question
	 * @param string $purpose
	 * @throws stack_exception
	 */
	public static function _saveStackQuestion(assStackQuestion $question, string $purpose = ''): bool
	{
		//Get first all ILIAS DB ids for the current question.
		$question_id = $question->getId();
		$ids = array('question_id' => $question_id);

		//Save Options
		$options_saved = self::_saveStackOptions($question);

		//Save Inputs
		$inputs_saved = self::_saveStackInputs($question, $purpose);

		//Save Prts
		$prts_saved = self::_saveStackPRTs($question, $purpose);

		//Extra Prts
		//$prts_saved = self::_saveStackExtraInformation($question, self::_readExtraInformation($ids['question_id'], true));

		//Validate from form, popup errors
		return true;
	}

	/**
	 * @param assStackQuestion $question
	 * @return bool
	 * @throws stack_exception
	 */
	public static function _saveStackOptions(assStackQuestion $question): bool
	{
		global $DIC;
		$db = $DIC->database();
		include_once("./Services/RTE/classes/class.ilRTE.php");

		$options_id = self::_readOptions($question->getId(), true);

		if ($options_id < 0) {
			//CREATE
			$db->insert("xqcas_options", array(
				"id" => array("integer", $db->nextId('xqcas_options')),
				"question_id" => array("integer", $question->getId()),
				"question_variables" => array("clob", $question->question_variables),
				"specific_feedback" => array("clob", $question->specific_feedback),
				"specific_feedback_format" => array("integer", 1),
				"question_note" => array("text", $question->question_note),
				"question_simplify" => array("integer", $question->options->get_option('simplify')),
				"assume_positive" => array("integer", $question->options->get_option('assumepos')),
				"prt_correct" => array("clob", $question->prt_correct),
				"prt_correct_format" => array("integer", 1),
				"prt_partially_correct" => array("clob", $question->prt_partially_correct),
				"prt_partially_correct_format" => array("integer", 1),
				"prt_incorrect" => array("clob", $question->prt_incorrect),
				"prt_incorrect_format" => array("integer", 1),
				"multiplication_sign" => array("text", $question->options->get_option('multiplicationsign') == null ? "dot" : $question->options->get_option('multiplicationsign')),
				"sqrt_sign" => array("integer", $question->options->get_option('sqrtsign')),
				"complex_no" => array("text", $question->options->get_option('complexno') == null ? "i" : $question->options->get_option('complexno')),
				"inverse_trig" => array("text", $question->options->get_option('inversetrig')),
				"variants_selection_seed" => array("text", $question->variants_selection_seed),
				"matrix_parens" => array("text", $question->options->get_option('matrixparens'))
			));
		} else {
			//UPDATE
			$db->replace('xqcas_options',
				array(
					"id" => array('integer', $options_id)),
				array(
					"question_id" => array("integer", $question->getId()),
					"question_variables" => array("clob", $question->question_variables),
					"specific_feedback" => array("clob", $question->specific_feedback),
					"specific_feedback_format" => array("integer", 1),
					"question_note" => array("text", $question->question_note),
					"question_simplify" => array("integer", $question->options->get_option('simplify')),
					"assume_positive" => array("integer", $question->options->get_option('assumepos')),
					"prt_correct" => array("clob", $question->prt_correct),
					"prt_correct_format" => array("integer", 1),
					"prt_partially_correct" => array("clob", $question->prt_partially_correct),
					"prt_partially_correct_format" => array("integer", 1),
					"prt_incorrect" => array("clob", $question->prt_incorrect),
					"prt_incorrect_format" => array("integer", 1),
					"multiplication_sign" => array("text", $question->options->get_option('multiplicationsign') == null ? "dot" : $question->options->get_option('multiplicationsign')),
					"sqrt_sign" => array("integer", $question->options->get_option('sqrtsign')),
					"complex_no" => array("text", $question->options->get_option('complexno') == null ? "i" : $question->options->get_option('complexno')),
					"inverse_trig" => array("text", $question->options->get_option('inversetrig')),
					"variants_selection_seed" => array("text", $question->variants_selection_seed),
					"matrix_parens" => array("text", $question->options->get_option('matrixparens')))
			);
		}
		return true;
	}

	/**
	 * @param assStackQuestion $question
	 * @param string $purpose
	 * @return bool
	 */
	public static function _saveStackInputs(assStackQuestion $question, string $purpose = ''): bool
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
					"forbid_words" => array("text", $input->get_parameter('forbidWords') !== null ? $input->get_parameter('forbidWords') : ''),
					"allow_words" => array("text", $input->get_parameter('allowWords') !== null ? $input->get_parameter('allowWords') : ''),
					"forbid_float" => array("integer", $input->get_parameter('forbidFloats') !== null ? $input->get_parameter('forbidFloats') : ''),
					"require_lowest_terms" => array("integer", $input->get_parameter('lowestTerms') !== null ? $input->get_parameter('lowestTerms') : ''),
					"check_answer_type" => array("integer", $input->get_parameter('sameType') !== null ? $input->get_parameter('sameType') : ''),
					"must_verify" => array("integer", $input->get_parameter('mustVerify') !== null ? $input->get_parameter('mustVerify') : ''),
					"show_validation" => array("integer", $input->get_parameter('showValidation') !== null ? $input->get_parameter('showValidation') : ''),
					"options" => array("clob", assStackQuestionUtils::_serializeExtraOptions($input->get_extra_options()) !== null ? assStackQuestionUtils::_serializeExtraOptions($input->get_extra_options()) : ''),
				));
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
						"forbid_words" => array("text", $input->get_parameter('forbidWords') !== null ? $input->get_parameter('forbidWords') : ''),
						"allow_words" => array("text", $input->get_parameter('allowWords') !== null ? $input->get_parameter('allowWords') : ''),
						"forbid_float" => array("integer", $input->get_parameter('forbidFloats') !== null ? $input->get_parameter('forbidFloats') : ''),
						"require_lowest_terms" => array("integer", $input->get_parameter('lowestTerms') !== null ? $input->get_parameter('lowestTerms') : ''),
						"check_answer_type" => array("integer", $input->get_parameter('sameType') !== null ? $input->get_parameter('sameType') : ''),
						"must_verify" => array("integer", $input->get_parameter('mustVerify') !== null ? $input->get_parameter('mustVerify') : ''),
						"show_validation" => array("integer", $input->get_parameter('showValidation') !== null ? $input->get_parameter('showValidation') : ''),
						"options" => array("clob", assStackQuestionUtils::_serializeExtraOptions($input->get_extra_options()) !== null ? assStackQuestionUtils::_serializeExtraOptions($input->get_extra_options()) : ''),
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
	public static function _saveStackPRTs(assStackQuestion $question, string $purpose = ''): bool
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
					"first_node_name" => array("text", $question->prts[$prt_name]->getFirstNode() == null ? '-1' : $question->prts[$prt_name]->getFirstNode()),
				));

				//Insert nodes
				foreach ($prt->getNodes() as $node) {
					self::_saveStackPRTNodes($node, $question_id, $prt_name, -1);
				}

			} else {

				//UPDATE
				$db->replace('xqcas_prts',
					array(
						"id" => array('integer', $prt_ids[$prt_name]['prt_id'])),
					array(
						"question_id" => array("integer", $question_id),
						"name" => array("text", $question->prts[$prt_name]->get_name()),
						"value" => array("text", $question->prts[$prt_name]->get_value() == null ? "1.0" : $question->prts[$prt_name]->get_value()),
						"auto_simplify" => array("integer", $question->prts[$prt_name]->isSimplify() == null ? 0 : $question->prts[$prt_name]->isSimplify()),
						"feedback_variables" => array("clob", $question->prts[$prt_name]->get_feedbackvariables_keyvals() == null ? "" : $question->prts[$prt_name]->get_feedbackvariables_keyvals()),
						"first_node_name" => array("text", $question->prts[$prt_name]->getFirstNode() == null ? '-1' : $question->prts[$prt_name]->getFirstNode()),
					)
				);

				//Update/Insert Nodes
				$prt_node_ids = self::_readPRTNodes($question_id, $prt_name, true);

				foreach ($prt->getNodes() as $node_name => $node) {
					if (!array_key_exists($node_name, $prt_node_ids) or empty($prt_node_ids) or $purpose == 'import') {
						//CREATE
						self::_saveStackPRTNodes($node, $question_id, $prt_name, -1);
					} else {
						//UPDATE
						if (isset($prt_ids[$prt_name]['nodes'][$node_name])) {
							self::_saveStackPRTNodes($node, $question_id, $prt_name, $prt_ids[$prt_name]['nodes'][$node_name]);
						} else {
							ilUtil::sendFailure('question:' . $question_id . $prt_name . $node_name);
						}
					}
				}
			}

		}
		return true;
	}

	/**
	 * @param stack_potentialresponse_node $node
	 * @param int $question_id
	 * @param string $prt_name
	 * @param int $id
	 */
	public static function _saveStackPRTNodes(stack_potentialresponse_node $node, int $question_id, string $prt_name, int $id = -1)
	{
		global $DIC;
		$db = $DIC->database();
		include_once("./Services/RTE/classes/class.ilRTE.php");

		$branches_info = $node->summarise_branches();
		$feedback_info = $node->getFeedbackFromNode();

		if ($id < 0) {
			//CREATE
			$db->insert("xqcas_prt_nodes", array(
				"id" => array("integer", $db->nextId('xqcas_prt_nodes')),
				"question_id" => array("integer", $question_id),
				"prt_name" => array("text", $prt_name),
				"node_name" => array("text", (string)$node->nodeid),
				"answer_test" => array("text", $node->get_test()),
				"sans" => array("text", $node->getRawSans()),
				"tans" => array("text", $node->getRawTans()),
				"test_options" => array("text", assStackQuestionUtils::_serializeExtraOptions($node->getAtoptions())),
				"quiet" => array("integer", $node->isQuiet()),
				"true_score_mode" => array("text", $branches_info->truescoremode),
				"true_score" => array("text", $branches_info->truescore),
				"true_penalty" => array("text", $feedback_info['true_penalty']),
				"true_next_node" => array("text", $branches_info->truenextnode),
				"true_answer_note" => array("text", $branches_info->truenote),
				"true_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($feedback_info['true_feedback'], 0)),
				"true_feedback_format" => array("integer", (int)$feedback_info['true_feedback_format']),
				"false_score_mode" => array("text", $branches_info->falsescoremode),
				"false_score" => array("text", $branches_info->falsescore),
				"false_penalty" => array("text", $feedback_info['false_penalty']),
				"false_next_node" => array("text", $branches_info->falsenextnode),
				"false_answer_note" => array("text", $branches_info->falsenote),
				"false_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($feedback_info['false_feedback_format'], 0)),
				"false_feedback_format" => array("integer", (int)$feedback_info['false_feedback_format']),
			));
		} else {
			//UPDATE
			$db->replace('xqcas_prt_nodes',
				array(
					"id" => array('integer', $id)),
				array(
					"question_id" => array("integer", $question_id),
					"prt_name" => array("text", $prt_name),
					"node_name" => array("text", (string)$node->nodeid),
					"answer_test" => array("text", $node->get_test()),
					"sans" => array("text", $node->getRawSans()),
					"tans" => array("text", $node->getRawTans()),
					"test_options" => array("text", assStackQuestionUtils::_serializeExtraOptions($node->getAtoptions())),
					"quiet" => array("integer", $node->isQuiet()),
					"true_score_mode" => array("text", $branches_info->truescoremode),
					"true_score" => array("text", $branches_info->truescore),
					"true_penalty" => array("text", $feedback_info['true_penalty']),
					"true_next_node" => array("text", $branches_info->truenextnode),
					"true_answer_note" => array("text", $branches_info->truenote),
					"true_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($feedback_info['true_feedback'], 0)),
					"true_feedback_format" => array("integer", (int)$feedback_info['true_feedback_format']),
					"false_score_mode" => array("text", $branches_info->falsescoremode),
					"false_score" => array("text", $branches_info->falsescore),
					"false_penalty" => array("text", $feedback_info['false_penalty']),
					"false_next_node" => array("text", $branches_info->falsenextnode),
					"false_answer_note" => array("text", $branches_info->falsenote),
					"false_feedback" => array("clob", ilRTE::_replaceMediaObjectImageSrc($feedback_info['false_feedback_format'], 0)),
					"false_feedback_format" => array("integer", (int)$feedback_info['false_feedback_format']),
				)
			);
		}
	}

	/**
	 * @param int $question_id
	 * @return bool
	 */
	public static function _deleteStackQuestion(int $question_id): bool
	{

		$options = self::_deleteStackOptions($question_id);

		$inputs = self::_deleteStackInputs($question_id);

		$prts = self::_deleteStackPrts($question_id);
		/*
				switch ($specific_table) {
					case 'options':
						$query = 'DELETE FROM xqcas_options WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'extra_info':
						$query = 'DELETE FROM xqcas_extra_info WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'inputs':
						$query = 'DELETE FROM xqcas_inputs WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'prts':
						$query = 'DELETE FROM xqcas_prts WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'prt_nodes':
						$query = 'DELETE FROM xqcas_prt_nodes WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'seeds':
						$query = 'DELETE FROM xqcas_deployed_seeds WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'qtest_expected':
						$query = 'DELETE FROM xqcas_qtest_expected WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'qtest_input':
						$query = 'DELETE FROM xqcas_qtest_input WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					case 'qtests':
						$query = 'DELETE FROM xqcas_qtests WHERE question_id = ' . $db->quote($question_id, 'integer');
						$db->manipulate($query);
						if ($purpose != 'delete_question') {
							break;
						}
					default:
						ilUtil::sendFailure('non existing table');
						break;
				}*/
		return true;
	}

	/**
	 * @param int $question_id
	 * @return bool
	 */
	public static function _deleteStackOptions(int $question_id): bool
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
	public static function _deleteStackInputs(int $question_id, string $input_name = ''): bool
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
	public static function _deleteStackPrts(int $question_id, string $prt_name = ''): bool
	{
		global $DIC;
		$db = $DIC->database();
		if ($prt_name == '') {
			//delete all prts
			$query = /** @lang text */
				'DELETE FROM xqcas_prts WHERE question_id = ' . $db->quote($question_id, 'integer');
			$prts_deleted = $db->manipulate($query);
			$nodes_deleted = self::_deleteStackPrtNodes($question_id);
		} else {
			//delete only $prt_name
			$query = /** @lang text */
				'DELETE FROM xqcas_prts WHERE question_id = ' . $db->quote($question_id, 'integer') . ' AND name = ' . $db->quote($prt_name, 'text');
			$prts_deleted = $db->manipulate($query);
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
}