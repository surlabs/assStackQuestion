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
use ilassStackQuestionPlugin;
use ilCtrlException;
use ilCtrlInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;

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
        $this->renderer = $DIC->ui()->renderer();
        $this->lng = $DIC->language();
        $this->request = $DIC->http()->request();
    }

    /**
     * @throws ilCtrlException
     */
    private function buildForm(): StandardForm
    {
        $sections = [
            "basic" => $this->factory->input()->field()->section($this->buildBasicSection(), $this->plugin->txt("edit_cas_question"))
        ];

        return $this->factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTargetByClass("assStackQuestionGUI", "editQuestion"),
            $sections
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function showAuthoringPanel(): string
    {
        $form = $this->buildForm();

        $saving_info = "";

        if ($this->request->getMethod() == "POST") {
            $saving_info = $this->save() ?? "";
        }

        return $saving_info . $this->renderer->render($form);
    }

    /**
     * @throws ilCtrlException
     */
    public function writePostData(): ?string
    {
        $form = $this->buildForm()->withRequest($this->request);

        $result = $form->getData();

        if($result){
            return $this->save();
        }

        return null;
    }

    private function save(): ?string
    {
        return "DEBUG: Saved!";
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
        $inputs["question"] = $this->factory->input()->field()->textarea($this->lng->txt("question"))->withRequired(true)
            ->withValue($this->question->getQuestion());

        return $inputs;
    }
}