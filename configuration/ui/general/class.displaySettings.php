<?php
declare(strict_types=1);

/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */
class displaySettings
{

    /**
     * @param ilassStackQuestionConfigGUI $GUI
     * @return ilPropertyFormGUI
     * @throws ilCtrlException
     */
    public static function _render(ilassStackQuestionConfigGUI $GUI): ilPropertyFormGUI
    {
        global $DIC;
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $ctrl = $DIC->ctrl();
        $form->setFormAction($ctrl->getFormAction($GUI));

        //Values from DB
        $display_data = assStackQuestionConfig::_getStoredSettings('display');
        $connection_data = assStackQuestionConfig::_getStoredSettings('connection');

        //Instant validation
        if ($connection_data['platform_type'] == 'server') {
            $instant_validation = new ilCheckboxInputGUI($GUI->getPlugin()->txt('instant_validation'), 'instant_validation');
            $instant_validation->setInfo($GUI->getPlugin()->txt("instant_validation_info"));
            $instant_validation->setChecked($display_data['instant_validation']);
        } else {
            $instant_validation = new ilCheckboxInputGUI($GUI->getPlugin()->txt('instant_validation'), 'instant_validation');
            $instant_validation->setInfo($GUI->getPlugin()->txt("instant_validation_info"));
            $instant_validation->setChecked(FALSE);
            $instant_validation->setDisabled(TRUE);
        }
        $form->addItem($instant_validation);

        //Maths filter
        $maths_filter = new ilSelectInputGUI($GUI->getPlugin()->txt('maths_filter'), 'maths_filter');
        $maths_filter->setOptions(array("mathjax" => "MathJax"));
        $maths_filter->setInfo($GUI->getPlugin()->txt('maths_filter_info'));
        $maths_filter->setValue($display_data['maths_filter']);
        $form->addItem($maths_filter);

        //Replace dollars
        $replace_dollars = new ilCheckboxInputGUI($GUI->getPlugin()->txt('replace_dollars'), 'replace_dollars');
        $replace_dollars->setInfo($GUI->getPlugin()->txt("replace_dollars_info"));
        $replace_dollars->setChecked($display_data['replace_dollars']);
        $form->addItem($replace_dollars);

        //JSXGraph activation
        $jsx_graph_activated = new ilCheckboxInputGUI($GUI->getPlugin()->txt('allow_jsx_graph'), 'allow_jsx_graph');
        $jsx_graph_activated->setInfo($GUI->getPlugin()->txt('allow_jsx_graph_info'));
        $jsx_graph_activated->setChecked($display_data['allow_jsx_graph']);
        $form->addItem($jsx_graph_activated);

        $form->setTitle($GUI->getPlugin()->txt('display_settings'));
        $form->addCommandButton("saveDisplaySettings", $GUI->getPlugin()->txt("save"));
        $form->addCommandButton("showDisplaySettings", $GUI->getPlugin()->txt("cancel"));
        $form->addCommandButton("setDefaultSettingsForDisplay", $GUI->getPlugin()->txt("default_settings"));

        return $form;
    }

}