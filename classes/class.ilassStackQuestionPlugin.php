<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

require_once "./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php";

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