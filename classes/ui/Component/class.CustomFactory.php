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

namespace Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component;

use Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component\Input\Field\ExpandableSection;
use Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component\Input\Field\TextareaRTE;

/**
 * Class CustomFactory
 */
class CustomFactory
{
    public function textareaRTE(int $obj_id, string $label, ?string $by_line = null): TextareaRTE
    {
        $textareaRTE = new TextareaRTE($label, $by_line);

        $textareaRTE->setRTESupport($obj_id, "qpl", "xqcas");

        return $textareaRTE;
    }

    public function expandableSection(array $inputs, string $label, ?string $by_line = null): ExpandableSection
    {
        return new ExpandableSection($inputs, $label, $by_line);
    }
}