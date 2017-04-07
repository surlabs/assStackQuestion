<?php
/**
 * Copyright (c) 2014 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv2, see LICENSE
 */
require_once './Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionUtils.php';

/**
 * STACK Question FEEDBACK management
 * This class manages the feedback after a STACK Question evaluation
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version    $Id: 1.6.1$$
 * @ingroup    ModulesTestQuestionPool
 *
 */
class assStackQuestionFeedback
{

	/**
	 * Plugin instance for templates and language management
	 * @var ilassStackQuestionPlugin
	 */
	private $plugin;

	/**
	 * The question already evaluated
	 * @var assStackQuestionStackQuestion
	 */
	private $question;

	/**
	 * @param ilassStackQuestionPlugin $plugin
	 * @param assStackQuestionStackQuestion $question
	 */
	function __construct(ilassStackQuestionPlugin $plugin, assStackQuestionStackQuestion $question)
	{
		//Set plugin object
		$this->setPlugin($plugin);
		//Set question object already evaluated
		$this->setQuestion($question);
	}

	/**
	 * ### MAIN METHOD OF THIS CLASS ###
	 * This method is called from assStackQuestion and assStackQuestionPreview
	 * This method creates the feedback information array and returns it
	 * @return array
	 */
	public function getFeedback()
	{
		//Feedback structure creation
		$question_feedback = array();

		//Fill global question vars
		$question_feedback['question_text'] = $this->getQuestion()->getQuestionTextInstantiated();
		$question_feedback['general_feedback'] = $this->getQuestion()->getGeneralFeedback();
		$question_feedback['question_note'] = $this->getQuestion()->getQuestionNoteInstantiated();
		$question_feedback['points'] = $this->getQuestion()->reached_points;

		//Fill specific PRT vars
		foreach ($this->getQuestion()->getPRTResults() as $prt_name => $prt_data)
		{
			$question_feedback['prt'][$prt_name] = $this->createPRTFeedback($prt_name, $prt_data);
		}

		return $question_feedback;
	}

	/**
	 * Creates specific feedback for each PRT evaluated
	 * Called from $this->getFeedback()
	 * @param string $prt_name
	 * @param array $prt_data
	 * @return array
	 */
	private function createPRTFeedback($prt_name, $prt_data)
	{
		//PRT Feedback structure creation
		$prt_feedback = array();

		//fill user response data
		$prt_feedback['response'] = $this->fillUserResponses($prt_data['inputs_evaluated']);
		//fill points data
		$prt_feedback['points'] = $prt_data['points'];
		//fill errors data
		$prt_feedback['errors'] = $prt_data['state']->__get('errors');
		//fill feedback message data
		$prt_feedback['feedback'] = $this->fillFeedback($prt_data['state']);
		//fill status and status message
		$prt_feedback['status'] = $this->fillStatus($prt_data['state']);
		//fill answernote
		$prt_feedback['answernote'] = $this->fillAnswerNote($prt_data['state']);

		return $prt_feedback;
	}

	/**
	 * Fills the user response structure for feedback
	 * Called from $this->createPRTFeedback()
	 * @param array $inputs_evaluated
	 * @return array
	 */
	private function fillUserResponses($inputs_evaluated)
	{
		//Prepare user response structure array
		$user_responses = array();

		$count = 0;
		//Fill user_response per each input evaluated by current PRT
		foreach ($inputs_evaluated as $input_name => $user_response_value)
		{
			//Input is Ok, use input states
			if (is_a($this->getQuestion()->getInputStates($input_name), 'stack_input_state'))
			{
				//Fill value
				$user_responses[$input_name]['value'] = $this->getQuestion()->getInputStates($input_name)->__get('contentsmodified');
				//Fill LaTeX display
				$user_responses[$input_name]['display'] = assStackQuestionUtils::_getLatex(assStackQuestionUtils::_solveKeyBracketsBug($this->getQuestion()->getInputStates($input_name)->__get('contentsdisplayed')));
				//Fill model answer
				$user_responses[$input_name]['model_answer'] = $this->getModelAnswerForInput($input_name);
				//Fill model answer display
				$user_responses[$input_name]['model_answer_display'] = $this->getModelAnswerDisplay($input_name);

			} else
			{
				//Input was not Ok, use getLatexText
				//Fill value
				$user_responses[$input_name]['value'] = $user_response_value;
				//Fill LaTeX display
				$user_responses[$input_name]['display'] = assStackQuestionUtils::_getLatex(assStackQuestionUtils::_solveKeyBracketsBug($user_response_value));
				//Fill model answer
				$user_responses[$input_name]['model_answer'] = $this->getModelAnswerForInput($input_name);
				//Fill model answer display
				$user_responses[$input_name]['model_answer_display'] = assStackQuestionUtils::_getLatex($this->getModelAnswerDisplay($input_name));
			}
		}

		return $user_responses;
	}

	/**
	 * Gets the model answer for the current input
	 * @param string $input_name
	 * @return string
	 */
	private function getModelAnswerForInput($input_name)
	{
		//Get the session value with key the teacher answer
		$teacher_answer = $this->getQuestion()->getSession()->get_value_key($input_name);

		if ($teacher_answer)
		{
			//If session value with key the teacher answer is set, returns it.
			return $teacher_answer;
		} else
		{
			//If not, returns the session value with key the input name.
			return assStackQuestionUtils::_getLatex($this->getQuestion()->getSession()->get_display_key($input_name));
		}
	}

	/**
	 * Gets the model answer for the current input
	 * @param string $input_name
	 * @return string
	 */
	private function getModelAnswerDisplay($input_name)
	{
		//TODO MATRIX ERROR
		$raw = $this->getQuestion()->getSession()->get_display_key($input_name);
		$raw1 = str_replace('\left[', "", $raw);
		$raw2 = str_replace('\right]', "", $raw1);

		return $raw2;
	}

	/**
	 * Create feedback message
	 * @param $prt_state
	 * @return string
	 */
	private function fillFeedback($prt_state)
	{
		//Prepare feedback message
		$feedback = '';

		//For each feedback obj add a line the the message with the feedback.
		if ($prt_state->__get('feedback'))
		{
			foreach ($prt_state->__get('feedback') as $feedback_obj)
			{
				$feedback .= $prt_state->substitue_variables_in_feedback($feedback_obj->feedback);
				$feedback .= '</br>';
			}
		}

		return $feedback;
	}

	/**
	 * Determines status for the current PRT and sets the message
	 * @param $prt_state
	 * @return array
	 */
	private function fillStatus($prt_state)
	{
		//Prepare status structure
		$status = array();

		if ((float)$prt_state->__get('score') * (float)$prt_state->__get('weight') == (float)$prt_state->__get('weight'))
		{
			//CORRECT
			$status['value'] = 1;
			$status['message'] = $this->getQuestion()->getPRTCorrectInstantiated();
		} elseif ((float)$prt_state->__get('score') > 0.0 AND (float)$prt_state->__get('score') < (float)$prt_state->__get('weight'))
		{
			//PARTIALLY CORRECT
			$status['value'] = 0;
			$status['message'] = $this->getQuestion()->getPRTPartiallyCorrectInstantiated();
		} else
		{
			//INCORRECT
			$status['value'] = -1;
			$status['message'] = $this->getQuestion()->getPRTIncorrectInstantiated();
		}

		return $status;
	}

	/**
	 * Determines answernote for the current PRT and sets the message
	 * @param $prt_state
	 * @return array
	 */
	private function fillAnswerNote($prt_state)
	{
		if (is_array($prt_state->__get('answernotes')))
		{
			return implode('_', $prt_state->__get('answernotes'));
		}
	}


	/*
	 * GETTERS AND SETTERS
	 */

	/**
	 * @param \ilassStackQuestionPlugin $plugin
	 */
	private function setPlugin($plugin)
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
	 * @param \assStackQuestionStackQuestion $question
	 */
	private function setQuestion($question)
	{
		$this->question = $question;
	}

	/**
	 * @return \assStackQuestionStackQuestion
	 */
	public function getQuestion()
	{
		return $this->question;
	}
}