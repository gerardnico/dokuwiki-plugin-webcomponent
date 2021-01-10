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

class dokuwiki_plugin_combo_code_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        parent::setUp();
    }

    public function testRequest()
    {

        $pageId = "code";
        $text = '<code html><code></code></code>';

        TestUtility::addPage($pageId, $text);

        // In a request
        $request = new TestRequest();
        $testResponse = $request->get(array('id' => $pageId), '/doku.php');
        $div = $testResponse->queryHTML("#" . syntax_plugin_combo_code::SCRIPT_ID);
        $scriptIdCount = $div->count();
        $this->assertEquals(1, $scriptIdCount, "The number of script  should be one");

        $preElement = $testResponse->queryHTML("pre");
        $this->assertEquals(1, $preElement->count(), "One pre element");

        $codeElement = $testResponse->queryHTML("code");
        $this->assertEquals(1, $codeElement->count(), "One code element");
        $this->assertEquals(true, $codeElement->hasClass("language-html"), "One code element");


    }

    public function testCompo()
    {
        $extra = 'After';
        $text = '<code html><file></file></code>';
        $text .= $extra;
        $expected = '<div id="'.syntax_plugin_combo_code::SCRIPT_ID.'">'.syntax_plugin_combo_code::SCRIPT_CONTENT.'</div>';
        $expected .= '<pre class="plain"><code class="language-html">&lt;file&gt;&lt;/file&gt;</code></pre>';
        $expected .= '<p>'.$extra.'</p>';

        $xhtml = PluginUtility::render($text);
        $this->assertEquals(
            TestUtility::normalizeDokuWikiHtml($expected),
            TestUtility::normalizeDokuWikiHtml($xhtml)
        );

    }


}
