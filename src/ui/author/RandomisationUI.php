<?php
declare(strict_types=1);

use ILIAS\UI\Component\Panel\Sub;

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
class RandomisationUI
{

    private \ILIAS\UI\Factory $factory;

    private \ILIAS\UI\Renderer $renderer;

    private \ilLanguage $language;

    private $control;

    private array $data;

    public function __construct(array $data)
    {
        global $DIC;

        $default_data = [
            "active_variant_identifier" => "\(236412378944623\)",
            "active_variant_question_text" => "\(Question Text\)",
            "active_variant_question_note" => "\(Question Note\)",
            "active_variant_question_variables" => "\(Question Variables\)",
            "active_variant_feedback_variables" => "\(Feedback Variables\)",
            "deployed_variants" => [
                "236412378944623" => [
                    "question_note" => "\(Question Note\)",
                    "question_variables" => "\(Question Variables\)",
                    "unit_test_passed" => "\(True\)"
                ],
                "236412378944624" => [
                    "question_note" => "\(Question Note\)",
                    "question_variables" => "\(Question Variables\)",
                    "unit_test_passed" => "\(True\)"
                ],
                "236412378944625" => [
                    "question_note" => "\(Question Note\)",
                    "question_variables" => "\(Question Variables\)",
                    "unit_test_passed" => "\(True\)"
                ]
            ]
        ];

        $this->data = $default_data;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();
        $this->control = $DIC->ctrl();

        //Ensure MathJax is loaded
        $mathJaxSetting = new ilSetting("MathJax");
        $DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));
    }

    public function show(): string
    {
        $html = "";

        //Deployed variants panel
        $deployed_variants_panel = $this->factory->panel()->standard(
            '[[Active Variant (Currently used in question preview)]]',
            array(
                $this->getCurrentActiveVariantPanelUIComponent(),
                $this->getCurrentlyDeployedVariantsPanelUIComponent()
            )
        );
        $html .= $this->renderer->render($deployed_variants_panel);

        //Test overview panel
        $test_overview_panel = $this->factory->panel()->standard(
            '[[Unit test for this question]]',
            array(
                $this->getCurrentActiveVariantPanelUIComponent(),
            )
        );
        $html .= $this->renderer->render($test_overview_panel);

        return $html;
    }

    /**
     * Returns the UI subcomponent for the currently active variant panel
     * which is a Sub section of a panel
     * @return Sub
     */
    private function getCurrentActiveVariantPanelUIComponent(): Sub
    {
        //Actions for the currently active variant
        $current_active_variant_panel_actions = $this->factory->dropdown()->standard(array(
            $this->factory->button()->shy(
                $this->language->txt("ui_author_randomisation_change_active_variant_button_text"),
                //TODO: Change this to a modal
                $this->control->getLinkTargetByClass("assstackquestiongui", "changeActiveVariant")),
            $this->factory->button()->shy(
            //TODO: Connect with method
                $this->language->txt("ui_author_randomisation_run_all_tests_for_active_variant_button_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "runAllTestsForActiveVariant")),
            $this->factory->button()->shy(
            //TODO: Connect with method
                $this->language->txt("ui_author_randomisation_run_all_tests_for_all_variants_button_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "runAllTestsForAllVariants")),
        ));

        //Return the UI component
        return $this->factory->panel()->sub(
            $this->data["active_variant_identifier"],
            $this->factory->legacy(
                $this->data["active_variant_question_note"] .
                $this->renderer->render($this->factory->divider()->horizontal()) .
                $this->data["active_variant_question_text"])
            )
            ->withCard($this->factory->card()->standard(
                '[[Question and Feedback Variables with this variant]]'
            )
            ->withSections(array(
                $this->factory->legacy($this->data["active_variant_question_variables"]),
                $this->factory->divider()->horizontal(),
                $this->factory->legacy($this->data["active_variant_feedback_variables"]),
            )))
            ->withActions($current_active_variant_panel_actions);
    }

    /**
     * Returns the UI subcomponent for the currently deployed variants panel
     * which is a Sub section of a panel
     * @return Sub
     */
    private function getCurrentlyDeployedVariantsPanelUIComponent(): Sub
    {

        //Actions for all deployed variants
        $deployed_seeds_bulk_actions = $this->factory->dropdown()->standard(array(
            $this->factory->button()->shy(
                $this->language->txt("ui_author_randomisation_generate_new_variants_title"),
                //TODO: Connect with method
                $this->control->getLinkTargetByClass("assstackquestiongui", "generateNewVariants")),
        ));

        //Actions for each deployed variant
        $deployed_variant_individual_actions = $this->factory->dropdown()->standard(array(
            $this->factory->button()->shy($this->language->txt("ui_author_randomisation_delete_deployed_variant_title"),
                //TODO: Connect with method
                $this->control->getLinkTargetByClass("assstackquestiongui", "deleteVariant")),
            $this->factory->button()->shy($this->language->txt("ui_author_randomisation_set_as_active_variant_title"),
                //TODO: Connect with method
                $this->control->getLinkTargetByClass("assstackquestiongui", "setAsActiveVariant")),
        ));

        $array_of_deployed_variants = [];

        //Fill the data for each deployed variant
        foreach ($this->data["deployed_variants"] as $deployed_variant_identifier => $deployed_variant_data) {
            $array_of_deployed_variants[] = $this->factory->panel()->sub(
                (string)$deployed_variant_identifier,
                $this->factory->legacy(
                    $deployed_variant_data["unit_test_passed"] .
                    $this->renderer->render($this->factory->divider()->vertical()) .
                    $deployed_variant_data["question_note"]))
                ->withActions($deployed_variant_individual_actions);
        }

        //Return the UI component
        return $this->factory->panel()->sub(
            '[[Currently deployed variants]]',
            array(
                $this->factory->item()->group('[[The following variants are currently used in tests]]',
                    $array_of_deployed_variants
                )))
            ->withActions($deployed_seeds_bulk_actions);
    }
}