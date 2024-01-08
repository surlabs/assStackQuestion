<?php
declare(strict_types=1);

namespace classes\ui\author;

use assStackQuestionDB;
use assStackQuestionUtils;
use ilCtrlException;
use ILIAS\UI\Component\Panel\Sub;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilSetting;

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
class RandomisationAndSecurityUI
{

    private Factory $factory;

    private Renderer $renderer;

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
                    "unit_test_passed" => "\(True\)",
                    "question_text" => "\(Question Text\)"
                ],
                "236412378944624" => [
                    "question_note" => "\(Question Note\)",
                    "question_variables" => "\(Question Variables\)",
                    "unit_test_passed" => "\(True\)",
                    "question_text" => "\(Question Text\)"
                ],
                "236412378944625" => [
                    "question_note" => "\(Question Note\)",
                    "question_variables" => "\(Question Variables\)",
                    "unit_test_passed" => "\(True\)",
                    "question_text" => "\(Question Text\)"
                ]
            ]
        ];

        $this->data = $default_data;

        foreach ($data as $key => $value) {

            //deployed seeds
            if ($key === "deployed_seeds") {
                $this->data["deployed_variants"] = [];
                foreach ($value as $id => $deployed_seed) {
                    $active_seed = assStackQuestionDB::_readActiveSeed($deployed_seed["question_id"]);
                    if ((int)$active_seed === (int)$deployed_seed["seed"]) {
                        $this->data["active_variant_identifier"] = (string)$deployed_seed["seed"];
                        $this->data["active_variant_question_note"] = (string)$deployed_seed["note"]->get_rendered();
                        $this->data["active_variant_question_text"] = (string)$deployed_seed["question_text"]->get_rendered();
                        $this->data["active_variant_question_variables"] = (string)$deployed_seed["question_variables"];
                        $this->data["active_variant_feedback_variables"] = (string)$deployed_seed["feedback_id"];
                    }

                    if ($id === "") {
                        //non deployed seed
                    } else {
                        $this->data["deployed_variants"][$deployed_seed["seed"]] = [
                            "question_note" => $deployed_seed["note"],
                            "question_variables" => $deployed_seed["question_id"],
                            "unit_test_passed" => "True",
                            "question_text" => $deployed_seed["question_text"]
                        ];
                    }
                }
                continue;
            }

            if ($key === "unit_tests") {
                $this->data["unit_tests"] = $value;
                continue;
            }
        }

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();
        $this->control = $DIC->ctrl();

        //Ensure MathJax is loaded
        $mathJaxSetting = new ilSetting("MathJax");
        $DIC->globalScreen()->layout()->meta()->addJs($mathJaxSetting->get("path_to_mathjax"));
    }

    public function show(bool $show_add_standard_test_button): string
    {
        $html = "";

        if ($show_add_standard_test_button) {
            //Add standard test button
            $add_standard_test_button = $this->factory->button()->standard(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_add_standard_test_button_text"),
                "addStandardTest");

            $add_standard_test_message_box = $this->factory->messageBox()->info(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_add_standard_test_description")
                . $this->renderer->render($this->factory->divider()->vertical()) .
                $this->renderer->render($add_standard_test_button)
            );
            $html .= $this->renderer->render($add_standard_test_message_box);
        }

        //Active variants panel
        $active_variants_panel = $this->factory->panel()->standard(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_active_variant_panel_title"),
            array(
                $this->getCurrentActiveVariantPanelUIComponent()
            )
        );
        $html .= $this->renderer->render($active_variants_panel);

        //Actions for all deployed variants
        $deployed_seeds_bulk_actions = $this->factory->dropdown()->standard(array(
            $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_generate_new_variants_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "generateNewVariants")),
        ));

        //Deployed variants panel
        $deployed_variants_panel = $this->factory->panel()->standard(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_deployed_variants_panel_title"),
            $this->getCurrentlyDeployedVariantsPanelUIComponent()
        );
        $html .= $this->renderer->render($deployed_variants_panel);

        //Test overview panel
        $test_overview_panel = $this->factory->panel()->standard(
            "",
            $this->getUnitTestStatusPanelUIComponent()
        );
        $html .= $this->renderer->render($test_overview_panel);

        return $html;
    }

    /**
     * Returns the UI subcomponent for the currently active variant panel
     * which is a Sub section of a panel
     * @return Sub
     * @throws ilCtrlException
     */
    private function getCurrentActiveVariantPanelUIComponent(): Sub
    {
        //Actions for the currently active variant
        $current_active_variant_panel_actions = $this->factory->dropdown()->standard(array(
            $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_generate_new_variants_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "generateNewVariants")),
            $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_change_to_random_seed_action_text"),
                //TODO: Change this to a modal
                $this->control->getLinkTargetByClass("assstackquestiongui", "changeToRandomSeed")),
            $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_all_tests_for_active_variant_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "runAllTestsForActiveVariant")),
            $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_all_tests_for_all_variants_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "runAllTestsForAllVariants")),
            $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_add_unit_test_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "addCustomTest"))
        ));

        $question_text = $this->data["active_variant_question_text"];

        $page = $this->factory->modal()->lightboxTextPage(assStackQuestionUtils::_getLatex($question_text), $this->language->txt("qpl_qst_xqcas_message_question_text"));
        $modal = $this->factory->modal()->lightbox($page);

        $button = $this->factory->button()->standard($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_show_question_text_action_text"), '')
            ->withOnClick($modal->getShowSignal());

        //Return the UI component
        $active_variant_identifier = $this->data["active_variant_identifier"];
        $active_variant_question_note = $this->data["active_variant_question_note"];
        $active_variant_question_variables = $this->data["active_variant_question_variables"];
        $active_variant_feedback_variables = $this->data["active_variant_feedback_variables"];

        return $this->factory->panel()->sub(
            $active_variant_identifier,
            $this->factory->legacy(
                assStackQuestionUtils::_getLatex($active_variant_question_note) .
                $this->renderer->render($this->factory->divider()->horizontal()) .
                $this->renderer->render([$button, $modal])
            )
        )
            ->withCard($this->factory->card()->standard(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_question_and_feedback_variables_text")
            )
                ->withSections(array(
                    $this->factory->legacy($active_variant_question_variables),
                    $this->factory->divider()->horizontal(),
                    $this->factory->legacy($active_variant_feedback_variables),
                )))
            ->withActions($current_active_variant_panel_actions);
    }

    /**
     * Returns the UI subcomponent for the currently deployed variants panel
     * which is a Sub section of a panel
     * @return array
     * @throws ilCtrlException
     */
    private function getCurrentlyDeployedVariantsPanelUIComponent(): array
    {


        $array_of_deployed_variants = [];

        //Fill the data for each deployed variant
        foreach ($this->data["deployed_variants"] as $deployed_variant_identifier => $deployed_variant_data) {

            //control parameters
            $this->control->setParameterByClass(
                'assStackQuestionGUI',
                'set_active_variant_identifier',
                $deployed_variant_identifier
            );

            //Actions for each deployed variant
            $deployed_variant_individual_actions = $this->factory->dropdown()->standard(array(
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_delete_deployed_variant_action_text") . ": " . (string)$deployed_variant_identifier,
                    $this->control->getLinkTargetByClass("assstackquestiongui", "deleteDeployedSeed")),
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_set_as_active_variant_action_text") . ": " . (string)$deployed_variant_identifier,
                    $this->control->getLinkTargetByClass("assstackquestiongui", "setAsActiveVariant"))));


            $path = './src/UI/examples/Symbol/Icon/Custom/my_custom_icon.svg';
            $ico = $this->factory->symbol()->icon()->custom($path, 'Example');

            if ((string)$deployed_variant_identifier != $this->data["active_variant_identifier"]) {
                $link = $this->factory->legacy('');
                $divider = $this->factory->legacy('');
            } else {
                $link = $this->factory->legacy(
                    $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_is_current_active_variant_text"));
                $divider = $this->factory->divider()->vertical();
            }
            $question_note = $deployed_variant_data["question_note"];
            $array_of_deployed_variants[] = $this->factory->panel()->sub(
                (string)$deployed_variant_identifier .
                $this->renderer->render($divider) .
                $this->renderer->render($link),
                $this->factory->legacy(
                    $this->renderer->render($ico) .
                    $this->renderer->render($this->factory->divider()->vertical()) .
                    assStackQuestionUtils::_getLatex($question_note->get_rendered())))
                ->withActions($deployed_variant_individual_actions);
        }

        //Return the UI component
        return $array_of_deployed_variants;
    }

    public function getUnitTestStatusPanelUIComponent()
    {
        if (isset($this->data["unit_tests"]["test_cases"])) {
            $unit_tests = $this->data["unit_tests"]["test_cases"];
        } else {
            $unit_tests = [];
        }

        $list = [];
        foreach ($unit_tests as $unit_test_number => $unit_test) {
            $actions = $this->factory->dropdown()->standard(array(
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_unit_test_action_text"), ""),
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_edit_unit_test_action_text"),
                    $this->control->getLinkTargetByClass("assstackquestiongui", "editTestcases")),
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_delete_unit_test_action_text"), ""),
            ));

            $list[$unit_test_number] = $this->factory->item()->group((string)$unit_test_number, array(
                $this->factory->legacy("DESCRIPTION: Loren ipsum..."),
                $this->factory->legacy("LAST RUN: 24.11.2023"),
                $this->factory->legacy("PASSED"),
            ))->withActions($actions);
        }

        $std_list = $this->factory->panel()->listing()->standard($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_status_panel_title"), array(
            $this->factory->item()->group("ALL PASSED", $list)
        ));

        return $std_list;
    }

}