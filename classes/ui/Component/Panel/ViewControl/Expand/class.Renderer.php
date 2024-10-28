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

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;

/**
 * Class Renderer
 */
class Renderer extends AbstractComponentRenderer
{
    protected function getComponentInterfaceName(): array
    {
        return [Expand::class];
    }

    /**
     * @throws ilTemplateException
     */
    public function render(Component $component, \ILIAS\UI\Renderer $default_renderer): string
    {
        global $DIC;

        $DIC->ui()->mainTemplate()->addCss("Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/expand.css");
        $DIC->ui()->mainTemplate()->addJavaScript("Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/expand.js");

        $tpl = new ilTemplate("Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/tpl.expand.html", true, true);

        $tpl->setVariable("IMG_EXP_OR_COL", $component->isExpandedByDefault() ? "exp" : "col");
        $tpl->setVariable("IMG_ALT", $component->isExpandedByDefault() ? "Expanded" : "Collapsed");
        $tpl->setVariable("STATE", $component->isExpandedByDefault() ? "expanded" : "collapsed");

        return $tpl->get();
    }
}