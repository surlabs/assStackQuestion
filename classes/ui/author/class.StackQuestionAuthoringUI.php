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
use JetBrains\PhpStorm\NoReturn;
use stack_exception;
use stack_input;
use stack_input_factory;
use stack_potentialresponse_tree_lite;

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

    #[NoReturn] private function save(array $result): ?string
    {
        dump($result);
        exit();
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
            ->withValue($input->get_parameter('forbidFloats'));
        $inputs["allowWords"] = $this->factory->input()->field()->text($this->plugin->txt("input_allow_words"), $this->plugin->txt("input_allow_words_info"))
            ->withValue($input->get_parameter('allowWords'));
        $inputs["lowestTerms"] = $this->factory->input()->field()->checkbox($this->plugin->txt("input_require_lowest_terms"), $this->plugin->txt("input_require_lowest_terms_info"))
            ->withValue($input->get_parameter('lowestTerms'));
        $inputs["sameType"] = $this->factory->input()->field()->checkbox($this->plugin->txt("input_check_answer_type"), $this->plugin->txt("input_check_answer_type_info"))
            ->withValue($input->get_parameter('sameType'));
        $inputs["mustVerify"] = $this->factory->input()->field()->checkbox($this->plugin->txt("input_must_verify"), $this->plugin->txt("input_must_verify_info"))
            ->withValue($input->get_parameter('mustVerify'));
        $inputs["showValidation"] = $this->factory->input()->field()->select($this->plugin->txt("input_show_validation"), [
            0 => $this->plugin->txt('show_validation_no'),
            1 => $this->plugin->txt('show_validation_yes_with_vars'),
            2 => $this->plugin->txt('show_validation_yes_without_vars')
        ], $this->plugin->txt("input_show_validation_info"))->withRequired(true)
            ->withValue($input->get_parameter('showValidation'));
        $inputs["options"] = $this->factory->input()->field()->text($this->plugin->txt("input_options"), $this->plugin->txt("input_options_info"))
            ->withValue($input->get_parameter('options'));


        return $this->customFactory->expandableSection($inputs, $name)->withExpandedByDefault($isFirst);
    }

    private function buildPrtSection(): array
    {
        $prts = [];

        if (!empty($this->question->prts)) {
            foreach ($this->question->prts as $prt_name => $prt) {
                $prts[$prt_name] = $this->buildPrt($prt);
            }
        }

        return $prts;
    }

    private function buildPrt(stack_potentialresponse_tree_lite $prt): array
    {
        $inputs = [];

        $inputs["prt_name"] = $this->factory->input()->field()->text($this->plugin->txt("prt_name"), $this->plugin->txt("prt_name_info"))
            ->withValue($prt->get_name());

        return $inputs;
    }
}