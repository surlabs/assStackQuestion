<?php
declare(strict_types=1);

/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */
class healthcheckView
{

    /**
     * @param ilassStackQuestionConfigGUI $GUI
     * @param bool                        $run_healthcheck
     * @return void
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    public static function _render(ilassStackQuestionConfigGUI $GUI, bool $run_healthcheck): void
    {
        global $DIC, $tpl;

        $toolbar = new ilToolbarGUI();
        $ctrl = $DIC->ctrl();

        $ctrl->saveParameter($GUI, 'server_id');
        $toolbar->setFormAction($ctrl->getFormAction($GUI));

        $healthcheck_reduced_button = $DIC->ui()->factory()->button()->standard(
            $GUI->getPlugin()->txt('healthcheck_reduced'),
            $DIC->ctrl()->getLinkTarget($GUI, 'runHealthcheck')
        );

        $DIC->toolbar()->addComponent($healthcheck_reduced_button);

        $clear_cache_button = $DIC->ui()->factory()->button()->standard(
            $GUI->getPlugin()->txt('clear_cache'),
            $DIC->ctrl()->getLinkTarget($GUI, 'clearCache')
        );

        $DIC->toolbar()->addComponent($clear_cache_button);

        $result_html = '';
        $message = '';

        if ($run_healthcheck) {
            if ($GUI->getConfig()->get('platform_type') == 'server') {
                $message = $DIC->ui()->factory()->messageBox()->info(
                    $GUI->getPlugin()->txt('srv_address') . ':<br/>' . assStackQuestionConfig::_getServerAddress()
                );
            }

            //Create Healthcheck
            $healthcheck_object = new assStackQuestionHealthcheck($GUI->getPlugin());
            try {
                $healthcheck_data = $healthcheck_object->doHealthcheck();
            } catch (Exception $e) {
                $message = $DIC->ui()->factory()->messageBox()->failure($e->getMessage() . $e->getTraceAsString());
                $healthcheck_data = false;
            }

            if ($healthcheck_data) {
                //Show healthcheck
                $healthcheck_gui_object = new assStackQuestionHealthcheckGUI($GUI->getPlugin(), $healthcheck_data);
                $healthcheck_gui = $healthcheck_gui_object->showHealthcheck();
                $result_html = $healthcheck_gui->get();
            }
        }

        if(is_a($message,'\ILIAS\UI\Component\MessageBox\MessageBox')){
            $DIC->ui()->renderer()->render($message);
        }
        $tpl->setContent($result_html);
    }

}