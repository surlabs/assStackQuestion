
DEVELOPMENT BRANCH. DO NOT USE IT PRODUCTIVE PLATFORMS
================================

Copyright 2022 Institut fuer Lern-Innovation,Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3 or later, see LICENSE

Includes a modified version of the stack folder of the Moodle Plugin STACK version 4.3.9
https://github.com/maths/moodle-qtype_stack/ created by Chris Sangwin.

Copyright 2012 University of Birmingham
licensed under GPLv3 or later, see classes/stack/COPYING.txt
http://stack.bham.ac.uk

ILIAS STACK Question Type Plugin.
================================

- Author: Jesus Copado <jesus.copado@fau.de>
- Forum: http://www.ilias.de/docu/goto_docu_frm_3474_2766.html
- Bug Reports: http://www.ilias.de/mantis (Choose project "ILIAS plugins" and filter by category "STACK Question Type")

This plugin is an ILIAS port of STACK, developed by Chris Sangwin. It provides a test question type
for mathematical questions that are calculated by a the Computer Algebra System (CAS) Maxima.
See the original STACK documentation at https://stack.maths.ed.ac.uk/demo/

Additional Software Requirements
--------------------------------

* PHP (http://php.net)

The minimum PHP Version required to run this plugin in 7.4

* Maxima (http://maxima.sourceforge.net)

Maxima is a open sorce computer algebra system and part of most Linux distributions.
A version for windows is available, too. Maxima needs to be installed on the web server running
your ILIAS installation.
Either install the package from your linux distribution or download and install it from
sourceforge (http://sourceforge.net/projects/maxima/files/)

**This STACK for ILIAS plugin version requires a Maxima Server with the maxima version 2021120900. Other versions may trigger errors.**


* GNUplot (http://www.gnuplot.info)

GNUplot is used by maxima to generate graphical plots of functions etc. It is freely available
and part of most Linux distrubutions. GNUplot needs to be installed on the web server
running your ILIAS and maxima installations.
Either install the package from your linux distribution or download and install it from
sourceforge (http://sourceforge.net/projects/gnuplot/files/)

* MathJax (http://www.mathjax.org)

MathJax is an open source JavaScript display engine for mathematics. It is used by the STACK plugin
to display maths in question, user input validation and feedback. It can either be linked from
cdn.mathjax.org or downloaded to your own web server. It has to be configured in ILIAS:

1. Go to Administration > Third Party Software > MathJax
2. Enable MathJax and enter the URL to MathJax (local or proposed cdn)
3. Save

First Installation of the plugin
--------------------------------
1. Create subdirectories, if necessary for Customizing/global/plugins/Modules/TestQuestionPool/Questions/
2. In Customizing/global/plugins/Modules/TestQuestionPool/Questions/ execute git clone https://github.com/ilifau/assStackQuestion.git
3. Go to Administration > Plugins
4. Choose action "Update" for the assStackQuestion plugin
5. Choose action "Activate" for the assStackQuestion plugin
6. Choose action "Refresh Languages" for the assStackQuestion plugin

Configuration and test of the plugin
------------------------------------
1. Go to Administration > Plugins
2. Choose action "Configure" for the assStackQuestion plugin
3. Set the platform type and maxima servers according your installation
4. Go to the tab "Health Check" and click "Do Health Check"

Import of questions from moodleXML
----------------------------------
1. Create an ILIAS question pool
2. Click "Create question", choose "Stack Question" and click "Create"
3. Click "Import Question from MoodleXML"
4. Select a moodleXML package on your computer and click "Import"

Usage of STACK questions
------------------------
You can work with a STACK question like any other question in ILIAS. You can preview it in the question pool
and already try it out there. You can copy it to an ILIAS test and use it there.  A a test participant you will
normally answer a question in two steps. First you enter your answer as a formula in an input field and click "Validate"
beneath that field to check how your input is interpretet. This will give you a graphical version of you entry which may
already be simplified. If you entry can't be interpreted, you will get an error message. When you are satisfied with your
input you can evaluate your answer (in self assessment mode) or move to the next question (in an exam).

Version History
===============

* The Beta testing version 6.0 for **ILIAS 7.12+** with the STACK Core version 4.3.9 can be found in the Github branch **stack_for_ilias7**
* The stable version 3.4.x for **ILIAS 7** with the STACK Core version 4.2.2 can be found in the Github branch **master-ilias7**
* The stable version 3.2.x for **ILIAS 6** with the STACK Core version 4.2.2 can be found in the Github branch **master-ilias6**
* The stable version 3.1.x for **ILIAS 5.4** is no longer maintained
* The stable version 3.0.x for **ILIAS 5.3** is no longer maintained
* The stable version 2.4.x for **ILIAS 5.2 to 5.3** is no longer maintained
* The stable version 2.3.x for **ILIAS 5.0 to 5.1** is no longer maintained

Version 6.8.6 for ILIAS 7.12+ (Beta)
----------------------------------------
- This STACK for ILIAS plugin version requires a Maxima Server with the maxima version 2021120900. Other versions may trigger validation errors.
- **This version includes DBUpdate steps which change values in the xqcas_prts and xqcas_prt_nodes tables, please, backup these tables before update.**
- Ensure you clear the cache after updating.
- **Changes in this Version**
- 95% Percent of the changes on this version were made to the backend, so do not expect many user interface changes.
- The core from STACK plugin Version for Moodle (4.3.9 December 2021) has been included.
- assStackQuestion becomes the "alter ego" of stack_question, the Moodle Plugin class. The old assStackQuestion is now named as assStackQuestion2 and it's going to be deleted.
- assStackQuestion is now responsible for all STACK variables, previously managed by assStackQuestionStackQuestion, which it's going to be deleted.
- assStackQuestionDB is now responsible for all DB Access methods, previously managed by the model/ilias_object classes. These classes are going to be deleted.
- assStackQuestionGUI Has been reworked, to adapt it to the new object assStackQuestion.
- assStackQuestionRender is now responsible for all the rendering methods.
- "0" is no longer valid as node name, a DB Update script to automatically change this value (Step 40) has been added
- The Save PRT function now checks also for invalid node names, which may come from Imported questions.
- New attributes added to the question (not yet customisable): assume real, logic symbol, stack version and compiled cache to stack_options  and syntax attribute for stack_inputs
- **The extremely rare and not replicable issue with question variants in test must be solved with this version, as the seed management in test function has been completely redesigned. **
- Questions exported from a platform with a stack plugin version < 6.2 won't be imported using the ILIAS format, as the import/export functions has been adapted to the new assStackQuestion structure. Funding is needed to allow compatibility with previous versions. You can always use the MoodleXML format for that purpose.
- Instant Validation is now active, as well as image in Texts Management.