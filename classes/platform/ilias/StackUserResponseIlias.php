<?php
declare(strict_types=1);

namespace classes\platform\ilias;

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
class StackUserResponseIlias extends StackUserResponse
{

    private string $purpose;

    public function __construct(string $purpose)
    {
        $this->setPurpose($purpose);
    }

    /**
     * @throws StackException
     */
    public function getStackUserResponse(): array
    {
        switch ($this->getPurpose()) {
            case 'preview':
                $stack_user_response = $this->getPreviewStackUserResponse();
                break;
            case 'test':
                $stack_user_response = $this->getTestStackUserResponse();
                break;
            default:
                throw new StackException('Invalid purpose selected: ' . $this->getPurpose() . '.');
        }

        if (!$this->checkStackUserResponse($stack_user_response)) {
            throw new StackException('Invalid stack user response.');
        } else {
            return $stack_user_response;
        }
    }

    private function getPreviewStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
    }

    private function getTestStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
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