<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use classes\platform\StackCastextRender;
use classes\platform\StackException;
use classes\platform\StackUserResponse;


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
class StackCastextRenderIlias extends StackCastextRender
{

    private string $purpose;

    public function __construct(string $purpose)
    {
        $this->setPurpose($purpose);
    }

    /**
     *
     * @throws StackException
     */
    public function render(): string
    {
        switch ($this->getPurpose()) {
            case 'ilias_question_text':
                return $this->composeIliasQuestionText();
            case 'ilias_specific_feedback':
                return $this->composeIliasSpecificFeedback();
            default:
                throw new StackException('Invalid purpose selected: ' . $this->getPurpose() . '.');
        }
    }

    private function composeIliasQuestionText(): string
    {
        return 'composeIliasQuestionText';
    }

    private function composeIliasSpecificFeedback(): string
    {
        return 'composeIliasSpecificFeedback';
    }


    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): void
    {
        $this->purpose = $purpose;
    }
}