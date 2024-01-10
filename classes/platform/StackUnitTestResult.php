<?php
declare(strict_types=1);

namespace classes\platform;

use prt_evaluatable;
use stack_utils;
use stdClass;

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

class StackUnitTestResult {
    /**
     * @var StackUnitTest the test case that this is the results for.
     */
    public StackUnitTest $testcase;

    /**
     * @var array input name => actual value put into this input.
     */
    public array $inputvalues;

    /**
     * @var array input name => modified value of this input.
     */
    public array $inputvaluesmodified;

    /**
     * @var array input name => the displayed value of that input.
     */
    public array $inputdisplayed;

    /**
     * @var array input name => any errors created by invalid input.
     */
    public array $inputerrors;

    /**
     * @var array input name => the input statues. One of the stack_input::STATUS_... constants.
     */
    public array $inputstatuses;

    /**
     * @var array prt name => stack_potentialresponse_tree_state object
     */
    public array $actualresults;

    /**
     * @var float Store the question penalty to check defaults.
     */
    public float $questionpenalty;

    /**
     * @var bool Store whether this looks like a trivial empty test case.
     */
    public bool $emptytestcase;

    /**
     * Constructor
     * @param StackUnitTest $testcase the testcase this is the results for.
     */
    public function __construct(StackUnitTest $testcase) {
        $this->testcase = $testcase;
    }

    /**
     * Set the part of the results data that describes the state of one of the inputs.
     * @param string $inputname the input name.
     * @param string $inputvalue the value of this input.
     * @param string $inputmodified
     * @param string $displayvalue the displayed version of the value that was input.
     * @param string $status one of the stack_input::STATUS_... constants.
     * @param string $error
     */
    public function setInputState(string $inputname, string $inputvalue, string $inputmodified, string $displayvalue, string $status, string $error) {
        $this->inputvalues[$inputname]         = $inputvalue;
        $this->inputvaluesmodified[$inputname] = $inputmodified;
        $this->inputdisplayed[$inputname]      = $displayvalue;
        $this->inputstatuses[$inputname]       = $status;
        $this->inputerrors[$inputname]         = $error;
    }

    public function setPrtResult($prtname, prt_evaluatable $actualresult) {
        $this->actualresults[$prtname] = $actualresult;
    }

    public function setQuestionPenalty($penalty) {
        $this->questionpenalty = $penalty;
    }

    /**
     * @return array input name => object with fields ->input, ->display and ->status.
     */
    public function getInputStates(): array
    {
        $states = array();

        foreach ($this->inputvalues as $inputname => $inputvalue) {
            $state = new stdClass();
            $state->rawinput = $this->testcase->getInput($inputname);
            $state->input = $inputvalue;
            $state->modified = $this->inputvaluesmodified[$inputname];
            $state->display = $this->inputdisplayed[$inputname];
            $state->status = $this->inputstatuses[$inputname];
            $state->errors = $this->inputerrors[$inputname];
            $states[$inputname] = $state;
        }

        return $states;
    }

    /**
     * Ensure we round scores and penalties consistently.
     * @param float $score
     * @return float
     */
    private function roundPrtScores(float $score): float
    {
        return round(stack_utils::fix_to_continued_fraction($score, 4), 3);
    }

    /**
     * @return array input name => object with fields ->mark, ->expectedmark,
     *      ->penalty, ->expectedpenalty, ->answernote, ->expectedanswernote,
     *      ->feedback and ->testoutcome.
     */
    public function getPrtStates(): array
    {
        $states = array();

        foreach ($this->testcase->expectedResults as $prtname => $expectedresult) {
            $expectedanswernote = $expectedresult->answernotes;

            $state = new stdClass();
            $state->expectedscore = $expectedresult->score;
            if (!is_null($state->expectedscore)) {
                $state->expectedscore = $this->roundPrtScores($state->expectedscore + 0);
            }
            $state->expectedpenalty = $expectedresult->penalty;
            if (!is_null($state->expectedpenalty)) {
                $state->expectedpenalty = $this->roundPrtScores($state->expectedpenalty + 0);
            }
            $state->expectedanswernote = reset($expectedanswernote);

            if (array_key_exists($prtname, $this->actualresults)) {
                $actualresult = $this->actualresults[$prtname];
                $actualscore = $actualresult->get_score();
                if (!is_null($actualscore)) {
                    $actualscore = $this->roundPrtScores($actualscore + 0);
                }
                $state->score = $actualscore;
                $actualpenalty = $actualresult->get_penalty();
                if (!is_null($actualpenalty)) {
                    $actualpenalty = $this->roundPrtScores($actualpenalty + 0);
                }
                $state->penalty = $actualpenalty;
                $state->answernote = implode(' | ', $actualresult->get_answernotes());
                $state->trace = implode("\n", $actualresult->get_trace());
                $state->feedback = $actualresult->get_feedback();
                $state->debuginfo = $actualresult->get_debuginfo();
            } else {
                $state->score = '';
                $state->penalty = '';
                $state->answernote = '';
                $state->trace = '';
                $state->feedback = '';
                $state->debuginfo = '';
            }

            $state->testoutcome = true;
            $reason = array();
            if (is_null($state->expectedscore) != is_null($state->score) ||
                abs($state->expectedscore - $state->score) > 10E-6) {
                $state->testoutcome = false;
                $reason[] = stack_string('score');
            }
            // If the expected penalty is null then we use the question default penalty.
            $penalty = $state->expectedpenalty;
            if (is_null($state->expectedpenalty)) {
                $penalty = $this->questionpenalty;
            }
            // If we have a "NULL" expected answer note we just ignore what happens to penalties here.
            if ('NULL' !== $state->expectedanswernote) {
                if (is_null($state->penalty) ||
                    abs($penalty - $state->penalty) > 10E-6) {
                    $state->testoutcome = false;
                    $reason[] = stack_string('penalty');
                }
            }
            if (isset($actualresult)) {
                if (!$this->testAnswerNote($state->expectedanswernote, $actualresult->get_answernotes())) {
                    $state->testoutcome = false;
                    $reason[] = stack_string('answernote');
                }
            }
            if (empty($reason)) {
                $state->reason = '';
            } else {
                $state->reason = ' ('.implode(', ', $reason).')';
            }

            $states[$prtname] = $state;
        }

        return $states;
    }

    /**
     * Test that the expected and actual answer notes match, to the level we can test.
     * @param string $expected the expected final answer note.
     * @param array $actual the actual answer notes returend.
     * @return bool whether the answer notes match sufficiently.
     */
    protected function testAnswerNote(string $expected, array $actual): bool
    {
        $lastactual = array_pop($actual) ?? '';
        if ('NULL' == $expected) {
            return '' == trim($lastactual);
        }
        return trim($lastactual) == trim($expected);
    }

    /**
     * @return bool whether the test passed successfully.
     */
    public function passed(): bool
    {
        if ($this->emptytestcase) {
            return false;
        }
        foreach ($this->getPrtStates() as $state) {
            if (!$state->testoutcome) {
                return false;
            }
        }
        return true;
    }
}