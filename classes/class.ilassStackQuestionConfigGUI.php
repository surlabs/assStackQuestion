<?php
declare(strict_types=1);

use classes\platform\ilias\StackBulktestingIlias;
use classes\platform\StackConfig;
use classes\platform\StackPlatform;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;


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
 * @ilCtrl_isCalledBy ilassStackQuestionConfigGUI: ilObjComponentSettingsGUI
 * @ilCtrl_Calls ilassStackQuestionConfigGUI: ilFormPropertyDispatchGUI
 *
 */
class ilassStackQuestionConfigGUI extends ilPluginConfigGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilCtrl $control;
    protected GlobalHttpState $http;
    protected Factory $factory;
    protected $request;
    protected Renderer $renderer;
    private ilLanguage $language;

    /**
     * @throws stack_exception|ilCtrlException
     */
    public function performCommand($cmd): void
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->control = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->factory = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();

        //Initialize the plugin platform
        StackPlatform::initialize('ilias');

        //Set tabs
        //try {

        $this->tabs->addTab(
            "configure",
            $this->getPluginObject()->txt("ui_admin_configuration_overview_title"),
            $this->control->getLinkTarget($this, "configure")
        );

        $this->tabs->addTab(
            "maxima",
            $this->getPluginObject()->txt("ui_admin_configuration_maxima_title"),
            $this->control->getLinkTarget($this, "maxima")
        );

        $this->tabs->addTab(
            "defaults",
            $this->getPluginObject()->txt("ui_admin_configuration_defaults_title"),
            $this->control->getLinkTarget($this, "defaults")
        );

        $this->tabs->addTab(
            "quality",
            $this->getPluginObject()->txt("ui_admin_configuration_quality_title"),
            $this->control->getLinkTarget($this, "quality")
        );

        //Add plugin title and description
        $this->tpl->setTitle($this->getPluginObject()->txt('ui_admin_configuration_title'));
        $this->tpl->setDescription($this->getPluginObject()->txt('ui_admin_configuration_description'));

        //Get stored settings from the platform database
        $data = StackConfig::getAll();

        switch ($cmd) {
            case "configure":
                $sections = $this->configure($data);
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "configure");
                $rendered = $this->renderForm($data, $form_action, $sections);
                break;
            case "maxima":
                $sections = $this->maxima($data);
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "maxima");
                $rendered = $this->renderForm($data, $form_action, $sections);
                break;
            case "defaults":
                $sections = $this->defaults($data);
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "defaults");
                $rendered = $this->renderForm($data, $form_action, $sections);
                break;
            case "quality":
                $this->quality($data);
                return;
            case "healthcheck":
                $serverAddress = $data["maxima_pool_url"];
                $healthcheck = new stack_cas_healthcheck($data);
                $data = $healthcheck->get_test_results();

                $sections = [];
                $sections["server-info"] = $this->factory->messageBox()->info(
                    $this->getPluginObject()->txt("srv_address") . ":<br \>"
                    . $serverAddress);

                foreach ($data as $key => $value) {

                    $form_fields = [];

                    if (isset($value['details'])) {
                        $form_fields["details"] = $this->factory->legacy($value["details"]);
                        $sections[$value["tag"]] = $this->factory->panel()->standard(
                            $this->getPluginObject()->txt("ui_admin_configuration_defaults_section_title_healthcheck_" . $value["tag"]),
                            $this->factory->legacy(
                                $this->renderer->render($form_fields)
                            )
                        );
                    }
                }

                $sections[] = $this->healthcheck($data);
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "healthcheck");
                $rendered = $this->renderPanel($data, $form_action, $sections);

                break;
            case 'clearCache':
                StackConfig::clearCache();
                $this->quality($data);
                return;
            case "bulktesting":
                //TODO connect with the bulktesting class
                $data = StackBulktestingIlias::doBulktesting();
                $sections = $this->bulktesting($data);
                $form_action = $this->control->getLinkTargetByClass("ilassStackQuestionConfigGUI", "bulktesting");
                $rendered = $this->renderPanel($data, $form_action, $sections);
                break;
            default:
                throw new stack_exception("Unknown configuration command: " . $cmd);
        }
        //} catch (Exception $e) {
        //    throw new stack_exception("Error at perform command: " . $e->getMessage());
        //}

        //sets the rendered content as the main content of the template
        $this->tpl->setContent($rendered);

    }

    /**
     * Renders the form with the given data and sections
     * @param array $data
     * @param string $form_action
     * @param array $sections
     * @return string
     */
    private function renderForm(array $data, string $form_action, array $sections): string
    {
        //Create the form
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        //Check if the form has been submitted
        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            $saving_info = $this->save($result);
        } else {
            $saving_info = "";
        }

        return $saving_info . $this->renderer->render($form);
    }

    /**
     * Renders the panel with the given data and sections
     * @param array $data
     * @param string $form_action
     * @param array $sections
     * @return string
     */
    private function renderPanel(array $data, string $form_action, array $sections): string
    {

        //TODO REPLACE WITH ACTUAL PANEL
        $page = $this->factory->modal()->lightboxTextPage("LOREN IPSUM", $this->language->txt("qpl_qst_xqcas_message_question_text"));
        $modal = $this->factory->modal()->lightbox($page);

        $button = $this->factory->button()->standard($this->language->txt("qpl_qst_xqcas_ui_author_randomisation_show_question_text_action_text"), '')
            ->withOnClick($modal->getShowSignal());

        //Return the UI component
        /*return $this->renderer->render($this->factory->panel()->sub(
            "LOREN IPSUM",
            $this->factory->legacy(
                "LOREN IPSUM" .
                $this->renderer->render($this->factory->divider()->horizontal()) .
                $this->renderer->render($sections)
            )
        ));*/
        return $this->renderer->render($sections);
    }

    /**
     * Shows the configuration overview of the plugin
     */
    private function configure(array $data): array
    {
        $this->tabs->activateTab("configure");
        return PluginConfigurationMainUI::show($data, $this->getPluginObject());
    }

    /**
     * Shows the UI for the Maxima Connection settings
     * @throws stack_exception|ilCtrlException
     */
    private function maxima(array $data): array
    {
        $this->tabs->activateTab("maxima");
        return PluginConfigurationMaximaUI::show($data, $this->getPluginObject());
    }

    /**
     * Shows the UI Form of the defaults values for the plugin
     */
    private function defaults(array $data): array
    {
        $this->tabs->activateTab("defaults");
        return PluginConfigurationDefaultsUI::show($data, $this->getPluginObject());
    }

    /**
     * Shows the UI for the quality assurance settings
     */
    private function quality(array $data): void
    {
        $this->tabs->activateTab("quality");
        $this->tpl->setContent(PluginConfigurationQualityUI::show($data, $this->getPluginObject()));
    }

    private function healthcheck(array $data): array
    {
        $this->tabs->activateTab("quality");
        return PluginConfigurationHealthcheckUI::show($data, $this->getPluginObject());
    }

    private function bulktesting(array $data): array
    {
        $this->tabs->activateTab("quality");
        return PluginConfigurationBulktestingUI::show($data, $this->getPluginObject());
    }

    /**
     * Saves the configuration
     */
    private function save(array $form_data): string
    {
        foreach ($form_data as $category => $input) {
            foreach ($input as $key => $value) {
                StackConfig::set($key, $value, $category);
            }
        }

        $result = StackConfig::save();

        if ($result === true) {
            return $this->renderer->render($this->factory->messageBox()->success($this->plugin_object->txt("ui_admin_configuration_saved")));
        } else {
            return $this->renderer->render($this->factory->messageBox()->failure($this->plugin_object->txt("ui_admin_configuration_not_saved")));
        }
    }
}