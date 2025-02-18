<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.



// This provides helper code for creating the files needed to connect to the CAS.

//require_once(__DIR__ . '/../../locallib.php');
require_once(__DIR__ . '/../utils.class.php');
//require_once(__DIR__ . '/ast.container.class.php');
//require_once(__DIR__ . '/connectorhelper.class.php');
//require_once(__DIR__ . '/cassession2.class.php');


use classes\core\filters\StackParser;
use classes\platform\StackPlatform;
use classes\platform\StackConfig;

class stack_cas_configuration {
    protected static $instance = null;

    /**
     * @var array This variable controls which optional packages are supported by STACK.
     * Each key is the library name, and the value checks the library appears loaded and working.
     * Each must return a boolean value true if and only if the library is loaded.
     *
     * When adding a library update the language string settingmaximalibraries_desc.
     **/
    public static $maximalibraries = array(
        'stats' => 'is(op(test_mean([1,2,3]))=inference_result)',
        'distrib' => 'not(is(op(pdf_normal(1,3,1))=pdf_normal))',
        'descriptive' => 'atom(mean([1,2,3.2]))',
        'simplex' => 'floatnump(epsilon_lp)'
    );

    protected $settings;

    /** @var string the date when these settings were worked out. */
    protected $date;

    protected $maximacodepath;

    protected $logpath;

    protected $vnum;

    protected $blocksettings;


    /**
     * Constructor, initialises all the settings.
     * @noinspection PhpArrayIndexImmediatelyRewrittenInspection
     */
    public function __construct() {
        global $CFG;
        StackPlatform::initialize('ilias');

        $this->settings = StackConfig::getAll();

        if(!empty($this->settings)){
            $this->settings["maximaversion"] = $this->settings["maxima_version"];

            $this->date = date("F j, Y, g:i a");

            $this->maximacodepath = stack_utils::convert_slash_paths(realpath(dirname(__DIR__)) . '/maxima');

            $this->logpath = stack_utils::convert_slash_paths(realpath($CFG->dataroot) . '/stack/logs');

            //SUR maximaversion to maxima_version
            $this->vnum = (float) substr($this->settings["maxima_version"], 2);

            $this->blocksettings = array();
            //SUR platform to platform_type
            $this->blocksettings['MAXIMA_PLATFORM'] = $this->settings["platform_type"];
            if ($this->blocksettings['MAXIMA_PLATFORM'] == "server") {
                if (isset($this->settings['maxima_uses_proxy']) && $this->settings['maxima_uses_proxy'] == "1") {
                    $this->blocksettings['MAXIMA_PLATFORM'] = "server-proxy";
                }
            }
            $this->blocksettings['maxima_tempdir'] = stack_utils::convert_slash_paths(realpath($CFG->dataroot) . '/stack/tmp/');
            $this->blocksettings['IMAGE_DIR'] = stack_utils::convert_slash_paths(realpath($CFG->dataroot) . '/stack/plots/');

            $this->blocksettings['PLOT_SIZE'] = '[450,300]';
            // These are used by the GNUplot "set terminal" command. Currently no user interface...
            $this->blocksettings['PLOT_TERMINAL'] = 'png';
            $this->blocksettings['PLOT_TERM_OPT'] = 'large transparent';
            $this->blocksettings['PLOT_TERMINAL'] = 'svg';
            // Note, the quotes need to be protected below.
            $this->blocksettings['PLOT_TERM_OPT'] = 'dynamic font \",11\" linewidth 1.2';

            //SUR platformtype to platform_type
            if ($this->settings["platform_type"] === 'win') {
                /*
                 * SUR no windows option in ilias
                $this->blocksettings['DEL_CMD']     = 'del';
                $this->blocksettings['GNUPLOT_CMD'] = $this->get_plotcommand_win();*/
            } else {
                $this->blocksettings['DEL_CMD']     = 'rm';
                //SUR plotcommand to plot_command
                if ((trim($this->settings["plot_command"])) != '') {
                    $this->blocksettings['GNUPLOT_CMD'] = $this->settings["plot_command"];
                }
                //SUR in ilias not possible
                //else if (is_readable('/Applications/Gnuplot.app/Contents/Resources/bin/gnuplot')) {
                //    $this->blocksettings['GNUPLOT_CMD'] = '/Applications/Gnuplot.app/Contents/Resources/bin/gnuplot';
                //}
                else {
                    $this->blocksettings['GNUPLOT_CMD'] = 'gnuplot';
                }
            }
            // Loop over this array to format them correctly...
            /*
             * SUR no windows option in ilias
            if ($this->settings["platform"] === 'win') {
                foreach ($this->blocksettings as $var => $val) {
                    if ($var != 'PLOT_TERM_OPT') {
                        $this->blocksettings[$var] = addslashes(str_replace( '/', '\\', $val));
                    }
                }
            }*/

            //SUR maximaversion to maxima_version
            $this->blocksettings['MAXIMA_VERSION_EXPECTED'] = $this->settings["maxima_version"];
            $this->blocksettings['URL_BASE']       = '!ploturl!';
            /*
             * SUR no windows option in ilias

            if ($this->settings["platform"] === 'win') {
                $this->blocksettings['URL_BASE']       = '!ploturl!/';
            }*/
        }
    }

    /**
     * Try to guess the gnuplot command on Windows.
     * @return string the command.
     */
    public function get_plotcommand_win() {
        global $CFG;
        if ($this->settings->plotcommand && $this->settings->plotcommand != 'gnuplot') {
            return $this->settings->plotcommand;
        }

        // This does its best to find your version of Gnuplot...
        $maximalocation = $this->maxima_win_location();

        $plotcommands = array();
        $plotcommands[] = $maximalocation. 'gnuplot/wgnuplot.exe';
        $plotcommands[] = $maximalocation. 'bin/wgnuplot.exe';
        $plotcommands[] = $maximalocation. 'gnuplot/bin/wgnuplot.exe';

        // I'm really now totally and finally fed up with dealing with spaces in MS filenames.
        $newplotlocation = stack_utils::convert_slash_paths($CFG->dataroot . '/stack/wgnuplot.exe');
        foreach ($plotcommands as $plotcommand) {
            if (file_exists($plotcommand)) {
                if (substr_count($plotcommand, ' ') === 0) {
                    $newplotlocation = stack_utils::convert_slash_paths($CFG->dataroot . '/stack/wgnuplot.bat');
                    if (!file_put_contents($newplotlocation, $this->maxima_win_location() .
                        "gnuplot/bin/wgnuplot.exe %1 %2 %3 %3 %5 %6 %7 \n\n")) {
                        throw new stack_exception('Failed to write wgnuplot batch file to:'. $newplotlocation);
                    }
                } else {
                    copy($plotcommand, $newplotlocation);
                }
                return $newplotlocation;
            }
        }
        throw new stack_exception('Could not locate GNUPlot.');
    }

    public function maxima_win_location() {
        if ($this->settings['platform'] != 'win') {
            return '';
        }

        $locations = array();
        $locations[] = 'C:/maxima-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/maxima-' . $this->settings->maximaversion . 'a/';
        $locations[] = 'C:/Maxima-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/bin/Maxima-gcl-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/bin/Maxima-sbcl-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/Program Files/Maxima-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/Program Files (x86)/Maxima-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/Program Files/Maxima-sbcl-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/Program Files (x86)/Maxima-sbcl-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/Program Files/Maxima-gcl-' . $this->settings->maximaversion . '/';
        $locations[] = 'C:/Program Files (x86)/Maxima-gcl-' . $this->settings->maximaversion . '/';
        if ('5.25.1' == $this->settings->maximaversion) {
            $locations[] = 'C:/Program Files/Maxima-5.25.1-gcl/';
            $locations[] = 'C:/Program Files (x86)/Maxima-5.25.1-gcl/';
        }
        if ('5.28.0' == $this->settings->maximaversion) {
            $locations[] = 'C:/Program Files/Maxima-5.28.0-2/';
            $locations[] = 'C:/Program Files (x86)/Maxima-5.28.0-2/';
        }
        if ('5.31.1' == $this->settings->maximaversion) {
            $locations[] = 'C:/Program Files/Maxima-5.31.1-1/';
            $locations[] = 'C:/Program Files (x86)/Maxima-5.31.1-1/';
        }
        $locations[] = 'C:/Program Files/Maxima/';
        $locations[] = 'C:/Program Files (x86)/Maxima/';

        foreach ($locations as $location) {
            if (file_exists($location.'bin/maxima.bat')) {
                return $location;
            }
        }

        throw new stack_exception('Could not locate the directory into which Maxima is installed. Tried the following:' .
                implode(', ', $locations));
    }

    public function copy_maxima_bat() {
        global $CFG;

        $batchfilename = $this->maxima_win_location() . 'bin/maxima.bat';
        if (substr_count($batchfilename, ' ') === 0) {
            $batchfilecontents = "rem Auto-generated Maxima batch file.  \n\n";
            $batchfilecontents .= $batchfilename."\n\n";
            if (!file_put_contents($CFG->dataroot . '/stack/maxima.bat', $batchfilecontents)) {
                throw new stack_exception('Failed to write Maxima batch file.');
            }
            return true;
        }

        // If there are spaces within the pathname to the windows batch file we need to copy the batch file.
        if (!copy($batchfilename, $CFG->dataroot . '/stack/maxima.bat')) {
            throw new stack_exception('Could not copy the Maxima batch file ' . $batchfilename .
                ' to location ' . $CFG->dataroot . '/stack/maxima.bat');
        }
        return true;
    }

    public function get_maximalocal_contents() {
        if(is_array($this->blocksettings)){
        $contents = <<<END
/* ***********************************************************************/
/* This file is automatically generated at installation time.            */
/* The purpose is to transfer configuration settings to Maxima.          */
/* Hence, you should not edit this file.  Edit your configuration.       */
/* This file is regularly overwritten, so your changes will be lost.     */
/* ***********************************************************************/

/* File generated on {$this->date} */

/* Add the location to Maxima's search path */
file_search_maxima:append( [sconcat("{$this->maximacodepath}/###.{mac,mc}")] , file_search_maxima)$
file_search_lisp:append( [sconcat("{$this->maximacodepath}/###.{lisp}")] , file_search_lisp)$
file_search_maxima:append( [sconcat("{$this->logpath}/###.{mac,mc}")] , file_search_maxima)$
file_search_lisp:append( [sconcat("{$this->logpath}/###.{lisp}")] , file_search_lisp)$

STACK_SETUP(ex):=block(
    MAXIMA_VERSION_NUM_EXPECTED:{$this->vnum},

END;
        foreach ($this->blocksettings as $name => $value) {
            if ($name == 'PLOT_SIZE') {
                $contents .= <<<END
    {$name}:{$value},

END;
            } else {
                $contents .= <<<END
    {$name}:"{$value}",

END;
            }
        }
        $contents .= stack_cas_casstring_units::maximalocal_units();
        $contents .= <<<END
    true)$

END;

        if ($this->settings['platform_type'] == 'linux-optimised') {
            $contents .= <<<END
/* We are using an optimised lisp image with maxima and the stack libraries
   pre-loaded. That is why you don't see the familiar load("stackmaxima.mac")$ here.
   We do need to ensure the values of the variables is reset now.
*/
STACK_SETUP(true);
END;

        } else {
            $contents .= <<<END
/* Load the main libraries. */
load("stackmaxima.mac")$

END;
            $maximalib = $this->settings['cas_maxima_libraries'];
            $maximalib = explode(',', $maximalib);
            foreach ($maximalib as $lib) {
                $lib = trim($lib);
                // Only include and load supported libraries.
                if (in_array($lib, array_keys(self::$maximalibraries))) {
                    $contents .= 'load("'.$lib.'")$'."\n";
                }
            }

        }

        $contents .= 'print(sconcat("[ STACK-Maxima started, library version ", stackmaximaversion, " ]"))$'."\n";
            return $contents;
        }
        return '';
    }

    /**
     * @return stack_cas_configuration the singleton instance of this class.
     */
    protected static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the full path for the maximalocal.mac file.
     * @return string the full path to where the maximalocal.mac file should be stored.
     */
    public static function maximalocal_location() {
        global $CFG;
        return stack_utils::convert_slash_paths(realpath("./" . ILIAS_WEB_DIR."/".CLIENT_ID) . '/xqcas' . '/stack/maximalocal.mac');
    }

    /**
     * Get the full path to the folder where plot files are stored.
     * @return string the full path to where the maximalocal.mac file should be stored.
     */
    public static function images_location() {
        global $CFG;
        return stack_utils::convert_slash_paths(realpath("./" . ILIAS_WEB_DIR."/".CLIENT_ID) . '/xqcas' . '/stack/plots');
    }

    /**
     * Create the maximalocal.mac file, overwriting it if it already exists.
     */
    public static function create_maximalocal() {
        if (!is_dir(realpath("./" . ILIAS_WEB_DIR."/".CLIENT_ID) . '/xqcas' . '/stack')) {
            mkdir(realpath("./" . ILIAS_WEB_DIR."/".CLIENT_ID) . '/xqcas' . '/stack', 0755, true);
            mkdir(realpath("./" . ILIAS_WEB_DIR."/".CLIENT_ID) . '/xqcas' . '/stack/logs');
            mkdir(realpath("./" . ILIAS_WEB_DIR."/".CLIENT_ID) . '/xqcas' . '/stack/plots');
            mkdir(realpath("./" . ILIAS_WEB_DIR."/".CLIENT_ID) . '/xqcas' . '/stack/tmp');
        }

        if (!file_put_contents(self::maximalocal_location(), self::generate_maximalocal_contents())) {
            throw new stack_exception('Failed to write Maxima configuration file.');
        }
    }

    /**
     * Generate the contents for the maximalocal configuration file.
     * @return string the contents that the maximalocal.mac file should have.
     */
    public static function generate_maximalocal_contents() {
        return self::get_instance()->get_maximalocal_contents();
    }

    /**
     * Generate the directoryname
     * @return string the contents that the maximalocal.mac file should have.
     */
    public static function confirm_maxima_win_location() {
        return self::get_instance()->maxima_win_location();
    }

    /**
     * This function checks the current setting match to the supported packages.
     */
    protected function get_validate_maximalibraries() {

        $valid = true;
        // Hold test cases for libraries to test.
        $livetestcases = array();
        $message = '';
        $permittedlibraries = array_keys(self::$maximalibraries);
        $maximalib = $this->settings['cas_maxima_libraries'];
        $maximalib = explode(',', $maximalib);
        foreach ($maximalib as $lib) {
            $lib = trim($lib);
            // Only include and load supported libraries.
            if ($lib !== '') {
                if (in_array($lib, $permittedlibraries)) {
                    // We need to check the library really loaded in the Maxima image.
                    $livetestcases[$lib] = self::$maximalibraries[$lib];
                } else {
                    $valid = false;
                    $a = $lib;
                    $message .= stack_string('settingmaximalibraries_error', $a);
                }
            }
        }
        return(array($valid, $message, $livetestcases));
    }

    /**
     * This function checks the current setting match to the supported packages.
     */
    public static function validate_maximalibraries() {
        return self::get_instance()->get_validate_maximalibraries();
    }

    /*
     * This function genuinely recreates the maxima image and stores the results in
     * the configuration settings.
     */
    public static function create_auto_maxima_image() {
        $config = get_config('qtype_stack');
            // Do not try to generate the optimised image on MS platforms.
        if ($config['platform'] == 'win') {
            $errmsg = "Microsoft Windows version cannot be automatically optimised";
            return array(false, $errmsg);
        } else if ($config['platform'] != 'linux' && $config['platform'] != 'linux-optimised') {
            $errmsg = '$config["platform"] version cannot be automatically optimised';
            return array(false, $errmsg);
        }

        // Revert to the plain Linux platform.  This will genuinely call the CAS, and
        // as a result create a new image.
        $oldplatform = $config['platform'];
        set_config('platform', 'linux', 'qtype_stack');
        if ($oldplatform == 'linux-optimised') {
            // If we have explicitly set a path, or a --use-version = we should respect it here.
            set_config('maximacommand', '', 'qtype_stack');
            self::get_instance()->settings->maximacommand = '';
            self::get_instance()->settings['platform'] = 'linux';
            stack_utils::get_config()->maximacommand = '';
            stack_utils::get_config()['platform'] = 'linux';
        }

        // Try to make a new version of the maxima local file.
        self::create_maximalocal();
        // Try to actually connect to Maxima.
        list($message, $genuinedebug, $result) = stack_connection_helper::stackmaxima_genuine_connect();

        // Check if the libraries look like they are messing things up.
        if (strpos($genuinedebug, 'eval_string not found') > 0) {
            // If so, get rid of the libraries and try again.
            set_config('maximalibraries', '', 'qtype_stack');
            stack_utils::get_config()->maximalibraries = '';
            list($message, $genuinedebug, $result) = stack_connection_helper::stackmaxima_genuine_connect();
        }

        $revert = true;
        if (!$result) {
            $errmsg = "Uncached connection failed: $message\n\n$genuinedebug";

        } else {
            // Try to auto make the optimised image.
            list($message, $genuinedebug, $result, $commandline, $rawcommand)
                    = stack_connection_helper::stackmaxima_auto_maxima_optimise($genuinedebug);

            if (!$result) {
                $errmsg = "Automake failed: $message\n\n$genuinedebug";
            } else {
                set_config('platform', 'linux-optimised', 'qtype_stack');
                set_config('maximacommandopt', $commandline, 'qtype_stack');
                stack_utils::get_config()['platform'] = 'linux-optimised';
                stack_utils::get_config()->maximacommandopt = $commandline;
                self::get_instance()->settings['platform'] = 'linux-optimised';
                self::get_instance()->settings->maximacommandopt = $commandline;
                // We need to regenerate this file to supress stackmaxima.mac and libraries being reloaded.
                self::create_maximalocal();

                // Now we need to check this actually works.
                $cs = stack_ast_container::make_from_teacher_source('a:1+1', '', new stack_cas_security());
                $ts = new stack_cas_session2(array($cs));
                $ts->instantiate();
                if ($cs->get_value() != '2') {
                    $errors = $ts->get_errors();
                    $errmsg = "Evaluation test failed, errors: $errors";
                } else {
                    // It worked!
                    $errmsg = '';
                    $revert = false;
                }
            }
        }

        if ($revert) {
            set_config('platform', $oldplatform, 'qtype_stack');
            stack_utils::get_config()['platform'] = $oldplatform;
            self::get_instance()->settings['platform'] = $oldplatform;
            self::create_maximalocal();
            return array(false, $errmsg);
        } else {
            return array(true, $rawcommand);
        }
    }
}
