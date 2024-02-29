<?php
declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Section;
use classes\platform\StackConfig;

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
        global $DIC;
        //Maxima version
        $maxima_version_options = [
            '5.32.1' => "5.32.1",
            '5.40.0' => '5.40.0', '5.41.0' => '5.41.0',
            '5.42.0' => '5.42.0', '5.42.1' => '5.42.1', '5.42.2' => '5.42.2',
            '5.43.0' => '5.43.0', '5.43.1' => '5.43.1', '5.43.2' => '5.43.2',
            '5.44.0' => '5.44.0', '5.46.0' => '5.46.0', '5.47.0' => '5.47.0',
            'default' => 'default'
        ];

        if (!isset($data["maxima_version"]) || !array_key_exists($data["maxima_version"], $maxima_version_options)) {
            $data["maxima_version"] = "default";

            StackConfig::set('maxima_version', 'default', "connection");
            StackConfig::save();
        }

        $maxima_version = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_version_title"),
            $maxima_version_options,
            $plugin_object->txt("ui_admin_configuration_connection_maxima_version_description")
        )->withValue($data["maxima_version"])->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('maxima_version', $v ?: '5.44.0', "connection");
                }
            ));

        //CAS Connection timeout
        $cas_connection_timeout = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_defaults_cas_connection_timeout_title"),
            $plugin_object->txt("ui_admin_configuration_defaults_cas_connection_timeout_description")
        )->withValue((string)$data["cas_connection_timeout"]?:"10")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('cas_connection_timeout', (string)$v ?: '10', "connection");
                }
            ));

        //CAS result caching
        $cas_result_caching_options = [
            'db' => $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_db"),
            'none' => $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_none"),
        ];
        $cas_result_caching = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_title"),
            $cas_result_caching_options,
            $plugin_object->txt("ui_admin_configuration_connection_cas_result_caching_description")
        )->withValue( $data["cas_result_caching"]?:'db')->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('cas_result_caching', $v ?: 'db', "connection");
                }
            ));

        //Preparse all code
        $preparse_all_options = [
            'true' => $plugin_object->txt("ui_admin_configuration_connection_preparse_all_yes"),
            'false' => $plugin_object->txt("ui_admin_configuration_connection_preparse_all_no"),
        ];

        $preparse_all = self::$factory->input()->field()->select(
            $plugin_object->txt("ui_admin_configuration_connection_preparse_all_title"),
            $preparse_all_options,
            $plugin_object->txt("ui_admin_configuration_connection_preparse_all_description")
        )->withValue($data["preparse_all"] ? 'true' : 'false')->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('preparse_all', $v, "common");
                }
            ));


        //CAS debugging
        $cas_debugging = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_connection_cas_debugging_title"),
            $plugin_object->txt("ui_admin_configuration_connection_cas_debugging_description")
        )->withValue($data["cas_debugging"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('cas_debugging', $v ? "1" : "0", "connection");
                }
            ));
        //Cache parsed expressions longer than
        $cache_parsed_expressions_longer_than = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_cache_parsed_expressions_longer_than_title"),
            $plugin_object->txt("ui_admin_configuration_connection_cache_parsed_expressions_longer_than_description")
        )->withValue(is_numeric($data["cache_parsed_expressions_longer_than"]) ? (string)$data["cache_parsed_expressions_longer_than"] : "0")
            ->withRequired(true)
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    if(is_numeric($v)){
                        StackConfig::set('cache_parsed_expressions_longer_than', $v, "common");
                    } else {
                        global $DIC;
                        throw new ILIAS\Refinery\ConstraintViolationException(
                            $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_connection_cache_parsed_expressions_longer_than_validation"),
                            'not_boolean'
                        );
                    }
                }
            ));

        //Maxima libraries;

        $maxima_libraries = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_libraries_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_libraries_description")
        )->withValue((string)$data["cas_maxima_libraries"]?:"")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    if(is_string($v)){
                        if(preg_match('/^([a-zA-Z]+(?:,\s*[a-zA-Z]+)*)?$/', $v)){
                            StackConfig::set('cas_maxima_libraries', $v, "connection");
                        } else {
                            global $DIC;
                            throw new ILIAS\Refinery\ConstraintViolationException(
                                $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_connection_maxima_libraries_validation"),
                                'not_list'
                            );
                        }
                    } else {
                        global $DIC;
                        throw new ILIAS\Refinery\ConstraintViolationException(
                            $DIC->language()->txt("qpl_qst_xqcas_ui_admin_configuration_connection_maxima_libraries_validation"),
                            'not_list'
                        );
                    }
                }
            ));

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
        global $DIC;
        //URL of the maxima pool
        $maxima_pool_url = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_url_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_url_description")
        )->withValue($data["maxima_pool_url"] ?? "")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('maxima_pool_url', $v ?? "", "server");
                }
            ));

        //Server/username:password of the maxima pool
        $maxima_pool_server_username_password = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_server_username_password_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_pool_server_username_password_description")
        )->withValue($data["maxima_pool_server_username_password"] ?? "")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('maxima_pool_server_username_password', $v ?? "", "server");
                }
            ));


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
        )->withValue($data["maxima_uses_proxy"]  == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('maxima_uses_proxy', $v ? "1" : "0", "server");
                }
            ));

        return self::$factory->input()->field()->section(
            [
                'maxima_pool_url' => $maxima_pool_url,
                'maxima_pool_server_username_password' => $maxima_pool_server_username_password,
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
        global $DIC;
        //Maxima command
        $maxima_command = self::$factory->input()->field()->text(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_command_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_command_description")
        )->withValue($data["maxima_command"] ?? "")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('maxima_command', $v ?? "", "connection");
                }
            ));

        //Optimized Maxima command
        //$optimized_maxima_command_value = $data["optimized_maxima_command"] ?? "";
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

        $maxima_uses_proxy = self::$factory->input()->field()->checkbox(
            $plugin_object->txt("ui_admin_configuration_connection_maxima_uses_proxy_title"),
            $plugin_object->txt("ui_admin_configuration_connection_maxima_uses_proxy_description")
        )->withValue($data["maxima_uses_proxy"] == "1")
            ->withAdditionalTransformation($DIC->refinery()->custom()->transformation(
                function ($v) {
                    StackConfig::set('maxima_uses_proxy', $v ? "1" : "0", "linux");
                }
            ));

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