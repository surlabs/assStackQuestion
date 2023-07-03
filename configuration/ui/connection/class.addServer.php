<?php
declare(strict_types=1);

/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */
class addServer
{

    /**
     * @param ilassStackQuestionConfigGUI $GUI
     * @return ilPropertyFormGUI
     * @throws ilCtrlException
     */
    public static function _render(ilassStackQuestionConfigGUI $GUI): ilPropertyFormGUI
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();


        if (isset($a_server_id) && $a_server_id > 0) {
            $server = assStackQuestionServer::getServerById($a_server_id);
            $title = $GUI->getPlugin()->txt('edit_server');
            $ctrl->setParameter($GUI, 'server_id', $a_server_id);
        } else {
            $server = assStackQuestionServer::getDefaultServer();
            $title = $GUI->getPlugin()->txt('add_server');
        }

        $form = new ilPropertyFormGUI();
        $form->setTitle($title);
        $form->setFormAction($ctrl->getFormAction($GUI));

        // purpose
        $options = [];
        foreach (assStackQuestionServer::getPurposes() as $purpose) {
            $options[$purpose] = $GUI->getPlugin()->txt('srv_purpose_' . $purpose);
        }
        $purpose = new ilSelectInputGUI($GUI->getPlugin()->txt('srv_purpose'), 'purpose');
        $purpose->setInfo($GUI->getPlugin()->txt('srv_purpose_info'));
        $purpose->setRequired(true);
        $purpose->setOptions($options);
        $purpose->setValue($server->getPurpose());
        $form->addItem($purpose);

        $address = new ilTextInputGUI($GUI->getPlugin()->txt('srv_address'), 'address');
        $address->setInfo($GUI->getPlugin()->txt('srv_address_info'));
        $address->setRequired(true);
        $address->setValue($server->getAddress());
        $form->addItem($address);

        $active = new ilCheckboxInputGUI($lng->txt('active'), 'active');
        $active->setInfo($GUI->getPlugin()->txt('srv_active_info'));
        $active->setChecked($server->isActive());
        $form->addItem($active);

        $form->addCommandButton('saveServerSettings', $lng->txt('save'));
        $form->addCommandButton('showServerList', $lng->txt('cancel'));

        return $form;
    }

}