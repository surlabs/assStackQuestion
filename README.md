Copyright 2017 Institut fuer Lern-Innovation,Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3 or later, see LICENSE

Includes a modified core part of STACK version 3.3
Copyright 2012 University of Birmingham
licensed under GPLv3 or later, see classes/stack/COPYING.txt
http://stack.bham.ac.uk

ILIAS STACK Question Type Plugin
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

* The stable version 2.4.x for **ILIAS 5.2** is found in the GitHub branch **master-ilias52**
* The stable version 2.3.x for **ILIAS 5.0 to 5.1** is found in the GitHub branch **master**

Version 2.3.12 (2017-4-19) for ILIAS 5.0 and 5.1
------------------------------------------------
- Now specific feedback is also shown in Test results
- This version includes some bugfixing:
    - http://www.ilias.de/mantis/view.php?id=20387 about error displaying info messages in ILIAS 5.0.
    - http://www.ilias.de/mantis/view.php?id=20407 about mistake in german text.

Version 2.3.11 (2017-04-19)
---------------------------
- This version includes some bugfixing:
    - Now forbidden words are properly forbidden when used by students in a STACK question
    - Feedback is now properly shown when there are more than two feedback placeholder on the specific feedback.
    - Info for feedback in a node is now properly shown.
    - Input fields in authoring interface now doesn't have a blank space by default, we changed the DB structure in order to avoid this problem.

Version 2.3.10 (2017-03-06)
---------------------------
- Changes in the Authoring interface:
    - Now expandable sections for input fields are independent, it's allowed to open more than one input field at the same time.
    - Now inputs and option field takes the whole page like PRTs, to give the user a better view of the different options.
    - Now enable and disable info messages is done instantly by clicking the button in top of the authoring interface. Info messages are shown by default
      And is user's decision about this is saved in session, so user can move among pages having always the same view.
    - Info messages of certain settings also includes a tooltip explaining which type of content the input accepts (CAS Text, HTML or CAS commands)
    - Teacher answer and Student answer are now required fields.
    - Error messages when fields are missing or when CAS validation failed has been re-built, If input has no model answer and if node has no
      student answer or teacher answer, error message persist until fail have been solved.
- This version includes some bugfixing:
    - http://www.ilias.de/mantis/view.php?id=20184 regarding feedback report in specific feedback block.
    - Error when  nodes are not part of a PRT in question authoring no longer shows a blank page but an error message.
    - http://www.ilias.de/mantis/view.php?id=20287 Error in ILIAS 5.0 for using ilButton::getInstance
    - Solved error related to random variables

Version 2.3.9 (2017-1-31)
-------------------------
- Scoring page shows a new information text explaining the behaviour of the page.
- This version includes some bugfixing.
    - http://www.ilias.de/mantis/view.php?id=20117 Regarding inconsistences when showing specific feedback.
    - http://www.ilias.de/mantis/view.php?id=18371 Regarding delete of inputs and PRT in a test, changes were not properly applied to the question pool.
    - http://www.ilias.de/mantis/view.php?id=18703 Regarding delete of PRT nodes was not done in question authoring.
    - http://www.ilias.de/mantis/view.php?id=20111 Regarding LaTeX problem in CASText.
    - http://www.ilias.de/mantis/view.php?id=19746 Regarding Matrix Parentheses always set to square brackets.

Version 2.3.8 (2016-11-29)
--------------------------
- Healthcheck no longer shows error when there are no errors.
- General feedback is now properly shown if best solution is shown.
- Now Maxima's version of user solution or best solution is shown as replacement for the validation button when feedback is given or best solution is given in Tests, This is a provisional change, waiting for feedback from the community, This option is only available if specific feedback per answer option is active in the instant feedback settings.
- Changes in the Authoring interface:
    - Now the Potential Response Tree part takes more space into the left of the page to allow teachers to check better the whole structure of the PRT.
    - Now required fields in authoring interface have a red * on the right of the title like in other ILIAS forms.
- This version includes some bugfixing.
    - http://www.ilias.de/mantis/view.php?id=19483 regarding show feedback for correct/wrong solutions when forcing feedback.
    - http://www.ilias.de/mantis/view.php?id=18939 regarding LaTeX display.
    - http://www.ilias.de/mantis/view.php?id=18343 regarding < and > symbols in feedback specific variables.

Version 2.3.7 (2016-10-24)
--------------------------
This version includes some bugfixing.
- The feedback presentation is now properly shown in both ILIAS 5.1 and 5.0.
- http://www.ilias.de/mantis/view.php?id=19249 regarding random variables in preview
- http://www.ilias.de/mantis/view.php?id=19002 and http://www.ilias.de/mantis/view.php?id=18974 regarding feedback in 5.1
- http://www.ilias.de/mantis/view.php?id=19290 regarding changes in teacher view of students results.
- http://www.ilias.de/mantis/view.php?id=18577 regarding feedback in 5.1

Version 2.3.5 (2016-08-11)
--------------------------
- This version includes some bugfixing.
    - http://www.ilias.de/mantis/view.php?id=18477 Regarding Error in matrix questions feedback.
    - Usability problem regarding old feedback after validation solved, now feedback given is gone when input is changed in instant validation mode or when click on validate if instant validation is not active.

Version 2.3.4 (2016-05-24)
--------------------------
- This version includes some bugfixing.
    - http://www.ilias.de/mantis/view.php?id=18343 Regarding error with < and <= in question variables.
    - http://www.ilias.de/mantis/view.php?id=18404 and
    - http://www.ilias.de/mantis/view.php?id=18091 Regarding error when more than one matrix in the same question
    - http://www.ilias.de/mantis/view.php?id=18263 Regarding matrix representation in best solutions
    - http://www.ilias.de/mantis/view.php?id=18347 Regarding feedback placeholder not created
    - Solved a false error message in the healthcheck.

Version 2.3.3 (2016-04-13)
--------------------------
- In configuration, the list of maxima versions available is now the same than in STACK 3.3 for Moodle. Plase notice that as it is said in the
STACK for Moodle version, some maxima versions can present some errors.
- The following bugs have been solved
    - http://www.ilias.de/mantis/view.php?id=18124 Regarding error when model answer is 0.
    - http://www.ilias.de/mantis/view.php?id=18244 Regarding text error of question note.
    - http://www.ilias.de/mantis/view.php?id=18248 Regarding error in text.
    - http://www.ilias.de/mantis/view.php?id=18249 Regarding scoring of a new question, now set to 1.
    - http://www.ilias.de/mantis/view.php?id=18246 regarding Inputs in recently created question.
    - http://www.ilias.de/mantis/view.php?id=18247 regarding Model answer of new inputs.
    - http://www.ilias.de/mantis/view.php?id=18123 Regarding best solution showing.
    - http://www.ilias.de/mantis/view.php?id=18263 Regarding error with matrix parens

Version 2.3.1 (2016-03-14)
--------------------------
This version includes some ideas from SIG Mathe+ILIAS Meeting in Bremen, like the links to the authoring guides in the head of the authoring page.
- It solve problems with Linux installations and creaation of non-full unit test.
- The following bugs have been solved
    - http://ilias.de/mantis/view.php?id=17358 regarding Matrix in STACK, now matrix also allows |,{ and "" as matrix parents.
    - http://ilias.de/mantis/view.php?id=18091 regarding Matrix best solutions, now best solution have a Matrix form without matrix parents.
    - http://ilias.de/mantis/view.php?id=18081 regarding Error message.
    - http://ilias.de/mantis/view.php?id=18069 regarding navigation from tabs within STACK and evaluable previews.

Version 2.3.0 (2016-02-29)
--------------------------
- STACK plugin can be used in ILIAS 5.0 and ILIAS 5.1 versions.
- New feedback report system:
- New ILIAS feedback tab, it works like in the other ILIAS question types and is ILIAS only, cannot be exported to Moodle. This feedback is shown when
  "feedback on fully correct answer" or "Feedback" options is activated in test settings. This feedback report appears always under the question text and
  there is two different messages, one qhen the question is correct and other if it is not fully correct. This is a normal Text and cannot contain CASText.
- Inline feedback is allowed. Feedback placeholders can be included into the question text, if done, the specific feedback for a PRT will appear within the
  question text, if not, the feedback placeholder must be in the specific feedback, a normal text area where the specific feedback will appear
  By default, specific feedback will appear under the question text. This feedback is shown when "Specific Feedback for Each Answer provided" or "Feedback" option
  are checked, then the specific feedback will be shown under the question text.
- Best solution is now displayed in the ILIAS way, like a question text filled in with the correct answer (model answer). Under this the how to solve (general feedback)
  is shown in case it exist. If there is no model answer in an input, best solution for that input will not be shown. Best solution is shown when "Show best possible answer"
  or "best solution" is checked.
- In question preview, STACK Evaluation button is not longer used, now the ILIAS check button is used to send the user answer in previews.
- The adaptation process to the new feedback system makes that all questions with no feedback placeholders will get automatically one feedback placeholder per PRT in
  the specific feedback field. Teachers can move this placeholder to the question text if they want.
    - The following bugs have been solved:
    - http://www.ilias.de/mantis/view.php?id=17984 regarding export to Moodle.
    - http://www.ilias.de/mantis/view.php?id=17989 regarding using of the plugin with ILIAS 5.1
    - http://www.ilias.de/mantis/view.php?id=15904 regarding order in PRT feedback
    - http://www.ilias.de/mantis/view.php?id=16915 regarding feedback report.
    - http://www.ilias.de/mantis/view.php?id=15088 regarding feedback report.
    - http://www.ilias.de/mantis/view.php?id=16074 regarding feedback report.
    - http://www.ilias.de/mantis/view.php?id=16665 regarding random variables in preview.
    - http://www.ilias.de/mantis/view.php?id=16640 regarding feedback report.
    - http://www.ilias.de/mantis/view.php?id=16645 regarding feedback report.
    - http://www.ilias.de/mantis/view.php?id=17774 regarding using of previous answer
