# JSXGraph Integration in the STACK Question plugin for ILIAS

### Installation steps
1. Update the plugin to the version 7.3.x or superior.
2. In plugin configuration, go to display settings.
3. Activate JSXGraph rendering.

> **Note** JSXGraph rendering is by default **deactivated** in STACK for ILIAS. By activating this option on plugin configuration / display options, you will allow STACK question authors to include JavaScript code blocks within the STACK questions. Please note that this code will be executed during the execution of the questions.

> **Caution** Allowing User's JavaScript code execution may pose a potential security risk.

## What is JSXGraph?
JSXGraph is a cross-browser JavaScript library for interactive geometry, function plotting, charting, and data visualization in the web browser.
[JSXGraph Official Website](http://jsxgraph.uni-bayreuth.de/wp/index.html)

## How is JSXGraph integrated in STACK for ILIAS?
**JSXGraph integration in STACK is an advanced topic**. Please ensure your knowledge in STACK questions and JSXGraph before creating your own JSXGraphs.

As in STACK's Moodle version, JSXGraph are question blocks ``[[jsxgraph]] JAVASCRIPT CODE [[/jsxgraph]]``, which include javascript code from the User to create the JSXGraph and other settings if binding with STACK inputs is desired.
You can find a detailed guide made for Moodle here: https://docs.stack-assessment.org/en/Authoring/JSXGraph/

**Ensure you adapt your STACK Questions with JSXGraph accordingly to the ILIAS Style**

STACK for ILIAS is using the version 1.6.0 of the JSXGraph library (latest version at integration release)
* You can import STACK Questions from Moodle which includes JSXGraph, those questions will require small changes to work in ILIAS.
* As in Moodle Version, we strongly recommend you do not use an HTML-aware editor (TinyMCE) when editing JSXGraph questions. 
* In order to bind points or sliders to STACK inputs, you must replace all references e.g. 'ans1Ref' with its ILIAS input reference, which is formed as follows: ```'xqcas_questionid_inputname'```. You can get questionid from the URL (param q_id) and inputname is the input name. so for a question with q_id = 823, the replacement for all 'ans1Ref' references in case of binding would be: 'xqcas_823_ans1'
* The comments within the code must be done in a / * COMMENT * / format, //COMMENT format comments will make JSXGraph not render.**
* If you want to hide the input, ensure you roll the ``style.display ='none';`` command with DOMContentLoaded as in the following example.

``document.addEventListener("DOMContentLoaded", function () {``

``document.getElementById("xqcas_QUESTIONID_INPUTNAME").style.display ='none';``

``});``

