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
        // Save a test page
        $pageId = "stats";
        TestUtility::addPage($pageId, "bla", 'Page creation');

        /**
         * The p_render function was stolen from the {@link p_cached_output} function
         * used the in the switch of the {@link \dokuwiki\Action\Export::preProcess()} function
         */
        $file = wikiFN($pageId, 0);
        $renderer = "combo_stats";
        global $ID;
        $ID=$pageId;
        $result = p_render($renderer, p_cached_instructions($file,false,$pageId), $info);
        $json = json_decode($result);
        $this->assertEquals($pageId, $json->id);
        $this->assertEquals("low", $json->quality->level);

    }
}
