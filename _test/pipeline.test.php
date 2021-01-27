<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

/**
 *
 *
 * plugin_combo
 * @group plugins
 *
 */

use ComboStrap\AdsUtility;
use ComboStrap\PipelineUtility;
use ComboStrap\PluginUtility;
use ComboStrap\TemplateUtility;


require_once(__DIR__ . '/../../combo/class/'.'PipelineUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'TemplateUtility.php');


class plugin_combo_pipeline_test extends DokuWikiTest
{


    public function setUp()
    {

        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();

    }


    /**
     * pipe in title
     */
    public function test_pipeline_pipe_in_title()
    {

        $input = '"Hallo | World" | replace("World","You") ';
        $output = PipelineUtility::execute($input);
        $this->assertEquals("Hallo | You", $output);


    }

    /**
     * pipe in title
     */
    public function test_pipeline_with_template()
    {
        $title = '"World"';
        $template = '"$title" | replace("World","You") ';
        $tplOutput =  TemplateUtility::render($template,"",$title);
        $output = PipelineUtility::execute($tplOutput);
        $this->assertEquals("'You'", $output);


    }

    /**
     *
     */
    public function test_pipeline_replace()
    {

        $input = '"Hallo World" | replace("World","You") ';
        $output = PipelineUtility::execute($input);
        $this->assertEquals("Hallo You", $output);


    }

    /**
     *
     */
    public function test_pipeline_head()
    {

        $input = '"Hallo World" | head(5) ';
        $output = PipelineUtility::execute($input);
        $this->assertEquals("Hallo", $output);


    }

    public function test_pipeline_tail()
    {

        $input = '"Hallo World" | tail(5) ';
        $output = PipelineUtility::execute($input);
        $this->assertEquals("World", $output);


    }

    /**
     *
     */
    public function test_pipeline_concat()
    {

        $input = '"Hallo World" | rconcat(" ...") ';
        $output = PipelineUtility::execute($input);
        $this->assertEquals("Hallo World ...", $output);

        $input = '"Hallo World" | lconcat(" ...") ';
        $output = PipelineUtility::execute($input);
        $this->assertEquals(" ...Hallo World", $output);

    }

    /**
     * Test pipeline in a page
     */
    public function test_pipeline_page()
    {

        $input = '<' . syntax_plugin_combo_pipeline::TAG . '>"Hallo World" | rconcat(" ...") </' . syntax_plugin_combo_pipeline::TAG . '>';
        $output = PluginUtility::render($input);
        $this->assertEquals("Hallo World ...", $output);


    }


}
