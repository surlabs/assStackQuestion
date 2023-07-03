<?php
declare(strict_types=1);

/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */
class serverList
{

    /**
     * @param ilassStackQuestionConfigGUI $GUI
     * @return string
     * @throws ilCtrlException
     */
    public static function _render(ilassStackQuestionConfigGUI $GUI): string
    {
        global $DIC;

        $button = $DIC->ui()->factory()->button()->standard(
            $GUI->getPlugin()->txt('add_server'),
            $DIC->ctrl()->getLinkTarget($GUI, 'addServer')
        );

        $DIC->toolbar()->addComponent($button);

        $table = new assStackQuestionServerTableGUI($GUI, 'showServerList');
        return $table->getHTML();
    }

}