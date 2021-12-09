<?php

/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */

/**
 * STATIC METHODS used in the ilias version of the STACK Plugin
 * STACK Only static methods are located in locallib.php
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id 4.0$
 *
 */
class assStackQuestionUtils2
{

	/**
	 * This function
	 * @param string $question_text
	 * @param assStackQuestion $question
	 */
	public static function _deleteUnusedAttributes(string $question_text, assStackQuestion $question)
	{
		$inputs = stack_utils::extract_placeholders($question_text, 'input');
		$validation = stack_utils::extract_placeholders($question_text, 'validation');
		$feedback = stack_utils::extract_placeholders($question_text, 'feedback');
	}

	/**
	 * This function stores the Variant for the preview
	 * @param int $question_id
	 * @return int the Variant used for this Preview
	 */
	public static function _getVariantForPreview(int $question_id): int
	{
		//Seed management
		if (isset($_REQUEST['fixed_seed'])) {
			$variant = (int)$_REQUEST['fixed_seed'];
			$_SESSION['q_seed_for_preview_' . $question_id . ''] = $variant;
		} else {
			if (isset($_SESSION['q_seed_for_preview_' . $question_id . ''])) {
				$variant = (int)$_SESSION['q_seed_for_preview_' . $question_id . ''];
			} else {
				$variant = -1;
			}
		}
		return $variant;
	}




}