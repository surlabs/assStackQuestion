<?php
/**
 * Copyright (c) Laboratorio de Soluciones del Sur, Sociedad Limitada
 * GPLv3, see LICENSE
 */

require_once "./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php";

/**
 * STACK Question plugin for ILIAS 4.4+
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 * @author Jesus Copado <jesus.copado@ili.fau.de>
 * @version $Id$
 *
 */
class ilassStackQuestionPlugin extends ilQuestionsPlugin
{

    final function getPluginName(): string
    {
        return "assStackQuestion";
    }

    final function getQuestionType(): string
    {
        return "assStackQuestion";
    }

    final function getQuestionTypeTranslation(): string
    {
        return $this->txt($this->getQuestionType());
    }

    protected function readEventListening(): void
    {
    }
    /**
     * Send Info Message to Screen.
     *
     * @param	string	message
     * @param	boolean	if true message is kept in session
     * @static
     *
     */
    public static function sendInfo($a_info = "", $a_keep = false)
    {
        global $DIC;

        if(isset($DIC["tpl"])) {
            $tpl = $DIC["tpl"];
            $tpl->setOnScreenMessage("info", $a_info, $a_keep);
        }
    }

    /**
     * Send Failure Message to Screen.
     *
     * @param	string	message
     * @param	boolean	if true message is kept in session
     * @static
     *
     */
    public static function sendFailure($a_info = "", $a_keep = false)
    {
        global $DIC;

        if (isset($DIC["tpl"])) {
            $tpl = $DIC["tpl"];
            $tpl->setOnScreenMessage("failure", $a_info, $a_keep);
        }
    }

    /**
     * Send Question to Screen.
     *
     * @param	string	message
     * @param	boolean	if true message is kept in session
     * @static	*/
    public static function sendQuestion($a_info = "", $a_keep = false)
    {
        global $DIC;

        if(isset($DIC["tpl"])) {
            $tpl = $DIC["tpl"];
            $tpl->setOnScreenMessage("question", $a_info, $a_keep);
        }
    }

    /**
     * Send Success Message to Screen.
     *
     * @param	string	message
     * @param	boolean	if true message is kept in session
     * @static
     *
     */
    public static function sendSuccess($a_info = "", $a_keep = false)
    {
        global $DIC;

        /** @var ilTemplate $tpl */
        if(isset($DIC["tpl"])) {
            $tpl = $DIC["tpl"];
            $tpl->setOnScreenMessage("success", $a_info, $a_keep);
        }
    }
}

?>