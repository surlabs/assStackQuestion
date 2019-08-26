Copyright 2017 Institut fuer Lern-Innovation,Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3 or later, see LICENSE

Includes a modified core part of STACK version 3.3
Copyright 2012 University of Birmingham
licensed under GPLv3 or later, see classes/stack/COPYING.txt
http://stack.bham.ac.uk

ILIAS STACK Question Type Plugin.
================================

- Author: Jesus Copado <jesus.copado@fim.uni-erlangen.de>, Fred Neumann <fred.neumann@fim.uni-erlangen.de>
- Forum: http://www.ilias.de/docu/goto_docu_frm_3474_2766.html
- Bug Reports: http://www.ilias.de/mantis (Choose project "ILIAS plugins" and filter by category "STACK Question Type")

This plugin is an ILIAS port of STACK, developed by Chris Sangwin. It provides a test question type
for mathematical questions that are calculated by a the Computer Algebra System (CAS) Maxima.
See the original STACK documentation at http://stack.bham.ac.uk/moodle

Additional Software Requirements
--------------------------------

* Maxima (http://maxima.sourceforge.net)

Maxima is a open sorce computer algebra system and part of most Linux distributions.
A version for windows is available, too. Maxima needs to be installed on the web server running
your ILIAS installation.
Either install the package from your linux distribution or download and install it from
sourceforge (http://sourceforge.net/projects/maxima/files/)

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
1. Copy the assStackQuestion directory to your ILIAS installation at the followin path
(create subdirectories, if neccessary):
Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion

2. Go to Administration > Plugins
3. Choose action "Update" for the assStackQuestion plugin
4. Choose action "Activate" for the assStackQuestion plugin
5. Choose action "Refresh Languages" for the assStackQuestion plugin

Configuration and test of the plugin
------------------------------------
1. Go to Administration > Plugins
2. Choose action "Configure" for the assStackQuestion plugin
3. Set the platform type and maxima version according your installation
4. Go to the tab "Health Check" and click "Do Health Check"
5. If some checks are not passed, click "Show Debugging Data" to get more information

Import of questions from moodleXML
----------------------------------
1. Create an ILIAS question pool
2. Click "Create question", choose "Stack Question" and click "Create"
3. Click "Create Question from MoodleXML"
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

* The stable version 3.1.x for **ILIAS 5.4** with new feedback styles is found in the Github branch **master-ilias54**
* The stable version 3.0.x for **ILIAS 5.3** with new functionalities from STACK such a new input types is found in the Github branch **master-ilias53**
* The stable version 2.4.x for **ILIAS 5.2 to 5.3** is found in the GitHub branch **master-ilias52**
* The stable version 2.3.x for **ILIAS 5.0 to 5.1** is found in the GitHub branch **master**

Version 3.1.1 (2019-06-24) for ILIAS 5.4
----------------------------------------
This version includes the changes needed to run STACK questions in ILIAS 5.4.
* A new feature has been included: Feedback Styles, that can be managed through plugin configuration and Layout and Styles / Content styles. You can use your own content style for STACK feedback, In plugin configuration there is a new tab under General Settings / Feedback Styles Settings where settings for this new feature can be found.

Version 3.1.2 (2019-07-01) for ILIAS 5.4
----------------------------------------
The following bugs have been solved:
- https://mantis.ilias.de/view.php?id=25256 About validation of matrix inputs after checking results in preview mode.
- https://mantis.ilias.de/view.php?id=25290 About default values for PRT and Nodes not working properly in non-new questions.

Version 3.1.3 (2019-07-31) for ILIAS 5.4
----------------------------------------
Some small changes has been made in language variables
Now all feedback Types are always displayed in the authoring interface also if no content style has been chosen in the plugin configuration.

Version 3.1.4 (2019-08-23) for ILIAS 5.4
----------------------------------------
This version includes support for PHP 7.2 in ILIAS 5.4 platform, during the process of update this plugin some ILIAS core bugs were found (e.g. importing question pools), those non-STACk related bugs can make the experience of using the plugin in a PHP 7.2 installation not smooth as intended, if you find any bugs in a PHP 7.2 platform, please report it in Mantis.

Version 3.1.5 (2019-08-26) for ILIAS 5.4
----------------------------------------
Some bugs in PHP 7.2 installations has been solved.