<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Section;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *  This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 *  originally created by Chris Sangwin.
 *
 *  The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "STACK Question" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/STACK
 *
 *  If you need support, please contact the maintainer of this software at:
 *  stack@surlabs.es
 *
 */
class PluginConfigurationMaximaUI
{

    private static Factory $factory;
    private static ilCtrl $control;


    /**
     * Shows the plugin configuration Maxima settings form
     * @throws stack_exception|ilCtrlException
     * @throws stack_exception
     */
    public static function show(array $data, ilPlugin $plugin_object): array
    {
        global $DIC;

        self::$factory = $DIC->ui()->factory();
        self::$control = $DIC->ctrl();

        //control parameters
        self::$control->setParameterByClass(
            'ilassStackQuestionConfigGUI',
            'maxima',
            'saveConnection'
        );

        //get sections
        if ($data["platform_type"] == "server") {
            $content = [
                'common' => self::getMaximaCommonSection($data, $plugin_object),
                'server' => self::getMaximaServerSection($data, $plugin_object)
            ];
        } elseif ($data["platform_type"] == "linux") {
            $content = [
                'common' => self::getMaximaCommonSection($data, $plugin_object),
                'linux' => self::getMaximaLocalSection($data, $plugin_object)
            ];
        } else {
            throw new stack_exception("Error: Platform type not valid: " . $data["platform_type"]);
        }

        return $content;
    }

    /**
     * Gets the Maxima connection section
     * @param array $data
     * @param ilPlugin $plugin_object
     * @return Section
     * @throws stack_exception
     */
    private static function getMaximaCommonSection(array $data, ilPlugin $plugin_object): Section
    {
        //Maxima version
        $maxima_version_options = [
            '5.32.1' => $plugin_object->txt("ui_admin_configuration_connection_maxima_version_5_32_1"),
            '5.40.0' => '5.40.0', '5.41.0' => '5.41.0',
            '5.42.0' => '5.42.0', '5.42.1' => '5.42.1', '5.42.2' => '5.42.2',
            '5.43.0' => '5.43.0', '5.43.1' => '5.43.1', '5.43.2' => '5.43.2',
            '5.44.0' => '5.44.0', '5.46.0' => '5.46.0', '5.47.0' => '5.47.0',
            'default' => 'default'
        ];
        if (isset($data["maxima_version"]) && array_key_exists($data["maxima_version"], $maxima_version_options)) {
            $maxima_version_value = $data["maxima_version"];
        } else {
            //TODO esto crashea la instalación, devolvemos la versión por defecto requerida
            //throw new stack_exception("Error: Maxima version value not valid: " . $data["maxima_version"]);
            $maxima_version_value = '5.44.0';
        }
        $maxima_version = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_version_title"),
            $maxima_version_options,
            $plugin_object->txt("ui_admin_configuration_connection_maxima_version_description")
        )->withValue($maxima_version_value)->withRequired(true);

        //CAS Connection timeout
        $cas_connection_timeout_value = (string)$data["cas_connection_timeout"] ?? "10";

        $cas_connection_timeout = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_defaults_cas_connection_timeout_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_cas_connection_timeout_description")
        )->withValue($cas_connection_timeout_value);

        //CAS result caching
        $cas_result_caching_options = [
            'db' => $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_db"),
            'none' => $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_none"),
        ];
        if (isset($data["cas_result_caching"]) && array_key_exists($data["cas_result_caching"], $cas_result_caching_options)) {
            $maxima_version_value = $data["cas_result_caching"];
        } else {
            throw new stack_exception("Error CAS result caching value not valid: " . $data["cas_result_caching"]);
        }
        $cas_result_caching = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_title"),
            $cas_result_caching_options,
            $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_description")
        )->withValue($maxima_version_value)->withRequired(true);

        //Preparse all code
        $preparse_all_options = [
            'true' => $plugin_object->txt("ui_admin_configuration_connection_preparse_all_yes"),
            'false' => $plugin_object->txt("ui_admin_configuration_connection_preparse_all_no"),
        ];
        if (isset($data["preparse_all"]) && array_key_exists((string)$data["preparse_all"], $preparse_all_options)) {
            $preparse_all_value = $data["preparse_all"];
        } else {
            //TODO throw new stack_exception("Error: Preparse all value not valid: " . $data["preparse_all"]);
            $preparse_all_value = 'true';
        }
        $preparse_all = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_connection_preparse_all_title"),
            $preparse_all_options,
            $plugin_object->txt("ui_admin_configuration_connection_preparse_all_description")
        )->withValue($preparse_all_value)->withRequired(true);


        //CAS debugging
        if (isset($data["cas_debugging"]) && $data["cas_debugging"] == "1") {
            $cas_debugging_value = true;
        } else {
            $cas_debugging_value = false;
        }
        $cas_debugging = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_connection_cas_debugging_title"),
            $plugin_object->txt("ui_admin_configuration_connection_cas_debugging_description")
        )->withValue($cas_debugging_value);

        //Cache parsed expressions longer than
        if (isset($data["cache_parsed_expressions_longer_than"]) && is_string($data["cache_parsed_expressions_longer_than"])) {
            $cache_parsed_expressions_longer_than_value = $data["cache_parsed_expressions_longer_than"];
        } else {
            $cache_parsed_expressions_longer_than_value = "";
        }
        $cache_parsed_expressions_longer_than = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_cache_parsed_expressions_longer_than_title"),
            $plugin_object->txt("ui_admin_configuration_connection_cache_parsed_expressions_longer_than_description")
        )->withValue((string)$cache_parsed_expressions_longer_than_value);

        //Maxima libraries
        $maxima_libraries_value = $data["cas_maxima_libraries"] ?? "";
        $maxima_libraries = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_libraries_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_libraries_description")
        )->withValue((string)$maxima_libraries_value);

        return self::$factory->input()->field()->section(
            [
                'maxima_version' => $maxima_version,
                'cas_connection_timeout' => $cas_connection_timeout,
                'cas_result_caching' => $cas_result_caching,
                'preparse_all' => $preparse_all,
                'cas_debugging' => $cas_debugging,
                'cache_parsed_expressions_longer_than' => $cache_parsed_expressions_longer_than,
                'cas_maxima_libraries' => $maxima_libraries,
            ],
            $plugin_object->txt("ui_admin_configuration_connection_maxima_connection_common_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_connection_common_description")
        );
    }

    /**
     * Gets the form for the plugin configuration Maxima settings when using
     * the Server option to connect to Maxima
     * @param array $data
     * @param ilPlugin $plugin_object
     * @return Section
     */
    private static function getMaximaServerSection(array $data, ilPlugin $plugin_object): Section
    {
        //URL of the maxima pool
        if (isset($data["maxima_pool_url"]) && is_string($data["maxima_pool_url"])) {
            $maxima_pool_url_value = $data["maxima_pool_url"];
        } else {
            $maxima_pool_url_value = "";
        }
        $maxima_pool_url = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_url_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_url_description")
        )->withValue($maxima_pool_url_value);

        //Server/username:password of the maxima pool
        if (isset($data["maxima_pool_server_username_password"]) && is_string($data["maxima_pool_server_username_password"])) {
            $maxima_pool_server_username_password_value = $data["maxima_pool_server_username_password"];
        } else {
            $maxima_pool_server_username_password_value = "";
        }
        $maxima_pool_server_username_password = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_server_username_password_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_server_username_password_description")
        )->withValue($maxima_pool_server_username_password_value);

        //Cache parsed expressions longer than
        if (isset($data["cache_parsed_expressions_longer_than"]) && is_string($data["cache_parsed_expressions_longer_than"])) {
            $cache_parsed_expressions_longer_than_value = $data["cache_parsed_expressions_longer_than"];
        } else {
            $cache_parsed_expressions_longer_than_value = "";
        }
        $cache_parsed_expressions_longer_than = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_cache_parsed_expressions_longer_than_title"),
            $plugin_object->txt("ui_admin_configuration_connection_cache_parsed_expressions_longer_than_description")
        )->withValue($cache_parsed_expressions_longer_than_value);

        //Maxima uses proxy
        //TODO Proxy option
        if (isset($data["maxima_uses_proxy"]) && $data["maxima_uses_proxy"] == "1") {
            $maxima_uses_proxy_value = true;
        } else {
            $maxima_uses_proxy_value = false;
        }
        $maxima_uses_proxy = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_uses_proxy_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_uses_proxy_description")
        )->withValue($maxima_uses_proxy_value);

        return self::$factory->input()->field()->section(
            [
                'maxima_pool_url' => $maxima_pool_url,
                'maxima_pool_server_username_password' => $maxima_pool_server_username_password,
                'cache_parsed_expressions_longer_than' => $cache_parsed_expressions_longer_than,
                'maxima_uses_proxy' => $maxima_uses_proxy,
            ],
            $plugin_object->txt("ui_admin_configuration_connection_maxima_connection_server_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_connection_server_description")
        );
    }

    /**
     * Gets the form for the plugin configuration Maxima settings when using
     * the Local option to connect to Maxima
     * @param array $data
     * @param ilPlugin $plugin_object
     * @return Section
     */
    private static function getMaximaLocalSection(array $data, ilPlugin $plugin_object): Section
    {
        //Maxima command
        $maxima_command_value = $data["maxima_command"] ?? "";
        $maxima_command = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_command_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_command_description")
        )->withValue($maxima_command_value);

        //Optimized Maxima command
        $optimized_maxima_command_value = $data["optimized_maxima_command"] ?? "";
        //FEATURE Optimized Maxima command
        /*
        $optimized_maxima_command = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_optimized_maxima_command_title"),
            $plugin_object->txt("ui_admin_configuration_connection_optimized_maxima_command_description")
        )->withValue(");*/

        //Plot command
        $plot_command_value = $data["plot_command"] ?? "";
        $plot_command = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_plot_command_title"),
            $plugin_object->txt("ui_admin_configuration_connection_plot_command_description")
        )->withValue($plot_command_value);

        //Maxima uses proxy
        if (isset($data["maxima_uses_proxy"]) && $data["maxima_uses_proxy"] == "1") {
            $maxima_uses_proxy_value = true;
        } else {
            $maxima_uses_proxy_value = false;
        }
        $maxima_uses_proxy = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_uses_proxy_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_uses_proxy_description")
        )->withValue($maxima_uses_proxy_value);

        return self::$factory->input()->field()->section(
            [
                'maxima_command' => $maxima_command,
                //'optimized_maxima_command' => $optimized_maxima_command,
                'plot_command' => $plot_command,
                'maxima_uses_proxy' => $maxima_uses_proxy,
            ],
            $plugin_object->txt("ui_admin_configuration_connection_maxima_connection_local_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_connection_local_description")
        );

    }

}