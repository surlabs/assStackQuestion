<?php
declare(strict_types=1);

namespace classes\ui\author;

use assStackQuestion;
use assStackQuestionDB;
use assStackQuestionUtils;
use classes\platform\ilias\StackRenderIlias;
use classes\platform\StackException;
use classes\platform\StackUnitTest;
use ilCtrl;
use ilCtrlException;
use ILIAS\UI\Component\Panel\Listing\Listing;
use ILIAS\UI\Component\Panel\Sub;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilSetting;
use stack_exception;

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

    private ilLanguage $language;

    private ilCtrl $control;

    private array $data;

    public function __construct(array $data)
    {
        global $DIC;

        $this->data = [];

        foreach ($data as $key => $value) {

            //deployed seeds
            if ($key === "deployed_seeds") {
                $this->data["deployed_variants"] = [];
                foreach ($value as $id => $deployed_seed) {
                    $active_seed = assStackQuestionDB::_readActiveSeed($deployed_seed["question_id"]);
                    if ((int)$active_seed === (int)$deployed_seed["seed"]) {
                        $this->data["active_variant_identifier"] = (string)$deployed_seed["seed"] ?? '1';
                        $this->data["active_variant_question_note"] = (string)$deployed_seed["note"]->get_rendered();
                        $this->data["active_variant_question_text"] = (string)$deployed_seed["question_text"]->get_rendered();
                        $this->data["active_variant_question_variables"] = (string)$deployed_seed["question_variables"];
                        $this->data["active_variant_feedback_variables"] = (string)$deployed_seed["feedback_variables"];
                    }

                    if ($id === "") {
                        //non deployed seed
                    } else {
                        $this->data["deployed_variants"][$deployed_seed["seed"]] = [
                            "question_note" => $deployed_seed["note"],
                            "question_variables" => $deployed_seed["question_id"],
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

            if ($key === "question") {
                $this->data["question"] = $value;
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

    /**
     * @return string
     * @throws StackException
     * @throws ilCtrlException
     * @throws stack_exception
     */
    public function show(): string
    {
        $html = "";
        $uses_randomisation = false;

        //Instantiate Question if not.
        if (!$this->data["question"]->isInstantiated()) {
            if (isset($this->data["active_variant_identifier"])) {
                $this->data["question"]->questionInitialisation((int)$this->data["active_variant_identifier"], true);
            } else {
                $this->data["question"]->questionInitialisation(1, true);
            }
        }

        if (assStackQuestionUtils::_hasRandomVariables($this->data["question"]->question_variables)) {
            $uses_randomisation = true;
            if (empty($this->data["deployed_variants"])) {
                //No deployed variants
                $generate_variants_button = $this->renderer->render(
                    $this->factory->button()->standard(
                        $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_no_variants_generate_new_variants_action_text"),
                        $this->control->getLinkTargetByClass("assstackquestiongui", "generateNewVariants"))
                );

                $html .= $this->renderer->render($this->factory->messageBox()->info(
                    $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_no_deployed_variants_message_but_has_randomisation") .
                    $this->renderer->render($this->factory->divider()->vertical()) .
                    $generate_variants_button
                ));
            }
        } else {
            //No randomisation in the question
            $html .= $this->renderer->render($this->factory->messageBox()->info(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_no_randomisation_message")
            ));
        }

        //Active variants panel
        $active_variants_panel = $this->factory->panel()->standard(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_active_variant_panel_title"),
            array(
                $this->getCurrentActiveVariantPanelUIComponent($uses_randomisation)
            )
        );
        $html .= $this->renderer->render($active_variants_panel);

        //Deployed variants panel
        $num_of_deployed_variants = count($this->data["deployed_variants"]);

        $deployed_variants_panel = $this->factory->panel()->standard(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_deployed_variants_panel_title") .
            $this->renderer->render($this->factory->divider()->vertical()) .
            (string)$num_of_deployed_variants,
            $this->getCurrentlyDeployedVariantsPanelUIComponent()
        );
        $html .= $this->renderer->render($deployed_variants_panel);

        if (empty($this->data["unit_tests"])) {
            //Add standard test button
            $add_standard_test_button = $this->factory->button()->standard(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_add_standard_test_button_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "addStandardTest"));
            $add_standard_test_message_box = $this->factory->messageBox()->info(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_add_standard_test_description")
                . $this->renderer->render($this->factory->divider()->vertical()) .
                $this->renderer->render($add_standard_test_button)
            );
            $html .= $this->renderer->render($add_standard_test_message_box);
        }

        $panel = $this->getUnitTestStatusPanelUIComponent($count_passed, $unit_tests);
        $total_unit_tests = !empty($this->data['unit_tests']) ? count($this->data['unit_tests']["test_cases"]) : 0;

        if ($total_unit_tests < $count_passed) {
            $total_unit_tests = $count_passed;
        }

        //Test overview panel
        $test_overview_panel = $this->factory->panel()->standard(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_status_panel_title") .
            $this->renderer->render($this->factory->divider()->vertical()) .
            $total_unit_tests .
            $this->renderer->render($this->factory->divider()->vertical()) .
            '(' . $count_passed . '/' . $total_unit_tests . ') ' .
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_passed"),
            $panel
        );
        $html .= $this->renderer->render($test_overview_panel);

        return $html;
    }

    /**
     * Returns the UI subcomponent for the currently active variant panel
     * which is a Sub section of a panel
     * @param bool $uses_randomisation
     * @return \ILIAS\UI\Component\Panel\Panel
     * @throws StackException
     * @throws stack_exception
     */
    private function getCurrentActiveVariantPanelUIComponent(bool $uses_randomisation): \ILIAS\UI\Component\Panel\Panel
    {
        $this->control->setParameterByClass(
            'assStackQuestionGUI',
            'active_variant_identifier',
            $this->data["active_variant_identifier"] ?? '1'
        );

        $array_of_actions = [];

        if ($uses_randomisation) {
            $array_of_actions[] = $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_generate_new_variants_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "generateNewVariants"));
            $array_of_actions[] = $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_all_tests_for_active_variant_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "runAllTestsForActiveVariant"));
            $array_of_actions[] = $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_all_tests_for_all_variants_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "runAllTestsForAllVariants"));
        } else {
            $array_of_actions[] = $this->factory->button()->shy(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_all_tests_for_this_question_action_text"),
                $this->control->getLinkTargetByClass("assstackquestiongui", "runAllTestsForActiveVariant"));
        }

        $array_of_actions[] = $this->factory->button()->shy(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_add_unit_test_action_text"),
            $this->control->getLinkTargetByClass("assstackquestiongui", "addCustomTestForm"));
        $array_of_actions[] = $this->factory->button()->shy(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_add_standard_test_button_text"),
            $this->control->getLinkTargetByClass("assstackquestiongui", "addStandardTest"));

        //Actions for the currently active variant
        $current_active_variant_panel_actions = $this->factory->dropdown()->standard($array_of_actions);

        $attempt_data = [];

        $attempt_data['response'] = [];
        $attempt_data['question'] = $this->data["question"];

        $display_options = [];
        $display_options['readonly'] = true;
        $display_options['feedback'] = true;

        //Render question text
        $question_text = "<div class='ilc_question_Standard'>" .
            StackRenderIlias::renderQuestion($attempt_data, $display_options)
            . "</div>";

        $page_text = $this->factory->modal()->lightboxTextPage(assStackQuestionUtils::_getLatex($question_text), $this->language->txt("qpl_qst_xqcas_message_question_text"));
        $modal_text = $this->factory->modal()->lightbox($page_text);

        $button_text = $this->factory->button()->standard($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_show_question_text_action_text"), '')
            ->withOnClick($modal_text->getShowSignal());

        //Render general feedback
        $general_feedback = StackRenderIlias::renderGeneralFeedback($attempt_data, $display_options);

        $page_general_feedback = $this->factory->modal()->lightboxTextPage(assStackQuestionUtils::_getLatex($general_feedback), $this->language->txt("qpl_qst_xqcas_message_general_feedback"));
        $modal_general_feedback = $this->factory->modal()->lightbox($page_general_feedback);

        $button_general_feedback = $this->factory->button()->standard($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_show_general_feedback_action_text"), '')
            ->withOnClick($modal_general_feedback->getShowSignal());

        //Return the UI component
        $active_variant_identifier = $this->data["active_variant_identifier"] ?? "";
        $active_variant_question_note = $this->data["active_variant_question_note"] ?? "";
        $active_variant_question_variables = $this->data["active_variant_question_variables"] ?? "";
        $active_variant_feedback_variables = $this->data["active_variant_feedback_variables"] ?? "";

        return $this->factory->panel()->sub(
            $active_variant_identifier .
            $this->renderer->render($this->factory->divider()->vertical()) .
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_is_current_active_variant_text"),
            $this->factory->legacy(
                assStackQuestionUtils::_getLatex($active_variant_question_note) .
                $this->renderer->render($this->factory->divider()->horizontal()) .
                $this->renderer->render([$button_text, $modal_text]) .
                $this->renderer->render($this->factory->divider()->vertical()) .
                $this->renderer->render([$button_general_feedback, $modal_general_feedback])
            )
        )->withFurtherInformation($this->factory->card()->standard(
            $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_question_variables_text")
        )->withSections(array(
            $this->factory->legacy(assStackQuestionUtils::parseToHTMLWithoutLatex($active_variant_question_variables)),
            $this->factory->divider()->horizontal(),
            $this->factory->legacy(assStackQuestionUtils::parseToHTMLWithoutLatex($active_variant_feedback_variables))
        )))->withActions($current_active_variant_panel_actions);
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
                'variant_identifier',
                $deployed_variant_identifier
            );
            $this->control->setParameterByClass(
                'assStackQuestionGUI',
                'active_variant',
                $this->data["active_variant_identifier"] ?? '1'
            );

            //Actions for each deployed variant
            $deployed_variant_individual_actions = $this->factory->dropdown()->standard(array(
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_delete_deployed_variant_action_text") . ": " . (string)$deployed_variant_identifier,
                    $this->control->getLinkTargetByClass("assstackquestiongui", "deleteDeployedSeed"))));

            //$path = './src/UI/examples/Symbol/Icon/Custom/my_custom_icon.svg';
            //$ico = $this->factory->symbol()->icon()->custom($path, 'Example');

            if(!isset($this->data["active_variant_identifier"])){
                $this->data["active_variant_identifier"] = 1;
            }
            if ((string)$deployed_variant_identifier != $this->data["active_variant_identifier"]) {
                $link = $this->factory->button()->standard($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_set_as_active_variant_action_text"),
                    $this->control->getLinkTargetByClass("assstackquestiongui", "setAsActiveVariant"));
            } else {
                $link = $this->factory->legacy(
                    $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_is_current_active_variant_text"));
            }

            $question_note = $deployed_variant_data["question_note"];
            $array_of_deployed_variants[] = $this->factory->panel()->sub(
                (string)$deployed_variant_identifier .
                $this->renderer->render($this->factory->divider()->vertical()) .
                $this->renderer->render($link),
                $this->factory->legacy(
                    assStackQuestionUtils::_getLatex($question_note->get_rendered()) .
                    $this->renderer->render($this->factory->divider()->horizontal())))
                ->withActions($deployed_variant_individual_actions);
        }

        //Return the UI component
        return $array_of_deployed_variants;
    }

    public function getUnitTestStatusPanelUIComponent(&$count_passed, &$status_text): Listing
    {
        $count_passed = 0;

        if (isset($this->data["unit_tests"]["test_cases"])) {
            $unit_tests = $this->data["unit_tests"]["test_cases"];
        } else {
            $unit_tests = [];
        }

        $list = [];

        foreach ($unit_tests as $unit_test_number => $unit_test) {
            $this->control->setParameterByClass(
                'assStackQuestionGUI',
                'test_case',
                $unit_test_number
            );

            $actions = $this->factory->dropdown()->standard(array(
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_unit_test_action_text"),
                    $this->control->getLinkTargetByClass("assstackquestiongui", "runUnitTest")),
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_run_unit_test_for_all_variants_action_text"),
                    $this->control->getLinkTargetByClass("assstackquestiongui", "runUnitTestForAllVariants")),
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_edit_unit_test_action_text"),
                    $this->control->getLinkTargetByClass("assstackquestiongui", "editTestcases")),
                $this->factory->button()->shy($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_delete_unit_test_action_text"),
                    $this->control->getLinkTargetByClass("assstackquestiongui", "deleteUnitTest")),
            ));

            $status_text = "<span style='font-weight: bold; color: orange;'>" . $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_not_run") . "</span>";
            $test_results_view = $this->factory->legacy("");
            $last_case = end($unit_test["results"]);

            if ($last_case) {
                $last_result = json_decode($last_case["result"]);

                if ((int) $last_result->passed === 1) {
                    $count_passed++;

                    $status_text = "<span style='font-weight: bold; color: green;'>" . $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_passed") . "</span>";
                } else {
                    $status_text = "<span style='font-weight: bold; color: red;'>" . $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_failed") . "</span>";
                }

                $test_results_view = $this->factory->legacy($this->renderQtestResults((int) $last_case["seed"], (int) $last_case["timerun"], $last_result->prts));
            }

            $list[$unit_test_number] = $this->factory->item()->group($status_text .
                $this->renderer->render($this->factory->divider()->vertical()) .
                $unit_test_number .
                $this->renderer->render($this->factory->divider()->vertical()) .
                $unit_test["description"],
                array(
                    $test_results_view
                ))->withActions($actions);
        }

        return $this->factory->panel()->listing()->standard(
            '',
            array(
                $this->factory->item()->group("", $list)
            ));
    }

    /**
     * @param int $seed
     * @param int $timerun
     * @param object|null $result
     * @return string
     */
    public function renderQtestResults(int $seed, int $timerun, object $result = null): string
    {
        $html = "<div style='margin-left: 20px;'>";
        $html .= "<strong>Seed</strong>: " . $seed . "<br>";
        $html .= "<strong>" . $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_last_run") . "</strong>: " . date('d-m-Y H:i:s', $timerun) . "<br>";
        $html .= "</div>";

        $html .= "<div style='padding: 20px;'>";
        // Crear una tabla con los resultados
        $html .= "<table class='table'>";
        $html .= "<thead class='thead-dark'>";
        $html .= "<tr>";
        $html .= "<th>PRT Name</th>";
        $html .= "<th>Score</th>";
        $html .= "<th>Expected score</th>";
        $html .= "<th>Penalty</th>";
        $html .= "<th>Expected penalty</th>";
        $html .= "<th>Answer note</th>";
        $html .= "<th>Expected answer note</th>";
        $html .= "<th>Feedback</th>";
        $html .= "<th>Passed</th>";
        $html .= "</tr>";
        $html .= "</thead>";

        if (isset($result)) {
            $html .= "<tbody>";

            foreach ($result as $key => $prt) {
                $html .= "<tr class='alert alert-" . ((int) $prt->passed == 1 ? "success" : "danger") . "'>";
                $html .= "<td>" . $key . "</td>";
                $html .= "<td>" . $prt->score . "</td>";
                $html .= "<td>" . $prt->expectedscore . "</td>";
                $html .= "<td>" . $prt->penalty . "</td>";
                $html .= "<td>" . $prt->expectedpenalty . "</td>";
                $html .= "<td>"
                    . $prt->answernote .
                    "<div style='background-color: white; border-radius: 5px; font-size: 0.6rem'>" .
                    str_replace("\n", "<br>", $prt->trace) . "</div>" .
                    "</td>";
                $html .= "<td>" . $prt->expectedanswernote . "</td>";
                $html .= "<td>" . str_replace("\n", "<br>", assStackQuestionUtils::_getLatex($prt->feedback)) . "</td>";
                $html .= "<td>" . ($prt->passed ? "Yes" : "No") . "</td>";
                $html .= "</tr>";
            }

            $html .= "</tbody>";
        }

        $html .= "</table>";
        $html .= "</div>";

        return $html;
    }

    public function showCustomTestForm(array $inputs, array $prts, assStackQuestion $question): string
    {
        $sections = $this->initCustomTest("", $inputs, null, $prts);
        $form_action = $this->control->getLinkTargetByClass("assStackQuestionGUI", "addCustomTestForm");
        return $this->renderCustomTest($form_action, $sections, $question);
    }

    public function initCustomTest(string $description = "", array $inputs = null, array $expected = null, array $prts = null): array
    {

        try {
            $this->control->setParameterByClass('assStackQuestionGUI', 'cmd', 'addCustomTestForm');

            //GENERAL SECTION
            $descInput = $this->factory->input()->field()->text(
                $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_addform_description")
                , ''
            )->withValue($description);

            $formFields = [
                'description' => $descInput
            ];

            $sectionGeneral = $this->factory->input()->field()->section($formFields, $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_addform_section_general"), "");
            $sections["general"] = $sectionGeneral;

            //ENTRIES SECTION
            $formFields = [];

            foreach ($inputs as $key => $input) {
                $ans = $this->factory->input()->field()->text($key, '');
                if ($expected) {
                    $ans = $ans->withValue($input["value"]);
                }
                $formFields[$key] = $ans;

            }

            $sectionEntries = $this->factory->input()->field()->section($formFields, $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_addform_section_entries"), "");
            $sections["inputs"] = $sectionEntries;

            //EXPECTED RESULT SECTION
            $formFields = [];

            foreach ($prts as $key => $prt) {
                $rating = $this->factory->input()->field()->text($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_addform_rating"), '');
                $penalization = $this->factory->input()->field()->text($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_addform_penalization"), '');

                $options = [];
                $options["NULL"] = "NULL";

                $sans = [];

                foreach ($prt->get_nodes() as $node) {
                    $options[trim($node->trueanswernote)] = trim($node->trueanswernote);
                    $options[trim($node->falseanswernote)] = trim($node->falseanswernote);

                    if (!in_array($node->sans, $sans)) {
                        $sans[] = $node->sans;
                    }

                }

                $responseNote = $this->factory->input()->field()->select($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_addform_response_note"), $options);

                if (isset($expected) && array_key_exists($key, $expected)) {
                    $rating = $rating->withValue($expected[$key]["score"]);
                    $penalization = $penalization->withValue($expected[$key]["penalty"]);
                    $responseNote = $responseNote->withValue($expected[$key]["answer_note"]);
                }

                $formFields['score'] = $rating;
                $formFields['penalty'] = $penalization;
                $formFields['answer_note'] = $responseNote;

                $sectionExpectedResult = $this->factory->input()->field()->section($formFields, $this->language->txt("qpl_qst_xqcas_ui_author_randomisation_unit_test_addform_section_expected_result") . " " . $key . ": [" . implode(",", $sans) . "]", "");

                $sections["result_" . $key] = $sectionExpectedResult;

            }

        } catch (StackException $e) {
            $section = $this->factory->messageBox()->failure($e->getMessage());
            $sections["object"] = $section;
        }

        return $sections;

    }

    public function renderCustomTest(string $form_action, array $sections, assStackQuestion $question): string
    {
        global $DIC;

        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        $saving_info = "";

        $request = $DIC->http()->request();

        //Check if the form has been submitted
        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $result = $form->getData();
            if ($result) {
                $saving_info = $this->saveUnitTest($_GET["test_case"], $result, $question);
            }
        }

        return $saving_info . $this->renderer->render($form);

    }

    public function showEditCustomTestForm(array $unit_tests, array $prts, assStackQuestion $question): string
    {
        $sections = $this->initCustomTest($unit_tests["description"], $unit_tests["inputs"], $unit_tests["expected"], $prts);
        $this->control->setParameterByClass(
            'assStackQuestionGUI',
            'test_case',
            $_GET["test_case"]
        );

        $form_action = $this->control->getLinkTargetByClass("assStackQuestionGUI", "editTestcases");
        return $this->renderCustomTest($form_action, $sections, $question);
    }

    /**
     * @param string|null $test_case
     * @param array $unit_test
     * @param assStackQuestion $question
     * @return string
     */
    private function saveUnitTest(?string $test_case, array $unit_test, assStackQuestion $question): string
    {
        // Parse the unit_test to the correct format
        $unit_test["description"] = $unit_test["general"]["description"];
        unset($unit_test["general"]);

        foreach ($unit_test["inputs"] as $key => $value) {
            $unit_test["inputs"][$key] = [
                "value" => $value
            ];
        }

        foreach ($unit_test as $key => $value) {
            if (strpos($key, "result_") !== false) {
                unset($unit_test[$key]);
                $key = str_replace("result_", "", $key);
                $unit_test["expected"][$key] = $value;
            }
        }

        $unit_test["time_modified"] = time();

        if (StackUnitTest::saveTestCase($test_case, $unit_test, $question)) {
            return $this->renderer->render($this->factory->messageBox()->success('Saved'));
        } else {
            return $this->renderer->render($this->factory->messageBox()->failure('Error saving'));
        }
    }
}