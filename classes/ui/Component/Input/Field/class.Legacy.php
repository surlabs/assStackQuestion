<?php

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
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

declare(strict_types=1);

namespace Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component\Input\Field;

use ILIAS\Data\Factory;
use ILIAS\UI\Implementation\Component\Input\Input;


/**
 * Class Legacy
 */
class Legacy extends Input {
    protected string $html = "";

    public function __construct(string $html)
    {
        global $DIC;

        $this->html = $html;

        parent::__construct(new Factory(), $DIC->refinery());
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function withHtml(string $html): self
    {
        $this->html = $html;
        return $this;
    }

    protected function isClientSideValueOk($value): bool
    {
        return true;
    }
}