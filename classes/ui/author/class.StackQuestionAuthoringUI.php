<?php

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

declare(strict_types=1);

namespace classes\ui\author;

use assStackQuestion;
use assStackQuestionUtils;
use classes\platform\StackConfig;
use Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component\CustomFactory;
use Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component\Input\Field\ExpandableSection;
use ilassStackQuestionPlugin;
use ilCtrlException;
use ilCtrlInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use stack_abstract_graph_svg_renderer;
use stack_ans_test_controller;
use stack_exception;
use stack_input;
use stack_input_factory;
use stack_options;
use stack_potentialresponse_tree_lite;
use stack_utils;
use stdClass;

/**
 * StackQuestionAuthoringUI
 *
 * @authors Jesús Copado Mejías, Saúl Díaz Díaz <stack@surlabs.es>
 */
class StackQuestionAuthoringUI
{
    private ilassStackQuestionPlugin $plugin;
    private assStackQuestion $question;
    private ilCtrlInterface $ctrl;
    private Factory $factory;
    private CustomFactory $customFactory;
    private Renderer $renderer;
    private ilLanguage $lng;
    private $request;

    public function __construct(ilassStackQuestionPlugin $plugin, assStackQuestion $question)
    {
        global $DIC;

        $DIC->globalScreen()->layout()->meta()->addCss('Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/templates/css/stack_graph.css');

        $this->plugin = $plugin;
        $this->question = $question;

        $this->ctrl = $DIC->ctrl();
        $this->factory = $DIC->ui()->factory();
        $this->customFactory = new CustomFactory();
        $this->renderer = $DIC->ui()->renderer();
        $this->lng = $DIC->language();
        $this->request = $DIC->http()->request();
    }

    /**
     * @throws ilCtrlException
     * @throws stack_exception
     */
    private function buildForm(): StandardForm
    {
        $sections = [
            "basic" => $this->factory->input()->field()->section($this->buildBasicSection(), $this->plugin->txt("edit_cas_question")),
            "options" => $this->factory->input()->field()->section($this->buildOptionsSection(), $this->plugin->txt("options")),
            "inputs" => $this->factory->input()->field()->section($this->buildInputsSection(), $this->plugin->txt("inputs")),
            "prt" => $this->customFactory->tabSection($this->buildPrtSection(), $this->plugin->txt("prts"))
        ];

        return $this->factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTargetByClass("assStackQuestionGUI", "editQuestion"),
            $sections
        );
    }

    /**
     * @throws ilCtrlException|stack_exception
     */
    public function showAuthoringPanel(): string
    {
        $form = $this->buildForm();

        $saving_info = "";

        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();

            if($result) {
                $saving_info = $this->save($result) ?? "";
            }
        }

        return $saving_info . $this->renderer->render($form);
    }

    /**
     * @throws ilCtrlException
     * @throws stack_exception
     */
    public function writePostData(): ?string
    {
        $form = $this->buildForm()->withRequest($this->request);

        $result = $form->getData();

        if($result){
            return $this->save($result);
        }

        return null;
    }

    /**
     * @throws stack_exception
     */
    private function save(array $result): ?string
    {
        // Save basic section
        $basic = $result["basic"];

        $this->question->setTitle($basic["title"]);
        $this->question->setAuthor($basic["author"]);
        $this->question->setComment($basic["description"]);

        $this->question->setQuestion($basic["question"]);

        $this->question->question_variables = $basic["question_variables"];
        $this->question->question_note = $basic["question_note"];
        $this->question->specific_feedback = $basic["specific_feedback"];

        // Save options section
        $options = $result["options"][0];

        $this->question->options = new stack_options(array(
            "simplify" => $options["simplify"] ? 1 : 0,
            "assumepos" => $options["assumepos"] ? 1 : 0,
            "assumereal" => $options["assumereal"] ? 1 : 0,
            "multiplicationsign" => $options["multiplicationsign"],
            "sqrtsign" => $options["sqrtsign"] ? 1 : 0,
            "complexno" => $options["complexno"],
            "inversetrig" => $options["inversetrig"],
            "matrixparens" => $options["matrixparens"]
        ));

        $this->question->prt_correct = $options["prt_correct"];
        $this->question->prt_partially_correct = $options["prt_partially_correct"];
        $this->question->prt_incorrect = $options["prt_incorrect"];
        $this->question->general_feedback = $options["general_feedback"];

        // Save inputs section
        $inputs = array();

        $required_inputs_parameters = stack_input_factory::get_parameters_used();

        foreach ($result["inputs"] as $name => $input) {
            $parameters = array();

            foreach ($required_inputs_parameters[$input["type"]] as $parameter_name) {
                if ($parameter_name != 'inputType') {
                    $parameters[$parameter_name] = $input[$parameter_name];
                }
            }

            $inputs[$name] = stack_input_factory::make($input["type"], $name, $input["teacher_answer"], $this->question->options, $parameters);
        }

        $this->question->inputs = $inputs;

        $inputs_placeholders = stack_utils::extract_placeholders($this->question->getQuestion(), 'input');

        foreach ($inputs_placeholders as $placeholder) {
            if (!isset($this->question->inputs[$placeholder])) {
                $this->question->loadStandardInput($placeholder);
            }
        }

        // Save prt section
        $prts_array = array();

        foreach ($result["prt"] as $prt_name => $_prt) {
            $prt = $_prt[0]["prt"];

            $prt_data = new stdClass();

            $prt_data->name = $prt_name;
            $prt_data->value = $prt["settings"]["prt_value"];
            $prt_data->autosimplify = $prt["settings"]["simplify"];
            $prt_data->feedbackvariables = $prt["settings"]["feedback_variables"];
            $prt_data->firstnodename = $prt["first_node"];


            $prt_data->nodes = array();

            foreach ($prt["nodes"] as $node_name => $node) {
                $node_data = new stdClass();

                $node_data->nodename = $node_name;
                $node_data->description = "";
                $node_data->prtname = $prt_name;
                $node_data->answertest = $node["answer_test"];
                $node_data->sans = $node["student_answer"];
                $node_data->tans = $node["teacher_answer"];
                $node_data->testoptions = $node["options"];
                $node_data->quiet = $node["quiet"];

                $node_data->truescoremode = $node["feedback"]["positive"]["mode"];
                $node_data->truescore = $node["feedback"]["positive"]["score"];
                $node_data->truepenalty = $node["feedback"]["positive"]["penalty"];
                $node_data->truenextnode = $node["feedback"]["positive"]["next_node"];
                $node_data->trueanswernote = $node["feedback"]["positive"]["answernote"];
                $node_data->truefeedback = $node["feedback"]["positive"]["specific_feedback"];
                $node_data->truefeedbackformat = $node["feedback"]["positive"]["feedback_class"];

                $node_data->falsescoremode = $node["feedback"]["negative"]["mode"];
                $node_data->falsescore = $node["feedback"]["negative"]["score"];
                $node_data->falsepenalty = $node["feedback"]["negative"]["penalty"];
                $node_data->falsenextnode = $node["feedback"]["negative"]["next_node"];
                $node_data->falseanswernote = $node["feedback"]["negative"]["answernote"];
                $node_data->falsefeedback = $node["feedback"]["negative"]["specific_feedback"];
                $node_data->falsefeedbackformat = $node["feedback"]["negative"]["feedback_class"];

                $prt_data->nodes[$node_name] = $node_data;
            }

            $prts_array[$prt_name] = $prt_data;
        }

        $total_value = 0;
        $all_formative = true;

        foreach ($prts_array as $prt_data) {
            $total_value += (float) $prt_data->value;

            if ((float) $prt_data->value > 0) {
                $all_formative = false;
            }
        }

        if ($prts_array && !$all_formative && $total_value < 0.0000001) {
            return $this->renderer->render($this->factory->messageBox()->failure('There is an error authoring your question. The $totalvalue, the marks available for the question, must be positive'));
        }

        $prts = array();

        foreach ($prts_array as $name => $prt_data) {
            $prt_value = 0;
            if (!$all_formative) {
                $prt_value = (float) $prt_data->value / $total_value;
            }
            $prts[$name] = new stack_potentialresponse_tree_lite($prt_data, $prt_value);
        }

        $this->question->prts = $prts;

        $prts_placeholders = stack_utils::extract_placeholders($this->question->getQuestion() . $this->question->specific_feedback, 'feedback');

        foreach ($prts_placeholders as $placeholder) {
            if (!isset($this->question->prts[$placeholder])) {
                $this->question->loadStandardPrt($placeholder);
            }
        }

        $this->question->saveToDb();

        return $this->renderer->render($this->factory->messageBox()->success($this->lng->txt('msg_obj_modified')));
    }

    private function buildBasicSection(): array
    {
        $inputs = [];

        $inputs["title"] = $this->factory->input()->field()->text($this->lng->txt("title"))->withRequired(true)
            ->withValue(!empty($this->question->getTitle()) ? $this->question->getTitle() : $this->plugin->txt("untitled_question"));
        $inputs["author"] = $this->factory->input()->field()->text($this->lng->txt("author"))->withRequired(true)
            ->withValue($this->question->getAuthor());
        $inputs["description"] = $this->factory->input()->field()->text($this->lng->txt("description"))
            ->withValue($this->question->getComment());
        $inputs["question"] = $this->customFactory->textareaRTE($this->question->getId(), $this->lng->txt("question"), $this->plugin->txt("authoring_input_creation_info"))->withRequired(true)
            ->withValue($this->question->getQuestion());
        $inputs["points"] = $this->factory->input()->field()->numeric($this->plugin->txt("preview_points_message_p3"), $this->plugin->txt("authoring_points_info"))->withRequired(true)
            ->withValue(1)->withDisabled(true);
        $inputs["question_variables"] = $this->factory->input()->field()->textarea($this->plugin->txt("options_question_variables"), $this->plugin->txt("options_question_variables_info"))
            ->withValue($this->question->question_variables);
        $inputs["question_note"] = $this->factory->input()->field()->textarea($this->plugin->txt("options_question_note"), $this->plugin->txt("options_question_note_info"))
            ->withValue($this->question->question_note);
        $inputs["specific_feedback"] = $this->customFactory->textareaRTE($this->question->getId(), $this->plugin->txt("options_specific_feedback"), $this->plugin->txt("options_specific_feedback_info"))
            ->withValue($this->question->specific_feedback);

        return $inputs;
    }

    /**
     * @throws stack_exception
     */
    private function buildOptionsSection(): array
    {
        $inputs = [];

        $inputs["simplify"] = $this->factory->input()->field()->checkbox($this->plugin->txt("options_question_simplify"), $this->plugin->txt("options_question_simplify_info"))
            ->withValue((bool) $this->question->options->get_option("simplify"));
        $inputs["assumepos"] = $this->factory->input()->field()->checkbox($this->plugin->txt("options_assume_positive"), $this->plugin->txt("options_assume_positive_info"))
            ->withValue((bool) $this->question->options->get_option("assumepos"));
        $inputs["assumereal"] = $this->factory->input()->field()->checkbox($this->plugin->txt("options_assume_real"), $this->plugin->txt("options_assume_real_info"))
            ->withValue((bool) $this->question->options->get_option("assumereal"));
        $inputs["prt_correct"] = $this->customFactory->textareaRTE($this->question->getId(), $this->plugin->txt("options_prt_correct"))
            ->withValue($this->question->prt_correct);
        $inputs["prt_partially_correct"] = $this->customFactory->textareaRTE($this->question->getId(), $this->plugin->txt("options_prt_partially_correct"))
            ->withValue($this->question->prt_partially_correct);
        $inputs["prt_incorrect"] = $this->customFactory->textareaRTE($this->question->getId(), $this->plugin->txt("options_prt_incorrect"))
            ->withValue($this->question->prt_incorrect);
        $inputs["multiplicationsign"] = $this->factory->input()->field()->select($this->plugin->txt("options_multiplication_sign"), [
            "dot" => $this->plugin->txt('options_mult_sign_dot'),
            "cross" => $this->plugin->txt('options_mult_sign_cross'),
            "none" => $this->plugin->txt('options_mult_sign_none')
        ], $this->plugin->txt("options_multiplication_sign_info"))->withRequired(true)
            ->withValue($this->question->options->get_option("multiplicationsign"));
        $inputs["sqrtsign"] = $this->factory->input()->field()->checkbox($this->plugin->txt("options_sqrt_sign"), $this->plugin->txt("options_sqrt_sign_info"))
            ->withValue((bool) $this->question->options->get_option("sqrtsign"));
        $inputs["complexno"] = $this->factory->input()->field()->select($this->plugin->txt("options_complex_numbers"), [
            "i" => $this->plugin->txt('options_complex_numbers_i'),
            "j" => $this->plugin->txt('options_complex_numbers_j')
        ], $this->plugin->txt("options_complex_numbers_info"))->withRequired(true)
            ->withValue($this->question->options->get_option("complexno"));
        $inputs["inversetrig"] = $this->factory->input()->field()->select($this->plugin->txt("options_inverse_trigonometric"), [
            "cos-1" => $this->plugin->txt('options_inverse_trigonometric_cos'),
            "acos" => $this->plugin->txt('options_inverse_trigonometric_acos'),
            "arccos" => $this->plugin->txt('options_inverse_trigonometric_arccos')
        ], $this->plugin->txt("options_inverse_trigonometric_info"))->withRequired(true)
            ->withValue((bool) $this->question->options->get_option("inversetrig"));
        $inputs["matrixparens"] = $this->factory->input()->field()->select($this->plugin->txt("options_matrix_parens"), [
            "[" => "[",
            "(" => "(",
            "" => "",
            "{" => "{",
            "|" => "|"
        ], $this->plugin->txt("options_matrix_parens_info"))->withRequired(true)
            ->withValue((bool) $this->question->options->get_option("matrixparens"));
        $inputs["general_feedback"] = $this->customFactory->textareaRTE($this->question->getId(), $this->plugin->txt("options_how_to_solve"), $this->plugin->txt("options_how_to_solve_info"))
            ->withValue($this->question->general_feedback);


        return [$this->customFactory->expandableSection($inputs, $this->plugin->txt("show_options"))->withExpandedByDefault(true)];
    }

    /**
     * @throws stack_exception
     * @throws ilCtrlException
     */
    private function buildInputsSection(): array
    {
        $inputs = [];

        if (empty($this->question->inputs)) {
            $standard_input = StackConfig::getAll('inputs');

            $required_parameters = stack_input_factory::get_parameters_used();

            $all_parameters = array(
                'boxWidth' => $standard_input['input_box_size'],
                'strictSyntax' => $standard_input['input_strict_syntax'],
                'insertStars' => $standard_input['input_insert_stars'],
                'syntaxHint' => $standard_input['input_syntax_hint'],
                'syntaxAttribute' => $standard_input['input_syntax_attribute'],
                'forbidWords' => $standard_input['input_forbidden_words'],
                'allowWords' => $standard_input['input_allow_words'],
                'forbidFloats' => $standard_input['input_forbid_float'],
                'lowestTerms' => $standard_input['input_require_lowest_terms'],
                'sameType' => $standard_input['input_check_answer_type'],
                'mustVerify' => $standard_input['input_must_verify'],
                'showValidation' => $standard_input['input_show_validation'],
                'options' => $standard_input['input_extra_options'],
            );

            $parameters = array();

            foreach ($required_parameters[$standard_input['input_type']] as $parameter_name) {
                if ($parameter_name == 'inputType') {
                    continue;
                }
                $parameters[$parameter_name] = $all_parameters[$parameter_name];
            }

            $inputs["ans1"] = $this->buildInput("ans1", stack_input_factory::make('algebraic', 'ans1', 1, $this->question->options, $parameters), true);
        } else {
            $isFirst = true;
            foreach ($this->question->inputs as $name => $input) {
                $inputs[$name] = $this->buildInput($name, $input, $isFirst);
                $isFirst = false;
            }
        }

        return $inputs;
    }

    /**
     * @throws ilCtrlException
     */
    private function buildInput(string $name, stack_input $input, bool $isFirst): ExpandableSection
    {
        $inputs = [];

        $inputs["type"] = $this->factory->input()->field()->select($this->plugin->txt("input_type"), [
            "algebraic" => $this->plugin->txt('input_type_algebraic'),
            "boolean" => $this->plugin->txt('input_type_boolean'),
            "matrix" => $this->plugin->txt('input_type_matrix'),
            "varmatrix" => $this->plugin->txt('input_type_varmatrix'),
            "singlechar" => $this->plugin->txt('input_type_singlechar'),
            "textarea" => $this->plugin->txt('input_type_textarea'),
            "checkbox" => $this->plugin->txt('input_type_checkbox'),
            "dropdown" => $this->plugin->txt('input_type_dropdown'),
            "equiv" => $this->plugin->txt('input_type_equiv'),
            "notes" => $this->plugin->txt('input_type_notes'),
            "radio" => $this->plugin->txt('input_type_radio'),
            "units" => $this->plugin->txt('input_type_units'),
            "string" => $this->plugin->txt('input_type_string'),
            "numerical" => $this->plugin->txt('input_type_numerical')
        ], $this->plugin->txt("input_type_info"))->withRequired(true)
            ->withValue(assStackQuestionUtils::_getInputType($input));
        $inputs["teacher_answer"] = $this->factory->input()->field()->text($this->plugin->txt("input_model_answer"), $this->plugin->txt("input_model_answer_info"))->withRequired(true)
            ->withValue($input->get_teacher_answer());
        $inputs["boxWidth"] = $this->factory->input()->field()->numeric($this->plugin->txt("input_box_size"), $this->plugin->txt("input_box_size_info"))
            ->withValue($input->get_parameter('boxWidth'));
        $inputs["insertStars"] = $this->factory->input()->field()->select($this->plugin->txt("input_insert_stars"), [
            "0" => $this->plugin->txt('input_stars_no_stars'),
            "1" => $this->plugin->txt('input_stars_implied'),
            "2" => $this->plugin->txt('input_stars_singlechar'),
            "3" => $this->plugin->txt('input_stars_spaces'),
            "4" => $this->plugin->txt('input_stars_implied_spaces'),
            "5" => $this->plugin->txt('input_type_implied_spaces_single')
        ], $this->plugin->txt("input_insert_stars_info"))->withRequired(true)
            ->withValue($input->get_parameter('insertStars'));
        $inputs["syntaxHint"] = $this->factory->input()->field()->text($this->plugin->txt("input_syntax_hint"), $this->plugin->txt("input_syntax_hint_info"))
            ->withValue($input->get_parameter('syntaxHint'));
        $inputs["syntaxAttribute"] = $this->factory->input()->field()->select($this->plugin->txt("hint_mode"), [
            0 => $this->plugin->txt('value'),
            1 => $this->plugin->txt('placeholder')
        ])->withRequired(true)
            ->withValue($input->get_parameter('syntaxAttribute'));
        $inputs["forbidWords"] = $this->factory->input()->field()->text($this->plugin->txt("input_forbidden_words"), $this->plugin->txt("input_forbidden_words_info"))
            ->withValue($input->get_parameter('forbidWords'));
        $inputs["forbidFloats"] = $this->factory->input()->field()->checkbox($this->plugin->txt("input_forbid_float"), $this->plugin->txt("input_forbid_float_info"))
            ->withValue(boolval($input->get_parameter('forbidFloats')));
        $inputs["allowWords"] = $this->factory->input()->field()->text($this->plugin->txt("input_allow_words"), $this->plugin->txt("input_allow_words_info"))
            ->withValue($input->get_parameter('allowWords'));
        $inputs["lowestTerms"] = $this->factory->input()->field()->checkbox($this->plugin->txt("input_require_lowest_terms"), $this->plugin->txt("input_require_lowest_terms_info"))
            ->withValue(boolval($input->get_parameter('lowestTerms')));
        $inputs["sameType"] = $this->factory->input()->field()->checkbox($this->plugin->txt("input_check_answer_type"), $this->plugin->txt("input_check_answer_type_info"))
            ->withValue(boolval($input->get_parameter('sameType')));
        $inputs["mustVerify"] = $this->factory->input()->field()->checkbox($this->plugin->txt("input_must_verify"), $this->plugin->txt("input_must_verify_info"))
            ->withValue(boolval($input->get_parameter('mustVerify')));
        $inputs["showValidation"] = $this->factory->input()->field()->select($this->plugin->txt("input_show_validation"), [
            0 => $this->plugin->txt('show_validation_no'),
            1 => $this->plugin->txt('show_validation_yes_with_vars'),
            2 => $this->plugin->txt('show_validation_yes_without_vars')
        ], $this->plugin->txt("input_show_validation_info"))->withRequired(true)
            ->withValue($input->get_parameter('showValidation'));
        $inputs["options"] = $this->factory->input()->field()->text($this->plugin->txt("input_options"), $this->plugin->txt("input_options_info"))
            ->withValue($input->get_parameter('options'));

        $this->ctrl->setParameterByClass("assStackQuestionGUI", "input_name", $name);
        $inputs["actions"] = $this->customFactory->buttonSection([
            $this->factory->button()->standard($this->plugin->txt("input_delete"), ""),
        ], $this->plugin->txt("actions"));
        $this->ctrl->clearParameterByClass("assStackQuestionGUI", "input_name");


        return $this->customFactory->expandableSection($inputs, $name)->withExpandedByDefault($isFirst);
    }

    /**
     * @throws stack_exception|ilCtrlException
     */
    private function buildPrtSection(): array
    {
        $prts = [];

        if (!empty($this->question->prts)) {
            foreach ($this->question->prts as $prt_name => $prt) {
                $prts[$prt_name] = [$this->customFactory->columnSection([
                    "graph" => [
                        "graph" => $this->customFactory->legacy(stack_abstract_graph_svg_renderer::render($prt->get_prt_graph(), $prt->get_name() . 'graphsvg')),
                    ],
                    "prt" => $this->buildPrt($prt)
                ], $prt_name)
                ->withColumnStyles([
                    "graph" => [
                        "flex" => "0",
                        "border" => "0px solid #000",
                        "text-align" => "center",
                        "padding-left" => "50px",
                        "padding-right" => "50px"
                    ]
                ])];
            }
        }

        return $prts;
    }

    /**
     * @throws ilCtrlException
     */
    private function buildPrt(stack_potentialresponse_tree_lite $prt): array
    {
        $inputs = [];

        $inputs["prt_name"] = $this->factory->input()->field()->text($this->plugin->txt("prt_name"), $this->plugin->txt("prt_name_info"))->withRequired(true)
            ->withValue($prt->get_name());
        $node_list = [];
        foreach ($prt->get_nodes_summary() as $node_name => $prt_node) {
            $node_list[$node_name] = $node_name;
        }
        $inputs["first_node"] = $this->factory->input()->field()->select($this->plugin->txt("prt_first_node"), $node_list)->withRequired(true)
            ->withValue($prt->get_first_node());
        $inputs["settings"] = $this->customFactory->expandableSection($this->buildPrtOptions($prt), $this->plugin->txt("prt_settings_and_nodes"))->withExpandedByDefault(true);
        $inputs["nodes"] = $this->customFactory->tabSection($this->buildNodeSection($prt), $this->plugin->txt("prt_nodes"));

        return $inputs;
    }

    /**
     * @throws ilCtrlException
     */
    private function buildPrtOptions(stack_potentialresponse_tree_lite $prt): array
    {
        $inputs = [];

        $inputs["prt_value"] = $this->factory->input()->field()->text($this->plugin->txt("prt_value"), $this->plugin->txt("prt_value_info"))->withRequired(true)
            ->withValue((string) $prt->get_value());
        $inputs["simplify"] = $this->factory->input()->field()->checkbox($this->plugin->txt("prt_simplify"), $this->plugin->txt("prt_simplify_info"))
            ->withValue($prt->isSimplify());
        $inputs["feedback_variables"] = $this->factory->input()->field()->textarea($this->plugin->txt("prt_feedback_variables"), $this->plugin->txt("prt_feedback_variables_info"))
            ->withValue($prt->get_feedbackvariables_keyvals());

        $this->ctrl->setParameterByClass("assStackQuestionGUI", "prt_name", $prt->get_name());
        $inputs["actions"] = $this->customFactory->buttonSection([
            $this->factory->button()->standard($this->plugin->txt("delete_prt"), ""),
            $this->factory->button()->standard($this->plugin->txt("copy_prt"), "")
        ], $this->plugin->txt("actions"));
        $this->ctrl->clearParameterByClass("assStackQuestionGUI", "prt_name");

        return $inputs;
    }

    /**
     * @throws ilCtrlException
     */
    private function buildNodeSection(stack_potentialresponse_tree_lite $prt): array
    {
        $nodes = [];

        if (!empty($prt->get_nodes())) {
            foreach ($prt->get_nodes() as $node_name => $node) {
                $nodes[$node_name] = $this->buildNode($prt, $node);
            }
        }

        return $nodes;
    }

    /**
     * @throws ilCtrlException
     */
    private function buildNode(stack_potentialresponse_tree_lite $prt, object $node): array
    {
        $inputs = [];

        $answer_tests = stack_ans_test_controller::get_available_ans_tests();

        $answer_test_choices = array_map(function ($string) {
            return stack_string($string);
        }, $answer_tests);

        $inputs["answer_test"] = $this->factory->input()->field()->select($this->plugin->txt("prt_node_answer_test"), $answer_test_choices, $this->plugin->txt("prt_node_answer_test_info"))->withRequired(true)
            ->withValue($node->answertest);
        $inputs["student_answer"] = $this->factory->input()->field()->text($this->plugin->txt("prt_node_student_answer"), $this->plugin->txt("prt_node_student_answer_info"))->withRequired(true)
            ->withValue($node->sans);
        $inputs["teacher_answer"] = $this->factory->input()->field()->text($this->plugin->txt("prt_node_teacher_answer"), $this->plugin->txt("prt_node_teacher_answer_info"))->withRequired(true)
            ->withValue($node->tans);
        $inputs["options"] = $this->factory->input()->field()->text($this->plugin->txt("prt_node_options"), $this->plugin->txt("prt_node_options_info"))
            ->withValue($node->testoptions);
        $inputs["quiet"] = $this->factory->input()->field()->select($this->plugin->txt("prt_node_quiet"), [
            0 => $this->lng->txt('no'),
            1 => $this->lng->txt('yes')
        ], $this->plugin->txt("prt_node_quiet_info"))->withRequired(true)
            ->withValue($node->quiet);

        $this->ctrl->setParameterByClass("assStackQuestionGUI", "prt_name", $prt->get_name());
        $this->ctrl->setParameterByClass("assStackQuestionGUI", "node_name", $node->nodename);
        $inputs["actions"] = $this->customFactory->buttonSection([
            $this->factory->button()->standard($this->plugin->txt("delete_node"), ""),
            $this->factory->button()->standard($this->plugin->txt("copy_node"), "")
        ], $this->plugin->txt("actions"));
        $this->ctrl->clearParameterByClass("assStackQuestionGUI", "prt_name");
        $this->ctrl->clearParameterByClass("assStackQuestionGUI", "node_name");

        $inputs["feedback"] = $this->customFactory->columnSection([
                "positive" => $this->buildPositivePart($prt, $node),
                "negative" => $this->buildNegativePart($prt, $node)
            ], $this->plugin->txt("prt_node_feedback"))
            ->withColumnStyles([
                "positive" => [
                    "background" => "linear-gradient(45deg, #e2fff1, #a3ffd0);"
                ],
                "negative" => [
                    "background" => "linear-gradient(45deg, #ffe2e3, #ffa3a3);"
                ]
            ]);

        return $inputs;
    }

    private function buildPositivePart(stack_potentialresponse_tree_lite $prt, object $node): array
    {
        $inputs = [];

        $inputs["mode"] = $this->factory->input()->field()->select($this->plugin->txt("prt_node_pos_mod"), [
            "=" => "=",
            "+" => "+",
            "-" => "-"
        ], $this->plugin->txt("prt_node_pos_mod_info"))->withRequired(true)
            ->withValue($node->truescoremode);
        $inputs["score"] = $this->factory->input()->field()->numeric($this->plugin->txt("prt_node_pos_score"), $this->plugin->txt("prt_node_pos_score_info"))->withRequired(true)
            ->withValue(floatval($node->truescore));
        $inputs["penalty"] = $this->factory->input()->field()->numeric($this->plugin->txt("prt_node_pos_penalty"), $this->plugin->txt("prt_node_pos_penalty_info"))->withRequired(true)
            ->withValue(floatval($node->truepenalty));
        $node_list = [
            -1 => $this->plugin->txt('end')
        ];
        foreach ($prt->get_nodes_summary() as $node_name => $prt_node) {
            if ($node_name != $node->nodename) {
                $node_list[$node_name] = $node_name;
            }
        }
        $inputs["next_node"] = $this->factory->input()->field()->select($this->plugin->txt("prt_node_pos_next"), $node_list, $this->plugin->txt("prt_node_pos_next_info"))->withRequired(true)
            ->withValue($node->truenextnode);
        $inputs["answernote"] = $this->factory->input()->field()->text($this->plugin->txt("prt_node_pos_answernote"), $this->plugin->txt("prt_node_pos_answernote_info"))->withRequired(true)
            ->withValue($node->trueanswernote);
        $inputs["specific_feedback"] = $this->customFactory->textareaRTE($this->question->getId(), $this->plugin->txt("prt_node_pos_specific_feedback"), $this->plugin->txt("prt_node_pos_specific_feedback_info"))
            ->withValue($node->truefeedback);
        $inputs["feedback_class"] = $this->factory->input()->field()->select($this->plugin->txt('prt_node_pos_feedback_class'), [
            $this->lng->txt("default"),
            $this->plugin->txt("feedback_node_right"),
            $this->plugin->txt("feedback_node_wrong"),
            $this->plugin->txt("feedback_solution_hint"),
            $this->plugin->txt("feedback_extra_info"),
            $this->plugin->txt("feedback_plot_feedback"),
        ], $this->plugin->txt('prt_node_pos_feedback_class_info'))->withRequired(true)
            ->withValue($node->truefeedbackformat);

        return $inputs;
    }

    private function buildNegativePart(stack_potentialresponse_tree_lite $prt, object $node): array
    {
        $inputs = [];

        $inputs["mode"] = $this->factory->input()->field()->select($this->plugin->txt("prt_node_neg_mod"), [
            "=" => "=",
            "+" => "+",
            "-" => "-"
        ], $this->plugin->txt("prt_node_neg_mod_info"))->withRequired(true)
            ->withValue($node->falsescoremode);
        $inputs["score"] = $this->factory->input()->field()->numeric($this->plugin->txt("prt_node_neg_score"), $this->plugin->txt("prt_node_neg_score_info"))->withRequired(true)
            ->withValue(floatval($node->falsescore));
        $inputs["penalty"] = $this->factory->input()->field()->numeric($this->plugin->txt("prt_node_neg_penalty"), $this->plugin->txt("prt_node_neg_penalty_info"))->withRequired(true)
            ->withValue(floatval($node->falsepenalty));
        $node_list = [
            -1 => $this->plugin->txt('end')
        ];
        foreach ($prt->get_nodes_summary() as $node_name => $prt_node) {
            if ($node_name != $node->nodename) {
                $node_list[$node_name] = $node_name;
            }
        }
        $inputs["next_node"] = $this->factory->input()->field()->select($this->plugin->txt("prt_node_neg_next"), $node_list, $this->plugin->txt("prt_node_neg_next_info"))->withRequired(true)
            ->withValue($node->falsenextnode);
        $inputs["answernote"] = $this->factory->input()->field()->text($this->plugin->txt("prt_node_neg_answernote"), $this->plugin->txt("prt_node_neg_answernote_info"))->withRequired(true)
            ->withValue($node->falseanswernote);
        $inputs["specific_feedback"] = $this->customFactory->textareaRTE($this->question->getId(), $this->plugin->txt("prt_node_neg_specific_feedback"), $this->plugin->txt("prt_node_neg_specific_feedback_info"))
            ->withValue($node->falsefeedback);
        $inputs["feedback_class"] = $this->factory->input()->field()->select($this->plugin->txt('prt_node_neg_feedback_class'), [
            $this->lng->txt("default"),
            $this->plugin->txt("feedback_node_right"),
            $this->plugin->txt("feedback_node_wrong"),
            $this->plugin->txt("feedback_solution_hint"),
            $this->plugin->txt("feedback_extra_info"),
            $this->plugin->txt("feedback_plot_feedback"),
        ], $this->plugin->txt('prt_node_neg_feedback_class_info'))->withRequired(true)
            ->withValue($node->falsefeedbackformat);

        return $inputs;
    }
}