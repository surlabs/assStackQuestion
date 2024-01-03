<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use classes\core\security\StackException;
use ilComponentFactory;
use ilComponentRepository;
use ilLanguage;
use classes\platform\StackPlatform;
use ilPlugin;
use ilQuestionsPlugin;

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
class StackPlatformIlias extends StackPlatform
{
    private ilLanguage $language;

    public function __construct()
    {
        global $DIC;

        $this->language = $DIC->language();
    }

    /**
     * Gets the platform translation of a string
     * @param string $str
     * @return string|null
     */
    public function getTranslationInternal(string $str): ?string
    {
        return $this->language->txt($str);
    }

    /**
     * Gets platform default settings for STACK question options
     * @return array|null
     */
    public function getPlatformDefaultQuestionOptionsInternal(): ?array
    {
        return [];
    }

    /**
     * Creates an HTML object from the contents
     * @param string $tag
     * @param string $contents
     * @param array $attributes
     * @return string
     */
    public function createTagInternal(string $tag, string $contents, array $attributes = []): string
    {
        // TODO: Check this to use $this->factory and $this->renderer instead of pure HTML

        $html = "<" . $tag;

        foreach ($attributes as $key => $value) {
            $html .= " " . $key . "=\"" . $value . "\"";
        }

        $html .= ">" . $contents . "</" . $tag . ">";

        return $html;
    }

    /**
     * Check if the command is the proxy bypass command
     *
     * @param string $command
     * @return bool
     */
    public static function isProxyBypassInternal(string $command): bool
    {
        // TODO: Implement isProxyBypassInternal() method.
        return true;
    }

    /**
     * Check if the proxy settings are ok
     *
     * @return bool
     */
    public static function isProxySettingsOkInternal(): bool
    {
        // TODO: Implement isProxySettingsOkInternal() method.
        return true;
    }

    /**
     * Called at ilias question object creation
     * @return ilPlugin|null
     */
    public static function getPlugin(): ?ilPlugin
    {
        global $DIC;

        /** @var ilComponentRepository $component_repository */
        $component_repository = $DIC["component.repository"];

        try {
            $plugin_name = 'assStackQuestion';
            $info = $component_repository->getPluginByName($plugin_name);
        } catch (StackException $e) {
            //TODO log error
            return null;
        }

        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC["component.factory"];

        /** @var ilQuestionsPlugin $plugin_obj */
        return $component_factory->getPlugin($info->getId());
    }
}