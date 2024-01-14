<?php
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

//require_once "./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php";

/**
 * STACK Question plugin for ILIAS 4.4+
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id$
 *
 */
class ilassStackQuestionPlugin extends ilQuestionsPlugin
{

    final function getPluginName(): string
    {
        return "assStackQuestion";
    }

    final function getQuestionType(): string
    {
        return "assStackQuestion";
    }

    final function getQuestionTypeTranslation(): string
    {
        return $this->txt($this->getQuestionType());
    }

    protected function readEventListening(): void
    {
    }

}

?>