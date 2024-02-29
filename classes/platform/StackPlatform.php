<?php
declare(strict_types=1);

namespace classes\platform;

use classes\platform\ilias\StackPlatformIlias;

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
abstract class StackPlatform
{
    public static StackPlatform $platform;

    /**
     * Start the platform
     * In this mathod, the platform should be initialized, the database connection should be established and the configuration should be loaded
     *
     * @param string $x
     * @return void
     * @throws StackException
     * @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection
     */
    public static function initialize(string $x): void {
        if (!isset(self::$platform)) {
            switch ($x) {
                case 'ilias':
                    self::$platform = new StackPlatformIlias();
                    break;
                default:
                    throw new StackException('Invalid platform selected: ' . $x . '.');
            }

            StackDatabase::setPlatform($x);

            StackConfig::load();
        }
    }

    /**
     * Gets the platform translation of a string
     * @param string $str
     * @param mixed $params
     * @return string|null
     */
    public static function getTranslation(string $str, mixed $params = null): ?string
    {
        $txt = self::$platform->getTranslationInternal($str);

        if (isset($params)) {
            if (is_string($params)) $params = array($params);

            $txt = vsprintf($txt, $params);
        }

        return $txt;
    }

    /**
     * Gets platform default settings for STACK question options
     * @return array|null
     */
    public static function getPlatformDefaultQuestionOptions(): ?array
    {
        return self::$platform->getPlatformDefaultQuestionOptionsInternal();
    }


    /**
     * @param string $tag
     * @param string $contents
     * @param array $attributes
     * @return string
     */
    public static function createTag(string $tag, string $contents, array $attributes = []): string
    {
        return self::$platform->createTagInternal($tag, $contents, $attributes);
    }

    /**
     * Check if the command is the proxy bypass command
     *
     * @param string $command
     * @return bool
     */
    public static function isProxyBypass(string $command) :bool {
        return self::$platform->isProxyBypassInternal($command);
    }

    /**
     * Check if the proxy settings are ok for the platform
     *
     * @return bool
     */
    public static function isProxySettingsOk() :bool {
        return self::$platform->isProxySettingsOkInternal();
    }
}