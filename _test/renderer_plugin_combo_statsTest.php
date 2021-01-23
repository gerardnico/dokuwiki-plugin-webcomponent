<?php
/**
 * Copyright (c) 2021. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */


use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

class renderer_plugin_combo_statsTest extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    public function testLowLevel()
    {
        // Save a page
        $pageId = "stats";
        TestUtility::addPage($pageId, "bla", 'Page creation');

        $request = new TestRequest();
        $result = $request->get(array('id' => $pageId,"do"=>"export_combo_stats"), '/doku.php');
        $json = json_decode($result->getContent());
        $this->assertEquals($pageId, $json->id);
        $this->assertEquals("low", $json->quality->level);
    }
}
