<?php
declare(strict_types=1);

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

use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Expand
 */
class Expand implements ExpandInterface {
    use ComponentHelper;

    private bool $expanded_by_default = false;

    public function __construct(bool $expanded_by_default = false) {
        $this->expanded_by_default = $expanded_by_default;
    }

    public function isExpandedByDefault(): bool {
        return $this->expanded_by_default;
    }

    public function withExpandedByDefault(bool $expanded_by_default): ExpandInterface {
        $this->expanded_by_default = $expanded_by_default;
        return $this;
    }
}