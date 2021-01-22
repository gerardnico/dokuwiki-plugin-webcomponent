<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

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

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');

class syntax_plugin_combo_boxTest extends DokuWikiTest
{
    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    public function testRender()
    {
        $text = '<box>Hallo</box>';
        $html = TestUtility::renderText2Xhtml($text);
        $expected = '<div>Hallo</div>';
        $this->assertEquals($expected, $html);
    }

    public function testRenderWithClass()
    {
        $text = '<box class="hallo">Hallo</box>';
        $html = TestUtility::renderText2Xhtml($text);
        $expected = '<div class="hallo">Hallo</div>';
        $this->assertEquals($expected, $html);
    }

    public function testRenderWithNote()
    {
        $text = '<box><note>Hallo</note></box>';
        $html = TestUtility::renderText2Xhtml($text);
        $expected = '<div><div class="alert alert-info" role="note">Hallo</div></div>';
        $this->assertEquals($expected, $html);
    }


}
