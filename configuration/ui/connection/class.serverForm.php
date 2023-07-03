<?php
declare(strict_types=1);

/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */
class serverForm
{

    /**
     * @param ilassStackQuestionConfigGUI $GUI
     * @param int                         $a_server_id
     * @return ilPropertyFormGUI
     * @throws ilCtrlException
     */
    public static function _render(ilassStackQuestionConfigGUI $GUI, int $a_server_id): ilPropertyFormGUI
    {
        global $DIC;
        $form = new ilPropertyFormGUI();

        if ($a_server_id > 0) {
            $server = assStackQuestionServer::getServerById($a_server_id);
            $title = $GUI->getPlugin()->txt('edit_server');
            $DIC->ctrl()->setParameter($GUI, 'server_id', $a_server_id);
        } else {
            $server = assStackQuestionServer::getDefaultServer();
            $title = $GUI->getPlugin()->txt('add_server');
        }

        $form->setTitle($title);
        $form->setFormAction($DIC->ctrl()->getFormAction($GUI));

        //purpose
        $options = array();
        foreach (assStackQuestionServer::getPurposes() as $purpose) {
            $options[$purpose] = $GUI->getPlugin()->txt('srv_purpose_' . $purpose);
        }

        $purpose = new ilSelectInputGUI($GUI->getPlugin()->txt('srv_purpose'), 'purpose');
        $purpose->setInfo($GUI->getPlugin()->txt('srv_purpose_info'));
        $purpose->setRequired(true);
        $purpose->setOptions($options);
        $purpose->setValue($server->getPurpose());

        $form->addItem($purpose);

        //address
        $address = new ilTextInputGUI($GUI->getPlugin()->txt('srv_address'), 'address');
        $address->setInfo($GUI->getPlugin()->txt('srv_address_info'));
        $address->setRequired(true);
        $address->setValue($server->getAddress());

        $form->addItem($address);

        //is active
        $active = new ilCheckboxInputGUI($DIC->language()->txt('active'), 'active');
        $active->setInfo($GUI->getPlugin()->txt('srv_active_info'));
        $active->setChecked($server->isActive());
        $form->addItem($active);

        //command buttons
        $form->addCommandButton('saveServerSettings', $DIC->language()->txt('save'));
        $form->addCommandButton('showServerList', $DIC->language()->txt('cancel'));

        return $form;
    }

}