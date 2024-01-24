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
