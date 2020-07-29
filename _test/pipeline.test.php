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
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PipelineUtility.php');
require_once(__DIR__ . '/../class/PluginUtility.php');


class plugin_combo_pipeline_test extends DokuWikiTest
{



    public function setUp()
    {

        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();

    }


    /**
     * Test a ad tag
     *
     */
    public function test_pipeline_replace()
    {

        $input = '"Hallo World" | replace("World","You") ';
        $output = PipelineUtility::execute($input);
        $this->assertEquals("Hallo You", $output);


    }


}
