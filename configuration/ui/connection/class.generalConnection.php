<?php
declare(strict_types=1);

/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */
class generalConnection
{

    /**
     * @param ilassStackQuestionConfigGUI $GUI
     * @return ilPropertyFormGUI
     * @throws ilCtrlException
     */
    public static function _render(ilassStackQuestionConfigGUI $GUI): ilPropertyFormGUI
    {
        global $DIC;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($GUI));

        //Values from DB
        $connection_data = assStackQuestionConfig::_getStoredSettings('connection');

        //Platform selection
        $platform_type = new ilSelectInputGUI($GUI->getPlugin()->txt('platform_type'), 'platform_type');
        $platform_type->setOptions(
            array(
                "server" => $GUI->getPlugin()->txt('server'),
                "win" => $GUI->getPlugin()->txt('windows'),
                "linux" => $GUI->getPlugin()->txt('unix')
            )
        );
        $platform_type->setInfo($GUI->getPlugin()->txt('platform_type_info'));
        $platform_type->setValue($connection_data['platform_type']);
        $form->addItem($platform_type);

        //Maxima version
        $maxima_version = new ilSelectInputGUI($GUI->getPlugin()->txt('maxima_version'), 'maxima_version');
        $maxima_version->setOptions(
            array(
                '5.23.2' => '5.23.2',
                '5.25.1' => '5.25.1',
                '5.26.0' => '5.26.0',
                '5.27.0' => '5.27.0',
                '5.28.0' => '5.28.0',
                '5.30.0' => '5.30.0',
                '5.31.1' => '5.31.1',
                '5.31.2' => '5.31.2',
                '5.31.3' => '5.31.3',
                '5.32.0' => '5.32.0',
                '5.32.1' => '5.32.1',
                '5.33.0' => '5.33.0',
                '5.34.0' => '5.34.0',
                '5.34.1' => '5.34.1',
                '5.35.1' => '5.35.1',
                '5.36.0' => '5.36.0',
                '5.36.1' => '5.36.1',
                '5.37.3' => '5.37.3',
                '5.38.0' => '5.38.0',
                '5.38.1' => '5.38.1',
                '5.39.0' => '5.39.0',
                '5.40.0' => '5.40.0',
                '5.41.0' => '5.41.0',
                'default' => 'default'
            )
        );
        $maxima_version->setInfo($GUI->getPlugin()->txt('maxima_version_info'));
        $maxima_version->setValue($connection_data['maxima_version']);
        $form->addItem($maxima_version);

        //CAS connection timeout
        $cas_connection_timeout = new ilTextInputGUI(
            $GUI->getPlugin()->txt('cas_connection_timeout'), 'cas_connection_timeout'
        );
        $cas_connection_timeout->setInfo($GUI->getPlugin()->txt('cas_connection_timeout_info'));
        $cas_connection_timeout->setValue($connection_data['cas_connection_timeout']);
        $form->addItem($cas_connection_timeout);

        //CAS result caching
        //NOT USED BY ILIAS VERSION
        $cas_result_caching = new ilHiddenInputGUI('cas_result_caching');
        $cas_result_caching->setValue('db');
        $form->addItem($cas_result_caching);

        if ($connection_data['platform_type'] == 'win') {
            //Maxima command
            $maxima_command = new ilTextInputGUI($GUI->getPlugin()->txt('maxima_command'), 'maxima_command');
            $maxima_command->setInfo($GUI->getPlugin()->txt('maxima_command_info'));
            $maxima_command->setValue($connection_data['maxima_command']);
            $form->addItem($maxima_command);
        } elseif ($connection_data['platform_type'] == 'server') {
            $link = $DIC->ctrl()->getLinkTarget($GUI, 'showServerList');
            $maxima_command = new ilNonEditableValueGUI($GUI->getPlugin()->txt('maxima_command'), '');
            $maxima_command->setValue($GUI->getPlugin()->txt('maxima_command_server'));
            $maxima_command->setInfo(sprintf($GUI->getPlugin()->txt('maxima_command_server_info'), $link));
            $form->addItem($maxima_command);
        }

        if ($connection_data['platform_type'] == 'win' or $connection_data['platform_type'] == 'server') {
            //Plot command
            $plot_command = new ilTextInputGUI($GUI->getPlugin()->txt('plot_command'), 'plot_command');
            $plot_command->setInfo($GUI->getPlugin()->txt('plot_command_info'));
            $plot_command->setValue($connection_data['plot_command']);
            $form->addItem($plot_command);
        }

        //CAS debugging
        //NOT USED BY ILIAS VERSION
        $cas_debugging_hidden = new ilHiddenInputGUI('cas_debugging');
        $cas_debugging_hidden->setValue('0');
        $form->addItem($cas_debugging_hidden);

        //Maxima libraries
        $maxima_libraries = new ilTextInputGUI($GUI->getPlugin()->txt('maxima_libraries'), 'cas_maxima_libraries');
        $maxima_libraries->setInfo($GUI->getPlugin()->txt('cas_maxima_libraries_info'));
        $maxima_libraries->setValue($connection_data['cas_maxima_libraries']);
        $form->addItem($maxima_libraries);

        $form->setTitle($GUI->getPlugin()->txt('connection_settings'));
        $form->addCommandButton("saveConnectionSettings", $GUI->getPlugin()->txt("save"));
        $form->addCommandButton("showConnectionSettings", $GUI->getPlugin()->txt("cancel"));
        $form->addCommandButton("setDefaultSettingsForConnection", $GUI->getPlugin()->txt("default_settings"));

        return $form;
    }

}