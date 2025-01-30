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

namespace ui\Component\Input\Field;

use assStackQuestionUtils;
use Expand;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Implementation\Component\Input\Field\Renderer as RendererILIAS;
use ilRTE;
use ilTemplate;
use ilTemplateException;
use ilTinyMCE;

/**
 * Class Renderer
 */
class Renderer extends RendererILIAS
{
    protected function getComponentInterfaceName(): array
    {
        return [
            TextareaRTE::class,
            ExpandableSection::class,
        ];
    }

    /**
     * @throws ilTemplateException
     */
    public function render(Component $component, \ILIAS\UI\Renderer $default_renderer): string
    {
        global $DIC;

        $DIC->ui()->mainTemplate()->addJavaScript('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/Input/Field/customField.js');
        $DIC->ui()->mainTemplate()->addCss('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/Input/Field/customField.css');

        switch (true) {
            case $component instanceof TextareaRTE:
                $component_rendered = $this->renderTextareaRTE($component);
                break;
            case $component instanceof ExpandableSection:
                $component_rendered = $this->renderExpandableSection($component, $default_renderer);
                break;
            case $component instanceof TabSection:
                $component_rendered = $this->renderTabSection($component, $default_renderer);
                break;
            case $component instanceof ColumnSection:
                $component_rendered = $this->renderColumnSection($component, $default_renderer);
                break;
            case $component instanceof Legacy:
                $component_rendered = $component->getHtml();
                break;
            case $component instanceof ButtonSection:
                $component_rendered = $this->renderButtonSection($component, $default_renderer);
                break;
            default:
                $component_rendered = parent::render($component, $default_renderer);
                break;
        }

        return $component_rendered;
    }

    /**
     * @throws ilTemplateException
     */
    protected function wrapInFormContext(
        FormInput $component,
        string $input_html,
        string $id_pointing_to_input = '',
        string $dependant_group_html = '',
        bool $bind_label_with_for = true
    ): string {
        $tpl = new ilTemplate("src/UI/templates/default/Input/tpl.context_form.html", true, true);

        $tpl->setVariable("INPUT", $input_html);

        if ($id_pointing_to_input && $bind_label_with_for) {
            $tpl->setCurrentBlock('for');
            $tpl->setVariable("ID", $id_pointing_to_input);
            $tpl->parseCurrentBlock();
        }

        $label = $component->getLabel();
        $tpl->setVariable("LABEL", $label);

        $byline = $component->getByline();
        if ($byline) {
            $tpl->setVariable("BYLINE", $byline);
        }

        $required = $component->isRequired();
        if ($required) {
            $tpl->touchBlock("required");
        }

        $error = $component->getError();
        if ($error) {
            $tpl->setVariable("ERROR", $error);
            $tpl->setVariable("ERROR_FOR_ID", $id_pointing_to_input);
        }

        $tpl->setVariable("DEPENDANT_GROUP", $dependant_group_html);
        return $tpl->get();
    }

    protected function maybeDisable2(FormInput $component, ilTemplate $tpl): void
    {
        if ($component->isDisabled()) {
            $tpl->setVariable("DISABLED", 'disabled="disabled"');
        }
    }

    protected function applyName2(FormInput $component, ilTemplate $tpl): ?string
    {
        $name = $component->getName();
        $tpl->setVariable("NAME", $name);
        return $name;
    }

    protected function bindJSandApplyId2(FormInput $component, ilTemplate $tpl): string
    {
        $id = $this->bindJavaScript($component) ?? $this->createId();
        $tpl->setVariable("ID", $id);
        return $id;
    }

    protected function applyValue2(FormInput $component, ilTemplate $tpl, callable $escape = null): void
    {
        $value = $component->getValue();
        if (!is_null($escape)) {
            $value = $escape($value);
        }
        if (isset($value) && $value != '') {
            $tpl->setVariable("VALUE", assStackQuestionUtils::_solveKeyBracketsBug($value));
        }
    }

    private function getTemplateCustom(string $name): ilTemplate
    {
        return new ilTemplate("Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/Component/Input/Field/$name", true, true);
    }

    /**
     * @throws ilTemplateException
     */
    private function renderTextareaRTE(TextareaRTE $component): string
    {
        /** @var $component TextareaRTE */
        $component = $component->withAdditionalOnLoadCode(
            static function ($id): string {
                return "
                    il.UI.Input.textarea.init('$id');
                ";
            }
        );

        $tpl = $this->getPreparedTextareaRTETemplate($component);
        $id = $this->bindJSandApplyId2($component, $tpl);

        return $this->wrapInFormContext($component, $tpl->get(), $id);
    }

    protected function getPreparedTextareaRTETemplate(TextareaRTE $component): ilTemplate
    {
        $tpl = $this->getTemplateCustom("tpl.textareaRte.html");

        if (0 < $component->getMaxLimit()) {
            $tpl->setVariable('REMAINDER_TEXT', $this->txt('ui_chars_remaining'));
            $tpl->setVariable('REMAINDER', $component->getMaxLimit() - strlen($component->getValue() ?? ''));
            $tpl->setVariable('MAX_LIMIT', $component->getMaxLimit());
        }

        if (null !== $component->getMinLimit()) {
            $tpl->setVariable('MIN_LIMIT', $component->getMinLimit());
        }

        $this->applyName2($component, $tpl);
        $this->applyValue2($component, $tpl, $this->htmlEntities());
        $this->maybeDisable2($component, $tpl);

        $rte_string = ilRTE::_getRTEClassname();
        /** @var ilTinyMCE $rte */
        $rte = new $rte_string();

        $rte->addPlugin("emoticons");
        $rte->addPlugin("latex");
        $rte->addButton("latex");
        $rte->addButton("pastelatex");

        $rteSupport = $component->getRTESupport();

        if (!empty($rteSupport)) {
            $rte->addRTESupport($rteSupport["obj_id"], $rteSupport["obj_type"], $rteSupport["module"], false, $rteSupport['cfg_template'], $rteSupport['hide_switch']);

            $tpl->setVariable('RTE_EDITOR', "yesRTEditor");
        } else {
            $tpl->setVariable('RTE_EDITOR', "noRTEditor");
        }

        return $tpl;
    }

    /**
     * @throws ilTemplateException
     */
    private function renderExpandableSection(ExpandableSection $component, \ILIAS\UI\Renderer $default_renderer): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.expandableSection.html");

        $inputs_html = "";

        foreach ($component->getInputs() as $input) {
            $inputs_html .= $default_renderer->render($input);
        }

        $section_tpl->setVariable("INPUTS", $inputs_html);
        $section_tpl->setVariable("LABEL", $component->getLabel());

        if ($component->getByline() !== null) {
            $section_tpl->setCurrentBlock("byline");
            $section_tpl->setVariable("BYLINE", $component->getByline());
            $section_tpl->parseCurrentBlock();
        }

        $expand = new Expand($component->isExpandedByDefault());

        $section_tpl->setVariable("VIEW_CONTROL", $default_renderer->render($expand));

        return $section_tpl->get();
    }

    /**
     * @throws ilTemplateException
     */
    private function renderTabSection(TabSection $component, \ILIAS\UI\Renderer $default_renderer): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.tabSection.html");

        $section_tpl->setVariable("LABEL", $component->getLabel());

        if ($component->getByline() !== null) {
            $section_tpl->setCurrentBlock("byline");
            $section_tpl->setVariable("BYLINE", $component->getByline());
            $section_tpl->parseCurrentBlock();
        }

        $tabs_buttons = "";
        $tabs_panels = "";

        $isFirst = " active";

        $uid = uniqid();

        foreach ($component->getTabs() as $tab_name => $tab) {
            $tabs_buttons .= "<div class='tab-button$isFirst' data-tab='$tab_name' data-section-id='$uid'>{$tab_name}</div>";

            $inputs_html = "";

            foreach ($tab as $input) {
                $inputs_html .= $default_renderer->render($input);
            }

            $tabs_panels .= "<div class='tab-panel$isFirst' data-tab-panel='$tab_name' data-section-id='$uid'>$inputs_html</div>";

            $isFirst = "";
        }

        $section_tpl->setVariable("TAB_BUTTONS", $tabs_buttons);
        $section_tpl->setVariable("TAB_PANELS", $tabs_panels);

        return $section_tpl->get();
    }

    /**
     * @throws ilTemplateException
     */
    private function renderColumnSection(ColumnSection $component, \ILIAS\UI\Renderer $default_renderer): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.columnSection.html");

        $section_tpl->setVariable("LABEL", $component->getLabel());

        if ($component->getByline() !== null) {
            $section_tpl->setCurrentBlock("byline");
            $section_tpl->setVariable("BYLINE", $component->getByline());
            $section_tpl->parseCurrentBlock();
        }

        $columns_html = "";

        foreach ($component->getColumns() as $column_name => $column) {
            $inputs_html = "";

            foreach ($column as $input) {
                $inputs_html .= $default_renderer->render($input);
            }

            $columns_html .= "<div class='column'";

            $columns_html .= $component->renderColumnStyle($column_name);

            $columns_html .= ">$inputs_html</div>";
        }

        $section_tpl->setVariable("COLUMNS", $columns_html);

        return $section_tpl->get();
    }

    /**
     * @throws ilTemplateException
     */
    private function renderButtonSection(ButtonSection $component, \ILIAS\UI\Renderer $default_renderer): string
    {
        $section_tpl = $this->getTemplateCustom("tpl.buttonSection.html");

        $buttons_html = "";

        foreach ($component->getButtons() as $button) {
            $buttons_html .= $default_renderer->render($button);
        }

        $section_tpl->setVariable("INPUTS", $buttons_html);

        return $this->wrapInFormContext($component, $section_tpl->get(), $this->bindJSandApplyId2($component, $section_tpl));
    }
}