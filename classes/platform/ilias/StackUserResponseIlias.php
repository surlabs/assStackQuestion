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

    /**
     * @var string The purpose of the stack user response.
     */
    private string $purpose;

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): void
    {
        $this->purpose = $purpose;
    }

    /**
     * @var ?array The stack user response.
     */
    private ?array $user_response = null;

    public function __construct(string $purpose)
    {
        $this->setPurpose($purpose);
    }

    /**
     * Returns the stack user response from different sources depending on the purpose.
     * @throws StackException
     */
    public function getStackUserResponse(): array
    {
        // Return the user response if it has already been set.
        if (is_array($this->user_response)) {
            return $this->user_response;
        }

        switch ($this->getPurpose()) {
            case 'post':
                $stack_user_response = $this->getPostStackUserResponse();
                break;
            case 'preview':
                $stack_user_response = $this->getPreviewStackUserResponse();
                break;
            case 'test':
                $stack_user_response = $this->getTestStackUserResponse();
                break;
            case 'unit_test':
                $stack_user_response = $this->getUnitTestStackUserResponse();
                break;
            case 'correct':
                $stack_user_response = $this->getCorrectStackUserResponse();
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

    public function saveStackUserResponse(array $stack_user_response): void
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

    }

    private function getPostStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
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

    private function getCorrectStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
    }

    private function getUnitTestStackUserResponse(): array
    {
        $stack_user_response = array();
        return $stack_user_response;
    }

}