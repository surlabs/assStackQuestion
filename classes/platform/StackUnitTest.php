<?php
declare(strict_types=1);

namespace classes\platform;


use assStackQuestion;
use stack_ast_container;
use stack_cas_security;
use stack_cas_session2;
use stack_exception;
use stack_maths;
use stack_potentialresponse_tree_state;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 * This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 * originally created by Chris Sangwin.
 *
 * The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "STACK Question" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/STACK
 *
 * If you need support, please contact the maintainer of this software at:
 * stack@surlabs.es
 *
 *********************************************************************/

class StackUnitTest {
    /**
     * @var string Give each testcase a meaningful description.
     */
    public string $description;

    /**
     * @var int|null test-case number, if this is a real test stored in the database, else null.
     */
    public ?int $testCase;

    /**
     * @var array input name => value to be entered.
     */
    public array $inputs;

    /**
     * @var array prt name => stack_potentialresponse_tree_state object
     */
    public array $expectedResults = array();

    /**
     * Constructor
     * @param string $description
     * @param array $inputs input name => value to enter.
     * @param int|null $testCase test-case number, if this is a real test stored in the database.
     */
    public function __construct(string $description, array $inputs, ?int $testCase = null) {
        $this->description = $description;
        $this->inputs = $inputs;
        $this->testCase = $testCase;
    }

    /**
     * Set the expected result for one of the PRTs.
     * @param string $prtname which PRT.
     * @param stack_potentialresponse_tree_state $expectedResult the expected result
     *      for this PRT. Only the mark, penalty and answernote fields are used.
     */
    public function addExpectedResult(string $prtname, stack_potentialresponse_tree_state $expectedResult) {
        $this->expectedResults[$prtname] = $expectedResult;
    }

    /**
     * Run this test against a particular question.
     * @param int $questionid The database id of the question to test.
     * @param int|null $seed the random seed to use.
     * @return StackUnitTestResult the test results.
     * @throws stack_exception
     */
    public function run(int $questionid, ?int $seed = null): StackUnitTestResult
    {
        // We don't permit completely empty test cases.
        // Completely empty test cases always pass, which is spurious in the bulk test.
        $emptytestcase = true;

        // Create a completely clean version of the question usage we will use.
        // Evaluated state is stored in question variables etc.
        $question = new assStackQuestion();
        $question->loadFromDb($questionid);

        if (!$question->isInstantiated()) {
            //TODO: Change the seed
            $question->questionInitialisation(1);
        }

        // Hard-wire testing to use the decimal point.
        // Teachers must use strict Maxima syntax, including in test case construction.
        // I appreciate teachers will, reasonably, want to test the input mechanism.
        // The added internal complexity here is serious.
        // This complexity includes things like matrix input types which need a valid Maxima expression as the value of the input.
        $question->options->set_option('decimals', '.');
        if (!is_null($seed)) {
            $question->seed = $seed;
        }

        $response = self::computeResponse($question, $this->inputs);

        $results = new StackUnitTestResult($this);
        $results->setQuestionPenalty($question->getPenalty());
        foreach ($this->inputs as $inputname => $notused) {
            // Check input still exits, could have been deleted in a question.
            if (array_key_exists($inputname, $question->inputs)) {
                $inputstate = $question->getInputState($inputname, $response);
                // The _val below is a hack.  Not all inputnames exist explicitly in
                // the response, but the _val does. Some inputs, e.g. matrices have
                // many entries in the response so none match $response[$inputname].
                // Of course, a teacher may have left a test case blank in which case the input isn't there either.
                $inputresponse = '';
                if (array_key_exists($inputname, $response)) {
                    $inputresponse = $response[$inputname];
                } else if (array_key_exists($inputname.'_val', $response)) {
                    $inputresponse = $response[$inputname.'_val'];
                }
                if ($inputresponse != '') {
                    $emptytestcase = false;
                }
                $results->setInputState($inputname, $inputresponse, $inputstate->contentsmodified,
                    $inputstate->contentsdisplayed, $inputstate->status, $inputstate->errors);
            }
        }

        foreach ($this->expectedResults as $prtname => $expectedresult) {
            if (implode(' | ', $expectedresult->answernotes) !== 'NULL') {
                $emptytestcase = false;
            }
            $result = $question->getPrtResult($prtname, $response, false);
            // Adapted from renderer.php prt_feedback_display.
            $feedback = $result->get_feedback();
            $feedback = stack_maths::process_display_castext($feedback);

            $result->override_feedback($feedback);
            $results->setPrtResult($prtname, $result);

        }

        $results->emptytestcase = $emptytestcase;

        if ($this->testCase) {
            $this->saveResult($question, $results);
        }

        return $results;
    }

    /**
     * @throws stack_exception
     */
    public static function computeResponse(assStackQuestion $question, $inputs) {
        // If the question has simp:false, then the local options should reflect this.
        // In this case, question authors will need to explicitly simplify their test case constructions.
        $localoptions = clone $question->options;

        // Start with the question variables (note that order matters here).
        $cascontext = new stack_cas_session2(array(), $localoptions, $question->seed);
        $question->addQuestionVarsToSession($cascontext);

        // Add the correct answer for all inputs.
        foreach ($question->inputs as $name => $input) {
            $cs = stack_ast_container::make_from_teacher_source($name . ':' . $input->get_teacher_answer(),
                '', new stack_cas_security());
            $cascontext->add_statement($cs);
        }

        // Turn off simplification - we need test cases to be unsimplified, even if the question option is true.
        $vars = array();
        $cs = stack_ast_container::make_from_teacher_source('simp:false' , '', new stack_cas_security());
        $vars[] = $cs;
        // Now add the expressions we want evaluated.
        foreach ($inputs as $name => $value) {
            // Check input still exits, could have been deleted in a question.
            if ('' !== $value && array_key_exists($name, $question->inputs)) {
                $val = 'testresponse_' . $name . ':' . $value;
                $input = $question->inputs[$name];
                // Except if the input simplifies, then so should the generated testcase.
                // The input will simplify again.
                // We may need to create test cases which will generate errors, such as makelist.
                if ($input->get_extra_option('simp')) {
                    $val = 'testresponse_' . $name . ':ev(' . $value .',simp)';
                }
                $cs = stack_ast_container::make_from_teacher_source($val , '', new stack_cas_security());
                if ($cs->get_valid()) {
                    $vars[] = $cs;
                }
            }
        }
        $cascontext->add_statements($vars);
        if ($cascontext->get_valid()) {
            $cascontext->instantiate();
        }

        $response = array();
        foreach ($inputs as $name => $input) {
            $var = $cascontext->get_by_key('testresponse_' . $name);
            $computedinput = '';
            if ($var !== null && $var->is_correctly_evaluated()) {
                $computedinput = $var->get_value();
            }
            // In the case we start with an invalid input, and hence don't send it to the CAS.
            // We want the response to constitute the raw invalid input.
            // This permits invalid expressions in the inputs, and to compute with valid expressions.
            if ('' == $computedinput) {
                $computedinput = $input;
            } else {
                // 4.3. means the logic_nouns_sort is done through parse trees.
                $computedinput = $cascontext->get_by_key('testresponse_' . $name)->get_dispvalue();
            }
            if (array_key_exists($name, $question->inputs)) {
                // Remove things like apostrophies in test case inputs so we don't create an invalid student input.
                // 4.3. changes this.
                $response = array_merge($response, $question->inputs[$name]->maxima_to_response_array($computedinput));
            }
        }
        return $response;
    }

    /**
     * @param string $inputname the name of one of the inputs.
     * @return string the value to be entered into that input.
     */
    public function getInput(string $inputname) :string {
        return $this->inputs[$inputname];
    }

    /**
     * Store the outcome of running a test in qtype_stack_qtest_results.
     *
     * @param assStackQuestion $question the question being tested.
     * @param StackUnitTestResult $result the test result.
     */
    protected function saveResult(assStackQuestion $question, StackUnitTestResult $result) {

    }
}