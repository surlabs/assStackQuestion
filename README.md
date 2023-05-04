
STACK FOR ILIAS7
================================
Copyright 2023 Laboratorio de Soluciones del Sur, Sociedad Limitada

This plugin version has been developed by Jesús Copado and the SURLABS' Team

Copyright 2014 Institut fuer Lern-Innovation,Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3 or later, see LICENSE

This plugin was developed by Jesús Copado with Fred Neumann's support at the University of Erlangen between 2014 and 2022

Copyright 2012 University of Birmingham

Includes a modified version of the stack folder of the Moodle Plugin STACK version 4.3.9
https://github.com/maths/moodle-qtype_stack/ created by Chris Sangwin.
licensed under GPLv3 or later, see classes/stack/COPYING.txt
http://stack.bham.ac.uk

- Author: Jesus Copado Mejias <jcopado@surlabs.es>
- Forum: http://www.ilias.de/docu/goto_docu_frm_3474_2766.html
- Bug Reports: http://www.ilias.de/mantis (Choose project "ILIAS plugins" and filter by category "STACK Question Type")

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
2. In Customizing/global/plugins/Modules/TestQuestionPool/Questions/ execute git clone https://github.com/surlabs/assStackQuestion.git
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

* The stable version 7.0.x for **ILIAS 7.13+** with the STACK Core version 4.3.9 can be found in the Github branch **stack_for_ilias7**
* The stable version 3.5 for **ILIAS 7.13+** with the STACK Core version 4.2.2 can be found in the Github branch **master-ilias713**
* The stable version 3.4.3 for **ILIAS <= 7.12** with the STACK Core version 4.2.2 can be found in the Github branch **master-ilias7** will enter on only security issues mode in June 2023.
* The stable version 3.2.x for **ILIAS 6** is on only-security-issues maintenance mode.
* The stable version 3.1.x for **ILIAS 5.4** is no longer maintained
* The stable version 3.0.x for **ILIAS 5.3** is no longer maintained
* The stable version 2.4.x for **ILIAS 5.2 to 5.3** is no longer maintained
* The stable version 2.3.x for **ILIAS 5.0 to 5.1** is no longer maintained

Version 7.1.0 (2023.04.09) Installation improvements, Legacy Import files,  Extra options tuning
----------------------------------------
- ILIAS Question Pools and Test created in older plugin versions (<master-ilias713) are again valid to ve imported in current ILIAS installations. some legacy code was included back due to this feature

**It solves the following errors**
- #32052, #30682, #37150? **Solves all composer / install warnings & errors.** --no-plugins is no longer required at cli setup commands
- #29446 **firstline** option for equivalence reasoning inputs is now properly validated and evaluated, firstline as syntaxhint still not working
- #37100 Dropdown inputs are now properly saved in previews.
- #37111 variables are no longer returned in a computer form after checking.
- #37107 **nonotanswered** extra options works again in radio inputs
- #37106 LaTeX in feedback is now properly rendered
- #36787 double seeds entered when adding seeds, seeds backend has been adapted to the new backend (2021).
- #35855 Warning is again shown when randomisation is present in a question without question note.
- #32708 standard matrix brackets are properly set in new questions
- #36980 pictures in feedback are not properly imported in ILIAS format
- #36989 Feedback of moodleXML exports now doesn't disappears
- #35709 Text error in feedback in german version
- #23393 False error message when plot2d option
- logic symbol error in MoodleXML format when importing questions in Moodle


Version 7.0.6 Bugfixing (V) 2023.03.04
----------------------------------------
- Solved #36872 about importing styled feedback from MoodleXML
- Solved #36578 about no not answered extra options for Radio Inputs
- Solved #36864 about CASText in Feedback not being properly rendered in Tests.
- Solved logic symbol error in MoodleXML import/export
- Solved linux and windows local configuration errors, beware this configuration, as it has not been properly tested by the maintainer.

- Version 7.0.5 Bugfixing (IV) 2023.01.27
----------------------------------------
- Solved issue in fresh instalation regarding default server settings

Version 7.0.5 Bugfixing (IV) 2023.01.05
----------------------------------------
- Solved an Error when trying to access the ILIAS feedback tab of a STACK question  from the question List in a non-started Test object
- Solved a bug where Feedback is not presented to the user in Test Results

Version 7.0.3 Bugfixing (III) 2022.11.29
----------------------------------------
- Solved issue importing variables using the "<" symbol, now are automatically replaced by "< " when importing from Moodle and ILIAS.
- Matrix Parenthesis option is active again.
- Check answer type option for inputs is active again.

Version 7.0.2 Bugfixing (II) 2022.11.04
----------------------------------------
- User Solutions and Best solutions in Test Results are now again presented as non editable fields filled in with the user responses and best solutions
- Specific feedback in Test Run is now properly rendered
- Solved #35476 HTML Lost at MoodleXML Importing
- Solved #35477 Input not shown validation option is se to do not show validation.

Version 7.0.1 Bugfixing (I) 2022.10.18
----------------------------------------
- Some changes were added to the authoring interface.
- First node in each potential response tree now shows the copy and delete function properly.
- Adding a node to a certain potential response tree is again possible
  - Instead of a new tab, authors can use the new Add Node button to add nodes to the current prt.
  - Addition and deletion of PRT is still done by adding / deleting the feedback placeholder in the question text or the specific feedback section.

Version 7.0 (New Core!!) for ILIAS 7.13+ (Stable)
----------------------------------------
This version has been declared stable on 2022.09.14
Its main functionalities has been tested by the University of Erlangen and the Helmut Schmidt University.
Some bugfixing may be expected when its use become more extensive. Please keep you ILIAS platform up to date
- **This STACK for ILIAS plugin version requires a Maxima Server with the maxima version 2021120900. Other versions may trigger validation errors or not show the question at all**
- Ensure you clear the cache after updating.
- **Ensure you use the --no-plugins mode on ILIAS Setup if STACK is installed**
- Update of this plugin is currently only Manual on plugins administration.
- **Changes in this Version**
- 95% Percent of the changes on this version were made to the backend, so do not expect many user interface changes.
- The plugin structure has been fully reworked to adapt it to STACK and ILIAS needs.
- The core from STACK plugin Version for Moodle (4.3.9 December 2021) has been included.
* **Use this version on ILIAS 7.13+ Platforms, for previous ILIAS Versions use Branch master-ilias7**
*** 
**Question Pool & Tests Import & Export**
From STACK version 7.1, older pools and test can be again be imported in STACK v7.x platforms.
*** 
**Known issues**
  - Unit Tests are currently not available. Unit Tests created will remain in its current state until time for this at the moment non-extensive used feature has been fund and found.
  - firstline syntax hint is not properly rendered in equivalence reasoning inputs.
  - Check answer type is currently not evaluated
  - Best solution in Test results is not shown properly
  - Instant validation for textareas not working properly
  - Feedback in Tests not rendered properly