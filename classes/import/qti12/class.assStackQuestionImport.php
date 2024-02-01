<?php
declare(strict_types=1);
/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
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

/**
 * STACK Question IMPORT OF QUESTIONS from an ILIAS file
 *
 * @author Jesús Copado Mejías <stack@surlabs.es>
 * @version $Id: 7.1$
 * @ingroup    ModulesTestQuestionPool
 *
 */

//require_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
//require_once './Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php';


class assStackQuestionImport extends assQuestionImport
{
    /** @var assStackQuestion */
    var $object;

    /**
     * @param assStackQuestion $object
     */
    public function __construct(assStackQuestion $object)
    {
        $this->object = $object;
    }

    /**
     * Receives parameters from a QTI parser and creates a valid ILIAS question object
     *
     * @param object $item The QTI item object
     * @param integer $questionpool_id The id of the parent questionpool
     * @param integer $tst_id The id of the parent test if the question is part of a test
     * @param object $tst_object A reference to the parent test object
     * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
     * @param array $import_mapping An array containing references to included ILIAS objects
     * @access public
     */
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
    {

        global $DIC;
        $ilUser = $DIC['ilUser'];
        // empty session variable for imported xhtml mobs
        unset($_SESSION["import_mob_xhtml"]);

        $presentation = $item->getPresentation();
        $duration = $item->getDuration();
        $shuffle = 0;
        $selectionLimit = null;
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $answers = array();

        //Obtain question general data
        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries((int) $item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $this->object->setPoints((float)$item->getMetadataEntry("POINTS"));

        $this->object->saveQuestionDataToDb();

        //question
        $stack_question = $item->getMetadataEntry('stack_question');

        //New style
        if ($stack_question != null) {
            $stack_question = unserialize(base64_decode($stack_question));

            $this->object = assStackQuestionUtils::_arrayToQuestion($stack_question, $this->object);

            foreach ($this->object->prts as $prt) {
                foreach ($prt->get_nodes() as $node) {
                    $node->truefeedback = $this->processNonAbstractedImageReferences($node->truefeedback, $item->getIliasSourceNic());
                    $node->falsefeedback = $this->processNonAbstractedImageReferences($node->falsefeedback, $item->getIliasSourceNic());
                }
            }
        } else {

            //Old Style

            //Objects
            //$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionOptions.php");
            /* @var assStackQuestionOptions $options_obj */
            $options_obj = unserialize(base64_decode($item->getMetadataEntry('options')));
            $this->object->question_variables = $options_obj->getQuestionVariables();

            $this->object->specific_feedback = $this->processNonAbstractedImageReferences($options_obj->getSpecificFeedback(), $item->getIliasSourceNic());
            $this->object->prt_correct = $this->processNonAbstractedImageReferences($options_obj->getPRTCorrect(), $item->getIliasSourceNic());
            $this->object->prt_incorrect = $this->processNonAbstractedImageReferences($options_obj->getPRTIncorrect(), $item->getIliasSourceNic());
            $this->object->prt_partially_correct = $this->processNonAbstractedImageReferences($options_obj->getPRTPartiallyCorrect(), $item->getIliasSourceNic());
            $this->object->question_note = $this->processNonAbstractedImageReferences($options_obj->getQuestionNote(), $item->getIliasSourceNic());
            $this->object->variants_selection_seed = '';
            $this->object->stack_version = '';

            //options
            $options = array();
            $options['simplify'] = ((int)$options_obj->getQuestionSimplify());
            $options['assumepos'] = ((int)$options_obj->getAssumePositive());
            $options['assumereal'] = ((int)1);
            $options['multiplicationsign'] = ilUtil::secureString((string)$options_obj->getMultiplicationSign());
            $options['sqrtsign'] = ((int)$options_obj->getSqrtSign());
            $options['complexno'] = ilUtil::secureString((string)$options_obj->getComplexNumbers());
            $options['inversetrig'] = ilUtil::secureString((string)$options_obj->getInverseTrig());
            $options['matrixparens'] = ilUtil::secureString((string)$options_obj->getMatrixParens());
            $options['logicsymbol'] = ilUtil::secureString('lang');

            //load options
            try {
                $this->object->options = new stack_options($options);
                //set stack version
                if (isset($question->stackversion->text)) {
                    $this->object->stack_version = (string)ilUtil::secureString((string)$question->stackversion->text);
                }
            } catch (stack_exception $e) {
                $this->error_log[] = $this->object->getTitle() . ': options not created';
            }

            //STEP 3: load xqcas_inputs fields
            //old format load
            //$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionInput.php");
            $inputs_raw = unserialize(base64_decode($item->getMetadataEntry('inputs')));
            $required_parameters = stack_input_factory::get_parameters_used();

            //load all inputs present in the old XML
            /* @var assStackQuestionInput $input */
            foreach ($inputs_raw as $input_name => $input) {


                $input_name = ilUtil::secureString((string)$input_name);
                $input_type = ilUtil::secureString((string)$input->getInputType());

                $all_parameters = array(
                    'boxWidth' => ilUtil::secureString((string)$input->getBoxSize()),
                    'strictSyntax' => ilUtil::secureString((string)$input->getStrictSyntax()),
                    'insertStars' => ilUtil::secureString((string)$input->getInsertStars()),
                    'syntaxHint' => ilUtil::secureString((string)$input->getSyntaxHint()),
                    'syntaxAttribute' => '',
                    'forbidWords' => ilUtil::secureString((string)$input->getForbidWords()),
                    'allowWords' => ilUtil::secureString((string)$input->getAllowWords()),
                    'forbidFloats' => ilUtil::secureString((string)$input->getForbidFloat()),
                    'lowestTerms' => ilUtil::secureString((string)$input->getRequireLowestTerms()),
                    'sameType' => ilUtil::secureString((string)$input->getCheckAnswerType()),
                    'mustVerify' => ilUtil::secureString((string)$input->getMustVerify()),
                    'showValidation' => ilUtil::secureString((string)$input->getShowValidation()),
                    'options' => ilUtil::secureString((string)$input->getOptions()),
                );

                $parameters = array();
                foreach ($required_parameters[$input_type] as $parameter_name) {
                    if ($parameter_name == 'inputType') {
                        continue;
                    }
                    $parameters[$parameter_name] = $all_parameters[$parameter_name];
                }

                //load inputs
                try {
                    $this->object->inputs[$input_name] = stack_input_factory::make($input_type, $input_name, ilUtil::secureString((string)$input->getTeacherAnswer()), $this->object->options, $parameters);
                } catch (stack_exception $e) {
                    $this->object->error_log[] = $this->object->getTitle() . ': ' . $e;
                }
            }

            //PRTs
            /* @var assStackQuestionPRT $prt */
            /* @var assStackQuestionPRTNode $node */
            //$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionPRT.php");
            //$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionPRTNode.php");
            $prts_from_import = unserialize(base64_decode($item->getMetadataEntry('prts')));
            foreach ($prts_from_import as $prt_name => $prt) {
                foreach ($prt->getPRTNodes() as $node_name => $node) {
                    $node->setFalseFeedback($this->processNonAbstractedImageReferences($node->getFalseFeedback(), $item->getIliasSourceNic()));
                    $node->setTrueFeedback($this->processNonAbstractedImageReferences($node->getTrueFeedback(), $item->getIliasSourceNic()));
                }
            }

            //STEP 4:load PRTs and PRT nodes
            $prts_array = array();

            foreach ($prts_from_import as $prt) {
                $prt_data = new stdClass();

                $prt_data->name = $prt->getPRTName();
                $prt_data->value = $prt->getPRTValue();
                $prt_data->autosimplify = $prt->getAutoSimplify();
                $prt_data->feedbackvariables = $prt->getPRTFeedbackVariables();
                $prt_data->firstnodename = $prt->getFirstNodeName();


                foreach ($prt->getPRTNodes() as $xml_node) {
                    try {
                        $node = new stdClass();

                        $node->nodename = $xml_node->getNodeName();
                        $node->description = '';
                        $node->prtname = $prt->getPRTName();
                        $node->truenextnode = $xml_node->getTrueNextNode();
                        $node->falsenextnode = $xml_node->getFalseNextNode();
                        $node->answertest = $xml_node->getAnswerTest() != '' ? $xml_node->getAnswerTest() : 'AlgEquiv';
                        $node->sans = $xml_node->getStudentAnswer() != '' ? $xml_node->getStudentAnswer() : 'ans1';
                        $node->tans = $xml_node->getTeacherAnswer() != '' ? $xml_node->getTeacherAnswer() : '0';
                        $node->testoptions = $xml_node->getTestOptions();
                        $node->quiet = $xml_node->getQuiet();

                        $node->truescore = $xml_node->getTrueScore();
                        $node->truescoremode = $xml_node->getTrueScoreMode();
                        $node->truepenalty = $xml_node->getTruePenalty();
                        $node->trueanswernote = $xml_node->getTrueAnswerNote();
                        $node->truefeedback = $xml_node->getTrueFeedback();
                        $node->truefeedbackformat = $xml_node->getTrueFeedbackFormat();

                        $node->falsescore = $xml_node->getFalseScore();
                        $node->falsescoremode = $xml_node->getFalseScoreMode();
                        $node->falsepenalty = $xml_node->getFalsePenalty();
                        $node->falseanswernote = $xml_node->getFalseAnswerNote();
                        $node->falsefeedback = $xml_node->getFalseFeedback();
                        $node->falsefeedbackformat = $xml_node->getFalseFeedbackFormat();

                        $prt_data->nodes[$node_name] = $node;
                    } catch (stack_exception $e) {
                        $this->error_log[] = $this->object->getTitle() . ': ' . $e;
                    }
                }

                $prts_array[$prt->getPRTName()] = $prt_data;
            }

            $total_value = 0;
            $all_formative = true;

            foreach ($prts_array as $name => $prt_data) {
                $total_value += (float) $prt_data->value;
                $all_formative = false;
            }

            foreach ($prts_array as $name => $prt_data) {
                $prt_value = 0;
                if (!$all_formative) {
                    $prt_value = (float) $prt_data->value / $total_value;
                }
                $this->object->prts[$name] = new stack_potentialresponse_tree_lite($prt_data, $prt_value);
            }

            //SEEDS
            //$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionDeployedSeed.php");
            $deployed_seeds = unserialize(base64_decode($item->getMetadataEntry('seeds')));

            //TODO Not done
            $seeds = array();
            /*
            if (isset($question->deployedseed)) {
                foreach ($question->deployedseed as $seed) {
                    $seeds[] = (int)ilUtil::secureString((string)$seed);
                }
            }*/
            $this->object->deployed_seeds = $seeds;

            //TESTS
            //$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTest.php");
            //$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTestInput.php");
            //$this->object->getPlugin()->includeClass("model/ilias_object/test/class.assStackQuestionTestExpected.php");
            $unit_tests = unserialize(base64_decode($item->getMetadataEntry('tests')));

            //EXTRA INFO
            /* @var assStackQuestionExtraInfo $extra_info */
            //$this->object->getPlugin()->includeClass("model/ilias_object/class.assStackQuestionExtraInfo.php");
            $extra_info = unserialize(base64_decode($item->getMetadataEntry('extra_info')));
            $extra_info->setHowToSolve($this->processNonAbstractedImageReferences($extra_info->getHowToSolve(), $item->getIliasSourceNic()));

            $this->object->general_feedback = $extra_info->getHowToSolve();
            // Don't save the question additionally to DB before media object handling
            // this would create double rows for options, prts etc.
        }

        // Don't save the question additionally to DB before media object handling
        // this would create double rows for options, prts etc.

        /*********************************
         * Media object handling
         * @see assClozeTestImport
         ********************************/

        // handle the import of media objects in XHTML code
        $question_text = $this->object->getQuestion();

        if (is_array($_SESSION["import_mob_xhtml"])) {

            //include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
            //include_once "./Services/RTE/classes/class.ilRTE.php";

            foreach ($_SESSION["import_mob_xhtml"] as $mob) {
                if ($tst_id > 0) {
                    //#22754
                    $importfile = $this->getTstImportArchivDirectory() . '/' . current(explode('?', $mob["uri"]));
                } else {
                    //#22754
                    $importfile = $this->getQplImportArchivDirectory() . '/' . current(explode('?', $mob["uri"]));
                }

                $GLOBALS['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $import_basename = basename($importfile);
                $compiled_media_object =ilObjMediaObject::_saveTempFileAsMediaObject($import_basename, $importfile, FALSE);
                $media_object =& $compiled_media_object;
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());

                $question_text = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $question_text);

                $this->object->specific_feedback = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->specific_feedback);

                $this->object->prt_correct = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->prt_correct);
                $this->object->prt_partially_correct = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->prt_partially_correct);
                $this->object->prt_incorrect = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->prt_incorrect);

                $this->object->general_feedback = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->object->general_feedback);

                foreach ($this->object->prts as $prt) {
                    foreach ($prt->get_nodes() as $node) {
                        $node->truefeedback = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $node->truefeedback);
                        $node->falsefeedback = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $node->falsefeedback);
                    }
                }
            }
        }

        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($question_text, 1));

        $this->object->specific_feedback = ilRTE::_replaceMediaObjectImageSrc($this->object->specific_feedback, 1);

        $this->object->prt_correct = ilRTE::_replaceMediaObjectImageSrc($this->object->prt_correct, 1);
        $this->object->prt_partially_correct = ilRTE::_replaceMediaObjectImageSrc($this->object->prt_partially_correct, 1);
        $this->object->prt_incorrect = ilRTE::_replaceMediaObjectImageSrc($this->object->prt_incorrect, 1);

        $this->object->general_feedback = ilRTE::_replaceMediaObjectImageSrc($this->object->general_feedback, 1);

        foreach ($this->object->prts as $prt) {
            foreach ($prt->get_nodes() as $node) {
                $node->truefeedback = ilRTE::_replaceMediaObjectImageSrc($node->truefeedback, 1);
                $node->falsefeedback = ilRTE::_replaceMediaObjectImageSrc($node->falsefeedback, 1);
            }
        }

        // now save the question as a whole
        $this->object->saveToDb();

        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate(true, null, null, null, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }

        return $import_mapping;
    }

    /**
     * We overwrite this method and modify it so that instead of
     * repacking the elements of import_mob_xhtml, it simply adds them
     *
     * @param $text
     * @param $sourceNic
     * @return string
     */
    protected function processNonAbstractedImageReferences($text, $sourceNic): string
    {
        $reg = '/<img.*src=".*\\/mm_(\\d+)\\/(.*?)".*>/m';
        $matches = null;

        if (preg_match_all($reg, $text, $matches)) {
            $mobs = array();
            for ($i = 0, $max = count($matches[1]); $i < $max; $i++) {
                $mobSrcId = $matches[1][$i];
                $mobSrcName = $matches[2][$i];
                $mobSrcLabel = 'il_' . $sourceNic . '_mob_' . $mobSrcId;

                //if (!is_array(ilSession::get("import_mob_xhtml"))) {
                //    ilSession::set("import_mob_xhtml", array());
                //}

                //$_SESSION["import_mob_xhtml"][] = array(
                $mobs[] = array(
                    "mob" => $mobSrcLabel, "uri" => 'objects/' . $mobSrcLabel . '/' . $mobSrcName
                );
            }

            if (is_array($_SESSION["import_mob_xhtml"])) {
                foreach ($_SESSION["import_mob_xhtml"] as $mob) {
                    $mobs[] = $mob;
                }
            }

            ilSession::set("import_mob_xhtml", $mobs);
        }

        return preg_replace('/src="([^"]*?\/mobs\/mm_([0-9]+)\/.*?)\"/', 'src="il_' . $sourceNic . '_mob_\\2"', $text);
    }
}
