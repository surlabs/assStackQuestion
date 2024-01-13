<?php
declare(strict_types=1);

use classes\platform\ilias\StackPlatformIlias;
use classes\platform\StackConfig;
use classes\platform\StackException;
use classes\platform\StackPlatform;

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
class assStackQuestion extends assQuestion implements iQuestionCondition, ilObjQuestionScoringAdjustable
{
    /**
     * @var ilPlugin Contains ilPlugin derived object
     * like ilLanguage
     */
    private ilPlugin $plugin;

    public function getPlugin(): ilPlugin
    {
        return $this->plugin;
    }

    public function setPlugin(ilPlugin $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @var array Contains the platform data
     */
    private array $platform_configuration = [];

    public function getPlatformConfiguration(): array
    {
        return $this->platform_configuration;
    }

    public function setPlatformConfiguration(array $platform_configuration): void
    {
        $this->platform_configuration = $platform_configuration;
    }

    /* ILIAS CORE ATTRIBUTES END */

    /* ILIAS VERSION SPECIFIC ATTRIBUTES BEGIN */

    //plugin attributes

    /**
     * @var float|null
     */
    private ?float $reached_points = null;

    /**
     * @var int
     */
    private int $hidden = 0;

    /**
     * @var float
     */
    private float $penalty = 0.0;

    /**
     * @var array The user answer given in each input
     */
    private array $user_response = array();

    /**
     * @var bool true when question has been instantiated with a seed
     */
    private bool $instantiated = false;

    /**
     * @var array
     */
    private array $evaluation = array();

    /* ILIAS VERSION SPECIFIC ATTRIBUTES END */

    /* STACK CORE ATTRIBUTES BEGIN */

    //question attributes

    /**
     * @var string STACK specific: Holds the version of the question when it was last saved.
     */
    public string $stack_version;

    /**
     * @var string|null STACK specific: variables, as authored by the teacher.
     */
    public ?string $question_variables;

    /**
     * @var string|null STACK specific: variables, as authored by the teacher.
     */
    public ?string $question_note;

    /**
     * @var string|null Any specific feedback for this question. This is displayed
     * in the 'yellow' feedback area of the question. It can contain PRT_feedback
     * tags, but not IE_feedback.
     */
    public ?string $specific_feedback;

    /** @var int|null one of the FORMAT_... constants */
    public ?int $specific_feedback_format;

    /** @var string|null Feedback that is displayed for any PRT that returns a score of 1. */
    public ?string $prt_correct;

    /** @var int|null one of the FORMAT_... constants */
    public ?int $prt_correct_format;

    /** @var string|null Feedback that is displayed for any PRT that returns a score between 0 and 1. */
    public ?string $prt_partially_correct;

    /** @var int|null one of the FORMAT_... constants */
    public ?int $prt_partially_correct_format;

    /** @var string|null Feedback that is displayed for any PRT that returns a score of 0. */
    public ?string $prt_incorrect;

    /** @var int|null one of the FORMAT_... constants */
    public ?int $prt_incorrect_format;

    /** @var string|null if set, this is used to control the pseudo-random generation of the seed. */
    public ?string $variants_selection_seed;

    /**
     * @var stack_input[] STACK specific: string name as it appears in the question text => stack_input
     */
    public array $inputs = array();

    /**
     * @var stack_potentialresponse_tree_lite[] STACK specific: responses tree number => ...
     */
    public array $prts = array();

    /**
     * @var stack_options STACK specific: question-level options.
     */
    public stack_options $options;

    /**
     * @var int[] of seed values that have been deployed.
     */
    public array $deployed_seeds;

    /**
     * @var int|null STACK specific: seeds Maxima's random number generator.
     */
    public ?int $seed = null;

    /**
     * @var array Question Unit Tests.
     */
    public array $unit_tests = array();

    /**
     * @var stack_cas_session2 STACK specific: session of variables.
     */
    protected stack_cas_session2 $session;

    /**
     * @var stack_ast_container[] STACK specific: the teacher's answers for each input.
     */
    private array $tas = [];

    /**
     * @var stack_cas_security the question level common security
     * settings, i.e. forbidden keys and whether units are in play.
     * Note that the security-object is used to enforce read-only
     * identifiers and therefore whether we are dealing with units
     * is important to it, as obviously one should not redefine units.
     */
    private stack_cas_security $security;

    /**
     * @var castext2_evaluatable|null STACK specific: variant specifying castext fragment.
     */
    public ?castext2_evaluatable $question_note_instantiated = null;

    /**
     * @var castext2_evaluatable|null instantiated version of question_text.
     * Initialised in start_attempt / apply_attempt_state.
     */
    public ?castext2_evaluatable $question_text_instantiated = null;

    /**
     * @var castext2_evaluatable|null instantiated version of question description.
     * Initialised in start_attempt / apply_attempt_state.
     */
    public ?castext2_evaluatable $question_description_instantiated = null;

    /**
     * @var castext2_evaluatable|null instantiated version of specific_feedback.
     * Initialised in start_attempt / apply_attempt_state.
     */
    public ?castext2_evaluatable $specific_feedback_instantiated = null;

    /**
     * @var castext2_evaluatable|null instantiated version of general feedback.
     * Initialised in start_attempt / apply_attempt_state.
     */
    public ?castext2_evaluatable $general_feedback_instantiated = null;

    /**
     * @var castext2_evaluatable|null instantiated version of prt_correct.
     * Initialised in start_attempt / apply_attempt_state.
     */
    public ?castext2_evaluatable $prt_correct_instantiated = null;

    /**
     * @var castext2_evaluatable|null instantiated version of prt_partially_correct.
     * Initialised in start_attempt / apply_attempt_state.
     */
    public ?castext2_evaluatable $prt_partially_correct_instantiated = null;

    /**
     * @var castext2_evaluatable|null instantiated version of prt_incorrect.
     * Initialised in start_attempt / apply_attempt_state.
     */
    public ?castext2_evaluatable $prt_incorrect_instantiated = null;

    /**
     * @var castext2_processor|null
     */
    private ?castext2_processor $cas_text_processor = null;

    /**
     * @var array Errors generated at runtime.
     * Any errors are stored as the keys to prevent duplicates.  Values are ignored.
     */
    public array $runtime_errors = array();

    /**
     * The next three fields cache the results of some expensive computations.
     * The cache is only valid for a particular response, so we store the current
     * response, so that we can learn the cached information in the result changes.
     * See {@link validate_cache()}.
     * @var array|null
     */
    protected ?array $last_response = null;

    /**
     * @var bool|null like $last_response, but for the $accept_valid argument to {@link validate_cache()}.
     */
    protected ?bool $last_accept_valid = null;

    /**
     * @var stack_input_state[] input name => stack_input_state.
     * This caches the results of validate_student_response for $last_response.
     */
    protected array $input_states = array();

    /**
     * @var array prt name => result of evaluate_response, if known.
     */
    protected array $prt_results = array();

    /**
     * @var array set of expensive to evaluate but static things.
     */
    public array $compiled_cache = [];

    //questionbase attributes

    /**
     * @var string|null question general feedback.
     */
    public ?string $general_feedback;

    /* STACK CORE ATTRIBUTES END */

    /* ILIAS REQUIRED METHODS BEGIN */

    /**
     * @return string ILIAS question type name
     */
    public function getQuestionType(): string
    {
        return "assStackQuestion";
    }

    /*Used in Feedback Button on Question List at non started Test*/
    public function getAnswers()
    {
        return array();
    }

    /**
     * CONSTRUCTOR.
     * @param string $title
     * @param string $comment
     * @param string $author
     * @param int $owner
     * @param string $question
     */
    function __construct($title = "", $comment = "", $author = "", $owner = -1, $question = "")
    {
        parent::__construct($title, $comment, $author, $owner, $question);

        StackPlatform::initialize('ilias');

        try {
            $this->setPlugin(ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", "assStackQuestion"));
        } catch (ilPluginException $e) {
            ilUtil::sendFailure($e, true);
        }

        //Get stored settings from the platform database
        $this->setPlatformConfiguration(StackConfig::getAll());

        //For some reason we should initialize lasttime for new questions, it seems not been donE in assQuestion Constructor
        $this->setLastChange(time());
        //Initialize some STACK required parameters
        require_once __DIR__ . '/utils/class.assStackQuestionInitialization.php';
        require_once(__DIR__ . '/utils/locallib.php');
    }

    //assQuestion abstract methods

    /**
     * Saves evaluation to user response in Test into tst_solutions
     * @param int $active_id
     * @param null $pass
     * @param bool $authorized
     * @return bool
     */
    public function saveWorkingData($active_id, $pass = null, $authorized = true): bool
    {
        global $DIC;
        $db = $DIC->database();

        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        //Determine seed for current test run
        $seed = assStackQuestionDB::_getSeed("test", $this, (int)$active_id, (int)$pass);

        $entered_values = 0;
        $user_solution = $this->getSolutionSubmit();

        //debug
        if (isset($user_solution['test_player_navigation_url'])) {
            $navigation_url = $user_solution['test_player_navigation_url'];
            unset($user_solution['test_player_navigation_url']);
        }

        //Instantiate Question if not.
        if (!$this->isInstantiated()) {
            $this->questionInitialisation($seed, true);
        }

        //Evaluate user response
        //Ensure evaluation has been done

        if (empty($this->getEvaluation())) {
            try{
                $this->evaluateQuestion($user_solution);
            } catch (stack_exception|StackException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
            }
        }

        //Save user test solution
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized) {
            //Remove previous solution
            $this->removeCurrentSolution($active_id, $pass, $authorized);
            //Add current solution
            $entered_values = assStackQuestionDB::_saveUserTestSolution($this, (int)$active_id, (int)$pass, $authorized);
        });


        if ($entered_values) {
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng('assessment', 'log_user_entered_values', ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            }
        } else {
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng('assessment', 'log_user_not_entered_values', ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            }
        }

        return true;
    }

    /**
     * Calculates points reached in Test
     * @param int $active_id
     * @param null $pass
     * @param bool $authorized_solution
     * @param false $return_details
     * @return float|int
     */
    public function calculateReachedPoints($active_id, $pass = null, $authorized_solution = true, $return_details = false)
    {
        global $DIC;
        $db = $DIC->database();


        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }

        // get all saved part solutions with points assigned
        $solution = $db->query("SELECT value1, value2, points FROM tst_solutions WHERE question_fi = " .
            $db->quote($this->getId(), 'integer') . " AND active_fi = " .
            $db->quote($active_id, 'integer') . " AND pass = " .
            $db->quote($pass, 'integer'));

        $tst_solutions = array();

        while ($row = $db->fetchAssoc($solution)) {
            $tst_solutions[] = $row;
        }

        $points = 0;

        if (count($tst_solutions) > 0 && $tst_solutions[0]['value1'] != "xqcas_raw_data") {
            // old format
            foreach ($tst_solutions as $row) {
                $points += (float) $row['points'];
            }
        } else {
            $parsed_user_response_from_db = (array) json_decode($tst_solutions[0]['value2']);

            $points = (float) $parsed_user_response_from_db['total_points'];
        }

        return $points;
    }


    /**
     * Duplicates the question in the same directory
     * @param bool $for_test
     * @param string $title
     * @param string $author
     * @param string $owner
     * @param null $test_obj_id
     * @return int|null the duplicated question id
     */
    public function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
        }
        // duplicate the question in database
        $this_id = $this->getId();
        $thisObjId = $this->getObjId();

        $clone = $this;
        //include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;

        if ((int)$testObjId > 0) {
            $clone->setObjId($testObjId);
        }

        if ($title) {
            $clone->setTitle($title);
        }
        if ($author) {
            $clone->setAuthor($author);
        }
        if ($owner) {
            $clone->setOwner($owner);
        }
        if ($for_test) {
            $clone->saveToDb($original_id);
        } else {
            $clone->saveToDb();
        }
        // copy question page content
        $clone->copyPageOfQuestion($this_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($this_id);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->getId();
    }

    /**
     * Copies an assStackQuestion object into the Clipboard
     *
     * @param integer $target_questionpool_id
     * @param string $title
     *
     * @return void|integer Id of the clone or nothing.
     */
    function copyObject(int $target_questionpool_id, string $title = "")
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }
        // duplicate the question in database
        $clone = $this;
        //include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");

        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;
        $source_questionpool_id = $this->getObjId();
        $clone->setObjId($target_questionpool_id);
        if ($title) {
            $clone->setTitle($title);
        }
        $clone->saveToDb("", TRUE);
        // copy question page content
        $clone->copyPageOfQuestion($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);

        $clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
     * Copies the question into a question pool
     * @param $targetParentId
     * @param string $targetQuestionTitle
     * @return int
     */
    public function createNewOriginalFromThisDuplicate($targetParentId, string $targetQuestionTitle = ""): int
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
        }

        //include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");

        $sourceQuestionId = $this->id;
        $sourceParentId = $this->getObjId();

        // duplicate the question in database
        $clone = $this;
        $clone->id = -1;

        $clone->setObjId($targetParentId);

        if ($targetQuestionTitle) {
            $clone->setTitle($targetQuestionTitle);
        }

        $clone->saveToDb();
        // copy question page content
        $clone->copyPageOfQuestion($sourceQuestionId);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    //iQuestionCondition methods

    /**
     * Get all available operations for a specific question
     *
     * @param $expression
     *
     * @return array
     * @internal param string $expression_type
     */
    public function getOperators($expression): array
    {
        //require_once "./Modules/TestQuestionPool/classes/class.ilOperatorsExpressionMapping.php";

        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    /**
     * Get all available expression types for a specific question
     *
     * @return array
     */
    public function getExpressionTypes(): array
    {
        return array(iQuestionCondition::PercentageResultExpression);
    }

    /**
     * Get the user solution for a question by active_id and the test pass
     *
     * @param int $active_id
     * @param int $pass
     *
     * @return ilUserQuestionResult
     */
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult
    {
        $result = new ilUserQuestionResult($this, $active_id, $pass);
        $points = (float)$this->calculateReachedPoints($active_id, $pass);
        $max_points = (float)$this->getMaximumPoints();
        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     *
     * @return array
     */
    public function getAvailableAnswerOptions($index = null)
    {
        return array();
    }

    /**
     * @param ilAssQuestionPreviewSession $previewSession
     * @return void
     */
    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $submittedAnswer = $this->getSolutionSubmit();
        if (!empty($submittedAnswer)) {
            $previewSession->setParticipantsSolution($submittedAnswer);

            assStackQuestionDB::_savePreviewSolution($this->getId(), $submittedAnswer);
        }
    }

    /**
     * @param array $valuePairs
     * @return array $indexedValues
     */
    public function fetchIndexedValuesFromValuePairs(array $valuePairs): array
    {
        return $valuePairs;
    }

    /**
     * @return bool
     */
    public function validateSolutionSubmit(): bool
    {
        return true;
    }

    /**
     * Removes an existing solution without removing the variables (specific for STACK question: don't delete seeds)
     * Called by resetting user answer
     * @param int $activeId
     * @param int $pass
     * @return int
     */
    public function removeExistingSolutions($activeId, $pass): int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = /** @lang text */
            "
			DELETE FROM tst_solutions
			WHERE active_fi = " . $ilDB->quote($activeId, 'integer') . "
			AND question_fi = " . $ilDB->quote($this->getId(), 'integer') . "
			AND pass = " . $ilDB->quote($pass, 'integer') . "
			AND value1 not like '%_seed'
		";

        return $ilDB->manipulate($query);
    }

    //assQuestion

    /**
     * Gets all the data of an assStackQuestion from the DB
     * Called by assStackQuestionGUI Constructor
     * For new questions, loads the standard values from xqcas_configuration.
     *
     * @param integer $question_id A unique key which defines the question in the database
     * @throws stack_exception
     */
    public function loadFromDb($question_id)
    {
        global $DIC;

        $db = $DIC->database();
        //load the basic question data
        $result = $db->query("SELECT qpl_questions.* FROM qpl_questions WHERE question_id = " . $db->quote($question_id, 'integer'));

        $data = $db->fetchAssoc($result);
        $this->setId($question_id);
        $this->setTitle($data["title"]);
        $this->setComment($data["description"]);
        $this->setSuggestedSolution($data["solution_hint"]);
        $this->setOriginalId($data["original_id"]);
        $this->setObjId($data["obj_fi"]);
        $this->setAuthor($data["author"]);
        $this->setOwner($data["owner"]);
        $this->setPoints($data["points"]);
        $this->lastChange = $data['tstamp'];

        //set question text
        $this->setQuestion($data["question_text"]);

        try {
            $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
        } catch (ilTestQuestionPoolInvalidArgumentException $e) {
            $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
        }

        //require_once("./Services/RTE/classes/class.ilRTE.php");
        $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
        $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));

        //Load the specific assStackQuestion data from DB
        //$this->getPlugin()->includeClass('class.assStackQuestionDB.php');

        $options_from_db_array = assStackQuestionDB::_readOptions($this->getId());
        if ($options_from_db_array === -1) {

            //NEW QUESTION, LOAD STANDARD INFORMATION FROM CONFIGURATION
            $this->loadStandardQuestion();

        } else {

            //EXISTING QUESTION, LOAD INFORMATION FROM DB

            //load options
            try {
                $options = new stack_options($options_from_db_array['options']);
                //SET OPTIONS
                $this->options = $options;
            } catch (stack_exception $e) {
                ilUtil::sendFailure($e, true);
            }

            //load Data stored in options but not part of the session options
            $this->question_variables = $options_from_db_array['ilias_options']['question_variables'];
            $this->question_note = $options_from_db_array['ilias_options']['question_note'];

            $this->specific_feedback = $options_from_db_array['ilias_options']['specific_feedback'];
            $this->specific_feedback_format = $options_from_db_array['ilias_options']['specific_feedback_format'];

            $this->prt_correct = $options_from_db_array['ilias_options']['prt_correct'];
            $this->prt_correct_format = $options_from_db_array['ilias_options']['prt_correct_format'];
            $this->prt_partially_correct = $options_from_db_array['ilias_options']['prt_partially_correct'];
            $this->prt_partially_correct_format = $options_from_db_array['ilias_options']['prt_partially_correct_format'];
            $this->prt_incorrect = $options_from_db_array['ilias_options']['prt_incorrect'];
            $this->prt_incorrect_format = $options_from_db_array['ilias_options']['prt_incorrect_format'];

            $this->variants_selection_seed = $options_from_db_array['ilias_options']['variants_selection_seed'];

            //stack version
            if (isset($options_from_db_array['ilias_options']['stack_version']) and $options_from_db_array['ilias_options']['stack_version'] !== null) {
                $this->stack_version = $options_from_db_array['ilias_options']['stack_version'];
            } else {
                //Stack version TODO CONFIG
                $this->stack_version = '2023121100';
            }

            //load inputs
            $inputs_from_db_array = assStackQuestionDB::_readInputs($question_id);

            $required_parameters = stack_input_factory::get_parameters_used();

            $new_inputs = array();

            //load only those inputs appearing in the question text
            foreach (stack_utils::extract_placeholders($this->getQuestion(), 'input') as $name) {

                $input_data = $inputs_from_db_array['inputs'][$name];

                //Adjust syntax Hint for Textareas
                //Firstline shown as irstlin

                /*
                 * str_starts_with NOT available until ilias8
				if ($input_data['type'] == 'equiv' || $input_data['type'] == 'textarea') {
					if (strlen($input_data['syntax_hint']) and !str_starts_with($input_data['syntax_hint'], '[')) {
						$input_data['syntax_hint'] = '[' . $input_data['syntax_hint'] . ']';
					}
					if (strlen($input_data['tans']) and !str_starts_with($input_data['tans'], '[')) {
						$input_data['tans'] = '[' . $input_data['tans'] . ']';
					}
				}*/

                $all_parameters = array(
                    'boxWidth' => $input_data['box_size'],
                    'strictSyntax' => $input_data['strict_syntax'],
                    'insertStars' => $input_data['insert_stars'],
                    'syntaxHint' => $input_data['syntax_hint'],
                    'syntaxAttribute' => $input_data['syntax_attribute'],
                    'forbidWords' => $input_data['forbid_words'],
                    'allowWords' => $input_data['allow_words'],
                    'forbidFloats' => $input_data['forbid_float'],
                    'lowestTerms' => $input_data['require_lowest_terms'],
                    'sameType' => $input_data['check_answer_type'],
                    'mustVerify' => $input_data['must_verify'],
                    'showValidation' => $input_data['show_validation'],
                    'options' => $input_data['options'],
                );

                $parameters = array();

                //We collect the non-existing inputs present in the question text
                //We take care about them later
                //Otherwise we proceed we normal loading
                if ($input_data == null) {
                    $new_inputs[$name] = '';
                } else {
                    foreach ($required_parameters[$input_data['type']] as $parameter_name) {
                        if ($parameter_name == 'inputType') {
                            continue;
                        }
                        $parameters[$parameter_name] = $all_parameters[$parameter_name];
                    }

                    //SET INPUTS
                    $this->inputs[$name] = stack_input_factory::make($input_data['type'], $input_data['name'], $input_data['tans'], $this->options, $parameters);
                }

            }

            //If an input placeholder has appeared in the text, but it is not in the DB
            //we create the new Input as default, load it to the question.
            //And save it into the DB.
            foreach ($new_inputs as $input_name => $i_value) {
                $this->loadStandardInput($input_name);
                assStackQuestionDB::_saveInput((string)$this->getId(), $this->inputs[$input_name]);
            }

            //get PRT and PRT Nodes from DB

            $prt_from_db_array = assStackQuestionDB::_readPRTs($question_id);

            //$this->getPlugin()->includeClass('utils/class.assStackQuestionUtils.php');
            $prt_names = assStackQuestionUtils::_getPRTNamesFromQuestion($this->getQuestion(), $options_from_db_array['ilias_options']['specific_feedback'], $prt_from_db_array);

            $total_value = 0;
            $all_formative = true;

            foreach ($prt_names as $name) {
                // If not then we have just created the PRT.
                if (array_key_exists($name, $prt_from_db_array)) {
                    $prt_data = $prt_from_db_array[$name];

                    $total_value += $prt_data->value;
                    $all_formative = false;
                }
            }

            if ($prt_from_db_array && !$all_formative && $total_value < 0.0000001) {
                throw new stack_exception('There is an error authoring your question. ' .
                    'The $totalvalue, the marks available for the question, must be positive in question ' .
                    $data["title"]);
            }

            foreach ($prt_names as $name) {
                if (array_key_exists($name, $prt_from_db_array)) {
                    $prt_value = 0;
                    if (!$all_formative) {
                        $prt_value = $prt_from_db_array[$name]->value / $total_value;
                    }
                    $this->prts[$name] = new stack_potentialresponse_tree_lite($prt_from_db_array[$name], $prt_value);
                } // If not we just added a PRT.
            }

            //load seeds
            $deployed_seeds = assStackQuestionDB::_readDeployedVariants($question_id);

            //Needs deployed seeds as key for initialisation
            $depured_deployed_seeds = array();
            foreach ($deployed_seeds as $deployed_seed) {
                $depured_deployed_seeds[$deployed_seed] = $deployed_seed;
            }
            $this->deployed_seeds = $depured_deployed_seeds;

            //load extra info
            $extra_info = assStackQuestionDB::_readExtraInformation($question_id);
            if (is_array($extra_info)) {
                $this->general_feedback = $extra_info['general_feedback'];
                $this->penalty = (float)$extra_info['penalty'];
                $this->hidden = (int)$extra_info['hidden'];
            } else {
                $this->general_feedback = '';
                $this->penalty = 0.0;
                $this->hidden = 0;
            }

            //load unit tests
            $unit_tests = assStackQuestionDB::_readUnitTests($question_id);
            $this->setUnitTests($unit_tests);
        }


        // loads additional stuff like suggested solutions
        parent::loadFromDb($question_id);
    }

    /**
     * Returns the user response given per $_POST
     * Used in Question Preview
     * @return array
     */
    public function getSolutionSubmit(): array
    {
        //RETURN DATA FROM POST
        $user_response_from_post = $_POST;
        unset($user_response_from_post["formtimestamp"]);
        unset($user_response_from_post["cmd"]);

        $user_solutions = assStackQuestionUtils::_adaptUserResponseTo($user_response_from_post, $this->getId(), "only_input_names");

        //Debug
        if (isset($user_solutions['test_player_navigation_url'])) {
            unset($user_solutions['test_player_navigation_url']);
        }

        return $user_solutions;
    }

    function oldTestImportIdFinder($string, $starts_with)
    {
        if (substr($string, 0, strlen($starts_with)) === $starts_with) {
            return substr($string, strlen($starts_with));
        }
        return $this->getId();
    }


    //Import and Export

    /**
     * Creates a question from a QTI file
     *
     * Receives parameters from a QTI parser and creates a valid ILIAS question object
     *
     * @param object $item The QTI item object
     * @param integer $questionpool_id The id of the parent questionpool
     * @param integer $tst_id The id of the parent test if the question is part of a test
     * @param object $tst_object A reference to the parent test object
     * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
     * @param array $import_mapping An array containing references to included ILIAS objects
     */
    public function fromXML(
        &$item,
        &$questionpool_id,
        &$tst_id,
        &$tst_object,
        &$question_counter,
        &$import_mapping,
        array $solutionhints = []
    )
    {
        //$this->getPlugin()->includeClass('import/qti12/class.assStackQuestionImport.php');
        $import = new assStackQuestionImport($this);
        return $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);

    }

    /**
     * Returns a QTI xml representation of the question and sets the internal
     * domxml variable with the DOM XML representation of the QTI xml representation
     * @param bool $a_include_header
     * @param bool $a_include_binary
     * @param bool $a_shuffle
     * @param bool $test_output
     * @param bool $force_image_references
     * @return string The QTI xml representation of the question
     */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        //$this->getPlugin()->includeClass('model/export/qti12/class.assStackQuestionExport.php');
        $export = new assStackQuestionExport($this);

        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    //Question Points

    /**
     * Calculate the points a user has reached in a preview session
     * @param ilAssQuestionPreviewSession $previewSession
     * @return float
     */
    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession): float
    {
        $points = 0.0;

        if (!empty($this->getEvaluation())) {
            $points = $this->getEvaluation()['points']['total'];
        }
        return $points;
    }

    //Save to DB
    //Authoring Interface

    /**
     * Saves a assStackQuestion object to the database
     *
     * @param string $original_id
     *
     */
    public function saveToDb($original_id = ""): void
    {
        if ($this->getTitle() != "" and $this->getAuthor() != "" and $this->getQuestion() != "") {
            $this->saveQuestionDataToDb($this->getOriginalId());
            $this->saveAdditionalQuestionDataToDb();

            parent::saveToDb($this->getOriginalId());
        } else {
            ilUtil::sendFailure($this->getPlugin()->txt('error_fields_missing'), 1);
        }
    }

    /**
     * Saves the STACK related parameters of the questions
     * @return void
     */
    public function saveAdditionalQuestionDataToDb()
    {
        //$this->getPlugin()->includeClass('class.assStackQuestionDB.php');
        try {
            assStackQuestionDB::_saveStackQuestion($this);
        } catch (stack_exception $e) {
            ilUtil::sendFailure($e);
        }
    }

    /**
     * Checks if question has minimum requirements
     * @return bool
     */
    function isComplete(): bool
    {
        if (strlen($this->title)
            && $this->author
            && $this->question
            && $this->getMaximumPoints() > 0
        ) {
            return true;
        }
        return false;
    }

    /**
     * Deletes the question from the DB
     * @param int $question_id
     */
    public function delete($question_id)
    {
        //delete general question data
        parent::delete($question_id);

        //$this->getPlugin()->includeClass('class.assStackQuestionDB.php');
        //delete stack specific question data
        assStackQuestionDB::_deleteStackQuestion((int)$question_id);
    }

    /* ILIAS OVERWRITTEN METHODS END */

    /* ILIAS SPECIFIC METHODS BEGIN */

    /**
     * Evaluates the question
     * @param array $user_response
     * @return bool
     * @throws StackException | stack_exception
     */
    public function evaluateQuestion(array $user_response): bool
    {
        $fraction = 0;
        $total_weight = 0;
        $evaluation_data = [];

        foreach ($this->prts as $prt_name => $prt) {
            if ($prt->is_formative()) {
                continue;
            }

            $frac = 0;
            //$accumulated_penalty = 0;
            //$last_input = [];
            //$penalty_to_apply = null;

            //$prt_input = $this->getPrtInput($prt_name, $user_response, true);

            //if (!$this->isSamePRTInput($prt_name, $last_input, $prt_input)) {
            //$penalty_to_apply = $accumulated_penalty;
            //    $last_input = $prt_input;
            //}

            $results = $this->getPrtResult($prt_name, $user_response, true);
            $total_weight += $results->getWeight();
            $evaluation_data['prts'][$prt_name]['prt_result'] = $results;

            if ($this->canExecutePrt($this->prts[$prt_name], $user_response, true)) {
                //$accumulated_penalty += $results->get_fractionalpenalty();
                $frac = (float)$results->get_fraction();

                //Set Feedback type
                if ($frac <= 0.0) {
                    $evaluation_data['points'][$prt_name]['status'] = 'incorrect';
                } elseif ($frac == $results->getWeight()) {
                    $evaluation_data['points'][$prt_name]['status'] = 'correct';
                } elseif ($frac < $results->getWeight()) {
                    $evaluation_data['points'][$prt_name]['status'] = 'partially_correct';
                } else {
                    throw new StackException('Error,  more points given than MAX Points');
                }
            } else {
                $evaluation_data['points'][$prt_name]['status'] = 'incorrect';
            }

            $fraction += max($frac, 0);
            $evaluation_data['points'][$prt_name]['prt_points'] = $frac;
        }

        if ($total_weight > 0) {
            $ilias_points = ($fraction / $total_weight) * $this->getMaximumPoints();
        } else {
            throw new StackException('No points available for evaluation');
        }

        $evaluation_data['points']['total'] = (float)$ilias_points;


        if ($fraction > $this->getMaximumPoints()) {
            throw new StackException('Error,  more points given than MAX Points');
        }

        //Manage Inputs and Validation
        foreach ($this->inputs as $input_name => $input) {
            $evaluation_data['inputs']['states'][$input_name] = $this->getInputState($input_name, $user_response);
            $evaluation_data['inputs']['validation'][$input_name] = $this->inputs[$input_name]->render_validation($evaluation_data['inputs']['states'][$input_name], $input_name);
        }

        //Mark as evaluated
        $this->setEvaluation($evaluation_data);

        return true;
    }

    /**
     * This function loads the standard values from xqcas_configuration to the question object
     * @throws stack_exception
     */
    public function loadStandardQuestion()
    {
        $standard_question = array();

        //load options
        //require_once __DIR__ . '/model/configuration/class.assStackQuestionConfig.php';
        $standard_options = StackConfig::getAll();
        $options_array = array();

        $options_array['simplify'] = ((int)$standard_options['options_question_simplify']);
        $options_array['assumepos'] = ((int)$standard_options['options_assume_positive']);
        $options_array['assumereal'] = ((int)$standard_options['options_assume_real']);
        $options_array['multiplicationsign'] = ($standard_options['options_multiplication_sign']);
        $options_array['sqrtsign'] = ((int)$standard_options['options_sqrt_sign']);
        $options_array['complexno'] = ($standard_options['options_complex_numbers']);
        $options_array['inversetrig'] = ($standard_options['options_inverse_trigonometric']);
        $options_array['matrixparens'] = ($standard_options['options_matrix_parents']);
        $options_array['logicsymbol'] = ($standard_options['options_logic_symbol']);

        try {
            $options = new stack_options($options_array);

            //Set Options
            $this->options = $options;
        } catch (stack_exception $e) {
            ilUtil::sendFailure($e, true);
        }

        $this->question_variables = '';
        $this->question_note = '';

        //We add the feedback for the first prt to the specific feedback section.
        $this->prt_correct = $standard_options['options_prt_correct'];
        $this->prt_correct_format = 1;
        $this->prt_partially_correct = $standard_options['options_prt_partially_correct'];
        $this->prt_partially_correct_format = 1;
        $this->prt_incorrect = $standard_options['options_prt_incorrect'];
        $this->prt_incorrect_format = 1;

        $this->variants_selection_seed = '';

        //Stack version TODO CONFIG
        $this->stack_version = '2023121100';

        //load standard input
        $this->loadStandardInput('ans1');
        $this->setQuestion('[[input:ans1]] [[validation:ans1]]');

        //load standard PRT
        $this->loadStandardPRT('prt1');
        $this->specific_feedback = ('[[feedback:prt1]]');
        $this->specific_feedback_format = 1;

        //load seeds
        $this->deployed_seeds = array();

        $this->setPoints(1);

        //load extra info
        $this->general_feedback = '';
        $this->penalty = 0.0;
        $this->hidden = 0;
    }

    /**
     * @throws stack_exception
     */
    public function loadStandardInput(string $input_name)
    {
        //Ensure input doesn't exists
        if (!isset($this->inputs[$input_name])) {
            //load standard input
            $standard_input = assStackQuestionConfig::_getStoredSettings('inputs');

            $required_parameters = stack_input_factory::get_parameters_used();

            $all_parameters = array(
                'boxWidth' => $standard_input['input_box_size'],
                'strictSyntax' => $standard_input['input_strict_syntax'],
                'insertStars' => $standard_input['input_insert_stars'],
                'syntaxHint' => $standard_input['input_syntax_hint'],
                'syntaxAttribute' => $standard_input['input_syntax_attribute'],
                'forbidWords' => $standard_input['input_forbidden_words'],
                'allowWords' => $standard_input['input_allow_words'],
                'forbidFloats' => $standard_input['input_forbid_float'],
                'lowestTerms' => $standard_input['input_require_lowest_terms'],
                'sameType' => $standard_input['input_check_answer_type'],
                'mustVerify' => $standard_input['input_must_verify'],
                'showValidation' => $standard_input['input_show_validation'],
                'options' => $standard_input['input_extra_options'],
            );

            $parameters = array();
            foreach ($required_parameters[$standard_input['input_type']] as $parameter_name) {
                if ($parameter_name == 'inputType') {
                    continue;
                }
                $parameters[$parameter_name] = $all_parameters[$parameter_name];
            }

            //Create Input
            $input = stack_input_factory::make($standard_input['input_type'], $input_name, 1, $this->options, $parameters);
            //Load input to the question.
            $this->inputs[$input_name] = $input;
        } else {
            ilUtil::sendInfo('The new input ' . $input_name . ' was already created', true);
        }
    }

    /**
     * @param string $prt_name
     * @param bool $return_standard_node
     * @return void || stack_potentialresponse_node
     */
    public function loadStandardPRT(string $prt_name, bool $return_standard_node = false)
    {
        //load PRTs and PRT nodes
        //TODO LOAD PLATFORM STANDARD PRT
        $standard_prt = assStackQuestionConfig::_getStoredSettings('prts');

        try {

            $prt = new stdClass;
            $prt->name = 'ans';
            $prt->id = 0;
            $prt->value = 1;
            $prt->feedbackstyle = 1;
            $prt->feedbackvariables = '';
            $prt->firstnodename = '0';
            $prt->nodes = [];
            $prt->autosimplify = true;
            $newnode = new stdClass;
            $newnode->id = '0';
            $newnode->nodename = '0';
            $newnode->prtname = 'ans';
            $newnode->description = '';
            $newnode->sans = 'ans1';
            $newnode->tans = 'ta';
            $newnode->answertest = 'AlgEquiv';
            $newnode->testoptions = '';
            $newnode->quiet = false;
            $newnode->falsescore = '0';
            $newnode->falsescoremode = '=';
            $newnode->falsepenalty = 0;
            $newnode->falsefeedback = '';
            $newnode->falsefeedbackformat = '1';
            $newnode->falseanswernote = 'ans-0-F';
            $newnode->falsenextnode = '1';
            $newnode->truescore = 'sc2';
            $newnode->truescoremode = '=';
            $newnode->truepenalty = 0;
            $newnode->truefeedback = '';
            $newnode->truefeedbackformat = '1';
            $newnode->trueanswernote = 'ans-0-T';
            $newnode->truenextnode = '-1';
            $prt->nodes[] = $newnode;
            $newnode = new stdClass;
            $newnode->id = '1';
            $newnode->nodename = '1';
            $newnode->prtname = 'ans';
            $newnode->description = '';
            $newnode->sans = 'ans1';
            $newnode->tans = '{p}';
            $newnode->answertest = 'AlgEquiv';
            $newnode->testoptions = '';
            $newnode->quiet = false;
            $newnode->falsescore = '0';
            $newnode->falsescoremode = '=';
            $newnode->falsepenalty = 0;
            $newnode->falsefeedback = '';
            $newnode->falsefeedbackformat = '1';
            $newnode->falseanswernote = 'ans-1-F';
            $newnode->falsenextnode = '2';
            $newnode->truescore = '0';
            $newnode->truescoremode = '=';
            $newnode->truepenalty = 0;
            $newnode->truefeedback = '';
            $newnode->truefeedbackformat = '1';
            $newnode->trueanswernote = 'ans-1-T';
            $newnode->truenextnode = '-1';
            $prt->nodes[] = $newnode;
            $newnode = new stdClass;
            $newnode->id = '2';
            $newnode->nodename = '2';
            $newnode->prtname = 'ans';
            $newnode->description = '';
            $newnode->sans = 'a1';
            $newnode->tans = '{0}';
            $newnode->answertest = 'AlgEquiv';
            $newnode->testoptions = '';
            $newnode->quiet = true;
            $newnode->falsescore = '0';
            $newnode->falsescoremode = '=';
            $newnode->falsepenalty = 0;
            $newnode->falsefeedback = '';
            $newnode->falsefeedbackformat = '1';
            $newnode->falseanswernote = 'ans-2-F';
            $newnode->falsenextnode = '-1';
            $newnode->truescore = 'sc2';
            $newnode->truescoremode = '=';
            $newnode->truepenalty = 0;
            $newnode->truefeedback =
                'All your answers satisfy the equation. But, you have missed some of the solutions.';
            $newnode->truefeedbackformat = '1';
            $newnode->trueanswernote = 'ans-2-T';
            $newnode->truenextnode = '-1';
            $prt->nodes[] = $newnode;
            $this->prts[$prt->name] = new stack_potentialresponse_tree_lite($prt, $prt->value, null);
        } catch (stack_exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
        }
    }

    /* ILIAS SPECIFIC METHODS END */

    /* STACK CORE METHODS BEGIN */

    /**
     * Make sure the cache is valid for the current response. If not, clear it.
     *
     * @param array $response the response.
     * @param bool|null $accept_valid if this is true, then we will grade things even
     * if the corresponding inputs are only VALID, and not SCORE.
     */
    public function validateCache(array $response, bool $accept_valid = null)
    {
        if (is_null($this->getLastResponse())) {
            $this->setLastResponse($response);
            $this->setLastAcceptValid($accept_valid);
            return;
        }

        // We really need the PHP === here, as "0.040" == "0.04", even as strings.
        // See https://stackoverflow.com/questions/80646/ for details.
        if ($this->getLastResponse() === $response && ($this->getLastAcceptValid() === null || $accept_valid === null || $this->getLastAcceptValid() === $accept_valid)) {
            if ($this->getLastAcceptValid() === null) {
                $this->setLastAcceptValid($accept_valid);
            }
            return; // Cache is good.
        }

        // Clear the cache.
        $this->setLastResponse($response);
        $this->setLastAcceptValid($accept_valid);
        $this->setInputStates(array());
        $this->setPrtResults(array());
    }

    /* make_behaviour() not required as behaviours are only Moodle relevant */

    /**
     * start_attempt(question_attempt_step $step, $variant) method
     * Transferred to ILIAS as questionInitialisation();
     * @param int|null $variant
     * @param bool $force_variant
     * @param bool $deployed_seeds_view true only in authoring mode / deployed seeds view
     * @throws stack_exception
     */
    public function questionInitialisation(?int $variant, bool $force_variant = false, bool $deployed_seeds_view = false)
    {
        //Initialize Options
        if (!is_a($this->options, 'stack_options')) {
            $this->options = new stack_options();
        }

        // @codingStandardsIgnoreStart
        // Work out the right seed to use.
        if (is_null($this->seed) or $deployed_seeds_view) {
            if ($force_variant) {
                $this->seed = $variant;
            } else if (!$this->hasRandomVariants()) {
                // Randomisation not used.
                $this->seed = 1;
            } else if (!empty($this->deployed_seeds)) {
                // Question has a fixed number of variants.
                $this->seed = $this->deployed_seeds[$variant - 1] + 0;
                // Don't know why this is coming out as a string. + 0 converts to int.
            } else {
                // This question uses completely free randomisation.
                $this->seed = $variant;
            }
        }

        $this->initialiseQuestionFromSeed();
    }

    /**
     * INITIALISATION MAIN METHOD
     * initialise_question_from_seed() Method in Moodle
     * Once we know the random seed, we can initialise all the other parts of the question.
     * @throws stack_exception
     */
    public function initialiseQuestionFromSeed()
    {
        // We can detect a logically faulty question by checking if the cache can
        // return anything if it can't then we can simply skip to the output of errors.
        if ($this->getCached('units') !== null) {
            // Build up the question session out of all the bits that need to go into it.
            // 1. question variables.
            $session = new stack_cas_session2([], $this->options, $this->seed);

            // If we are using localisation we should tell the CAS side logic about it.
            // For castext rendering and other tasks.
            /* not using moodle multi-lang
            if (count($this->getCached('langs')) > 0) {
                $ml = new stack_multilang();
                $selected = $ml->pick_lang($this->getCached('langs'));
                $session->add_statement(new stack_secure_loader('%_STACK_LANG:' .
                    stack_utils::php_string_to_maxima_string($selected), 'language setting'), false);
            }*/

            // Construct the security object. But first units declaration into the session.
            $units = (boolean)$this->getCached('units');

            // If we have units we might as well include the units declaration in the session.
            // To simplify authors work and remove the need to call that long function.
            // TODO: Maybe add this to the preable to save lines, but for now documented here.
            if ($units) {
                $session->add_statement(new stack_secure_loader('stack_unit_si_declare(true)',
                    'automatic unit declaration'), false);
            }

            if ($this->getCached('preamble-qv') !== null) {
                $session->add_statement(new stack_secure_loader((string)$this->getCached('preamble-qv'), 'preamble'));
            }
            // Context variables should be first.
            if ($this->getCached('contextvariables-qv') !== null) {
                $session->add_statement(new stack_secure_loader((string)$this->getCached('contextvariables-qv'), '/qv'));
            }
            if ($this->getCached('statement-qv') !== null) {
                $session->add_statement(new stack_secure_loader((string)$this->getCached('statement-qv'), '/qv'));
            }

            // Note that at this phase the security object has no "words".
            // The student's answer may not contain any of the variable names with which
            // the teacher has defined question variables. Otherwise when it is evaluated
            // in a PRT, the student's answer will take these values.   If the teacher defines
            // 'ta' to be the answer, the student could type in 'ta'!  We forbid this.

            // TODO: shouldn't we also protect variables used in PRT logic? Feedback vars
            // and so on?
            $forbiddenkeys = array();
            if ($this->getCached('forbiddenkeys') !== null) {
                $forbiddenkeys = $this->getCached('forbiddenkeys');
            }
            $this->security = new stack_cas_security($units, '', '', $forbiddenkeys);

            // The session to keep. Note we do not need to reinstantiate the teachers answers.
            $sessiontokeep = new stack_cas_session2($session->get_session(), $this->options, $this->seed);

            // 2. correct answer for all inputs.
            foreach ($this->inputs as $name => $input) {
                $cs = stack_ast_container::make_from_teacher_source($input->get_teacher_answer(),
                    '', $this->security);
                $this->tas[$name] = $cs;
                $session->add_statement($cs);
            }

            // Check for signs of errors.
            if ($this->getCached('static-castext-strings') === null) {
                throw new stack_exception(implode('; ', array_keys($this->runtime_errors)));
            }

            // 3.0 setup common CASText2 staticreplacer.
            $static = new castext2_static_replacer($this->getCached('static-castext-strings'));

            // 3. CAS bits inside the question text.
            $questiontext = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-qt'), '/qt', $static);
            if ($questiontext->requires_evaluation()) {
                $session->add_statement($questiontext);
            }

            // 4. CAS bits inside the specific feedback.
            $feedbacktext = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-sf'), '/sf', $static);
            if ($feedbacktext->requires_evaluation()) {
                $session->add_statement($feedbacktext);
            }

            // Add the context to the security, needs some unpacking of the cached.
            if ($this->getCached('security-context') === null) {
                $this->security->set_context([]);
            } else {
                $this->security->set_context($this->getCached('security-context'));
            }

            // The session to keep. Note we do not need to reinstantiate the teachers answers.
            $sessiontokeep = new stack_cas_session2($session->get_session(), $this->options, $this->seed);

            // 5. CAS bits inside the question note.
            $notetext = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-qn'), '/qn', $static);
            if ($notetext->requires_evaluation()) {
                $session->add_statement($notetext);
            }

            // 6. The standard PRT feedback.
            $prtcorrect = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-prt-c'),
                '/pc', $static);
            $prtpartiallycorrect = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-prt-pc'),
                '/pp', $static);
            $prtincorrect = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-prt-ic'),
                '/pi', $static);
            if ($prtcorrect->requires_evaluation()) {
                $session->add_statement($prtcorrect);
            }
            if ($prtpartiallycorrect->requires_evaluation()) {
                $session->add_statement($prtpartiallycorrect);
            }
            if ($prtincorrect->requires_evaluation()) {
                $session->add_statement($prtincorrect);
            }

            // 7. The general feedback.
            $generalfeedback = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-gf'), '/gf', $static);
            if ($generalfeedback->requires_evaluation()) {
                $session->add_statement($generalfeedback);
            }

            // 8. The question description.
            $questiondescription = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-qd'), '/qd', $static);
            if ($questiondescription->requires_evaluation()) {
                $session->add_statement($questiondescription);
            }

            // Now instantiate the session.
            if ($session->get_valid()) {
                $session->instantiate();
            }
            if ($session->get_errors()) {
                // In previous versions we threw an exception here.
                // Upgrade and import stops errors being caught during validation when the question was edited or deployed.
                // This breaks bulk testing in a nasty way.
                $this->runtime_errors[$session->get_errors(true)] = true;
            }

            // Finally, store only those values really needed for later.
            $this->question_text_instantiated = $questiontext;
            if ($questiontext->get_errors()) {
                $s = stack_string('runtimefielderr',
                    array('field' => stack_string('questiontext'), 'err' => $questiontext->get_errors()));
                $this->runtime_errors[$s] = true;
            }
            $this->specific_feedback_instantiated = $feedbacktext;
            if ($feedbacktext->get_errors()) {
                $s = stack_string('runtimefielderr',
                    array('field' => stack_string('specificfeedback'), 'err' => $feedbacktext->get_errors()));
                $this->runtime_errors[$s] = true;
            }
            $this->question_note_instantiated = $notetext;
            if ($notetext->get_errors()) {
                $s = stack_string('runtimefielderr',
                    array('field' => stack_string('questionnote'), 'err' => $notetext->get_errors()));
                $this->runtime_errors[$s] = true;
            }
            $this->prt_correct_instantiated = $prtcorrect;
            $this->prt_partially_correct_instantiated = $prtpartiallycorrect;
            $this->prt_incorrect_instantiated = $prtincorrect;
            $this->session = $sessiontokeep;
            if ($sessiontokeep->get_errors()) {
                $s = stack_string('runtimefielderr',
                    array('field' => stack_string('questionvariables'), 'err' => $sessiontokeep->get_errors(true)));
                $this->runtime_errors[$s] = true;
            }

            // Allow inputs to update themselves based on the model answers.
            $this->adaptInputs();

            $this->general_feedback_instantiated = $generalfeedback;
            $this->question_description_instantiated = $questiondescription;
        }

        if ($this->runtime_errors) {
            // It is quite possible that questions will, legitimately, throw some kind of error.
            // For example, if one of the question variables is 1/0.
            // This should not be a show stopper.
            // Something has gone wrong here, and the student will be shown nothing.
            $s = html_writer::tag('span', stack_string('runtimeerror'), array('class' => 'stackruntimeerrror'));
            $errmsg = '';
            foreach ($this->runtime_errors as $key => $val) {
                $errmsg .= html_writer::tag('li', $key);
            }
            $s .= html_writer::tag('ul', $errmsg);
            // So we have this logic where a raw string needs to turn to a CASText2 object.
            // As we do not know what it contains we escape it.
            $this->question_text_instantiated = castext2_evaluatable::make_from_source('[[escape]]' . $s . '[[/escape]]', '/qt');
            // It is a static string and by calling this we make it look like it was evaluated.
            $this->question_text_instantiated->requires_evaluation();

            // Do some setup for the features that do not work.
            $this->security = new stack_cas_security();
            $this->tas = [];
            $this->session = new stack_cas_session2([]);
        }
    }

    /**
     * adapt_inputs() method in Moodle
     * Give all the input elements a chance to configure themselves given the
     * teacher's model answers.
     * @throws stack_exception
     */
    protected function adaptInputs()
    {
        foreach ($this->inputs as $name => $input) {
            // TODO: again should we give the whole thing to the input.
            $teacheranswer = '';
            if ($this->tas[$name]->is_correctly_evaluated()) {
                $teacheranswer = $this->tas[$name]->get_value();
            }
            $input->adapt_to_model_answer($teacheranswer);
            if ($this->getCached('contextvariables-qv') !== null) {
                $input->add_contextsession(new stack_secure_loader((string)$this->getCached('contextvariables-qv'), '/qv'));
            }
        }
    }

    /**
     * get_hint_castext(question_hint $hint) from Moodle
     * Get the castext for a hint, instantiated within the question's session.
     * @param string $hint the hint.
     * @throws stack_exception
     */
    public function getHintCASText(string $hint): castext2_evaluatable
    {
        // TODO: NO existe hint
        // These are not currently cached as compiled fragments, maybe they should be.
        $this->hint = "dummy hint";
        $hinttext = castext2_evaluatable::make_from_source($hint, 'hint');

        $session = null;
        if ($this->session === null) {
            $session = new stack_cas_session2([], $this->options, $this->seed);
        } else {
            $session = new stack_cas_session2($this->session->get_session(), $this->options, $this->seed);
        }
        /* Not using moodle multi lang
        if (count($this->get_cached('langs')) > 0) {
            $ml = new stack_multilang();
            $selected = $ml->pick_lang($this->get_cached('langs'));
            $session->add_statement(new stack_secure_loader('%_STACK_LANG:' .
                stack_utils::php_string_to_maxima_string($selected), 'language setting'), false);
        }*/
        $session->add_statement($hinttext);
        $session->instantiate();

        if ($hinttext->get_errors()) {
            $this->runtime_errors[$hinttext->get_errors()] = true;
        }

        return $hinttext;
    }

    /**
     * Get the cattext for the general feedback, instantiated within the question's session.
     * @return castext2_evaluatable the castext.
     * @throws stack_exception
     */
    public function getGeneralFeedbackCasText(): ?castext2_evaluatable
    {
        // Could be that this is instantiated already.
        if ($this->general_feedback_instantiated !== null) {
            return $this->general_feedback_instantiated;
        }
        // We can have a failed question.
        if ($this->getCached('castext-gf') === null) {
            $ct = castext2_evaluatable::make_from_compiled('"Broken question."', '/gf',
                new castext2_static_replacer([])); // This mainly for the bulk-test script.
            $ct->requires_evaluation(); // Makes it as if it were evaluated.
            return $ct;
        }

        $this->general_feedback_instantiated = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-gf'),
            '/gf', new castext2_static_replacer($this->getCached('static-castext-strings')));
        // Might not require any evaluation anyway.
        if (!$this->general_feedback_instantiated->requires_evaluation()) {
            return $this->general_feedback_instantiated;
        }

        // Init a session with question-variables and the related details.
        $session = new stack_cas_session2([], $this->options, $this->seed);
        if ($this->getCached('preamble-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('preamble-qv'), 'preamble'));
        }
        if ($this->getCached('contextvariables-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('contextvariables-qv'), '/qv'));
        }
        if ($this->getCached('statement-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('statement-qv'), '/qv'));
        }

        // Then add the general-feedback code.
        $session->add_statement($this->general_feedback_instantiated);
        $session->instantiate();

        if ($this->general_feedback_instantiated->get_errors()) {
            $this->runtime_errors[$this->general_feedback_instantiated->get_errors()] = true;
        }

        return $this->general_feedback_instantiated;
    }

    /**
     * Get the castext for the question description, instantiated within the question's session.
     * @throws stack_exception
     */
    public function getQuestionDescriptionCasText(): ?castext2_evaluatable
    {
        // Could be that this is instantiated already.
        if ($this->question_description_instantiated !== null) {
            return $this->question_description_instantiated;
        }
        // We can have a failed question.
        if ($this->getCached('castext-gf') === null) {
            $ct = castext2_evaluatable::make_from_compiled('"Broken question."', '/gf',
                new castext2_static_replacer([])); // This mainly for the bulk-test script.
            $ct->requires_evaluation(); // Makes it as if it were evaluated.
            return $ct;
        }

        $this->question_description_instantiated = castext2_evaluatable::make_from_compiled((string)$this->getCached('castext-qd'),
            '/gf', new castext2_static_replacer($this->getCached('static-castext-strings')));
        // Might not require any evaluation anyway.
        if (!$this->question_description_instantiated->requires_evaluation()) {
            return $this->question_description_instantiated;
        }

        // Init a session with question-variables and the related details.
        $session = new stack_cas_session2([], $this->options, $this->seed);
        if ($this->getCached('preamble-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('preamble-qv'), 'preamble'));
        }
        if ($this->getCached('contextvariables-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('contextvariables-qv'), '/qv'));
        }
        if ($this->getCached('statement-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('statement-qv'), '/qv'));
        }

        // Then add the description code.
        $session->add_statement($this->question_description_instantiated);
        $session->instantiate();

        if ($this->question_description_instantiated->get_errors()) {
            $this->runtime_errors[$this->question_description_instantiated->get_errors()] = true;
        }

        return $this->question_description_instantiated;
    }

    /**
     * format_correct_response($qa) in Moodle
     * We need to make sure the inputs are displayed in the order in which they
     * occur in the question text. This is not necessarily the order in which they
     * are listed in the array $this->inputs.
     */
    public function formatCorrectResponse(): string
    {
        $feedback = '';
        $inputs = stack_utils::extract_placeholders($this->question_text_instantiated->get_rendered(), 'input');
        foreach ($inputs as $name) {
            $input = $this->inputs[$name];
            $feedback .= html_writer::tag('p', $input->get_teacher_answer_display($this->tas[$name]->get_dispvalue(),
                $this->tas[$name]->get_latex()));
        }
        return stack_ouput_castext($feedback);
    }

    /**
     * Used in testing
     * @return array
     */
    public function getExpectedData(): array
    {
        $expected = array();
        foreach ($this->inputs as $input) {
            $expected += $input->get_expected_data();
        }
        return $expected;
    }

    /**
     * used in testing
     * @return string
     */
    public function getQuestionSummary(): string
    {
        if ($this->question_note_instantiated !== null &&
            '' !== $this->question_note_instantiated->get_rendered()) {
            return $this->question_note_instantiated->get_rendered();
        }
        return stack_string('questionnote_missing');
    }

    /**
     * @throws stack_exception
     */
    public function summariseResponse(array $response): string
    {
        // Provide seed information on student's version via the normal moodle quiz report.
        $bits = array('Seed: ' . $this->seed);
        foreach ($this->inputs as $name => $input) {
            $state = $this->getInputState($name, $response);
            if (stack_input::BLANK != $state->status) {
                $bits[] = $input->summarise_response($name, $state, $response);
            }
        }
        // Add in the answer note for this response.
        foreach ($this->prts as $name => $prt) {
            $state = $this->getPrtResult($name, $response, false);
            $note = implode(' | ', array_map('trim', $state->get_answernotes()));
            $score = '';
            if (trim($note) == '') {
                $note = '!';
            } else {
                $score = "# = " . $state->get_score();
                if ($prt->is_formative()) {
                    $score .= ' [formative]';
                }
                $score .= " | ";
            }
            if ($state->get_errors()) {
                $score = '[RUNTIME_ERROR] ' . $score . implode("|", $state->get_errors());
            }
            if ($state->get_fverrors()) {
                $score = '[RUNTIME_FV_ERROR] ' . $score . implode("|", $state->get_fverrors()) . ' | ';
            }
            $bits[] = $name . ": " . $score . $note;
        }
        return implode('; ', $bits);
    }

    /**
     * Used in reporting
     * @throws stack_exception
     */
    public function summariseResponseData(array $response): array
    {
        $bits = array();
        foreach ($this->inputs as $name => $input) {
            $state = $this->getInputState($name, $response);
            $bits[$name] = $state->status;
        }
        return $bits;
    }

    /**
     * get_correct_response() in Moodle
     * @return array|string
     */
    public function getCorrectResponse()
    {
        $teacher_answer = array();
        if ($this->runtime_errors || $this->getCached('units') === null) {
            return [];
        }
        foreach ($this->inputs as $name => $input) {
            $teacher_answer = array_merge($teacher_answer,
                $input->get_correct_response($this->tas[$name]->get_dispvalue()));
        }
        return $teacher_answer;
    }

    /**
     * @param array $prevresponse
     * @param array $newresponse
     * @return bool
     */
    public function isSameResponse(array $prevresponse, array $newresponse): bool
    {
        foreach ($this->getExpectedData() as $name => $notused) {
            if (!assStackQuestionUtils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, $name)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $index
     * @param array $prevresponse
     * @param array $newresponse
     * @return bool
     * @throws stack_exception
     */
    public function isSameResponseForPart($index, array $prevresponse, array $newresponse): bool
    {
        $previnput = $this->getPrtInput($index, $prevresponse, true);
        $newinput = $this->getPrtInput($index, $newresponse, true);

        return $this->isSamePRTInput($index, $previnput, $newinput);
    }

    /**
     * get_input_state($name, $response, $rawinput=false) in Moodle
     * Get the results of validating one of the input elements.
     * @param string $name the name of one of the input elements.
     * @param array $response the response, in Maxima format.
     * @param bool $raw_input the response in raw form. Needs converting to Maxima format by the input.
     * @return stack_input_state|string the result of calling validate_student_response() on the input.
     * @throws stack_exception
     */
    public function getInputState(string $name, array $response, bool $raw_input = false)
    {
        $this->validateCache($response, null);

        if (array_key_exists($name, $this->getInputStates())) {
            return $this->getInputStates($name);
        }

        /* Not using moodle multi lang
        $lang = null;
        if ($this->get_cached('langs') !== null && count($this->get_cached('langs')) > 0) {
            $ml = new stack_multilang();
            $lang = $ml->pick_lang($this->get_cached('langs'));
        }*/

        // TODO: we should probably give the whole ast_container to the input.
        // Direct access to LaTeX and the AST might be handy.
        $teacheranswer = '';
        if (array_key_exists($name, $this->tas)) {
            if ($this->tas[$name]->is_correctly_evaluated()) {
                $teacheranswer = $this->tas[$name]->get_value();
            }
        }
        if (array_key_exists($name, $this->inputs)) {
            $qv = [];
            $qv['preamble-qv'] = $this->getCached('preamble-qv');
            $qv['contextvariables-qv'] = $this->getCached('contextvariables-qv');
            $qv['statement-qv'] = $this->getCached('statement-qv');

            $this->input_states[$name] = $this->inputs[$name]->validate_student_response(
                $response, $this->options, $teacheranswer, $this->security, $raw_input,
                $this->cas_text_processor, $qv, null);
            return $this->input_states[$name];
        }
        return '';
    }

    /**
     * is_any_input_blank(array $response) in Moodle
     * @param array $response the current response being processed.
     * @return boolean whether any of the inputs are blank.
     * @throws stack_exception
     */
    public function isAnyInputBlank(array $response): bool
    {
        foreach ($this->inputs as $name => $input) {
            if (stack_input::BLANK == $this->getInputState($name, $response)->status) {
                return true;
            }
        }
        return false;
    }

    /**
     * is_any_part_invalid(array $response) in Moodle
     * @param array $response
     * @return bool
     * @throws stack_exception
     */
    public function isAnyPartInvalid(array $response): bool
    {
        // Invalid if any input is invalid, ...
        foreach ($this->inputs as $name => $input) {
            if (stack_input::INVALID == $this->getInputState($name, $response)->status) {
                return true;
            }
        }

        // ... or any PRT gives an error.
        foreach ($this->prts as $name => $prt) {
            $result = $this->getPrtResult($name, $response, false);
            //TODO PRT result get error
            if ($result->get_errors()) {
                return true;
            }
        }

        return false;
    }


    /**
     * get_prt_result($index, $response, $acceptvalid) in Moodle
     * @throws stack_exception
     *
    public function isCompleteResponse(array $response): bool
    {

        // If all PRTs are gradable, then the question is complete. Optional inputs may be blank.
        foreach ($this->prts as $prt) {
            // Formative PRTs do not contribute to complete responses.
            if (!$prt->is_formative() && !$this->canExecutePrt($prt, $response, false)) {
                return false;
            }
        }

        // If there are no PRTs, then check that all inputs are complete.
        if (!$this->prts) {
            foreach ($this->inputs as $name => $notused) {
                if (stack_input::SCORE != $this->getInputState($name, $response)->status) {
                    return false;
                }
            }
        }

        return true;
    }*/

    /**
     * @param array $response
     * @return bool

    public function isGradableResponse(array $response): bool
    {
        // Manually graded answers are always gradable.
        if (!empty($this->inputs)) {
            foreach ($this->inputs as $input) {
                if ($input->get_extra_option('manualgraded')) {
                    return true;
                }
            }
        }
        // If any PRT is gradable, then we can grade the question.
        $no_prts = true;
        foreach ($this->prts as $index => $prt) {
            $no_prts = false;
            // Whether formative PRTs can be executed is not relevant to gradability.
            if (!$prt->is_formative() && $this->canExecutePrt($prt, $response, true)) {
                return true;
            }
        }
        // In the case of no PRTs,  questions are in state "is_gradable" if we have
        // at least one input in the "score" or "valid" state.
        if ($no_prts) {
            foreach ($this->input_states as $key => $input_state) {
                if ($input_state->status == 'score' || $input_state->status == 'valid') {
                    return true;
                }
            }
        }
        // Otherwise we are not "is_gradable".
        return false;
    }*/

    /**
     * get_validation_error(array $response)
     * @param array $response
     * @return string
     * @throws stack_exception
     */
    public function getValidationError(array $response): string
    {
        if ($this->isAnyPartInvalid($response)) {
            // There will already be a more specific validation error displayed.
            //TODO SUR: Add validation error
            return '';
        } else if ($this->isAnyInputBlank($response)) {
            return stack_string('pleaseananswerallparts');
        } else {
            return stack_string('pleasecheckyourinputs');
        }
    }

    /**
     * grade_response(array $response) in Moodle
     * for Manual scoring
     */
    public function gradeResponse(array $response)
    {
        $fraction = 0;

        // If we have one or more notes input which needs manual grading, then mark it as needs grading.
        // SUR Futura feature de correccion manual
        /*if (!empty($this->inputs)) {
            foreach ($this->inputs as $input) {
                if ($input->get_extra_option('manualgraded')) {
                    return question_state::$needsgrading;
                }
            }
        }*/
        foreach ($this->prts as $name => $prt) {
            if (!$prt->is_formative()) {
                $results = $this->getPrtResult($name, $response, true);
                //TODO add get_fraction
                $fraction += $results->get_fraction();
            }
        }
        return array($fraction, assStackQuestionUtils::graded_state_for_fraction($fraction));
    }

    /**
     * @param $current_prt_name
     * @param $last_input
     * @param $prt_input
     * @return bool
     */
    public function isSamePRTInput($index, $prtinput1, $prtinput2): bool
    {
        foreach ($this->getCached('required')[$this->prts[$index]->get_name()] as $name => $ignore) {
            if (!assStackQuestionUtils::arrays_same_at_key_missing_is_blank($prtinput1, $prtinput2, $name)) {
                return false;
            }
        }
        return true;
    }

    public function getPartsAndWeights()
    {
        $weights = array();
        foreach ($this->prts as $index => $prt) {
            if (!$prt->is_formative()) {
                $weights[$index] = $prt->get_value();
            }
        }
        return $weights;
    }

    public function gradePartsThatCanBeGraded(array $response, array $lastgradedresponses, $finalsubmit)
    {
        $partresults = array();

        // At the moment, this method is not written as efficiently as it might
        // be in terms of caching. For now I will be happy it computes the right score.
        // Once we are confident enough, we can try to optimise.

        foreach ($this->prts as $index => $prt) {
            // Some optimisation now hidden behind this, it will eval all PRTs
            // of the question for this input.
            $results = $this->getPrtResult($index, $response, $finalsubmit);
            if (!$results->is_evaluated()) {
                continue;
            }

            if (!$results->get_valid()) {
                $partresults[$index] = [$index, null, null, true];
                continue;
            }

            if (array_key_exists($index, $lastgradedresponses)) {
                $lastresponse = $lastgradedresponses[$index];
            } else {
                $lastresponse = array();
            }

            $lastinput = $this->getPrtInput($index, $lastresponse, $finalsubmit);
            $prtinput = $this->getPrtInput($index, $response, $finalsubmit);

            if ($this->isSamePRTInput($index, $lastinput, $prtinput)) {
                continue;
            }

            $partresults[$index] = [
                $index, $results->get_score(), $results->get_penalty()];
        }

        return $partresults;
    }


    /**
     * @throws stack_exception
     */
    public function computeFinalGrade($responses, $totaltries)
    {
        // This method is used by the interactive behaviour to compute the final
        // grade after all the tries are done.

        // At the moment, this method is not written as efficiently as it might
        // be in terms of caching. For now I am happy it computes the right score.
        // Once we are confident enough, we could try switching the nesting
        // of the loops to increase efficiency.

        // TODO: switch the nesting, now that the eval is by response and not by PRT.
        // Current CAS-cache helps but it is wasted cycles to go to it so many times.
        $fraction = 0;
        foreach ($this->prts as $index => $prt) {
            if ($prt->is_formative()) {
                continue;
            }

            $accumulatedpenalty = 0;
            $lastinput = array();
            $penaltytoapply = null;
            $results = new stdClass();
            $results->fraction = 0;

            $frac = 0;
            foreach ($responses as $response) {
                $prtinput = $this->getPrtInput($index, $response, true);

                if (!$this->isSamePRTInput($index, $lastinput, $prtinput)) {
                    $penaltytoapply = $accumulatedpenalty;
                    $lastinput = $prtinput;
                }

                if ($this->canExecutePrt($this->prts[$index], $response, true)) {
                    $results = $this->getPrtResult($index, $response, true);

                    $accumulatedpenalty += $results->get_fractionalpenalty();
                    $frac = $results->get_fraction();
                }
            }

            $fraction += max($frac - $penaltytoapply, 0);
        }

        return $fraction;
    }

    /**
     * has_necessary_prt_inputs(stack_potentialresponse_tree_lite $prt, $response, $acceptvalid)
     * Do we have all the necessary inputs to execute one of the potential response trees?
     * @param stack_potentialresponse_tree_lite $prt the tree in question.
     * @param array $response the response.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return bool can this PRT be executed for that response.
     * @throws stack_exception
     */
    public function hasNecessaryPrtInputs(stack_potentialresponse_tree_lite $prt, array $response, bool $accept_valid): bool
    {
        // Some kind of time-time error in the question, so bail here.
        if ($this->getCached('required') === null) {
            return false;
        }

        foreach ($this->getCached('required')[$prt->get_name()] as $name => $ignore) {
            $status = $this->getInputState($name, $response)->status;
            if (!(stack_input::SCORE == $status || ($accept_valid && stack_input::VALID == $status))) {
                return false;
            }
        }

        return true;
    }

    /**
     * can_execute_prt(stack_potentialresponse_tree_lite $prt, $response, $acceptvalid) in Moodle
     * Do we have all the necessary inputs to execute one of the potential response trees?
     * @param stack_potentialresponse_tree_lite $prt the tree in question.
     * @param array $response the response.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return bool can this PRT be executed for that response.
     * @throws stack_exception
     */
    protected function canExecutePrt(stack_potentialresponse_tree_lite $prt, array $response, bool $accept_valid): bool
    {
        // The only way to find out is to actually try evaluating it. This calls
        // has_necessary_prt_inputs, and then does the computation, which ensures
        // there are no CAS errors.

        $result = $this->getPrtResult($prt->get_name(), $response, $accept_valid);
        return $result->is_evaluated() && !$result->get_errors();
    }

    /**
     * get_prt_input($index, $response, $acceptvalid) in Moodle
     * Extract the input for a given PRT from a full response.
     * @param string $index the name of the PRT.
     * @param array $response the full response data.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return array
     * @throws stack_exception
     */
    protected function getPrtInput(string $index, array $response, bool $accept_valid): array
    {
        if (!array_key_exists($index, $this->prts)) {
            $msg = '"' . $this->getTitle() . '" (' . $this->getId() . ') seed = ' .
                $this->getSeed() . ' and STACK version = ' . $this->stack_version;
            throw new stack_exception ("get_prt_input called for PRT " . $index . " which does not exist in question " . $msg);
        }
        $prt = $this->prts[$index];
        $prt_input = array();
        foreach ($this->getCached('required')[$prt->get_name()] as $name => $ignore) {
            $state = $this->getInputState($name, $response);
            if (stack_input::SCORE == $state->status || ($accept_valid && stack_input::VALID == $state->status)) {
                $val = $state->contentsmodified;
                if ($state->simp === true) {
                    $val = 'ev(' . $val . ',simp)';
                }
                $prt_input[$name] = $val;
            }
        }

        return $prt_input;
    }

    /**
     * get_prt_result($index, $response, $acceptvalid) in Moodle
     * Evaluate a PRT for a particular response.
     * @param string $prt_name the name of the PRT to evaluate.
     * @param array $response the response to process.
     * @param bool $accept_valid if this is true, then we will grade things even if the corresponding inputs are only VALID, and not SCORE.
     * @return prt_evaluatable
     * @throws stack_exception
     */
    public function getPrtResult(string $prt_name, array $response, bool $accept_valid): prt_evaluatable
    {
        $this->validateCache($response, $accept_valid);

        if (array_key_exists($prt_name, $this->prt_results)) {
            return $this->prt_results[$prt_name];
        }

        // We can end up with a null prt at this point if we have question tests for a deleted PRT.
        // Alternatively we have a question that could not be compiled.
        if (!array_key_exists($prt_name, $this->prts) || $this->getCached('units') === null) {
            // Bail here with an empty state to avoid a later exception which prevents question test editing.
            return new prt_evaluatable('prt_' . $prt_name . '(???)', 1, new castext2_static_replacer([]), array());
        }

        // If we do not have inputs for this then no need to continue.
        if (!$this->hasNecessaryPrtInputs($this->prts[$prt_name], $response, $accept_valid)) {
            $this->prt_results[$prt_name] = new prt_evaluatable($this->getCached('prt-signature')[$prt_name],
                $this->prts[$prt_name]->get_value(),
                new castext2_static_replacer($this->getCached('static-castext-strings')),
                $this->getCached('prt-trace')[$prt_name]);
            return $this->prt_results[$prt_name];
        }

        // First figure out which PRTs can be called.
        $prts = [];
        $inputs = [];
        foreach ($this->prts as $name => $prt) {
            if ($this->hasNecessaryPrtInputs($prt, $response, $accept_valid)) {
                $prts[$name] = $prt;
                $inputs += $this->getPrtInput($name, $response, $accept_valid);
            }
        }

        // So now we build a session to evaluate all the PRTs.
        $session = new stack_cas_session2([], $this->options, $this->seed);

        /* Not using moodle multi lang
        if (count($this->getCached('langs')) > 0) {
            $ml = new stack_multilang();
            $selected = $ml->pick_lang($this->getCached('langs'));
            $session->add_statement(new stack_secure_loader('%_STACK_LANG:' .
                stack_utils::php_string_to_maxima_string($selected), 'language setting'), false);
        }*/

        // Construct the security object. But first units declaration into the session.
        $units = (boolean)$this->getCached('units');

        // If we have units we might as well include the units declaration in the session.
        // To simplify authors work and remove the need to call that long function.
        // TODO: Maybe add this to the preable to save lines, but for now documented here.
        if ($units) {
            $session->add_statement(new stack_secure_loader('stack_unit_si_declare(true)',
                'automatic unit declaration'), false);
        }

        if ($this->getCached('preamble-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('preamble-qv'), 'preamble'));
        }
        // Add preamble from PRTs as well.
        foreach ($this->getCached('prt-preamble') as $name => $stmt) {
            if (isset($prts[$name])) {
                $session->add_statement(new stack_secure_loader($stmt, 'preamble PRT: ' . $name));
            }
        }

        // Context variables should be first.
        if ($this->getCached('contextvariables-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('contextvariables-qv'), '/qv'));
        }
        // Add contextvars from PRTs as well.
        foreach ($this->getCached('prt-contextvariables') as $name => $stmt) {
            if (isset($prts[$name])) {
                $session->add_statement(new stack_secure_loader($stmt, 'contextvariables PRT: ' . $name));
            }
        }

        if ($this->getCached('statement-qv') !== null) {
            $session->add_statement(new stack_secure_loader((string)$this->getCached('statement-qv'), '/qv'));
        }

        // Then the definitions of the PRT-functions. Note not just statements for a reason.
        foreach ($this->getCached('prt-definition') as $name => $stmt) {
            if (isset($prts[$name])) {
                $session->add_statement(new stack_secure_loader($stmt, 'definition PRT: ' . $name));
            }
        }

        // Suppress simplification of raw inputs.
        $session->add_statement(new stack_secure_loader('simp:false', 'input-simplification'));

        // Now push in the input values and the new _INPUT_STRING.
        // Note these have been validated in the input system.
        $is = '_INPUT_STRING:["stack_map"';
        foreach ($inputs as $key => $value) {
            $session->add_statement(new stack_secure_loader($key . ':' . $value, 'i/' .
                array_search($key, array_keys($this->inputs)) . '/s'));
            $is .= ',[' . stack_utils::php_string_to_maxima_string($key) . ',';
            if (strpos($value, 'ev(') === 0) { // Unpack the value if we have simp...
                $is .= stack_utils::php_string_to_maxima_string(mb_substr($value, 3, -6)) . ']';
            } else {
                $is .= stack_utils::php_string_to_maxima_string($value) . ']';
            }
        }
        $is .= ']';
        $session->add_statement(new stack_secure_loader($is, 'input-strings'));

        // Generate, cache and instantiate the results.
        foreach ($this->prts as $name => $prt) {
            // Put the input string map in the trace.
            $trace = array_merge(array($is . '$', '/* ------------------- */'), $this->getCached('prt-trace')[$name]);
            $p = new prt_evaluatable($this->getCached('prt-signature')[$name],
                $prt->get_value(), new castext2_static_replacer($this->getCached('static-castext-strings')),
                $trace);
            if (isset($prts[$name])) {
                // Always make sure it gets called with simp:false.
                $session->add_statement(new stack_secure_loader('simp:false', 'prt-simplification'));
                $session->add_statement($p);
            }
            $this->prt_results[$name] = $p;
        }
        $session->instantiate();
        return $this->prt_results[$prt_name];
    }

    /**
     * For a possibly nested array, replace all the values with $newvalue.
     * @param string|array $arrayorscalar input array/value.
     * @param mixed $newvalue the new value to set.
     * @return string|array array.
     */
    protected function setValueInNestedArrays($arrayorscalar, $newvalue)
    {
        if (!is_array($arrayorscalar)) {
            return $newvalue;
        }

        $newarray = array();
        foreach ($arrayorscalar as $key => $value) {
            $newarray[$key] = $this->setValueInNestedArrays($value, $newvalue);
        }
        return $newarray;
    }


    /**
     * Pollute the question's input state and PRT result caches so that each
     * input appears to contain the name of the input, and each PRT feedback
     * area displays "Feedback from PRT {name}". Naturally, this method should
     * only be used for special purposes, namely the tidyquestion.php script.
     * @throws stack_exception
     */
    public function setupFakeFeedbackAndInputValidation()
    {
        // Set the cached input stats as if the user types the input name into each box.
        foreach ($this->input_states as $name => $inputstate) {
            $this->input_states[$name] = new stack_input_state(
                $inputstate->status, $this->setValueInNestedArrays($inputstate->contents, $name),
                $inputstate->contentsmodified, $inputstate->contentsdisplayed, $inputstate->errors, $inputstate->note, '');
        }

        // Set the cached prt results as if the feedback for each PRT was
        // "Feedback from PRT {name}".
        foreach ($this->prt_results as $name => $prtresult) {
            $prtresult->override_feedback(stack_string('feedbackfromprtx', $name));
        }
    }

    /**
     * has_random_variants in Moodle
     * @return bool whether this question uses randomisation.
     */
    public function hasRandomVariants(): bool
    {
        return assStackQuestionUtils::_hasRandomVariables($this->question_variables);
    }

    /**
     * get_num_variants() in Moodle
     * @return int
     */
    public function getNumVariants(): int
    {
        if (!$this->hasRandomVariants()) {
            // This question does not use randomisation. Only declare one variant.
            return 1;
        }

        if (!empty($this->deployed_seeds)) {
            // Fixed number of deployed variants, declare that.
            return count($this->deployed_seeds);
        }

        // Random question without fixed variants.
        return 1000000;
    }

    /**
     * @return string|null
     */
    public function getVariantsSelectionSeed(): ?string
    {
        if (!empty($this->variants_selection_seed)) {
            return $this->variants_selection_seed;
        } else {
            return (string)time();
        }
    }

    /* check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) not required as it is only Moodle relevant */
    //TODO FEATURE ROLES

    /* get_context() not required as it is only Moodle relevant */

    /* has_question_capability($type) not required as it is only Moodle relevant */

    /* user_can_view() not required as it is only Moodle relevant */

    /* user_can_edit() not required as it is only Moodle relevant */

    /**
     * QUESTION TEST EDIT
     * QUESTION TEST RUN
     * Get the values of all variables which have a key.  So, function definitions
     * and assignments are ignored by this method.  Used to display the values of
     * variables used in a question variant.  Beware that some functions have side
     * effects in Maxima, e.g. orderless.  If you use these values you may not get
     * the same results as if you recreate the whole session from $this->questionvariables.
     *
     * @throws stack_exception
     */
    public function getQuestionSessionKeyvalRepresentation(): string
    {
        // After the cached compilation update the session no longer returns these.
        // So we will build another session just for this.
        // First we replace the compiled statements with the raw keyval statements.
        $tmp = $this->session->get_session();
        $tmp = array_filter($tmp, function ($v) {
            return method_exists($v, 'is_correctly_evaluated');
        });
        $kv = new stack_cas_keyval($this->question_variables, $this->options, $this->seed);
        $kv->get_valid();
        $session = $kv->get_session();
        $session->add_statements($tmp);
        $session->get_valid();
        if ($session->get_valid()) {
            $session->instantiate();
        }

        // We always want the values when this method is called.
        return $session->get_keyval_representation(true);
    }

    /**
     * add_question_vars_to_session(stack_cas_session2 $session) in Moodle
     * Add all the question variables to a give CAS session. This can be used to
     * initialise that session, so expressions can be evaluated in the context of
     * the question variables.
     * @param stack_cas_session2 $session the CAS session to add the question variables to.
     */
    public function addQuestionVarsToSession(stack_cas_session2 $session)
    {
        // Question vars will always get added to the beginning of whatever session you give.
        $this->session->prepend_to_session($session);
    }

    /**
     * get_ta_for_input(string $vname) in Moodle
     * Enable the renderer to access the teacher's answer in the session.
     * TODO: should we give the whole thing?
     * @param string $vname variable name.
     * @return string|bool|stack_ast_container[]|stack_ast_container
     * @throws stack_exception
     */
    public function getTeacherAnswerForInput(string $vname): string
    {
        if (isset($this->tas[$vname]) && $this->tas[$vname]->is_correctly_evaluated()) {
            return $this->tas[$vname]->get_value();
        }
        return '';
    }

    /* classify_response(array $response) not required as it is only Moodle relevant */
    //TODO FEATURE CLASSIFY RESPONSE

    /**
     * deploy_variant($seed) in Moodle
     * Deploy a variant of this question.
     * @param int $seed the seed to deploy.
     */
    public function deployVariant(int $seed)
    {
        //TODO COPY
    }

    /**
     * undeploy_variant($questionid, $seed) in Moodle
     * Deploy a variant of this question.
     * @param int $question_id
     * @param int $seed
     */
    public function undeployVariant(int $question_id, int $seed)
    {
        //TODO COPY
    }

    /**
     * This function is called by the bulk testing script on upgrade.
     * This checks if questions use features which have changed.
     */
    public function validateAgainstStackVersion($context): string
    {
        $errors = array();
        $qfields = array('questiontext', 'questionvariables', 'questionnote', 'questiondescription',
            'specificfeedback', 'generalfeedback');

        $stackversion = (int)$this->stack_version;

        // Things no longer allowed in questions.
        $patterns = array(
            array('pat' => 'addrow', 'ver' => 2018060601, 'alt' => 'rowadd'),
            array('pat' => 'texdecorate', 'ver' => 2018080600),
            array('pat' => 'logbase', 'ver' => 2019031300, 'alt' => 'lg')
        );
        foreach ($patterns as $checkpat) {
            if ($stackversion < $checkpat['ver']) {
                foreach ($qfields as $field) {
                    if (strstr($this->$field ?? '', $checkpat['pat'])) {
                        $a = array('pat' => $checkpat['pat'], 'ver' => $checkpat['ver'], 'qfield' => stack_string($field));
                        $err = stack_string('stackversionerror', $a);
                        if (array_key_exists('alt', $checkpat)) {
                            $err .= ' ' . stack_string('stackversionerroralt', $checkpat['alt']);
                        }
                        $errors[] = $err;
                    }
                }
                // Look inside the PRT feedback variables.  Should probably check the feedback as well.
                foreach ($this->prts as $name => $prt) {
                    $kv = $prt->get_feedbackvariables_keyvals();
                    if (strstr($kv, $checkpat['pat'])) {
                        $a = array('pat' => $checkpat['pat'], 'ver' => $checkpat['ver'],
                            'qfield' => stack_string('feedbackvariables') . ' (' . $name . ')');
                        $err = stack_string('stackversionerror', $a);
                        if (array_key_exists('alt', $checkpat)) {
                            $err .= ' ' . stack_string('stackversionerroralt', $checkpat['alt']);
                        }
                        $errors[] = $err;
                    }
                }
            }
        }

        // Mul is no longer supported.
        // We don't need to include a date check here because it is not a change in behaviour.
        foreach ($this->inputs as $inputname => $input) {

            if (!preg_match('/^([a-zA-Z]+|[a-zA-Z]+[0-9a-zA-Z_]*[0-9a-zA-Z]+)$/', $inputname)) {
                $errors[] = stack_string('inputnameform', $inputname);
            }

            $options = $input->get_parameter('options');
            if (trim($options ?? '') !== '') {
                $options = explode(',', $options);
                foreach ($options as $opt) {
                    $opt = strtolower(trim($opt));
                    if ($opt === 'mul') {
                        $errors[] = stack_string('stackversionmulerror');
                    }
                }
            }
        }

        // Look for RexExp answer test which is no longer supported.
        foreach ($this->prts as $name => $prt) {
            if (array_key_exists('RegExp', $prt->get_answertests())) {
                $errors[] = stack_string('stackversionregexp');
            }
        }

        // Check files use match the files in the question.
        //TODO SUR por ahora comentado
        /*
        $fs = get_file_storage();
        $pat = '/@@PLUGINFILE@@([^@"])*[\'"]/';
        $fields = array('questiontext', 'specificfeedback', 'generalfeedback', 'questiondescription');
        foreach ($fields as $field) {
            $text = $this->$field;
            $filesexpected = preg_match($pat, $text ?? '');
            $filesfound    = $fs->get_area_files($context->id, 'question', $field, $this->id);
            if (!$filesexpected && $filesfound != array()) {
                $errors[] = stack_string('stackfileuseerror', stack_string($field));
            }
        }*/

        // Add in any warnings.
        $errors = array_merge($errors, $this->validateWarnings(true));

        return implode(' ', $errors);
    }

    /*
     * Unfortunately, "errors" stop a question being saved.  So, we have a parallel warning mechanism.
     * Warnings need to be addressed but should not stop a question being saved.
     */
    /**
     * @throws stack_exception
     */
    public function validateWarnings($errors = false): array
    {

        $warnings = array();

        // 1. Answer tests which require raw inputs actually have SAns a calculated value.
        foreach ($this->prts as $prt) {
            foreach ($prt->get_raw_sans_used() as $key => $sans) {
                if (!array_key_exists(trim($sans), $this->inputs)) {
                    $warnings[] = stack_string_error('AT_raw_sans_needed', array('prt' => $key));
                }
            }
            foreach ($prt->get_raw_arguments_used() as $name => $ans) {
                $tvalue = trim($ans);
                $tvalue = substr($tvalue, strlen($tvalue) - 1);
                if ($tvalue === ';') {
                    $warnings[] = stack_string('nosemicolon') . ':' . $name;
                }
            }
        }

        // 2. Check alt-text exists.
        // Reminder: previous approach in Oct 2021 tried to use libxml_use_internal_errors, but this was a dead end.
        $tocheck = array();
        $text = '';
        if ($this->question_text_instantiated !== null) {
            $text = trim($this->question_text_instantiated->get_rendered());
        }
        if ($text !== '') {
            $tocheck[stack_string('questiontext')] = $text;
        }
        $ct = $this->getGeneralFeedbackCasText();
        $text = trim($ct->get_rendered($this->cas_text_processor));
        if ($text !== '') {
            $tocheck[stack_string('generalfeedback')] = $text;
        }
        // This is a compromise.  We concatinate all nodes and we don't instantiate this!
        foreach ($this->prts as $prt) {
            $text = trim($prt->get_feedback_test());
            if ($text !== '') {
                $tocheck[$prt->get_name()] = $text;
            }
        }

        foreach ($tocheck as $field => $text) {
            // Replace unprotected & symbols, which happens a lot inside LaTeX equations.
            $text = preg_replace("/&(?!\S+;)/", "&amp;", $text);

            $missingalt = stack_utils::count_missing_alttext($text);
            if ($missingalt > 0) {
                $warnings[] = stack_string_error('alttextmissing', array('field' => $field, 'num' => $missingalt));
            }
        }

        // 3. Check for todo blocks.
        $tocheck = array();
        $fields = array('questiontext', 'specificfeedback', 'generalfeedback', 'questiondescription');
        foreach ($fields as $field) {
            $tocheck[stack_string($field)] = $this->$field;
        }
        foreach ($this->prts as $prt) {
            $text = trim($prt->get_feedback_test());
            if ($text !== '') {
                $tocheck[$prt->get_name()] = $text;
            }
        }
        $pat = '/\[\[todo/';
        foreach ($tocheck as $field => $text) {
            if (preg_match($pat, $text ?? '')) {
                $warnings[] = stack_string_error('todowarning', array('field' => $field));
            }
        }

        // 4. Language warning checks.
        // Put language warning checks last (see guard clause below).
        // Check multi-language versions all have the same languages.
        /* Not using moodle multi lang
        $ml = new stack_multilang();
        $qlangs = $ml->languages_used($this->questiontext);
        asort($qlangs);
        if ($qlangs != array() && !$errors) {
            $warnings['questiontext'] = stack_string('questiontextlanguages', implode(', ', $qlangs));
        }
        */
        return $warnings;

        /*
        // Language tags don't exist.
        if ($qlangs == array()) {
            return $warnings;
        }

        $problems = false;
        $missinglang = array();
        $extralang = array();
        $fields = array('specificfeedback', 'generalfeedback');
        foreach ($fields as $field) {
            $text = $this->$field;
            // Strip out feedback tags (to help non-trivial content check)..
            foreach ($this->prts as $prt) {
                $text = str_replace('[[feedback:' . $prt->get_name() . ']]', '', $text);
            }

            if ($ml->non_trivial_content_for_check($text)) {

                $langs = $ml->languages_used($text);
                foreach ($qlangs as $expectedlang) {
                    if (!in_array($expectedlang, $langs)) {
                        $problems = true;
                        $missinglang[$expectedlang][] = stack_string($field);
                    }
                }
                foreach ($langs as $lang) {
                    if (!in_array($lang, $qlangs)) {
                        $problems = true;
                        $extralang[stack_string($field)][] = $lang;
                    }
                }

            }
        }

        foreach ($this->prts as $prt) {
            foreach ($prt->get_feedback_languages() as $nodes) {
                // The nodekey is really the answernote from one branch of the node.
                // No actually it is not in the new PRT-system, it's just 'true' or 'false'.
                foreach ($nodes as $nodekey => $langs) {
                    foreach ($qlangs as $expectedlang) {
                        if (!in_array($expectedlang, $langs)) {
                            $problems = true;
                            $missinglang[$expectedlang][] = $nodekey;
                        }
                    }
                    foreach ($langs as $lang) {
                        if (!in_array($lang, $qlangs)) {
                            $problems = true;
                            $extralang[$nodekey][] = $lang;
                        }
                    }
                }
            }
        }

        if ($problems) {
            $warnings[] = stack_string_error('languageproblemsexist');
        }
        foreach ($missinglang as $lang => $missing) {
            $warnings[] = stack_string('languageproblemsmissing',
                array('lang' => $lang, 'missing' => implode(', ', $missing)));
        }
        foreach ($extralang as $field => $langs) {
            $warnings[] = stack_string('languageproblemsextra',
                array('field' => $field, 'langs' => implode(', ', $langs)));
        }

        return $warnings;*/

    }

    /**
     * Cache management.
     * getCached(string $key) method in Moodle
     *
     * Returns named items from the cache and rebuilds it if the cache
     * has been cleared.
     * @param string $key
     * @return array|null
     */
    public function getCached(string $key)
    {
        if ($this->compiled_cache !== null && isset($this->compiled_cache['FAIL'])) {
            // This question failed compilation, no need to try again in this request.
            // Make sure the error is back in the error list.
            $this->runtime_errors[$this->compiled_cache['FAIL']] = true;
            return null;
        }

        // Do we have that particular thing in the cache?
        if ($this->compiled_cache === null || !array_key_exists($key, $this->compiled_cache)) {
            // If not do the compilation.
                $this->compiled_cache = assStackQuestion::compile($this->id,
                    $this->question_variables, $this->inputs, $this->prts,
                    $this->options, $this->getQuestion(), assStackQuestionUtils::FORMAT_HTML,
                    $this->question_note,
                    $this->general_feedback, assStackQuestionUtils::FORMAT_HTML,
                    $this->specific_feedback, assStackQuestionUtils::FORMAT_HTML,
                    $this->getComment(), assStackQuestionUtils::FORMAT_HTML,
                    $this->prt_correct, $this->prt_correct_format,
                    $this->prt_partially_correct, $this->prt_partially_correct_format,
                    $this->prt_incorrect, $this->prt_incorrect_format, $this->penalty);
        }

        // A run-time error means we don't have the $key in the cache.
        // We don't want an error here, we want to degrade gracefully.*/
        $ret = null;
        if (is_array($this->compiled_cache) && array_key_exists($key, $this->compiled_cache)) {
            $ret = $this->compiled_cache[$key];
        }

        return $ret;
    }

    /* STACK CORE METHODS END */

    /* GETTERS AND SETTERS BEGIN */

    /**
     * @return int|null
     */
    public function getSeed(): ?int
    {
        return $this->seed;
    }

    /**
     * @param int|null $seed
     */
    public function setSeed(?int $seed): void
    {
        $this->seed = $seed;
    }

    /**
     * @return array
     */
    public function getUnitTests(): array
    {
        return $this->unit_tests;
    }

    /**
     * @param array $unit_tests
     */
    public function setUnitTests(array $unit_tests): void
    {
        $this->unit_tests = $unit_tests;
    }

    /**
     * @param string $test_case
     * @param array $unit_test
     */
    public function addUnitTest(string $test_case, array $unit_test): void
    {
        $this->unit_tests["test_cases"][$test_case] = $unit_test;
    }

    /**
     * @return string
     */
    public function getNextTestCaseNumber(): string
    {
        $max = 0;

        if (!empty($this->unit_tests["test_cases"]) && is_array($this->unit_tests["test_cases"])) {
            foreach ($this->unit_tests["test_cases"] as $test_case => $unit_test) {
                if ((int)$test_case > $max) {
                    $max = (int)$test_case;
                }
            }
        } else {
            return '1';
        }

        return (string)($max + 1);
    }

    /**
     * @return stack_cas_session2
     */
    public function getSession(): stack_cas_session2
    {
        return $this->session;
    }

    /**
     * @param stack_cas_session2 $session
     */
    public function setSession(stack_cas_session2 $session): void
    {
        $this->session = $session;
    }

    /**
     * SPECIAL GETTER
     * @param null|string $name
     * @return stack_ast_container[]|stack_ast_container
     */
    public function getTas(string $name = null)
    {
        if ($name) {
            return $this->tas[$name];
        } else {
            return $this->tas;
        }
    }

    /**
     * SPECIAL SETTER
     * @param array|stack_ast_container $tas
     * @param null|string $name
     */
    public function setTas($tas, $name = null): void
    {
        if ($name) {
            $this->tas[$name] = $tas;
        } else {
            $this->tas = $tas;
        }
    }

    /**
     * @return stack_cas_security
     */
    public function getSecurity(): stack_cas_security
    {
        return $this->security;
    }

    /**
     * @param stack_cas_security $security
     */
    public function setSecurity(stack_cas_security $security): void
    {
        $this->security = $security;
    }

    /**
     * @return array|null
     */
    public function getLastResponse(): ?array
    {
        return $this->last_response;
    }

    /**
     * @param array|null $last_response
     */
    public function setLastResponse(?array $last_response): void
    {
        $this->last_response = $last_response;
    }

    /**
     * @return bool|null
     */
    public function getLastAcceptValid(): ?bool
    {
        return $this->last_accept_valid;
    }

    /**
     * @param bool|null $last_accept_valid
     */
    public function setLastAcceptValid(?bool $last_accept_valid): void
    {
        $this->last_accept_valid = $last_accept_valid;
    }

    /**
     * SPECIAL GETTER
     * @param false|string $name
     * @return stack_input_state[]|stack_input_state
     */
    public function getInputStates($name = false)
    {
        if ($name) {
            return $this->input_states[$name];
        } else {
            return $this->input_states;
        }
    }

    /**
     * SPECIAL SETTER
     * @param stack_input_state[]|stack_input_state $input_states
     * @param false|string $name
     */
    public function setInputStates($input_states, $name = false): void
    {
        if ($name) {
            $this->input_states[$name] = $input_states;
        } else {
            $this->input_states = $input_states;
        }
    }

    /**
     * SPECIAL GETTER
     * @param false|string $index
     * @return array|stack_potentialresponse_tree_state
     */
    public function getPrtResults($index = false)
    {
        if ($index) {
            return $this->prt_results[$index];
        } else {
            return $this->prt_results;
        }
    }

    /**
     * SPECIAL SETTER
     * @param array|stack_potentialresponse_tree_state $prt_results
     * @param false|string $index
     */
    public function setPrtResults($prt_results, $index = false): void
    {
        if ($index) {
            $this->prt_results[$index] = $prt_results;
        } else {
            $this->prt_results = $prt_results;
        }
    }

    /**
     * @return int
     */
    public function getHidden(): int
    {
        return $this->hidden;
    }

    /**
     * @param int $hidden
     */
    public function setHidden(int $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return float
     */
    public function getPenalty()
    {
        return $this->penalty;
    }

    /**
     * @param float $penalty
     */
    public function setPenalty(float $penalty): void
    {
        $this->penalty = $penalty;
    }

    /**
     * SPECIAL GETTER
     * @param false|string $input_name
     * @return array|string
     */
    public function getUserResponse($input_name = false)
    {
        if ($input_name) {
            return $this->user_response[$input_name];
        } else {
            return $this->user_response;
        }
    }

    /**
     * SPECIAL SETTER
     * @param array $user_response
     * @param false|string $input_name
     */
    public function setUserResponse(array $user_response, $input_name = false)
    {
        if ($input_name) {
            $this->user_response[$input_name] = $user_response[$input_name];
        } else {
            $this->user_response = $user_response;
        }
    }

    /**
     * Not get because that function exists in assQuestion with a different purpose
     * @return float|null
     */
    public function obtainReachedPoints(): ?float
    {
        return $this->reached_points;
    }

    /**
     * @param float|null $reached_points
     */
    public function setReachedPoints(?float $reached_points): void
    {
        $this->reached_points = $reached_points;
    }

    /**
     * @return bool
     */
    public function isInstantiated(): bool
    {
        return $this->instantiated;
    }

    /**
     * @param bool $instantiated
     */
    public function setInstantiated(bool $instantiated): void
    {
        $this->instantiated = $instantiated;
    }

    /**
     * @return array
     */
    public function getEvaluation(): array
    {
        return $this->evaluation;
    }

    /**
     * @param array $evaluation
     */
    public function setEvaluation(array $evaluation): void
    {
        $this->evaluation = $evaluation;
    }

    /* GETTERS AND SETTERS END */

    /* QUESTIONTYPE METHODS BEGIN */

    /* rename_input($questionid, $from, $to) not required as it is only Moodle relevant */
    //TODO FEATURE RENAME INPUT

    /* rename_prt($questionid, $from, $to) not required as it is only Moodle relevant */
    //TODO FEATURE RENAME PRT

    /* rename_prt_node($questionid, $prtname, $from, $to) not required as it is only Moodle relevant */
    //TODO FEATURE RENAME PRT NODE

    /* notify_question_edited($questionid) not required as it is only Moodle relevant */
    //TODO FEATURE NOTIFY QUESTION EDITED

    /* load_question_tests($questionid) not required as it is only Moodle relevant */
    //TODO FEATURE BULK UNIT TESTS

    /* load_question_test($questionid, $testcase) not required as it is only Moodle relevant */
    //TODO

    /* delete_question_tests($questionid) not required as it is only Moodle relevant */
    //TODO FEATURE BULK UNIT TESTS

    /* delete_question_test($questionid, $testcase) not required as it is only Moodle relevant */
    //TODO

    /**
     * Helper method for "compiling" a question, validates and finds all the things
     * that do not change unless the question changes and stores them in a dictionary.
     *
     * Note that does throw exceptions about validation details.
     *
     * Currently the cache contains the following keys:
     *  'units' for declaring the units-mode.
     *  'forbiddenkeys' for the lsit of those.
     *  'contextvariables-qv' the pre-validated question-variables which are context variables.
     *  'statement-qv' the pre-validated question-variables.
     *  'preamble-qv' the matching blockexternals.
     *  'required' the lists of inputs required by given PRTs an array by PRT-name.
     *  'castext-qt' for the question-text as compiled CASText2.
     *  'castext-qn' for the question-note as compiled CASText2.
     *  'castext-...' for the model-solution and prtpartiallycorrect etc.
     *  'castext-td-...' for downloadable generated text content.
     *  'security-context' mainly lists keys that are student inputs.
     *  'prt-*' the compiled PRT-logics in an array. Divided by usage.
     *  'langs' a list of language codes used in this question.
     *
     * In the future expect the following:
     *  'security-config' extended logic for cas-security, e.g. custom-units.
     *
     * @param int $id the identifier of this question fot use if we have pluginfiles
     * @param string $questionvariables the questionvariables
     * @param array $inputs inputs as objects, keyed by input name
     * @param array $prts PRTs as objects
     * @param stack_options $options the options in use, if they would ever matter
     * @param string $questiontext question-text
     * @param string $questiontextformat question-text format
     * @param string $questionnote question-note
     * @param string $generalfeedback general-feedback
     * @param string $generalfeedbackformat general-feedback format...
     * @param float $defaultpenalty default penalty
     * @return array a dictionary of things that might be expensive to generate.
     * @throws stack_exception
     */
    public static function compile($id, $questionvariables, $inputs, $prts, $options,
                                   $questiontext, $questiontextformat,
                                   $questionnote,
                                   $generalfeedback, $generalfeedbackformat,
                                   $specificfeedback, $specificfeedbackformat,
                                   $questiondescription, $questiondescriptionformat,
                                   $prtcorrect, $prtcorrectformat,
                                   $prtpartiallycorrect, $prtpartiallycorrectformat,
                                   $prtincorrect, $prtincorrectformat, $defaultpenalty): array
    {
        // NOTE! We do not compile during question save as that would make
        // import actions slow. We could compile during fromform-validation
        // but we really should look at refactoring that to better interleave
        // the compilation.
        //
        // As we currently compile at the first use things start slower than they could.

        // The cache will be a dictionary with many things.
        $cc = [];
        // Some details are globals built from many sources.
        $units = false;
        $forbiddenkeys = [];
        $sec = new stack_cas_security();

        // Some counter resets to ensure that the result is the same even if
        // we for some reason would compile twice in a session.
        // Happens during first preview and can lead to cache being always out
        // of sync if textdownload is in play.
        stack_cas_castext2_textdownload::$countfiles = 1;

        // Static string extrraction now for CASText2 in top level text blobs and PRTs,
        // question varaibles and in the future probably also from input2.
        $map = new castext2_static_replacer([]);

        // First handle the question variables.
        if ($questionvariables === null || trim($questionvariables) === '') {
            $cc['statement-qv'] = null;
            $cc['preamble-qv'] = null;
            $cc['contextvariables-qv'] = null;
            $cc['security-context'] = [];
        } else {
            $kv = new stack_cas_keyval($questionvariables, $options);

            /* $kv->get_security($sec); ??? Maybe set_security instead*/
            $kv->set_security($sec);


            if (!$kv->get_valid()) {
                throw new stack_exception('Error(s) in question-variables: ' . implode('; ', $kv->get_errors()));
            }
            $c = $kv->compile('/qv', $map);
            // Store the pre-validated statement representing the whole qv.
            $cc['statement-qv'] = $c['statement'];
            // Store any contextvariables, e.g. assume statements.
            $cc['contextvariables-qv'] = $c['contextvariables'];
            // Store the possible block external features.
            $cc['preamble-qv'] = $c['blockexternal'];
            // Finally extend the forbidden keys set if we saw any variables written.
            if (isset($c['references']['write'])) {
                $forbiddenkeys = array_merge($forbiddenkeys, $c['references']['write']);
            }
            if (isset($c['includes'])) {
                $cc['includes']['keyval'] = $c['includes'];
            }
        }

        // Then do some basic detail collection related to the inputs and PRTs.
        foreach ($inputs as $input) {
            if (is_a($input, 'stack_units_input')) {
                $units = true;
                break;
            }
        }
        $cc['required'] = [];
        $cc['prt-preamble'] = [];
        $cc['prt-contextvariables'] = [];
        $cc['prt-signature'] = [];
        $cc['prt-definition'] = [];
        $cc['prt-trace'] = [];
        $i = 0;
        foreach ($prts as $name => $prt) {
            $path = '/p/' . $i;
            $i = $i + 1;
            $r = $prt->compile($inputs, $forbiddenkeys, $defaultpenalty, $sec, $path, $map);
            $cc['required'][$name] = $r['required'];
            if ($r['be'] !== null && $r['be'] !== '') {
                $cc['prt-preamble'][$name] = $r['be'];
            }
            if ($r['cv'] !== null && $r['cv'] !== '') {
                $cc['prt-contextvariables'][$name] = $r['cv'];
            }
            $cc['prt-signature'][$name] = $r['sig'];
            $cc['prt-definition'][$name] = $r['def'];
            $cc['prt-trace'][$name] = $r['trace'];
            $units = $units || $r['units'];
            if (isset($r['includes'])) {
                if (!isset($cc['includes'])) {
                    $cc['includes'] = $r['includes'];
                } else {
                    if (isset($r['includes']['keyval'])) {
                        if (!isset($cc['includes']['keyval'])) {
                            $cc['includes']['keyval'] = [];
                        }
                        $cc['includes']['keyval'] = array_unique(array_merge($cc['includes']['keyval'],
                            $r['includes']['keyval']));
                    }
                    if (isset($r['includes']['castext'])) {
                        if (!isset($cc['includes']['castext'])) {
                            $cc['includes']['castext'] = [];
                        }
                        $cc['includes']['castext'] = array_unique(array_merge($cc['includes']['castext'],
                            $r['includes']['castext']));
                    }
                }
            }
        }

        // Note that instead of just adding the unit loading to the 'preamble-qv'
        // and forgetting about units we do keep this bit of information stored
        // as it may be used in input configuration at some later time.
        $cc['units'] = $units;
        $cc['forbiddenkeys'] = $forbiddenkeys;

        // Do some pluginfile mapping. Note that the PRT-nodes are mapped in PRT-compiler.
        $questiontext = assStackQuestionUtils::stack_castext_file_filter($questiontext, [
            'questionid' => $id,
            'field' => 'questiontext'
        ]);
        $generalfeedback = assStackQuestionUtils::stack_castext_file_filter($generalfeedback, [
            'questionid' => $id,
            'field' => 'generalfeedback'
        ]);
        $specificfeedback = assStackQuestionUtils::stack_castext_file_filter($specificfeedback, [
            'questionid' => $id,
            'field' => 'specificfeedback'
        ]);
        // Legacy questions may have a null description before being saved/compiled.
        if ($questiondescription === null) {
            $questiondescription = '';
        }
        $questiondescription = assStackQuestionUtils::stack_castext_file_filter($questiondescription, [
            'questionid' => $id,
            'field' => 'questiondescription'
        ]);
        $prtcorrect = assStackQuestionUtils::stack_castext_file_filter($prtcorrect, [
            'questionid' => $id,
            'field' => 'prtcorrect'
        ]);
        $prtpartiallycorrect = assStackQuestionUtils::stack_castext_file_filter($prtpartiallycorrect, [
            'questionid' => $id,
            'field' => 'prtpartiallycorrect'
        ]);
        $prtincorrect = assStackQuestionUtils::stack_castext_file_filter($prtincorrect, [
            'questionid' => $id,
            'field' => 'prtincorrect'
        ]);

        // Compile the castext fragments.
        $ctoptions = [
            'bound-vars' => $forbiddenkeys,
            'prt-names' => array_flip(array_keys($prts)),
            'io-blocks-as-raw' => 'pre-input2',
            'static string extractor' => $map
        ];
        $ct = castext2_evaluatable::make_from_source($questiontext, '/qt');
        if (!$ct->get_valid($questiontextformat, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in question-text: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-qt'] = $ct->get_evaluationform();
            // Note that only with "question-text" may we get inlined downloads.
            foreach ($ct->get_special_content() as $key => $values) {
                if ($key === 'text-download') {
                    foreach ($values as $k => $v) {
                        $cc['castext-td-' . $k] = $v;
                    }
                } else if ($key === 'castext-includes') {
                    if (!isset($cc['includes'])) {
                        $cc['includes'] = ['castext' => $values];
                    } else if (!isset($cc['includes']['castext'])) {
                        $cc['includes']['castext'] = $values;
                    } else {
                        foreach ($values as $url) {
                            if (array_search($url, $cc['includes']['castext']) === false) {
                                $cc['includes']['castext'][] = $url;
                            }
                        }
                    }
                }
            }
        }

        $ct = castext2_evaluatable::make_from_source($questionnote, '/qn');
        if (!$ct->get_valid(assStackQuestionUtils::FORMAT_HTML, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in question-note: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-qn'] = $ct->get_evaluationform();
        }

        $ct = castext2_evaluatable::make_from_source($generalfeedback, '/gf');
        if (!$ct->get_valid($generalfeedbackformat, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in general-feedback: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-gf'] = $ct->get_evaluationform();
        }

        $ct = castext2_evaluatable::make_from_source($specificfeedback, '/sf');
        if (!$ct->get_valid($specificfeedbackformat, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in specific-feedback: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-sf'] = $ct->get_evaluationform();
        }

        $ct = castext2_evaluatable::make_from_source($questiondescription, '/qd');
        if (!$ct->get_valid($questiondescriptionformat, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in question description: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-qd'] = $ct->get_evaluationform();
        }

        $ct = castext2_evaluatable::make_from_source($prtcorrect, '/pc');
        if (!$ct->get_valid($prtcorrectformat, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in PRT-correct message: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-prt-c'] = $ct->get_evaluationform();
        }

        $ct = castext2_evaluatable::make_from_source($prtpartiallycorrect, '/pp');
        if (!$ct->get_valid($prtpartiallycorrectformat, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in PRT-partially correct message: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-prt-pc'] = $ct->get_evaluationform();
        }

        $ct = castext2_evaluatable::make_from_source($prtincorrect, '/pi');
        if (!$ct->get_valid($prtincorrectformat, $ctoptions, $sec)) {
            throw new stack_exception('Error(s) in PRT-incorrect message: ' . implode('; ', $ct->get_errors(false)));
        } else {
            $cc['castext-prt-ic'] = $ct->get_evaluationform();
        }

        // Remember to collect the extracted strings once all has been done.
        $cc['static-castext-strings'] = $map->get_map();

        // The time of the security context as it were during 2021 was short, now only
        // the input variables remain.
        $si = [];

        // Mark all inputs. To let us know that they have special types.
        foreach ($inputs as $key => $value) {
            if (!isset($si[$key])) {
                $si[$key] = [];
            }
            $si[$key][-2] = -2;
        }
        $cc['security-context'] = $si;

        return $cc;
    }

    /**
     * Collects all text in the question which could contain media objects
     * These were created with the Rich Text Editor
     * The collection is needed to delete unused media objects
     */
    protected function getRTETextWithMediaObjects(): string
    {

        // question text, suggested solutions etc
        $collected = parent::getRTETextWithMediaObjects();

        if (isset($this->options)) {
            $collected .= $this->specific_feedback;
            $collected .= $this->prt_correct;
            $collected .= $this->prt_partially_correct;
            $collected .= $this->prt_incorrect;
        }

        if (isset($this->extra_info)) {
            $collected .= $this->general_feedback;
        }

        foreach ($this->prts as $prt) {
            foreach ($prt->get_nodes_summary() as $node) {
                $collected .= $node->truefeedback;
                $collected .= $node->falsefeedback;
            }
        }

        return $collected;
    }

    /* QUESTIONTYPE METHODS END */

    /**
     * @return bool
     */
    public function checkMaximaConnection(): bool
    {
        try {
            list($message, $genuinedebug, $result) = stack_connection_helper::stackmaxima_genuine_connect();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCasTextProcessor(): ?castext2_processor
    {
        return $this->cas_text_processor;
    }

    public function setCasTextProcessor(?castext2_processor $cas_text_processor): void
    {
        $this->cas_text_processor = $cas_text_processor;
    }
}