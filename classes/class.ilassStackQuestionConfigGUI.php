<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

use ILIAS\UI\Implementation\Component\MessageBox\MessageBox;


/**
 * STACK Question plugin config GUI
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 8.0$
 *
 * @ilCtrl_isCalledBy ilassStackQuestionConfigGUI: ilObjComponentSettingsGUI
 * @ilCtrl_Calls ilassStackQuestionConfigGUI: ilFormPropertyDispatchGUI
 *
 */
class ilassStackQuestionConfigGUI extends ilObjectGUI
{
    protected ?assStackQuestionConfig $config = null;
    protected ?ilPlugin $plugin = null;
    protected ?MessageBox $message = null;
    public function __construct()
    {
        global $DIC;

        parent::__construct(array(), 0, true, true);

        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC["component.factory"];
        $this->setPluginObject($component_factory->getPlugin('xqcas'));

        //Set config object
        $plugin_config = new assStackQuestionConfig($this->plugin);
        $this->setConfig($plugin_config);

        //#ILIAS8 Set plugin name in ilObjComponentSettingsGUI
        $this->ctrl->setParameter($this, ilObjComponentSettingsGUI::P_PLUGIN_NAME, 'assStackQuestion');
    }

    /**
     * @return assStackQuestionConfig|null
     */
    public function getConfig(): ?assStackQuestionConfig
    {
        return $this->config;
    }

    /**
     * @param assStackQuestionConfig|null $config
     */
    public function setConfig(?assStackQuestionConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @return ilassStackQuestionPlugin
     */
    public function getPlugin(): ilassStackQuestionPlugin
    {
        /** @var ilassStackQuestionPlugin $plugin */
        $plugin = $this->plugin;
        return $plugin;
    }

    /**
     * #ILIAS8 Add Object to setPlugin to fit the current ilObjComponentSettingsGUI implementation
     * @param ilPlugin|null $plugin
     */
    public function setPluginObject(?ilPlugin $plugin): void
    {
        $this->plugin = $plugin;
    }


    /**
     * Called when clicking on configure at plugin administration list
     * show_connection_settings
     * basic_connection_settings
     * @return void
     */
    public function configureObject()
    {
        global $DIC, $tpl;

        try {
            $this->initTabs('show_connection_settings','basic_connection_settings');
            $form = generalConnection::_render($this);
            if (is_a($this->message, 'ILIAS\UI\Component\MessageBox\MessageBox')) {
                $tpl->setContent($DIC->ui()->renderer()->render($this->message) . $form->getHTML());
            } else {
                $tpl->setContent($form->getHTML());
            }
        } catch (Exception $e) {
            $error_message = $DIC->ui()->factory()->messageBox()->failure($e->getMessage().$e->getTraceAsString());
            $tpl->setContent($DIC->ui()->renderer()->render($error_message));
        }
    }

    /**
     * action: show general connection form
     * @return void
     */
    public function showConnectionSettingsObject(){
        $this->configureObject();
    }

    /**
     * view: show server list
     * show_connection_settings
     * server_configuration
     * @return void
     */
    public function showServerListObject()
    {
        global $DIC, $tpl;

        try {
            $this->initTabs('show_connection_settings','server_configuration');
            $table = serverList::_render($this);

            if (is_a($this->message, 'ILIAS\UI\Component\MessageBox\MessageBox')) {
                $tpl->setContent($DIC->ui()->renderer()->render($this->message) . $table);
            } else {
                $tpl->setContent($table);
            }
        } catch (Exception $e) {
            $error_message = $DIC->ui()->factory()->messageBox()->failure($e->getMessage() . $e->getTraceAsString());

            //render content
            $tpl->setContent($DIC->ui()->renderer()->render($error_message));
        }
    }

    /**
     * action: go to edit server form
     * called when clicking on add server button in server configuration toolbar
     * show_connection_settings
     * server_configuration
     * @return void
     * @throws ilCtrlException
     */
    public function addServerObject()
    {
        $this->editServerObject();
    }

    /**
     * action: save server settings
     * show_connection_settings
     * server_configuration
     * @return void
     * @throws ilCtrlException
     */
    public function saveServerSettingsObject(): void
    {
        global $DIC, $tpl;
        $form = serverForm::_render($this, $_GET['server_id'] ?? 0);

        if ($form->checkInput()) {
            if ($_GET['server_id'] ?? 0) {
                $server = assStackQuestionServer::getServerById($_GET['server_id']);
            } else {
                $server = assStackQuestionServer::getDefaultServer();
            }
            $server->setPurpose($form->getInput('purpose'));
            $server->setAddress($form->getInput('address'));
            $server->setActive($form->getInput('active'));
            $server->save();

            $this->message = $DIC->ui()->factory()->messageBox()->success(
                $this->getPlugin()->txt('server_activated')
            );
            $this->showServerListObject();
        } else {
            $this->showServerListObject();
        }
    }

    /**
     * view: edit server view form
     * @return void
     * @throws ilCtrlException
     */
    public function editServerObject()
    {
        global $DIC, $tpl;

        try {
            $this->initTabs('show_connection_settings', 'server_configuration');
            $form = serverForm::_render($this, $_GET['server_id'] ?? 0);
            $tpl->setContent($form->getHTML());
        } catch (Exception $e) {
            $error_message = $DIC->ui()->factory()->messageBox()->failure($e->getMessage() . $e->getTraceAsString());

            //render content
            $tpl->setContent($DIC->ui()->renderer()->render($error_message));
        }
    }

    /**
     * action: activate server
     * @return void
     */
    public function activateServersObject()
    {
        $this->changeServerActivation(true);
    }

    /**
     * action: deactivate server
     * @return void
     */
    public function deactivateServersObject()
    {
        $this->changeServerActivation(false);
    }

    /**
     * action: switch server activation/deactivation
     * @return void
     */
    protected function changeServerActivation($active)
    {
        global $DIC;

        if (isset($_POST['server_id'])) {
            $server_ids = (array) $_POST['server_id'];
        } elseif (isset($_GET['server_id'])) {
            $server_ids = (array) $_GET['server_id'];
        }

        if (empty($server_ids)) {
            $this->message = $DIC->ui()->factory()->messageBox()->failure(
                $this->getPlugin()->txt('no_server_selected')
            );
        } else {
            foreach ($server_ids as $server_id) {
                $server = assStackQuestionServer::getServerById($server_id);
                $server->setActive($active);
            }
            assStackQuestionServer::saveServers();

            if (count($server_ids) == 1) {
                $this->message = $DIC->ui()->factory()->messageBox()->success(
                    $this->getPlugin()->txt($active ? 'server_activated' : 'server_deactivated')
                );
            } else {
                $this->message = $DIC->ui()->factory()->messageBox()->success(
                    $this->getPlugin()->txt($active ? 'servers_activated' : 'servers_deactivated')
                );
            }
        }

        $this->showServerListObject();
    }

    /**
     * view: confirmation screen for delete servers
     * @throws ilCtrlException
     */
    public function confirmDeleteServersObject()
    {
        global $DIC, $tpl;

        if (isset($_POST['server_id'])) {
            $server_ids = (array) $_POST['server_id'];
        } elseif (isset($_GET['server_id'])) {
            $server_ids = (array) $_GET['server_id'];
        } else {
            $server_ids = array();
        }

        if (empty($server_ids)) {
            $this->message = $DIC->ui()->factory()->messageBox()->failure(
                $this->getPlugin()->txt('no_server_selected')
            );
            $this->showServerListObject();
        }

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setHeaderText($this->getPlugin()->txt('confirm_delete_servers'));
        $confirmation_gui->setFormAction($DIC->ctrl()->getFormAction($this));
        $confirmation_gui->setConfirm($DIC->language()->txt('delete'), 'deleteServers');
        $confirmation_gui->setCancel($DIC->language()->txt('cancel'), 'showServerList');

        foreach ($server_ids as $server_id) {
            $server = assStackQuestionServer::getServerById($server_id);
            $confirmation_gui->addItem('server_id[]', $server_id, $server->getAddress());
        }

        $tpl->setContent($confirmation_gui->getHTML());
    }

    /**
     * action: confirm and proceed to deletion
     * @return void
     */
    public function deleteServersObject()
    {
        global $DIC;

        $server_ids = (array)$_POST['server_id'];
        assStackQuestionServer::deleteServers($server_ids);

        $this->message = $DIC->ui()->factory()->messageBox()->success(
            $this->getPlugin()->txt(count($server_ids) == 1 ? 'server_deleted' : 'servers_deleted'
        ));

        $this->showServerListObject();
    }

    /**
     * action: save general connection settings
     */
    public function saveConnectionSettingsObject()
    {
        global $DIC;

        try {
            $ok = $this->getConfig()->saveConnectionSettings();
            if ($ok) {
                $this->message = $DIC->ui()->factory()->messageBox()->success(
                    $this->getPlugin()->txt('config_connection_changed_message'
                    ));

            } else {
                $this->message = $DIC->ui()->factory()->messageBox()->failure(
                    $this->getPlugin()->txt('config_error_message'
                    ));
            }
        } catch (Exception $e) {
            $this->message = $DIC->ui()->factory()->messageBox()->failure(
                $e->getMessage()
                );

        }
        $this->configureObject();
    }

    /**
     * @return void
     */
    public function runHealthcheckObject(){
        global $DIC, $tpl;
        try {
            healthcheckView::_render($this, true);
        } catch (ilCtrlException|ilTemplateException $e) {
            $this->message = $DIC->ui()->factory()->messageBox()->failure(
                $e->getMessage()
            );
            $this->configureObject();
        }
    }

    /**
     * @return void
     */
    public function showHealthcheck(){
        global $DIC, $tpl;
        try {
            healthcheckView::_render($this, false);
        } catch (ilCtrlException|ilTemplateException $e) {
            $this->message = $DIC->ui()->factory()->messageBox()->failure(
                $e->getMessage()
            );
            $this->configureObject();
        }
    }

    /**
     * @param string $main_tab
     * @param string $secondary_tab
     * @return void
     * @throws ilCtrlException
     */
    public function initTabs(string $main_tab, string $secondary_tab)
    {
        global $DIC;

        //Main Tabs
        $this->initMainTabs();
        $DIC->tabs()->activateTab($main_tab);

        //Secondary Tabs
        $this->initSecondaryTabs($main_tab);
        $DIC->tabs()->activateSubTab($secondary_tab);
    }

    /**
     * Init always visible configuration main tabs
     *
     * @return void
     * @throws ilCtrlException
     */
    public function initMainTabs(){
        global $DIC;

        //main tab
        $DIC->tabs()->addTab('show_connection_settings', $this->getPlugin()->txt('show_connection_settings'), $DIC->ctrl()->getLinkTarget($this, 'showConnectionSettings'));
        $DIC->tabs()->addTab('show_other_settings', $this->getPlugin()->txt('show_other_settings'), $DIC->ctrl()->getLinkTarget($this, 'showOtherSettings'));
        $DIC->tabs()->addTab('show_healthcheck', $this->getPlugin()->txt('show_healthcheck'), $DIC->ctrl()->getLinkTarget($this, 'showHealthcheck'));
    }

    /**
     * Init secondary tabs depending on parent
     * @param string $parent
     * @return void
     * @throws ilCtrlException
     */
    public function initSecondaryTabs(string $parent)
    {
        global $DIC;

        switch ($parent) {
            case 'show_connection_settings':
                $DIC->tabs()->addSubTab(
                    'basic_connection_settings', $this->getPlugin()->txt('basic_connection_settings'),
                    $DIC->ctrl()->getLinkTarget($this, 'showConnectionSettings')
                );
                $DIC->tabs()->addSubTab(
                    'server_configuration', $this->getPlugin()->txt('server_configuration'),
                    $DIC->ctrl()->getLinkTarget($this, 'showServerList')
                );
                break;
            case 'show_other_settings':
                $DIC->tabs()->addSubTab(
                    'show_display_settings', $this->getPlugin()->txt('show_display_settings'),
                    $DIC->ctrl()->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showDisplaySettings')
                );
                $DIC->tabs()->addSubTab(
                    'show_default_options_settings', $this->getPlugin()->txt('show_default_options_settings'),
                    $DIC->ctrl()->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showDefaultOptionsSettings')
                );
                $DIC->tabs()->addSubTab(
                    'show_default_inputs_settings', $this->getPlugin()->txt('show_default_inputs_settings'),
                    $DIC->ctrl()->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showDefaultInputsSettings')
                );
                $DIC->tabs()->addSubTab(
                    'show_default_prts_settings', $this->getPlugin()->txt('show_default_prts_settings'),
                    $DIC->ctrl()->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showDefaultPRTsSettings')
                );
                $DIC->tabs()->addSubTab(
                    'show_feedback_styles_settings', $this->getPlugin()->txt('feedback_styles_settings'),
                    $DIC->ctrl()->getLinkTargetByClass('ilassStackQuestionConfigGUI', 'showFeedbackStylesSettings')
                );
                break;
        }
    }

    public function performCommand(string $cmd): void
    {
        // TODO: Implement performCommand() method.
    }
}