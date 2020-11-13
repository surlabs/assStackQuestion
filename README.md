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

* The stable version 3.2.x for **ILIAS 6** with the STACK Core version 4.2.2 can be found in the Github branch **master-ilias6**
* The stable version 3.1.x for **ILIAS 5.4** with new feedback styles is found in the Github branch **master-ilias54**
* The stable version 3.0.x for **ILIAS 5.3** with new functionalities from STACK such a new input types is found in the Github branch **master-ilias53**
* The stable version 2.4.x for **ILIAS 5.2 to 5.3** is found in the GitHub branch **master-ilias52**
* The stable version 2.3.x for **ILIAS 5.0 to 5.1** is found in the GitHub branch **master**

Version 3.2.0 (2020-06-18) for ILIAS 6
----------------------------------------
This version includes the STACK core 4.2.2, this STACK version requires a Maxima Version =>5.41, if your installation runs this plugin with a Maxima Pool, the Maxima Pool must include the stack core required files of STACK version 2019090200. 

Version 3.2.1 (2020-06-22) for ILIAS 6
----------------------------------------
The following known issues has been **solved**:
- Textarea questions are not evaluated properly, this will be solved in a bugfix in a few days.
- firstline extra option is not evaluated properly in equivalence reasoning questions.

Version 3.2.2 (2020-06-25) for ILIAS 6
----------------------------------------
The following bugs have been solved:
- 28533 for ILIAS6 only, Test import not working.
- 25256 and 27830 and 24273 about Matrix validation.
- 24199 about dropdown field dissapearing if answered but other prt related inputs are not answered.
- 27560 about inputs not properly deleted.
- 25256 about specific feedback not shown in test results.

Version 3.3.2 (2020-11-13) for ILIAS 6
----------------------------------------
The following bugs have been solved:
- 25938 firstline option not appearing the first line of the teacher answer as hint in equivalence reasoning inputs.
- 24273 In test results, user solutions for Matrix inputs now appears like the rest of the inputs and not as LaTeX entry.